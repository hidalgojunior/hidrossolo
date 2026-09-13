<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\BaseMiddleware;

class CsrfMiddleware extends BaseMiddleware
{
    private array $except = [];

    public function __construct(array $except = [])
    {
        $this->except = $except;
    }

    public function handle(): bool
    {
        // Apenas para métodos que modificam estado
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return true;
        }

        // Verificar exceções (rotas que não precisam de CSRF)
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        foreach ($this->except as $path) {
            if (str_starts_with($uri, $path)) {
                return true;
            }
        }

        // Obter token do request
        $token = $_POST['_csrf_token']
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? $_SERVER['HTTP_X_CSRF-TOKEN']
            ?? null;

        if (!$token || !$this->validateToken($token)) {
            // Requisição AJAX/API: retorna JSON
            if ($this->isApiRequest()) {
                http_response_code(419);
                echo json_encode([
                    'error' => 'CSRF token inválido ou expirado.',
                    'message' => 'Por favor, recarregue a página e tente novamente.',
                ]);
                exit;
            }

            // Requisição normal: redireciona com flash message
            $_SESSION['flash_error'] = 'Sessão expirada. Por favor, recarregue a página e tente novamente.';
            $referer = $_SERVER['HTTP_REFERER'] ?? '/admin/dashboard';
            header('Location: ' . $referer);
            exit;
        }

        return true;
    }

    private function validateToken(string $token): bool
    {
        if (!isset($_SESSION['_csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['_csrf_token'], $token);
    }

    /**
     * Gera um novo token CSRF e armazena na sessão.
     */
    public static function generate(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['_csrf_token'] = $token;

        return $token;
    }

    /**
     * Retorna o token atual ou gera um novo.
     */
    public static function token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['_csrf_token'])) {
            return self::generate();
        }

        return $_SESSION['_csrf_token'];
    }

    /**
     * Renderiza o campo hidden com o token CSRF.
     */
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . self::token() . '">';
    }

    /**
     * Detecta se é uma requisição AJAX/API.
     */
    private function isApiRequest(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        return str_contains($accept, 'application/json')
            || str_contains($contentType, 'application/json')
            || str_starts_with($_SERVER['REQUEST_URI'] ?? '/', '/api/');
    }
}
