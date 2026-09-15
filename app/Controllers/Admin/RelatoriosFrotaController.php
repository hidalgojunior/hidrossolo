<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Support\Exportador;
use App\Support\FleetAgenda;

/**
 * Relatórios de consumo e manutenção de veículos e equipamentos.
 */
class RelatoriosFrotaController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();

        // Período (padrão: últimos 12 meses)
        $de = $this->validDate($_GET['de'] ?? null) ?? date('Y-m-d', strtotime('-11 months first day of this month'));
        $ate = $this->validDate($_GET['ate'] ?? null) ?? date('Y-m-d');

        $categoriaRaw = (string) ($_GET['categoria'] ?? 'all');
        $categoria = in_array($categoriaRaw, ['all', 'vehicle', 'equipment'], true) ? $categoriaRaw : 'all';

        $assetFilter = '';
        $assetParams = [];

        if ($categoria !== 'all') {
            $assetFilter = ' AND v.category = ?';
            $assetParams[] = $categoria;
        }

        /* -----------------------------------------------------------------
         | Totais do período
         * --------------------------------------------------------------- */
        $combustivel = $db->fetch(
            "SELECT COALESCE(SUM(f.liters),0) AS litros,
                    COALESCE(SUM(f.cost),0) AS custo,
                    COUNT(f.id) AS lancamentos
             FROM vehicle_fuel f
             JOIN vehicles v ON v.id = f.vehicle_id
             WHERE f.fuel_date BETWEEN ? AND ?{$assetFilter}",
            array_merge([$de, $ate], $assetParams)
        );

        $manutencao = $db->fetch(
            "SELECT COALESCE(SUM(m.cost),0) AS custo, COUNT(m.id) AS lancamentos
             FROM vehicle_maintenance m
             JOIN vehicles v ON v.id = m.vehicle_id
             WHERE m.maintenance_date BETWEEN ? AND ?{$assetFilter}",
            array_merge([$de, $ate], $assetParams)
        );

        $litros = (float) ($combustivel['litros'] ?? 0);
        $custoCombustivel = (float) ($combustivel['custo'] ?? 0);
        $custoManutencao = (float) ($manutencao['custo'] ?? 0);

        $totais = [
            'litros' => $litros,
            'custo_combustivel' => $custoCombustivel,
            'custo_manutencao' => $custoManutencao,
            'custo_total' => $custoCombustivel + $custoManutencao,
            'preco_medio_litro' => $litros > 0 ? $custoCombustivel / $litros : 0.0,
            'abastecimentos' => (int) ($combustivel['lancamentos'] ?? 0),
            'manutencoes' => (int) ($manutencao['lancamentos'] ?? 0),
        ];

        /* -----------------------------------------------------------------
         | Séries mensais
         * --------------------------------------------------------------- */
        $serieCombustivel = $db->fetchAll(
            "SELECT DATE_FORMAT(f.fuel_date,'%Y-%m') AS mes,
                    COALESCE(SUM(f.liters),0) AS litros,
                    COALESCE(SUM(f.cost),0) AS custo
             FROM vehicle_fuel f
             JOIN vehicles v ON v.id = f.vehicle_id
             WHERE f.fuel_date BETWEEN ? AND ?{$assetFilter}
             GROUP BY mes ORDER BY mes",
            array_merge([$de, $ate], $assetParams)
        );

        $serieManutencao = $db->fetchAll(
            "SELECT DATE_FORMAT(m.maintenance_date,'%Y-%m') AS mes,
                    COALESCE(SUM(m.cost),0) AS custo,
                    COUNT(m.id) AS qtd
             FROM vehicle_maintenance m
             JOIN vehicles v ON v.id = m.vehicle_id
             WHERE m.maintenance_date BETWEEN ? AND ?{$assetFilter}
             GROUP BY mes ORDER BY mes",
            array_merge([$de, $ate], $assetParams)
        );

        /* -----------------------------------------------------------------
         | Por ativo (veículo/equipamento)
         * --------------------------------------------------------------- */
        $porAtivo = $db->fetchAll(
            "SELECT v.id, v.plate, v.brand, v.model, v.category, v.equipment_type,
                    v.current_km, v.current_hours,
                    COALESCE(fc.litros, 0) AS litros,
                    COALESCE(fc.custo, 0) AS custo_combustivel,
                    COALESCE(fc.qtd, 0) AS abastecimentos,
                    COALESCE(mt.custo, 0) AS custo_manutencao,
                    COALESCE(mt.qtd, 0) AS manutencoes,
                    COALESCE(km.rodados, 0) AS km_rodados,
                    (COALESCE(fc.custo, 0) + COALESCE(mt.custo, 0)) AS custo_total
             FROM vehicles v
             LEFT JOIN (
                 SELECT vehicle_id, SUM(liters) AS litros, SUM(cost) AS custo, COUNT(*) AS qtd
                 FROM vehicle_fuel WHERE fuel_date BETWEEN ? AND ?
                 GROUP BY vehicle_id
             ) fc ON fc.vehicle_id = v.id
             LEFT JOIN (
                 SELECT vehicle_id, SUM(cost) AS custo, COUNT(*) AS qtd
                 FROM vehicle_maintenance WHERE maintenance_date BETWEEN ? AND ?
                 GROUP BY vehicle_id
             ) mt ON mt.vehicle_id = v.id
             LEFT JOIN (
                 SELECT vehicle_id, (MAX(km_at_refuel) - MIN(km_at_refuel)) AS rodados
                 FROM vehicle_fuel
                 WHERE fuel_date BETWEEN ? AND ? AND km_at_refuel IS NOT NULL
                 GROUP BY vehicle_id
             ) km ON km.vehicle_id = v.id
             WHERE 1=1{$assetFilter}
             ORDER BY custo_total DESC, v.plate",
            array_merge([$de, $ate, $de, $ate, $de, $ate], $assetParams)
        );

        // Custo por km (apenas veículos com km medido no período)
        foreach ($porAtivo as &$item) {
            $rodados = (int) ($item['km_rodados'] ?? 0);
            $item['custo_por_km'] = ($rodados > 0 && ($item['category'] ?? 'vehicle') === 'vehicle')
                ? ((float) $item['custo_total'] / $rodados)
                : null;
            $item['consumo_km_l'] = ($rodados > 0 && (float) $item['litros'] > 0)
                ? ($rodados / (float) $item['litros'])
                : null;
        }
        unset($item);

        // Manutenções por tipo
        $porTipo = $db->fetchAll(
            "SELECT m.type, COUNT(m.id) AS qtd, COALESCE(SUM(m.cost),0) AS custo
             FROM vehicle_maintenance m
             JOIN vehicles v ON v.id = m.vehicle_id
             WHERE m.maintenance_date BETWEEN ? AND ?{$assetFilter}
             GROUP BY m.type",
            array_merge([$de, $ate], $assetParams)
        );

        // Últimos lançamentos (auditoria rápida)
        $ultimosLancamentos = $db->fetchAll(
            "SELECT 'fuel' AS tipo, f.fuel_date AS data, f.cost, v.plate, v.equipment_type, v.category,
                    u.name AS usuario
             FROM vehicle_fuel f
             JOIN vehicles v ON v.id = f.vehicle_id
             LEFT JOIN users u ON u.id = f.user_id
             WHERE f.fuel_date BETWEEN ? AND ?
             UNION ALL
             SELECT 'maintenance' AS tipo, m.maintenance_date AS data, m.cost, v.plate, v.equipment_type, v.category,
                    u.name AS usuario
             FROM vehicle_maintenance m
             JOIN vehicles v ON v.id = m.vehicle_id
             LEFT JOIN users u ON u.id = m.user_id
             WHERE m.maintenance_date BETWEEN ? AND ?
             ORDER BY data DESC
             LIMIT 12",
            [$de, $ate, $de, $ate]
        );

        echo $this->view('admin.frota.relatorios', [
            'title' => 'Relatórios de Frota',
            'de' => $de,
            'ate' => $ate,
            'categoria' => $categoria,
            'totais' => $totais,
            'serieCombustivel' => $serieCombustivel,
            'serieManutencao' => $serieManutencao,
            'porAtivo' => $porAtivo,
            'porTipo' => $porTipo,
            'ultimosLancamentos' => $ultimosLancamentos,
            'config' => $this->config('company'),
        ]);
    }

    /**
     * Exporta os relatórios de consumo em PDF.
     */
    public function pdf(): void
    {
        Exportador::pdf('relatorio-consumo-' . date('Y-m-d'), $this->relatorio());
    }

    /**
     * Exporta os relatórios de consumo em planilha XLSX formatada (2 abas).
     */
    public function xlsx(): void
    {
        Exportador::xlsx('relatorio-consumo-' . date('Y-m-d'), $this->relatorio());
    }

    /**
     * Monta o relatório de consumo por ativo no período selecionado.
     *
     * @return array<string,mixed>
     */
    private function relatorio(): array
    {
        $db = $this->db();

        $de = $this->validDate($_GET['de'] ?? null) ?? date('Y-m-d', strtotime('-11 months first day of this month'));
        $ate = $this->validDate($_GET['ate'] ?? null) ?? date('Y-m-d');

        $categoriaRaw = (string) ($_GET['categoria'] ?? 'all');
        $categoria = in_array($categoriaRaw, ['all', 'vehicle', 'equipment'], true) ? $categoriaRaw : 'all';

        $assetFilter = '';
        $assetParams = [];

        if ($categoria !== 'all') {
            $assetFilter = ' AND v.category = ?';
            $assetParams[] = $categoria;
        }

        $porAtivo = $db->fetchAll(
            "SELECT v.id, v.plate, v.brand, v.model, v.category, v.equipment_type, v.fuel_type,
                    v.current_km, v.current_hours,
                    COALESCE(fc.litros, 0) AS litros,
                    COALESCE(fc.custo, 0) AS custo_combustivel,
                    COALESCE(fc.qtd, 0) AS abastecimentos,
                    COALESCE(mt.custo, 0) AS custo_manutencao,
                    COALESCE(mt.qtd, 0) AS manutencoes,
                    COALESCE(km.rodados, 0) AS km_rodados,
                    (COALESCE(fc.custo, 0) + COALESCE(mt.custo, 0)) AS custo_total
             FROM vehicles v
             LEFT JOIN (
                 SELECT vehicle_id, SUM(liters) AS litros, SUM(cost) AS custo, COUNT(*) AS qtd
                 FROM vehicle_fuel WHERE fuel_date BETWEEN ? AND ?
                 GROUP BY vehicle_id
             ) fc ON fc.vehicle_id = v.id
             LEFT JOIN (
                 SELECT vehicle_id, SUM(cost) AS custo, COUNT(*) AS qtd
                 FROM vehicle_maintenance WHERE maintenance_date BETWEEN ? AND ?
                 GROUP BY vehicle_id
             ) mt ON mt.vehicle_id = v.id
             LEFT JOIN (
                 SELECT vehicle_id, (MAX(km_at_refuel) - MIN(km_at_refuel)) AS rodados
                 FROM vehicle_fuel
                 WHERE fuel_date BETWEEN ? AND ? AND km_at_refuel IS NOT NULL
                 GROUP BY vehicle_id
             ) km ON km.vehicle_id = v.id
             WHERE 1=1{$assetFilter}
             ORDER BY custo_total DESC, v.plate",
            array_merge([$de, $ate, $de, $ate, $de, $ate], $assetParams)
        );

        $linhas = [];
        $totalCombustivel = 0.0;
        $totalManutencao = 0.0;
        $totalLitros = 0.0;

        foreach ($porAtivo as $item) {
            $rodados = (int) ($item['km_rodados'] ?? 0);
            $litros = (float) $item['litros'];
            $custoComb = (float) $item['custo_combustivel'];
            $custoMan = (float) $item['custo_manutencao'];
            $custoTotal = $custoComb + $custoMan;

            $totalCombustivel += $custoComb;
            $totalManutencao += $custoMan;
            $totalLitros += $litros;

            $comb = FleetAgenda::combustivelInfo($item['fuel_type'] ?? null, $item['category'] ?? 'vehicle');
            $equipamento = ($item['category'] ?? 'vehicle') === 'equipment';

            $linhas[] = [
                'ativo' => asset_label($item),
                'tipo' => $equipamento ? 'Equipamento' : 'Veículo',
                'combustivel' => $comb['label'],
                'km' => $equipamento
                    ? number_format((float) ($item['current_hours'] ?? 0), 1, ',', '.') . ' h'
                    : number_format((float) ($item['current_km'] ?? 0), 0, ',', '.') . ' km',
                'rodados' => $rodados,
                'litros' => $litros,
                'abastecimentos' => (int) $item['abastecimentos'],
                'custo_combustivel' => $custoComb,
                'manutencoes' => (int) $item['manutencoes'],
                'custo_manutencao' => $custoMan,
                'custo_total' => $custoTotal,
                'consumo' => ($rodados > 0 && $litros > 0) ? round($rodados / $litros, 2) : 0.0,
                'custo_km' => ($rodados > 0) ? round($custoTotal / $rodados, 3) : 0.0,
            ];
        }

        $colunas = [
            ['label' => 'Ativo', 'key' => 'ativo', 'width' => 28],
            ['label' => 'Tipo', 'key' => 'tipo', 'width' => 12, 'type' => 'center'],
            ['label' => 'Combustível', 'key' => 'combustivel', 'width' => 13, 'type' => 'center'],
            ['label' => 'Odômetro atual', 'key' => 'km', 'width' => 15, 'type' => 'center'],
            ['label' => 'KM rodados', 'key' => 'rodados', 'width' => 13, 'type' => 'number'],
            ['label' => 'Litros', 'key' => 'litros', 'width' => 12, 'type' => 'number'],
            ['label' => 'Abastec.', 'key' => 'abastecimentos', 'width' => 11, 'type' => 'center'],
            ['label' => 'Combustível (R$)', 'key' => 'custo_combustivel', 'width' => 18, 'type' => 'money'],
            ['label' => 'Manut.', 'key' => 'manutencoes', 'width' => 10, 'type' => 'center'],
            ['label' => 'Manutenção (R$)', 'key' => 'custo_manutencao', 'width' => 17, 'type' => 'money'],
            ['label' => 'Custo total (R$)', 'key' => 'custo_total', 'width' => 16, 'type' => 'money'],
            ['label' => 'km/L', 'key' => 'consumo', 'width' => 10, 'type' => 'number'],
            ['label' => 'R$/km', 'key' => 'custo_km', 'width' => 11, 'type' => 'number'],
        ];

        $periodo = 'Período de ' . date('d/m/Y', (int) strtotime($de)) . ' a ' . date('d/m/Y', (int) strtotime($ate))
            . ($categoria === 'all' ? ' · veículos e equipamentos' : ' · somente ' . ($categoria === 'vehicle' ? 'veículos' : 'equipamentos'));

        return [
            'title' => 'Relatório de consumo e custos da frota',
            'subtitle' => $periodo,
            'sheet' => 'Custo por ativo',
            'company' => $this->config('company'),
            'columns' => $colunas,
            'rows' => $linhas,
            'totals' => [
                'litros' => $totalLitros,
                'custo_combustivel' => $totalCombustivel,
                'custo_manutencao' => $totalManutencao,
                'custo_total' => $totalCombustivel + $totalManutencao,
            ],
            'totals_label' => 'TOTAL DO PERÍODO',
            'summary' => [
                ['label' => 'Ativos', 'value' => (string) count($linhas), 'tone' => 'neutral'],
                ['label' => 'Litros', 'value' => number_format($totalLitros, 1, ',', '.'), 'tone' => 'neutral'],
                ['label' => 'Combustível', 'value' => 'R$ ' . number_format($totalCombustivel, 2, ',', '.'), 'tone' => 'out'],
                ['label' => 'Manutenção', 'value' => 'R$ ' . number_format($totalManutencao, 2, ',', '.'), 'tone' => 'out'],
                ['label' => 'Custo total', 'value' => 'R$ ' . number_format($totalCombustivel + $totalManutencao, 2, ',', '.'), 'tone' => 'warn'],
                ['label' => 'Preço médio do litro', 'value' => 'R$ ' . number_format($totalLitros > 0 ? $totalCombustivel / $totalLitros : 0, 3, ',', '.'), 'tone' => 'neutral'],
            ],
            'notes' => 'km/L e R$/km são calculados apenas para os ativos com odômetro registrado nos abastecimentos do período.',
            'sheets' => [
                [
                    'name' => 'Custo por ativo',
                    'columns' => $colunas,
                    'rows' => $linhas,
                    'options' => [
                        'title' => 'Consumo e custos por ativo',
                        'subtitle' => $periodo,
                        'totals' => [
                            'litros' => $totalLitros,
                            'custo_combustivel' => $totalCombustivel,
                            'custo_manutencao' => $totalManutencao,
                            'custo_total' => $totalCombustivel + $totalManutencao,
                        ],
                        'totals_label' => 'TOTAL DO PERÍODO',
                    ],
                ],
                [
                    'name' => 'Resumo do período',
                    'columns' => [
                        ['label' => 'Indicador', 'key' => 'indicador', 'width' => 32],
                        ['label' => 'Valor', 'key' => 'valor', 'width' => 22],
                    ],
                    'rows' => [
                        ['indicador' => 'Período', 'valor' => date('d/m/Y', (int) strtotime($de)) . ' a ' . date('d/m/Y', (int) strtotime($ate))],
                        ['indicador' => 'Ativos analisados', 'valor' => (string) count($linhas)],
                        ['indicador' => 'Litros abastecidos', 'valor' => number_format($totalLitros, 1, ',', '.')],
                        ['indicador' => 'Custo de combustível', 'valor' => 'R$ ' . number_format($totalCombustivel, 2, ',', '.')],
                        ['indicador' => 'Custo de manutenção', 'valor' => 'R$ ' . number_format($totalManutencao, 2, ',', '.')],
                        ['indicador' => 'Custo total', 'valor' => 'R$ ' . number_format($totalCombustivel + $totalManutencao, 2, ',', '.')],
                        ['indicador' => 'Preço médio do litro', 'valor' => 'R$ ' . number_format($totalLitros > 0 ? $totalCombustivel / $totalLitros : 0, 3, ',', '.')],
                    ],
                    'options' => ['title' => 'Resumo do período', 'subtitle' => $periodo, 'zebra' => false],
                ],
            ],
        ];
    }

    /**
     * Aceita dd/mm/aaaa (máscara dos formulários) e aaaa-mm-dd.
     * Devolve sempre aaaa-mm-dd, o formato do banco.
     */
    private function validDate(mixed $value): ?string
    {
        return data_br_para_iso($value);
    }
}
