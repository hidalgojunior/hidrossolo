<?php

declare(strict_types=1);

namespace App\Models;

class Page extends Model
{
    protected string $table = 'pages';
    protected array $fillable = [
        'title', 'slug', 'content', 'excerpt',
        'meta_title', 'meta_description', 'meta_keywords',
        'featured_image', 'status', 'show_in_menu',
        'sort_order', 'created_by',
    ];

    /**
     * Busca por slug.
     */
    public static function findBySlug(string $slug): ?static
    {
        return self::findBy('slug', $slug);
    }

    /**
     * Busca páginas publicadas.
     */
    public static function published(): array
    {
        return self::findAllBy('status', 'published', 'sort_order ASC');
    }

    /**
     * Busca páginas no menu.
     */
    public static function inMenu(): array
    {
        return self::findAllBy('show_in_menu', 1, 'sort_order ASC');
    }

    /**
     * Retorna os conteúdos da página (seções).
     */
    public function contents(): array
    {
        return PageContent::findAllBy('page_id', $this->attributes['id'], 'sort_order ASC');
    }

    /**
     * Retorna conteúdo de uma seção específica.
     */
    public function contentBySection(string $section): ?PageContent
    {
        return PageContent::findByPageAndSection($this->attributes['id'], $section);
    }
}
