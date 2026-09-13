<?php

declare(strict_types=1);

namespace App\Controllers\Motorista;

use App\Core\BaseController;
use App\Core\Security;

/**
 * Lançamento de manutenções pelo motorista.
 */
class ManutencaoController extends BaseController
{
    private const TYPES = ['preventive', 'corrective'];

    public function index(): void
    {
        $db = $this->db();
        $userId = (int) ($_SESSION['user_id'] ?? 0);

        $registros = $db->fetchAll(
            "SELECT m.*, v.plate, v.brand, v.model, v.category, v.equipment_type
             FROM vehicle_maintenance m
             JOIN vehicles v ON v.id = m.vehicle_id
             WHERE m.user_id = ?
             ORDER BY m.maintenance_date DESC, m.id DESC
             LIMIT 50",
            [$userId]
        );

        echo $this->view('motorista.manutencoes.index', [
            'title' => 'Minhas Manutenções',
            'registros' => $registros,
        ]);
    }

    public function create(): void
    {
        echo $this->view('motorista.manutencoes.form', [
            'title' => 'Nova Manutenção',
            'veiculos' => $this->assets(),
            'tipos' => self::TYPES,
        ]);
    }

    public function store(): void
    {
        $vehicles = $this->assets();
        $ids = array_map(static fn(array $v): int => (int) $v['id'], $vehicles);

        $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
        $type = (string) ($_POST['type'] ?? '');
        $date = trim((string) ($_POST['maintenance_date'] ?? ''));
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
        if ($date === '' || !strtotime($date)) {
            $errors[] = 'Informe uma data válida.';
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
            'workshop' => $workshop !== '' ? mb_substr($workshop, 0, 255) : null,
            'maintenance_date' => date('Y-m-d', strtotime($date)),
            'km_at_maintenance' => $km,
            'cost' => $cost,
            'description' => $description,
            'user_id' => (int) ($_SESSION['user_id'] ?? 0),
            'hours_at_maintenance' => $hours,
            'notes' => $notes !== '' ? $notes : null,
        ]);

        Security::audit('maintenance_created', 'vehicle_maintenance', $id, [
            'vehicle_id' => $vehicleId,
            'type' => $type,
            'cost' => $cost,
        ]);

        unset($_SESSION['old_input']);
        $_SESSION['flash_success'] = 'Manutenção registrada com sucesso!';
        $this->redirect('/motorista/manutencoes');
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
