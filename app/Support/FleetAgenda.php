<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\App;

require_once __DIR__ . '/../Core/helpers.php';

/**
 * Agenda de manutenção da frota: sincronização de revisões e avisos.
 *
 * Os avisos são criados na tabela `notifications` (user_id NULL = visível para
 * toda a equipe administrativa) nos marcos de 30, 15 e 7 dias antes do
 * compromisso, sem gerar duplicidade.
 */
final class FleetAgenda
{
    /** Marcos de aviso: dias antes => coluna de controle */
    public const MARCOS = [
        30 => 'notified_30_at',
        15 => 'notified_15_at',
        7 => 'notified_7_at',
    ];

    /**
     * Cria os avisos pendentes para a janela informada.
     *
     * @return int Quantidade de avisos criados
     */
    public static function notifyUpcoming(): int
    {
        $db = App::getInstance()->getDb();
        $criados = 0;

        foreach (self::MARCOS as $dias => $coluna) {
            $dias = (int) $dias;

            $agendamentos = $db->fetchAll(
                "SELECT s.id, s.title, s.scheduled_date, s.scheduled_time, s.type,
                        v.plate, v.brand, v.model, v.category, v.equipment_type
                 FROM fleet_schedules s
                 LEFT JOIN vehicles v ON v.id = s.vehicle_id
                 WHERE s.status = 'planned'
                   AND s.`{$coluna}` IS NULL
                   AND s.scheduled_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$dias} DAY)"
            );

            foreach ($agendamentos as $a) {
                $ativo = $a['plate'] !== null || $a['model'] !== null
                    ? asset_label($a)
                    : 'Frota';

                $quando = date('d/m/Y', strtotime((string) $a['scheduled_date']));
                $hora = !empty($a['scheduled_time']) ? ' às ' . substr((string) $a['scheduled_time'], 0, 5) : '';

                $db->insert('notifications', [
                    'user_id' => null,
                    'title' => $dias <= 7
                        ? "Manutenção em {$dias} dias — {$ativo}"
                        : "Manutenção em {$dias} dias",
                    'message' => trim(($a['title'] ?? 'Compromisso') . " para {$ativo} em {$quando}{$hora}."),
                    'type' => $dias <= 7 ? 'warning' : 'info',
                    'link' => '/admin/agenda',
                ]);

                $db->update('fleet_schedules', [$coluna => date('Y-m-d H:i:s')], 'id = ?', [(int) $a['id']]);
                $criados++;
            }
        }

        return $criados;
    }

    /**
     * Mantém um compromisso de "próxima revisão" para o veículo/equipamento.
     * Substitui o compromisso pendente anterior, mantendo apenas um por ativo.
     */
    public static function syncRevision(int $vehicleId, ?string $data, ?int $userId, ?int $km = null): void
    {
        if ($vehicleId <= 0 || $data === null || $data === '') {
            return;
        }

        $db = App::getInstance()->getDb();
        $data = date('Y-m-d', strtotime($data));

        $existente = $db->fetch(
            "SELECT id FROM fleet_schedules
             WHERE vehicle_id = ? AND type = 'revision' AND status = 'planned'
             ORDER BY scheduled_date ASC LIMIT 1",
            [$vehicleId]
        );

        $ativo = $db->fetch("SELECT plate, brand, model, category, equipment_type FROM vehicles WHERE id = ?", [$vehicleId]);
        $label = $ativo ? asset_label($ativo) : 'Ativo #' . $vehicleId;

        $descricao = 'Próxima revisão de ' . $label;
        if ($km !== null && $km > 0) {
            $descricao .= ' (referência: ' . number_format($km, 0, ',', '.') . ' km)';
        }

        if ($existente) {
            $db->update('fleet_schedules', [
                'title' => 'Revisão — ' . $label,
                'description' => $descricao,
                'scheduled_date' => $data,
                'notified_30_at' => null,
                'notified_15_at' => null,
                'notified_7_at' => null,
            ], 'id = ?', [(int) $existente['id']]);

            return;
        }

        $db->insert('fleet_schedules', [
            'vehicle_id' => $vehicleId,
            'title' => 'Revisão — ' . $label,
            'description' => $descricao,
            'scheduled_date' => $data,
            'type' => 'revision',
            'status' => 'planned',
            'created_by' => $userId,
        ]);
    }

    /**
     * Rótulo de um tipo de compromisso.
     */
    public static function tipoLabel(string $tipo): string
    {
        return [
            'maintenance' => 'Manutenção',
            'revision' => 'Revisão',
            'inspection' => 'Inspeção',
            'other' => 'Outro',
        ][$tipo] ?? $tipo;
    }

    /**
     * Cor (classe de badge) por tipo de compromisso.
     */
    public static function tipoCor(string $tipo): string
    {
        return [
            'maintenance' => 'bg-warning',
            'revision' => 'bg-info',
            'inspection' => 'bg-primary',
            'other' => 'bg-secondary',
        ][$tipo] ?? 'bg-secondary';
    }

    /**
     * Cor por tipo de combustível — usada para diferenciar veículos/equipamentos.
     *
     * @return array{label:string,bg:string,text:string,hex:string}
     */
    public static function combustivelInfo(?string $fuelType, ?string $category = 'vehicle'): array
    {
        $mapa = [
            'diesel' => ['Diesel', 'bg-secondary', '#334155'],
            'gasoline' => ['Gasolina', 'bg-danger', '#dc2626'],
            'ethanol' => ['Etanol', 'bg-success', '#059669'],
            'flex' => ['Flex', 'bg-primary', '#1e40af'],
            'electric' => ['Elétrico', 'bg-info', '#0891b2'],
        ];

        [$label, $bg, $hex] = $mapa[$fuelType ?? 'diesel'] ?? ['Não informado', 'bg-secondary', '#64748b'];

        if (($category ?? 'vehicle') === 'equipment') {
            // Equipamentos recebem um tom próprio (âmbar) para não confundir com veículos
            return ['label' => $label . ' (equipamento)', 'bg' => 'bg-warning', 'hex' => '#d97706'];
        }

        return ['label' => $label, 'bg' => $bg, 'hex' => $hex];
    }
}
