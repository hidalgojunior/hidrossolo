<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;

class AbastecimentosController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();
        $veiculoId = $_GET['veiculo_id'] ?? null;

        $where = '';
        $params = [];
        if ($veiculoId) {
            $where = 'WHERE vf.vehicle_id = ?';
            $params = [$veiculoId];
        }

        $abastecimentos = $db->fetchAll(
            "SELECT vf.*, v.plate, v.brand, v.model
             FROM vehicle_fuel vf
             JOIN vehicles v ON v.id = vf.vehicle_id
             {$where}
             ORDER BY vf.fuel_date DESC
             LIMIT 100",
            $params
        );

        $veiculos = $db->fetchAll("SELECT id, plate, brand, model FROM vehicles WHERE status != 'inactive' ORDER BY plate");

        echo $this->view('admin.abastecimentos.index', [
            'title' => 'Controle de Abastecimento',
            'abastecimentos' => $abastecimentos,
            'veiculos' => $veiculos,
            'veiculoId' => $veiculoId,
            'config' => $this->config('company'),
        ]);
    }

    public function create(): void
    {
        $veiculos = $this->db()->fetchAll("SELECT id, plate, brand, model FROM vehicles WHERE status != 'inactive' ORDER BY plate");
        $veiculoId = $_GET['veiculo_id'] ?? null;

        echo $this->view('admin.abastecimentos.form', [
            'title' => 'Novo Abastecimento',
            'abastecimento' => null,
            'veiculos' => $veiculos,
            'veiculoId' => $veiculoId,
            'config' => $this->config('company'),
        ]);
    }

    public function store(): void
    {
        $data = $this->validate([
            'vehicle_id' => 'required',
            'fuel_date' => 'required',
            'liters' => 'required',
            'cost' => 'required',
        ]);

        $liters = str_replace(',', '.', $data['liters']);
        $cost = str_replace(['.', ','], ['', '.'], $data['cost']);

        $this->db()->insert('vehicle_fuel', [
            'vehicle_id' => (int)$data['vehicle_id'],
            'fuel_date' => $data['fuel_date'],
            'liters' => (float)$liters,
            'cost' => (float)$cost,
            'km_at_refuel' => (int)($_POST['km_at_refuel'] ?? 0),
        ]);

        // Atualizar KM do veículo
        if (!empty($_POST['km_at_refuel'])) {
            $this->db()->update('vehicles',
                ['current_km' => (int)$_POST['km_at_refuel']],
                'id = ?',
                [(int)$data['vehicle_id']]
            );
        }

        $_SESSION['flash_success'] = 'Abastecimento registrado com sucesso!';
        $this->redirect('/admin/abastecimentos?veiculo_id=' . $data['vehicle_id']);
    }
}
