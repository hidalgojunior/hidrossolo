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
        <h4 class="mb-0">Agenda de Manutenção</h4>
        <p class="text-muted small mb-0">
            Programe os compromissos por veículo/equipamento. Os avisos de
            <strong>30, 15 e 7 dias</strong> são enviados automaticamente.
        </p>
    </div>
    <button type="button" class="btn btn-primary" data-novo-compromisso>
        <i class="bi bi-calendar-plus me-1"></i>Novo compromisso
    </button>
</div>

<?php if (($avisosGerados ?? 0) > 0) { ?>
    <div class="alert alert-info">
        <i class="bi bi-bell me-1"></i>
        <strong><?= e((int) $avisosGerados) ?></strong> aviso(s) de manutenção gerado(s) agora e enviado(s) para as notificações do painel.
    </div>
<?php } ?>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-calendar-event"></i></div>
            <div><div class="stat-value"><?= e((int) ($resumo['proximos30'] ?? 0)) ?></div><div class="stat-label">Próximos 30 dias</div></div>
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
                                <span class="agenda-chip tipo-<?= e($ev['type']) ?> <?= $ev['status'] === 'done' ? 'is-done' : '' ?>"
                                      title="<?= e($ev['title']) ?>">
                                    <?php if (!empty($ev['scheduled_time'])) { ?>
                                        <span class="hora"><?= e(substr((string) $ev['scheduled_time'], 0, 5)) ?></span>
                                    <?php } ?>
                                    <?= e($ev['title']) ?>
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
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
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
                        <label class="form-label" for="agenda_date">Data *</label>
                        <input type="date" name="scheduled_date" id="agenda_date" class="form-control" required
                               value="<?= e(date('Y-m-d')) ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="agenda_time">Hora</label>
                        <input type="time" name="scheduled_time" id="agenda_time" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="agenda_type">Tipo</label>
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

                <div class="alert alert-info mt-3 mb-0 small">
                    <i class="bi bi-bell me-1"></i>
                    Você receberá avisos automáticos <strong>30, 15 e 7 dias</strong> antes desta data.
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

    function abrir(data) {
        if (data) dataInput.value = data;
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
})();
</script>
<?php $view->endSection(); ?>
