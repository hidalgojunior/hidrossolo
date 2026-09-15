<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Security;
use App\Support\Exportador;
use App\Support\FleetAgenda;

/**
 * Frota e Equipamentos (geradores, compressores...).
 * Ambos vivem na tabela `vehicles`, diferenciados por `category`.
 */
class VeiculosController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();
        $tipo = (string) ($_GET['tipo'] ?? 'all');

        $where = '';
        $params = [];

        if (in_array($tipo, ['vehicle', 'equipment'], true)) {
            $where = 'WHERE v.category = ?';
            $params[] = $tipo;
        }

        $veiculos = $db->fetchAll(
            "SELECT v.*,
                    (SELECT COUNT(*) FROM vehicle_fuel f WHERE f.vehicle_id = v.id) AS fuel_count,
                    (SELECT COUNT(*) FROM vehicle_maintenance m WHERE m.vehicle_id = v.id) AS maint_count,
                    (SELECT COUNT(*) FROM fleet_schedules s WHERE s.vehicle_id = v.id) AS schedule_count,
                    (SELECT COUNT(*) FROM vehicles c WHERE c.parent_vehicle_id = v.id) AS child_count
             FROM vehicles v {$where}
             ORDER BY v.category, COALESCE(NULLIF(v.plate, ''), v.equipment_type), v.brand",
            $params
        );

        $totais = $db->fetch(
            "SELECT
                COALESCE(SUM(category = 'vehicle'), 0) AS veiculos,
                COALESCE(SUM(category = 'equipment'), 0) AS equipamentos
             FROM vehicles"
        );

        echo $this->view('admin.veiculos.index', [
            'title' => 'Frota & Equipamentos',
            'veiculos' => $veiculos,
            'tipo' => $tipo,
            'totais' => $totais,
            'config' => $this->config('company'),
        ]);
    }

    /**
     * Exporta a frota em PDF.
     */
    public function pdf(): void
    {
        Exportador::pdf(Exportador::nomeArquivo('frota'), $this->relatorio());
    }

    /**
     * Exporta a frota em planilha XLSX formatada.
     */
    public function xlsx(): void
    {
        Exportador::xlsx(Exportador::nomeArquivo('frota'), $this->relatorio());
    }

    /**
     * @return array<string,mixed>
     */
    private function relatorio(): array
    {
        $linhas = [];

        foreach ($this->frotaCompleta() as $v) {
            $comb = FleetAgenda::combustivelInfo($v['fuel_type'] ?? null, $v['category'] ?? 'vehicle');

            $linhas[] = [
                'tipo' => ($v['category'] ?? 'vehicle') === 'equipment' ? 'Equipamento' : 'Veículo',
                'ativo' => asset_label($v),
                'ano' => (string) ($v['year'] ?? ''),
                'placa' => (string) ($v['plate'] ?? ''),
                'combustivel' => $comb['label'],
                'km' => ($v['category'] ?? 'vehicle') === 'equipment'
                    ? number_format((float) ($v['current_hours'] ?? 0), 1, ',', '.') . ' h'
                    : number_format((float) ($v['current_km'] ?? 0), 0, ',', '.') . ' km',
                'status' => ($v['status'] ?? '') === 'inactive' ? 'Inativo' : 'Ativo',
            ];
        }

        return [
            'title' => 'Frota & equipamentos',
            'subtitle' => 'Inventário de veículos, geradores e demais equipamentos',
            'company' => $this->config('company'),
            'columns' => [
                ['label' => 'Tipo', 'key' => 'tipo', 'width' => 14, 'type' => 'center'],
                ['label' => 'Identificação', 'key' => 'ativo', 'width' => 40],
                ['label' => 'Ano', 'key' => 'ano', 'width' => 8, 'type' => 'center'],
                ['label' => 'Placa / Série', 'key' => 'placa', 'width' => 16, 'type' => 'center'],
                ['label' => 'Combustível', 'key' => 'combustivel', 'width' => 14, 'type' => 'center'],
                ['label' => 'KM / Horímetro', 'key' => 'km', 'width' => 18, 'type' => 'center'],
                ['label' => 'Situação', 'key' => 'status', 'width' => 12, 'type' => 'center'],
            ],
            'rows' => $linhas,
            'summary' => [
                ['label' => 'Ativos cadastrados', 'value' => (string) count($linhas), 'tone' => 'neutral'],
            ],
            'notes' => 'Documento gerado a partir do módulo Frota & Equipamentos.',
        ];
    }

    public function create(): void
    {
        echo $this->view('admin.veiculos.form', [
            'title' => 'Novo Veículo',
            'veiculo' => null,
            'parents' => $this->parentVehicles(),
            'config' => $this->config('company'),
        ]);
    }

    public function store(): void
    {
        $db = $this->db();
        $category = ($_POST['category'] ?? 'vehicle') === 'equipment' ? 'equipment' : 'vehicle';
        $isEquipment = $category === 'equipment';

        $rules = [
            'brand' => 'required|max:100',
            'model' => 'required|max:100',
        ];

        if (!$isEquipment) {
            $rules['plate'] = 'required|min:7|max:10';
            $rules['year'] = 'required';
        }

        $data = $this->validate($rules);

        $id = $db->insert('vehicles', [
            'plate' => mb_substr($this->plate($isEquipment), 0, 10),
            'brand' => mb_substr((string) $data['brand'], 0, 100),
            'model' => mb_substr((string) $data['model'], 0, 100),
            'year' => (int) ($_POST['year'] ?? date('Y')),
            'renavam' => $this->nullable($_POST['renavam'] ?? null, 20),
            'chassis' => $this->nullable($_POST['chassis'] ?? null, 50),
            'fuel_type' => in_array($_POST['fuel_type'] ?? 'diesel', ['gasoline', 'ethanol', 'diesel', 'flex', 'electric'], true)
                ? (string) $_POST['fuel_type']
                : 'diesel',
            'current_km' => (int) ($_POST['current_km'] ?? 0),
            'category' => $category,
            'equipment_type' => $isEquipment ? $this->nullable($_POST['equipment_type'] ?? null, 100) : null,
            'parent_vehicle_id' => $isEquipment ? $this->intOrNull($_POST['parent_vehicle_id'] ?? null) : null,
            'current_hours' => $isEquipment ? $this->decimalOrNull($_POST['current_hours'] ?? null) : null,
            'status' => 'active',
            'notes' => $this->nullable($_POST['notes'] ?? null),
        ]);

        Security::audit($isEquipment ? 'equipment_created' : 'vehicle_created', 'vehicles', $id);

        $_SESSION['flash_success'] = $isEquipment
            ? 'Equipamento cadastrado com sucesso!'
            : 'Veículo cadastrado com sucesso!';

        $this->redirect('/admin/frota');
    }

    public function edit(string $id): void
    {
        $veiculo = $this->db()->fetch("SELECT * FROM vehicles WHERE id = ?", [$id]);

        if (!$veiculo) {
            $_SESSION['flash_error'] = 'Registro não encontrado.';
            $this->redirect('/admin/frota');
        }

        $manutencoes = $this->db()->fetchAll(
            "SELECT * FROM vehicle_maintenance WHERE vehicle_id = ? ORDER BY maintenance_date DESC",
            [$id]
        );

        $abastecimentos = $this->db()->fetchAll(
            "SELECT * FROM vehicle_fuel WHERE vehicle_id = ? ORDER BY fuel_date DESC LIMIT 20",
            [$id]
        );

        echo $this->view('admin.veiculos.form', [
            'title' => ($veiculo['category'] ?? 'vehicle') === 'equipment' ? 'Editar Equipamento' : 'Editar Veículo',
            'veiculo' => $veiculo,
            'parents' => $this->parentVehicles((int) $id),
            'manutencoes' => $manutencoes,
            'abastecimentos' => $abastecimentos,
            'config' => $this->config('company'),
        ]);
    }

    public function update(string $id): void
    {
        $db = $this->db();
        $atual = $db->fetch("SELECT * FROM vehicles WHERE id = ?", [$id]);

        if (!$atual) {
            $_SESSION['flash_error'] = 'Registro não encontrado.';
            $this->redirect('/admin/frota');
        }

        $category = ($_POST['category'] ?? $atual['category'] ?? 'vehicle') === 'equipment' ? 'equipment' : 'vehicle';
        $isEquipment = $category === 'equipment';

        $rules = [
            'brand' => 'required|max:100',
            'model' => 'required|max:100',
        ];

        if (!$isEquipment) {
            $rules['plate'] = 'required|min:7|max:10';
            $rules['year'] = 'required';
        }

        $data = $this->validate($rules);

        $db->update('vehicles', [
            'plate' => mb_substr($this->plate($isEquipment, (string) ($atual['plate'] ?? '')), 0, 10),
            'brand' => mb_substr((string) $data['brand'], 0, 100),
            'model' => mb_substr((string) $data['model'], 0, 100),
            'year' => (int) ($_POST['year'] ?? $atual['year'] ?? date('Y')),
            'renavam' => $this->nullable($_POST['renavam'] ?? null, 20),
            'chassis' => $this->nullable($_POST['chassis'] ?? null, 50),
            'fuel_type' => $_POST['fuel_type'] ?? 'diesel',
            'current_km' => (int) ($_POST['current_km'] ?? 0),
            'category' => $category,
            'equipment_type' => $isEquipment ? $this->nullable($_POST['equipment_type'] ?? null, 100) : null,
            'parent_vehicle_id' => $isEquipment ? $this->intOrNull($_POST['parent_vehicle_id'] ?? null) : null,
            'current_hours' => $isEquipment ? $this->decimalOrNull($_POST['current_hours'] ?? null) : null,
            'status' => in_array($_POST['status'] ?? 'active', ['active', 'maintenance', 'inactive'], true)
                ? (string) $_POST['status']
                : 'active',
            'notes' => $this->nullable($_POST['notes'] ?? null),
        ], 'id = ?', [$id]);

        Security::audit($isEquipment ? 'equipment_updated' : 'vehicle_updated', 'vehicles', (int) $id);

        $_SESSION['flash_success'] = 'Registro atualizado com sucesso!';
        $this->redirect('/admin/frota');
    }

    /* ===================================================================== */

    /**
     * Ativa/desativa um veículo ou equipamento (operação reversível).
     */
    public function toggleStatus(string $id): void
    {
        $db = $this->db();
        $registro = $db->fetch("SELECT id, status, category FROM vehicles WHERE id = ?", [$id]);

        if (!$registro) {
            $_SESSION['flash_error'] = 'Registro não encontrado.';
            $this->redirect('/admin/frota');
        }

        $novo = ($registro['status'] === 'inactive') ? 'active' : 'inactive';

        $db->update('vehicles', ['status' => $novo], 'id = ?', [$id]);
        Security::audit('vehicle_status_changed', 'vehicles', (int) $id);

        $rotulo = ($registro['category'] ?? 'vehicle') === 'equipment' ? 'Equipamento' : 'Veículo';

        $_SESSION['flash_success'] = $novo === 'inactive'
            ? $rotulo . ' desativado. Ele continua no histórico, mas sai da lista de ativos.'
            : $rotulo . ' reativado com sucesso!';

        $this->redirect('/admin/frota');
    }

    /**
     * Exclui um veículo/equipamento.
     *
     * ATENÇÃO: o schema define ON DELETE CASCADE nas tabelas vehicle_fuel e
     * vehicle_maintenance. Excluir o registro apaga junto todo o histórico de
     * abastecimentos e manutenções dele. Por isso a exclusão é bloqueada
     * quando existe histórico: nesse caso orientamos a desativar.
     */
    public function delete(string $id): void
    {
        $db = $this->db();
        $registro = $db->fetch("SELECT id, category FROM vehicles WHERE id = ?", [$id]);

        if (!$registro) {
            $_SESSION['flash_error'] = 'Registro não encontrado.';
            $this->redirect('/admin/frota');
        }

        $rotulo = ($registro['category'] ?? 'vehicle') === 'equipment' ? 'Equipamento' : 'Veículo';

        $abastecimentos = (int) $db->fetch(
            "SELECT COUNT(*) AS n FROM vehicle_fuel WHERE vehicle_id = ?",
            [$id]
        )['n'];

        $manutencoes = (int) $db->fetch(
            "SELECT COUNT(*) AS n FROM vehicle_maintenance WHERE vehicle_id = ?",
            [$id]
        )['n'];

        $agendamentos = (int) $db->fetch(
            "SELECT COUNT(*) AS n FROM fleet_schedules WHERE vehicle_id = ?",
            [$id]
        )['n'];

        if ($abastecimentos > 0 || $manutencoes > 0) {
            $_SESSION['flash_error'] = sprintf(
                '%s não pode ser excluído: existem %d abastecimento(s) e %d manutenção(ões) ligados a ele, ' .
                'e a exclusão apagaria todo esse histórico. Use "Desativar" para tirá-lo do uso sem perder os registros.',
                $rotulo,
                $abastecimentos,
                $manutencoes
            );

            $this->redirect('/admin/frota');
        }

        // Equipamentos vinculados ficam apenas sem vínculo (ON DELETE SET NULL)
        $db->query('UPDATE vehicles SET parent_vehicle_id = NULL WHERE parent_vehicle_id = ?', [$id]);

        // Agendamentos não têm FK: limpa para não deixar órfãos
        if ($agendamentos > 0) {
            $db->delete('fleet_schedules', 'vehicle_id = ?', [$id]);
        }

        $db->delete('vehicles', 'id = ?', [$id]);
        Security::audit('vehicle_deleted', 'vehicles', (int) $id);

        $_SESSION['flash_success'] = $rotulo . ' excluído com sucesso!';
        $this->redirect('/admin/frota');
    }

    /* ===================================================================== */

    /**
     * Lista completa da frota (veículos e equipamentos), já ordenada.
     *
     * @return array<int,array<string,mixed>>
     */
    private function frotaCompleta(): array
    {
        return $this->db()->fetchAll(
            "SELECT * FROM vehicles
             ORDER BY category, COALESCE(NULLIF(plate, ''), equipment_type), brand"
        );
    }

    private function parentVehicles(?int $excludeId = null): array
    {
        $sql = "SELECT id, plate, brand, model FROM vehicles WHERE category = 'vehicle'";
        $params = [];

        if ($excludeId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $excludeId;
        }

        return $this->db()->fetchAll($sql . ' ORDER BY plate', $params);
    }

    private function plate(bool $isEquipment, string $current = ''): string
    {
        $plate = strtoupper(trim((string) ($_POST['plate'] ?? '')));

        if ($plate !== '') {
            return $plate;
        }

        if ($current !== '') {
            return $current;
        }

        return strtoupper('EQ' . bin2hex(random_bytes(3)));
    }

    private function nullable(mixed $value, ?int $maxLength = null): ?string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return null;
        }

        return $maxLength !== null ? mb_substr($value, 0, $maxLength) : $value;
    }

    private function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }

    private function decimalOrNull(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = str_replace(['.', ','], ['', '.'], (string) $value);

        return is_numeric($normalized) ? round((float) $normalized, 1) : null;
    }
}
