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

        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        // Permite acessar chaves internas de um arquivo de configuração
        // (ex.: 'company', que vive dentro de config/app.php).
        foreach ($this->config as $fileConfig) {
            if (is_array($fileConfig) && array_key_exists($key, $fileConfig)) {
                return $fileConfig[$key];
            }
        }

        return null;
    }

    /**
     * Dados institucionais de contato exibidos no site.
     *
     * Fonte única de verdade (na ordem de prioridade):
     *   1. tabela site_settings, grupo 'contato' (o que o admin salva em
     *      Admin -> CMS -> Contato);
     *   2. config/app.php (chave 'company');
     *   3. valores padrão abaixo.
     *
     * A página de contato, o rodapé e o JSON-LD consomem este mesmo array
     * para nunca divergirem entre si.
     */
    public function companyInfo(): array
    {
        $info = [
            'name'          => 'Hidrossolo Poços Artesianos',
            'address'       => 'R. Assad Haddad, 584 - Parque das Indústrias',
            'city'          => 'Marília',
            'state'         => 'SP',
            'zip'           => '17519-700',
            'phone'         => '(14) 3413-2437',
            'whatsapp'      => '(14) 99123-4567',
            'email'         => 'hidrossolo@hidrossolopocos.com.br',
            'working_hours' => 'Seg a Sex: 08h às 18h | Sáb: 08h às 12h',
            'form_title'    => 'Envie sua Mensagem',
            'form_text'     => 'Entre em contato e solicite seu orçamento',

            // Redes sociais (Admin -> CMS -> Contato). Vazio = não exibido.
            'social_instagram' => '',
            'social_facebook'  => '',
            'social_youtube'   => '',
            'social_linkedin'  => '',
            'social_tiktok'    => '',
            'social_x'         => '',
            'social_outras'    => '',
        ];

        $arquivo = $this->config['app']['company'] ?? null;
        if (is_array($arquivo)) {
            foreach ($arquivo as $chave => $valor) {
                if (is_scalar($valor) && trim((string) $valor) !== '') {
                    $info[$chave] = (string) $valor;
                }
            }
        }

        try {
            $settings = $this->db->fetchAll(
                "SELECT `key`, `value` FROM site_settings WHERE `group` = 'contato'"
            );

            foreach ($settings as $setting) {
                $chave = (string) ($setting['key'] ?? '');
                $valor = trim((string) ($setting['value'] ?? ''));

                if ($valor !== '' && array_key_exists($chave, $info)) {
                    $info[$chave] = $valor;
                }
            }
        } catch (\Throwable) {
            // Banco indisponível: mantém os valores do arquivo de configuração.
        }

        return $info;
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

        // Dados de contato (fonte única para contato, rodapé e JSON-LD)
        View::share('company', $this->companyInfo());

        // E-mail do encarregado de dados (Admin -> CMS -> LGPD).
        // O campo existia no painel mas as páginas legais ignoravam.
        try {
            $lgpdEmail = $this->db->fetch("SELECT `value` FROM site_settings WHERE `key` = 'lgpd_contact_email'");
            View::share('lgpd_email', trim((string) ($lgpdEmail['value'] ?? '')));
        } catch (\Throwable) {
            View::share('lgpd_email', '');
        }

        // Logo configurável (busca do site_settings ou usa default)
        try {
            $logo = $this->db->fetch("SELECT `value` FROM site_settings WHERE `key` = 'site_logo'");
            View::share('logo', $logo['value'] ?? '/assets/images/hidrossolo.png');
        } catch (\Throwable) {
            View::share('logo', '/assets/images/hidrossolo.png');
        }

        // Contador de notificações para o topbar (avisos globais + do usuário)
        try {
            $userId = (int) ($_SESSION['user_id'] ?? 0);

            $notif = $userId > 0
                ? $this->db->fetch(
                    "SELECT COUNT(*) AS c FROM notifications WHERE read_at IS NULL AND (user_id IS NULL OR user_id = ?)",
                    [$userId]
                )
                : $this->db->fetch("SELECT COUNT(*) AS c FROM notifications WHERE read_at IS NULL AND user_id IS NULL");

            View::share('unread_notifications', (int)($notif['c'] ?? 0));
        } catch (\Throwable) {
            View::share('unread_notifications', 0);
        }
    }

    // Evitar clonagem
    private function __clone() {}
}
