<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Gerador de planilhas .xlsx (Office Open XML) sem dependências externas.
 *
 * Escreve um arquivo XLSX real e formatado: cabeçalho colorido congelado,
 * filtro automático, largura de colunas, bordas, zebra e formatos de número
 * (moeda brasileira e data). Não é CSV.
 *
 * Uso:
 *
 *   $xlsx = new XlsxWriter('Hidrossolo');
 *   $xlsx->addSheet('Fluxo de caixa', [
 *       ['label' => 'Vencimento', 'key' => 'due_date', 'width' => 12, 'type' => 'date'],
 *       ['label' => 'Valor', 'key' => 'amount', 'width' => 14, 'type' => 'money'],
 *   ], $linhas, ['subtitle' => 'Setembro/2026', 'totals' => ['Valor' => 1234.5]]);
 *   $xlsx->download('fluxo-de-caixa.xlsx');
 */
final class XlsxWriter
{
    /** Formatos de número personalizados (ids a partir de 164). */
    private const FMT_MONEY = 164;
    private const FMT_DATE = 165;
    private const FMT_MONEY_SIGNED = 166;

    private const MIME = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    private string $creator;

    /** @var array<int,array{name:string,xml:string,rows:int}> */
    private array $sheets = [];

    public function __construct(string $creator = 'Hidrossolo')
    {
        $this->creator = $creator;
    }

    /**
     * Adiciona uma aba.
     *
     * Colunas aceitas: `label`, `key`, `width` (default 18) e `type`
     * (`text`, `money`, `date`, `center`, `income`, `expense`).
     *
     * @param array<int,array<string,mixed>> $columns
     * @param array<int,array<string,mixed>> $rows
     * @param array<string,mixed>            $options subtitle, title_extra, totals, zebra, freeze
     */
    public function addSheet(string $name, array $columns, array $rows, array $options = []): self
    {
        $name = $this->safeSheetName($name);

        $zebra = (bool) ($options['zebra'] ?? true);
        $cols = [];
        $header = [];

        foreach ($columns as $column) {
            $cols[] = [
                'label' => (string) ($column['label'] ?? ''),
                'key' => (string) ($column['key'] ?? ''),
                'width' => (float) ($column['width'] ?? 18),
                'type' => (string) ($column['type'] ?? 'text'),
            ];
            $header[] = (string) ($column['label'] ?? '');
        }

        $totalCols = max(1, count($cols));
        $out = [];

        // Título da aba
        $titulo = (string) ($options['title'] ?? $name);
        $out[] = $this->row(1, [
            ['col' => 1, 's' => 1, 't' => 's', 'v' => $titulo],
        ]);

        $subtitulo = (string) ($options['subtitle'] ?? '');
        if ($subtitulo !== '') {
            $out[] = $this->row(2, [
                ['col' => 1, 's' => 2, 't' => 's', 'v' => $subtitulo],
            ]);
        }

        // Emitido em
        $out[] = $this->row(3, [
            ['col' => 1, 's' => 2, 't' => 's', 'v' => 'Emitido em ' . date('d/m/Y H:i')],
        ]);

        $headerRow = count($out) + 1;

        // Cabeçalho
        $celulas = [];
        foreach ($header as $i => $rotulo) {
            $celulas[] = ['s' => 3, 't' => 's', 'v' => $rotulo, 'col' => $i + 1];
        }
        $out[] = $this->row($headerRow, $celulas);

        // Linhas
        $linhaAtual = $headerRow;
        $indice = 0;

        foreach ($rows as $linha) {
            $linhaAtual++;
            $celulas = [];

            foreach ($cols as $i => $col) {
                $valor = $linha[$col['key']] ?? null;
                $celulas[] = $this->cell($i + 1, $valor, $col['type'], $zebra && $indice % 2 === 1);
            }

            $out[] = $this->row($linhaAtual, $celulas);
            $indice++;
        }

        // Linha de totais
        $totais = $options['totals'] ?? null;
        if (is_array($totais) && $totais !== []) {
            $linhaAtual++;
            $celulas = [];

            foreach ($cols as $i => $col) {
                $chave = $col['key'];

                if (array_key_exists($chave, $totais)) {
                    $valor = $totais[$chave];

                    if ($valor === null || $valor === '') {
                        $celulas[] = ['s' => 8, 't' => 's', 'v' => '', 'col' => $i + 1];
                    } else {
                        $celulas[] = ['s' => 9, 'n' => (float) $valor, 'col' => $i + 1];
                    }
                } elseif ($i === 0) {
                    $celulas[] = ['s' => 8, 't' => 's', 'v' => (string) ($options['totals_label'] ?? 'TOTAL'), 'col' => 1];
                }
            }

            $out[] = $this->row($linhaAtual, $celulas);
        }

        $ultimaLinha = max($linhaAtual, $headerRow);

        $this->sheets[] = [
            'name' => $name,
            'xml' => $this->sheetXml($out, $cols, $totalCols, (int) ($options['freeze'] ?? $headerRow), $ultimaLinha, count($rows) > 0),
            'rows' => $ultimaLinha,
        ];

        return $this;
    }

    /**
     * Envia o arquivo para o navegador (download).
     */
    public function download(string $filename): void
    {
        $conteudo = $this->build();

        if (!headers_sent()) {
            header('Content-Type: ' . self::MIME);
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($conteudo));
            header('Cache-Control: max-age=0');
            header('Pragma: public');
        }

        echo $conteudo;
        exit;
    }

    /**
     * Monta o conteúdo binário do arquivo XLSX.
     */
    public function build(): string
    {
        if ($this->sheets === []) {
            $this->addSheet('Planilha', [], []);
        }

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        if ($tmp === false) {
            throw new \RuntimeException('Não foi possível criar o arquivo temporário.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($tmp, \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Não foi possível criar a planilha.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('docProps/core.xml', $this->coreProps());
        $zip->addFromString('docProps/app.xml', $this->appProps());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels());
        $zip->addFromString('xl/styles.xml', $this->styles());

        foreach ($this->sheets as $i => $sheet) {
            $zip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', $sheet['xml']);
        }

        $zip->close();

        $conteudo = (string) file_get_contents($tmp);
        @unlink($tmp);

        return $conteudo;
    }

    /* ===================================================================== */

    /**
     * @param array<int,array<string,mixed>> $cells
     */
    private function row(int $numero, array $cells): string
    {
        $xml = '<row r="' . $numero . '">';

        foreach ($cells as $cell) {
            $cell['r'] = $numero;
            $xml .= $this->cellXml($cell);
        }

        return $xml . '</row>';
    }

    /**
     * @return array<string,mixed>
     */
    private function cell(int $col, mixed $valor, string $tipo, bool $zebra): array
    {
        $base = ['col' => $col];

        if ($valor === null || $valor === '') {
            return $base + ['s' => $this->style($tipo, $zebra, true), 't' => 's', 'v' => ''];
        }

        return match ($tipo) {
            'money' => $base + ['s' => $this->style('money', $zebra, false), 'n' => round((float) $valor, 2)],
            'income' => $base + ['s' => $this->style('income', $zebra, false), 'n' => round((float) $valor, 2)],
            'expense' => $base + ['s' => $this->style('expense', $zebra, false), 'n' => round((float) $valor, 2)],
            'date' => $base + ['s' => $this->style('date', $zebra, false), 'n' => self::dateSerial((string) $valor)],
            'center' => $base + ['s' => $this->style('center', $zebra, false), 't' => 's', 'v' => (string) $valor],
            default => $base + ['s' => $this->style('text', $zebra, false), 't' => 's', 'v' => (string) $valor],
        };
    }

    /**
     * Mapa de estilos (índice em cellXfs).
     */
    private function style(string $tipo, bool $zebra, bool $vazio): int
    {
        if ($vazio) {
            return $zebra ? 10 : 4;
        }

        return match ($tipo) {
            'money' => $zebra ? 11 : 5,
            'date' => $zebra ? 12 : 6,
            'center' => $zebra ? 13 : 7,
            'income' => $zebra ? 14 : 15,
            'expense' => $zebra ? 16 : 17,
            default => $zebra ? 10 : 4,
        };
    }

    /**
     * @param array<string,mixed> $cell
     */
    private function cellXml(array $cell): string
    {
        $ref = self::columnLetter((int) $cell['col']) . (int) $cell['r'];

        if (isset($cell['n'])) {
            return '<c r="' . $ref . '" s="' . (int) $cell['s'] . '"><v>'
                . self::num((float) $cell['n']) . '</v></c>';
        }

        $texto = self::esc((string) ($cell['v'] ?? ''));

        return '<c r="' . $ref . '" s="' . (int) $cell['s'] . '" t="inlineStr"><is><t xml:space="preserve">'
            . $texto . '</t></is></c>';
    }

    /**
     * @param array<int,string>              $rowsXml
     * @param array<int,array<string,mixed>> $cols
     */
    private function sheetXml(array $rowsXml, array $cols, int $totalCols, int $freeze, int $ultimaLinha, bool $filtro): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';

        // Congela as linhas acima do cabeçalho
        $xml .= '<sheetViews><sheetView workbookViewId="0"';
        $xml .= $freeze > 0 ? ' tabSelected="1"><pane ySplit="' . $freeze . '" topLeftCell="A' . ($freeze + 1) . '" activePane="bottomLeft" state="frozen"/>' : ' tabSelected="1"';
        $xml .= '<selection pane="bottomLeft" activeCell="A' . max(1, $freeze + 1) . '" sqref="A' . max(1, $freeze + 1) . '"/></sheetView></sheetViews>';

        $xml .= '<sheetFormatPr defaultRowHeight="15"/><cols>';
        foreach ($cols as $i => $col) {
            $xml .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . self::num($col['width']) . '" customWidth="1"/>';
        }
        $xml .= '</cols><sheetData>';

        foreach ($rowsXml as $row) {
            $xml .= $row;
        }

        $xml .= '</sheetData>';

        if ($filtro) {
            $xml .= '<autoFilter ref="A' . ($freeze) . ':' . self::columnLetter($totalCols) . $ultimaLinha . '"/>';
        }

        $xml .= '<pageMargins left="0.4" right="0.4" top="0.5" bottom="0.5" header="0.3" footer="0.3"/>';
        $xml .= '<pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/>';

        return $xml . '</worksheet>';
    }

    private function contentTypes(): string
    {
        $overrides = '';
        foreach ($this->sheets as $i => $sheet) {
            $overrides .= '<Override PartName="/xl/worksheets/sheet' . ($i + 1)
                . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . $overrides
            . '</Types>';
    }

    private function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private function workbook(): string
    {
        $sheets = '';
        foreach ($this->sheets as $i => $sheet) {
            $sheets .= '<sheet name="' . self::esc($sheet['name']) . '" sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<workbookPr/><sheets>' . $sheets . '</sheets></workbook>';
    }

    private function workbookRels(): string
    {
        $rels = '';
        foreach ($this->sheets as $i => $sheet) {
            $rels .= '<Relationship Id="rId' . ($i + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . ($i + 1) . '.xml"/>';
        }

        $rels .= '<Relationship Id="rId' . (count($this->sheets) + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $rels . '</Relationships>';
    }

    private function coreProps(): string
    {
        $agora = gmdate('Y-m-d\TH:i:s\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
            . 'xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:creator>' . self::esc($this->creator) . '</dc:creator>'
            . '<cp:lastModifiedBy>' . self::esc($this->creator) . '</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $agora . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $agora . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    private function appProps(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" '
            . 'xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>' . self::esc($this->creator) . '</Application></Properties>';
    }

    private function styles(): string
    {
        $money = '&quot;R$&quot;\ #,##0.00';
        $moneySigned = '&quot;R$&quot;\ #,##0.00;[Red]-&quot;R$&quot;\ #,##0.00';

        // 0 padrão | 1 título | 2 legenda | 3 cabeçalho | 4 texto | 5 moeda | 6 data | 7 centro
        // 8 total rótulo | 9 total moeda | 10 zebra texto | 11 zebra moeda | 12 zebra data
        // 13 zebra centro | 14 zebra entrada | 15 entrada | 16 zebra saída | 17 saída
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="3">'
            . '<numFmt numFmtId="164" formatCode="' . $money . '"/>'
            . '<numFmt numFmtId="165" formatCode="dd/mm/yyyy"/>'
            . '<numFmt numFmtId="166" formatCode="' . $moneySigned . '"/>'
            . '</numFmts>'
            . '<fonts count="7">'
            . '<font><sz val="11"/><color theme="1"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="15"/><color rgb="FF0F2B46"/><name val="Calibri"/></font>'
            . '<font><i/><sz val="9"/><color rgb="FF6B7280"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FF0F2B46"/><name val="Calibri"/></font>'
            . '<font><sz val="11"/><color rgb="FF15803D"/><name val="Calibri"/></font>'
            . '<font><sz val="11"/><color rgb="FFB91C1C"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="6">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF0F2B46"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFF1F5F9"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFDCFCE7"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFFEE2E2"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="3">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border><left style="thin"><color rgb="FFD8DEE9"/></left><right style="thin"><color rgb="FFD8DEE9"/></right>'
            . '<top style="thin"><color rgb="FFD8DEE9"/></top><bottom style="thin"><color rgb="FFD8DEE9"/></bottom><diagonal/></border>'
            . '<border><left/><right/><top style="thin"><color rgb="FF0F2B46"/></top><bottom/><diagonal/></border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="18">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            . '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            . '<xf numFmtId="0" fontId="3" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">'
            . '<alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="165" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="4" fillId="0" borderId="2" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="164" fontId="4" fillId="0" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            . '<xf numFmtId="164" fontId="0" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="165" fontId="0" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="164" fontId="5" fillId="4" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="164" fontId="5" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="164" fontId="6" fillId="5" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="164" fontId="6" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    /* ===================================================================== */

    public static function columnLetter(int $index): string
    {
        $letra = '';

        while ($index > 0) {
            $index--;
            $letra = chr(65 + ($index % 26)) . $letra;
            $index = intdiv($index, 26);
        }

        return $letra !== '' ? $letra : 'A';
    }

    /**
     * Converte uma data (Y-m-d ou dd/mm/Y) no número serial usado pelo Excel.
     */
    public static function dateSerial(string $data): float
    {
        $data = trim($data);

        if ($data === '') {
            return 0.0;
        }

        $timestamp = strtotime($data);

        if ($timestamp === false) {
            return 0.0;
        }

        return (float) round($timestamp / 86400 + 25569, 5);
    }

    private function safeSheetName(string $name): string
    {
        $name = str_replace(['\\', '/', '?', '*', '[', ']', ':'], ' ', $name);
        $name = trim(preg_replace('/\s+/', ' ', $name) ?: 'Planilha');

        return $name === '' ? 'Planilha' : mb_substr($name, 0, 31);
    }

    private static function num(float $valor): string
    {
        return rtrim(rtrim(number_format($valor, 6, '.', ''), '0'), '.') ?: '0';
    }

    private static function esc(string $texto): string
    {
        return htmlspecialchars($texto, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
