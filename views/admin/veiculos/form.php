<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<?php
$categoria = $veiculo['category'] ?? 'vehicle';
$isEquipment = $categoria === 'equipment';
?>
<h4 class="mb-4"><?= e($veiculo ? ($isEquipment ? 'Editar Equipamento' : 'Editar Veículo') : 'Novo Veículo / Equipamento') ?></h4>

<div class="card mb-4">
    <div class="card-body">
        <form method="POST" action="<?= e($veiculo ? '/admin/frota/editar/' . $veiculo['id'] : '/admin/frota/novo') ?>">

            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tipo de registro *</label>
                    <select name="category" class="form-select" id="categorySelect">
                        <option value="vehicle" <?= e($categoria === 'vehicle' ? 'selected' : '') ?>>Veículo</option>
                        <option value="equipment" <?= e($categoria === 'equipment' ? 'selected' : '') ?>>Equipamento / Gerador</option>
                    </select>
                </div>

                <div class="col-md-4" data-vehicle-only>
                    <label class="form-label">Placa <?= e($isEquipment ? '' : '*') ?></label>
                    <input type="text" name="plate" class="form-control" maxlength="10"
                           value="<?= e($veiculo['plate'] ?? '') ?>" <?= e($isEquipment ? '' : 'required') ?>>
                </div>

                <div class="col-md-4" data-equipment-only style="display:none">
                    <label class="form-label">Tipo de equipamento</label>
                    <input type="text" name="equipment_type" class="form-control" maxlength="100"
                           placeholder="Ex.: Gerador, Compressor, Bomba"
                           value="<?= e($veiculo['equipment_type'] ?? '') ?>">
                </div>

                <div class="col-md-4" data-equipment-only style="display:none">
                    <label class="form-label">Instalado no veículo</label>
                    <select name="parent_vehicle_id" class="form-select">
                        <option value="">— Não vinculado —</option>
                        <?php foreach (($parents ?? []) as $p) { ?>
                            <option value="<?= e($p['id']) ?>" <?= e((string) ($veiculo['parent_vehicle_id'] ?? '') === (string) $p['id'] ? 'selected' : '') ?>>
                                <?= e($p['plate']) ?> — <?= e($p['brand']) ?> <?= e($p['model']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Marca *</label>
                    <input type="text" name="brand" class="form-control" required value="<?= e($veiculo['brand'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Modelo *</label>
                    <input type="text" name="model" class="form-control" required value="<?= e($veiculo['model'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ano</label>
                    <input type="number" name="year" class="form-control" value="<?= e($veiculo['year'] ?? date('Y')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Renavam</label>
                    <input type="text" name="renavam" class="form-control" value="<?= e($veiculo['renavam'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Chassi</label>
                    <input type="text" name="chassis" class="form-control" value="<?= e($veiculo['chassis'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Combustível</label>
                    <select name="fuel_type" class="form-select">
                        <?php foreach (['diesel' => 'Diesel', 'gasoline' => 'Gasolina', 'ethanol' => 'Etanol', 'flex' => 'Flex', 'electric' => 'Elétrico'] as $k => $v) { ?>
                            <option value="<?= e($k) ?>" <?= e(($veiculo['fuel_type'] ?? 'diesel') == $k ? 'selected' : '') ?>><?= e($v) ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-3" data-vehicle-only>
                    <label class="form-label">KM Atual</label>
                    <input type="number" name="current_km" class="form-control" value="<?= e($veiculo['current_km'] ?? 0) ?>">
                </div>
                <div class="col-md-3" data-equipment-only style="display:none">
                    <label class="form-label">Horímetro (h)</label>
                    <input type="text" inputmode="decimal" name="current_hours" class="form-control"
                           placeholder="Ex.: 1284,5" value="<?= e($veiculo['current_hours'] ?? '') ?>">
                </div>

                <?php if ($veiculo) { ?>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= e($veiculo['status'] == 'active' ? 'selected' : '') ?>>Ativo</option>
                        <option value="maintenance" <?= e($veiculo['status'] == 'maintenance' ? 'selected' : '') ?>>Em Manutenção</option>
                        <option value="inactive" <?= e($veiculo['status'] == 'inactive' ? 'selected' : '') ?>>Inativo</option>
                    </select>
                </div>
                <?php } ?>

                <div class="col-12">
                    <label class="form-label">Observações</label>
                    <textarea name="notes" class="form-control" rows="2"><?= e($veiculo['notes'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="/admin/frota" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $view->endSection(); ?>

<?php $view->section('scripts'); ?>
<script>
(function () {
    var select = document.getElementById('categorySelect');
    if (!select) return;

    function refresh() {
        var equipment = select.value === 'equipment';

        document.querySelectorAll('[data-vehicle-only]').forEach(function (el) {
            el.style.display = equipment ? 'none' : '';
        });
        document.querySelectorAll('[data-equipment-only]').forEach(function (el) {
            el.style.display = equipment ? '' : 'none';
        });

        var plate = document.querySelector('[name=plate]');
        if (plate) {
            plate.required = !equipment;
            plate.closest('[data-vehicle-only]').style.display = equipment ? 'none' : '';
        }
    }

    select.addEventListener('change', refresh);
    refresh();
})();
</script>
<?php $view->endSection(); ?>
