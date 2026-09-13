<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\BaseMiddleware;

class AuthMiddleware extends BaseMiddleware
{
    public function handle(): bool
    {
        // 1. Tentar autenticação via Bearer Token (API)
        $token = $this->getBearerToken();
        if ($token) {
            return $this->authenticateByToken($token);
        }

        // 2. Autenticação via sessão (navegador)
        if (!isset($_SESSION['user_id'])) {
            // Se for rota de API, retornar JSON 401
            if ($this->isApiRoute()) {
                http_response_code(401);
                echo json_encode(['error' => 'Não autenticado', 'message' => 'Use um Bearer token no header Authorization ou faça login.']);
                exit;
            }
            header('Location: /admin/login');
            exit;
        }

        // Verificar se usuário ainda está ativo (e qual o papel)
        $db = \App\Core\App::getInstance()->getDb();
        $user = $db->fetch(
            "SELECT u.active, r.name AS role_name
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE u.id = ?",
            [$_SESSION['user_id']]
        );

        if (!$user || !$user['active']) {
            \App\Core\Security::destroySession();
            if ($this->isApiRoute()) {
                http_response_code(401);
                echo json_encode(['error' => 'Usuário inativo']);
                exit;
            }
            header('Location: /admin/login');
            exit;
        }

        // Motorista só tem acesso à área dele
        if (($user['role_name'] ?? '') === 'motorista' && !$this->isApiRoute()) {
            header('Location: /motorista');
            exit;
        }

        return true;
    }

    /**
     * Extrai o Bearer token do header Authorization.
     */
    private function getBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Autentica via api_token no banco de dados.
     */
    private function authenticateByToken(string $token): bool
    {
        $db = \App\Core\App::getInstance()->getDb();
        $user = $db->fetch(
            "SELECT id, name, email, active FROM users WHERE api_token = ?",
            [$token]
        );

        if (!$user || !$user['active']) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido ou usuário inativo']);
            exit;
        }

        // Popular sessão para compatibilidade
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        return true;
    }

    /**
     * Detecta se a rota atual é da API.
     */
    private function isApiRoute(): bool
    {
        return str_starts_with($_SERVER['REQUEST_URI'] ?? '/', '/api/');
    }
}
