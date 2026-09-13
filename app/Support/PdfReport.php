<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Relatórios em PDF (A4, paisagem ou retrato) gerados com dompdf.
 *
 * O layout traz cabeçalho com a marca, cartões de resumo, tabela formatada
 * com zebra, linha de totais e rodapé com numeração de páginas — sem depender
 * de nenhuma biblioteca de front-end.
 *
 * Uso:
 *
 *   PdfReport::download('fluxo-de-caixa', [
 *       'title' => 'Fluxo de caixa',
 *       'subtitle' => 'Setembro/2026',
 *       'company' => $config,
 *       'columns' => [['label' => 'Valor', 'key' => 'amount', 'type' => 'money']],
 *       'rows' => $linhas,
 *       'totals' => ['amount' => 1234.5],
 *       'summary' => [['label' => 'A receber', 'value' => 'R$ 1.000,00', 'tone' => 'in']],
 *   ]);
 */
final class PdfReport
{
    /**
     * @param array<string,mixed> $relatorio
     */
    public static function download(string $filename, array $relatorio): void
    {
        $dompdf = new \Dompdf\Dompdf([
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        $dompdf->loadHtml(self::html($relatorio), 'UTF-8');
        $dompdf->setPaper('A4', ($relatorio['orientation'] ?? 'landscape') === 'portrait' ? 'portrait' : 'landscape');
        $dompdf->render();

        $filename = preg_replace('/[^A-Za-z0-9\-_]/', '-', $filename) ?: 'relatorio';
        $conteudo = $dompdf->output();

        if (!headers_sent()) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
            header('Content-Length: ' . strlen($conteudo));
        }

        echo $conteudo;
        exit;
    }

    /**
     * @param array<string,mixed> $relatorio
     */
    public static function html(array $relatorio): string
    {
        $titulo = (string) ($relatorio['title'] ?? 'Relatório');
        $subtitulo = (string) ($relatorio['subtitle'] ?? '');
        $empresa = is_array($relatorio['company'] ?? null) ? $relatorio['company'] : [];
        $cols = is_array($relatorio['columns'] ?? null) ? $relatorio['columns'] : [];
        $linhas = is_array($relatorio['rows'] ?? null) ? $relatorio['rows'] : [];
        $totais = is_array($relatorio['totals'] ?? null) ? $relatorio['totals'] : [];
        $resumo = is_array($relatorio['summary'] ?? null) ? $relatorio['summary'] : [];
        $notas = (string) ($relatorio['notes'] ?? '');

        $nomeEmpresa = (string) ($empresa['name'] ?? 'Hidrossolo');
        $linhaEmpresa = implode(' · ', array_filter([
            (string) ($empresa['address'] ?? ''),
            trim(((string) ($empresa['city'] ?? '')) . ' ' . ((string) ($empresa['state'] ?? ''))) ?: null,
            (string) ($empresa['phone'] ?? ''),
            (string) ($empresa['email'] ?? ''),
        ]));

        $html = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8"><title>'
            . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</title><style>'
            . self::css()
            . '</style></head><body>';

        // Cabeçalho
        $html .= '<div class="doc-header">'
            . '<div class="brand">'
            . '<div class="brand-mark">H</div>'
            . '<div class="brand-text"><strong>' . self::e($nomeEmpresa) . '</strong>'
            . ($linhaEmpresa !== '' ? '<span>' . self::e($linhaEmpresa) . '</span>' : '')
            . '</div></div>'
            . '<div class="doc-meta"><span>Emitido em ' . date('d/m/Y \à\s H:i') . '</span>'
            . '<span>Documento gerado pelo sistema Hidrossolo</span></div>'
            . '</div>';

        $html .= '<h1 class="doc-title">' . self::e($titulo) . '</h1>';
        if ($subtitulo !== '') {
            $html .= '<p class="doc-subtitle">' . self::e($subtitulo) . '</p>';
        }

        // Cartões de resumo
        if ($resumo !== []) {
            $html .= '<table class="cards"><tr>';
            foreach ($resumo as $cartao) {
                $tom = (string) ($cartao['tone'] ?? 'neutral');
                $html .= '<td class="card card-' . self::e($tom) . '">'
                    . '<span class="card-label">' . self::e((string) ($cartao['label'] ?? '')) . '</span>'
                    . '<span class="card-value">' . self::e((string) ($cartao['value'] ?? '')) . '</span>'
                    . (isset($cartao['hint']) ? '<span class="card-hint">' . self::e((string) $cartao['hint']) . '</span>' : '')
                    . '</td>';
            }
            $html .= '</tr></table>';
        }

        // Tabela
        if ($cols !== []) {
            $html .= '<table class="data"><thead><tr>';
            foreach ($cols as $col) {
                $html .= '<th class="' . self::align((string) ($col['type'] ?? 'text')) . '">'
                    . self::e((string) ($col['label'] ?? '')) . '</th>';
            }
            $html .= '</tr></thead><tbody>';

            if ($linhas === []) {
                $html .= '<tr><td class="empty" colspan="' . count($cols) . '">Nenhum registro no período.</td></tr>';
            }

            foreach ($linhas as $indice => $linha) {
                $classe = $indice % 2 === 1 ? ' class="zebra"' : '';
                $html .= '<tr' . $classe . '>';

                foreach ($cols as $col) {
                    $tipo = (string) ($col['type'] ?? 'text');
                    $valor = $linha[(string) ($col['key'] ?? '')] ?? null;
                    $html .= '<td class="' . self::align($tipo) . ' ' . self::tone($tipo, $valor) . '">'
                        . self::valor($valor, $tipo) . '</td>';
                }

                $html .= '</tr>';
            }

            if ($totais !== []) {
                $html .= '<tr class="totals">';
                foreach ($cols as $i => $col) {
                    $chave = (string) ($col['key'] ?? '');
                    $tipo = (string) ($col['type'] ?? 'text');

                    if (array_key_exists($chave, $totais) && $totais[$chave] !== null && $totais[$chave] !== '') {
                        $html .= '<td class="' . self::align($tipo) . '">' . self::valor($totais[$chave], $tipo) . '</td>';
                    } elseif ($i === 0) {
                        $html .= '<td>' . self::e((string) ($relatorio['totals_label'] ?? 'TOTAL')) . '</td>';
                    } else {
                        $html .= '<td></td>';
                    }
                }
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
        }

        if ($notas !== '') {
            $html .= '<div class="doc-notes">' . nl2br(self::e($notas)) . '</div>';
        }

        $html .= '<div class="doc-footer">' . self::e($nomeEmpresa)
            . ' — relatório gerado automaticamente em ' . date('d/m/Y H:i') . '</div>';

        return $html . '</body></html>';
    }

    /* ===================================================================== */

    private static function css(): string
    {
        return '
            @page { margin: 1.4cm 1.2cm 1.6cm 1.2cm; }
            * { box-sizing: border-box; }
            body { font-family: "DejaVu Sans", Tahoma, sans-serif; font-size: 9.5px; color: #1f2937; margin: 0; }
            .doc-header { border-bottom: 2px solid #0f2b46; padding-bottom: 8px; margin-bottom: 12px; }
            .brand { display: table; width: 100%; }
            .brand-mark { display: table-cell; width: 34px; height: 34px; background: #0f2b46; color: #fff;
                font-size: 18px; font-weight: bold; text-align: center; vertical-align: middle; border-radius: 8px; }
            .brand-text { display: table-cell; vertical-align: middle; padding-left: 10px; }
            .brand-text strong { display: block; font-size: 13px; color: #0f2b46; }
            .brand-text span { display: block; font-size: 8px; color: #64748b; }
            .doc-meta { text-align: right; font-size: 8px; color: #64748b; margin-top: -30px; }
            .doc-meta span { display: block; }
            .doc-title { font-size: 17px; color: #0f2b46; margin: 14px 0 2px; }
            .doc-subtitle { font-size: 10px; color: #475569; margin: 0 0 10px; }
            table.cards { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 6px -6px 12px; }
            td.card { width: 25%; border: 1px solid #e2e8f0; border-left: 4px solid #0f2b46; border-radius: 6px;
                padding: 7px 8px; background: #f8fafc; vertical-align: top; }
            td.card-in { border-left-color: #16a34a; }
            td.card-out { border-left-color: #dc2626; }
            td.card-warn { border-left-color: #d97706; }
            .card-label { display: block; font-size: 8px; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
            .card-value { display: block; font-size: 12px; font-weight: bold; color: #0f2b46; margin-top: 2px; }
            .card-hint { display: block; font-size: 7.5px; color: #64748b; }
            table.data { width: 100%; border-collapse: collapse; }
            table.data thead th { background: #0f2b46; color: #fff; font-size: 8.5px; text-align: left;
                padding: 6px 6px; border: 1px solid #0f2b46; }
            table.data td { padding: 5px 6px; border: 1px solid #e2e8f0; vertical-align: top; }
            table.data tr.zebra td { background: #f8fafc; }
            table.data tr.totals td { background: #eef2f9; font-weight: bold; border-top: 2px solid #0f2b46; }
            table.data td.empty { text-align: center; color: #94a3b8; padding: 18px; }
            .right { text-align: right; }
            .center { text-align: center; }
            .in { color: #15803d; font-weight: bold; }
            .out { color: #b91c1c; font-weight: bold; }
            .doc-notes { margin-top: 12px; padding: 8px 10px; background: #f8fafc; border-left: 3px solid #64748b;
                font-size: 8.5px; color: #475569; }
            .doc-footer { margin-top: 14px; padding-top: 6px; border-top: 1px solid #e2e8f0;
                font-size: 7.5px; color: #94a3b8; text-align: center; }
        ';
    }

    private static function align(string $tipo): string
    {
        return match ($tipo) {
            'money', 'income', 'expense' => 'right',
            'date', 'center' => 'center',
            default => 'left',
        };
    }

    private static function tone(string $tipo, mixed $valor): string
    {
        if ($tipo === 'income') {
            return 'in';
        }

        if ($tipo === 'expense') {
            return 'out';
        }

        return '';
    }

    private static function valor(mixed $valor, string $tipo): string
    {
        if ($valor === null || $valor === '') {
            return '—';
        }

        return match ($tipo) {
            'money', 'income', 'expense' => 'R$ ' . number_format((float) $valor, 2, ',', '.'),
            'date' => self::e(date('d/m/Y', (int) strtotime((string) $valor))),
            'number' => self::e(number_format((float) $valor, 0, ',', '.')),
            default => nl2br(self::e((string) $valor)),
        };
    }

    private static function e(string $texto): string
    {
        return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    }
}
