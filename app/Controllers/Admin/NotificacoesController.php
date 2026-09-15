<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Security;
use App\Support\Financeiro;

/**
 * Central de notificações do sistema.
 *
 * Os avisos de manutenção (30/15/7 dias), as contas a vencer e os eventos de
 * segurança chegam aqui. O sino da barra superior leva para esta tela.
 */
class NotificacoesController extends BaseController
{
    public function index(): void
    {
        $filtro = (string) ($_GET['filtro'] ?? 'todas');

        if (!in_array($filtro, ['todas', 'nao_lidas', 'lidas'], true)) {
            $filtro = 'todas';
        }

        $where = $this->escopo();
        $params = $this->escopoParams();

        if ($filtro === 'nao_lidas') {
            $where .= ' AND read_at IS NULL';
        } elseif ($filtro === 'lidas') {
            $where .= ' AND read_at IS NOT NULL';
        }

        $notificacoes = $this->db()->fetchAll(
            "SELECT * FROM notifications WHERE {$where} ORDER BY read_at IS NOT NULL, created_at DESC, id DESC LIMIT 200",
            $params
        );

        $total = (int) ($this->db()->fetch(
            "SELECT COUNT(*) AS c FROM notifications WHERE {$where}",
            $params
        )['c'] ?? 0);

        echo $this->view('admin.notificacoes.index', [
            'title' => 'Notificações',
            'notificacoes' => $notificacoes,
            'total' => $total,
            'filtro' => $filtro,
            'resumo' => $this->resumo(),
            'config' => $this->config('company'),
        ]);
    }

    /**
     * Abre a notificação: marca como lida e leva para a tela relacionada.
     */
    public function abrir(string $id): void
    {
        $notificacao = $this->buscar((int) $id);

        if ($notificacao['read_at'] === null) {
            $this->db()->update('notifications', ['read_at' => date('Y-m-d H:i:s')], 'id = ?', [(int) $id]);
        }

        $destino = trim((string) ($notificacao['link'] ?? ''));

        // Só aceita caminhos internos (evita redirecionamento para fora do sistema)
        if ($destino === '' || !str_starts_with($destino, '/') || str_starts_with($destino, '//')) {
            $destino = '/admin/notificacoes';
        }

        $this->redirect($destino);
    }

    /**
     * Marca uma notificação como lida (ou não lida) sem sair da tela.
     */
    public function ler(string $id): void
    {
        $this->buscar((int) $id);

        $marcar = (string) ($_POST['marcar'] ?? 'lida');
        $valor = $marcar === 'nao_lida' ? null : date('Y-m-d H:i:s');

        $this->db()->update('notifications', ['read_at' => $valor], 'id = ?', [(int) $id]);

        $this->voltar();
    }

    /**
     * Marca todas as notificações do usuário como lidas.
     */
    public function lerTodas(): void
    {
        $where = $this->escopo();
        $params = $this->escopoParams();

        $this->db()->query(
            "UPDATE notifications SET read_at = NOW() WHERE {$where} AND read_at IS NULL",
            $params
        );

        Security::audit('notifications_read_all', 'notifications');

        $_SESSION['flash_success'] = 'Todas as notificações foram marcadas como lidas.';
        $this->voltar();
    }

    public function excluir(string $id): void
    {
        $this->buscar((int) $id);
        $this->db()->delete('notifications', 'id = ?', [(int) $id]);

        $_SESSION['flash_success'] = 'Notificação removida.';
        $this->voltar();
    }

    /**
     * Remove todas as notificações já lidas.
     */
    public function limpar(): void
    {
        $where = $this->escopo();
        $params = $this->escopoParams();

        $this->db()->query(
            "DELETE FROM notifications WHERE {$where} AND read_at IS NOT NULL",
            $params
        );

        Security::audit('notifications_cleared', 'notifications');

        $_SESSION['flash_success'] = 'Notificações lidas foram removidas.';
        $this->voltar();
    }

    /**
     * Reprocessa os avisos de manutenção pendentes (30, 15 e 7 dias).
     */
    public function gerarAvisos(): void
    {
        $criados = \App\Support\FleetAgenda::notifyUpcoming();

        $_SESSION['flash_success'] = $criados > 0
            ? "{$criados} aviso(s) de manutenção gerado(s)."
            : 'Nenhum aviso novo: todas as manutenções já foram avisadas.';

        $this->voltar();
    }

    /* ===================================================================== */

    /**
     * @return array<string,int|float>
     */
    private function resumo(): array
    {
        $where = $this->escopo();
        $params = $this->escopoParams();

        $linha = $this->db()->fetch(
            "SELECT
                COUNT(*) AS total,
                COALESCE(SUM(read_at IS NULL), 0) AS nao_lidas,
                COALESCE(SUM(type = 'warning' AND read_at IS NULL), 0) AS alertas
             FROM notifications
             WHERE {$where}",
            $params
        ) ?? [];

        $financeiro = Financeiro::resumoPeriodo(date('Y-m-01'), date('Y-m-t'));

        return [
            'total' => (int) ($linha['total'] ?? 0),
            'nao_lidas' => (int) ($linha['nao_lidas'] ?? 0),
            'alertas' => (int) ($linha['alertas'] ?? 0),
            'contas_atrasadas' => $financeiro['atrasadas_qtd'],
            'contas_atrasadas_valor' => $financeiro['atrasadas'],
            'contas_semana' => $this->contasNaSemana(),
        ];
    }

    private function contasNaSemana(): int
    {
        $linha = $this->db()->fetch(
            "SELECT COUNT(*) AS c FROM financial_entries
             WHERE status = 'pending' AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
        );

        return (int) ($linha['c'] ?? 0);
    }

    /**
     * Notificações globais (user_id NULL) e as do usuário logado.
     */
    private function escopo(): string
    {
        return '(user_id IS NULL' . ($this->userId() > 0 ? ' OR user_id = ?' : '') . ')';
    }

    /**
     * @return array<int,mixed>
     */
    private function escopoParams(): array
    {
        $id = $this->userId();

        return $id > 0 ? [$id] : [];
    }

    private function userId(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    /**
     * @return array<string,mixed>
     */
    private function buscar(int $id): array
    {
        $notificacao = $this->db()->fetch(
            'SELECT * FROM notifications WHERE id = ? AND ' . $this->escopo(),
            array_merge([$id], $this->escopoParams())
        );

        if (!$notificacao) {
            $_SESSION['flash_error'] = 'Notificação não encontrada.';
            $this->redirect('/admin/notificacoes');
        }

        return $notificacao;
    }

    private function voltar(): void
    {
        $this->redirectInterno($_POST['redirect'] ?? null, '/admin/notificacoes');
    }
}
