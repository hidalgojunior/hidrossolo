<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Security;

/**
 * Páginas legais: Política de Privacidade, Cookies, Termos de Uso e Central LGPD.
 */
class LegalController extends BaseController
{
    /** Data da última revisão dos documentos. */
    public const UPDATED = '13/09/2026';
    public const VERSION = '2.0';

    public function privacidade(): void
    {
        $this->pagina('pages.legal.politica-privacidade', [
            'title' => 'Política de Privacidade',
            'subtitle' => 'Como a Hidrossolo trata seus dados pessoais em conformidade com a LGPD.',
            'slug' => 'politica-de-privacidade',
        ]);
    }

    public function cookies(): void
    {
        $this->pagina('pages.legal.politica-cookies', [
            'title' => 'Política de Cookies',
            'subtitle' => 'O que são cookies, quais usamos e como você pode gerenciá-los.',
            'slug' => 'politica-de-cookies',
        ]);
    }

    public function termos(): void
    {
        $this->pagina('pages.legal.termos-uso', [
            'title' => 'Termos de Uso',
            'subtitle' => 'Regras para utilização do site e dos serviços da Hidrossolo.',
            'slug' => 'termos-de-uso',
        ]);
    }

    public function lgpd(): void
    {
        $this->pagina('pages.legal.lgpd', [
            'title' => 'Central LGPD',
            'subtitle' => 'Transparência, direitos do titular e canal direto com o encarregado de dados.',
            'slug' => 'lgpd',
            'tipos' => self::tiposSolicitacao(),
            'protocolo' => $_SESSION['lgpd_protocolo'] ?? null,
        ]);

        unset($_SESSION['lgpd_protocolo']);
    }

    /**
     * Recebe uma solicitação de titular de dados (art. 18 da LGPD).
     */
    public function solicitacao(): void
    {
        $nome = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $documento = preg_replace('/[^0-9A-Za-z.\-\/]/', '', (string) ($_POST['document'] ?? ''));
        $telefone = trim((string) ($_POST['phone'] ?? ''));
        $tipo = trim((string) ($_POST['request_type'] ?? ''));
        $mensagem = trim((string) ($_POST['message'] ?? ''));
        $consentimento = isset($_POST['consent']);

        $tipos = array_keys(self::tiposSolicitacao());
        $erros = [];

        if (mb_strlen($nome) < 3) {
            $erros[] = 'Informe seu nome completo.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'Informe um e-mail válido.';
        }
        if (!in_array($tipo, $tipos, true)) {
            $erros[] = 'Selecione o tipo de solicitação.';
        }
        if ($mensagem === '') {
            $erros[] = 'Descreva sua solicitação.';
        }
        if (!$consentimento) {
            $erros[] = 'É necessário declarar que as informações estão corretas e autorizar o contato.';
        }

        // Verificação anti-robô simples (honeypot + tempo mínimo de preenchimento)
        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            $erros[] = 'Não foi possível processar a solicitação.';
        }

        if ($erros !== []) {
            $_SESSION['flash_error'] = implode(' ', $erros);
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/lgpd#solicitacao');
        }

        $protocolo = 'LGPD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        $id = $this->db()->insert('lgpd_requests', [
            'protocol' => $protocolo,
            'name' => mb_substr($nome, 0, 255),
            'email' => mb_substr($email, 0, 255),
            'document' => $documento !== '' ? mb_substr($documento, 0, 30) : null,
            'phone' => $telefone !== '' ? mb_substr($telefone, 0, 30) : null,
            'request_type' => $tipo,
            'message' => $mensagem,
            'consent' => 1,
            'ip_address' => Security::clientIp(),
            'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
        ]);

        Security::audit('lgpd_request_created', 'lgpd_requests', $id, ['tipo' => $tipo]);

        unset($_SESSION['old_input']);
        $_SESSION['lgpd_protocolo'] = $protocolo;
        $_SESSION['flash_success'] = 'Solicitação registrada! Seu protocolo é ' . $protocolo . '. Responderemos em até 15 dias.';

        $this->redirect('/lgpd#solicitacao');
    }

    /**
     * Tipos de solicitação do titular (LGPD, art. 18).
     *
     * @return array<string,string>
     */
    public static function tiposSolicitacao(): array
    {
        return [
            'confirmacao' => 'Confirmação da existência de tratamento',
            'acesso' => 'Acesso aos dados',
            'correcao' => 'Correção de dados incompletos, inexatos ou desatualizados',
            'anonimizacao' => 'Anonimização, bloqueio ou eliminação de dados desnecessários',
            'portabilidade' => 'Portabilidade dos dados a outro fornecedor',
            'eliminacao' => 'Eliminação dos dados tratados com consentimento',
            'compartilhamento' => 'Informação sobre compartilhamento de dados',
            'revogacao' => 'Revogação do consentimento',
            'oposicao' => 'Oposição ao tratamento',
            'outro' => 'Outro assunto relacionado à privacidade',
        ];
    }

    private function pagina(string $view, array $dados): void
    {
        $title = (string) $dados['title'];

        echo $this->view($view, array_merge($dados, [
            'config' => $this->config('company'),
            'atualizadoEm' => self::UPDATED,
            'versao' => self::VERSION,
            'seo' => [
                'title' => $title . ' | Hidrossolo',
                'description' => (string) ($dados['subtitle'] ?? $title) . ' Hidrossolo Poços Artesianos — Marília/SP.',
            ],
        ]));
    }
}
