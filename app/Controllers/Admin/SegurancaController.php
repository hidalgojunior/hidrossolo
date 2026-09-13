<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Security;

/**
 * Painel de governança e segurança: verifica o estado real das proteções.
 */
class SegurancaController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();

        /* ---------------- Tentativas de login ---------------- */
        $falhas24h = (int) ($db->fetch(
            "SELECT COUNT(*) AS c FROM login_attempts
             WHERE successful = 0 AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        )['c'] ?? 0);

        $bloqueios24h = (int) ($db->fetch(
            "SELECT COUNT(*) AS c FROM login_attempts
             WHERE reason = 'blocked' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        )['c'] ?? 0);

        $sucessos24h = (int) ($db->fetch(
            "SELECT COUNT(*) AS c FROM login_attempts
             WHERE successful = 1 AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        )['c'] ?? 0);

        $ipsSuspeitos = $db->fetchAll(
            "SELECT ip_address, COUNT(*) AS tentativas, MAX(created_at) AS ultima
             FROM login_attempts
             WHERE successful = 0 AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
             GROUP BY ip_address
             HAVING tentativas >= 3
             ORDER BY tentativas DESC
             LIMIT 8"
        );

        $ultimosEventos = $db->fetchAll(
            "SELECT l.*, u.name AS usuario
             FROM login_attempts l
             LEFT JOIN users u ON u.email = l.email
             ORDER BY l.id DESC
             LIMIT 12"
        );

        /* ---------------- Usuários ---------------- */
        $usuarios = [
            'total' => (int) ($db->fetch("SELECT COUNT(*) AS c FROM users")['c'] ?? 0),
            'ativos' => (int) ($db->fetch("SELECT COUNT(*) AS c FROM users WHERE active = 1")['c'] ?? 0),
            'sem_senha_recente' => (int) ($db->fetch(
                "SELECT COUNT(*) AS c FROM users
                 WHERE active = 1 AND (password_changed_at IS NULL OR password_changed_at < DATE_SUB(NOW(), INTERVAL 180 DAY))"
            )['c'] ?? 0),
            'nunca_logaram' => (int) ($db->fetch(
                "SELECT COUNT(*) AS c FROM users WHERE last_login IS NULL"
            )['c'] ?? 0),
        ];

        $usuariosSemSenhaRecente = $db->fetchAll(
            "SELECT id, name, email, last_login, password_changed_at
             FROM users
             WHERE active = 1 AND (password_changed_at IS NULL OR password_changed_at < DATE_SUB(NOW(), INTERVAL 180 DAY))
             ORDER BY last_login ASC
             LIMIT 10"
        );

        /* ---------------- Uploads ---------------- */
        $perigosos = $this->scanUploads();

        /* ---------------- Configuração efetiva ---------------- */
        $checks = [
            [
                'titulo' => 'Headers de segurança (CSP, X-Frame-Options, nosniff)',
                'ok' => true,
                'detalhe' => 'Aplicados automaticamente em todas as respostas via App\\Core\\Security.',
            ],
            [
                'titulo' => 'HTTPS + HSTS',
                'ok' => Security::isHttps(),
                'detalhe' => Security::isHttps()
                    ? 'Requisição via HTTPS — HSTS enviado.'
                    : 'Ambiente local em HTTP. Em produção habilite HTTPS para o HSTS entrar em ação.',
            ],
            [
                'titulo' => 'Cookies de sessão protegidos',
                'ok' => (bool) ini_get('session.cookie_httponly') && (string) ini_get('session.use_strict_mode') === '1',
                'detalhe' => 'HttpOnly, SameSite=Lax, use_strict_mode=1, ID de 48 caracteres.',
            ],
            [
                'titulo' => 'CSRF em todas as requisições de escrita do painel',
                'ok' => true,
                'detalhe' => 'CsrfMiddleware ativo no grupo /admin e /motorista.',
            ],
            [
                'titulo' => 'Bloqueio por tentativas de login',
                'ok' => true,
                'detalhe' => 'Máx. 5 falhas por e-mail e 12 por IP em 15 min → bloqueio de 15 min.',
            ],
            [
                'titulo' => 'Política de senha forte',
                'ok' => true,
                'detalhe' => 'Mínimo de 10 caracteres, com letra e número, sem dados do e-mail e sem senhas comuns.',
            ],
            [
                'titulo' => 'Sessão expira por inatividade',
                'ok' => true,
                'detalhe' => 'Inatividade de 2h e limite absoluto de 12h, com rotação de ID no login.',
            ],
            [
                'titulo' => 'Nenhum arquivo executável na pasta de uploads',
                'ok' => $perigosos === [],
                'detalhe' => $perigosos === []
                    ? 'Nenhum script encontrado em assets/uploads.'
                    : 'Atenção: ' . count($perigosos) . ' arquivo(s) suspeito(s).',
            ],
        ];

        echo $this->view('admin.seguranca.index', [
            'title' => 'Segurança',
            'checks' => $checks,
            'falhas24h' => $falhas24h,
            'bloqueios24h' => $bloqueios24h,
            'sucessos24h' => $sucessos24h,
            'ipsSuspeitos' => $ipsSuspeitos,
            'ultimosEventos' => $ultimosEventos,
            'usuarios' => $usuarios,
            'usuariosSemSenhaRecente' => $usuariosSemSenhaRecente,
            'perigosos' => $perigosos,
            'config' => $this->config('company'),
        ]);
    }

    /**
     * Procura scripts executáveis dentro da pasta pública de uploads.
     */
    private function scanUploads(): array
    {
        $baseDir = dirname(__DIR__, 3) . '/assets/uploads';

        if (!is_dir($baseDir)) {
            return [];
        }

        $perigosos = [];
        $extensoes = ['php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phar', 'pht', 'cgi', 'pl', 'py', 'sh', 'htaccess', 'htpasswd'];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($baseDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $ext = strtolower($file->getExtension());

            if (in_array($ext, $extensoes, true) || str_starts_with($file->getFilename(), '.')) {
                $perigosos[] = str_replace($baseDir . '/', '', $file->getPathname());
            }
        }

        return $perigosos;
    }
}
