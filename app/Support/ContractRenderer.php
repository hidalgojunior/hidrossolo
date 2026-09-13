<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Substituição de variáveis ({{coringas}}) em modelos de contrato.
 */
final class ContractRenderer
{
    /**
     * Lista as variáveis presentes no conteúdo, sem repetição.
     *
     * @return string[]
     */
    public static function variables(string $content): array
    {
        preg_match_all('/\{\{\s*([a-zA-Z0-9_.\-]+)\s*\}\}/', $content, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    /**
     * Substitui as variáveis pelos valores informados (com escape seguro).
     */
    public static function render(string $content, array $values): string
    {
        $rendered = preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_.\-]+)\s*\}\}/',
            static function (array $m) use ($values): string {
                $value = $values[$m[1]] ?? '';

                return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            },
            $content
        );

        return (string) $rendered;
    }

    /**
     * Nome amigável para uma variável: "data_inicio" -> "Data Inicio".
     */
    public static function label(string $key): string
    {
        $label = str_replace(['_', '-', '.'], ' ', $key);

        return mb_convert_case($label, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Valores preenchidos automaticamente a partir dos dados do contrato.
     */
    public static function defaults(array $contract): array
    {
        $valor = (float) ($contract['value'] ?? 0);

        return [
            'numero_contrato' => (string) ($contract['contract_number'] ?? ''),
            'numero' => (string) ($contract['contract_number'] ?? ''),
            'cliente' => (string) ($contract['client_name'] ?? ''),
            'contratante' => (string) ($contract['client_name'] ?? ''),
            'responsavel' => (string) ($contract['responsible'] ?? ''),
            'data_inicio' => self::date($contract['start_date'] ?? null),
            'data_fim' => self::date($contract['end_date'] ?? null),
            'valor' => number_format($valor, 2, ',', '.'),
            'valor_total' => number_format($valor, 2, ',', '.'),
            'valor_numerico' => number_format($valor, 2, '.', ''),
            'data_hoje' => date('d/m/Y'),
            'cidade' => 'Marília',
            'empresa' => 'Hidrossolo Poços Artesianos',
            'cnpj' => '',
            'endereco_empresa' => 'R. Assad Haddad, 584 - Parque das Indústrias, Marília/SP',
            'telefone_empresa' => '(14) 3413-2437',
            'email_empresa' => 'contato@hidrossolo.com.br',
        ];
    }

    private static function date(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' && strtotime($value) ? date('d/m/Y', strtotime($value)) : '';
    }
}
