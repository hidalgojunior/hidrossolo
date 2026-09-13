<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Security;
use App\Support\ContractRenderer;

/**
 * Contratos: cadastro, geração a partir de modelos com variáveis e PDF.
 */
class ContratosController extends BaseController
{
    public function index(): void
    {
        $contratos = $this->db()->fetchAll(
            "SELECT c.*, t.name AS template_name
             FROM contracts c
             LEFT JOIN contract_templates t ON t.id = c.template_id
             ORDER BY c.created_at DESC"
        );

        echo $this->view('admin.contratos.index', [
            'title' => 'Contratos',
            'contratos' => $contratos,
            'config' => $this->config('company'),
        ]);
    }

    public function create(): void
    {
        echo $this->view('admin.contratos.form', [
            'title' => 'Novo Contrato',
            'contrato' => null,
            'templates' => $this->templatesComVariaveis(),
            'valores' => [],
            'config' => $this->config('company'),
        ]);
    }

    public function store(): void
    {
        $db = $this->db();

        $data = $this->validate([
            'contract_number' => 'required|max:100',
            'client_name' => 'required|max:255',
        ]);

        $template = $this->template((int) ($_POST['template_id'] ?? 0));

        $contrato = [
            'contract_number' => (string) $data['contract_number'],
            'client_name' => (string) $data['client_name'],
            'responsible' => (string) ($_POST['responsible'] ?? ''),
            'start_date' => $this->date($_POST['start_date'] ?? null),
            'end_date' => $this->date($_POST['end_date'] ?? null),
            'value' => $this->decimal($_POST['value'] ?? null) ?? 0.0,
            'status' => in_array($_POST['status'] ?? 'active', ['active', 'expired', 'cancelled', 'completed'], true)
                ? (string) $_POST['status']
                : 'active',
            'notes' => $this->nullable($_POST['notes'] ?? null),
        ];

        $valores = $this->valores($contrato, $template);
        $conteudo = $template ? ContractRenderer::render((string) $template['content'], $valores) : null;

        $id = $db->insert('contracts', array_merge($contrato, [
            'template_id' => $template['id'] ?? null,
            'content' => $conteudo,
            'variables' => json_encode($valores, JSON_UNESCAPED_UNICODE),
            'generated_at' => $conteudo !== null ? date('Y-m-d H:i:s') : null,
            'created_by' => (int) ($_SESSION['user_id'] ?? 0),
        ]));

        Security::audit('contract_created', 'contracts', $id, ['template_id' => $template['id'] ?? null]);

        $_SESSION['flash_success'] = $conteudo !== null
            ? 'Contrato criado e documento gerado a partir do modelo!'
            : 'Contrato criado.';

        $this->redirect('/admin/contratos');
    }

    public function edit(string $id): void
    {
        $contrato = $this->contrato((int) $id);

        if (!$contrato) {
            $_SESSION['flash_error'] = 'Contrato não encontrado.';
            $this->redirect('/admin/contratos');
        }

        echo $this->view('admin.contratos.form', [
            'title' => 'Editar Contrato',
            'contrato' => $contrato,
            'templates' => $this->templatesComVariaveis(),
            'valores' => json_decode((string) ($contrato['variables'] ?? '{}'), true) ?: [],
            'config' => $this->config('company'),
        ]);
    }

    public function update(string $id): void
    {
        $db = $this->db();
        $atual = $this->contrato((int) $id);

        if (!$atual) {
            $_SESSION['flash_error'] = 'Contrato não encontrado.';
            $this->redirect('/admin/contratos');
        }

        $data = $this->validate([
            'contract_number' => 'required|max:100',
            'client_name' => 'required|max:255',
        ]);

        $template = $this->template((int) ($_POST['template_id'] ?? $atual['template_id'] ?? 0));

        $contrato = [
            'contract_number' => (string) $data['contract_number'],
            'client_name' => (string) $data['client_name'],
            'responsible' => (string) ($_POST['responsible'] ?? ''),
            'start_date' => $this->date($_POST['start_date'] ?? null),
            'end_date' => $this->date($_POST['end_date'] ?? null),
            'value' => $this->decimal($_POST['value'] ?? null) ?? 0.0,
            'status' => in_array($_POST['status'] ?? 'active', ['active', 'expired', 'cancelled', 'completed'], true)
                ? (string) $_POST['status']
                : 'active',
            'notes' => $this->nullable($_POST['notes'] ?? null),
        ];

        $valores = $this->valores($contrato, $template);
        $conteudo = $template ? ContractRenderer::render((string) $template['content'], $valores) : null;

        $db->update('contracts', array_merge($contrato, [
            'template_id' => $template['id'] ?? null,
            'content' => $conteudo,
            'variables' => json_encode($valores, JSON_UNESCAPED_UNICODE),
            'generated_at' => $conteudo !== null ? date('Y-m-d H:i:s') : null,
        ]), 'id = ?', [(int) $id]);

        Security::audit('contract_updated', 'contracts', (int) $id);

        $_SESSION['flash_success'] = $conteudo !== null
            ? 'Contrato atualizado e documento regerado!'
            : 'Contrato atualizado.';

        $this->redirect('/admin/contratos');
    }

    /**
     * Pré-visualização do documento (pronta para impressão).
     */
    public function documento(string $id): void
    {
        $contrato = $this->contrato((int) $id);

        if (!$contrato || trim((string) ($contrato['content'] ?? '')) === '') {
            http_response_code(404);
            echo $this->view('errors.404');
            return;
        }

        echo $this->view('admin.contratos.documento', [
            'title' => 'Contrato ' . $contrato['contract_number'],
            'contrato' => $contrato,
            'config' => $this->config('company'),
        ]);
    }

    /**
     * Gera o PDF do contrato com dompdf.
     */
    public function pdf(string $id): void
    {
        $contrato = $this->contrato((int) $id);

        if (!$contrato || trim((string) ($contrato['content'] ?? '')) === '') {
            http_response_code(404);
            echo $this->view('errors.404');
            return;
        }

        Security::audit('contract_pdf', 'contracts', (int) $id);

        $dompdf = new \Dompdf\Dompdf([
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        $dompdf->loadHtml($this->documentHtml($contrato), 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $numero = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($contrato['contract_number'] ?: $contrato['id']));
        $filename = 'contrato-' . ($numero !== '' ? $numero : $contrato['id']) . '.pdf';

        if (!headers_sent()) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($dompdf->output()));
        }

        echo $dompdf->output();
        exit;
    }

    /* ===================================================================== */

    private function contractDocumentHtml(string $content): string
    {
        return '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">'
            . '<style>'
            . 'body{font-family:"DejaVu Sans",Tahoma,sans-serif;font-size:11pt;line-height:1.6;color:#111;margin:0}'
            . 'h2{font-size:13pt;text-align:center;margin:0 0 18px}'
            . 'h3{font-size:11.5pt;margin:16px 0 6px}'
            . 'p{margin:0 0 10px;text-align:justify}'
            . 'strong{font-weight:bold}'
            . '</style></head><body>' . $content . '</body></html>';
    }

    private function documentHtml(array $contrato): string
    {
        return $this->contractDocumentHtml((string) $contrato['content']);
    }

    private function contrato(int $id): ?array
    {
        return $this->db()->fetch(
            "SELECT c.*, t.name AS template_name
             FROM contracts c
             LEFT JOIN contract_templates t ON t.id = c.template_id
             WHERE c.id = ?",
            [$id]
        );
    }

    private function template(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        return $this->db()->fetch("SELECT * FROM contract_templates WHERE id = ? AND active = 1", [$id]) ?: null;
    }

    /**
     * Modelos ativos com a lista de variáveis para montar o formulário.
     */
    private function templatesComVariaveis(): array
    {
        $templates = $this->db()->fetchAll(
            "SELECT id, name, content, description FROM contract_templates WHERE active = 1 ORDER BY name"
        );

        foreach ($templates as &$t) {
            $t['variaveis'] = ContractRenderer::variables((string) $t['content']);
        }
        unset($t);

        return $templates;
    }

    /**
     * Junta os valores automáticos do contrato com os preenchidos no formulário.
     */
    private function valores(array $contrato, ?array $template): array
    {
        $valores = ContractRenderer::defaults($contrato);
        $enviados = $_POST['vars'] ?? [];

        if (is_array($enviados)) {
            foreach ($enviados as $chave => $valor) {
                $chave = (string) $chave;

                // Variáveis conhecidas não podem ser sobrescritas por dados divergentes do cadastro
                if (array_key_exists($chave, $valores) && $valores[$chave] !== '') {
                    continue;
                }

                $valores[$chave] = is_scalar($valor) ? trim((string) $valor) : '';
            }
        }

        return $valores;
    }

    private function date(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' && strtotime($value) ? date('Y-m-d', strtotime($value)) : null;
    }

    private function decimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = str_replace(['.', ','], ['', '.'], (string) $value);

        return is_numeric($normalized) ? round((float) $normalized, 2) : null;
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
