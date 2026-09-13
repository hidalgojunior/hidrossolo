<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;

class EmpresaController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();

        $page = $db->fetch(
            "SELECT * FROM pages WHERE slug = 'empresa' AND status = 'published'"
        );

        $sections = $db->fetchAll(
            "SELECT * FROM page_contents WHERE page_id = ? ORDER BY sort_order",
            [$page['id'] ?? 0]
        );

        $sectionContents = [];
        foreach ($sections as $s) {
            $sectionContents[$s['section']] = $s['content'];
        }

        echo $this->view('pages.empresa', [
            'title' => 'Sobre a Hidrossolo',
            'page' => $page,
            'sections' => $sections,
            'sectionContents' => $sectionContents,
            'config' => $this->config('company'),
            'seo' => [
                'title' => 'Empresa - Hidrossolo Poços Artesianos',
                'description' => 'Conheça a história, missão, visão e valores da Hidrossolo Poços Artesianos.',
            ],
        ]);
    }
}
