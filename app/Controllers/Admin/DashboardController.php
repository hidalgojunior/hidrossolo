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
            'config' => $this->config('company'),
        ]);
    }
}
