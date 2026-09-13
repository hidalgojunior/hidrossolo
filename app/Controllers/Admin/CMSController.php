<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;

class CMSController extends BaseController
{
    // ==================== HOME ====================

    public function home(): void
    {
        $db = $this->db();
        $page = $db->fetch("SELECT * FROM pages WHERE slug = 'home'");
        $sections = $page ? $db->fetchAll(
            "SELECT * FROM page_contents WHERE page_id = ? ORDER BY section, sort_order",
            [$page['id']]
        ) : [];

        echo $this->view('admin.cms.home', [
            'title' => 'Gerenciar Home',
            'page' => $page,
            'sections' => $sections,
            'config' => $this->config('company'),
        ]);
    }

    public function updateHome(): void
    {
        $db = $this->db();

        // Garantir página home
        $page = $db->fetch("SELECT id FROM pages WHERE slug = 'home'");
        $pageId = $page ? $page['id'] : $db->insert('pages', [
            'title' => 'Home', 'slug' => 'home', 'status' => 'published',
        ]);

        // Remover seções antigas e reinserir
        $db->delete('page_contents', 'page_id = ?', [$pageId]);

        // Hero
        $db->insert('page_contents', [
            'page_id' => $pageId, 'section' => 'hero',
            'title' => $_POST['hero_title'] ?? 'Soluções Completas em Poços Artesianos',
            'subtitle' => $_POST['hero_subtitle'] ?? '',
            'content' => $_POST['hero_cta_text'] ?? 'Solicitar Orçamento',
            'link_url' => $_POST['hero_cta_url'] ?? '/contato',
            'sort_order' => 0,
        ]);

        // Seção Serviços
        $db->insert('page_contents', [
            'page_id' => $pageId, 'section' => 'servicos_section',
            'title' => $_POST['servicos_title'] ?? 'Nossos Serviços',
            'subtitle' => $_POST['servicos_subtitle'] ?? '',
            'sort_order' => 0,
        ]);

        // Diferenciais
        $this->saveSectionItems($db, $pageId, 'diferenciais', $_POST['section_diferenciais'] ?? []);

        // Depoimentos
        $this->saveSectionItems($db, $pageId, 'depoimentos', $_POST['section_depoimentos'] ?? []);

        // Banners / Divulgação
        $this->saveSectionItems($db, $pageId, 'banners', $_POST['section_banners'] ?? []);

        // CTA
        $db->insert('page_contents', [
            'page_id' => $pageId, 'section' => 'cta',
            'title' => $_POST['cta_title'] ?? 'Precisa de um Poço Artesiano?',
            'subtitle' => $_POST['cta_subtitle'] ?? '',
            'content' => $_POST['cta_btn_text'] ?? 'Solicitar Orçamento',
            'link_url' => $_POST['cta_btn_url'] ?? '/contato',
            'sort_order' => 0,
        ]);

        $_SESSION['flash_success'] = 'Home atualizada com sucesso!';
        $this->redirect('/admin/cms/home');
    }

    // ==================== EMPRESA ====================

    public function empresa(): void
    {
        $db = $this->db();
        $page = $db->fetch("SELECT * FROM pages WHERE slug = 'empresa'");
        $sections = $page ? $db->fetchAll(
            "SELECT * FROM page_contents WHERE page_id = ? ORDER BY sort_order",
            [$page['id']]
        ) : [];

        // Se não houver dados no banco, fornecer os valores padrão (mesmos do site público)
        if (!$page) {
            $page = [
                'content' => '<p>A <strong>Hidrossolo Poços Artesianos</strong> atua há mais de 20 anos no mercado de perfuração de poços artesianos, oferecendo soluções completas para captação de água subterrânea em Marília e região.</p><p>Com equipe técnica altamente qualificada e equipamentos modernos, realizamos desde o estudo geológico inicial até a instalação completa do sistema de bombeamento, sempre seguindo as normas técnicas e ambientais vigentes.</p>',
                'featured_image' => '/assets/images/empresa.jpg',
                'meta_title' => 'Empresa - Hidrossolo Poços Artesianos',
                'meta_description' => 'Conheça a história, missão, visão e valores da Hidrossolo Poços Artesianos.',
            ];
        }

        if (empty($sections)) {
            $sections = [
                ['section' => 'missao', 'content' => 'Oferecer soluções sustentáveis em captação de água subterrânea, garantindo qualidade, segurança e satisfação dos nossos clientes.'],
                ['section' => 'visao', 'content' => 'Ser referência regional em perfuração de poços artesianos, reconhecida pela excelência técnica e responsabilidade ambiental.'],
                ['section' => 'valores', 'content' => 'Ética, transparência, compromisso com o meio ambiente, inovação constante e respeito às pessoas.'],
            ];
        }

        echo $this->view('admin.cms.empresa', [
            'title' => 'Gerenciar Empresa',
            'page' => $page,
            'sections' => $sections,
            'config' => $this->config('company'),
        ]);
    }

    public function updateEmpresa(): void
    {
        $db = $this->db();

        $page = $db->fetch("SELECT id FROM pages WHERE slug = 'empresa'");
        if (!$page) {
            $pageId = $db->insert('pages', [
                'title' => 'Empresa', 'slug' => 'empresa',
                'content' => $_POST['content'] ?? '',
                'meta_title' => $_POST['meta_title'] ?? '',
                'meta_description' => $_POST['meta_description'] ?? '',
                'featured_image' => $_POST['featured_image'] ?? '',
                'status' => 'published',
            ]);
        } else {
            $pageId = $page['id'];
            $db->update('pages', [
                'content' => $_POST['content'] ?? '',
                'meta_title' => $_POST['meta_title'] ?? '',
                'meta_description' => $_POST['meta_description'] ?? '',
                'featured_image' => $_POST['featured_image'] ?? '',
            ], 'id = ?', [$pageId]);
        }

        // Salvar missão, visão, valores como sections
        $db->delete('page_contents', 'page_id = ?', [$pageId]);
        foreach (['missao', 'visao', 'valores'] as $section) {
            if (!empty($_POST[$section])) {
                $db->insert('page_contents', [
                    'page_id' => $pageId, 'section' => $section,
                    'content' => $_POST[$section],
                    'sort_order' => 0,
                ]);
            }
        }

        $_SESSION['flash_success'] = 'Página Empresa atualizada!';
        $this->redirect('/admin/cms/empresa');
    }

    // ==================== CONTATO ====================

    public function contato(): void
    {
        $db = $this->db();
        $settings = $db->fetchAll("SELECT * FROM site_settings WHERE `group` = 'contato'");
        $settingsMap = [];
        foreach ($settings as $s) {
            $settingsMap[$s['key']] = $s['value'];
        }

        echo $this->view('admin.cms.contato', [
            'title' => 'Gerenciar Contato',
            'settings' => $settingsMap,
            'config' => $this->config('company'),
        ]);
    }

    public function updateContato(): void
    {
        $db = $this->db();
        $fields = ['address', 'phone', 'whatsapp', 'email', 'working_hours', 'form_title', 'form_text', 'site_logo'];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $existing = $db->fetch("SELECT id FROM site_settings WHERE `key` = ?", [$field]);
                if ($existing) {
                    $db->update('site_settings', ['value' => $_POST[$field]], '`key` = ?', [$field]);
                } else {
                    $db->insert('site_settings', [
                        'key' => $field, 'value' => $_POST[$field], 'group' => 'contato',
                    ]);
                }
            }
        }

        $_SESSION['flash_success'] = 'Página Contato atualizada!';
        $this->redirect('/admin/cms/contato');
    }

    // ==================== LGPD ====================

    public function lgpd(): void
    {
        $db = $this->db();
        $pages = $db->fetchAll("SELECT * FROM pages WHERE slug IN ('politica-privacidade', 'politica-cookies', 'termos-uso')");

        $pagesMap = [];
        foreach ($pages as $p) {
            $pagesMap[$p['slug']] = $p;
        }

        $settings = $db->fetchAll("SELECT * FROM site_settings WHERE `group` = 'lgpd'");
        $settingsMap = [];
        foreach ($settings as $s) {
            $settingsMap[$s['key']] = $s['value'];
        }

        echo $this->view('admin.cms.lgpd', [
            'title' => 'LGPD - Políticas Legais',
            'pages' => $pagesMap,
            'settings' => $settingsMap,
            'config' => $this->config('company'),
        ]);
    }

    public function updateLgpd(): void
    {
        try {
            $db = $this->db();

            // Salvar páginas legais
            $slugs = [
                'politica-privacidade' => 'Política de Privacidade',
                'politica-cookies' => 'Política de Cookies',
                'termos-uso' => 'Termos de Uso',
            ];

            foreach ($slugs as $slug => $title) {
                $existing = $db->fetch("SELECT id FROM pages WHERE slug = ?", [$slug]);
                $content = $_POST["content_{$slug}"] ?? '';

                if ($existing) {
                    $db->update('pages', ['content' => $content, 'status' => 'published'], 'id = ?', [$existing['id']]);
                } else {
                    $db->insert('pages', [
                        'title' => $title, 'slug' => $slug,
                        'content' => $content, 'status' => 'published',
                    ]);
                }
            }

            // Salvar configurações LGPD
            $lgpdFields = ['cookie_consent_enabled', 'cookie_consent_text', 'lgpd_contact_email', 'maintenance_mode', 'maintenance_message'];
            foreach ($lgpdFields as $field) {
                if (isset($_POST[$field])) {
                    $existing = $db->fetch("SELECT id FROM site_settings WHERE `key` = ?", [$field]);
                    if ($existing) {
                        $db->update('site_settings', ['value' => $_POST[$field]], '`key` = ?', [$field]);
                    } else {
                        $db->insert('site_settings', ['key' => $field, 'value' => $_POST[$field], 'group' => 'lgpd']);
                    }
                }
            }

            $_SESSION['flash_success'] = 'Configurações LGPD atualizadas!';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Erro ao salvar: ' . $e->getMessage();
        }
        $this->redirect('/admin/cms/lgpd');
    }

    // ==================== HELPERS ====================

    private function saveSectionItems($db, int $pageId, string $section, array $items): void
    {
        foreach ($items as $index => $item) {
            if (!empty($item['title']) || !empty($item['content'])) {
                $db->insert('page_contents', [
                    'page_id' => $pageId,
                    'section' => $section,
                    'title' => $item['title'] ?? '',
                    'subtitle' => $item['subtitle'] ?? '',
                    'content' => $item['content'] ?? '',
                    'image' => $item['image'] ?? '',
                    'link_url' => $item['link_url'] ?? '',
                    'link_text' => $item['link_text'] ?? '',
                    'sort_order' => $index,
                ]);
            }
        }
    }
}
