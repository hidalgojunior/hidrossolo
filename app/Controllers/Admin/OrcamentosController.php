<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Support\Orcamentos;

/**
 * Solicitações de orçamento recebidas pelo formulário público `/orcamento`.
 *
 * Antes desta tela os dados eram gravados mas ninguém conseguia lê-los pelo
 * painel.
 */
class OrcamentosController extends BaseController
{
    /** Situações possíveis de uma solicitação. */
    public const STATUS = [
        'novo' => ['rotulo' => 'Nova', 'cor' => 'danger'],
        'em_analise' => ['rotulo' => 'Em análise', 'cor' => 'warning'],
        'respondido' => ['rotulo' => 'Respondida', 'cor' => 'primary'],
        'aprovado' => ['rotulo' => 'Aprovada', 'cor' => 'success'],
        'recusado' => ['rotulo' => 'Recusada', 'cor' => 'secondary'],
    ];

    public function index(): void
    {
        $db = $this->db();

        // A tabela pode não existir ainda (nenhuma solicitação recebida).
        Orcamentos::garantir($db);

        $status = (string) ($_GET['status'] ?? 'all');

        if ($status !== 'all' && !array_key_exists($status, self::STATUS)) {
            $status = 'all';
        }

        $where = $status !== 'all' ? 'WHERE status = ?' : '';
        $params = $status !== 'all' ? [$status] : [];

        $orcamentos = $db->fetchAll(
            'SELECT * FROM ' . Orcamentos::TABELA . " {$where} ORDER BY created_at DESC, id DESC",
            $params
        );

        $total = (int) ($db->fetch('SELECT COUNT(*) AS c FROM ' . Orcamentos::TABELA)['c'] ?? 0);

        $porStatus = [];
        $linhas = $db->fetchAll('SELECT status, COUNT(*) AS c FROM ' . Orcamentos::TABELA . ' GROUP BY status');

        foreach ($linhas as $linha) {
            $porStatus[(string) $linha['status']] = (int) $linha['c'];
        }

        echo $this->view('admin.orcamentos.index', [
            'title' => 'Orçamentos Recebidos',
            'orcamentos' => $orcamentos,
            'status' => $status,
            'total' => $total,
            'porStatus' => $porStatus,
            'statusLabels' => self::STATUS,
            'config' => $this->config('company'),
        ]);
    }

    public function show(string $id): void
    {
        $db = $this->db();

        Orcamentos::garantir($db);

        $orcamento = $db->fetch('SELECT * FROM ' . Orcamentos::TABELA . ' WHERE id = ?', [(int) $id]);

        if (!$orcamento) {
            $_SESSION['flash_error'] = 'Solicitação não encontrada.';
            $this->redirect('/admin/orcamentos');
        }

        // Abrir uma solicitação nova já a coloca em análise.
        if ($orcamento['status'] === 'novo') {
            $db->update(Orcamentos::TABELA, ['status' => 'em_analise'], 'id = ?', [(int) $id]);
            $orcamento['status'] = 'em_analise';
        }

        echo $this->view('admin.orcamentos.show', [
            'title' => 'Solicitação de Orçamento',
            'orcamento' => $orcamento,
            'statusLabels' => self::STATUS,
            'config' => $this->config('company'),
        ]);
    }

    public function status(string $id): void
    {
        $status = (string) ($_POST['status'] ?? '');
        $destino = '/admin/orcamentos/' . (int) $id;

        if (!array_key_exists($status, self::STATUS)) {
            $_SESSION['flash_error'] = 'Situação inválida.';
            $this->redirect($destino);
        }

        $this->db()->update(Orcamentos::TABELA, ['status' => $status], 'id = ?', [(int) $id]);
        $this->logAudit('orcamento_status', (int) $id);

        $_SESSION['flash_success'] = 'Situação atualizada para "' . self::STATUS[$status]['rotulo'] . '".';
        $this->redirect($destino);
    }

    public function delete(string $id): void
    {
        $this->db()->delete(Orcamentos::TABELA, 'id = ?', [(int) $id]);
        $this->logAudit('orcamento_delete', (int) $id);

        $_SESSION['flash_success'] = 'Solicitação excluída.';
        $this->redirect('/admin/orcamentos');
    }

    private function logAudit(string $action, int $id): void
    {
        $this->db()->insert('audit_logs', [
            'user_id' => $_SESSION['user_id'] ?? null,
            'action' => $action,
            'entity_type' => 'orcamento',
            'entity_id' => $id,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}
