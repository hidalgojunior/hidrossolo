<?php
$view->layout('layouts.motorista');

/** Rótulo amigável de um ativo (veículo ou equipamento). */
$assetLabel = static function (array $a): string {
    if (($a['category'] ?? 'vehicle') === 'equipment') {
        $tipo = $a['equipment_type'] ? $a['equipment_type'] . ' ' : '';
        return trim($tipo . ($a['brand'] ?? '') . ' ' . ($a['model'] ?? ''));
    }

    return trim(($a['plate'] ?? '') . ' — ' . ($a['brand'] ?? '') . ' ' . ($a['model'] ?? ''));
};
?>

<?php $view->section('content'); ?>

<div class="driver-stats">
    <div class="driver-stat">
        <div class="value"><?= e($meusAbastecimentos) ?></div>
        <div class="label">Abastecimentos</div>
    </div>
    <div class="driver-stat">
        <div class="value"><?= e($minhasManutencoes) ?></div>
        <div class="label">Manutenções</div>
    </div>
    <div class="driver-stat">
        <div class="value">R$ <?= e(number_format($gastoMes, 0, ',', '.')) ?></div>
        <div class="label">Combustível no mês</div>
    </div>
</div>

<div class="driver-actions mb-4">
    <a href="/motorista/abastecimentos/novo" class="driver-action">
        <i class="bi bi-fuel-pump"></i>
        Registrar abastecimento
    </a>
    <a href="/motorista/manutencoes/nova" class="driver-action">
        <i class="bi bi-tools"></i>
        Registrar manutenção
    </a>
</div>

<div class="driver-card">
    <h6 class="mb-3"><i class="bi bi-clock-history me-1"></i> Últimos abastecimentos</h6>

    <?php if (empty($ultimos)) { ?>
        <div class="driver-empty">
            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
            Nenhum lançamento ainda.<br>
            <small>Use os botões acima para começar.</small>
        </div>
    <?php } else { ?>
        <div class="driver-list">
            <?php foreach ($ultimos as $r) { ?>
                <div class="driver-item">
                    <div>
                        <div class="asset"><?= e($assetLabel($r)) ?></div>
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
                    </div>
                    <div class="amount">R$ <?= e(number_format((float) $r['cost'], 2, ',', '.')) ?></div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<?php $view->endSection(); ?>
