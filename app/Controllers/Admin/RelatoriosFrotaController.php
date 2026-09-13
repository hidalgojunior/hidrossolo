<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;

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

    private function validDate(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return strtotime($value) ? $value : null;
    }
}
