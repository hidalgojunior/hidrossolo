<?php

declare(strict_types=1);

namespace App\Core;

abstract class BaseController
{
    protected function view(string $template, array $data = []): string
    {
        return App::getInstance()->getBlade()->render($template, $data);
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    protected function redirect(string $url, int $status = 302): void
    {
        header("Location: {$url}", true, $status);
        exit;
    }

    protected function back(): void
    {
        $url = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($url);
    }

    protected function db(): Database
    {
        return App::getInstance()->getDb();
    }

    protected function config(string $key = null): mixed
    {
        return App::getInstance()->getConfig($key);
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function all(): array
    {
        return $_POST + $_GET;
    }

    /**
     * Valida os dados da requisição com regras no formato:
     * 'campo' => 'required|min:3|max:255|email'
     *
     * Regras disponíveis: required, email, min:N, max:N, numeric, integer,
     * string, date:format, url, phone, confirmed, in:val1,val2, cpf
     */
    protected function validate(array $rules): array
    {
        $validator = new Validator($_POST + $_GET);

        if (!$validator->validate($rules)) {
            $_SESSION['validation_errors'] = $validator->errors();
            $_SESSION['old_input'] = $_POST;
            $this->back();
        }

        // Retorna apenas os dados validados
        return array_intersect_key($_POST, $rules);
    }

    /**
     * Retorna erros de validação da sessão (flash messages).
     */
    protected function validationErrors(): array
    {
        $errors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['validation_errors']);
        return $errors;
    }

    /**
     * Retorna input antigo da sessão (flash).
     */
    protected function old(string $key, mixed $default = ''): mixed
    {
        $old = $_SESSION['old_input'][$key] ?? $default;
        unset($_SESSION['old_input'][$key]);
        return $old;
    }

    protected function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
}
