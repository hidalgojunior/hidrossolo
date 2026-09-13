<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\App;
use App\Core\BaseMiddleware;

class MaintenanceMiddleware extends BaseMiddleware
{
    public function handle(): bool
    {
        // Rotas de admin e login sempre acessíveis
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        if (str_starts_with($uri, '/admin')) {
            return true;
        }

        // Verificar se modo manutenção está ativo
        try {
            $db = App::getInstance()->getDb();
            $setting = $db->fetch("SELECT `value` FROM site_settings WHERE `key` = 'maintenance_mode'");
            $enabled = ($setting['value'] ?? '0') === '1';
        } catch (\Throwable) {
            $enabled = false;
        }

        if (!$enabled) {
            return true;
        }

        // Usuários logados (admin) podem navegar normalmente
        if (!empty($_SESSION['user_id'])) {
            return true;
        }

        // Exibir página de manutenção
        http_response_code(503);
        echo App::getInstance()->getBlade()->render('pages.maintenance', [
            'message' => $db->fetch("SELECT `value` FROM site_settings WHERE `key` = 'maintenance_message'")['value'] ?? 'Estamos em manutenção. Voltaremos em breve!',
        ]);
        exit;
    }
}
