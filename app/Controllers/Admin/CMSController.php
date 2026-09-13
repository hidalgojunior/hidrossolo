<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Security;
use App\Support\HomeBlocks;

class CMSController extends BaseController
{
    /**
     * Chave usada em `page_contents.section` para as seções livres da Home.
     */
    private const SECAO_LIVRE = 'secao';

    // ==================== HOME ====================

    public function home(): void
    {
        $db = $this->db();
        $page = $db->fetch("SELECT * FROM pages WHERE slug = 'home'");
        $sections = $page ? $db->fetchAll(
            "SELECT * FROM page_contents WHERE page_id = ? ORDER BY section, sort_order",
            [$page['id']]
        ) : [];

        // Seções livres criadas pelo administrador (podem ser incluídas e removidas)
        $secoes = $page ? $db->fetchAll(
            "SELECT * FROM page_contents WHERE page_id = ? AND section = ? ORDER BY sort_order, id",
            [$page['id'], self::SECAO_LIVRE]
        ) : [];

        echo $this->view('admin.cms.home', [
            'title' => 'Gerenciar Home',
            'page' => $page,
            'sections' => $sections,
            'secoes' => $secoes,
            'blocos' => $page ? $this->blocos((int) $page['id']) : [],
            'catalogoBlocos' => HomeBlocks::catalogo(),
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

        // Remover apenas as seções fixas e reinserir.
        // As seções livres (SECAO_LIVRE) são preservadas, para que o
        // administrador possa incluir e excluir seções sem perdê-las ao salvar.
        $db->delete('page_contents', 'page_id = ? AND section <> ?', [$pageId, self::SECAO_LIVRE]);

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

        // Estatísticas (valor em `title`, rótulo em `subtitle`, ícone em `content`)
        $this->saveSectionItems($db, $pageId, 'estatisticas', $_POST['section_estatisticas'] ?? []);

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

    /**
     * Adiciona uma nova seção livre à Home.
     */
    public function storeSection(): void
    {
        $pageId = $this->pageId();

        $titulo = trim((string) ($_POST['title'] ?? ''));

        if ($titulo === '') {
            $_SESSION['flash_error'] = 'Informe o título da seção.';
            $this->redirect('/admin/cms/home');
        }

        $db = $this->db();

        $ordem = $db->fetch(
            "SELECT COALESCE(MAX(sort_order), 0) + 1 AS proxima FROM page_contents WHERE page_id = ? AND section = ?",
            [$pageId, self::SECAO_LIVRE]
        );

        $id = $db->insert('page_contents', [
            'page_id' => $pageId,
            'section' => self::SECAO_LIVRE,
            'title' => mb_substr($titulo, 0, 255),
            'subtitle' => $this->texto('subtitle', 255),
            'content' => $this->texto('content'),
            'image' => $this->texto('image', 500),
            'link_url' => $this->texto('link_url', 500),
            'link_text' => $this->texto('link_text', 255),
            'sort_order' => (int) ($ordem['proxima'] ?? 1),
        ]);

        Security::audit('home_section_created', 'page_contents', $id, ['page_id' => $pageId]);

        $_SESSION['flash_success'] = 'Seção adicionada à Home!';
        $this->redirect('/admin/cms/home');
    }

    /**
     * Atualiza uma seção livre da Home.
     */
    public function updateSection(string $id): void
    {
        $secao = $this->secao((int) $id);

        $this->db()->update('page_contents', [
            'title' => mb_substr(trim((string) ($_POST['title'] ?? $secao['title'])), 0, 255),
            'subtitle' => $this->texto('subtitle', 255),
            'content' => $this->texto('content'),
            'image' => $this->texto('image', 500),
            'link_url' => $this->texto('link_url', 500),
            'link_text' => $this->texto('link_text', 255),
            'sort_order' => (int) ($_POST['sort_order'] ?? $secao['sort_order']),
        ], 'id = ?', [(int) $id]);

        Security::audit('home_section_updated', 'page_contents', (int) $id);

        $_SESSION['flash_success'] = 'Seção atualizada!';
        $this->redirect('/admin/cms/home');
    }

    /**
     * Remove uma seção livre da Home.
     */
    public function deleteSection(string $id): void
    {
        $this->secao((int) $id);

        $this->db()->delete('page_contents', 'id = ?', [(int) $id]);

        Security::audit('home_section_deleted', 'page_contents', (int) $id);

        $_SESSION['flash_success'] = 'Seção removida da Home.';
        $this->redirect('/admin/cms/home');
    }

    /**
     * Move a seção para cima ou para baixo na ordem de exibição.
     */
    public function moveSection(string $id): void
    {
        $secao = $this->secao((int) $id);
        $direcao = ($_POST['direcao'] ?? 'cima') === 'baixo' ? 'baixo' : 'cima';
        $db = $this->db();

        $vizinho = $db->fetch(
            $direcao === 'cima'
                ? "SELECT * FROM page_contents WHERE page_id = ? AND section = ? AND (sort_order < ? OR (sort_order = ? AND id < ?)) ORDER BY sort_order DESC, id DESC LIMIT 1"
                : "SELECT * FROM page_contents WHERE page_id = ? AND section = ? AND (sort_order > ? OR (sort_order = ? AND id > ?)) ORDER BY sort_order ASC, id ASC LIMIT 1",
            [$secao['page_id'], self::SECAO_LIVRE, $secao['sort_order'], $secao['sort_order'], $secao['id']]
        );

        if ($vizinho) {
            $db->update('page_contents', ['sort_order' => $vizinho['sort_order']], 'id = ?', [(int) $secao['id']]);
            $db->update('page_contents', ['sort_order' => $secao['sort_order']], 'id = ?', [(int) $vizinho['id']]);
        }

        $this->redirect('/admin/cms/home');
    }

    /* ===================================================================== */

    /**
     * Blocos da Home: exibe/oculta um bloco.
     */
    public function toggleBlock(string $bloco): void
    {
        $registro = $this->bloco($bloco);

        $this->db()->update('page_blocks', [
            'enabled' => !empty($_POST['enabled']) ? 1 : 0,
        ], 'id = ?', [(int) $registro['id']]);

        Security::audit('home_block_toggled', 'page_blocks', (int) $registro['id'], [
            'block' => $bloco,
            'enabled' => !empty($_POST['enabled']) ? 1 : 0,
        ]);

        $_SESSION['flash_success'] = !empty($_POST['enabled'])
            ? 'Bloco “' . HomeBlocks::label($bloco) . '” agora aparece na Home.'
            : 'Bloco “' . HomeBlocks::label($bloco) . '” foi removido da Home.';

        $this->redirect('/admin/cms/home');
    }

    /**
     * Blocos da Home: move o bloco para cima ou para baixo.
     */
    public function moveBlock(string $bloco): void
    {
        $registro = $this->bloco($bloco);
        $direcao = ($_POST['direcao'] ?? 'cima') === 'baixo' ? 'baixo' : 'cima';
        $db = $this->db();

        $vizinho = $db->fetch(
            $direcao === 'cima'
                ? 'SELECT * FROM page_blocks WHERE page_id = ? AND (sort_order < ? OR (sort_order = ? AND id < ?)) ORDER BY sort_order DESC, id DESC LIMIT 1'
                : 'SELECT * FROM page_blocks WHERE page_id = ? AND (sort_order > ? OR (sort_order = ? AND id > ?)) ORDER BY sort_order ASC, id ASC LIMIT 1',
            [$registro['page_id'], $registro['sort_order'], $registro['sort_order'], $registro['id']]
        );

        if ($vizinho) {
            $db->update('page_blocks', ['sort_order' => $vizinho['sort_order']], 'id = ?', [(int) $registro['id']]);
            $db->update('page_blocks', ['sort_order' => $registro['sort_order']], 'id = ?', [(int) $vizinho['id']]);
        }

        $this->redirect('/admin/cms/home');
    }

    /**
     * Restaura a ordem e a exibição padrão dos blocos da Home.
     */
    public function resetBlocks(): void
    {
        $pageId = $this->pageId();
        $db = $this->db();

        foreach (HomeBlocks::PADRAO as $ordem => $bloco) {
            $existente = $db->fetch(
                'SELECT id FROM page_blocks WHERE page_id = ? AND block = ?',
                [$pageId, $bloco]
            );

            if ($existente) {
                $db->update('page_blocks', [
                    'sort_order' => $ordem + 1,
                    'enabled' => 1,
                ], 'id = ?', [(int) $existente['id']]);
            } else {
                $db->insert('page_blocks', [
                    'page_id' => $pageId,
                    'block' => $bloco,
                    'sort_order' => $ordem + 1,
                    'enabled' => 1,
                ]);
            }
        }

        Security::audit('home_blocks_reset', 'page_blocks', null, ['page_id' => $pageId]);

        $_SESSION['flash_success'] = 'Ordem e exibição dos blocos restauradas ao padrão.';
        $this->redirect('/admin/cms/home');
    }

    /* ===================================================================== */

    /**
     * Lista os blocos da Home na ordem configurada (auto-recupera se vazia).
     *
     * @return array<int,array<string,mixed>>
     */
    private function blocos(int $pageId): array
    {
        $db = $this->db();
        $blocos = $db->fetchAll(
            'SELECT * FROM page_blocks WHERE page_id = ? ORDER BY sort_order, id',
            [$pageId]
        );

        if ($blocos !== []) {
            return $blocos;
        }

        foreach (HomeBlocks::PADRAO as $ordem => $bloco) {
            $db->insert('page_blocks', [
                'page_id' => $pageId,
                'block' => $bloco,
                'sort_order' => $ordem + 1,
                'enabled' => 1,
            ]);
        }

        return $db->fetchAll(
            'SELECT * FROM page_blocks WHERE page_id = ? ORDER BY sort_order, id',
            [$pageId]
        );
    }

    /**
     * Carrega um bloco válido da Home.
     *
     * @return array<string,mixed>
     */
    private function bloco(string $chave): array
    {
        if (!array_key_exists($chave, HomeBlocks::catalogo())) {
            $_SESSION['flash_error'] = 'Bloco não encontrado.';
            $this->redirect('/admin/cms/home');
        }

        $pageId = $this->pageId();
        $this->blocos($pageId);

        $bloco = $this->db()->fetch(
            'SELECT * FROM page_blocks WHERE page_id = ? AND block = ?',
            [$pageId, $chave]
        );

        if (!$bloco) {
            $_SESSION['flash_error'] = 'Bloco não encontrado.';
            $this->redirect('/admin/cms/home');
        }

        return $bloco;
    }

    /**
     * Garante (e devolve) o id da página Home.
     */
    private function pageId(): int
    {
        $page = $this->db()->fetch("SELECT id FROM pages WHERE slug = 'home'");

        if ($page) {
            return (int) $page['id'];
        }

        return $this->db()->insert('pages', [
            'title' => 'Home',
            'slug' => 'home',
            'status' => 'published',
        ]);
    }

    /**
     * Carrega uma seção livre, garantindo que ela pertence à Home.
     *
     * @return array<string,mixed>
     */
    private function secao(int $id): array
    {
        $secao = $this->db()->fetch(
            'SELECT * FROM page_contents WHERE id = ? AND section = ?',
            [$id, self::SECAO_LIVRE]
        );

        if (!$secao) {
            $_SESSION['flash_error'] = 'Seção não encontrada.';
            $this->redirect('/admin/cms/home');
        }

        return $secao;
    }

    /**
     * Lê um campo de texto do formulário, respeitando o limite da coluna.
     */
    private function texto(string $campo, ?int $limite = null): ?string
    {
        $valor = trim((string) ($_POST[$campo] ?? ''));

        if ($valor === '') {
            return null;
        }

        return $limite !== null ? mb_substr($valor, 0, $limite) : $valor;
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
