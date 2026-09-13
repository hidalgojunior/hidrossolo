<?php
$view->layout('layouts.motorista');

$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

$veiculos = $veiculos ?? [];
$vehiclesList = array_values(array_filter($veiculos, static fn(array $v): bool => ($v['category'] ?? 'vehicle') === 'vehicle'));
$equipmentList = array_values(array_filter($veiculos, static fn(array $v): bool => ($v['category'] ?? '') === 'equipment'));
$tiposLabel = ['preventive' => 'Preventiva', 'corrective' => 'Corretiva'];
?>

<?php $view->section('content'); ?>

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="/motorista/manutencoes" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0">Nova manutenção</h5>
</div>

<form method="POST" action="/motorista/manutencoes/nova" class="driver-form driver-card">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label" for="vehicle_id">Veículo / Equipamento *</label>
        <select name="vehicle_id" id="vehicle_id" class="form-select" required>
            <option value="">Selecione...</option>
            <?php if ($vehiclesList !== []) { ?>
                <optgroup label="Veículos">
                    <?php foreach ($vehiclesList as $v) { ?>
                        <option value="<?= e($v['id']) ?>" data-km="<?= e($v['current_km'] ?? '') ?>"
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
            <label class="form-label" for="type">Tipo *</label>
            <select name="type" id="type" class="form-select" required>
                <?php foreach ($tiposLabel as $value => $label) { ?>
                    <option value="<?= e($value) ?>" <?= ($old['type'] ?? '') === $value ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="col-6">
            <label class="form-label" for="maintenance_date">Data *</label>
            <input type="date" name="maintenance_date" id="maintenance_date" class="form-control" required
                   value="<?= e($old['maintenance_date'] ?? date('Y-m-d')) ?>">
        </div>
        <div class="col-12">
            <label class="form-label" for="workshop">Oficina / Fornecedor</label>
            <input type="text" name="workshop" id="workshop" class="form-control" maxlength="255"
                   placeholder="Onde o serviço foi feito" value="<?= e($old['workshop'] ?? '') ?>">
        </div>
        <div class="col-6">
            <label class="form-label" for="km_at_maintenance">KM atual</label>
            <input type="number" min="0" name="km_at_maintenance" id="km_at_maintenance" class="form-control"
                   placeholder="Opcional" value="<?= e($old['km_at_maintenance'] ?? '') ?>">
        </div>
        <div class="col-6">
            <label class="form-label" for="hours_at_maintenance">Horímetro (h)</label>
            <input type="text" inputmode="decimal" name="hours_at_maintenance" id="hours_at_maintenance"
                   class="form-control" placeholder="Opcional" value="<?= e($old['hours_at_maintenance'] ?? '') ?>">
        </div>
        <div class="col-12">
            <label class="form-label" for="cost">Valor gasto (R$) *</label>
            <input type="text" inputmode="decimal" name="cost" id="cost" class="form-control" required
                   placeholder="0,00" value="<?= e($old['cost'] ?? '') ?>">
        </div>
        <div class="col-12">
            <label class="form-label" for="description">Serviço realizado *</label>
            <textarea name="description" id="description" class="form-control" rows="3" required
                      placeholder="Ex.: troca de óleo e filtros"><?= e($old['description'] ?? '') ?></textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="notes">Observações</label>
            <textarea name="notes" id="notes" class="form-control" rows="2"><?= e($old['notes'] ?? '') ?></textarea>
        </div>
    </div>

    <button type="submit" class="btn btn-primary driver-submit mt-3">
        <i class="bi bi-check-lg me-1"></i> Salvar manutenção
    </button>
</form>

<?php $view->endSection(); ?>
