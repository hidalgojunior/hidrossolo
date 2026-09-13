<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;

class ContratosController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();
        $contratos = $db->fetchAll(
            "SELECT * FROM contracts ORDER BY created_at DESC"
        );

        // Contratos vencendo em 30 dias
        $vencendo = $db->fetchAll(
            "SELECT * FROM contracts
             WHERE status = 'active' AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
             ORDER BY end_date ASC"
        );

        echo $this->view('admin.contratos.index', [
            'title' => 'Gestão de Contratos',
            'contratos' => $contratos,
            'vencendo' => $vencendo,
            'config' => $this->config('company'),
        ]);
    }

    public function create(): void
    {
        echo $this->view('admin.contratos.form', [
            'title' => 'Novo Contrato',
            'contrato' => null,
            'config' => $this->config('company'),
        ]);
    }

    public function store(): void
    {
        $data = $this->validate([
            'contract_number' => 'required|max:100',
            'client_name' => 'required|max:255',
            'start_date' => 'required',
            'end_date' => 'required',
            'value' => 'required',
        ]);

        $value = str_replace(['.', ','], ['', '.'], $data['value']);

        $this->db()->insert('contracts', [
            'contract_number' => $data['contract_number'],
            'client_name' => $data['client_name'],
            'responsible' => $_POST['responsible'] ?? '',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'value' => (float)$value,
            'status' => $_POST['status'] ?? 'active',
            'notes' => $_POST['notes'] ?? '',
        ]);

        $_SESSION['flash_success'] = 'Contrato cadastrado com sucesso!';
        $this->redirect('/admin/contratos');
    }
}
