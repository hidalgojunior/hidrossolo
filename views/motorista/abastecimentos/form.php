<?php
$view->layout('layouts.motorista');

$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

$veiculos = $veiculos ?? [];
$vehiclesList = array_values(array_filter($veiculos, static fn(array $v): bool => ($v['category'] ?? 'vehicle') === 'vehicle'));
$equipmentList = array_values(array_filter($veiculos, static fn(array $v): bool => ($v['category'] ?? '') === 'equipment'));
$extrasOld = is_array($old['extras'] ?? null) ? $old['extras'] : [];
?>

<?php $view->section('content'); ?>

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="/motorista/abastecimentos" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0">Novo abastecimento</h5>
</div>

<form method="POST" action="/motorista/abastecimentos/novo" class="driver-form driver-card" data-offline-queue>
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label" for="vehicle_id">Veículo / Equipamento *</label>
        <select name="vehicle_id" id="vehicle_id" class="form-select" required>
            <option value="">Selecione...</option>
            <?php if ($vehiclesList !== []) { ?>
                <optgroup label="Veículos">
                    <?php foreach ($vehiclesList as $v) { ?>
                        <option value="<?= e($v['id']) ?>" data-km="<?= e($v['current_km'] ?? '') ?>"
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
                        <option value="<?= e($v['id']) ?>" data-km="<?= e($v['current_km'] ?? '') ?>"
                                data-hours="<?= e($v['current_hours'] ?? '') ?>"
                                <?= (string) ($old['vehicle_id'] ?? '') === (string) $v['id'] ? 'selected' : '' ?>>
                            <?= e(asset_label($v)) ?>
                        </option>
                    <?php } ?>
                </optgroup>
            <?php } ?>
        </select>
    </div>

    <div class="row g-3">
        <div class="col-6">
            <label class="form-label" for="fuel_date">Data *</label>
            <input type="text" name="fuel_date" id="fuel_date" class="form-control" required data-date-br
                   placeholder="dd/mm/aaaa"
                   value="<?= e(data_valor_br($old['fuel_date'] ?? date('Y-m-d'))) ?>">
        </div>
        <div class="col-6">
            <label class="form-label" for="liters">Litros *</label>
            <input type="text" inputmode="decimal" name="liters" id="liters" class="form-control" required
                   placeholder="0,00" value="<?= e($old['liters'] ?? '') ?>">
        </div>
        <div class="col-6">
            <label class="form-label" for="cost">Valor do combustível (R$) *</label>
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

    <!-- Despesas extras -->
    <div class="driver-repeater mt-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong><i class="bi bi-receipt me-1"></i> Outras despesas deste abastecimento</strong>
            <button type="button" class="btn btn-sm btn-outline-primary" data-add-row>
                <i class="bi bi-plus-lg"></i> Adicionar
            </button>
        </div>
        <p class="text-muted small mb-2">
            Use para arla, aditivo, óleo, lavagem, pedágio ou qualquer outro gasto feito junto com o abastecimento.
        </p>

        <div data-rows>
            <?php foreach ($extrasOld as $i => $extra) { ?>
                <div class="driver-repeater-row" data-row>
                    <input type="text" name="extras[<?= e($i) ?>][description]" class="form-control form-control-sm"
                           maxlength="255" placeholder="Descrição (ex.: Arla 32)" value="<?= e($extra['description'] ?? '') ?>">
                    <input type="text" inputmode="decimal" name="extras[<?= e($i) ?>][amount]"
                           class="form-control form-control-sm money" placeholder="0,00" value="<?= e($extra['amount'] ?? '') ?>">
                    <button type="button" class="btn btn-sm btn-outline-danger" data-remove-row aria-label="Remover">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            <?php } ?>
        </div>

        <div class="text-end small text-muted mt-1">
            Total geral: <strong data-total-geral>—</strong>
        </div>
    </div>

    <div id="odometerHint" class="alert alert-info mt-3 mb-0" style="display:none"></div>

    <button type="submit" class="btn btn-primary driver-submit mt-3">
        <i class="bi bi-check-lg me-1"></i> Salvar abastecimento
    </button>
</form>

<template id="extraRowTemplate">
    <div class="driver-repeater-row" data-row>
        <input type="text" class="form-control form-control-sm" maxlength="255" placeholder="Descrição (ex.: Arla 32)" data-name="description">
        <input type="text" inputmode="decimal" class="form-control form-control-sm money" placeholder="0,00" data-name="amount">
        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-row aria-label="Remover">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</template>

<?php $view->endSection(); ?>

<?php $view->section('scripts'); ?>
<script>
(function () {
    var form = document.querySelector('[data-offline-queue]');
    var select = document.getElementById('vehicle_id');
    var km = document.getElementById('km_at_refuel');
    var hours = document.getElementById('hours_at_refuel');
    var hint = document.getElementById('odometerHint');

    // ---------- Despesas extras ----------
    var rowsBox = form.querySelector('[data-rows]');
    var template = document.getElementById('extraRowTemplate');
    var prefix = 'extras';

    function renumber() {
        rowsBox.querySelectorAll('[data-row]').forEach(function (row, index) {
            row.querySelectorAll('[data-name]').forEach(function (input) {
                input.name = prefix + '[' + index + '][' + input.getAttribute('data-name') + ']';
            });
        });
        updateTotal();
    }

    function addRow() {
        var node = template.content.firstElementChild.cloneNode(true);
        rowsBox.appendChild(node);
        renumber();
    }

    form.querySelector('[data-add-row]').addEventListener('click', addRow);

    rowsBox.addEventListener('click', function (event) {
        var btn = event.target.closest('[data-remove-row]');
        if (!btn) return;
        btn.closest('[data-row]').remove();
        renumber();
    });

    function parseMoney(value) {
        if (!value) return 0;
        return Number(String(value).replace(/\./g, '').replace(',', '.')) || 0;
    }

    function updateTotal() {
        var total = parseMoney(document.getElementById('cost').value);
        rowsBox.querySelectorAll('.money').forEach(function (input) {
            total += parseMoney(input.value);
        });
        form.querySelector('[data-total-geral]').textContent =
            'R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    }

    document.getElementById('cost').addEventListener('input', updateTotal);
    rowsBox.addEventListener('input', updateTotal);

    if (!rowsBox.querySelector('[data-row]')) addRow();
    renumber();

    // ---------- Odômetro ----------
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
