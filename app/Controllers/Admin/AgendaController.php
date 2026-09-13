<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Security;
use App\Support\Financeiro;
use App\Support\FleetAgenda;
use App\Support\PdfReport;
use App\Support\XlsxWriter;

/**
 * Agenda unificada: compromissos da frota e o fluxo de caixa da empresa.
 *
 * O calendário mostra, no mesmo dia, as manutenções previstas, contas a pagar
 * e contas a receber — permitindo enxergar o que precisa ser pago e quando.
 */
class AgendaController extends BaseController
{
    private const TIPOS = ['maintenance', 'revision', 'inspection', 'other'];

    /** Tipos de lançamento que o calendário aceita */
    private const TIPOS_LANCAMENTO = ['schedule', 'income', 'expense'];

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

        // Visão unificada do mês: compromissos da frota + entradas e saídas do caixa
        $eventos = $this->eventos($inicio, $fim);

        $porDia = [];

        foreach ($eventos as $evento) {
            $porDia[(int) substr((string) $evento['data'], 8, 2)][] = $evento;
        }

        $resumoFinanceiro = Financeiro::resumoPeriodo($inicio, $fim);

        // Contas a vencer nos próximos 30 dias
        $contasProximas = $db->fetchAll(
            "SELECT fe.*, v.plate, v.brand, v.model, v.category AS vehicle_category, v.equipment_type
             FROM financial_entries fe
             LEFT JOIN vehicles v ON v.id = fe.vehicle_id
             WHERE fe.status = 'pending'
               AND fe.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
             ORDER BY fe.due_date ASC
             LIMIT 10"
        );

        // Contas vencidas e ainda pendentes
        $contasVencidas = $db->fetchAll(
            "SELECT fe.*, v.plate, v.brand, v.model, v.category AS vehicle_category, v.equipment_type
             FROM financial_entries fe
             LEFT JOIN vehicles v ON v.id = fe.vehicle_id
             WHERE fe.status = 'pending' AND fe.due_date < CURDATE()
             ORDER BY fe.due_date ASC
             LIMIT 10"
        );

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
            'title' => 'Agenda & Compromissos',
            'mes' => $mes,
            'ano' => $ano,
            'inicio' => $inicio,
            'fim' => $fim,
            'porDia' => $porDia,
            'eventos' => $eventos,
            'agendamentos' => $agendamentos,
            'proximos' => $proximos,
            'contasProximas' => $contasProximas,
            'contasVencidas' => $contasVencidas,
            'resumo' => $resumo,
            'resumoFinanceiro' => $resumoFinanceiro,
            'avisosGerados' => $avisos,
            'veiculos' => $this->ativos(),
            'tipos' => self::TIPOS,
            'categoriasDespesa' => Financeiro::CATEGORIAS_DESPESA,
            'categoriasReceita' => Financeiro::CATEGORIAS_RECEITA,
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
        $lancamento = (string) ($_POST['entry_kind'] ?? 'schedule');

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
        if (!in_array($lancamento, self::TIPOS_LANCAMENTO, true)) {
            $lancamento = 'schedule';
        }

        // Lançamentos financeiros também podem ser criados direto do calendário
        $valor = null;
        if ($lancamento !== 'schedule') {
            $valor = $this->decimal($_POST['amount'] ?? null);

            if ($valor === null || $valor <= 0) {
                $erros[] = 'Informe o valor do lançamento financeiro.';
            }
        }

        if ($erros !== []) {
            $_SESSION['flash_error'] = implode(' ', $erros);
            $this->redirect('/admin/agenda?mes=' . (int) date('n', strtotime($data ?: 'now')) . '&ano=' . (int) date('Y', strtotime($data ?: 'now')));
        }

        $dataSql = date('Y-m-d', strtotime($data));

        if ($lancamento !== 'schedule') {
            $categoria = (string) ($_POST['category'] ?? '');
            $categorias = Financeiro::categorias($lancamento);

            if (!array_key_exists($categoria, $categorias)) {
                $categoria = $lancamento === 'income' ? 'outras_receitas' : 'outros';
            }

            $id = $this->db()->insert('financial_entries', [
                'kind' => $lancamento,
                'category' => $categoria,
                'description' => mb_substr($titulo, 0, 255),
                'amount' => $valor,
                'due_date' => $dataSql,
                'status' => 'pending',
                'party' => mb_substr(trim((string) ($_POST['party'] ?? '')), 0, 160) ?: null,
                'vehicle_id' => $vehicleId > 0 ? $vehicleId : null,
                'recurrence' => 'none',
                'notes' => $descricao !== '' ? mb_substr($descricao, 0, 500) : null,
                'created_by' => (int) ($_SESSION['user_id'] ?? 0),
            ]);

            Security::audit('finance_created', 'financial_entries', $id, [
                'origem' => 'agenda',
                'kind' => $lancamento,
                'due_date' => $dataSql,
            ]);

            $_SESSION['flash_success'] = $lancamento === 'income'
                ? 'Conta a receber agendada no fluxo de caixa!'
                : 'Conta a pagar agendada no fluxo de caixa!';

            $this->redirect('/admin/agenda?mes=' . (int) date('n', strtotime($dataSql)) . '&ano=' . (int) date('Y', strtotime($dataSql)));
        }

        $id = $this->db()->insert('fleet_schedules', [
            'vehicle_id' => $vehicleId > 0 ? $vehicleId : null,
            'title' => mb_substr($titulo, 0, 255),
            'description' => $descricao !== '' ? mb_substr($descricao, 0, 500) : null,
            'scheduled_date' => $dataSql,
            'scheduled_time' => $hora !== '' ? $hora : null,
            'type' => $tipo,
            'status' => 'planned',
            'created_by' => (int) ($_SESSION['user_id'] ?? 0),
        ]);

        Security::audit('schedule_created', 'fleet_schedules', $id, ['data' => $data, 'tipo' => $tipo]);

        $_SESSION['flash_success'] = 'Compromisso agendado! Os avisos de 30, 15 e 7 dias serão enviados automaticamente.';
        $this->redirect('/admin/agenda?mes=' . (int) date('n', strtotime($dataSql)) . '&ano=' . (int) date('Y', strtotime($dataSql)));
    }

    /**
     * Exporta a agenda do mês (compromissos + contas) em PDF.
     */
    public function pdf(): void
    {
        [$inicio, $fim, $rotulo] = $this->periodoExport();
        $eventos = $this->eventos($inicio, $fim);
        $resumo = Financeiro::resumoPeriodo($inicio, $fim);

        Security::audit('agenda_export_pdf', 'fleet_schedules', null, ['periodo' => $inicio . '..' . $fim]);

        PdfReport::download('agenda-' . $inicio . '-a-' . $fim, [
            'title' => 'Agenda & compromissos',
            'subtitle' => $rotulo,
            'company' => $this->config('company'),
            'columns' => self::colunasExport(),
            'rows' => array_map([$this, 'linhaExport'], $eventos),
            'totals' => ['valor' => $this->totalNoMes($eventos, 'expense')],
            'totals_label' => 'TOTAL A PAGAR NO PERÍODO',
            'summary' => [
                ['label' => 'Compromissos', 'value' => (string) count($eventos), 'tone' => 'neutral', 'hint' => 'agenda do período'],
                ['label' => 'A receber', 'value' => $this->moeda((float) $resumo['a_receber']), 'tone' => 'in', 'hint' => $resumo['a_receber_qtd'] . ' lançamento(s)'],
                ['label' => 'A pagar', 'value' => $this->moeda((float) $resumo['a_pagar']), 'tone' => 'out', 'hint' => $resumo['a_pagar_qtd'] . ' lançamento(s)'],
                ['label' => 'Saldo previsto', 'value' => $this->moeda((float) $resumo['previsto']), 'tone' => (float) $resumo['previsto'] >= 0 ? 'in' : 'out'],
                ['label' => 'Em atraso', 'value' => $this->moeda((float) $resumo['atrasadas']), 'tone' => 'warn', 'hint' => $resumo['atrasadas_qtd'] . ' conta(s)'],
            ],
            'notes' => 'Compromissos de manutenção são gerados a partir da agenda da frota; entradas e saídas vêm do fluxo de caixa da empresa.',
        ]);
    }

    /**
     * Exporta a agenda do mês (compromissos + contas) em XLSX formatado.
     */
    public function xlsx(): void
    {
        [$inicio, $fim, $rotulo] = $this->periodoExport();
        $eventos = $this->eventos($inicio, $fim);
        $resumo = Financeiro::resumoPeriodo($inicio, $fim);

        Security::audit('agenda_export_xlsx', 'fleet_schedules', null, ['periodo' => $inicio . '..' . $fim]);

        $xlsx = new XlsxWriter('Hidrossolo');

        $xlsx->addSheet('Agenda', self::colunasExport(), array_map([$this, 'linhaExport'], $eventos), [
            'title' => 'Agenda & compromissos',
            'subtitle' => $rotulo,
            'freeze' => 4,
        ]);

        $xlsx->addSheet('Resumo do período', [
            ['label' => 'Indicador', 'key' => 'indicador', 'width' => 30],
            ['label' => 'Valor', 'key' => 'valor', 'width' => 20],
        ], [
            ['indicador' => 'Compromissos no período', 'valor' => (string) count($eventos)],
            ['indicador' => 'A receber (pendente)', 'valor' => $this->moeda((float) $resumo['a_receber'])],
            ['indicador' => 'A pagar (pendente)', 'valor' => $this->moeda((float) $resumo['a_pagar'])],
            ['indicador' => 'Recebido', 'valor' => $this->moeda((float) $resumo['recebido'])],
            ['indicador' => 'Pago', 'valor' => $this->moeda((float) $resumo['pago'])],
            ['indicador' => 'Saldo realizado', 'valor' => $this->moeda((float) $resumo['realizado'])],
            ['indicador' => 'Saldo previsto', 'valor' => $this->moeda((float) $resumo['previsto'])],
            ['indicador' => 'Contas em atraso', 'valor' => $this->moeda((float) $resumo['atrasadas'])],
        ], [
            'title' => 'Resumo do período',
            'zebra' => false,
        ]);

        $xlsx->download('agenda-' . $inicio . '-a-' . $fim . '.xlsx');
    }

    /**
     * @return array{label:string,key:string,width:int,type:string}[]
     */
    public static function colunasExport(): array
    {
        return [
            ['label' => 'Data', 'key' => 'data', 'width' => 12, 'type' => 'date'],
            ['label' => 'Hora', 'key' => 'hora', 'width' => 8, 'type' => 'center'],
            ['label' => 'Tipo', 'key' => 'tipo', 'width' => 12, 'type' => 'center'],
            ['label' => 'Descrição', 'key' => 'titulo', 'width' => 46],
            ['label' => 'Ativo', 'key' => 'ativo', 'width' => 26],
            ['label' => 'Valor (R$)', 'key' => 'valor', 'width' => 16, 'type' => 'money'],
            ['label' => 'Situação', 'key' => 'situacao', 'width' => 14, 'type' => 'center'],
            ['label' => 'Detalhes', 'key' => 'detalhes', 'width' => 30],
        ];
    }

    /**
     * @param  array<string,mixed> $evento
     * @return array<string,mixed>
     */
    public function linhaExport(array $evento): array
    {
        return [
            'data' => (string) $evento['data'],
            'hora' => (string) ($evento['hora'] ?? ''),
            'tipo' => (string) $evento['tipo_label'],
            'titulo' => (string) $evento['titulo'],
            'ativo' => (string) $evento['ativo'],
            'valor' => $evento['valor'] !== null ? (float) $evento['valor'] : null,
            'situacao' => (string) $evento['situacao'],
            'detalhes' => (string) $evento['detalhes'],
        ];
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

    /**
     * Visão unificada do período: compromissos da frota + entradas/saídas.
     *
     * @return array<int,array<string,mixed>>
     */
    private function eventos(string $inicio, string $fim): array
    {
        $rotulos = [
            'maintenance' => 'Manutenção',
            'revision' => 'Revisão',
            'inspection' => 'Inspeção',
            'other' => 'Compromisso',
        ];

        $eventos = [];
        $hoje = date('Y-m-d');

        // Compromissos da frota
        $agendamentos = $this->db()->fetchAll(
            "SELECT s.*, v.plate, v.brand, v.model, v.category, v.equipment_type
             FROM fleet_schedules s
             LEFT JOIN vehicles v ON v.id = s.vehicle_id
             WHERE s.scheduled_date BETWEEN ? AND ? AND s.status <> 'cancelled'
             ORDER BY s.scheduled_date, s.scheduled_time, s.id",
            [$inicio, $fim]
        );

        foreach ($agendamentos as $a) {
            $temAtivo = !empty($a['plate']) || !empty($a['model']);

            $eventos[] = [
                'id' => (int) $a['id'],
                'origem' => 'schedule',
                'kind' => 'schedule',
                'tipo' => (string) $a['type'],
                'tipo_label' => $rotulos[$a['type']] ?? 'Compromisso',
                'data' => (string) $a['scheduled_date'],
                'hora' => !empty($a['scheduled_time']) ? substr((string) $a['scheduled_time'], 0, 5) : null,
                'titulo' => (string) $a['title'],
                'descricao' => (string) ($a['description'] ?? ''),
                'ativo' => $temAtivo ? asset_label($a) : '',
                'valor' => null,
                'situacao' => match ((string) $a['status']) {
                    'done' => 'Concluído',
                    'cancelled' => 'Cancelado',
                    default => (string) $a['scheduled_date'] < $hoje ? 'Atrasado' : 'Planejado',
                },
                'detalhes' => trim((string) ($a['description'] ?? '')),
                'referencia' => '/admin/agenda?mes=' . date('n', (int) strtotime((string) $a['scheduled_date']))
                    . '&ano=' . date('Y', (int) strtotime((string) $a['scheduled_date']))
                    . '&dia=' . date('j', (int) strtotime((string) $a['scheduled_date'])),
            ];
        }

        // Contas a pagar e a receber
        $lancamentos = $this->db()->fetchAll(
            "SELECT fe.*, v.plate, v.brand, v.model, v.category AS vehicle_category, v.equipment_type
             FROM financial_entries fe
             LEFT JOIN vehicles v ON v.id = fe.vehicle_id
             WHERE fe.due_date BETWEEN ? AND ? AND fe.status <> 'cancelled'
             ORDER BY fe.due_date, fe.id",
            [$inicio, $fim]
        );

        foreach ($lancamentos as $l) {
            $entrada = $l['kind'] === 'income';
            $temAtivo = !empty($l['plate']) || !empty($l['model']);

            $eventos[] = [
                'id' => (int) $l['id'],
                'origem' => 'finance',
                'kind' => (string) $l['kind'],
                'tipo' => (string) $l['kind'],
                'tipo_label' => $entrada ? 'Entrada' : 'Saída',
                'data' => (string) $l['due_date'],
                'hora' => null,
                'titulo' => (string) $l['description'],
                'descricao' => Financeiro::categoriaLabel($l['kind'], $l['category']),
                'ativo' => $temAtivo ? asset_label($l) : '',
                'valor' => (float) $l['amount'],
                'situacao' => match ((string) $l['status']) {
                    'paid' => $entrada ? 'Recebido' : 'Pago',
                    default => (string) $l['due_date'] < $hoje ? 'Em atraso' : 'Pendente',
                },
                'detalhes' => trim(implode(' · ', array_filter([
                    (string) ($l['party'] ?? ''),
                    !empty($l['document']) ? 'Doc. ' . $l['document'] : '',
                ]))),
                'referencia' => '/admin/financeiro/editar/' . (int) $l['id'],
            ];
        }

        usort($eventos, static function (array $a, array $b): int {
            return [$a['data'], (string) ($a['hora'] ?? '99:99'), $a['origem']]
                <=> [$b['data'], (string) ($b['hora'] ?? '99:99'), $b['origem']];
        });

        return $eventos;
    }

    /**
     * @return array{0:string,1:string,2:string}
     */
    private function periodoExport(): array
    {
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
        $fim = date('Y-m-t', (int) strtotime($inicio));

        return [
            $inicio,
            $fim,
            'Período de ' . date('d/m/Y', (int) strtotime($inicio)) . ' a ' . date('d/m/Y', (int) strtotime($fim)),
        ];
    }

    /**
     * @param array<int,array<string,mixed>> $eventos
     */
    private function totalNoMes(array $eventos, string $kind): float
    {
        $total = 0.0;

        foreach ($eventos as $evento) {
            if ($evento['kind'] === $kind && $evento['valor'] !== null) {
                $total += (float) $evento['valor'];
            }
        }

        return $total;
    }

    private function moeda(float $valor): string
    {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }

    private function decimal(mixed $valor): ?float
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $normalizado = str_replace(['.', ','], ['', '.'], (string) $valor);

        return is_numeric($normalizado) ? round((float) $normalizado, 2) : null;
    }

    private function ativos(): array
    {
        return $this->db()->fetchAll(
            "SELECT id, plate, brand, model, category, equipment_type, fuel_type
             FROM vehicles WHERE status <> 'inactive'
             ORDER BY category, COALESCE(plate, equipment_type), brand"
        );
    }
}
