<?php

declare(strict_types=1);

use App\Core\Database;

/**
 * Migration: blocos da Home (ordem e visibilidade).
 *
 * Antes, os blocos da Home (hero, estatísticas, serviços, diferenciais, etc.)
 * eram fixos no código: não dava para excluir nem reordenar. Agora cada bloco
 * tem um registro em `page_blocks` com ordem e um flag de exibição,
 * controlados em Admin → Home → Blocos da Home.
 *
 * Data: 2026-09-13
 */
class CreatePageBlocks
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `page_blocks` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `page_id` INT UNSIGNED NOT NULL,
                `block` VARCHAR(60) NOT NULL,
                `sort_order` INT NOT NULL DEFAULT 0,
                `enabled` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_page_block` (`page_id`, `block`),
                KEY `idx_page_order` (`page_id`, `sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $page = $this->db->fetch("SELECT id FROM pages WHERE slug = 'home'");

        if (!$page) {
            // Sem a página home o CMS não teria onde se apoiar; cria agora.
            $pageId = $this->db->insert('pages', [
                'title' => 'Home',
                'slug' => 'home',
                'status' => 'published',
            ]);
        } else {
            $pageId = (int) $page['id'];
        }

        foreach (self::blocosPadrao() as $ordem => $bloco) {
            $existente = $this->db->fetch(
                'SELECT id FROM page_blocks WHERE page_id = ? AND block = ?',
                [$pageId, $bloco]
            );

            if ($existente) {
                continue;
            }

            $this->db->insert('page_blocks', [
                'page_id' => $pageId,
                'block' => $bloco,
                'sort_order' => $ordem + 1,
                'enabled' => 1,
            ]);
        }
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS `page_blocks`');
    }

    /**
     * Ordem padrão dos blocos da Home (mesma ordem em que já apareciam).
     *
     * @return array<int,string>
     */
    public static function blocosPadrao(): array
    {
        return [
            'hero',
            'estatisticas',
            'servicos',
            'diferenciais',
            'como_trabalhamos',
            'secoes',
            'depoimentos',
            'parceiros',
            'cta',
        ];
    }
}
