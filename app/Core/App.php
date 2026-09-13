<?php

declare(strict_types=1);

namespace App\Core;

use Dotenv\Dotenv;
use Jenssegers\Blade\Blade;
use App\Core\Router;
use App\Core\Database;
use App\Core\ExceptionHandler;
use App\Core\Logger;
use App\Middleware\CsrfMiddleware;
use Illuminate\Support\Str;

class App
{
    private static ?App $instance = null;
    private Router $router;
    private Blade $blade;
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
        $this->blade = new Blade(
            dirname(__DIR__, 2) . '/views',
            dirname(__DIR__, 2) . '/cache'
        );

        // Registrar aliases para uso nas views Blade
        if (!class_exists('Str')) {
            class_alias(Str::class, 'Str');
        }

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

    public function getBlade(): Blade
    {
        return $this->blade;
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
        $this->blade->share('app_name', $_ENV['APP_NAME'] ?? 'Hidrossolo');
        $this->blade->share('app_url', $_ENV['APP_URL'] ?? 'http://localhost:8080');
        $this->blade->share('app_env', $_ENV['APP_ENV'] ?? 'production');
        $this->blade->share('csrf_token', CsrfMiddleware::token());
        $this->blade->share('csrf_field', CsrfMiddleware::field());

        // Logo configurável (busca do site_settings ou usa default)
        try {
            $logo = $this->db->fetch("SELECT `value` FROM site_settings WHERE `key` = 'site_logo'");
            $this->blade->share('logo', $logo['value'] ?? '/assets/images/hidrossolo.png');
        } catch (\Throwable) {
            $this->blade->share('logo', '/assets/images/hidrossolo.png');
        }

        // Contador de notificações para o topbar
        try {
            $notif = $this->db->fetch("SELECT COUNT(*) as c FROM notifications WHERE read_at IS NULL");
            $this->blade->share('unread_notifications', (int)($notif['c'] ?? 0));
        } catch (\Throwable) {
            $this->blade->share('unread_notifications', 0);
        }

        // Registrar diretiva Blade @csrf
        $this->blade->directive('csrf', function () {
            return '<?php echo \App\Middleware\CsrfMiddleware::field(); ?>';
        });
    }

    // Evitar clonagem
    private function __clone() {}
}
