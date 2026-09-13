<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Fachada simples para exportação de relatórios em PDF e XLSX.
 *
 * Concentra o formato de arquivo, o cabeçalho da marca e a linha de totais,
 * para que qualquer tela possa oferecer "Exportar PDF" e "Exportar Excel"
 * com poucas linhas de código.
 *
 * Uso:
 *
 *   Exportador::pdf('manutencoes', $relatorio);
 *   Exportador::xlsx('manutencoes', $relatorio);
 *
 * Onde `$relatorio` aceita: title, subtitle, company, columns, rows, totals,
 * totals_label, summary, notes, orientation (PDF) e sheets (XLSX, várias abas).
 */
final class Exportador
{
    public const MIME_XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    /**
     * @param array<string,mixed> $relatorio
     */
    public static function pdf(string $nomeArquivo, array $relatorio): void
    {
        PdfReport::download($nomeArquivo, $relatorio);
    }

    /**
     * @param array<string,mixed> $relatorio
     */
    public static function xlsx(string $nomeArquivo, array $relatorio): void
    {
        $xlsx = new XlsxWriter((string) ($relatorio['company']['name'] ?? 'Hidrossolo'));

        $abas = $relatorio['sheets'] ?? null;

        if (is_array($abas) && $abas !== []) {
            foreach ($abas as $aba) {
                $xlsx->addSheet(
                    (string) ($aba['name'] ?? 'Planilha'),
                    is_array($aba['columns'] ?? null) ? $aba['columns'] : [],
                    is_array($aba['rows'] ?? null) ? $aba['rows'] : [],
                    is_array($aba['options'] ?? null) ? $aba['options'] : []
                );
            }
        } else {
            $xlsx->addSheet(
                (string) ($relatorio['sheet'] ?? 'Relatório'),
                is_array($relatorio['columns'] ?? null) ? $relatorio['columns'] : [],
                is_array($relatorio['rows'] ?? null) ? $relatorio['rows'] : [],
                [
                    'title' => (string) ($relatorio['title'] ?? 'Relatório'),
                    'subtitle' => (string) ($relatorio['subtitle'] ?? ''),
                    'totals' => is_array($relatorio['totals'] ?? null) ? $relatorio['totals'] : [],
                    'totals_label' => (string) ($relatorio['totals_label'] ?? 'TOTAL'),
                ]
            );
        }

        $xlsx->download($nomeArquivo . '.xlsx');
    }

    /**
     * Botões de exportação prontos para as telas administrativas.
     */
    public static function botoes(string $urlBase, array $parametros = []): string
    {
        $query = $parametros === [] ? '' : '?' . http_build_query($parametros);

        return '<a href="' . htmlspecialchars($urlBase . '/pdf' . $query, ENT_QUOTES, 'UTF-8') . '" class="btn btn-outline-danger">'
            . '<i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>'
            . '<a href="' . htmlspecialchars($urlBase . '/xlsx' . $query, ENT_QUOTES, 'UTF-8') . '" class="btn btn-outline-success">'
            . '<i class="bi bi-file-earmark-excel me-1"></i>Excel (XLSX)</a>';
    }

    /**
     * Nome de arquivo seguro, com a data para facilitar o histórico.
     */
    public static function nomeArquivo(string $base, ?string $sufixo = null): string
    {
        $nome = preg_replace('/[^A-Za-z0-9\-]/', '-', $base) ?: 'relatorio';

        return trim($nome . ($sufixo !== null && $sufixo !== '' ? '-' . $sufixo : ''), '-') . '-' . date('Y-m-d');
    }
}
