<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Security;
use App\Support\Exportador;
use App\Support\FleetCatalog;

class AbastecimentosController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();
        $veiculoId = $_GET['veiculo_id'] ?? null;

        $where = '';
        $params = [];
        if ($veiculoId) {
            $where = 'WHERE vf.vehicle_id = ?';
            $params = [$veiculoId];
        }

        $abastecimentos = $db->fetchAll(
            "SELECT vf.*, v.plate, v.brand, v.model, v.category, v.equipment_type, v.fuel_type,
                    COALESCE(ex.total, 0) AS extras_total
             FROM vehicle_fuel vf
             JOIN vehicles v ON v.id = vf.vehicle_id
             LEFT JOIN (
                 SELECT fuel_id, SUM(amount) AS total FROM vehicle_fuel_extras GROUP BY fuel_id
             ) ex ON ex.fuel_id = vf.id
             {$where}
             ORDER BY vf.fuel_date DESC
             LIMIT 100",
            $params
        );

        // Despesas extras por abastecimento
        $extrasPorAbastecimento = [];
        if ($abastecimentos !== []) {
            $ids = array_map(static fn(array $a): int => (int) $a['id'], $abastecimentos);
            $ph = implode(',', array_fill(0, count($ids), '?'));
            foreach ($db->fetchAll(
                "SELECT fuel_id, description, amount FROM vehicle_fuel_extras WHERE fuel_id IN ({$ph}) ORDER BY id",
                $ids
            ) as $extra) {
                $extrasPorAbastecimento[(int) $extra['fuel_id']][] = $extra;
            }
        }

        $veiculos = $db->fetchAll("SELECT id, plate, brand, model, category, equipment_type FROM vehicles WHERE status != 'inactive' ORDER BY category, plate");

        echo $this->view('admin.abastecimentos.index', [
            'title' => 'Controle de Abastecimento',
            'abastecimentos' => $abastecimentos,
            'extrasPorAbastecimento' => $extrasPorAbastecimento,
            'veiculos' => $veiculos,
            'veiculoId' => $veiculoId,
            'config' => $this->config('company'),
        ]);
    }

    public function create(): void
    {
        $veiculos = $this->db()->fetchAll("SELECT id, plate, brand, model, category, equipment_type, fuel_type FROM vehicles WHERE status != 'inactive' ORDER BY category, plate");
        $veiculoId = $_GET['veiculo_id'] ?? null;

        echo $this->view('admin.abastecimentos.form', [
            'title' => 'Novo Abastecimento',
            'abastecimento' => null,
            'veiculos' => $veiculos,
            'veiculoId' => $veiculoId,
            'config' => $this->config('company'),
        ]);
    }

    public function store(): void
    {
        $data = $this->validate([
            'vehicle_id' => 'required',
            'fuel_date' => 'required',
            'liters' => 'required',
            'cost' => 'required',
        ]);

        $liters = str_replace(',', '.', $data['liters']);
        $cost = str_replace(['.', ','], ['', '.'], $data['cost']);
        $km = (int) ($_POST['km_at_refuel'] ?? 0);

        $id = $this->db()->insert('vehicle_fuel', [
            'vehicle_id' => (int)$data['vehicle_id'],
            'fuel_date' => $data['fuel_date'],
            'liters' => (float)$liters,
            'cost' => (float)$cost,
            'km_at_refuel' => $km,
            'user_id' => (int) ($_SESSION['user_id'] ?? 0),
        ]);

        // Despesas extras (Arla 32, lavagem, pedágio, óleo, etc.)
        $extras = $this->extras();

        foreach ($extras as $extra) {
            $this->db()->insert('vehicle_fuel_extras', [
                'fuel_id' => $id,
                'description' => $extra['description'],
                'amount' => $extra['amount'],
            ]);
        }

        // Atualizar KM do veículo
        if (!empty($_POST['km_at_refuel'])) {
            $this->db()->update('vehicles',
                ['current_km' => $km],
                'id = ?',
                [(int)$data['vehicle_id']]
            );
        }

        Security::audit('fuel_created', 'vehicle_fuel', (int) $id, [
            'vehicle_id' => (int) $data['vehicle_id'],
            'liters' => (float) $liters,
            'cost' => (float) $cost,
            'extras' => count($extras),
        ]);

        $_SESSION['flash_success'] = 'Abastecimento registrado com sucesso!';
        $this->redirect('/admin/abastecimentos?veiculo_id=' . $data['vehicle_id']);
    }

    /**
     * Exclui um abastecimento. As despesas extras caem junto (ON DELETE CASCADE).
     */
    public function delete(string $id): void
    {
        $db = $this->db();
        $registro = $db->fetch('SELECT id FROM vehicle_fuel WHERE id = ?', [$id]);

        if (!$registro) {
            $_SESSION['flash_error'] = 'Abastecimento não encontrado.';
            $this->redirect('/admin/abastecimentos');
        }

        $db->delete('vehicle_fuel_extras', 'fuel_id = ?', [$id]);
        $db->delete('vehicle_fuel', 'id = ?', [$id]);

        Security::audit('fuel_deleted', 'vehicle_fuel', (int) $id);

        $_SESSION['flash_success'] = 'Abastecimento excluído com sucesso!';
        $this->redirect('/admin/abastecimentos');
    }

    /* ===================================================================== */

    /**
     * Exporta os abastecimentos em PDF.
     */
    public function pdf(): void
    {
        Exportador::pdf(Exportador::nomeArquivo('abastecimentos'), $this->relatorio());
    }

    /**
     * Exporta os abastecimentos em planilha XLSX formatada.
     */
    public function xlsx(): void
    {
        Exportador::xlsx(Exportador::nomeArquivo('abastecimentos'), $this->relatorio());
    }

    /**
     * @return array<string,mixed>
     */
    private function relatorio(): array
    {
        $db = $this->db();
        $veiculoId = $_GET['veiculo_id'] ?? null;

        $filtros = [
            'inicio' => (string) ($_GET['inicio'] ?? ''),
            'fim' => (string) ($_GET['fim'] ?? ''),
            'vehicle_id' => (int) ($veiculoId ?? 0),
        ];

        $where = [];
        $params = [];

        if ($filtros['vehicle_id'] > 0) {
            $where[] = 'vf.vehicle_id = ?';
            $params[] = $filtros['vehicle_id'];
        }
        if ($filtros['inicio'] !== '' && strtotime($filtros['inicio'])) {
            $where[] = 'vf.fuel_date >= ?';
            $params[] = date('Y-m-d', (int) strtotime($filtros['inicio']));
        }
        if ($filtros['fim'] !== '' && strtotime($filtros['fim'])) {
            $where[] = 'vf.fuel_date <= ?';
            $params[] = date('Y-m-d', (int) strtotime($filtros['fim']));
        }

        $sqlWhere = $where === [] ? '' : 'WHERE ' . implode(' AND ', $where);

        $abastecimentos = $db->fetchAll(
            "SELECT vf.*, v.plate, v.brand, v.model, v.category, v.equipment_type, v.fuel_type,
                    COALESCE(ex.total, 0) AS extras_total,
                    COALESCE(ex.descricao, '') AS extras_descricao
             FROM vehicle_fuel vf
             JOIN vehicles v ON v.id = vf.vehicle_id
             LEFT JOIN (
                 SELECT fuel_id, SUM(amount) AS total, GROUP_CONCAT(description SEPARATOR ', ') AS descricao
                 FROM vehicle_fuel_extras GROUP BY fuel_id
             ) ex ON ex.fuel_id = vf.id
             {$sqlWhere}
             ORDER BY vf.fuel_date DESC
             LIMIT 1000",
            $params
        );

        $linhas = [];
        $totalLitros = 0.0;
        $totalCombustivel = 0.0;
        $totalExtras = 0.0;

        foreach ($abastecimentos as $a) {
            $litros = (float) $a['liters'];
            $valor = (float) $a['cost'];
            $extras = (float) ($a['extras_total'] ?? 0);

            $totalLitros += $litros;
            $totalCombustivel += $valor;
            $totalExtras += $extras;

            $linhas[] = [
                'data' => (string) $a['fuel_date'],
                'ativo' => asset_label($a),
                'combustivel' => \App\Support\FleetAgenda::combustivelInfo($a['fuel_type'] ?? null, $a['category'] ?? 'vehicle')['label'],
                'litros' => $litros,
                'valor' => $valor,
                'preco' => $litros > 0 ? round($valor / $litros, 3) : 0.0,
                'km' => (float) ($a['km_at_refuel'] ?? 0),
                'extras' => $extras,
                'extras_desc' => mb_substr((string) ($a['extras_descricao'] ?? ''), 0, 120),
                'total' => $valor + $extras,
            ];
        }

        return [
            'title' => 'Controle de abastecimento',
            'subtitle' => 'Consumo de combustível e despesas extras por abastecimento',
            'company' => $this->config('company'),
            'columns' => [
                ['label' => 'Data', 'key' => 'data', 'width' => 12, 'type' => 'date'],
                ['label' => 'Ativo', 'key' => 'ativo', 'width' => 28],
                ['label' => 'Combustível', 'key' => 'combustivel', 'width' => 14, 'type' => 'center'],
                ['label' => 'Litros', 'key' => 'litros', 'width' => 12, 'type' => 'number'],
                ['label' => 'R$/L', 'key' => 'preco', 'width' => 10, 'type' => 'number'],
                ['label' => 'Combustível (R$)', 'key' => 'valor', 'width' => 18, 'type' => 'money'],
                ['label' => 'Extras (R$)', 'key' => 'extras', 'width' => 14, 'type' => 'money'],
                ['label' => 'Total (R$)', 'key' => 'total', 'width' => 16, 'type' => 'money'],
                ['label' => 'KM', 'key' => 'km', 'width' => 12, 'type' => 'number'],
                ['label' => 'Despesas extras', 'key' => 'extras_desc', 'width' => 34],
            ],
            'rows' => $linhas,
            'totals' => ['litros' => $totalLitros, 'valor' => $totalCombustivel, 'extras' => $totalExtras, 'total' => $totalCombustivel + $totalExtras],
            'totals_label' => 'TOTAIS',
            'summary' => [
                ['label' => 'Abastecimentos', 'value' => (string) count($linhas), 'tone' => 'neutral'],
                ['label' => 'Litros', 'value' => number_format($totalLitros, 1, ',', '.'), 'tone' => 'neutral'],
                ['label' => 'Combustível', 'value' => 'R$ ' . number_format($totalCombustivel, 2, ',', '.'), 'tone' => 'out'],
                ['label' => 'Extras', 'value' => 'R$ ' . number_format($totalExtras, 2, ',', '.'), 'tone' => 'warn'],
                ['label' => 'Custo total', 'value' => 'R$ ' . number_format($totalCombustivel + $totalExtras, 2, ',', '.'), 'tone' => 'out'],
                ['label' => 'Preço médio', 'value' => 'R$ ' . number_format($totalLitros > 0 ? ($totalCombustivel / $totalLitros) : 0, 3, ',', '.') . '/L', 'tone' => 'neutral'],
            ],
            'notes' => 'As despesas extras incluem Arla 32, lavagem, pedágio, óleo e outros itens lançados junto ao abastecimento.',
        ];
    }

    /* ===================================================================== */

    /**
     * @return array<int,array{description:string,amount:float}>
     */
    private function extras(): array
    {
        $linhas = $_POST['extras'] ?? [];

        if (!is_array($linhas)) {
            return [];
        }

        $extras = [];

        foreach ($linhas as $linha) {
            if (!is_array($linha)) {
                continue;
            }

            $descricao = trim((string) ($linha['description'] ?? ''));

            if ($descricao === '') {
                continue;
            }

            $valor = str_replace(['.', ','], ['', '.'], (string) ($linha['amount'] ?? '0'));

            if (!is_numeric($valor) || (float) $valor <= 0) {
                continue;
            }

            $extras[] = [
                'description' => mb_substr($descricao, 0, 255),
                'amount' => round((float) $valor, 2),
            ];
        }

        return $extras;
    }
}
