<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;

class OrcamentoController extends BaseController
{
    public function index(): void
    {
        echo $this->view('pages.orcamento', [
            'title' => 'Solicitar Orçamento',
            'config' => $this->config('company'),
            'seo' => ['title' => 'Solicitar Orçamento', 'description' => 'Solicite um orçamento sem compromisso para perfuração de poços artesianos.'],
        ]);
    }

    public function enviar(): void
    {
        $validator = new \App\Core\Validator($_POST);
        if (!$validator->validate([
            'name' => 'required|min:3',
            'email' => 'required|email',
            'phone' => 'max:20',
            'service_type' => 'required',
            'description' => 'required|min:10',
        ])) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/orcamento');
        }

        $db = $this->db();
        $db->insert('orcamentos', [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'] ?? '',
            'service_type' => $_POST['service_type'],
            'description' => $_POST['description'],
            'address' => $_POST['address'] ?? '',
            'preferred_date' => ($_POST['preferred_date'] ?? '') ?: null,
            'status' => 'novo',
        ]);

        $_SESSION['flash_success'] = 'Orçamento enviado! Entraremos em contato em breve.';
        $this->redirect('/obrigado');
    }
}
