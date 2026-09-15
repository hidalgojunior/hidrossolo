<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="mb-0">Controle de Abastecimento</h4>
    <div class="d-flex flex-wrap gap-2">
        <a href="/admin/abastecimentos/pdf<?= $veiculoId ? '?veiculo_id=' . e($veiculoId) : '' ?>" class="btn btn-outline-danger">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
        <a href="/admin/abastecimentos/xlsx<?= $veiculoId ? '?veiculo_id=' . e($veiculoId) : '' ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i>Excel
        </a>
        <a href="/admin/abastecimentos/novo<?= e($veiculoId ? '?veiculo_id=' . $veiculoId : '') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Novo Abastecimento
        </a>
    </div>
</div>

<!-- Filtro por veículo -->
<div class="mb-3">
    <select class="form-select d-inline-block w-auto" onchange="location = this.value ? '?veiculo_id=' + this.value : '?'">
        <option value="">Todos os veículos</option>
        <?php foreach ($veiculos as $v) { ?>
            <option value="<?= e($v['id']) ?>" <?= e($veiculoId == $v['id'] ? 'selected' : '') ?>>
                <?= e(asset_label($v)) ?>
            </option>
        <?php } ?>
    </select>
</div>

<!-- Resumo -->
<?php if (!empty($abastecimentos) && $veiculoId) { ?>
<?php
    $totalLitros = array_sum(array_column($abastecimentos, 'liters'));
    $totalCusto = array_sum(array_column($abastecimentos, 'cost'));
    $totalExtras = array_sum(array_column($abastecimentos, 'extras_total'));
    $mediaConsumo = $totalLitros > 0 && count($abastecimentos) > 1
        ? round((max(array_column($abastecimentos, 'km_at_refuel')) - min(array_column($abastecimentos, 'km_at_refuel'))) / $totalLitros, 1)
        : 0;
?>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="stat-card"><div><div class="stat-label">Total Abastecido</div><div class="stat-value"><?= e(number_format($totalLitros, 1, ',', '.')) ?> L</div></div></div></div>
    <div class="col-md-3"><div class="stat-card"><div><div class="stat-label">Custo Total</div><div class="stat-value">R$ <?= e(number_format($totalCusto, 2, ',', '.')) ?></div></div></div></div>
    <div class="col-md-3"><div class="stat-card"><div><div class="stat-label">Custo Médio/L</div><div class="stat-value">R$ <?= e($totalLitros > 0 ? number_format($totalCusto / $totalLitros, 2, ',', '.') : '0,00') ?></div></div></div></div>
    <div class="col-md-3"><div class="stat-card"><div><div class="stat-label">Média Consumo</div><div class="stat-value"><?= e($mediaConsumo) ?> km/L</div></div></div></div>
</div>
<?php if ($totalExtras > 0) { ?>
<div class="alert alert-info py-2 small mb-4">
    <i class="bi bi-receipt me-1"></i>Neste período foram lançadas <strong>outras despesas</strong> no valor total de
    <strong>R$ <?= e(number_format((float) $totalExtras, 2, ',', '.')) ?></strong> (já incluídas nos custos operacionais da frota).
</div>
<?php } ?>
<?php } ?>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($abastecimentos)) { ?>
            <p class="text-muted text-center py-5">Nenhum abastecimento registrado.</p>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Veículo</th>
                            <th>Combustível</th>
                            <th>Data</th>
                            <th>Litros</th>
                            <th>Valor</th>
                            <th>R$/L</th>
                            <th>KM</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($abastecimentos as $a) { ?>
                        <?php $comb = \App\Support\FleetAgenda::combustivelInfo($a['fuel_type'] ?? null, $a['category'] ?? 'vehicle'); ?>
                        <?php $extras = $extrasPorAbastecimento[(int) $a['id']] ?? []; ?>
                        <tr>
                            <td>
                                <strong><?= e(asset_label($a)) ?></strong>
                                <?php if ((float) ($a['extras_total'] ?? 0) > 0) { ?>
                                    <ul class="driver-extras">
                                        <?php foreach ($extras as $extra) { ?>
                                            <li>
                                                <i class="bi bi-receipt"></i><?= e($extra['description']) ?>
                                                <span>R$ <?= e(number_format((float) $extra['amount'], 2, ',', '.')) ?></span>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                <?php } ?>
                            </td>
                            <td>
                                <span class="badge" style="background:<?= e($comb['hex']) ?> !important"><?= e($comb['label']) ?></span>
                            </td>
                            <td><?= e(date('d/m/Y', strtotime($a['fuel_date']))) ?></td>
                            <td><?= e(number_format((float) $a['liters'], 1, ',', '.')) ?> L</td>
                            <td>
                                R$ <?= e(number_format((float) $a['cost'], 2, ',', '.')) ?>
                                <?php if ((float) ($a['extras_total'] ?? 0) > 0) { ?>
                                    <div class="small text-muted">+ R$ <?= e(number_format((float) $a['extras_total'], 2, ',', '.')) ?> extras</div>
                                <?php } ?>
                            </td>
                            <td>R$ <?= e($a['liters'] > 0 ? number_format((float) $a['cost'] / (float) $a['liters'], 2, ',', '.') : '—') ?></td>
                            <td><?= e($a['km_at_refuel'] ? number_format((float) $a['km_at_refuel'], 0, ',', '.') . ' km' : '—') ?></td>
                            <td class="text-end">
                                <form action="/admin/abastecimentos/excluir/<?= e($a['id']) ?>" method="POST" class="d-inline"
                                      onsubmit="return confirm('Excluir este abastecimento? As despesas extras ligadas a ele também serão apagadas.')">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</div>
<?php $view->endSection(); ?>
