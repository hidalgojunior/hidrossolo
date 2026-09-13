<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<h4 class="mb-4"><?= e($veiculo ? 'Editar Veículo' : 'Novo Veículo') ?></h4>

<div class="card mb-4">
    <div class="card-body">
            <form method="POST" action="<?= e($veiculo ? '/admin/frota/editar/' . $veiculo['id'] : '/admin/frota/novo') ?>">

            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Placa *</label>
                    <input type="text" name="plate" class="form-control" required maxlength="10" value="<?= e($veiculo['plate'] ?? '') ?>">
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
                    <label class="form-label">Ano *</label>
                    <input type="number" name="year" class="form-control" required value="<?= e($veiculo['year'] ?? '') ?>">
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
                        <?php foreach (['diesel' => 'Diesel', 'gasoline' => 'Gasolina', 'ethanol' => 'Etanol', 'flex' => 'Flex'] as $k => $v) { ?>
                            <option value="<?= e($k) ?>" <?= e(($veiculo['fuel_type'] ?? 'diesel') == $k ? 'selected' : '') ?>><?= e($v) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">KM Atual</label>
                    <input type="number" name="current_km" class="form-control" value="<?= e($veiculo['current_km'] ?? 0) ?>">
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

<?php if ($veiculo && !empty($manutencoes)) { ?>
<div class="card mt-4">
    <div class="card-header bg-white"><h5 class="mb-0">Histórico de Manutenções</h5></div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Data</th><th>Tipo</th><th>Oficina</th><th>KM</th><th>Valor</th><th>Descrição</th></tr></thead>
            <tbody>
                <?php foreach ($manutencoes as $m) { ?>
                <tr>
                    <td><?= e(date('d/m/Y', strtotime($m['maintenance_date']))) ?></td>
                    <td><?= e($m['type'] === 'preventive' ? 'Preventiva' : 'Corretiva') ?></td>
                    <td><?= e($m['workshop']) ?></td>
                    <td><?= e(number_format($m['km_at_maintenance'] ?? 0, 0, ',', '.')) ?> km</td>
                    <td>R$ <?= e(number_format($m['cost'], 2, ',', '.')) ?></td>
                    <td><?= e(str_limit($m['description'], 50)) ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php } ?>
<?php $view->endSection(); ?>
