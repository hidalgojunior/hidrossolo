<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Security;

class UsuariosController extends BaseController
{
    /**
     * Conta administradora geral: acesso total e invisível para os demais perfis.
     */
    private const OWNER_EMAIL = 'hidalgojunior@gmail.com';

    /**
     * Verifica se o usuário logado é superadmin
     */
    private function isSuperAdmin(): bool
    {
        return (int) ($_SESSION['user_role'] ?? 0) === 5;
    }

    /**
     * O usuário logado é a própria conta administradora geral?
     */
    private function isOwner(): bool
    {
        return strtolower((string) ($_SESSION['user_email'] ?? '')) === self::OWNER_EMAIL;
    }

    public function index(): void
    {
        $db = $this->db();
        $isSuperAdmin = $this->isSuperAdmin();

        $where = '';
        $params = [];

        // A conta administradora geral e os superadmins não aparecem para os demais perfis
        if (!$isSuperAdmin && !$this->isOwner()) {
            $where = 'WHERE u.role_id != 5 AND LOWER(u.email) <> ?';
            $params[] = self::OWNER_EMAIL;
        }

        $usuarios = $db->fetchAll(
            "SELECT u.*, r.name as role_name FROM users u
             JOIN roles r ON r.id = u.role_id
             {$where}
             ORDER BY u.name",
            $params
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

        // A conta administradora geral é protegida
        if (strtolower((string) $data['email']) === self::OWNER_EMAIL && !$this->isOwner()) {
            $_SESSION['flash_error'] = 'Este endereço de e-mail não pode ser utilizado.';
            $this->redirect('/admin/usuarios/novo');
        }

        // Somente superadmin pode criar outro superadmin
        $roleId = (int) $data['role_id'];
        if ($roleId === 5 && !$this->isSuperAdmin()) {
            $_SESSION['flash_error'] = 'Você não tem permissão para atribuir este perfil.';
            $this->redirect('/admin/usuarios/novo');
        }

        // Política de senha forte
        $problemas = Security::passwordIssues((string) $data['password'], (string) $data['email']);
        if ($problemas !== []) {
            $_SESSION['flash_error'] = implode(' ', $problemas);
            $this->redirect('/admin/usuarios/novo');
        }

        $id = $this->db()->insert('users', [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'role_id' => $roleId,
            'active' => 1,
            'password_changed_at' => date('Y-m-d H:i:s'),
        ]);

        Security::audit('user_created', 'users', $id, ['role_id' => $roleId]);

        $_SESSION['flash_success'] = 'Usuário cadastrado com sucesso!';
        $this->redirect('/admin/usuarios');
    }
}
