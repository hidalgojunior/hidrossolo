<?php

declare(strict_types=1);

namespace App\Models;

class Service extends Model
{
    protected string $table = 'services';
    protected array $fillable = [
        'title', 'slug', 'description', 'content', 'icon',
        'featured_image', 'meta_title', 'meta_description', 'meta_keywords',
        'active', 'highlight', 'sort_order',
    ];

    /**
     * Busca por slug.
     */
    public static function findBySlug(string $slug): ?static
    {
        return self::findBy('slug', $slug);
    }

    /**
     * Serviços ativos.
     */
    public static function active(): array
    {
        return self::findAllBy('active', 1, 'sort_order ASC');
    }

    /**
     * Serviços em destaque.
     */
    public static function highlighted(int $limit = 6): array
    {
        $rows = self::query()->fetchAll(
            "SELECT * FROM services WHERE active = 1 AND highlight = 1 ORDER BY sort_order LIMIT ?",
            [$limit]
        );
        return array_map(fn($row) => new static($row), $rows);
    }
}
