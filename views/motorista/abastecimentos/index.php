<?php
$view->layout('layouts.motorista');
?>

<?php $view->section('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="bi bi-fuel-pump me-1"></i> Meus abastecimentos</h5>
    <a href="/motorista/abastecimentos/novo" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Novo
    </a>
</div>

<?php if (empty($registros)) { ?>
    <div class="driver-empty">
        <i class="bi bi-fuel-pump fs-2 d-block mb-2"></i>
        Você ainda não registrou abastecimentos.
    </div>
<?php } else { ?>
    <div class="driver-list">
        <?php foreach ($registros as $r) { ?>
            <div class="driver-item">
                <div>
                    <div class="asset"><?= e(asset_label($r)) ?></div>
                    <div class="meta">
                        <?= e(date('d/m/Y', strtotime($r['fuel_date']))) ?>
                        · <?= e(number_format((float) $r['liters'], 2, ',', '.')) ?> L
                        <?php if (!empty($r['km_at_refuel'])) { ?>
                            · <?= e(number_format((float) $r['km_at_refuel'], 0, ',', '.')) ?> km
                        <?php } ?>
                        <?php if (!empty($r['hours_at_refuel'])) { ?>
                            · <?= e(number_format((float) $r['hours_at_refuel'], 1, ',', '.')) ?> h
                        <?php } ?>
                    </div>
                    <?php if (!empty($r['notes'])) { ?>
                        <div class="meta fst-italic"><?= e($r['notes']) ?></div>
                    <?php } ?>
                </div>
                <div class="amount">R$ <?= e(number_format((float) $r['cost'], 2, ',', '.')) ?></div>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<?php $view->endSection(); ?>
