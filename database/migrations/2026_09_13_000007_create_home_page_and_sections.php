<?php

declare(strict_types=1);

use App\Core\Database;

/**
 * Migration: cria o registro da página "home" e as seções fixas iniciais.
 *
 * Sem o registro em `pages`, o CMS da Home não tinha a que se vincular: a tela
 * abria vazia e o site caía sempre nos valores de fallback da view.
 *
 * Data: 2026-09-13
 */
class CreateHomePageAndSections
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $page = $this->db->fetch("SELECT id FROM pages WHERE slug = 'home'");

        if ($page) {
            return;
        }

        $pageId = $this->db->insert('pages', [
            'title' => 'Home',
            'slug' => 'home',
            'status' => 'published',
        ]);

        $secoes = [
            [
                'section' => 'hero',
                'title' => 'Soluções completas em poços artesianos',
                'subtitle' => 'Perfuração, licenciamento, limpeza e manutenção com equipe especializada em Marília e região.',
                'content' => 'Solicitar Orçamento',
                'link_url' => '/orcamento',
                'sort_order' => 1,
            ],
            [
                'section' => 'servicos_section',
                'title' => 'Nossos Serviços',
                'subtitle' => 'Soluções completas para captação de água subterrânea, do estudo inicial à manutenção.',
                'sort_order' => 2,
            ],
            [
                'section' => 'cta',
                'title' => 'Precisa de um Poço Artesiano?',
                'subtitle' => 'Solicite um orçamento sem compromisso.',
                'content' => 'Solicitar Orçamento',
                'link_url' => '/orcamento',
                'sort_order' => 3,
            ],
        ];

        foreach ($secoes as $secao) {
            $this->db->insert('page_contents', array_merge($secao, ['page_id' => $pageId]));
        }
    }

    public function down(): void
    {
        $page = $this->db->fetch("SELECT id FROM pages WHERE slug = 'home'");

        if (!$page) {
            return;
        }

        $this->db->delete('page_contents', 'page_id = ?', [$page['id']]);
        $this->db->delete('pages', 'id = ?', [$page['id']]);
    }
}
