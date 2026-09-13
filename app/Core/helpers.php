<?php

declare(strict_types=1);

/**
 * Helpers globais dos templates (PHP puro).
 *
 * Este arquivo é carregado automaticamente pelo motor de views (App\Core\View)
 * e fornece as funções utilitárias usadas nas views, sem qualquer dependência
 * de framework.
 */

use App\Middleware\CsrfMiddleware;

if (!function_exists('e')) {
    /**
     * Escapa uma string para exibição segura em HTML.
     */
    function e(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('str_limit')) {
    /**
     * Limita o tamanho de um texto, adicionando reticências quando necessário.
     */
    function str_limit(mixed $value, int $limit = 100, string $end = '...'): string
    {
        $text = trim((string) ($value ?? ''));

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, max(0, $limit - mb_strlen($end)))) . $end;
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Retorna o token CSRF da sessão atual.
     */
    function csrf_token(): string
    {
        return CsrfMiddleware::token();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Retorna o campo hidden com o token CSRF.
     */
    function csrf_field(): string
    {
        return CsrfMiddleware::field();
    }
}

if (!function_exists('old')) {
    /**
     * Recupera o valor antigo de um campo de formulário (após validação falhar).
     */
    function old(string $key, mixed $default = ''): mixed
    {
        $value = $_SESSION['_old'][$key] ?? $default;
        return is_string($value) ? htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : $value;
    }
}

if (!function_exists('flash')) {
    /**
     * Consome e retorna uma mensagem flash da sessão.
     */
    function flash(string $key): ?string
    {
        $message = $_SESSION['flash_' . $key] ?? null;
        unset($_SESSION['flash_' . $key]);

        return $message;
    }
}

if (!function_exists('asset')) {
    /**
     * Monta a URL de um asset estático.
     */
    function asset(string $path): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('json_attr')) {
    /**
     * Codifica dados para uso seguro dentro de atributos/scripts.
     */
    function json_attr(mixed $value): string
    {
        return json_encode(
            $value,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        ) ?: '[]';
    }
}
