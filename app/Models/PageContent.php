<?php

declare(strict_types=1);

namespace App\Models;

class PageContent extends Model
{
    protected string $table = 'page_contents';
    protected array $fillable = [
        'page_id', 'section', 'title', 'subtitle',
        'content', 'image', 'link_url', 'link_text', 'sort_order',
    ];

    /**
     * Busca conteúdo por página e seção.
     */
    public static function findByPageAndSection(int $pageId, string $section): ?static
    {
        $row = self::query()->fetch(
            "SELECT * FROM page_contents WHERE page_id = ? AND section = ? LIMIT 1",
            [$pageId, $section]
        );
        return $row ? new static($row) : null;
    }

    /**
     * Busca todos os conteúdos de uma seção em uma página.
     */
    public static function findAllBySection(int $pageId, string $section): array
    {
        $rows = self::query()->fetchAll(
            "SELECT * FROM page_contents WHERE page_id = ? AND section = ? ORDER BY sort_order ASC",
            [$pageId, $section]
        );
        return array_map(fn($row) => new static($row), $rows);
    }

    /**
     * Retorna a página relacionada.
     */
    public function page(): ?Page
    {
        return Page::find($this->attributes['page_id']);
    }
}
