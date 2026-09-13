<?php
$view->layout('layouts.motorista');

use App\Support\FleetCatalog;

$itensPorManutencao = $itensPorManutencao ?? [];
$tiposLabel = ['preventive' => 'Preventiva', 'corrective' => 'Corretiva'];
?>

<?php $view->section('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="bi bi-tools me-1"></i> Minhas manutenções</h5>
    <a href="/motorista/manutencoes/nova" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Nova
    </a>
</div>

<?php if (empty($registros)) { ?>
    <div class="driver-empty">
        <i class="bi bi-tools fs-2 d-block mb-2"></i>
        Você ainda não registrou manutenções.
    </div>
<?php } else { ?>
    <div class="driver-list">
        <?php foreach ($registros as $r) { ?>
            <?php $itens = $itensPorManutencao[(int) $r['id']] ?? []; ?>
            <div class="driver-item is-maintenance">
                <div>
                    <div class="asset">
                        <i class="bi <?= e(FleetCatalog::servicoIcon($r['service_category'] ?? null)) ?> me-1"></i>
                        <?= e(asset_label($r)) ?>
                    </div>
                    <div class="meta">
                        <?= e(date('d/m/Y', strtotime($r['maintenance_date']))) ?>
                        · <?= e($tiposLabel[$r['type']] ?? $r['type']) ?>
                        · <?= e(FleetCatalog::servicoLabel($r['service_category'] ?? null)) ?>
                        <?php if (!empty($r['workshop'])) { ?>
                            · <?= e($r['workshop']) ?>
                        <?php } ?>
                        <?php if (!empty($r['km_at_maintenance'])) { ?>
                            · <?= e(number_format((float) $r['km_at_maintenance'], 0, ',', '.')) ?> km
                        <?php } ?>
                    </div>
                    <div class="meta"><?= e($r['description'] ?? '') ?></div>

                    <?php if ($itens !== []) { ?>
                        <ul class="driver-extras">
                            <?php foreach ($itens as $item) { ?>
                                <li>
                                    <i class="bi bi-check2"></i>
                                    <?= e($item['description']) ?>
                                    <?php if ((float) $item['quantity'] > 1) { ?>
                                        <span class="meta">×<?= e(rtrim(rtrim(number_format((float) $item['quantity'], 2, ',', '.'), '0'), ',')) ?></span>
                                    <?php } ?>
                                    <?php if ((float) $item['unit_cost'] > 0) { ?>
                                        <span>R$ <?= e(number_format((float) $item['unit_cost'], 2, ',', '.')) ?></span>
                                    <?php } ?>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } ?>

                    <?php if (!empty($r['next_review_date'])) { ?>
                        <div class="driver-next">
                            <i class="bi bi-calendar-event"></i>
                            Próxima revisão: <strong><?= e(date('d/m/Y', strtotime($r['next_review_date']))) ?></strong>
                        </div>
                    <?php } ?>
                </div>
                <div class="amount">R$ <?= e(number_format((float) $r['cost'], 2, ',', '.')) ?></div>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<?php $view->endSection(); ?>
