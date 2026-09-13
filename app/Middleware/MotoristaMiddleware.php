<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\App;
use App\Core\BaseMiddleware;
use App\Core\Security;

/**
 * Restringe o acesso à área do motorista.
 *
 * Permitido: motorista (dono da área) e admin/superadmin (suporte).
 * Demais papéis são devolvidos ao painel administrativo.
 */
class MotoristaMiddleware extends BaseMiddleware
{
    private const ALLOWED = ['motorista', 'admin', 'superadmin'];

    public function handle(): bool
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /admin/login');
            exit;
        }

        $user = App::getInstance()->getDb()->fetch(
            "SELECT u.active, r.name AS role_name
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE u.id = ?",
            [$_SESSION['user_id']]
        );

        if (!$user || !$user['active']) {
            Security::destroySession();
            header('Location: /admin/login');
            exit;
        }

        $role = (string) ($user['role_name'] ?? '');

        if (!in_array($role, self::ALLOWED, true)) {
            $_SESSION['flash_error'] = 'Você não tem acesso à área do motorista.';
            header('Location: /admin');
            exit;
        }

        $_SESSION['user_role_name'] = $role;

        return true;
    }
}
