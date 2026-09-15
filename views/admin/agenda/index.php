<?php $view->layout('layouts.admin'); ?>

<?php
use App\Support\FleetAgenda;

$meses = [1 => 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto',
    'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$semana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

$primeiroDiaSemana = (int) date('w', strtotime($inicio));
$diasNoMes = (int) date('t', strtotime($inicio));
$hojeStr = date('Y-m-d');

$mesAnterior = $mes === 1 ? 12 : $mes - 1;
$anoAnterior = $mes === 1 ? $ano - 1 : $ano;
$mesSeguinte = $mes === 12 ? 1 : $mes + 1;
$anoSeguinte = $mes === 12 ? $ano + 1 : $ano;
?>

<?php $view->section('styles'); ?>
<link rel="stylesheet" href="<?= e(asset_v('assets/css/agenda.css')) ?>">
<?php $view->endSection(); ?>

<?php $view->section('content'); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="mb-0">Agenda &amp; Compromissos</h4>
        <p class="text-muted small mb-0">
            Manutenções, contas a pagar e a receber no mesmo calendário — veja o que precisa ser pago e quando.
            Os avisos de <strong>30, 15 e 7 dias</strong> são gerados automaticamente.
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="/admin/agenda/pdf?mes=<?= e($mes) ?>&ano=<?= e($ano) ?>" class="btn btn-outline-danger">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
        <a href="/admin/agenda/xlsx?mes=<?= e($mes) ?>&ano=<?= e($ano) ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i>Excel (XLSX)
        </a>
        <a href="/admin/financeiro?mes=<?= e(sprintf('%04d-%02d', $ano, $mes)) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-cash-coin me-1"></i>Fluxo de caixa
        </a>
        <button type="button" class="btn btn-primary" data-novo-compromisso>
            <i class="bi bi-calendar-plus me-1"></i>Novo lançamento
        </button>
    </div>
</div>

<?php if (($avisosGerados ?? 0) > 0) { ?>
    <div class="alert alert-info">
        <i class="bi bi-bell me-1"></i>
        <strong><?= e((int) $avisosGerados) ?></strong> aviso(s) de manutenção gerado(s) agora e enviado(s) para as notificações do painel.
    </div>
<?php } ?>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100" style="border-left:4px solid #16a34a">
            <div>
                <div class="stat-label">A receber no mês</div>
                <div class="stat-value">R$ <?= e(number_format((float) ($resumoFinanceiro['a_receber'] ?? 0), 2, ',', '.')) ?></div>
                <small class="text-muted"><?= e((string) ($resumoFinanceiro['a_receber_qtd'] ?? 0)) ?> lançamento(s)</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100" style="border-left:4px solid #dc2626">
            <div>
                <div class="stat-label">A pagar no mês</div>
                <div class="stat-value">R$ <?= e(number_format((float) ($resumoFinanceiro['a_pagar'] ?? 0), 2, ',', '.')) ?></div>
                <small class="text-muted"><?= e((string) ($resumoFinanceiro['a_pagar_qtd'] ?? 0)) ?> lançamento(s)</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100" style="border-left:4px solid #0891b2">
            <div>
                <div class="stat-label">Saldo previsto do mês</div>
                <div class="stat-value <?= (float) ($resumoFinanceiro['previsto'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                    R$ <?= e(number_format((float) ($resumoFinanceiro['previsto'] ?? 0), 2, ',', '.')) ?>
                </div>
                <small class="text-muted">Realizado: R$ <?= e(number_format((float) ($resumoFinanceiro['realizado'] ?? 0), 2, ',', '.')) ?></small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100" style="border-left:4px solid #d97706">
            <div>
                <div class="stat-label">Contas em atraso</div>
                <div class="stat-value">R$ <?= e(number_format((float) ($resumoFinanceiro['atrasadas'] ?? 0), 2, ',', '.')) ?></div>
                <small class="text-muted"><?= e((string) ($resumoFinanceiro['atrasadas_qtd'] ?? 0)) ?> conta(s) vencida(s)</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-calendar-event"></i></div>
            <div><div class="stat-value"><?= e((int) ($resumo['proximos30'] ?? 0)) ?></div><div class="stat-label">Manutenções em 30 dias</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100">
            <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-exclamation-triangle"></i></div>
            <div><div class="stat-value"><?= e((int) ($resumo['atrasados'] ?? 0)) ?></div><div class="stat-label">Atrasados</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-hourglass-split"></i></div>
            <div><div class="stat-value"><?= e((int) ($resumo['planejados'] ?? 0)) ?></div><div class="stat-label">Planejados</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-check2-circle"></i></div>
            <div><div class="stat-value"><?= e((int) ($resumo['concluidos'] ?? 0)) ?></div><div class="stat-label">Concluídos</div></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header bg-white">
                <div class="agenda-nav">
                    <a href="?mes=<?= e($mesAnterior) ?>&ano=<?= e($anoAnterior) ?>" class="btn btn-sm btn-light">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <span class="month"><?= e($meses[$mes]) ?> <?= e($ano) ?></span>
                    <a href="?mes=<?= e($mesSeguinte) ?>&ano=<?= e($anoSeguinte) ?>" class="btn btn-sm btn-light">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="/admin/agenda" class="btn btn-sm btn-outline-secondary">Hoje</a>

                    <div class="agenda-legend ms-auto">
                        <span><i class="agenda-dot" style="background:#f59e0b"></i> Manutenção</span>
                        <span><i class="agenda-dot" style="background:#0891b2"></i> Revisão</span>
                        <span><i class="agenda-dot" style="background:#1e40af"></i> Inspeção</span>
                        <span><i class="agenda-dot" style="background:#16a34a"></i> Entrada</span>
                        <span><i class="agenda-dot" style="background:#dc2626"></i> Saída</span>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="agenda-grid mb-1">
                    <?php foreach ($semana as $dia) { ?>
                        <div class="agenda-head"><?= e($dia) ?></div>
                    <?php } ?>
                </div>

                <div class="agenda-grid">
                    <?php for ($i = 0; $i < $primeiroDiaSemana; $i++) { ?>
                        <div class="agenda-day is-empty"></div>
                    <?php } ?>

                    <?php for ($dia = 1; $dia <= $diasNoMes; $dia++) { ?>
                        <?php
                        $dataStr = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
                        $doDia = $porDia[$dia] ?? [];
                        $classes = 'agenda-day';
                        if ($dataStr === $hojeStr) {
                            $classes .= ' is-today';
                        } elseif ($dataStr < $hojeStr) {
                            $classes .= ' is-past';
                        }
                        ?>
                        <button type="button" class="<?= e($classes) ?>" data-dia="<?= e($dataStr) ?>">
                            <span class="agenda-day-num">
                                <?= e($dia) ?>
                                <?php if ($doDia !== []) { ?>
                                    <span class="badge bg-primary"><?= e(count($doDia)) ?></span>
                                <?php } ?>
                            </span>

                            <?php foreach (array_slice($doDia, 0, 3) as $ev) { ?>
                                <?php
                                $classeTipo = $ev['origem'] === 'schedule' ? 'tipo-' . $ev['tipo'] : 'tipo-' . $ev['kind'];
                                $feito = in_array($ev['situacao'], ['Concluído', 'Pago', 'Recebido'], true);
                                ?>
                                <span class="agenda-chip <?= e($classeTipo) ?> <?= $feito ? 'is-done' : '' ?>"
                                      title="<?= e($ev['titulo'] . ($ev['ativo'] !== '' ? ' — ' . $ev['ativo'] : '') . ' (' . $ev['situacao'] . ')') ?>">
                                    <?php if (!empty($ev['hora'])) { ?>
                                        <span class="hora"><?= e($ev['hora']) ?></span>
                                    <?php } else { ?>
                                        <span class="hora"><i class="bi <?= e($ev['kind'] === 'income' ? 'bi-arrow-down' : ($ev['kind'] === 'expense' ? 'bi-arrow-up' : 'bi-tools')) ?>"></i></span>
                                    <?php } ?>
                                    <?= e($ev['titulo']) ?>
                                </span>
                            <?php } ?>

                            <?php if (count($doDia) > 3) { ?>
                                <span class="agenda-more">+<?= e(count($doDia) - 3) ?> mais</span>
                            <?php } ?>
                        </button>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header bg-white"><strong>Próximos 60 dias</strong></div>
            <div class="card-body">
                <?php if (empty($proximos)) { ?>
                    <p class="text-muted text-center py-4 mb-0">Nenhum compromisso agendado.</p>
                <?php } else { ?>
                    <div class="agenda-list">
                        <?php foreach ($proximos as $p) { ?>
                            <?php
                            $dias = (int) (new \DateTimeImmutable($p['scheduled_date']))->diff(new \DateTimeImmutable('today'))->format('%r%a');
                            $badge = $dias <= 7 ? 'd7' : ($dias <= 15 ? 'd15' : 'd30');
                            ?>
                            <div class="agenda-list-item tipo-<?= e($p['type']) ?>">
                                <div class="data">
                                    <?= e(date('d/m', strtotime($p['scheduled_date']))) ?>
                                    <small><?= e($dias === 0 ? 'hoje' : ($dias === 1 ? 'amanhã' : $dias . 'd')) ?></small>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="titulo"><?= e($p['title']) ?></div>
                                    <div class="meta">
                                        <?= e(asset_label($p)) ?>
                                        <?php if (!empty($p['scheduled_time'])) { ?>
                                            · <?= e(substr((string) $p['scheduled_time'], 0, 5)) ?>
                                        <?php } ?>
                                    </div>
                                    <div class="mt-1">
                                        <span class="agenda-alert-badge <?= e($badge) ?>">
                                            <i class="bi bi-bell"></i>
                                            aviso <?= e($badge === 'd7' ? '7' : ($badge === 'd15' ? '15' : '30')) ?> dias
                                        </span>
                                    </div>
                                </div>
                                <form method="POST" action="/admin/agenda/status/<?= e($p['id']) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="status" value="done">
                                    <input type="hidden" name="redirect" value="/admin/agenda?mes=<?= e($mes) ?>&ano=<?= e($ano) ?>">
                                    <button class="btn btn-sm btn-outline-success" title="Marcar como concluído">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <form method="POST" action="/admin/agenda/excluir/<?= e($p['id']) ?>"
                                      onsubmit="return confirm('Excluir este compromisso da agenda? Não há como desfazer.')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="redirect" value="/admin/agenda?mes=<?= e($mes) ?>&ano=<?= e($ano) ?>">
                                    <button class="btn btn-sm btn-outline-danger" title="Excluir compromisso">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Contas a vencer (30 dias)</strong>
                <a href="/admin/financeiro" class="btn btn-sm btn-light">Abrir caixa</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($contasProximas)) { ?>
                    <p class="text-muted text-center py-4 mb-0">Nenhuma conta a vencer nos próximos 30 dias.</p>
                <?php } else { ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($contasProximas as $c) { ?>
                            <?php $entrada = $c['kind'] === 'income'; ?>
                            <li class="d-flex justify-content-between align-items-start gap-2 px-3 py-2 border-bottom">
                                <div style="min-width:0">
                                    <div class="fw-semibold text-truncate"><?= e($c['description']) ?></div>
                                    <small class="text-muted">
                                        <?= e(date('d/m/Y', (int) strtotime((string) $c['due_date']))) ?>
                                        · <?= e(\App\Support\Financeiro::categoriaLabel($c['kind'], $c['category'])) ?>
                                    </small>
                                </div>
                                <span class="badge <?= $entrada ? 'bg-success' : 'bg-danger' ?> text-nowrap">
                                    <?= e($entrada ? '+' : '-') ?> R$ <?= e(number_format((float) $c['amount'], 2, ',', '.')) ?>
                                </span>
                                <form method="POST" action="/admin/financeiro/excluir/<?= e($c['id']) ?>"
                                      onsubmit="return confirm('Excluir este lançamento? Não há como desfazer.')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="redirect" value="/admin/agenda?mes=<?= e($mes) ?>&ano=<?= e($ano) ?>">
                                    <button class="btn btn-sm btn-outline-danger" title="Excluir lançamento">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </div>
        </div>

        <?php if (!empty($contasVencidas)) { ?>
            <div class="card mt-4 border-danger">
                <div class="card-header bg-danger-subtle"><strong class="text-danger"><i class="bi bi-exclamation-octagon me-1"></i>Contas em atraso</strong></div>
                <div class="card-body p-0">
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($contasVencidas as $c) { ?>
                            <?php $entrada = $c['kind'] === 'income'; ?>
                            <li class="d-flex justify-content-between align-items-start gap-2 px-3 py-2 border-bottom">
                                <div style="min-width:0">
                                    <div class="fw-semibold text-truncate"><?= e($c['description']) ?></div>
                                    <small class="text-muted">venceu em <?= e(date('d/m/Y', (int) strtotime((string) $c['due_date']))) ?></small>
                                </div>
                                <span class="badge bg-danger text-nowrap">
                                    <?= e($entrada ? '+' : '-') ?> R$ <?= e(number_format((float) $c['amount'], 2, ',', '.')) ?>
                                </span>
                                <form method="POST" action="/admin/financeiro/excluir/<?= e($c['id']) ?>"
                                      onsubmit="return confirm('Excluir este lançamento? Não há como desfazer.')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="redirect" value="/admin/agenda?mes=<?= e($mes) ?>&ano=<?= e($ano) ?>">
                                    <button class="btn btn-sm btn-outline-danger" title="Excluir lançamento">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<!-- Modal: novo compromisso -->
<div class="modal" id="agendaModal" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="/admin/agenda/novo" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Novo compromisso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label d-block">O que você quer agendar? *</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="entry_kind" id="tipoCompromisso" value="schedule" checked>
                            <label class="btn btn-outline-primary btn-sm" for="tipoCompromisso">
                                <i class="bi bi-tools me-1"></i>Manutenção
                            </label>

                            <input type="radio" class="btn-check" name="entry_kind" id="tipoSaida" value="expense">
                            <label class="btn btn-outline-danger btn-sm" for="tipoSaida">
                                <i class="bi bi-arrow-up-circle me-1"></i>Conta a pagar
                            </label>

                            <input type="radio" class="btn-check" name="entry_kind" id="tipoEntrada" value="income">
                            <label class="btn btn-outline-success btn-sm" for="tipoEntrada">
                                <i class="bi bi-arrow-down-circle me-1"></i>Conta a receber
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="agenda_vehicle">Veículo / Equipamento</label>
                        <select name="vehicle_id" id="agenda_vehicle" class="form-select">
                            <option value="">— Frota em geral —</option>
                            <?php
                            $vehiclesA = array_values(array_filter($veiculos, static fn(array $v): bool => ($v['category'] ?? 'vehicle') === 'vehicle'));
                            $equipA = array_values(array_filter($veiculos, static fn(array $v): bool => ($v['category'] ?? '') === 'equipment'));
                            ?>
                            <?php if ($vehiclesA !== []) { ?>
                                <optgroup label="Veículos">
                                    <?php foreach ($vehiclesA as $v) { ?>
                                        <option value="<?= e($v['id']) ?>"><?= e(asset_label($v)) ?></option>
                                    <?php } ?>
                                </optgroup>
                            <?php } ?>
                            <?php if ($equipA !== []) { ?>
                                <optgroup label="Equipamentos / Geradores">
                                    <?php foreach ($equipA as $v) { ?>
                                        <option value="<?= e($v['id']) ?>"><?= e(asset_label($v)) ?></option>
                                    <?php } ?>
                                </optgroup>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="agenda_title">Título *</label>
                        <input type="text" name="title" id="agenda_title" class="form-control" required maxlength="255"
                               placeholder="Ex.: Troca de pneus / Revisão dos 20.000 km">
                    </div>

                    <div class="col-6">
                        <label class="form-label" for="agenda_date" data-rotulo-data>Data *</label>
                        <input type="text" name="scheduled_date" id="agenda_date" class="form-control" required
                               data-date-br placeholder="dd/mm/aaaa"
                               value="<?= e(data_iso_para_br(date('Y-m-d'))) ?>">
                    </div>
                    <div class="col-6" data-grupo-hora>
                        <label class="form-label" for="agenda_time">Hora</label>
                        <input type="time" name="scheduled_time" id="agenda_time" class="form-control">
                    </div>

                    <!-- Campos financeiros -->
                    <div class="col-6 d-none" data-grupo-financeiro>
                        <label class="form-label" for="agenda_amount">Valor (R$) *</label>
                        <input type="text" name="amount" id="agenda_amount" class="form-control" placeholder="0,00">
                    </div>
                    <div class="col-6 d-none" data-grupo-financeiro>
                        <label class="form-label" for="agenda_party">Fornecedor / Cliente</label>
                        <input type="text" name="party" id="agenda_party" class="form-control">
                    </div>
                    <div class="col-12 d-none" data-grupo-financeiro>
                        <label class="form-label" for="agenda_category">Categoria</label>
                        <select name="category" id="agenda_category" class="form-select">
                            <optgroup label="Despesas" data-grupo-categoria="expense">
                                <?php foreach ($categoriasDespesa as $chave => $cat) { ?>
                                    <option value="<?= e($chave) ?>" data-cat-kind="expense"><?= e($cat['label']) ?></option>
                                <?php } ?>
                            </optgroup>
                            <optgroup label="Receitas" data-grupo-categoria="income">
                                <?php foreach ($categoriasReceita as $chave => $cat) { ?>
                                    <option value="<?= e($chave) ?>" data-cat-kind="income"><?= e($cat['label']) ?></option>
                                <?php } ?>
                            </optgroup>
                        </select>
                    </div>

                    <div class="col-12" data-grupo-compromisso>
                        <label class="form-label" for="agenda_type">Tipo de manutenção</label>
                        <select name="type" id="agenda_type" class="form-select">
                            <?php foreach ($tipos as $t) { ?>
                                <option value="<?= e($t) ?>" <?= $t === 'maintenance' ? 'selected' : '' ?>>
                                    <?= e(FleetAgenda::tipoLabel($t)) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="agenda_description">Observações</label>
                        <textarea name="description" id="agenda_description" class="form-control" rows="2"
                                  placeholder="Oficina, itens previstos, etc."></textarea>
                    </div>
                </div>

                <div class="alert alert-info mt-3 mb-0 small" data-grupo-compromisso>
                    <i class="bi bi-bell me-1"></i>
                    Você receberá avisos automáticos <strong>30, 15 e 7 dias</strong> antes desta data.
                </div>

                <div class="alert alert-warning mt-3 mb-0 small d-none" data-grupo-financeiro>
                    <i class="bi bi-cash-coin me-1"></i>
                    O lançamento entra no <strong>fluxo de caixa</strong> com vencimento nesta data e aparece nas notificações.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg me-1"></i>Agendar</button>
            </div>
        </form>
    </div>
</div>
<?php $view->endSection(); ?>

<?php $view->section('scripts'); ?>
<script>
(function () {
    var modal = document.getElementById('agendaModal');
    var dataInput = document.getElementById('agenda_date');

    // O calendário usa aaaa-mm-dd por dentro; o campo exibe dd/mm/aaaa.
    function isoParaBr(iso) {
        var partes = String(iso).split('-');

        return partes.length === 3 ? partes[2] + '/' + partes[1] + '/' + partes[0] : '';
    }

    function abrir(data) {
        if (data) dataInput.value = isoParaBr(data);
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    document.querySelector('[data-novo-compromisso]').addEventListener('click', function () { abrir(null); });

    document.querySelectorAll('.agenda-day[data-dia]').forEach(function (celula) {
        celula.addEventListener('click', function () { abrir(celula.getAttribute('data-dia')); });
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    });

    // Alterna entre compromisso de manutenção e lançamento financeiro
    var tituloModal = modal.querySelector('.modal-title');
    var categorias = document.getElementById('agenda_category');
    var valorInput = document.getElementById('agenda_amount');
    var rotuloData = document.querySelector('[data-rotulo-data]');

    function atualizarTipo() {
        var kind = document.querySelector('input[name="entry_kind"]:checked').value;
        var financeiro = kind !== 'schedule';

        document.querySelectorAll('[data-grupo-financeiro]').forEach(function (el) {
            el.classList.toggle('d-none', !financeiro);
        });
        document.querySelectorAll('[data-grupo-compromisso]').forEach(function (el) {
            el.classList.toggle('d-none', financeiro);
        });
        document.querySelectorAll('[data-grupo-hora]').forEach(function (el) {
            el.classList.toggle('d-none', financeiro);
        });

        if (valorInput) { valorInput.required = financeiro; }
        if (rotuloData) { rotuloData.textContent = financeiro ? 'Vencimento *' : 'Data *'; }

        if (categorias) {
            var grupos = categorias.querySelectorAll('[data-grupo-categoria]');
            grupos.forEach(function (grupo) {
                var combina = financeiro && grupo.dataset.grupoCategoria === kind;
                grupo.disabled = !combina;
                grupo.hidden = !combina;
            });

            // Seleciona a primeira opção válida do grupo ativo
            if (financeiro) {
                var primeira = categorias.querySelector('option[data-cat-kind="' + kind + '"]');
                if (primeira) { categorias.value = primeira.value; }
            }
        }

        if (tituloModal) {
            tituloModal.innerHTML = '<i class="bi ' + (kind === 'income' ? 'bi-arrow-down-circle'
                : (kind === 'expense' ? 'bi-arrow-up-circle' : 'bi-calendar-plus')) + ' me-2"></i>'
                + (kind === 'income' ? 'Nova conta a receber' : (kind === 'expense' ? 'Nova conta a pagar' : 'Novo compromisso'));
        }
    }

    document.querySelectorAll('input[name="entry_kind"]').forEach(function (radio) {
        radio.addEventListener('change', atualizarTipo);
    });
    atualizarTipo();

    // Abre o formulário já no dia indicado por ?dia= (usado pelas notificações)
    var diaParam = new URLSearchParams(location.search).get('dia');
    if (diaParam && document.querySelector('.agenda-day[data-dia$="-' + String(diaParam).padStart(2, '0') + '"]')) {
        var celula = document.querySelector('.agenda-day[data-dia$="-' + String(diaParam).padStart(2, '0') + '"]');
        celula.classList.add('is-focus');
        abrir(celula.getAttribute('data-dia'));
    }
})();
</script>
<?php $view->endSection(); ?>
