<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;

class ContatosController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();

        $status = $_GET['status'] ?? 'all';
        $where = $status !== 'all' ? "WHERE status = ?" : "";
        $params = $status !== 'all' ? [$status] : [];

        $contatos = $db->fetchAll(
            "SELECT * FROM contacts {$where} ORDER BY created_at DESC",
            $params
        );

        $total = $db->fetch("SELECT COUNT(*) as total FROM contacts")['total'] ?? 0;
        $novos = $db->fetch("SELECT COUNT(*) as total FROM contacts WHERE status = 'new'")['total'] ?? 0;

        echo $this->view('admin.contatos.index', [
            'title' => 'Mensagens Recebidas',
            'contatos' => $contatos,
            'total' => $total,
            'novos' => $novos,
            'status' => $status,
            'config' => $this->config('company'),
        ]);
    }

    public function show(string $id): void
    {
        $db = $this->db();

        $contato = $db->fetch("SELECT * FROM contacts WHERE id = ?", [$id]);

        if (!$contato) {
            $_SESSION['flash_error'] = 'Mensagem não encontrada.';
            $this->redirect('/admin/contatos');
        }

        // Marcar como lida
        if ($contato['status'] === 'new') {
            $db->update('contacts', ['status' => 'read'], 'id = ?', [$id]);
        }

        echo $this->view('admin.contatos.show', [
            'title' => 'Visualizar Mensagem',
            'contato' => $contato,
            'config' => $this->config('company'),
        ]);
    }
}
