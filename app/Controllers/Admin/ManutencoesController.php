<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;

class ManutencoesController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();
        $veiculoId = $_GET['veiculo_id'] ?? null;

        $where = '';
        $params = [];
        if ($veiculoId) {
            $where = 'WHERE vm.vehicle_id = ?';
            $params = [$veiculoId];
        }

        $manutencoes = $db->fetchAll(
            "SELECT vm.*, v.plate, v.brand, v.model
             FROM vehicle_maintenance vm
             JOIN vehicles v ON v.id = vm.vehicle_id
             {$where}
             ORDER BY vm.maintenance_date DESC
             LIMIT 50",
            $params
        );

        $veiculos = $db->fetchAll("SELECT id, plate, brand, model FROM vehicles WHERE status != 'inactive' ORDER BY plate");

        echo $this->view('admin.manutencoes.index', [
            'title' => 'Controle de Manutenções',
            'manutencoes' => $manutencoes,
            'veiculos' => $veiculos,
            'veiculoId' => $veiculoId,
            'config' => $this->config('company'),
        ]);
    }

    public function create(): void
    {
        $veiculos = $this->db()->fetchAll("SELECT id, plate, brand, model FROM vehicles WHERE status != 'inactive' ORDER BY plate");
        $veiculoId = $_GET['veiculo_id'] ?? null;

        echo $this->view('admin.manutencoes.form', [
            'title' => 'Nova Manutenção',
            'manutencao' => null,
            'veiculos' => $veiculos,
            'veiculoId' => $veiculoId,
            'config' => $this->config('company'),
        ]);
    }

    public function store(): void
    {
        $data = $this->validate([
            'vehicle_id' => 'required',
            'type' => 'required',
            'maintenance_date' => 'required',
            'cost' => 'required',
        ]);

        $cost = str_replace(['.', ','], ['', '.'], $data['cost']);

        $this->db()->insert('vehicle_maintenance', [
            'vehicle_id' => (int)$data['vehicle_id'],
            'type' => $data['type'],
            'workshop' => $_POST['workshop'] ?? '',
            'maintenance_date' => $data['maintenance_date'],
            'km_at_maintenance' => (int)($_POST['km_at_maintenance'] ?? 0),
            'cost' => (float)$cost,
            'description' => $_POST['description'] ?? '',
            'notes' => $_POST['notes'] ?? '',
        ]);

        // Atualizar KM do veículo
        if (!empty($_POST['km_at_maintenance'])) {
            $this->db()->update('vehicles',
                ['current_km' => (int)$_POST['km_at_maintenance']],
                'id = ?',
                [(int)$data['vehicle_id']]
            );
        }

        $_SESSION['flash_success'] = 'Manutenção registrada com sucesso!';
        $this->redirect('/admin/manutencoes?veiculo_id=' . $data['vehicle_id']);
    }

    public function edit(string $id): void
    {
        $manutencao = $this->db()->fetch(
            "SELECT vm.*, v.plate FROM vehicle_maintenance vm JOIN vehicles v ON v.id = vm.vehicle_id WHERE vm.id = ?",
            [$id]
        );

        if (!$manutencao) {
            $_SESSION['flash_error'] = 'Manutenção não encontrada.';
            $this->redirect('/admin/manutencoes');
        }

        $veiculos = $this->db()->fetchAll("SELECT id, plate, brand, model FROM vehicles ORDER BY plate");

        echo $this->view('admin.manutencoes.form', [
            'title' => 'Editar Manutenção',
            'manutencao' => $manutencao,
            'veiculos' => $veiculos,
            'veiculoId' => $manutencao['vehicle_id'],
            'config' => $this->config('company'),
        ]);
    }

    public function update(string $id): void
    {
        $data = $this->validate([
            'vehicle_id' => 'required',
            'type' => 'required',
            'maintenance_date' => 'required',
            'cost' => 'required',
        ]);

        $cost = str_replace(['.', ','], ['', '.'], $data['cost']);

        $this->db()->update('vehicle_maintenance', [
            'vehicle_id' => (int)$data['vehicle_id'],
            'type' => $data['type'],
            'workshop' => $_POST['workshop'] ?? '',
            'maintenance_date' => $data['maintenance_date'],
            'km_at_maintenance' => (int)($_POST['km_at_maintenance'] ?? 0),
            'cost' => (float)$cost,
            'description' => $_POST['description'] ?? '',
            'notes' => $_POST['notes'] ?? '',
        ], 'id = ?', [$id]);

        $_SESSION['flash_success'] = 'Manutenção atualizada com sucesso!';
        $this->redirect('/admin/manutencoes');
    }

    public function delete(string $id): void
    {
        $this->db()->delete('vehicle_maintenance', 'id = ?', [$id]);
        $_SESSION['flash_success'] = 'Manutenção excluída!';
        $this->redirect('/admin/manutencoes');
    }
}
