<?php

declare(strict_types=1);

namespace App\Controllers\Motorista;

use App\Core\BaseController;
use App\Core\Security;

/**
 * Lançamento de abastecimentos pelo motorista.
 */
class AbastecimentoController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();
        $userId = (int) ($_SESSION['user_id'] ?? 0);

        $registros = $db->fetchAll(
            "SELECT f.*, v.plate, v.brand, v.model, v.category, v.equipment_type
             FROM vehicle_fuel f
             JOIN vehicles v ON v.id = f.vehicle_id
             WHERE f.user_id = ?
             ORDER BY f.fuel_date DESC, f.id DESC
             LIMIT 50",
            [$userId]
        );

        echo $this->view('motorista.abastecimentos.index', [
            'title' => 'Meus Abastecimentos',
            'registros' => $registros,
        ]);
    }

    public function create(): void
    {
        echo $this->view('motorista.abastecimentos.form', [
            'title' => 'Novo Abastecimento',
            'veiculos' => $this->assets(),
        ]);
    }

    public function store(): void
    {
        $vehicles = $this->assets();
        $ids = array_map(static fn(array $v): int => (int) $v['id'], $vehicles);

        $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
        $fuelDate = trim((string) ($_POST['fuel_date'] ?? ''));
        $liters = $this->decimal($_POST['liters'] ?? null);
        $cost = $this->decimal($_POST['cost'] ?? null);
        $km = $this->intOrNull($_POST['km_at_refuel'] ?? null);
        $hours = $this->decimalOrNull($_POST['hours_at_refuel'] ?? null);
        $notes = trim((string) ($_POST['notes'] ?? ''));

        $errors = [];

        if (!in_array($vehicleId, $ids, true)) {
            $errors[] = 'Selecione um veículo/equipamento válido.';
        }
        if ($fuelDate === '' || !strtotime($fuelDate)) {
            $errors[] = 'Informe uma data válida.';
        }
        if ($liters === null || $liters <= 0) {
            $errors[] = 'Informe a quantidade de litros.';
        }
        if ($cost === null || $cost < 0) {
            $errors[] = 'Informe o valor pago.';
        }

        if ($errors !== []) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/motorista/abastecimentos/novo');
        }

        $id = $this->db()->insert('vehicle_fuel', [
            'vehicle_id' => $vehicleId,
            'fuel_date' => date('Y-m-d', strtotime($fuelDate)),
            'liters' => $liters,
            'cost' => $cost,
            'km_at_refuel' => $km,
            'user_id' => (int) ($_SESSION['user_id'] ?? 0),
            'hours_at_refuel' => $hours,
            'notes' => $notes !== '' ? mb_substr($notes, 0, 500) : null,
        ]);

        // Mantém a quilometragem/horímetro do ativo atualizado
        $this->syncAssetOdometers($vehicleId, $km, $hours);

        Security::audit('fuel_created', 'vehicle_fuel', $id, [
            'vehicle_id' => $vehicleId,
            'liters' => $liters,
            'cost' => $cost,
        ]);

        unset($_SESSION['old_input']);
        $_SESSION['flash_success'] = 'Abastecimento registrado com sucesso!';
        $this->redirect('/motorista/abastecimentos');
    }

    /* ===================================================================== */

    private function assets(): array
    {
        return $this->db()->fetchAll(
            "SELECT id, plate, brand, model, category, equipment_type, current_km, current_hours
             FROM vehicles
             WHERE status <> 'inactive'
             ORDER BY category, COALESCE(plate, equipment_type), brand, model"
        );
    }

    private function syncAssetOdometers(int $vehicleId, ?int $km, ?float $hours): void
    {
        $asset = $this->db()->fetch("SELECT current_km, current_hours FROM vehicles WHERE id = ?", [$vehicleId]);

        if (!$asset) {
            return;
        }

        $update = [];

        if ($km !== null && $km > 0 && ($asset['current_km'] === null || $km > (int) $asset['current_km'])) {
            $update['current_km'] = $km;
        }

        if ($hours !== null && $hours > 0 && ($asset['current_hours'] === null || $hours > (float) $asset['current_hours'])) {
            $update['current_hours'] = $hours;
        }

        if ($update !== []) {
            $this->db()->update('vehicles', $update, 'id = ?', [$vehicleId]);
        }
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
