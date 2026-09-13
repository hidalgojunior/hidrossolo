<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;

class AuthController extends BaseController
{
    public function loginForm(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/admin');
        }

        echo $this->view('admin.login', [
            'title' => 'Login - Área Restrita',
        ]);
    }

    public function login(): void
    {
        $data = $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $db = $this->db();

        $user = $db->fetch(
            "SELECT id, name, email, password, role_id FROM users WHERE email = ? AND active = 1",
            [$data['email']]
        );

        if (!$user || !password_verify($data['password'], $user['password'])) {
            $_SESSION['flash_error'] = 'E-mail ou senha inválidos.';
            $this->redirect('/admin/login');
        }

        // Atualizar último login
        $db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

        // Registrar auditoria
        $db->insert('audit_logs', [
            'user_id' => $user['id'],
            'action' => 'login',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        // Criar sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role_id'];

        $this->redirect('/admin');
    }

    public function logout(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->db()->insert('audit_logs', [
                'user_id' => $_SESSION['user_id'],
                'action' => 'logout',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        }

        session_destroy();
        $this->redirect('/admin/login');
    }

    public function perfil(): void
    {
        $user = $this->db()->fetch(
            "SELECT u.*, r.name as role_name FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.id = ?",
            [$_SESSION['user_id']]
        );

        echo $this->view('admin.perfil', [
            'title' => 'Meu Perfil',
            'user' => $user,
            'config' => $this->config('company'),
        ]);
    }

    public function updatePerfil(): void
    {
        $data = $this->validate([
            'name' => 'required|min:3|max:255',
        ]);

        $updateData = ['name' => $data['name']];

        // Senha opcional
        if (!empty($_POST['new_password'])) {
            if ($_POST['new_password'] !== ($_POST['confirm_password'] ?? '')) {
                $_SESSION['flash_error'] = 'As senhas não conferem.';
                $this->redirect('/admin/perfil');
            }
            $updateData['password'] = password_hash($_POST['new_password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $this->db()->update('users', $updateData, 'id = ?', [$_SESSION['user_id']]);

        $_SESSION['user_name'] = $data['name'];
        $_SESSION['flash_success'] = 'Perfil atualizado com sucesso!';
        $this->redirect('/admin/perfil');
    }

    // ==================== RECUPERAÇÃO DE SENHA ====================

    public function forgotForm(): void
    {
        echo $this->view('admin.forgot-password', [
            'title' => 'Recuperar Senha',
        ]);
    }

    public function forgotSend(): void
    {
        $data = $this->validate([
            'email' => 'required|email',
        ]);

        $db = $this->db();
        $user = $db->fetch("SELECT id, name, email FROM users WHERE email = ? AND active = 1", [$data['email']]);

        if ($user) {
            // Gerar token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $db->update('users', [
                'password_reset_token' => $token,
                'password_reset_expires' => $expires,
            ], 'id = ?', [$user['id']]);

            // Em produção: enviar e-mail. Aqui apenas mostramos na tela
            $resetLink = ($_ENV['APP_URL'] ?? 'http://localhost:8083') . '/admin/reset-senha/' . $token;
            $_SESSION['reset_link'] = $resetLink;
            $_SESSION['reset_email'] = $user['email'];
        }

        // Sempre mostrar sucesso (não revela se o e-mail existe)
        $_SESSION['flash_success'] = 'Se o e-mail estiver cadastrado, um link de recuperação foi gerado.';
        $this->redirect('/admin/esqueci-senha');
    }

    public function resetForm(string $token): void
    {
        $db = $this->db();
        $user = $db->fetch(
            "SELECT id, email FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW()",
            [$token]
        );

        if (!$user) {
            $_SESSION['flash_error'] = 'Link inválido ou expirado.';
            $this->redirect('/admin/login');
        }

        echo $this->view('admin.reset-password', [
            'title' => 'Redefinir Senha',
            'token' => $token,
            'email' => $user['email'],
        ]);
    }

    public function resetPassword(): void
    {
        $data = $this->validate([
            'token' => 'required',
            'password' => 'required|min:6',
        ]);

        if ($data['password'] !== ($_POST['confirm_password'] ?? '')) {
            $_SESSION['flash_error'] = 'As senhas não conferem.';
            $this->redirect('/admin/reset-senha/' . $data['token']);
        }

        $db = $this->db();
        $user = $db->fetch(
            "SELECT id FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW()",
            [$data['token']]
        );

        if (!$user) {
            $_SESSION['flash_error'] = 'Link inválido ou expirado.';
            $this->redirect('/admin/login');
        }

        $db->update('users', [
            'password' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'password_reset_token' => null,
            'password_reset_expires' => null,
        ], 'id = ?', [$user['id']]);

        $_SESSION['flash_success'] = 'Senha redefinida com sucesso! Faça login.';
        $this->redirect('/admin/login');
    }
}
