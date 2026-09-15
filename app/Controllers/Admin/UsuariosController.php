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

    /**
     * Formulário de edição.
     */
    public function edit(string $id): void
    {
        $usuario = $this->buscar((int) $id);

        echo $this->view('admin.usuarios.form', [
            'title' => 'Editar Usuário',
            'usuario' => $usuario,
            'roles' => $this->rolesDisponiveis(),
            'isSuperAdmin' => $this->isSuperAdmin(),
            'config' => $this->config('company'),
        ]);
    }

    /**
     * Atualiza um usuário existente.
     *
     * A senha é opcional: em branco mantém a atual. A conta administradora
     * geral tem proteções extras (identidade, perfil e senha).
     */
    public function update(string $id): void
    {
        $id = (int) $id;
        $alvo = $this->buscar($id);

        $data = $this->validate([
            'name' => 'required|min:3|max:255',
            'email' => 'required|email',
            'password' => 'min:6',
            'role_id' => 'required',
        ]);

        $email = strtolower(trim((string) $data['email']));
        $roleId = (int) $data['role_id'];
        $ativo = isset($_POST['active']) ? 1 : 0;
        $novaSenha = trim((string) ($data['password'] ?? ''));

        $ehProprioUsuario = $id === (int) ($_SESSION['user_id'] ?? 0);
        $alvoEhOwner = strtolower((string) $alvo['email']) === self::OWNER_EMAIL;

        $erros = [];

        // E-mail único (ignorando o próprio registro)
        if ($this->db()->fetch('SELECT id FROM users WHERE LOWER(email) = ? AND id <> ?', [$email, $id])) {
            $erros[] = 'Este e-mail já está cadastrado para outro usuário.';
        }

        // A identidade da conta administradora geral nunca muda
        if ($alvoEhOwner && $email !== self::OWNER_EMAIL) {
            $erros[] = 'O e-mail da conta administradora geral não pode ser alterado.';
        }
        if (!$alvoEhOwner && $email === self::OWNER_EMAIL) {
            $erros[] = 'Este endereço de e-mail não pode ser utilizado.';
        }

        // Somente superadmin administra superadmin
        if ($roleId === 5 && !$this->isSuperAdmin()) {
            $erros[] = 'Você não tem permissão para atribuir este perfil.';
        }

        // A conta administradora geral permanece superadmin
        if ($alvoEhOwner && $roleId !== 5) {
            $erros[] = 'O perfil da conta administradora geral não pode ser rebaixado.';
        }

        // Ninguém remove o próprio acesso
        if ($ehProprioUsuario && $ativo === 0) {
            $erros[] = 'Você não pode desativar o seu próprio acesso.';
        }

        // Só a própria conta administradora geral altera a sua senha
        if ($alvoEhOwner && !$this->isOwner() && $novaSenha !== '') {
            $erros[] = 'A senha da conta administradora geral só pode ser alterada por ela mesma.';
        }

        // Política de senha (apenas quando uma nova senha é informada)
        if ($novaSenha !== '' && $erros === []) {
            $problemas = Security::passwordIssues($novaSenha, $email);

            if ($problemas !== []) {
                $erros = array_merge($erros, $problemas);
            }
        }

        if ($erros !== []) {
            $_SESSION['flash_error'] = implode(' ', $erros);
            $this->redirect('/admin/usuarios/editar/' . $id);
        }

        $dados = [
            'name' => trim((string) $data['name']),
            'email' => $email,
            'role_id' => $roleId,
            'active' => $ativo,
        ];

        if ($novaSenha !== '') {
            $dados['password'] = password_hash($novaSenha, PASSWORD_BCRYPT, ['cost' => 12]);
            $dados['password_changed_at'] = date('Y-m-d H:i:s');
            $dados['password_reset_token'] = null;
            $dados['password_reset_expires'] = null;
        }

        $this->db()->update('users', $dados, 'id = ?', [$id]);

        Security::audit('user_updated', 'users', $id, [
            'role_id' => $roleId,
            'active' => $ativo,
            'senha_alterada' => $novaSenha !== '',
        ]);

        unset($_SESSION['old_input']);
        $_SESSION['flash_success'] = 'Usuário atualizado com sucesso!';
        $this->redirect('/admin/usuarios');
    }

    /**
     * Exclui um usuário, respeitando as mesmas proteções da edição.
     */
    public function delete(string $id): void
    {
        $alvo = $this->buscar((int) $id);

        // Ninguém exclui a própria conta
        if ((int) $alvo['id'] === (int) ($_SESSION['user_id'] ?? 0)) {
            $_SESSION['flash_error'] = 'Você não pode excluir o seu próprio usuário.';
            $this->redirect('/admin/usuarios');
        }

        // A conta administradora geral nunca pode ser excluída
        if (strtolower((string) $alvo['email']) === self::OWNER_EMAIL) {
            $_SESSION['flash_error'] = 'A conta administradora geral não pode ser excluída.';
            $this->redirect('/admin/usuarios');
        }

        // Somente superadmin administra superadmin
        if ((int) $alvo['role_id'] === 5 && !$this->isSuperAdmin()) {
            $_SESSION['flash_error'] = 'Você não tem permissão para excluir este usuário.';
            $this->redirect('/admin/usuarios');
        }

        // Não deixar o sistema sem nenhum administrador ativo
        $outrosAdmins = (int) $this->db()->fetch(
            'SELECT COUNT(*) AS n FROM users WHERE role_id IN (1, 5) AND active = 1 AND id <> ?',
            [$id]
        )['n'];

        if ($outrosAdmins === 0) {
            $_SESSION['flash_error'] = 'Não é possível excluir o último administrador ativo do sistema.';
            $this->redirect('/admin/usuarios');
        }

        $this->db()->delete('users', 'id = ?', [$id]);
        Security::audit('user_deleted', 'users', (int) $id, ['email' => $alvo['email']]);

        $_SESSION['flash_success'] = 'Usuário excluído com sucesso!';
        $this->redirect('/admin/usuarios');
    }

    /* ===================================================================== */

    /**
     * Carrega um usuário respeitando as regras de visibilidade:
     * a conta administradora geral e os superadmins não aparecem para os
     * demais perfis.
     *
     * @return array<string,mixed>
     */
    private function buscar(int $id): array
    {
        $usuario = $this->db()->fetch('SELECT * FROM users WHERE id = ?', [$id]);

        $oculto = $usuario !== null && (
            (int) $usuario['role_id'] === 5
            || strtolower((string) $usuario['email']) === self::OWNER_EMAIL
        );

        if ($usuario === null || ($oculto && !$this->isSuperAdmin() && !$this->isOwner())) {
            $_SESSION['flash_error'] = 'Usuário não encontrado.';
            $this->redirect('/admin/usuarios');
        }

        return $usuario;
    }

    /**
     * Perfis que o usuário logado pode atribuir.
     *
     * @return array<int,array<string,mixed>>
     */
    private function rolesDisponiveis(): array
    {
        return $this->db()->fetchAll(
            $this->isSuperAdmin()
                ? 'SELECT * FROM roles ORDER BY id'
                : 'SELECT * FROM roles WHERE id != 5 ORDER BY id'
        );
    }
}
