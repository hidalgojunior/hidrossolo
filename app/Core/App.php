<?php

declare(strict_types=1);

namespace App\Core;

use Dotenv\Dotenv;
use App\Core\Router;
use App\Core\Database;
use App\Core\ExceptionHandler;
use App\Core\Logger;
use App\Core\View;
use App\Middleware\CsrfMiddleware;

class App
{
    private static ?App $instance = null;
    private Router $router;
    private Database $db;
    private array $config = [];

    private function __construct()
    {
        // Carregar variáveis de ambiente
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->safeLoad();

        // Registrar exception handler global
        $handler = new ExceptionHandler();
        $handler->register();

        // Configurar timezone e locale brasileiro
        date_default_timezone_set('America/Sao_Paulo');
        setlocale(LC_ALL, 'pt_BR.UTF-8', 'pt_BR', 'Portuguese_Brazil', 'C.UTF-8');
        mb_internal_encoding('UTF-8');

        // Inicializar componentes
        $this->router = new Router();
        $this->db = new Database();

        // Motor de views em PHP puro (sem Blade/Laravel)
        View::setBasePath(dirname(__DIR__, 2) . '/views');

        // Carregar configurações
        $this->loadConfig();

        // Compartilhar dados globais com views
        $this->shareViewData();

        Logger::info('App inicializada', ['env' => $_ENV['APP_ENV'] ?? 'production']);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function run(): void
    {
        $this->router->dispatch();
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getDb(): Database
    {
        return $this->db;
    }

    public function getConfig(string $key = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }
        return $this->config[$key] ?? null;
    }

    private function loadConfig(): void
    {
        $configFiles = glob(dirname(__DIR__, 2) . '/config/*.php');
        foreach ($configFiles as $file) {
            $key = basename($file, '.php');
            $this->config[$key] = require $file;
        }
    }

    private function shareViewData(): void
    {
        View::share('app_name', $_ENV['APP_NAME'] ?? 'Hidrossolo');
        View::share('app_url', $_ENV['APP_URL'] ?? 'http://localhost:8080');
        View::share('app_env', $_ENV['APP_ENV'] ?? 'production');
        View::share('csrf_token', CsrfMiddleware::token());
        View::share('csrf_field', CsrfMiddleware::field());

        // Logo configurável (busca do site_settings ou usa default)
        try {
            $logo = $this->db->fetch("SELECT `value` FROM site_settings WHERE `key` = 'site_logo'");
            View::share('logo', $logo['value'] ?? '/assets/images/hidrossolo.png');
        } catch (\Throwable) {
            View::share('logo', '/assets/images/hidrossolo.png');
        }

        // Contador de notificações para o topbar
        try {
            $notif = $this->db->fetch("SELECT COUNT(*) as c FROM notifications WHERE read_at IS NULL");
            View::share('unread_notifications', (int)($notif['c'] ?? 0));
        } catch (\Throwable) {
            View::share('unread_notifications', 0);
        }
    }

    // Evitar clonagem
    private function __clone() {}
}
