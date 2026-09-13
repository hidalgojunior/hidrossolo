<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

class ExceptionHandler
{
    private bool $debug;

    public function __construct(?bool $debug = null)
    {
        $this->debug = $debug ?? filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Registra os handlers globais.
     */
    public function register(): void
    {
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Lida com exceções não capturadas.
     */
    public function handleException(Throwable $e): void
    {
        try {
            Logger::error($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        } catch (\Throwable) {}

        // Limpar buffer de saída
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!headers_sent()) {
            http_response_code(500);
        }

        if ($this->debug) {
            $this->renderDebug($e);
        } else {
            $this->renderProduction();
        }

        exit(1);
    }

    /**
     * Converte erros PHP em exceções.
     */
    public function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Lida com fatal errors no shutdown.
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            try {
                Logger::critical('Fatal Error: ' . $error['message'], [
                    'file' => $error['file'],
                    'line' => $error['line'],
                ]);
            } catch (\Throwable) {}

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            if (!headers_sent()) {
                http_response_code(500);
            }

            if ($this->debug) {
                echo "<h1>Fatal Error</h1>";
                echo "<pre>" . htmlspecialchars($error['message']) . "</pre>";
                echo "<p>File: " . htmlspecialchars($error['file']) . ":" . $error['line'] . "</p>";
            } else {
                echo $this->productionTemplate();
            }
        }
    }

    /**
     * Tela de erro em desenvolvimento.
     */
    private function renderDebug(Throwable $e): void
    {
        $title = get_class($e);
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = $e->getTraceAsString();

        // Se for requisição AJAX/API, retorna JSON
        if ($this->isApiRequest()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => $title,
                'message' => $message,
                'file' => $file,
                'line' => $line,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return;
        }

        echo <<<HTML
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <title>Erro: {$title}</title>
            <style>
                *{margin:0;padding:0;box-sizing:border-box}
                body{font-family:monospace;background:#1e1e2e;color:#cdd6f4;padding:2rem}
                .error-box{background:#313244;border-radius:12px;padding:2rem;max-width:960px;margin:0 auto}
                .error-type{color:#f38ba8;font-size:1.5rem;font-weight:bold;margin-bottom:.5rem}
                .error-msg{color:#f9e2af;font-size:1.1rem;margin-bottom:1rem;word-wrap:break-word}
                .error-loc{color:#a6adc8;margin-bottom:1.5rem}
                .error-trace{background:#181825;border-radius:8px;padding:1rem;overflow-x:auto;white-space:pre-wrap;font-size:.85rem;color:#a6e3a1}
                h2{color:#89b4fa;margin-bottom:.5rem}
            </style>
        </head>
        <body>
            <div class="error-box">
                <div class="error-type">{$title}</div>
                <div class="error-msg">{$message}</div>
                <div class="error-loc">📁 {$file} : {$line}</div>
                <h2>Stack Trace</h2>
                <div class="error-trace">{$trace}</div>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Tela de erro em produção (amigável).
     */
    private function renderProduction(): void
    {
        if ($this->isApiRequest()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => 'Erro interno do servidor.',
                'message' => 'Tente novamente mais tarde.',
            ]);
            return;
        }

        echo $this->productionTemplate();
    }

    private function productionTemplate(): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Erro - Hidrossolo</title>
            <style>
                *{margin:0;padding:0;box-sizing:border-box}
                body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#1e40af,#3b82f6);min-height:100vh;display:flex;align-items:center;justify-content:center}
                .container{background:rgba(255,255,255,.95);border-radius:16px;padding:3rem;text-align:center;max-width:480px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2)}
                h1{font-size:4rem;color:#1e40af;margin-bottom:.5rem}
                h2{color:#1f2937;margin-bottom:1rem}
                p{color:#6b7280;margin-bottom:2rem;line-height:1.6}
                a{display:inline-block;background:#1e40af;color:#fff;padding:12px 32px;border-radius:50px;text-decoration:none;font-weight:600;transition:background .3s}
                a:hover{background:#1e3a8a}
            </style>
        </head>
        <body>
            <div class="container">
                <h1>500</h1>
                <h2>Erro Interno</h2>
                <p>Ocorreu um erro inesperado. Nossa equipe já foi notificada. Por favor, tente novamente em alguns instantes.</p>
                <a href="/">Voltar ao Início</a>
            </div>
        </body>
        </html>
        HTML;
    }

    private function isApiRequest(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        return str_contains($accept, 'application/json')
            || str_contains($contentType, 'application/json')
            || str_starts_with($_SERVER['REQUEST_URI'] ?? '/', '/api/');
    }
}
