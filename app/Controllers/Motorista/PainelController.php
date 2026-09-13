<?php

declare(strict_types=1);

namespace App\Controllers\Motorista;

use App\Core\BaseController;

/**
 * Área do motorista — apenas abastecimentos e manutenções.
 */
class PainelController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();
        $userId = (int) ($_SESSION['user_id'] ?? 0);

        $meusAbastecimentos = (int) ($db->fetch(
            "SELECT COUNT(*) AS c FROM vehicle_fuel WHERE user_id = ?",
            [$userId]
        )['c'] ?? 0);

        $minhasManutencoes = (int) ($db->fetch(
            "SELECT COUNT(*) AS c FROM vehicle_maintenance WHERE user_id = ?",
            [$userId]
        )['c'] ?? 0);

        $gastoMes = (float) ($db->fetch(
            "SELECT COALESCE(SUM(cost),0) AS t FROM vehicle_fuel
             WHERE user_id = ? AND fuel_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')",
            [$userId]
        )['t'] ?? 0);

        $ultimos = $db->fetchAll(
            "SELECT f.id, f.fuel_date, f.liters, f.cost, f.km_at_refuel, f.hours_at_refuel,
                    v.plate, v.brand, v.model, v.category, v.equipment_type
             FROM vehicle_fuel f
             JOIN vehicles v ON v.id = f.vehicle_id
             WHERE f.user_id = ?
             ORDER BY f.fuel_date DESC, f.id DESC
             LIMIT 5",
            [$userId]
        );

        echo $this->view('motorista.index', [
            'title' => 'Painel do Motorista',
            'meusAbastecimentos' => $meusAbastecimentos,
            'minhasManutencoes' => $minhasManutencoes,
            'gastoMes' => $gastoMes,
            'ultimos' => $ultimos,
        ]);
    }
}
