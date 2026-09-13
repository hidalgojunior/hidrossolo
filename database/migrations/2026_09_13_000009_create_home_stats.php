<?php

declare(strict_types=1);

use App\Core\Database;

/**
 * Migration: cadastra as estatísticas da Home na tabela `page_contents`.
 *
 * Antes elas eram fixas no código da view (`$stats`), sem nenhum lugar no painel
 * para atualizar os números. Agora ficam em `page_contents` com
 * `section = 'estatisticas'` e são editáveis em Admin → Home → Estatísticas.
 *
 * Mapeamento dos campos:
 *  - title    => valor em destaque (ex.: "+20")
 *  - subtitle => rótulo (ex.: "Anos de experiência")
 *  - content  => ícone do Bootstrap Icons, sem o prefixo "bi-"
 *
 * Data: 2026-09-13
 */
class CreateHomeStats
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $page = $this->db->fetch("SELECT id FROM pages WHERE slug = 'home'");

        if (!$page) {
            $pageId = $this->db->insert('pages', [
                'title' => 'Home',
                'slug' => 'home',
                'status' => 'published',
            ]);
        } else {
            $pageId = (int) $page['id'];
        }

        $existentes = $this->db->fetch(
            "SELECT COUNT(*) AS c FROM page_contents WHERE page_id = ? AND section = 'estatisticas'",
            [$pageId]
        );

        if ((int) ($existentes['c'] ?? 0) > 0) {
            return;
        }

        foreach ($this->estatisticas() as $ordem => $item) {
            $this->db->insert('page_contents', [
                'page_id' => $pageId,
                'section' => 'estatisticas',
                'title' => $item['valor'],
                'subtitle' => $item['rotulo'],
                'content' => $item['icone'],
                'sort_order' => $ordem,
            ]);
        }
    }

    public function down(): void
    {
        $page = $this->db->fetch("SELECT id FROM pages WHERE slug = 'home'");

        if ($page) {
            $this->db->delete('page_contents', "page_id = ? AND section = 'estatisticas'", [$page['id']]);
        }
    }

    /**
     * Números que já apareciam na Home.
     *
     * @return array<int,array{valor:string,rotulo:string,icone:string}>
     */
    private function estatisticas(): array
    {
        return [
            ['valor' => '+20', 'rotulo' => 'Anos de experiência', 'icone' => 'calendar'],
            ['valor' => '+1.500', 'rotulo' => 'Poços perfurados', 'icone' => 'droplet'],
            ['valor' => '100%', 'rotulo' => 'Regularização ambiental', 'icone' => 'shield-check'],
            ['valor' => '+50', 'rotulo' => 'Municípios atendidos', 'icone' => 'geo-alt'],
        ];
    }
}
