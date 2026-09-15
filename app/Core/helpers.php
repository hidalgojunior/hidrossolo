<?php

declare(strict_types=1);

/**
 * Helpers globais dos templates (PHP puro).
 *
 * Este arquivo é carregado automaticamente pelo motor de views (App\Core\View)
 * e fornece as funções utilitárias usadas nas views, sem qualquer dependência
 * de framework.
 */

use App\Middleware\CsrfMiddleware;

if (!function_exists('e')) {
    /**
     * Escapa uma string para exibição segura em HTML.
     */
    function e(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('str_limit')) {
    /**
     * Limita o tamanho de um texto, adicionando reticências quando necessário.
     */
    function str_limit(mixed $value, int $limit = 100, string $end = '...'): string
    {
        $text = trim((string) ($value ?? ''));

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, max(0, $limit - mb_strlen($end)))) . $end;
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Retorna o token CSRF da sessão atual.
     */
    function csrf_token(): string
    {
        return CsrfMiddleware::token();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Retorna o campo hidden com o token CSRF.
     */
    function csrf_field(): string
    {
        return CsrfMiddleware::field();
    }
}

if (!function_exists('old')) {
    /**
     * Recupera o valor antigo de um campo de formulário (após validação falhar).
     */
    function old(string $key, mixed $default = ''): mixed
    {
        $value = $_SESSION['_old'][$key] ?? $default;
        return is_string($value) ? htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : $value;
    }
}

if (!function_exists('flash')) {
    /**
     * Consome e retorna uma mensagem flash da sessão.
     */
    function flash(string $key): ?string
    {
        $message = $_SESSION['flash_' . $key] ?? null;
        unset($_SESSION['flash_' . $key]);

        return $message;
    }
}

if (!function_exists('asset')) {
    /**
     * Monta a URL de um asset estático.
     */
    function asset(string $path): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset_v')) {
    /**
     * URL de um asset com versionamento por data de modificação,
     * evitando cache antigo de CSS/JS no navegador.
     */
    function asset_v(string $path): string
    {
        $url = '/' . ltrim($path, '/');
        $file = dirname(__DIR__, 2) . $url;

        return is_file($file) ? $url . '?v=' . filemtime($file) : $url;
    }
}

if (!function_exists('json_attr')) {    /**
     * Codifica dados para uso seguro dentro de atributos/scripts.
     */
    function json_attr(mixed $value): string
    {
        return json_encode(
            $value,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        ) ?: '[]';
    }
}

if (!function_exists('company_address_lines')) {
    /**
     * Quebra o endereço institucional nas mesmas linhas em qualquer view,
     * garantindo que contato e rodapé exibam exatamente o mesmo texto.
     *
     * @param array<string, mixed> $company Dados vindos de App::companyInfo().
     * @return list<string>
     */
    function company_address_lines(array $company): array
    {
        $linhas = [];

        $logradouro = trim((string) ($company['address'] ?? ''));
        if ($logradouro !== '') {
            $linhas[] = $logradouro;
        }

        $cidade = trim((string) ($company['city'] ?? ''));
        $uf = trim((string) ($company['state'] ?? ''));
        $linhaCidade = $cidade . ($uf !== '' ? ' - ' . $uf : '');

        // O campo "Endereço" do CMS é livre e o admin costuma digitar tudo numa
        // linha só. Nesse caso a cidade/UF já aparece acima e não é repetida.
        $cidadeJaInformada = $cidade !== '' && mb_stripos($logradouro, $cidade) !== false;

        if (!$cidadeJaInformada && trim($linhaCidade, ' -') !== '') {
            $linhas[] = $linhaCidade;
        }

        $cep = trim((string) ($company['zip'] ?? ''));
        if ($cep !== '' && ($logradouro === '' || mb_stripos($logradouro, $cep) === false)) {
            $linhas[] = 'CEP: ' . $cep;
        }

        return $linhas;
    }
}

if (!function_exists('company_lines')) {
    /**
     * Quebra um campo de contato em linhas. O separador pode ser "|" ou uma
     * quebra de linha — o admin costuma cadastrar dois telefones ou dois
     * horários numa linha só.
     *
     * Usado tanto pela página de contato quanto pelo rodapé, para que os dois
     * exibam exatamente o mesmo texto.
     *
     * @return list<string>
     */
    function company_lines(mixed $valor): array
    {
        $texto = trim((string) $valor);

        if ($texto === '') {
            return [];
        }

        $linhas = array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n|\|/', $texto) ?: []),
            static fn (string $linha): bool => $linha !== ''
        ));

        return $linhas !== [] ? $linhas : [$texto];
    }
}

if (!function_exists('company_whatsapp_link')) {
    /**
     * Monta o link do WhatsApp (wa.me) a partir do telefone institucional,
     * aceitando formatos como "(14) 99123-4567".
     */
    function company_whatsapp_link(mixed $numero, string $texto = ''): string
    {
        $digitos = preg_replace('/\D+/', '', (string) $numero) ?? '';

        if ($digitos === '') {
            return 'https://wa.me/';
        }

        if (!str_starts_with($digitos, '55')) {
            $digitos = '55' . $digitos;
        }

        $url = 'https://wa.me/' . $digitos;

        return $texto !== '' ? $url . '?text=' . rawurlencode($texto) : $url;
    }
}

if (!function_exists('company_tel_link')) {
    /**
     * Monta o link de discagem (tel:) a partir de um telefone institucional,
     * aceitando formatos como "(14) 3413-7789".
     */
    function company_tel_link(mixed $telefone): string
    {
        $digitos = preg_replace('/\D+/', '', (string) $telefone) ?? '';

        if ($digitos === '') {
            return '';
        }

        if (!str_starts_with($digitos, '55')) {
            $digitos = '55' . $digitos;
        }

        return 'tel:+' . $digitos;
    }
}

if (!function_exists('company_maps_link')) {
    /**
     * Link do Google Maps para o endereço institucional. No celular abre o app
     * de mapas já pronto para traçar a rota (GPS).
     *
     * @param array<string, mixed> $company Dados vindos de App::companyInfo().
     */
    function company_maps_link(array $company, string $consulta = ''): string
    {
        $alvo = trim($consulta) !== ''
            ? trim($consulta)
            : implode(', ', company_address_lines($company));

        return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($alvo);
    }
}

if (!function_exists('data_br_para_iso')) {
    /**
     * Converte uma data digitada no padrao brasileiro (dd/mm/aaaa) para o
     * formato do banco (aaaa-mm-dd). Tambem aceita aaaa-mm-dd, para nao
     * quebrar valores vindos de campos nativos ou de integracoes.
     *
     * Retorna null quando a data estiver vazia ou for invalida.
     */
    function data_br_para_iso(mixed $valor): ?string
    {
        $texto = trim((string) $valor);

        if ($texto === '') {
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $texto, $partes) === 1) {
            return checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1]) ? $texto : null;
        }

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $texto, $partes) !== 1) {
            return null;
        }

        $dia = (int) $partes[1];
        $mes = (int) $partes[2];
        $ano = (int) $partes[3];

        return checkdate($mes, $dia, $ano)
            ? sprintf('%04d-%02d-%02d', $ano, $mes, $dia)
            : null;
    }
}

if (!function_exists('data_iso_para_br')) {
    /**
     * Converte uma data do banco (aaaa-mm-dd) para exibicao (dd/mm/aaaa).
     * O fuso da aplicacao e America/Sao_Paulo (definido em App::__construct).
     */
    function data_iso_para_br(mixed $valor): string
    {
        $texto = trim((string) $valor);

        if ($texto === '' || str_starts_with($texto, '0000-00-00')) {
            return '';
        }

        $timestamp = strtotime($texto);

        return $timestamp === false ? '' : date('d/m/Y', $timestamp);
    }
}

if (!function_exists('company_social_links')) {
    /**
     * Redes sociais da empresa para exibicao no site.
     *
     * A ordem segue a lista abaixo; o que estiver vazio nao e exibido.
     * Alem das redes conhecidas, o campo `social_outras` aceita uma por linha
     * no formato "Nome | endereco".
     *
     * @param array<string, mixed> $company Dados vindos de App::companyInfo().
     * @return list<array{nome:string,url:string,icone:string}>
     */
    function company_social_links(array $company): array
    {
        $conhecidas = [
            ['chave' => 'social_instagram', 'nome' => 'Instagram',    'icone' => 'bi-instagram'],
            ['chave' => 'social_facebook',  'nome' => 'Facebook',     'icone' => 'bi-facebook'],
            ['chave' => 'social_youtube',   'nome' => 'YouTube',      'icone' => 'bi-youtube'],
            ['chave' => 'social_linkedin',  'nome' => 'LinkedIn',     'icone' => 'bi-linkedin'],
            ['chave' => 'social_tiktok',    'nome' => 'TikTok',       'icone' => 'bi-tiktok'],
            ['chave' => 'social_x',         'nome' => 'X (Twitter)',  'icone' => 'bi-twitter-x'],
        ];

        $links = [];

        foreach ($conhecidas as $rede) {
            $url = company_social_url((string) ($company[$rede['chave']] ?? ''));

            if ($url !== '') {
                $links[] = ['nome' => $rede['nome'], 'url' => $url, 'icone' => $rede['icone']];
            }
        }

        // Redes livres: "Nome | endereco", uma por linha.
        // A quebra e somente por linha (nao usar company_lines(): ele tambem
        // divide por "|", que aqui e o separador entre nome e endereco).
        $outras = preg_split('/\r\n|\r|\n/', trim((string) ($company['social_outras'] ?? ''))) ?: [];

        foreach ($outras as $linha) {
            $partes = array_map('trim', explode('|', $linha, 2));

            if (count($partes) !== 2 || $partes[0] === '' || $partes[1] === '') {
                continue;
            }

            $url = company_social_url($partes[1]);

            if ($url === '') {
                continue;
            }

            $links[] = ['nome' => $partes[0], 'url' => $url, 'icone' => company_social_icon($partes[1])];
        }

        return $links;
    }
}

if (!function_exists('company_social_url')) {
    /**
     * Normaliza o endereco informado no CMS: aceita "instagram.com/x" e
     * devolve "https://instagram.com/x". Retorna string vazia quando o valor
     * nao for um endereco http(s) valido — o que evita javascript: e afins.
     */
    function company_social_url(string $valor): string
    {
        $valor = trim($valor);

        if ($valor === '') {
            return '';
        }

        if (preg_match('~^https?://~i', $valor) !== 1) {
            // Aceita o dominio sem protocolo.
            if (preg_match('~^[\w-]+(\.[\w-]+)+([/?#].*)?$~', $valor) !== 1) {
                return '';
            }

            $valor = 'https://' . $valor;
        }

        return filter_var($valor, FILTER_VALIDATE_URL) === false ? '' : $valor;
    }
}

if (!function_exists('company_social_icon')) {
    /**
     * Descobre o icone pela rede social de destino. Serve para as redes
     * cadastradas livremente em `social_outras`.
     */
    function company_social_icon(string $url): string
    {
        $mapa = [
            'instagram'  => 'bi-instagram',
            'facebook'   => 'bi-facebook',
            'fb.com'     => 'bi-facebook',
            'youtube'    => 'bi-youtube',
            'youtu.be'   => 'bi-youtube',
            'linkedin'   => 'bi-linkedin',
            'tiktok'     => 'bi-tiktok',
            'twitter'    => 'bi-twitter-x',
            'x.com'      => 'bi-twitter-x',
            'whatsapp'   => 'bi-whatsapp',
            'wa.me'      => 'bi-whatsapp',
            'pinterest'  => 'bi-pinterest',
            'threads'    => 'bi-threads',
            'telegram'   => 'bi-telegram',
        ];

        foreach ($mapa as $dominio => $icone) {
            if (stripos($url, $dominio) !== false) {
                return $icone;
            }
        }

        return 'bi-link-45deg';
    }
}

if (!function_exists('data_valor_br')) {
    /**
     * Valor para preencher um campo de data com mascara (atributo data-date-br).
     *
     * Aceita o que o usuario digitou (dd/mm/aaaa), uma data do banco
     * (aaaa-mm-dd) ou vazio — sempre devolve dd/mm/aaaa. Serve para
     * reexibir o formulario apos um erro sem perder o que foi digitado.
     */
    function data_valor_br(mixed $valor): string
    {
        $texto = trim((string) $valor);

        if ($texto === '') {
            return '';
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $texto) === 1) {
            return $texto;
        }

        return data_iso_para_br($texto);
    }
}

if (!function_exists('lgpd_email')) {
    /**
     * E-mail do encarregado de dados (DPO).
     *
     * Vem de Admin -> CMS -> LGPD (`site_settings.lgpd_contact_email`) e cai no
     * padrao quando o campo esta vazio.
     */
    function lgpd_email(string $padrao = 'privacidade@hidrossolo.com.br'): string
    {
        $email = trim((string) \App\Core\View::getShared('lgpd_email'));

        return $email !== '' ? $email : $padrao;
    }
}

if (!function_exists('asset_label')) {
    /**
     * Rótulo amigável de um veículo ou equipamento (gerador, compressor...).
     */
    function asset_label(array $asset): string
    {
        $brand = trim((string) ($asset['brand'] ?? ''));
        $model = trim((string) ($asset['model'] ?? ''));

        if (($asset['category'] ?? 'vehicle') === 'equipment') {
            $tipo = trim((string) ($asset['equipment_type'] ?? ''));
            $label = trim($tipo . ' ' . $brand . ' ' . $model);

            return $label !== '' ? $label : 'Equipamento #' . ($asset['id'] ?? '');
        }

        $plate = trim((string) ($asset['plate'] ?? ''));
        $label = $plate !== '' ? $plate : 'Veículo #' . ($asset['id'] ?? '');

        $rest = trim($brand . ' ' . $model);

        return $rest !== '' ? $label . ' — ' . $rest : $label;
    }
}
