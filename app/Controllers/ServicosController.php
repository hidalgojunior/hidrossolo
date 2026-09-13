<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;

class ServicosController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();

        $servicos = $db->fetchAll(
            "SELECT * FROM services WHERE active = 1 ORDER BY sort_order"
        );

        echo $this->view('pages.servicos', [
            'title' => 'Nossos Serviços',
            'servicos' => $servicos,
            'config' => $this->config('company'),
            'seo' => [
                'title' => 'Serviços - Hidrossolo Poços Artesianos',
                'description' => 'Perfuração de poços, licenciamento, outorgas, limpeza, manutenção e instalação de bombas.',
            ],
        ]);
    }

    public function detalhe(string $slug): void
    {
        $db = $this->db();

        $servico = $db->fetch(
            "SELECT * FROM services WHERE slug = ? AND active = 1",
            [$slug]
        );

        if (!$servico) {
            http_response_code(404);
            echo $this->view('errors.404');
            return;
        }

        // Serviço sem conteúdo visível: trata como página inexistente
        $temConteudo = trim((string) ($servico['content'] ?? '')) !== ''
            || trim((string) ($servico['description'] ?? '')) !== '';

        if (!$temConteudo) {
            http_response_code(404);
            echo $this->view('errors.404');
            return;
        }

        $galeria = $db->fetchAll(
            "SELECT * FROM service_images WHERE service_id = ? ORDER BY sort_order",
            [$servico['id']]
        );

        $outrosServicos = $db->fetchAll(
            "SELECT * FROM services WHERE active = 1 AND id != ? ORDER BY sort_order LIMIT 4",
            [$servico['id']]
        );

        echo $this->view('pages.servico-detalhe', [
            'title' => $servico['title'],
            'servico' => $servico,
            'galeria' => $galeria,
            'outros' => $outrosServicos,
            'config' => $this->config('company'),
            'seo' => [
                'title' => $servico['meta_title'] ?: $servico['title'] . ' - Hidrossolo',
                'description' => $servico['meta_description'] ?? $servico['description'],
            ],
        ]);
    }
}
