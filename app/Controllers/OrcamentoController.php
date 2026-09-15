<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Logger;
use App\Support\Orcamentos;

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

        $dataDesejada = trim((string) ($_POST['preferred_date'] ?? ''));

        // O campo aceita o formato brasileiro (dd/mm/aaaa); antes de gravar a
        // data e convertida para o formato do banco (aaaa-mm-dd).
        if ($dataDesejada !== '' && data_br_para_iso($dataDesejada) === null) {
            $_SESSION['flash_error'] = 'Data desejada inválida. Use o formato dd/mm/aaaa.';
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/orcamento');
        }

        try {
            Orcamentos::garantir($this->db());

            $this->db()->insert('orcamentos', [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'] ?? '',
                'service_type' => $_POST['service_type'],
                'description' => $_POST['description'],
                'address' => $_POST['address'] ?? '',
                'preferred_date' => data_br_para_iso($dataDesejada),
                'status' => 'novo',
            ]);
        } catch (\Throwable $e) {
            // Melhor avisar o visitante do que devolver erro 500 no formulario.
            Logger::error('Falha ao gravar solicitacao de orcamento', ['erro' => $e->getMessage()]);

            $_SESSION['flash_error'] = 'Não foi possível enviar sua solicitação agora. Tente novamente em instantes.';
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/orcamento');
        }

        $_SESSION['flash_success'] = 'Orçamento enviado! Entraremos em contato em breve.';
        $this->redirect('/obrigado');
    }
}
