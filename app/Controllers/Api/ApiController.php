<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\BaseController;

class ApiController extends BaseController
{
    // ========================================
    // PÚBLICO
    // ========================================

    /**
     * GET /api/status
     * Status do sistema (ping).
     */
    public function status(): void
    {
        $this->json([
            'status' => 'online',
            'app' => $this->config('app')['name'] ?? 'Hidrossolo',
            'version' => '1.0.0',
            'timestamp' => date('c'),
            'timezone' => 'America/Sao_Paulo',
        ]);
    }

    /**
     * POST /api/contato
     * Envia formulário de contato público.
     */
    public function enviarContato(): void
    {
        // Aceitar JSON ou form data
        $input = $this->getJsonInput() ?: $_POST;

        $validator = new \App\Core\Validator($input);
        $rules = [
            'name' => 'required|min:3|max:255',
            'email' => 'required|email',
            'phone' => 'max:20',
            'message' => 'required|min:10|max:5000',
        ];

        if (!$validator->validate($rules)) {
            $this->json(['error' => 'Dados inválidos', 'errors' => $validator->errors()], 422);
            return;
        }

        $db = $this->db();
        $db->insert('contacts', [
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'] ?? null,
            'message' => $input['message'],
            'status' => 'new',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        $this->json(['success' => true, 'message' => 'Mensagem enviada com sucesso!'], 201);
    }

    /**
     * Parse JSON body da requisição.
     */
    private function getJsonInput(): ?array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            return is_array($data) ? $data : null;
        }
        return null;
    }

    // ========================================
    // AUTENTICADO
    // ========================================

    /**
     * GET /api/dashboard
     * Estatísticas gerais.
     */
    public function dashboard(): void
    {
        $db = $this->db();

        $this->json([
            'contatos_novos' => (int) ($db->fetch("SELECT COUNT(*) as c FROM contacts WHERE status='new'")['c'] ?? 0),
            'contratos_ativos' => (int) ($db->fetch("SELECT COUNT(*) as c FROM contracts WHERE status='active'")['c'] ?? 0),
            'veiculos_ativos' => (int) ($db->fetch("SELECT COUNT(*) as c FROM vehicles WHERE status='active'")['c'] ?? 0),
            'servicos_publicados' => (int) ($db->fetch("SELECT COUNT(*) as c FROM services WHERE active=1")['c'] ?? 0),
            'posts_publicados' => (int) ($db->fetch("SELECT COUNT(*) as c FROM posts WHERE status='published'")['c'] ?? 0),
            'paginas_publicadas' => (int) ($db->fetch("SELECT COUNT(*) as c FROM pages WHERE status='published'")['c'] ?? 0),
            'ultimos_contatos' => $db->fetchAll("SELECT id, name, email, status, created_at FROM contacts ORDER BY created_at DESC LIMIT 5"),
            'contratos_vencendo' => $db->fetchAll("SELECT id, contract_number, client_name, end_date FROM contracts WHERE status='active' AND end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY end_date ASC LIMIT 5"),
        ]);
    }

    /**
     * GET /api/servicos
     * Lista serviços.
     */
    public function servicos(): void
    {
        $db = $this->db();
        $rows = $db->fetchAll("SELECT * FROM services WHERE active = 1 ORDER BY sort_order ASC");
        $this->json($rows);
    }

    /**
     * GET /api/servicos/{id}
     */
    public function servicoDetalhe(int $id): void
    {
        $db = $this->db();
        $row = $db->fetch("SELECT * FROM services WHERE id = ?", [$id]);
        if (!$row) {
            $this->json(['error' => 'Serviço não encontrado'], 404);
            return;
        }
        $row['images'] = $db->fetchAll("SELECT * FROM service_images WHERE service_id = ? ORDER BY sort_order", [$id]);
        $this->json($row);
    }

    /**
     * GET /api/contatos
     * Lista mensagens de contato.
     */
    public function contatos(): void
    {
        $db = $this->db();
        $status = $_GET['status'] ?? null;
        $sql = "SELECT * FROM contacts";
        $params = [];

        if ($status) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC LIMIT 50";
        $this->json($db->fetchAll($sql, $params));
    }

    /**
     * GET /api/contatos/{id}
     */
    public function contatoDetalhe(int $id): void
    {
        $db = $this->db();
        $row = $db->fetch("SELECT * FROM contacts WHERE id = ?", [$id]);
        if (!$row) {
            $this->json(['error' => 'Contato não encontrado'], 404);
            return;
        }
        $this->json($row);
    }

    /**
     * GET /api/frota
     * Lista veículos.
     */
    public function frota(): void
    {
        $db = $this->db();
        $status = $_GET['status'] ?? null;
        $sql = "SELECT * FROM vehicles";
        $params = [];

        if ($status) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY plate ASC";
        $this->json($db->fetchAll($sql, $params));
    }

    /**
     * GET /api/frota/{id}
     */
    public function veiculoDetalhe(int $id): void
    {
        $db = $this->db();
        $row = $db->fetch("SELECT * FROM vehicles WHERE id = ?", [$id]);
        if (!$row) {
            $this->json(['error' => 'Veículo não encontrado'], 404);
            return;
        }
        $this->json($row);
    }

    /**
     * GET /api/frota/{id}/manutencoes
     */
    public function veiculoManutencoes(int $id): void
    {
        $db = $this->db();
        $this->json($db->fetchAll(
            "SELECT * FROM vehicle_maintenance WHERE vehicle_id = ? ORDER BY maintenance_date DESC",
            [$id]
        ));
    }

    /**
     * GET /api/frota/{id}/abastecimentos
     */
    public function veiculoAbastecimentos(int $id): void
    {
        $db = $this->db();
        $this->json($db->fetchAll(
            "SELECT * FROM vehicle_fuel WHERE vehicle_id = ? ORDER BY fuel_date DESC LIMIT 100",
            [$id]
        ));
    }

    /**
     * GET /api/contratos
     * Lista contratos.
     */
    public function contratos(): void
    {
        $db = $this->db();
        $status = $_GET['status'] ?? null;
        $sql = "SELECT * FROM contracts";
        $params = [];

        if ($status) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC";
        $this->json($db->fetchAll($sql, $params));
    }

    /**
     * GET /api/contratos/{id}
     */
    public function contratoDetalhe(int $id): void
    {
        $db = $this->db();
        $row = $db->fetch("SELECT * FROM contracts WHERE id = ?", [$id]);
        if (!$row) {
            $this->json(['error' => 'Contrato não encontrado'], 404);
            return;
        }
        $row['files'] = $db->fetchAll("SELECT * FROM contract_files WHERE contract_id = ?", [$id]);
        $this->json($row);
    }

    /**
     * GET /api/paginas
     * Lista páginas publicadas.
     */
    public function paginas(): void
    {
        $db = $this->db();
        $this->json($db->fetchAll(
            "SELECT id, title, slug, excerpt, meta_title, meta_description, status, created_at FROM pages ORDER BY sort_order ASC"
        ));
    }

    /**
     * GET /api/paginas/{slug}
     */
    public function paginaDetalhe(string $slug): void
    {
        $db = $this->db();
        $row = $db->fetch("SELECT * FROM pages WHERE slug = ?", [$slug]);
        if (!$row) {
            $this->json(['error' => 'Página não encontrada'], 404);
            return;
        }
        $row['sections'] = $db->fetchAll(
            "SELECT * FROM page_contents WHERE page_id = ? ORDER BY sort_order",
            [$row['id']]
        );
        $this->json($row);
    }

    /**
     * GET /api/blog
     * Lista posts do blog.
     */
    public function blog(): void
    {
        $db = $this->db();
        $this->json($db->fetchAll(
            "SELECT id, title, slug, excerpt, featured_image, status, published_at, created_at FROM posts WHERE status = 'published' ORDER BY published_at DESC"
        ));
    }

    /**
     * GET /api/blog/{slug}
     */
    public function postDetalhe(string $slug): void
    {
        $db = $this->db();
        $row = $db->fetch("SELECT * FROM posts WHERE slug = ? AND status = 'published'", [$slug]);
        if (!$row) {
            $this->json(['error' => 'Post não encontrado'], 404);
            return;
        }
        $this->json($row);
    }

    /**
     * GET /api/midia
     * Lista arquivos de mídia.
     */
    public function midia(): void
    {
        $db = $this->db();
        $category = $_GET['category'] ?? null;
        $sql = "SELECT id, filename, original_name, mime_type, file_size, file_path, thumbnail_path, alt_text, category, created_at FROM media_library";
        $params = [];

        if ($category) {
            $sql .= " WHERE category = ?";
            $params[] = $category;
        }

        $sql .= " ORDER BY created_at DESC LIMIT 100";
        $this->json($db->fetchAll($sql, $params));
    }

    /**
     * GET /api/configuracoes
     * Configurações do site (contato, empresa, etc).
     */
    public function configuracoes(): void
    {
        $db = $this->db();
        $settings = $db->fetchAll("SELECT `key`, `value`, `group` FROM site_settings");

        $grouped = [];
        foreach ($settings as $s) {
            $grouped[$s['group']][$s['key']] = $s['value'];
        }

        $this->json([
            'company' => $this->config('company'),
            'seo' => $this->config('seo'),
            'settings' => $grouped,
        ]);
    }
}
