<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;

class BlogController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();

        $page = $_GET['page'] ?? 1;
        $perPage = 6;
        $offset = ((int)$page - 1) * $perPage;

        $posts = $db->fetchAll(
            "SELECT p.*, u.name as author_name
             FROM posts p
             LEFT JOIN users u ON u.id = p.author_id
             WHERE p.status = 'published'
             ORDER BY p.published_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );

        $total = $db->fetch(
            "SELECT COUNT(*) as total FROM posts WHERE status = 'published'"
        );

        echo $this->view('pages.blog', [
            'title' => 'Blog',
            'posts' => $posts,
            'total' => $total['total'] ?? 0,
            'page' => (int)$page,
            'perPage' => $perPage,
            'config' => $this->config('company'),
            'seo' => [
                'title' => 'Blog - Hidrossolo Poços Artesianos',
                'description' => 'Artigos e novidades sobre poços artesianos, manutenção e sustentabilidade hídrica.',
            ],
        ]);
    }

    public function detalhe(string $slug): void
    {
        $db = $this->db();

        $post = $db->fetch(
            "SELECT p.*, u.name as author_name
             FROM posts p
             LEFT JOIN users u ON u.id = p.author_id
             WHERE p.slug = ? AND p.status = 'published'",
            [$slug]
        );

        if (!$post) {
            http_response_code(404);
            echo $this->view('errors.404');
            return;
        }

        $recentes = $db->fetchAll(
            "SELECT * FROM posts WHERE status = 'published' AND id != ? ORDER BY published_at DESC LIMIT 3",
            [$post['id']]
        );

        echo $this->view('pages.blog-post', [
            'title' => $post['title'],
            'post' => $post,
            'recentes' => $recentes,
            'config' => $this->config('company'),
            'seo' => [
                'title' => $post['meta_title'] ?: $post['title'],
                'description' => $post['meta_description'] ?? $post['excerpt'],
            ],
        ]);
    }
}
