<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;

class ContatoController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();

        $page = $db->fetch(
            "SELECT * FROM pages WHERE slug = 'contato' AND status = 'published'"
        );

        echo $this->view('pages.contato', [
            'title' => 'Fale Conosco',
            'page' => $page,
            'config' => $this->config('company'),
            'seo' => [
                'title' => 'Contato - Hidrossolo Poços Artesianos',
                'description' => 'Entre em contato com a Hidrossolo. Solicite seu orçamento para perfuração de poços artesianos.',
            ],
        ]);
    }

    public function enviar(): void
    {
        $data = $this->validate([
            'nome' => 'required|min:3|max:255',
            'email' => 'required|email',
            'telefone' => 'max:20',
            'mensagem' => 'required|min:10|max:2000',
        ]);

        $this->db()->insert('contacts', [
            'name' => $data['nome'],
            'email' => $data['email'],
            'phone' => $data['telefone'] ?? '',
            'message' => $data['mensagem'],
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        $_SESSION['flash_success'] = 'Mensagem enviada com sucesso! Entraremos em contato em breve.';
        $this->redirect('/obrigado');
    }

    public function obrigado(): void
    {
        echo $this->view('pages.obrigado', [
            'title' => 'Obrigado!',
            'config' => $this->config('company'),
            'seo' => ['title' => 'Obrigado pelo contato', 'description' => 'Recebemos sua mensagem!'],
        ]);
    }
}
