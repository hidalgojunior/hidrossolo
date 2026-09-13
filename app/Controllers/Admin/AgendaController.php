<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Security;
use App\Support\FleetAgenda;

/**
 * Agenda de manutenções: calendário mensal de compromissos por veículo/equipamento.
 */
class AgendaController extends BaseController
{
    private const TIPOS = ['maintenance', 'revision', 'inspection', 'other'];

    public function index(): void
    {
        $db = $this->db();

        // Gera os avisos de 30/15/7 dias antes de exibir a agenda
        $avisos = FleetAgenda::notifyUpcoming();

        $hoje = new \DateTimeImmutable('today');

        $mes = (int) ($_GET['mes'] ?? $hoje->format('n'));
        $ano = (int) ($_GET['ano'] ?? $hoje->format('Y'));

        if ($mes < 1 || $mes > 12) {
            $mes = (int) $hoje->format('n');
        }
        if ($ano < 2000 || $ano > 2100) {
            $ano = (int) $hoje->format('Y');
        }

        $inicio = sprintf('%04d-%02d-01', $ano, $mes);
        $fim = date('Y-m-t', strtotime($inicio));

        $agendamentos = $db->fetchAll(
            "SELECT s.*, v.plate, v.brand, v.model, v.category, v.equipment_type, v.fuel_type
             FROM fleet_schedules s
             LEFT JOIN vehicles v ON v.id = s.vehicle_id
             WHERE s.scheduled_date BETWEEN ? AND ?
             ORDER BY s.scheduled_date, s.scheduled_time, s.id",
            [$inicio, $fim]
        );

        $porDia = [];

        foreach ($agendamentos as $a) {
            $porDia[(int) substr((string) $a['scheduled_date'], 8, 2)][] = $a;
        }

        // Próximos compromissos (60 dias), independentes do mês exibido
        $proximos = $db->fetchAll(
            "SELECT s.*, v.plate, v.brand, v.model, v.category, v.equipment_type
             FROM fleet_schedules s
             LEFT JOIN vehicles v ON v.id = s.vehicle_id
             WHERE s.status = 'planned' AND s.scheduled_date >= CURDATE()
               AND s.scheduled_date <= DATE_ADD(CURDATE(), INTERVAL 60 DAY)
             ORDER BY s.scheduled_date ASC
             LIMIT 12"
        );

        $resumo = $db->fetch(
            "SELECT
                COALESCE(SUM(status = 'planned'), 0) AS planejados,
                COALESCE(SUM(status = 'done'), 0) AS concluidos,
                COALESCE(SUM(status = 'planned' AND scheduled_date < CURDATE()), 0) AS atrasados,
                COALESCE(SUM(status = 'planned' AND scheduled_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)), 0) AS proximos30
             FROM fleet_schedules"
        );

        echo $this->view('admin.agenda.index', [
            'title' => 'Agenda de Manutenção',
            'mes' => $mes,
            'ano' => $ano,
            'inicio' => $inicio,
            'fim' => $fim,
            'porDia' => $porDia,
            'agendamentos' => $agendamentos,
            'proximos' => $proximos,
            'resumo' => $resumo,
            'avisosGerados' => $avisos,
            'veiculos' => $this->ativos(),
            'tipos' => self::TIPOS,
            'config' => $this->config('company'),
        ]);
    }

    public function store(): void
    {
        $titulo = trim((string) ($_POST['title'] ?? ''));
        $data = trim((string) ($_POST['scheduled_date'] ?? ''));
        $hora = trim((string) ($_POST['scheduled_time'] ?? ''));
        $tipo = (string) ($_POST['type'] ?? 'maintenance');
        $descricao = trim((string) ($_POST['description'] ?? ''));
        $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);

        $erros = [];

        if ($titulo === '') {
            $erros[] = 'Informe o título do compromisso.';
        }
        if ($data === '' || !strtotime($data)) {
            $erros[] = 'Informe uma data válida.';
        }
        if (!in_array($tipo, self::TIPOS, true)) {
            $tipo = 'maintenance';
        }

        if ($erros !== []) {
            $_SESSION['flash_error'] = implode(' ', $erros);
            $this->redirect('/admin/agenda?mes=' . (int) date('n', strtotime($data ?: 'now')) . '&ano=' . (int) date('Y', strtotime($data ?: 'now')));
        }

        $id = $this->db()->insert('fleet_schedules', [
            'vehicle_id' => $vehicleId > 0 ? $vehicleId : null,
            'title' => mb_substr($titulo, 0, 255),
            'description' => $descricao !== '' ? mb_substr($descricao, 0, 500) : null,
            'scheduled_date' => date('Y-m-d', strtotime($data)),
            'scheduled_time' => $hora !== '' ? $hora : null,
            'type' => $tipo,
            'status' => 'planned',
            'created_by' => (int) ($_SESSION['user_id'] ?? 0),
        ]);

        Security::audit('schedule_created', 'fleet_schedules', $id, ['data' => $data, 'tipo' => $tipo]);

        $_SESSION['flash_success'] = 'Compromisso agendado! Os avisos de 30, 15 e 7 dias serão enviados automaticamente.';
        $this->redirect('/admin/agenda?mes=' . (int) date('n', strtotime($data)) . '&ano=' . (int) date('Y', strtotime($data)));
    }

    public function status(string $id): void
    {
        $id = (int) $id;
        $agenda = $this->db()->fetch("SELECT * FROM fleet_schedules WHERE id = ?", [$id]);

        if (!$agenda) {
            $_SESSION['flash_error'] = 'Compromisso não encontrado.';
            $this->redirect('/admin/agenda');
        }

        $status = (string) ($_POST['status'] ?? 'done');

        if (!in_array($status, ['planned', 'done', 'cancelled'], true)) {
            $status = 'done';
        }

        $this->db()->update('fleet_schedules', ['status' => $status], 'id = ?', [$id]);
        Security::audit('schedule_' . $status, 'fleet_schedules', $id);

        $_SESSION['flash_success'] = 'Compromisso atualizado.';
        $this->redirect($_POST['redirect'] ?? '/admin/agenda');
    }

    public function delete(string $id): void
    {
        $id = (int) $id;
        $this->db()->delete('fleet_schedules', 'id = ?', [$id]);
        Security::audit('schedule_deleted', 'fleet_schedules', $id);

        $_SESSION['flash_success'] = 'Compromisso removido.';
        $this->redirect('/admin/agenda');
    }

    /* ===================================================================== */

    private function ativos(): array
    {
        return $this->db()->fetchAll(
            "SELECT id, plate, brand, model, category, equipment_type, fuel_type
             FROM vehicles WHERE status <> 'inactive'
             ORDER BY category, COALESCE(plate, equipment_type), brand"
        );
    }
}
