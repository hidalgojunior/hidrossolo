<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;

class PaginasController extends BaseController
{
    public function show(string $slug): void
    {
        $db = $this->db();

        $page = $db->fetch(
            "SELECT * FROM pages WHERE slug = ? AND status = 'published'",
            [$slug]
        );

        if (!$page) {
            http_response_code(404);
            echo $this->view('errors.404');
            return;
        }

        echo $this->view('pages.dinamica', [
            'title' => $page['title'],
            'page' => $page,
            'config' => $this->config('company'),
            'seo' => [
                'title' => $page['meta_title'] ?: $page['title'],
                'description' => $page['meta_description'] ?? '',
            ],
        ]);
    }

    public function busca(): void
    {
        $q = trim($_GET['q'] ?? '');
        $results = ['pages' => [], 'services' => [], 'posts' => []];

        if (strlen($q) >= 2) {
            $db = $this->db();
            $like = "%{$q}%";
            $results['pages'] = $db->fetchAll("SELECT title, slug, excerpt, 'Página' as tipo FROM pages WHERE status='published' AND (title LIKE ? OR content LIKE ?) LIMIT 5", [$like, $like]);
            $results['services'] = $db->fetchAll("SELECT title, slug, description as excerpt, 'Serviço' as tipo FROM services WHERE active=1 AND (title LIKE ? OR description LIKE ?) LIMIT 5", [$like, $like]);
            $results['posts'] = $db->fetchAll("SELECT title, slug, excerpt, 'Blog' as tipo FROM posts WHERE status='published' AND (title LIKE ? OR content LIKE ?) LIMIT 5", [$like, $like]);
        }

        echo $this->view('pages.busca', [
            'title' => 'Busca: ' . htmlspecialchars($q),
            'q' => $q,
            'results' => $results,
            'config' => $this->config('company'),
            'seo' => ['title' => 'Busca', 'description' => 'Resultados da busca no site'],
        ]);
    }
}
