<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<?php
$isEquipment = static fn(array $v): bool => ($v['category'] ?? 'vehicle') === 'equipment';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="mb-0">Frota &amp; Equipamentos</h4>
        <p class="text-muted small mb-0">Veículos e equipamentos (geradores, compressores) com seus lançamentos.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="/admin/frota/pdf<?= $tipo !== 'all' ? '?tipo=' . e($tipo) : '' ?>" class="btn btn-outline-danger">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
        <a href="/admin/frota/xlsx<?= $tipo !== 'all' ? '?tipo=' . e($tipo) : '' ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i>Excel
        </a>
        <a href="/admin/frota/relatorios" class="btn btn-outline-primary">
            <i class="bi bi-graph-up me-1"></i>Relatórios de consumo
        </a>
        <a href="/admin/frota/novo" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Novo</a>
    </div>
</div>

<div class="media-filters mb-3">
    <a href="?tipo=all" class="media-chip <?= $tipo === 'all' ? 'is-active' : '' ?>">
        Todos <span class="count"><?= e((int) ($totais['veiculos'] ?? 0) + (int) ($totais['equipamentos'] ?? 0)) ?></span>
    </a>
    <a href="?tipo=vehicle" class="media-chip <?= $tipo === 'vehicle' ? 'is-active' : '' ?>">
        Veículos <span class="count"><?= e((int) ($totais['veiculos'] ?? 0)) ?></span>
    </a>
    <a href="?tipo=equipment" class="media-chip <?= $tipo === 'equipment' ? 'is-active' : '' ?>">
        Equipamentos <span class="count"><?= e((int) ($totais['equipamentos'] ?? 0)) ?></span>
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($veiculos)) { ?>
            <p class="text-muted text-center py-5 mb-0">Nenhum registro encontrado.</p>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tipo</th>
                            <th>Identificação</th>
                            <th>Ano</th>
                            <th>Combustível</th>
                            <th>KM / Horímetro</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($veiculos as $v) { ?>
                        <tr>
                            <td>
                                <?php if ($isEquipment($v)) { ?>
                                    <span class="badge bg-info"><?= e($v['equipment_type'] ?: 'Equipamento') ?></span>
                                <?php } else { ?>
                                    <span class="badge bg-secondary">Veículo</span>
                                <?php } ?>
                            </td>
                            <td>
                                <strong><?= e(asset_label($v)) ?></strong>
                                <?php if ($isEquipment($v) && !empty($v['parent_vehicle_id'])) { ?>
                                    <div><small class="text-muted">Vinculado a um veículo</small></div>
                                <?php } ?>
                            </td>
                            <td><?= e($v['year']) ?></td>
                            <td><?= e(ucfirst((string) $v['fuel_type'])) ?></td>
                            <td>
                                <?php if ($isEquipment($v)) { ?>
                                    <?= $v['current_hours'] !== null ? e(number_format((float) $v['current_hours'], 1, ',', '.')) . ' h' : '—' ?>
                                <?php } else { ?>
                                    <?= e(number_format((float) $v['current_km'], 0, ',', '.')) ?> km
                                <?php } ?>
                            </td>
                            <td>
                                <?= match ($v['status']) {
                                    'active' => '<span class="badge bg-success">Ativo</span>',
                                    'maintenance' => '<span class="badge bg-warning">Em Manutenção</span>',
                                    default => '<span class="badge bg-secondary">Inativo</span>',
                                } ?>
                            </td>
                            <td class="text-end">
                                <a href="/admin/frota/editar/<?= e($v['id']) ?>" class="btn btn-sm btn-outline-primary">Editar</a>
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
