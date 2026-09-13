<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Security;
use App\Support\Exportador;
use App\Support\FleetAgenda;
use App\Support\FleetCatalog;

class ManutencoesController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();
        $veiculoId = $_GET['veiculo_id'] ?? null;

        $where = '';
        $params = [];
        if ($veiculoId) {
            $where = 'WHERE vm.vehicle_id = ?';
            $params = [$veiculoId];
        }

        $manutencoes = $db->fetchAll(
            "SELECT vm.*, v.plate, v.brand, v.model
             FROM vehicle_maintenance vm
             JOIN vehicles v ON v.id = vm.vehicle_id
             {$where}
             ORDER BY vm.maintenance_date DESC
             LIMIT 50",
            $params
        );

        $veiculos = $db->fetchAll("SELECT id, plate, brand, model FROM vehicles WHERE status != 'inactive' ORDER BY plate");

        // Itens trocados em cada manutenção
        $itensPorManutencao = [];
        if ($manutencoes !== []) {
            $ids = array_map(static fn(array $m): int => (int) $m['id'], $manutencoes);
            $ph = implode(',', array_fill(0, count($ids), '?'));
            $itens = $db->fetchAll(
                "SELECT * FROM vehicle_maintenance_items WHERE maintenance_id IN ({$ph}) ORDER BY id",
                $ids
            );
            foreach ($itens as $item) {
                $itensPorManutencao[(int) $item['maintenance_id']][] = $item;
            }
        }

        echo $this->view('admin.manutencoes.index', [
            'title' => 'Controle de Manutenções',
            'manutencoes' => $manutencoes,
            'itensPorManutencao' => $itensPorManutencao,
            'servicos' => FleetCatalog::servicos(),
            'veiculos' => $veiculos,
            'veiculoId' => $veiculoId,
            'config' => $this->config('company'),
        ]);
    }

    public function create(): void
    {
        $veiculos = $this->db()->fetchAll("SELECT id, plate, brand, model, category, equipment_type FROM vehicles WHERE status != 'inactive' ORDER BY category, plate");
        $veiculoId = $_GET['veiculo_id'] ?? null;

        echo $this->view('admin.manutencoes.form', [
            'title' => 'Nova Manutenção',
            'manutencao' => null,
            'itens' => [],
            'servicos' => FleetCatalog::servicos(),
            'veiculos' => $veiculos,
            'veiculoId' => $veiculoId,
            'config' => $this->config('company'),
        ]);
    }

    public function store(): void
    {
        $data = $this->validate([
            'vehicle_id' => 'required',
            'type' => 'required',
            'maintenance_date' => 'required',
            'cost' => 'required',
        ]);

        $cost = str_replace(['.', ','], ['', '.'], $data['cost']);

        $serviceCategory = (string) ($_POST['service_category'] ?? 'other');
        if (!array_key_exists($serviceCategory, FleetCatalog::servicos())) {
            $serviceCategory = 'other';
        }

        $nextReview = trim((string) ($_POST['next_review_date'] ?? ''));
        $nextReviewSql = ($nextReview !== '' && strtotime($nextReview)) ? date('Y-m-d', strtotime($nextReview)) : null;
        $km = (int) ($_POST['km_at_maintenance'] ?? 0);

        $id = $this->db()->insert('vehicle_maintenance', [
            'vehicle_id' => (int)$data['vehicle_id'],
            'type' => $data['type'],
            'service_category' => $serviceCategory,
            'workshop' => $_POST['workshop'] ?? '',
            'maintenance_date' => $data['maintenance_date'],
            'next_review_date' => $nextReviewSql,
            'km_at_maintenance' => $km,
            'cost' => (float)$cost,
            'description' => $_POST['description'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'user_id' => (int) ($_SESSION['user_id'] ?? 0),
        ]);

        $itens = $this->itens();
        $this->salvarItens((int) $id, $itens);

        if ($nextReviewSql !== null) {
            FleetAgenda::syncRevision((int) $data['vehicle_id'], $nextReviewSql, (int) ($_SESSION['user_id'] ?? 0), $km);
        }

        // Atualizar KM do veículo
        if (!empty($_POST['km_at_maintenance'])) {
            $this->db()->update('vehicles',
                ['current_km' => $km],
                'id = ?',
                [(int)$data['vehicle_id']]
            );
        }

        Security::audit('maintenance_created', 'vehicle_maintenance', (int) $id, [
            'vehicle_id' => (int) $data['vehicle_id'],
            'service_category' => $serviceCategory,
            'itens' => count($itens),
        ]);

        $_SESSION['flash_success'] = 'Manutenção registrada com sucesso!';
        $this->redirect('/admin/manutencoes?veiculo_id=' . $data['vehicle_id']);
    }

    public function edit(string $id): void
    {
        $manutencao = $this->db()->fetch(
            "SELECT vm.*, v.plate FROM vehicle_maintenance vm JOIN vehicles v ON v.id = vm.vehicle_id WHERE vm.id = ?",
            [$id]
        );

        if (!$manutencao) {
            $_SESSION['flash_error'] = 'Manutenção não encontrada.';
            $this->redirect('/admin/manutencoes');
        }

        $veiculos = $this->db()->fetchAll("SELECT id, plate, brand, model, category, equipment_type FROM vehicles ORDER BY category, plate");

        $itens = $this->db()->fetchAll(
            "SELECT * FROM vehicle_maintenance_items WHERE maintenance_id = ? ORDER BY id",
            [$id]
        );

        echo $this->view('admin.manutencoes.form', [
            'title' => 'Editar Manutenção',
            'manutencao' => $manutencao,
            'itens' => $itens,
            'servicos' => FleetCatalog::servicos(),
            'veiculos' => $veiculos,
            'veiculoId' => $manutencao['vehicle_id'],
            'config' => $this->config('company'),
        ]);
    }

    public function update(string $id): void
    {
        $data = $this->validate([
            'vehicle_id' => 'required',
            'type' => 'required',
            'maintenance_date' => 'required',
            'cost' => 'required',
        ]);

        $cost = str_replace(['.', ','], ['', '.'], $data['cost']);

        $serviceCategory = (string) ($_POST['service_category'] ?? 'other');
        if (!array_key_exists($serviceCategory, FleetCatalog::servicos())) {
            $serviceCategory = 'other';
        }

        $nextReview = trim((string) ($_POST['next_review_date'] ?? ''));
        $nextReviewSql = ($nextReview !== '' && strtotime($nextReview)) ? date('Y-m-d', strtotime($nextReview)) : null;
        $km = (int) ($_POST['km_at_maintenance'] ?? 0);

        $this->db()->update('vehicle_maintenance', [
            'vehicle_id' => (int)$data['vehicle_id'],
            'type' => $data['type'],
            'service_category' => $serviceCategory,
            'workshop' => $_POST['workshop'] ?? '',
            'maintenance_date' => $data['maintenance_date'],
            'next_review_date' => $nextReviewSql,
            'km_at_maintenance' => $km,
            'cost' => (float)$cost,
            'description' => $_POST['description'] ?? '',
            'notes' => $_POST['notes'] ?? '',
        ], 'id = ?', [$id]);

        $this->db()->delete('vehicle_maintenance_items', 'maintenance_id = ?', [$id]);
        $itens = $this->itens();
        $this->salvarItens((int) $id, $itens);

        if ($nextReviewSql !== null) {
            FleetAgenda::syncRevision((int) $data['vehicle_id'], $nextReviewSql, (int) ($_SESSION['user_id'] ?? 0), $km);
        }

        Security::audit('maintenance_updated', 'vehicle_maintenance', (int) $id, [
            'vehicle_id' => (int) $data['vehicle_id'],
            'service_category' => $serviceCategory,
            'itens' => count($itens),
        ]);

        $_SESSION['flash_success'] = 'Manutenção atualizada com sucesso!';
        $this->redirect('/admin/manutencoes');
    }

    public function delete(string $id): void
    {
        $this->db()->delete('vehicle_maintenance_items', 'maintenance_id = ?', [$id]);
        $this->db()->delete('vehicle_maintenance', 'id = ?', [$id]);
        Security::audit('maintenance_deleted', 'vehicle_maintenance', (int) $id);
        $_SESSION['flash_success'] = 'Manutenção excluída!';
        $this->redirect('/admin/manutencoes');
    }

    /* ===================================================================== */

    /**
     * Exporta as manutenções em PDF.
     */
    public function pdf(): void
    {
        Exportador::pdf(Exportador::nomeArquivo('manutencoes'), $this->relatorio());
    }

    /**
     * Exporta as manutenções em planilha XLSX formatada.
     */
    public function xlsx(): void
    {
        Exportador::xlsx(Exportador::nomeArquivo('manutencoes'), $this->relatorio());
    }

    /**
     * @return array<string,mixed>
     */
    private function relatorio(): array
    {
        $db = $this->db();
        $veiculoId = $_GET['veiculo_id'] ?? null;

        $where = '';
        $params = [];
        if ($veiculoId) {
            $where = 'WHERE vm.vehicle_id = ?';
            $params = [$veiculoId];
        }

        $manutencoes = $db->fetchAll(
            "SELECT vm.*, v.plate, v.brand, v.model, v.category, v.equipment_type,
                    COALESCE(itens.total, 0) AS itens_total
             FROM vehicle_maintenance vm
             JOIN vehicles v ON v.id = vm.vehicle_id
             LEFT JOIN (
                 SELECT maintenance_id, SUM(quantity * unit_cost) AS total
                 FROM vehicle_maintenance_items GROUP BY maintenance_id
             ) itens ON itens.maintenance_id = vm.id
             {$where}
             ORDER BY vm.maintenance_date DESC
             LIMIT 500",
            $params
        );

        $servicos = FleetCatalog::servicos();
        $linhas = [];
        $total = 0.0;

        foreach ($manutencoes as $m) {
            $total += (float) $m['cost'];

            $linhas[] = [
                'data' => (string) $m['maintenance_date'],
                'ativo' => asset_label($m),
                'servico' => $servicos[$m['service_category'] ?? 'other']['label'] ?? 'Outros',
                'tipo' => ($m['type'] ?? '') === 'preventive' ? 'Preventiva' : 'Corretiva',
                'workshop' => (string) ($m['workshop'] ?? ''),
                'km' => (float) ($m['km_at_maintenance'] ?? 0),
                'valor' => (float) $m['cost'],
                'itens' => (float) ($m['itens_total'] ?? 0),
                'proxima' => (string) ($m['next_review_date'] ?? ''),
                'descricao' => mb_substr((string) ($m['description'] ?? ''), 0, 160),
            ];
        }

        return [
            'title' => 'Controle de manutenções',
            'subtitle' => 'Serviços executados na frota e próximas revisões',
            'company' => $this->config('company'),
            'columns' => [
                ['label' => 'Data', 'key' => 'data', 'width' => 12, 'type' => 'date'],
                ['label' => 'Ativo', 'key' => 'ativo', 'width' => 30],
                ['label' => 'Serviço', 'key' => 'servico', 'width' => 24],
                ['label' => 'Tipo', 'key' => 'tipo', 'width' => 12, 'type' => 'center'],
                ['label' => 'Oficina', 'key' => 'workshop', 'width' => 22],
                ['label' => 'KM', 'key' => 'km', 'width' => 12, 'type' => 'number'],
                ['label' => 'Itens (R$)', 'key' => 'itens', 'width' => 14, 'type' => 'money'],
                ['label' => 'Valor (R$)', 'key' => 'valor', 'width' => 14, 'type' => 'money'],
                ['label' => 'Próxima revisão', 'key' => 'proxima', 'width' => 14, 'type' => 'date'],
                ['label' => 'Resumo', 'key' => 'descricao', 'width' => 40],
            ],
            'rows' => $linhas,
            'totals' => ['valor' => $total, 'itens' => array_sum(array_column($linhas, 'itens'))],
            'totals_label' => 'TOTAL',
            'summary' => [
                ['label' => 'Manutenções', 'value' => (string) count($linhas), 'tone' => 'neutral'],
                ['label' => 'Custo total', 'value' => 'R$ ' . number_format($total, 2, ',', '.'), 'tone' => 'out'],
                ['label' => 'Itens trocados', 'value' => 'R$ ' . number_format((float) array_sum(array_column($linhas, 'itens')), 2, ',', '.'), 'tone' => 'neutral'],
            ],
            'notes' => 'Os valores de itens consideram quantidade × valor unitário lançados em cada manutenção.',
        ];
    }

    /* ===================================================================== */

    /**
     * Lê as linhas de itens enviadas pelo formulário.
     *
     * @return array<int,array{description:string,quantity:float,unit_cost:float}>
     */
    private function itens(): array
    {
        $linhas = $_POST['items'] ?? [];

        if (!is_array($linhas)) {
            return [];
        }

        $itens = [];

        foreach ($linhas as $linha) {
            if (!is_array($linha)) {
                continue;
            }

            $descricao = trim((string) ($linha['description'] ?? ''));

            if ($descricao === '') {
                continue;
            }

            $quantidade = $this->decimal($linha['quantity'] ?? null) ?? 1.0;
            $valorUnitario = $this->decimal($linha['unit_cost'] ?? null) ?? 0.0;

            $itens[] = [
                'description' => mb_substr($descricao, 0, 255),
                'quantity' => $quantidade > 0 ? $quantidade : 1.0,
                'unit_cost' => max(0, $valorUnitario),
            ];
        }

        return $itens;
    }

    /**
     * @param array<int,array{description:string,quantity:float,unit_cost:float}> $itens
     */
    private function salvarItens(int $manutencaoId, array $itens): void
    {
        foreach ($itens as $item) {
            $this->db()->insert('vehicle_maintenance_items', [
                'maintenance_id' => $manutencaoId,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
            ]);
        }
    }

    private function decimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = str_replace(['.', ','], ['', '.'], (string) $value);

        return is_numeric($normalized) ? round((float) $normalized, 2) : null;
    }
}
