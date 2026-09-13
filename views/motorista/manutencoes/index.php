<?php
$view->layout('layouts.motorista');

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
            <div class="driver-item is-maintenance">
                <div>
                    <div class="asset"><?= e(asset_label($r)) ?></div>
                    <div class="meta">
                        <?= e(date('d/m/Y', strtotime($r['maintenance_date']))) ?>
                        · <?= e($tiposLabel[$r['type']] ?? $r['type']) ?>
                        <?php if (!empty($r['workshop'])) { ?>
                            · <?= e($r['workshop']) ?>
                        <?php } ?>
                        <?php if (!empty($r['km_at_maintenance'])) { ?>
                            · <?= e(number_format((float) $r['km_at_maintenance'], 0, ',', '.')) ?> km
                        <?php } ?>
                        <?php if (!empty($r['hours_at_maintenance'])) { ?>
                            · <?= e(number_format((float) $r['hours_at_maintenance'], 1, ',', '.')) ?> h
                        <?php } ?>
                    </div>
                    <div class="meta"><?= e($r['description'] ?? '') ?></div>
                </div>
                <div class="amount">R$ <?= e(number_format((float) $r['cost'], 2, ',', '.')) ?></div>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<?php $view->endSection(); ?>
