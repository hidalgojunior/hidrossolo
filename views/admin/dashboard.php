<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<h4 class="mb-4">Dashboard</h4>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-envelope"></i></div>
            <div>
                <div class="stat-value"><?= e($contatosNovos) ?></div>
                <div class="stat-label">Mensagens Novas</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-file-earmark-text"></i></div>
            <div>
                <div class="stat-value"><?= e($contratosAtivos) ?></div>
                <div class="stat-label">Contratos Ativos</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-truck"></i></div>
            <div>
                <div class="stat-value"><?= e($totalVeiculos) ?></div>
                <div class="stat-label">Veículos Ativos</div>
            </div>
        </div>
    </div>
</div>

<!-- Atalhos do app (PWA do gestor) -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <strong><i class="bi bi-lightning-charge me-1"></i> Atalhos rápidos</strong>
        <div class="small text-muted">Agenda, contas e lançamentos da frota — inclusive pelo celular.</div>
    </div>
    <div class="card-body">
        <div class="row g-2">
            <div class="col-6 col-md-3 col-xl-2">
                <a href="/admin/agenda" class="btn btn-outline-primary w-100 py-3">
                    <i class="bi bi-calendar3 d-block fs-5 mb-1"></i>Agenda
                </a>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <a href="/admin/financeiro?kind=expense&status=pending" class="btn btn-outline-danger w-100 py-3">
                    <i class="bi bi-arrow-up-circle d-block fs-5 mb-1"></i>A pagar
                </a>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <a href="/admin/financeiro?kind=income&status=pending" class="btn btn-outline-success w-100 py-3">
                    <i class="bi bi-arrow-down-circle d-block fs-5 mb-1"></i>A receber
                </a>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <a href="/admin/abastecimentos/novo" class="btn btn-outline-secondary w-100 py-3">
                    <i class="bi bi-fuel-pump d-block fs-5 mb-1"></i>Abastecer
                </a>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <a href="/admin/manutencoes/novo" class="btn btn-outline-secondary w-100 py-3">
                    <i class="bi bi-wrench d-block fs-5 mb-1"></i>Manutenção
                </a>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <a href="/admin/financeiro" class="btn btn-outline-primary w-100 py-3">
                    <i class="bi bi-cash-coin d-block fs-5 mb-1"></i>Fluxo de caixa
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Frota & Equipamentos -->
<div class="card mb-4">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <strong><i class="bi bi-truck me-1"></i> Frota &amp; Equipamentos — visão do mês</strong>
        <a href="/admin/frota/relatorios" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-graph-up me-1"></i>Relatórios completos
        </a>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-truck"></i></div>
                    <div>
                        <div class="stat-value"><?= e((int) ($frotaContagem['veiculos_ativos'] ?? 0)) ?></div>
                        <div class="stat-label">Veículos ativos</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-gear-wide-connected"></i></div>
                    <div>
                        <div class="stat-value"><?= e((int) ($frotaContagem['equipamentos_ativos'] ?? 0)) ?></div>
                        <div class="stat-label">Equipamentos ativos</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-fuel-pump"></i></div>
                    <div>
                        <div class="stat-value">R$ <?= e(number_format($custoCombMes ?? 0, 0, ',', '.')) ?></div>
                        <div class="stat-label">Combustível · <?= e(number_format($litrosMes ?? 0, 0, ',', '.')) ?> L</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-tools"></i></div>
                    <div>
                        <div class="stat-value">R$ <?= e(number_format($custoManutMes ?? 0, 0, ',', '.')) ?></div>
                        <div class="stat-label">Manutenções</div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($frotaPorCombustivel)) { ?>
            <div class="mb-4">
                <h6 class="text-muted">Ativos por tipo de combustível (12 meses)</h6>
                <div class="row g-2">
                    <?php foreach ($frotaPorCombustivel as $fc) { ?>
                        <?php $comb = \App\Support\FleetAgenda::combustivelInfo($fc['fuel_type'] ?? null, $fc['category'] ?? 'vehicle'); ?>
                        <div class="col-6 col-md-4 col-xl-3">
                            <div class="card h-100" style="border-left:5px solid <?= e($comb['hex']) ?>">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="badge <?= e($comb['bg']) ?>" style="background:<?= e($comb['hex']) ?> !important">
                                            <?= e($comb['label']) ?>
                                        </span>
                                        <strong class="small"><?= e((int) $fc['ativos']) ?> ativo(s)</strong>
                                    </div>
                                    <div class="small text-muted mt-2">
                                        <?= e(number_format((float) $fc['litros'], 0, ',', '.')) ?> L ·
                                        R$ <?= e(number_format((float) $fc['custo'], 0, ',', '.')) ?> combustível
                                    </div>
                                    <div class="small text-muted">
                                        Manutenção: R$ <?= e(number_format((float) $fc['manutencao'], 0, ',', '.')) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <div class="row g-4">
            <div class="col-lg-7">
                <h6 class="text-muted">Maior custo nos últimos 12 meses</h6>                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Ativo</th><th class="text-end">Combustível</th><th class="text-end">Manutenção</th><th class="text-end">Total</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach (($frotaTop ?? []) as $f) { ?>
                                <tr>
                                    <td>
                                        <?php $comb = \App\Support\FleetAgenda::combustivelInfo($f['fuel_type'] ?? null, $f['category'] ?? 'vehicle'); ?>
                                        <span class="badge" style="background:<?= e($comb['hex']) ?> !important">
                                            <?= e($comb['label']) ?>
                                        </span>
                                        <?php if (($f['category'] ?? 'vehicle') === 'equipment') { ?>
                                            <span class="badge bg-info ms-1"><?= e($f['equipment_type'] ?: 'Equip.') ?></span>
                                        <?php } ?>
                                        <div class="mt-1"><?= e(asset_label($f)) ?></div>
                                    </td>
                                    <td class="text-end">R$ <?= e(number_format((float) $f['custo_combustivel'], 2, ',', '.')) ?></td>
                                    <td class="text-end">R$ <?= e(number_format((float) $f['custo_manutencao'], 2, ',', '.')) ?></td>
                                    <td class="text-end"><strong>R$ <?= e(number_format((float) $f['custo_total'], 2, ',', '.')) ?></strong></td>
                                </tr>
                            <?php } ?>
                            <?php if (empty($frotaTop)) { ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">Nenhum lançamento de frota ainda.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-5">
                <h6 class="text-muted">Próximas manutenções</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Data</th><th>Compromisso</th><th>Ativo</th></tr></thead>
                        <tbody>
                            <?php foreach (($agendaProxima ?? []) as $ag) { ?>
                                <tr>
                                    <td><?= e(date('d/m', strtotime($ag['scheduled_date']))) ?></td>
                                    <td><?= e($ag['title']) ?></td>
                                    <td class="text-muted small">
                                        <?= ($ag['plate'] ?? null) !== null || ($ag['model'] ?? null) !== null ? e(asset_label($ag)) : 'Frota' ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            <?php if (empty($agendaProxima)) { ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">Nada programado.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <h6 class="text-muted">Últimos lançamentos</h6>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Data</th><th>Ativo</th><th>Por</th><th class="text-end">Valor</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach (($ultimosLancamentosFrota ?? []) as $l) { ?>
                                <tr>
                                    <td><?= e(date('d/m', strtotime($l['data']))) ?></td>
                                    <td>
                                        <?= e(asset_label($l)) ?>
                                    </td>
                                    <td class="text-muted small"><?= e($l['usuario'] ?? '—') ?></td>
                                    <td class="text-end">R$ <?= e(number_format((float) $l['cost'], 2, ',', '.')) ?></td>
                                </tr>
                            <?php } ?>
                            <?php if (empty($ultimosLancamentosFrota)) { ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">Sem lançamentos.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$meses = ['01'=>'Jan','02'=>'Fev','03'=>'Mar','04'=>'Abr','05'=>'Mai','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Set','10'=>'Out','11'=>'Nov','12'=>'Dez'];
$labelsM = []; $dataM = [];
foreach ($chartManutencao as $m) { $labelsM[] = $meses[substr($m['mes'],5,2)] ?? $m['mes']; $dataM[] = (int)$m['total']; }
$labelsC = []; $dataC = [];
foreach ($chartCombustivel as $c) { $labelsC[] = $meses[substr($c['mes'],5,2)] ?? $c['mes']; $dataC[] = (int)$c['total']; }
?>

<!-- Gráficos -->
<?php if (!empty($dataM) || !empty($dataC)) { ?>
<div class="row g-4 mb-4" id="chartsSection">
    <div class="col-lg-6">
        <div class="card"><div class="card-header bg-white d-flex justify-content-between"><h5 class="mb-0">📊 Manutenções (R$)</h5><small class="text-muted">últimos 6 meses</small></div>
        <div class="card-body"><canvas id="chartManutencao" height="200"></canvas></div></div>
    </div>
    <div class="col-lg-6">
        <div class="card"><div class="card-header bg-white d-flex justify-content-between"><h5 class="mb-0">⛽ Combustível (R$)</h5><small class="text-muted">últimos 6 meses</small></div>
        <div class="card-body"><canvas id="chartCombustivel" height="200"></canvas></div></div>
    </div>
</div>
<?php } ?>

<!-- Relatórios Rápidos -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card"><div class="card-header bg-white d-flex justify-content-between align-items-center"><h5 class="mb-0">📋 Relatórios</h5></div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <a href="/admin/contratos?export=csv" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Contratos (CSV)</a>
                <a href="/admin/frota?export=csv" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Frota (CSV)</a>
                <a href="/admin/contatos?export=csv" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Mensagens (CSV)</a>
                <a href="/admin/manutencoes?export=csv" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Manutenções (CSV)</a>
                <a href="/admin/abastecimentos?export=csv" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Abastecimentos (CSV)</a>
            </div>
        </div></div>
    </div>
</div>

<div class="row g-4">
    <!-- Últimos Contatos -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Últimas Mensagens</h5>
                <a href="/admin/contatos" class="btn btn-sm btn-outline-primary">Ver Todas</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($ultimosContatos)) { ?>
                    <p class="text-muted text-center py-4">Nenhuma mensagem recebida.</p>
                <?php } else { ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($ultimosContatos as $contato) { ?>
                        <a href="/admin/contatos/<?= e($contato['id']) ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between">
                                <strong><?= e($contato['name']) ?></strong>
                                <small class="text-muted"><?= e(date('d/m/Y H:i', strtotime($contato['created_at']))) ?></small>
                            </div>
                            <small class="text-muted"><?= e(str_limit($contato['message'], 80)) ?></small>
                            <?php if ($contato['status'] === 'new') { ?>
                                <span class="badge bg-danger ms-2">Nova</span>
                            <?php } ?>
                        </a>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Contratos Próximos do Vencimento -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Contratos Vencendo</h5>
                <a href="/admin/contratos" class="btn btn-sm btn-outline-primary">Ver Todos</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($contratosVencendo)) { ?>
                    <p class="text-muted text-center py-4">Nenhum contrato próximo do vencimento.</p>
                <?php } else { ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($contratosVencendo as $c) { ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong><?= e($c['contract_number']) ?></strong> - <?= e($c['client_name']) ?>
                                </div>
                                <span class="badge bg-warning text-dark">Vence <?= e(date('d/m/Y', strtotime($c['end_date']))) ?></span>
                            </div>
                            <small class="text-muted">R$ <?= e(number_format($c['value'], 2, ',', '.')) ?></small>
                        </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php $view->endSection(); ?>

<?php $view->section('scripts'); ?>
<?php if (!empty($dataM) || !empty($dataC)) { ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const colors = ['#1e40af','#3b82f6','#06b6d4','#f59e0b','#ef4444','#10b981'];
<?php if (!empty($dataM)) { ?>
new Chart(document.getElementById('chartManutencao'), {
    type: 'bar',
    data: { labels: <?= json_attr($labelsM) ?>, datasets: [{ label: 'Manutenções (R$)', data: <?= json_attr($dataM) ?>, backgroundColor: colors[0], borderRadius: 6 }] },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
<?php } ?>
<?php if (!empty($dataC)) { ?>
new Chart(document.getElementById('chartCombustivel'), {
    type: 'line',
    data: { labels: <?= json_attr($labelsC) ?>, datasets: [{ label: 'Combustível (R$)', data: <?= json_attr($dataC) ?>, borderColor: colors[4], backgroundColor: colors[4]+'20', fill: true, tension: 0.3 }] },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
<?php } ?>
</script>
<?php } ?>
<?php $view->endSection(); ?>
