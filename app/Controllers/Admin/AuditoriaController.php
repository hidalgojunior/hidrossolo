<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;

class AuditoriaController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();
        $page = (int)($_GET['page'] ?? 1);
        $limit = 30;
        $offset = ($page - 1) * $limit;

        $logs = $db->fetchAll(
            "SELECT a.*, u.name as user_name FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );

        $total = $db->fetch("SELECT COUNT(*) as c FROM audit_logs")['c'] ?? 0;

        echo $this->view('admin.auditoria.index', [
            'title' => 'Auditoria',
            'logs' => $logs,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }
}
