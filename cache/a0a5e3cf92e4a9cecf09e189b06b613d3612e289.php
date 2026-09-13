<?php $__env->startSection('content'); ?>
<h4 class="mb-4"><?php echo e($veiculo ? 'Editar Veículo' : 'Novo Veículo'); ?></h4>

<div class="card mb-4">
    <div class="card-body">
            <form method="POST" action="<?php echo e($veiculo ? '/admin/frota/editar/' . $veiculo['id'] : '/admin/frota/novo'); ?>">

            <?php echo \App\Middleware\CsrfMiddleware::field(); ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Placa *</label>
                    <input type="text" name="plate" class="form-control" required maxlength="10" value="<?php echo e($veiculo['plate'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Marca *</label>
                    <input type="text" name="brand" class="form-control" required value="<?php echo e($veiculo['brand'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Modelo *</label>
                    <input type="text" name="model" class="form-control" required value="<?php echo e($veiculo['model'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ano *</label>
                    <input type="number" name="year" class="form-control" required value="<?php echo e($veiculo['year'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Renavam</label>
                    <input type="text" name="renavam" class="form-control" value="<?php echo e($veiculo['renavam'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Chassi</label>
                    <input type="text" name="chassis" class="form-control" value="<?php echo e($veiculo['chassis'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Combustível</label>
                    <select name="fuel_type" class="form-select">
                        <?php $__currentLoopData = ['diesel' => 'Diesel', 'gasoline' => 'Gasolina', 'ethanol' => 'Etanol', 'flex' => 'Flex']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($k); ?>" <?php echo e(($veiculo['fuel_type'] ?? 'diesel') == $k ? 'selected' : ''); ?>><?php echo e($v); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">KM Atual</label>
                    <input type="number" name="current_km" class="form-control" value="<?php echo e($veiculo['current_km'] ?? 0); ?>">
                </div>
                <?php if($veiculo): ?>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?php echo e($veiculo['status'] == 'active' ? 'selected' : ''); ?>>Ativo</option>
                        <option value="maintenance" <?php echo e($veiculo['status'] == 'maintenance' ? 'selected' : ''); ?>>Em Manutenção</option>
                        <option value="inactive" <?php echo e($veiculo['status'] == 'inactive' ? 'selected' : ''); ?>>Inativo</option>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-12">
                    <label class="form-label">Observações</label>
                    <textarea name="notes" class="form-control" rows="2"><?php echo e($veiculo['notes'] ?? ''); ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="/admin/frota" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if($veiculo && !empty($manutencoes)): ?>
<div class="card mt-4">
    <div class="card-header bg-white"><h5 class="mb-0">Histórico de Manutenções</h5></div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Data</th><th>Tipo</th><th>Oficina</th><th>KM</th><th>Valor</th><th>Descrição</th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $manutencoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e(date('d/m/Y', strtotime($m['maintenance_date']))); ?></td>
                    <td><?php echo e($m['type'] === 'preventive' ? 'Preventiva' : 'Corretiva'); ?></td>
                    <td><?php echo e($m['workshop']); ?></td>
                    <td><?php echo e(number_format($m['km_at_maintenance'] ?? 0, 0, ',', '.')); ?> km</td>
                    <td>R$ <?php echo e(number_format($m['cost'], 2, ',', '.')); ?></td>
                    <td><?php echo e(Str::limit($m['description'], 50)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/admin/veiculos/form.blade.php ENDPATH**/ ?>