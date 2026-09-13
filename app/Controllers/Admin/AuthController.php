<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Security;

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
        $email = (string) $data['email'];
        $ip = Security::clientIp();

        // Bloqueio progressivo contra força bruta
        $blockedFor = Security::loginBlockedFor($email, $ip);

        if ($blockedFor > 0) {
            Security::recordLoginAttempt($email, $ip, false, 'blocked');
            Security::audit('login_blocked', 'user', null, ['email' => $email]);
            $_SESSION['flash_error'] = 'Muitas tentativas. Tente novamente em ' . (int) ceil($blockedFor / 60) . ' minuto(s).';
            $this->redirect('/admin/login');
        }

        $user = $db->fetch(
            "SELECT u.id, u.name, u.email, u.password, u.role_id, u.active, r.name AS role_name
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE u.email = ? LIMIT 1",
            [$email]
        );

        if (!$user || !$user['active'] || !password_verify($data['password'], $user['password'])) {
            Security::recordLoginAttempt($email, $ip, false, 'invalid_credentials');
            Security::audit('login_failed', 'user', null, ['email' => $email]);
            $_SESSION['flash_error'] = 'E-mail ou senha inválidos.';
            $this->redirect('/admin/login');
        }

        // Sessão limpa + ID renovado (evita fixação de sessão)
        Security::clearLoginAttempts($email, $ip);
        Security::loginSession($user);
        Security::recordLoginAttempt($email, $ip, true, 'ok');

        $db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
        Security::audit('login', 'user', (int) $user['id']);

        // Motorista vai direto para a área dele
        if (($user['role_name'] ?? '') === 'motorista') {
            $this->redirect('/motorista');
        }

        $this->redirect('/admin');
    }

    public function logout(): void
    {
        if (isset($_SESSION['user_id'])) {
            Security::audit('logout', 'user', (int) $_SESSION['user_id']);
        }

        Security::destroySession();
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

            $issues = Security::passwordIssues((string) $_POST['new_password'], (string) ($_SESSION['user_email'] ?? ''));

            if ($issues !== []) {
                $_SESSION['flash_error'] = implode(' ', $issues);
                $this->redirect('/admin/perfil');
            }

            $updateData['password'] = password_hash($_POST['new_password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $updateData['password_changed_at'] = date('Y-m-d H:i:s');
            Security::audit('password_changed', 'user', (int) $_SESSION['user_id']);
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
