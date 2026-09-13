<?php

declare(strict_types=1);

namespace App\Core;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;

class Logger
{
    private static ?MonologLogger $instance = null;

    public static function getInstance(): MonologLogger
    {
        if (self::$instance === null) {
            self::$instance = new MonologLogger('hidrossolo');

            $logDir = dirname(__DIR__, 2) . '/logs';

            // Sempre loga no stdout (garantia de funcionamento)
            self::$instance->pushHandler(new StreamHandler('php://stdout', Level::Debug));

            // Tenta log em arquivo, falha silenciosamente se sem permissão
            try {
                if (!is_dir($logDir)) {
                    @mkdir($logDir, 0775, true);
                }

                if (is_dir($logDir) && is_writable($logDir)) {
                    self::$instance->pushHandler(
                        new RotatingFileHandler($logDir . '/app.log', 7, Level::Debug)
                    );
                    self::$instance->pushHandler(
                        new RotatingFileHandler($logDir . '/error.log', 30, Level::Error)
                    );
                }
            } catch (\Throwable) {
                // Silent fail — stdout já está garantido
            }
        }

        return self::$instance;
    }

    /**
     * Atalhos para níveis de log — nunca lançam exceção.
     */
    public static function debug(string $message, array $context = []): void
    {
        try { self::getInstance()->debug($message, $context); } catch (\Throwable) {}
    }

    public static function info(string $message, array $context = []): void
    {
        try { self::getInstance()->info($message, $context); } catch (\Throwable) {}
    }

    public static function warning(string $message, array $context = []): void
    {
        try { self::getInstance()->warning($message, $context); } catch (\Throwable) {}
    }

    public static function error(string $message, array $context = []): void
    {
        try { self::getInstance()->error($message, $context); } catch (\Throwable) {}
    }

    public static function critical(string $message, array $context = []): void
    {
        try { self::getInstance()->critical($message, $context); } catch (\Throwable) {}
    }
}
