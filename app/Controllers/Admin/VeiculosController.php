<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;

class VeiculosController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();
        $veiculos = $db->fetchAll("SELECT * FROM vehicles ORDER BY created_at DESC");

        echo $this->view('admin.veiculos.index', [
            'title' => 'Controle de Frota',
            'veiculos' => $veiculos,
            'config' => $this->config('company'),
        ]);
    }

    public function create(): void
    {
        echo $this->view('admin.veiculos.form', [
            'title' => 'Novo Veículo',
            'veiculo' => null,
            'config' => $this->config('company'),
        ]);
    }

    public function store(): void
    {
        $data = $this->validate([
            'plate' => 'required|min:7|max:10',
            'brand' => 'required|max:100',
            'model' => 'required|max:100',
            'year' => 'required',
        ]);

        $this->db()->insert('vehicles', [
            'plate' => strtoupper($data['plate']),
            'brand' => $data['brand'],
            'model' => $data['model'],
            'year' => (int)$data['year'],
            'renavam' => $_POST['renavam'] ?? '',
            'chassis' => $_POST['chassis'] ?? '',
            'fuel_type' => $_POST['fuel_type'] ?? 'diesel',
            'current_km' => (int)($_POST['current_km'] ?? 0),
            'notes' => $_POST['notes'] ?? '',
        ]);

        $_SESSION['flash_success'] = 'Veículo cadastrado com sucesso!';
        $this->redirect('/admin/frota');
    }

    public function edit(string $id): void
    {
        $veiculo = $this->db()->fetch("SELECT * FROM vehicles WHERE id = ?", [$id]);

        if (!$veiculo) {
            $_SESSION['flash_error'] = 'Veículo não encontrado.';
            $this->redirect('/admin/frota');
        }

        // Manutenções
        $manutencoes = $this->db()->fetchAll(
            "SELECT * FROM vehicle_maintenance WHERE vehicle_id = ? ORDER BY maintenance_date DESC",
            [$id]
        );

        // Abastecimentos
        $abastecimentos = $this->db()->fetchAll(
            "SELECT * FROM vehicle_fuel WHERE vehicle_id = ? ORDER BY fuel_date DESC LIMIT 20",
            [$id]
        );

        echo $this->view('admin.veiculos.form', [
            'title' => 'Editar Veículo',
            'veiculo' => $veiculo,
            'manutencoes' => $manutencoes,
            'abastecimentos' => $abastecimentos,
            'config' => $this->config('company'),
        ]);
    }

    public function update(string $id): void
    {
        $data = $this->validate([
            'plate' => 'required|min:7|max:10',
            'brand' => 'required|max:100',
            'model' => 'required|max:100',
            'year' => 'required',
        ]);

        $this->db()->update('vehicles', [
            'plate' => strtoupper($data['plate']),
            'brand' => $data['brand'],
            'model' => $data['model'],
            'year' => (int)$data['year'],
            'renavam' => $_POST['renavam'] ?? '',
            'chassis' => $_POST['chassis'] ?? '',
            'fuel_type' => $_POST['fuel_type'] ?? 'diesel',
            'current_km' => (int)($_POST['current_km'] ?? 0),
            'status' => $_POST['status'] ?? 'active',
            'notes' => $_POST['notes'] ?? '',
        ], 'id = ?', [$id]);

        $_SESSION['flash_success'] = 'Veículo atualizado com sucesso!';
        $this->redirect('/admin/frota');
    }
}
