<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;

class UsuariosController extends BaseController
{
    /**
     * Verifica se o usuário logado é superadmin
     */
    private function isSuperAdmin(): bool
    {
        return ($_SESSION['user_role'] ?? 0) == 5;
    }

    public function index(): void
    {
        $db = $this->db();
        $isSuperAdmin = $this->isSuperAdmin();

        // Superadmins só são visíveis para outros superadmins
        $whereExclude = $isSuperAdmin ? '' : 'WHERE u.role_id != 5';

        $usuarios = $db->fetchAll(
            "SELECT u.*, r.name as role_name FROM users u
             JOIN roles r ON r.id = u.role_id
             {$whereExclude}
             ORDER BY u.name"
        );

        // Função para ocultar role superadmin de não-superadmins
        $roles = $db->fetchAll(
            $isSuperAdmin
                ? "SELECT * FROM roles ORDER BY id"
                : "SELECT * FROM roles WHERE id != 5 ORDER BY id"
        );

        echo $this->view('admin.usuarios.index', [
            'title' => 'Gerenciar Usuários',
            'usuarios' => $usuarios,
            'roles' => $roles,
            'isSuperAdmin' => $isSuperAdmin,
            'config' => $this->config('company'),
        ]);
    }

    public function create(): void
    {
        $isSuperAdmin = $this->isSuperAdmin();
        $roles = $this->db()->fetchAll(
            $isSuperAdmin
                ? "SELECT * FROM roles ORDER BY id"
                : "SELECT * FROM roles WHERE id != 5 ORDER BY id"
        );

        echo $this->view('admin.usuarios.form', [
            'title' => 'Novo Usuário',
            'usuario' => null,
            'roles' => $roles,
            'isSuperAdmin' => $isSuperAdmin,
            'config' => $this->config('company'),
        ]);
    }

    public function store(): void
    {
        $data = $this->validate([
            'name' => 'required|min:3|max:255',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'role_id' => 'required',
        ]);

        // Verificar e-mail único
        $exists = $this->db()->fetch("SELECT id FROM users WHERE email = ?", [$data['email']]);
        if ($exists) {
            $_SESSION['flash_error'] = 'Este e-mail já está cadastrado.';
            $this->redirect('/admin/usuarios/novo');
        }

        $this->db()->insert('users', [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'role_id' => (int)$data['role_id'],
            'active' => 1,
        ]);

        $_SESSION['flash_success'] = 'Usuário cadastrado com sucesso!';
        $this->redirect('/admin/usuarios');
    }
}
