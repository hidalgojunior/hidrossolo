<?php

declare(strict_types=1);

namespace App\Controllers\Motorista;

use App\Core\BaseController;
use App\Core\Security;
use App\Support\FleetAgenda;
use App\Support\FleetCatalog;

/**
 * Lançamento de manutenções pelo motorista.
 *
 * Aceita qualquer tipo de serviço — inclusive troca de pneus — com a lista de
 * itens trocados e a definição da próxima revisão do ativo.
 */
class ManutencaoController extends BaseController
{
    private const TYPES = ['preventive', 'corrective'];

    public function index(): void
    {
        $db = $this->db();
        $userId = (int) ($_SESSION['user_id'] ?? 0);

        $registros = $db->fetchAll(
            "SELECT m.*, v.plate, v.brand, v.model, v.category, v.equipment_type,
                    COALESCE(i.itens, 0) AS itens_qtd, COALESCE(i.total, 0) AS itens_total
             FROM vehicle_maintenance m
             JOIN vehicles v ON v.id = m.vehicle_id
             LEFT JOIN (
                 SELECT maintenance_id, COUNT(*) AS itens, SUM(quantity * unit_cost) AS total
                 FROM vehicle_maintenance_items GROUP BY maintenance_id
             ) i ON i.maintenance_id = m.id
             WHERE m.user_id = ?
             ORDER BY m.maintenance_date DESC, m.id DESC
             LIMIT 50",
            [$userId]
        );

        $ids = array_map(static fn(array $r): int => (int) $r['id'], $registros);
        $itensPorManutencao = [];

        if ($ids !== []) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $linhas = $db->fetchAll(
                "SELECT maintenance_id, description, quantity, unit_cost
                 FROM vehicle_maintenance_items
                 WHERE maintenance_id IN ({$placeholders}) ORDER BY id",
                $ids
            );

            foreach ($linhas as $l) {
                $itensPorManutencao[(int) $l['maintenance_id']][] = $l;
            }
        }

        echo $this->view('motorista.manutencoes.index', [
            'title' => 'Minhas Manutenções',
            'registros' => $registros,
            'itensPorManutencao' => $itensPorManutencao,
        ]);
    }

    public function create(): void
    {
        echo $this->view('motorista.manutencoes.form', [
            'title' => 'Nova Manutenção',
            'veiculos' => $this->assets(),
            'servicos' => FleetCatalog::servicos(),
            'sugestoes' => FleetCatalog::sugestoesItens(),
        ]);
    }

    public function store(): void
    {
        $vehicles = $this->assets();
        $ids = array_map(static fn(array $v): int => (int) $v['id'], $vehicles);

        $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
        $type = (string) ($_POST['type'] ?? '');
        $serviceCategory = (string) ($_POST['service_category'] ?? 'other');
        $date = trim((string) ($_POST['maintenance_date'] ?? ''));
        $nextReview = trim((string) ($_POST['next_review_date'] ?? ''));
        $workshop = trim((string) ($_POST['workshop'] ?? ''));
        $km = $this->intOrNull($_POST['km_at_maintenance'] ?? null);
        $hours = $this->decimalOrNull($_POST['hours_at_maintenance'] ?? null);
        $cost = $this->decimal($_POST['cost'] ?? null);
        $description = trim((string) ($_POST['description'] ?? ''));
        $notes = trim((string) ($_POST['notes'] ?? ''));

        $errors = [];

        if (!in_array($vehicleId, $ids, true)) {
            $errors[] = 'Selecione um veículo/equipamento válido.';
        }
        if (!in_array($type, self::TYPES, true)) {
            $errors[] = 'Selecione o tipo de manutenção.';
        }
        if (!array_key_exists($serviceCategory, FleetCatalog::servicos())) {
            $serviceCategory = 'other';
        }
        if (data_br_para_iso($date) === null) {
            $errors[] = 'Informe uma data válida (dd/mm/aaaa).';
        }
        if ($description === '') {
            $errors[] = 'Descreva o serviço realizado.';
        }
        if ($cost === null || $cost < 0) {
            $errors[] = 'Informe o valor gasto.';
        }

        if ($errors !== []) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/motorista/manutencoes/nova');
        }

        $id = $this->db()->insert('vehicle_maintenance', [
            'vehicle_id' => $vehicleId,
            'type' => $type,
            'service_category' => $serviceCategory,
            'workshop' => $workshop !== '' ? mb_substr($workshop, 0, 255) : null,
            'maintenance_date' => data_br_para_iso($date),
            'next_review_date' => data_br_para_iso($nextReview),
            'km_at_maintenance' => $km,
            'cost' => $cost,
            'description' => $description,
            'user_id' => (int) ($_SESSION['user_id'] ?? 0),
            'hours_at_maintenance' => $hours,
            'notes' => $notes !== '' ? $notes : null,
        ]);

        $itens = $this->itens();

        foreach ($itens as $item) {
            $this->db()->insert('vehicle_maintenance_items', [
                'maintenance_id' => $id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
            ]);
        }

        // Agenda automaticamente a próxima revisão (gera avisos de 30/15/7 dias)
        if ($nextReview !== '' && strtotime($nextReview)) {
            FleetAgenda::syncRevision($vehicleId, $nextReview, (int) ($_SESSION['user_id'] ?? 0), $km);
        }

        Security::audit('maintenance_created', 'vehicle_maintenance', $id, [
            'vehicle_id' => $vehicleId,
            'service_category' => $serviceCategory,
            'cost' => $cost,
            'itens' => count($itens),
        ]);

        unset($_SESSION['old_input']);
        $_SESSION['flash_success'] = 'Manutenção registrada com sucesso!';
        $this->redirect('/motorista/manutencoes');
    }

    /* ===================================================================== */

    /**
     * @return array<int,array{description:string,quantity:float,unit_cost:float}>
     */
    private function itens(): array
    {
        $linhas = $_POST['items'] ?? [];

        if (!is_array($linhas)) {
            return [];
        }

        $itens = [];

        foreach ($linhas as $linha) {
            if (!is_array($linha)) {
                continue;
            }

            $descricao = trim((string) ($linha['description'] ?? ''));

            if ($descricao === '') {
                continue;
            }

            $quantidade = $this->decimal($linha['quantity'] ?? null) ?? 1;
            $valorUnitario = $this->decimal($linha['unit_cost'] ?? null) ?? 0.0;

            $itens[] = [
                'description' => mb_substr($descricao, 0, 255),
                'quantity' => $quantidade > 0 ? $quantidade : 1,
                'unit_cost' => max(0, $valorUnitario),
            ];
        }

        return $itens;
    }

    private function assets(): array
    {
        return $this->db()->fetchAll(
            "SELECT id, plate, brand, model, category, equipment_type, fuel_type, current_km, current_hours
             FROM vehicles
             WHERE status <> 'inactive'
             ORDER BY category, COALESCE(plate, equipment_type), brand, model"
        );
    }

    private function decimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = str_replace(['.', ','], ['', '.'], (string) $value);

        return is_numeric($normalized) ? round((float) $normalized, 2) : null;
    }

    private function decimalOrNull(mixed $value): ?float
    {
        $result = $this->decimal($value);

        return $result !== null && $result > 0 ? $result : null;
    }

    private function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
