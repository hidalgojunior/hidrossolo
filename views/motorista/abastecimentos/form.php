<?php
$view->layout('layouts.motorista');

$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

$veiculos = $veiculos ?? [];
$vehiclesList = array_values(array_filter($veiculos, static fn(array $v): bool => ($v['category'] ?? 'vehicle') === 'vehicle'));
$equipmentList = array_values(array_filter($veiculos, static fn(array $v): bool => ($v['category'] ?? '') === 'equipment'));
?>

<?php $view->section('content'); ?>

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="/motorista/abastecimentos" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0">Novo abastecimento</h5>
</div>

<form method="POST" action="/motorista/abastecimentos/novo" class="driver-form driver-card">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label" for="vehicle_id">Veículo / Equipamento *</label>
        <select name="vehicle_id" id="vehicle_id" class="form-select" required>
            <option value="">Selecione...</option>
            <?php if ($vehiclesList !== []) { ?>
                <optgroup label="Veículos">
                    <?php foreach ($vehiclesList as $v) { ?>
                        <option value="<?= e($v['id']) ?>"
                                data-km="<?= e($v['current_km'] ?? '') ?>"
                                data-hours="<?= e($v['current_hours'] ?? '') ?>"
                                <?= (string) ($old['vehicle_id'] ?? '') === (string) $v['id'] ? 'selected' : '' ?>>
                            <?= e(asset_label($v)) ?>
                        </option>
                    <?php } ?>
                </optgroup>
            <?php } ?>
            <?php if ($equipmentList !== []) { ?>
                <optgroup label="Equipamentos / Geradores">
                    <?php foreach ($equipmentList as $v) { ?>
                        <option value="<?= e($v['id']) ?>"
                                data-km="<?= e($v['current_km'] ?? '') ?>"
                                data-hours="<?= e($v['current_hours'] ?? '') ?>"
                                <?= (string) ($old['vehicle_id'] ?? '') === (string) $v['id'] ? 'selected' : '' ?>>
                            <?= e(asset_label($v)) ?>
                        </option>
                    <?php } ?>
                </optgroup>
            <?php } ?>
        </select>
        <?php if ($veiculos === []) { ?>
            <small class="text-danger d-block mt-1">Nenhum veículo/equipamento cadastrado. Avise o administrador.</small>
        <?php } ?>
    </div>

    <div class="row g-3">
        <div class="col-6">
            <label class="form-label" for="fuel_date">Data *</label>
            <input type="date" name="fuel_date" id="fuel_date" class="form-control" required
                   value="<?= e($old['fuel_date'] ?? date('Y-m-d')) ?>">
        </div>
        <div class="col-6">
            <label class="form-label" for="liters">Litros *</label>
            <input type="text" inputmode="decimal" name="liters" id="liters" class="form-control" required
                   placeholder="0,00" value="<?= e($old['liters'] ?? '') ?>">
        </div>
        <div class="col-6">
            <label class="form-label" for="cost">Valor pago (R$) *</label>
            <input type="text" inputmode="decimal" name="cost" id="cost" class="form-control" required
                   placeholder="0,00" value="<?= e($old['cost'] ?? '') ?>">
        </div>
        <div class="col-6">
            <label class="form-label" for="km_at_refuel">KM atual</label>
            <input type="number" min="0" name="km_at_refuel" id="km_at_refuel" class="form-control"
                   placeholder="Opcional" value="<?= e($old['km_at_refuel'] ?? '') ?>">
        </div>
        <div class="col-6">
            <label class="form-label" for="hours_at_refuel">Horímetro (h)</label>
            <input type="text" inputmode="decimal" name="hours_at_refuel" id="hours_at_refuel" class="form-control"
                   placeholder="Opcional" value="<?= e($old['hours_at_refuel'] ?? '') ?>">
        </div>
        <div class="col-12">
            <label class="form-label" for="notes">Observação</label>
            <input type="text" name="notes" id="notes" class="form-control" maxlength="500"
                   placeholder="Ex.: posto, forma de pagamento" value="<?= e($old['notes'] ?? '') ?>">
        </div>
    </div>

    <div id="odometerHint" class="alert alert-info mt-3 mb-0" style="display:none"></div>

    <button type="submit" class="btn btn-primary driver-submit mt-3">
        <i class="bi bi-check-lg me-1"></i> Salvar abastecimento
    </button>
</form>

<?php $view->endSection(); ?>

<?php $view->section('scripts'); ?>
<script>
(function () {
    var select = document.getElementById('vehicle_id');
    var km = document.getElementById('km_at_refuel');
    var hours = document.getElementById('hours_at_refuel');
    var hint = document.getElementById('odometerHint');

    function refresh(preserve) {
        var opt = select.options[select.selectedIndex];
        if (!opt || !opt.value) { hint.style.display = 'none'; return; }

        var curKm = opt.getAttribute('data-km');
        var curHours = opt.getAttribute('data-hours');
        var parts = [];

        if (curKm) {
            parts.push('KM registrado: ' + Number(curKm).toLocaleString('pt-BR'));
            if (!preserve) km.value = curKm;
        }
        if (curHours) {
            parts.push('Horímetro: ' + Number(curHours).toLocaleString('pt-BR', { minimumFractionDigits: 1 }) + ' h');
            if (!preserve) hours.value = curHours;
        }

        if (parts.length) {
            hint.innerHTML = '<i class="bi bi-info-circle me-1"></i>' + parts.join(' · ') + ' — ajuste se necessário.';
            hint.style.display = 'block';
        } else {
            hint.style.display = 'none';
        }
    }

    select.addEventListener('change', function () { refresh(false); });
    refresh(true);
})();
</script>
<?php $view->endSection(); ?>
