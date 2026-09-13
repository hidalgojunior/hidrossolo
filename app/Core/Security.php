<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Núcleo de segurança e governança.
 *
 * Responsabilidades:
 *  - Headers de segurança (CSP, clickjacking, MIME sniffing, referrer...)
 *  - Hardening de sessão (cookies, idle timeout, rotação de ID)
 *  - Rate limiting / bloqueio progressivo de tentativas de login
 *  - Política de senha
 *  - Trilha de auditoria padronizada
 */
final class Security
{
    /**
     * Tamanho mínimo exigido para senhas (cadastro de usuários, troca de senha
     * e redefinição). A senha também precisa ter ao menos uma letra e um número.
     */
    public const PASSWORD_MIN_LENGTH = 6;
    /** Tempo máximo de inatividade da sessão (segundos) — 2 horas. */
    private const IDLE_TIMEOUT = 7200;

    /** Vida absoluta da sessão (segundos) — 12 horas. */
    private const ABSOLUTE_TIMEOUT = 43200;

    /** Tentativas de login permitidas antes do bloqueio. */
    private const MAX_ATTEMPTS_PER_EMAIL = 5;
    private const MAX_ATTEMPTS_PER_IP = 12;
    private const ATTEMPT_WINDOW_MINUTES = 15;
    private const BLOCK_MINUTES = 15;

    /* =====================================================================
     | Bootstrap
     * =================================================================== */

    /**
     * Prepara sessão (antes do session_start) e envia headers de segurança.
     */
    public static function boot(): void
    {
        self::hardenSession();
        self::sendHeaders();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        self::enforceSessionLifetime();
    }

    public static function isHttps(): bool
    {
        return (($_SERVER['HTTPS'] ?? '') === 'on')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
            || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
    }

    /**
     * Configura os parâmetros do cookie de sessão antes de iniciá-la.
     */
    public static function hardenSession(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.sid_length', '48');
        ini_set('session.sid_bits_per_character', '5');
        ini_set('session.gc_maxlifetime', (string) self::ABSOLUTE_TIMEOUT);
        ini_set('expose_php', '0');

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_name('hidrossolo_session');
    }

    /**
     * Headers de segurança aplicados a todas as respostas.
     */
    public static function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        // Não permite a página ser embutida em iframes (clickjacking/phishing)
        header('X-Frame-Options: SAMEORIGIN');

        // Impede sniffing de MIME
        header('X-Content-Type-Options: nosniff');

        // Referrer limitado a requisições same-origin
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Desliga APIs sensíveis do navegador que não usamos
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=()');

        // Isola a janela de navegação
        header('Cross-Origin-Opener-Policy: same-origin');

        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }

        // CSP: apenas origens necessárias ao próprio site.
        // 'unsafe-inline' é exigido pelos scripts/estilos inline do painel.
        $csp = implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
            "font-src 'self' data: https://cdn.jsdelivr.net https://fonts.gstatic.com",
            "img-src 'self' data: blob: https:",
            "connect-src 'self'",
            "frame-src 'self' https://www.google.com https://maps.google.com",
        ]);

        header('Content-Security-Policy: ' . $csp);
    }

    /**
     * Encerra sessões inativas ou expiradas.
     */
    private static function enforceSessionLifetime(): void
    {
        if (!isset($_SESSION['user_id'])) {
            return;
        }

        $now = time();
        $started = (int) ($_SESSION['_started_at'] ?? $now);
        $last = (int) ($_SESSION['_last_activity'] ?? $now);

        if (($now - $last) > self::IDLE_TIMEOUT || ($now - $started) > self::ABSOLUTE_TIMEOUT) {
            self::destroySession();
            return;
        }

        $_SESSION['_last_activity'] = $now;
    }

    public static function destroySession(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }

        session_destroy();
    }

    /**
     * Registra o início de uma sessão autenticada com rotação de ID.
     */
    public static function loginSession(array $user): void
    {
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_name'] = $user['name'] ?? '';
        $_SESSION['user_email'] = $user['email'] ?? '';
        $_SESSION['user_role'] = (int) ($user['role_id'] ?? 0);
        $_SESSION['_started_at'] = time();
        $_SESSION['_last_activity'] = time();
    }

    /* =====================================================================
     | Rate limiting de login
     * =================================================================== */

    /**
     * Quantos segundos faltam para liberar o login (0 = liberado).
     */
    public static function loginBlockedFor(string $email, string $ip): int
    {
        $db = self::db();

        $byEmail = $db->fetch(
            "SELECT COUNT(*) AS c, MAX(created_at) AS ultimo
             FROM login_attempts
             WHERE successful = 0 AND email = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)",
            [$email, self::ATTEMPT_WINDOW_MINUTES]
        );

        $byIp = $db->fetch(
            "SELECT COUNT(*) AS c, MAX(created_at) AS ultimo
             FROM login_attempts
             WHERE successful = 0 AND ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)",
            [$ip, self::ATTEMPT_WINDOW_MINUTES]
        );

        $exceeded = ((int) ($byEmail['c'] ?? 0) >= self::MAX_ATTEMPTS_PER_EMAIL)
            || ((int) ($byIp['c'] ?? 0) >= self::MAX_ATTEMPTS_PER_IP);

        if (!$exceeded) {
            return 0;
        }

        $last = strtotime((string) ($byEmail['ultimo'] ?? $byIp['ultimo'] ?? 'now'));
        $unlockAt = $last + (self::BLOCK_MINUTES * 60);

        return max(0, $unlockAt - time());
    }

    public static function recordLoginAttempt(string $email, string $ip, bool $successful, ?string $reason = null): void
    {
        try {
            self::db()->insert('login_attempts', [
                'email' => $email !== '' ? $email : null,
                'ip_address' => $ip,
                'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
                'successful' => $successful ? 1 : 0,
                'reason' => $reason,
            ]);
        } catch (\Throwable) {
            // Nunca falhar o login por causa do log
        }
    }

    public static function clearLoginAttempts(string $email, string $ip): void
    {
        try {
            self::db()->delete('login_attempts', 'successful = 0 AND (email = ? OR ip_address = ?)', [$email, $ip]);
        } catch (\Throwable) {
        }
    }

    public static function clientIp(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    /* =====================================================================
     | Política de senha
     * =================================================================== */

    /**
     * @return string[] Lista de problemas encontrados (vazia = senha aceitável)
     */
    public static function passwordIssues(string $password, string $email = ''): array
    {
        $issues = [];

        if (mb_strlen($password) < self::PASSWORD_MIN_LENGTH) {
            $issues[] = 'A senha deve ter ao menos ' . self::PASSWORD_MIN_LENGTH . ' caracteres.';
        }
        if (!preg_match('/[A-Za-zÀ-ÿ]/', $password)) {
            $issues[] = 'Inclua ao menos uma letra.';
        }
        if (!preg_match('/\d/', $password)) {
            $issues[] = 'Inclua ao menos um número.';
        }
        if ($email !== '' && stripos($password, (string) strstr($email, '@', true)) !== false) {
            $issues[] = 'A senha não pode conter parte do seu e-mail.';
        }
        if (preg_match('/^(.)\1+$/', $password)) {
            $issues[] = 'Evite repetir o mesmo caractere.';
        }
        if (in_array(strtolower($password), self::commonPasswords(), true)) {
            $issues[] = 'Essa senha é muito comum. Escolha outra.';
        }

        return $issues;
    }

    private static function commonPasswords(): array
    {
        return [
            '123456789', '1234567890', 'senha123456', 'password', 'password123',
            'qwerty12345', 'administrador', 'hidrossolo', 'admin123456', '1q2w3e4r5t',
            'abcd123456', 'senha12345',
        ];
    }

    /* =====================================================================
     | Auditoria
     * =================================================================== */

    public static function audit(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        array $newValues = [],
        ?int $userId = null
    ): void {
        try {
            self::db()->insert('audit_logs', [
                'user_id' => $userId ?? ($_SESSION['user_id'] ?? null),
                'action' => mb_substr($action, 0, 100),
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'new_values' => $newValues === [] ? null : json_encode($newValues, JSON_UNESCAPED_UNICODE),
                'ip_address' => self::clientIp(),
                'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
            ]);
        } catch (\Throwable) {
        }
    }

    private static function db(): Database
    {
        return App::getInstance()->getDb();
    }
}
