<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;

class ServicosAdminController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();
        $servicos = $db->fetchAll("SELECT * FROM services ORDER BY sort_order");

        echo $this->view('admin.servicos.index', [
            'title' => 'Gerenciar Serviços',
            'servicos' => $servicos,
            'config' => $this->config('company'),
        ]);
    }

    public function create(): void
    {
        echo $this->view('admin.servicos.form', [
            'title' => 'Novo Serviço',
            'servico' => null,
            'config' => $this->config('company'),
        ]);
    }

    public function store(): void
    {
        $data = $this->validate([
            'title' => 'required|min:3|max:255',
            'description' => 'required|min:10',
        ]);

        $slug = $this->slugify($data['title']);

        $this->db()->insert('services', [
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'],
            'content' => $_POST['content'] ?? '',
            'icon' => $_POST['icon'] ?? '',
            'featured_image' => $_POST['featured_image'] ?? '',
            'meta_title' => $_POST['meta_title'] ?? '',
            'meta_description' => $_POST['meta_description'] ?? '',
            'active' => isset($_POST['active']) ? 1 : 0,
            'highlight' => isset($_POST['highlight']) ? 1 : 0,
        ]);

        $this->logAudit('create', 'service');

        $_SESSION['flash_success'] = 'Serviço cadastrado com sucesso!';
        $this->redirect('/admin/servicos');
    }

    public function edit(string $id): void
    {
        $servico = $this->db()->fetch("SELECT * FROM services WHERE id = ?", [$id]);

        if (!$servico) {
            $_SESSION['flash_error'] = 'Serviço não encontrado.';
            $this->redirect('/admin/servicos');
        }

        $galeria = $this->db()->fetchAll(
            "SELECT * FROM service_images WHERE service_id = ? ORDER BY sort_order",
            [$id]
        );

        echo $this->view('admin.servicos.form', [
            'title' => 'Editar Serviço',
            'servico' => $servico,
            'galeria' => $galeria,
            'config' => $this->config('company'),
        ]);
    }

    public function update(string $id): void
    {
        $data = $this->validate([
            'title' => 'required|min:3|max:255',
            'description' => 'required|min:10',
        ]);

        $this->db()->update('services', [
            'title' => $data['title'],
            'description' => $data['description'],
            'content' => $_POST['content'] ?? '',
            'icon' => $_POST['icon'] ?? '',
            'featured_image' => $_POST['featured_image'] ?? '',
            'meta_title' => $_POST['meta_title'] ?? '',
            'meta_description' => $_POST['meta_description'] ?? '',
            'active' => isset($_POST['active']) ? 1 : 0,
            'highlight' => isset($_POST['highlight']) ? 1 : 0,
        ], 'id = ?', [$id]);

        $this->logAudit('update', 'service', (int)$id);

        $_SESSION['flash_success'] = 'Serviço atualizado com sucesso!';
        $this->redirect('/admin/servicos');
    }

    public function delete(string $id): void
    {
        $this->db()->delete('services', 'id = ?', [$id]);
        $this->logAudit('delete', 'service', (int)$id);

        $_SESSION['flash_success'] = 'Serviço excluído com sucesso!';
        $this->redirect('/admin/servicos');
    }

    private function slugify(string $text): string
    {
        $text = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
        $text = preg_replace('/\s+/', '-', trim($text));
        $text = strtolower($text);

        // Garantir unicidade
        $db = $this->db();
        $original = $text;
        $counter = 1;
        while ($db->fetch("SELECT id FROM services WHERE slug = ?", [$text])) {
            $text = $original . '-' . $counter++;
        }

        return $text;
    }

    private function logAudit(string $action, string $entity, ?int $entityId = null): void
    {
        $this->db()->insert('audit_logs', [
            'user_id' => $_SESSION['user_id'] ?? null,
            'action' => $action,
            'entity_type' => $entity,
            'entity_id' => $entityId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}
