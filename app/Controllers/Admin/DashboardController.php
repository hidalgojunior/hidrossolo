<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();

        // Estatísticas
        $totalContatos = $db->fetch("SELECT COUNT(*) as total FROM contacts")['total'] ?? 0;
        $contatosNovos = $db->fetch("SELECT COUNT(*) as total FROM contacts WHERE status = 'new'")['total'] ?? 0;
        $totalVeiculos = $db->fetch("SELECT COUNT(*) as total FROM vehicles WHERE status = 'active'")['total'] ?? 0;
        $contratosAtivos = $db->fetch("SELECT COUNT(*) as total FROM contracts WHERE status = 'active'")['total'] ?? 0;
        $manutencoesPendentes = $db->fetch(
            "SELECT COUNT(*) as total FROM vehicle_maintenance WHERE maintenance_date >= CURDATE()"
        )['total'] ?? 0;

        // Dados para gráficos
        $chartManutencao = $db->fetchAll(
            "SELECT DATE_FORMAT(maintenance_date,'%Y-%m') as mes, SUM(cost) as total FROM vehicle_maintenance WHERE maintenance_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY mes ORDER BY mes"
        );
        $chartCombustivel = $db->fetchAll(
            "SELECT DATE_FORMAT(fuel_date,'%Y-%m') as mes, SUM(cost) as total, SUM(liters) as litros FROM vehicle_fuel WHERE fuel_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY mes ORDER BY mes"
        );

        // Últimos contatos
        $ultimosContatos = $db->fetchAll("SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5");

        // Contratos próximos do vencimento (30 dias)
        $contratosVencendo = $db->fetchAll("SELECT * FROM contracts WHERE status='active' AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY end_date ASC");

        // Notificações
        $notificacoes = $db->fetchAll("SELECT * FROM notifications WHERE read_at IS NULL ORDER BY created_at DESC LIMIT 10");
        $totalNotificacoes = $db->fetch("SELECT COUNT(*) as c FROM notifications WHERE read_at IS NULL")['c'] ?? 0;

        // ---- Frota & Equipamentos ----
        $frotaContagem = $db->fetch(
            "SELECT
                COALESCE(SUM(category = 'vehicle' AND status = 'active'), 0) AS veiculos_ativos,
                COALESCE(SUM(category = 'equipment' AND status = 'active'), 0) AS equipamentos_ativos,
                COALESCE(SUM(status = 'maintenance'), 0) AS em_manutencao
             FROM vehicles"
        );

        $custoCombMes = (float) ($db->fetch(
            "SELECT COALESCE(SUM(cost),0) AS c FROM vehicle_fuel
             WHERE fuel_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
        )['c'] ?? 0);

        $custoManutMes = (float) ($db->fetch(
            "SELECT COALESCE(SUM(cost),0) AS c FROM vehicle_maintenance
             WHERE maintenance_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
        )['c'] ?? 0);

        $litrosMes = (float) ($db->fetch(
            "SELECT COALESCE(SUM(liters),0) AS c FROM vehicle_fuel
             WHERE fuel_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
        )['c'] ?? 0);

        $frotaTop = $db->fetchAll(
            "SELECT v.id, v.plate, v.brand, v.model, v.category, v.equipment_type, v.fuel_type,
                    COALESCE(fc.custo, 0) AS custo_combustivel,
                    COALESCE(mt.custo, 0) AS custo_manutencao,
                    (COALESCE(fc.custo, 0) + COALESCE(mt.custo, 0)) AS custo_total
             FROM vehicles v
             LEFT JOIN (
                 SELECT vehicle_id, SUM(cost) AS custo FROM vehicle_fuel
                 WHERE fuel_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY vehicle_id
             ) fc ON fc.vehicle_id = v.id
             LEFT JOIN (
                 SELECT vehicle_id, SUM(cost) AS custo FROM vehicle_maintenance
                 WHERE maintenance_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY vehicle_id
             ) mt ON mt.vehicle_id = v.id
             ORDER BY custo_total DESC
             LIMIT 5"
        );

        // Resumo por tipo de combustível (12 meses) — usado para colorir o painel
        $frotaPorCombustivel = $db->fetchAll(
            "SELECT v.fuel_type, v.category,
                    COUNT(DISTINCT v.id) AS ativos,
                    COALESCE(SUM(f.liters), 0) AS litros,
                    COALESCE(SUM(f.cost), 0) AS custo,
                    COALESCE(SUM(m.cost), 0) AS manutencao
             FROM vehicles v
             LEFT JOIN vehicle_fuel f
                    ON f.vehicle_id = v.id AND f.fuel_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             LEFT JOIN vehicle_maintenance m
                    ON m.vehicle_id = v.id AND m.maintenance_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             WHERE v.status <> 'inactive'
             GROUP BY v.fuel_type, v.category
             ORDER BY v.category, v.fuel_type"
        );

        $agendaProxima = $db->fetchAll(
            "SELECT s.id, s.title, s.scheduled_date, s.type, v.plate, v.brand, v.model, v.category, v.equipment_type
             FROM fleet_schedules s
             LEFT JOIN vehicles v ON v.id = s.vehicle_id
             WHERE s.status = 'planned' AND s.scheduled_date >= CURDATE()
             ORDER BY s.scheduled_date ASC LIMIT 4"
        );

        $ultimosLancamentosFrota = $db->fetchAll(
            "SELECT 'fuel' AS tipo, f.fuel_date AS data, f.cost, f.liters, v.plate, v.equipment_type, v.category, u.name AS usuario
             FROM vehicle_fuel f
             JOIN vehicles v ON v.id = f.vehicle_id
             LEFT JOIN users u ON u.id = f.user_id
             ORDER BY f.id DESC
             LIMIT 5"
        );

        echo $this->view('admin.dashboard', [
            'title' => 'Dashboard',
            'totalContatos' => $totalContatos,
            'contatosNovos' => $contatosNovos,
            'totalVeiculos' => $totalVeiculos,
            'contratosAtivos' => $contratosAtivos,
            'manutencoesPendentes' => $manutencoesPendentes,
            'notificacoes' => $notificacoes,
            'totalNotificacoes' => $totalNotificacoes,
            'chartManutencao' => $chartManutencao,
            'chartCombustivel' => $chartCombustivel,
            'ultimosContatos' => $ultimosContatos,
            'contratosVencendo' => $contratosVencendo,
            'frotaContagem' => $frotaContagem,
            'custoCombMes' => $custoCombMes,
            'custoManutMes' => $custoManutMes,
            'litrosMes' => $litrosMes,
            'frotaTop' => $frotaTop,
            'frotaPorCombustivel' => $frotaPorCombustivel,
            'agendaProxima' => $agendaProxima,
            'ultimosLancamentosFrota' => $ultimosLancamentosFrota,
            'config' => $this->config('company'),
        ]);
    }
}
