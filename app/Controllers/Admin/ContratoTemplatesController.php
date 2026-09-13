<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Security;
use App\Support\ContractRenderer;

/**
 * Modelos de contrato com variáveis ({{cliente}}, {{valor}}...).
 */
class ContratoTemplatesController extends BaseController
{
    public function index(): void
    {
        $templates = $this->db()->fetchAll(
            "SELECT t.*, (SELECT COUNT(*) FROM contracts c WHERE c.template_id = t.id) AS usos
             FROM contract_templates t
             ORDER BY t.active DESC, t.name"
        );

        foreach ($templates as &$t) {
            $t['variaveis'] = ContractRenderer::variables((string) $t['content']);
        }
        unset($t);

        echo $this->view('admin.contratos.modelos', [
            'title' => 'Modelos de Contrato',
            'templates' => $templates,
            'config' => $this->config('company'),
        ]);
    }

    public function create(): void
    {
        echo $this->view('admin.contratos.modelos-form', [
            'title' => 'Novo Modelo de Contrato',
            'template' => null,
            'config' => $this->config('company'),
        ]);
    }

    public function store(): void
    {
        $data = $this->validate([
            'name' => 'required|min:3|max:255',
            'content' => 'required',
        ]);

        $id = $this->db()->insert('contract_templates', [
            'name' => mb_substr((string) $data['name'], 0, 255),
            'description' => $this->nullable($_POST['description'] ?? null, 500),
            'content' => (string) $data['content'],
            'variables' => json_encode(ContractRenderer::variables((string) $data['content']), JSON_UNESCAPED_UNICODE),
            'active' => isset($_POST['active']) ? 1 : 0,
            'created_by' => (int) ($_SESSION['user_id'] ?? 0),
        ]);

        Security::audit('contract_template_created', 'contract_templates', $id);

        $_SESSION['flash_success'] = 'Modelo criado com sucesso!';
        $this->redirect('/admin/contratos/modelos');
    }

    public function edit(string $id): void
    {
        $template = $this->db()->fetch("SELECT * FROM contract_templates WHERE id = ?", [(int) $id]);

        if (!$template) {
            $_SESSION['flash_error'] = 'Modelo não encontrado.';
            $this->redirect('/admin/contratos/modelos');
        }

        echo $this->view('admin.contratos.modelos-form', [
            'title' => 'Editar Modelo',
            'template' => $template,
            'config' => $this->config('company'),
        ]);
    }

    public function update(string $id): void
    {
        $db = $this->db();
        $template = $db->fetch("SELECT id FROM contract_templates WHERE id = ?", [(int) $id]);

        if (!$template) {
            $_SESSION['flash_error'] = 'Modelo não encontrado.';
            $this->redirect('/admin/contratos/modelos');
        }

        $data = $this->validate([
            'name' => 'required|min:3|max:255',
            'content' => 'required',
        ]);

        $db->update('contract_templates', [
            'name' => mb_substr((string) $data['name'], 0, 255),
            'description' => $this->nullable($_POST['description'] ?? null, 500),
            'content' => (string) $data['content'],
            'variables' => json_encode(ContractRenderer::variables((string) $data['content']), JSON_UNESCAPED_UNICODE),
            'active' => isset($_POST['active']) ? 1 : 0,
        ], 'id = ?', [(int) $id]);

        Security::audit('contract_template_updated', 'contract_templates', (int) $id);

        $_SESSION['flash_success'] = 'Modelo atualizado com sucesso!';
        $this->redirect('/admin/contratos/modelos');
    }

    public function delete(string $id): void
    {
        $db = $this->db();
        $usos = (int) ($db->fetch("SELECT COUNT(*) AS c FROM contracts WHERE template_id = ?", [(int) $id])['c'] ?? 0);

        if ($usos > 0) {
            $_SESSION['flash_error'] = "Este modelo está em uso por {$usos} contrato(s). Desative-o em vez de excluir.";
            $this->redirect('/admin/contratos/modelos');
        }

        $db->delete('contract_templates', 'id = ?', [(int) $id]);
        Security::audit('contract_template_deleted', 'contract_templates', (int) $id);

        $_SESSION['flash_success'] = 'Modelo removido.';
        $this->redirect('/admin/contratos/modelos');
    }

    private function nullable(mixed $value, ?int $maxLength = null): ?string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return null;
        }

        return $maxLength !== null ? mb_substr($value, 0, $maxLength) : $value;
    }
}
