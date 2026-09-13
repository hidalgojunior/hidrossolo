<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Security;

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
            $where = 'WHERE category = ?';
            $params[] = $tipo;
        }

        $veiculos = $db->fetchAll(
            "SELECT * FROM vehicles {$where}
             ORDER BY category, COALESCE(NULLIF(plate, ''), equipment_type), brand",
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
