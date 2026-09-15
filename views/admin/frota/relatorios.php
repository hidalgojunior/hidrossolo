<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<?php
$meses = ['01' => 'Jan', '02' => 'Fev', '03' => 'Mar', '04' => 'Abr', '05' => 'Mai', '06' => 'Jun',
    '07' => 'Jul', '08' => 'Ago', '09' => 'Set', '10' => 'Out', '11' => 'Nov', '12' => 'Dez'];

$labels = [];
$dataLitros = [];
$dataCustoComb = [];
foreach ($serieCombustivel as $s) {
    $labels[] = ($meses[substr($s['mes'], 5, 2)] ?? $s['mes']) . '/' . substr($s['mes'], 2, 2);
    $dataLitros[] = round((float) $s['litros'], 1);
    $dataCustoComb[] = round((float) $s['custo'], 2);
}

$mapaManut = [];
foreach ($serieManutencao as $s) {
    $mapaManut[$s['mes']] = round((float) $s['custo'], 2);
}
$dataCustoManut = [];
foreach ($serieCombustivel as $s) {
    $dataCustoManut[] = $mapaManut[$s['mes']] ?? 0;
}
if (empty($labels) && !empty($mapaManut)) {
    foreach ($mapaManut as $mes => $custo) {
        $labels[] = ($meses[substr($mes, 5, 2)] ?? $mes) . '/' . substr($mes, 2, 2);
        $dataCustoManut[] = $custo;
        $dataLitros[] = 0;
        $dataCustoComb[] = 0;
    }
}

$tiposLabel = ['preventive' => 'Preventiva', 'corrective' => 'Corretiva'];
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="mb-0">Relatórios de Frota &amp; Equipamentos</h4>
        <p class="text-muted small mb-0">Consumo de combustível e custos de manutenção por ativo.</p>
    </div>
    <a href="/admin/frota" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Voltar para a frota
    </a>
</div>

<form method="GET" action="/admin/frota/relatorios" class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label" for="de">De</label>
                <input type="text" name="de" id="de" class="form-control" data-date-br placeholder="dd/mm/aaaa" value="<?= e(data_iso_para_br($de)) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label" for="ate">Até</label>
                <input type="text" name="ate" id="ate" class="form-control" data-date-br placeholder="dd/mm/aaaa" value="<?= e(data_iso_para_br($ate)) ?>">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label" for="categoria">Tipo</label>
                <select name="categoria" id="categoria" class="form-select">
                    <option value="all" <?= $categoria === 'all' ? 'selected' : '' ?>>Veículos + Equipamentos</option>
                    <option value="vehicle" <?= $categoria === 'vehicle' ? 'selected' : '' ?>>Somente veículos</option>
                    <option value="equipment" <?= $categoria === 'equipment' ? 'selected' : '' ?>>Somente equipamentos</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label d-none d-md-block">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i>Aplicar
                    </button>
                    <a href="/admin/frota/relatorios/pdf?de=<?= e($de) ?>&ate=<?= e($ate) ?>&categoria=<?= e($categoria) ?>"
                       class="btn btn-outline-danger" title="Exportar em PDF">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </a>
                    <a href="/admin/frota/relatorios/xlsx?de=<?= e($de) ?>&ate=<?= e($ate) ?>&categoria=<?= e($categoria) ?>"
                       class="btn btn-outline-success" title="Exportar em Excel (XLSX)">
                        <i class="bi bi-file-earmark-excel"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-fuel-pump"></i></div>
            <div>
                <div class="stat-value"><?= e(number_format($totais['litros'], 0, ',', '.')) ?> L</div>
                <div class="stat-label">Combustível no período</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-cash-coin"></i></div>
            <div>
                <div class="stat-value">R$ <?= e(number_format($totais['custo_combustivel'], 0, ',', '.')) ?></div>
                <div class="stat-label">Gasto com combustível</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-tools"></i></div>
            <div>
                <div class="stat-value">R$ <?= e(number_format($totais['custo_manutencao'], 0, ',', '.')) ?></div>
                <div class="stat-label">Manutenções (<?= e($totais['manutencoes']) ?>)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100">
            <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-graph-up"></i></div>
            <div>
                <div class="stat-value">R$ <?= e(number_format($totais['custo_total'], 0, ',', '.')) ?></div>
                <div class="stat-label">Custo total · média R$ <?= e(number_format($totais['preco_medio_litro'], 2, ',', '.')) ?>/L</div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos (só aparecem quando existe dado no período) -->
<?php if (!empty($labels)) { ?>
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white"><strong>Custo mensal — combustível x manutenção</strong></div>
            <div class="card-body"><canvas id="chartCustos" height="220"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white"><strong>Litros abastecidos por mês</strong></div>
            <div class="card-body"><canvas id="chartLitros" height="220"></canvas></div>
        </div>
    </div>
</div>
<?php } ?>

<!-- Por ativo -->
<div class="card mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Detalhamento por veículo / equipamento</strong>
        <span class="badge bg-secondary"><?= e(count($porAtivo)) ?> ativo(s)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ativo</th>
                        <th class="text-end">Litros</th>
                        <th class="text-end">Combustível</th>
                        <th class="text-end">Manutenção</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">KM no período</th>
                        <th class="text-end">R$/km</th>
                        <th class="text-end">km/L</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($porAtivo as $a) { ?>
                        <tr>
                            <td>
                                <?php if (($a['category'] ?? 'vehicle') === 'equipment') { ?>
                                    <span class="badge bg-info me-1"><?= e($a['equipment_type'] ?: 'Equip.') ?></span>
                                <?php } ?>
                                <?= e(asset_label($a)) ?>
                            </td>
                            <td class="text-end"><?= e(number_format((float) $a['litros'], 0, ',', '.')) ?></td>
                            <td class="text-end">R$ <?= e(number_format((float) $a['custo_combustivel'], 2, ',', '.')) ?></td>
                            <td class="text-end">R$ <?= e(number_format((float) $a['custo_manutencao'], 2, ',', '.')) ?></td>
                            <td class="text-end"><strong>R$ <?= e(number_format((float) $a['custo_total'], 2, ',', '.')) ?></strong></td>
                            <td class="text-end"><?= $a['km_rodados'] > 0 ? e(number_format((float) $a['km_rodados'], 0, ',', '.')) : '—' ?></td>
                            <td class="text-end"><?= $a['custo_por_km'] !== null ? 'R$ ' . e(number_format((float) $a['custo_por_km'], 2, ',', '.')) : '—' ?></td>
                            <td class="text-end"><?= $a['consumo_km_l'] !== null ? e(number_format((float) $a['consumo_km_l'], 2, ',', '.')) : '—' ?></td>
                        </tr>
                    <?php } ?>
                    <?php if (empty($porAtivo)) { ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">Nenhum ativo cadastrado.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-white"><strong>Manutenções por tipo</strong></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr><th>Tipo</th><th class="text-end">Qtd.</th><th class="text-end">Custo</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($porTipo as $t) { ?>
                                <tr>
                                    <td><?= e($tiposLabel[$t['type']] ?? $t['type']) ?></td>
                                    <td class="text-end"><?= e($t['qtd']) ?></td>
                                    <td class="text-end">R$ <?= e(number_format((float) $t['custo'], 2, ',', '.')) ?></td>
                                </tr>
                            <?php } ?>
                            <?php if (empty($porTipo)) { ?>
                                <tr><td colspan="3" class="text-center text-muted py-4">Sem manutenções no período.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-white"><strong>Últimos lançamentos</strong></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Data</th><th>Tipo</th><th>Ativo</th><th>Registrado por</th><th class="text-end">Valor</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimosLancamentos as $l) { ?>
                                <tr>
                                    <td><?= e(date('d/m/Y', strtotime($l['data']))) ?></td>
                                    <td>
                                        <?php if ($l['tipo'] === 'fuel') { ?>
                                            <span class="badge bg-primary">Abastec.</span>
                                        <?php } else { ?>
                                            <span class="badge bg-warning">Manut.</span>
                                        <?php } ?>
                                    </td>
                                    <td><?= e(asset_label($l)) ?></td>
                                    <td><?= e($l['usuario'] ?? '—') ?></td>
                                    <td class="text-end">R$ <?= e(number_format((float) $l['cost'], 2, ',', '.')) ?></td>
                                </tr>
                            <?php } ?>
                            <?php if (empty($ultimosLancamentos)) { ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">Nenhum lançamento no período.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $view->endSection(); ?>

<?php $view->section('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    var labels = <?= json_attr($labels) ?>;
    var litros = <?= json_attr($dataLitros) ?>;
    var custoComb = <?= json_attr($dataCustoComb) ?>;
    var custoManut = <?= json_attr($dataCustoManut) ?>;

    if (!labels.length) return;

    var brl = function (v) { return 'R$ ' + Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 2 }); };

    new Chart(document.getElementById('chartCustos'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Combustível', data: custoComb, backgroundColor: '#1e40af', borderRadius: 6 },
                { label: 'Manutenção', data: custoManut, backgroundColor: '#f59e0b', borderRadius: 6 }
            ]
        },
        options: {
            responsive: true,
            plugins: { tooltip: { callbacks: { label: function (c) { return c.dataset.label + ': ' + brl(c.parsed.y); } } } },
            scales: { y: { beginAtZero: true, ticks: { callback: function (v) { return 'R$ ' + v; } } } }
        }
    });

    new Chart(document.getElementById('chartLitros'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Litros',
                data: litros,
                borderColor: '#0891b2',
                backgroundColor: 'rgba(8,145,178,.15)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });
})();
</script>
<?php $view->endSection(); ?>
