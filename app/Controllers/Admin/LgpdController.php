<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Security;

/**
 * Gestão das solicitações de titulares de dados (LGPD).
 */
class LgpdController extends BaseController
{
    private const STATUSES = ['new', 'in_progress', 'answered', 'denied'];

    public function index(): void
    {
        $db = $this->db();

        $status = (string) ($_GET['status'] ?? 'all');
        $where = '';
        $params = [];

        if (in_array($status, self::STATUSES, true)) {
            $where = 'WHERE status = ?';
            $params[] = $status;
        }

        $solicitacoes = $db->fetchAll(
            "SELECT * FROM lgpd_requests {$where} ORDER BY
                FIELD(status, 'new', 'in_progress', 'answered', 'denied'), created_at DESC
             LIMIT 100",
            $params
        );

        $totais = $db->fetch(
            "SELECT
                COUNT(*) AS total,
                COALESCE(SUM(status = 'new'), 0) AS novos,
                COALESCE(SUM(status = 'in_progress'), 0) AS em_andamento,
                COALESCE(SUM(status = 'answered'), 0) AS respondidos,
                COALESCE(SUM(status = 'new' AND created_at < DATE_SUB(NOW(), INTERVAL 14 DAY)), 0) AS atrasados
             FROM lgpd_requests"
        );

        echo $this->view('admin.lgpd.index', [
            'title' => 'Solicitações LGPD',
            'solicitacoes' => $solicitacoes,
            'totais' => $totais,
            'status' => $status,
            'tipos' => \App\Controllers\LegalController::tiposSolicitacao(),
            'config' => $this->config('company'),
        ]);
    }

    public function responder(string $id): void
    {
        $db = $this->db();
        $id = (int) $id;
        $atual = $db->fetch("SELECT * FROM lgpd_requests WHERE id = ?", [$id]);

        if (!$atual) {
            $_SESSION['flash_error'] = 'Solicitação não encontrada.';
            $this->redirect('/admin/lgpd');
        }

        $status = (string) ($_POST['status'] ?? 'in_progress');
        $resposta = trim((string) ($_POST['response'] ?? ''));

        if (!in_array($status, self::STATUSES, true)) {
            $status = 'in_progress';
        }

        $db->update('lgpd_requests', [
            'status' => $status,
            'response' => $resposta !== '' ? $resposta : null,
            'answered_at' => in_array($status, ['answered', 'denied'], true) ? date('Y-m-d H:i:s') : null,
            'answered_by' => (int) ($_SESSION['user_id'] ?? 0),
        ], 'id = ?', [$id]);

        Security::audit('lgpd_request_' . $status, 'lgpd_requests', $id);

        $_SESSION['flash_success'] = 'Solicitação atualizada.';
        $this->redirect('/admin/lgpd');
    }
}
