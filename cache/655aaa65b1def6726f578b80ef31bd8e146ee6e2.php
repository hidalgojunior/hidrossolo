<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Controle de Abastecimento</h4>
    <a href="/admin/abastecimentos/novo<?php echo e($veiculoId ? '?veiculo_id=' . $veiculoId : ''); ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Novo Abastecimento
    </a>
</div>

<!-- Filtro por veículo -->
<div class="mb-3">
    <select class="form-select d-inline-block w-auto" onchange="location = this.value ? '?veiculo_id=' + this.value : '?'">
        <option value="">Todos os veículos</option>
        <?php $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($v['id']); ?>" <?php echo e($veiculoId == $v['id'] ? 'selected' : ''); ?>>
                <?php echo e($v['plate']); ?> - <?php echo e($v['brand']); ?> <?php echo e($v['model']); ?>

            </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</div>

<!-- Resumo -->
<?php if(!empty($abastecimentos) && $veiculoId): ?>
<?php
    $totalLitros = array_sum(array_column($abastecimentos, 'liters'));
    $totalCusto = array_sum(array_column($abastecimentos, 'cost'));
    $mediaConsumo = $totalLitros > 0 && count($abastecimentos) > 1
        ? round((max(array_column($abastecimentos, 'km_at_refuel')) - min(array_column($abastecimentos, 'km_at_refuel'))) / $totalLitros, 1)
        : 0;
?>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="stat-card"><div><div class="stat-label">Total Abastecido</div><div class="stat-value"><?php echo e(number_format($totalLitros, 1, ',', '.')); ?> L</div></div></div></div>
    <div class="col-md-3"><div class="stat-card"><div><div class="stat-label">Custo Total</div><div class="stat-value">R$ <?php echo e(number_format($totalCusto, 2, ',', '.')); ?></div></div></div></div>
    <div class="col-md-3"><div class="stat-card"><div><div class="stat-label">Custo Médio/L</div><div class="stat-value">R$ <?php echo e($totalLitros > 0 ? number_format($totalCusto / $totalLitros, 2, ',', '.') : '0,00'); ?></div></div></div></div>
    <div class="col-md-3"><div class="stat-card"><div><div class="stat-label">Média Consumo</div><div class="stat-value"><?php echo e($mediaConsumo); ?> km/L</div></div></div></div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <?php if(empty($abastecimentos)): ?>
            <p class="text-muted text-center py-5">Nenhum abastecimento registrado.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Veículo</th>
                            <th>Data</th>
                            <th>Litros</th>
                            <th>Valor</th>
                            <th>R$/L</th>
                            <th>KM</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $abastecimentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><strong><?php echo e($a['plate']); ?></strong></td>
                            <td><?php echo e(date('d/m/Y', strtotime($a['fuel_date']))); ?></td>
                            <td><?php echo e(number_format($a['liters'], 1, ',', '.')); ?> L</td>
                            <td>R$ <?php echo e(number_format($a['cost'], 2, ',', '.')); ?></td>
                            <td>R$ <?php echo e($a['liters'] > 0 ? number_format($a['cost'] / $a['liters'], 2, ',', '.') : '—'); ?></td>
                            <td><?php echo e($a['km_at_refuel'] ? number_format($a['km_at_refuel'], 0, ',', '.') . ' km' : '—'); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/admin/abastecimentos/index.blade.php ENDPATH**/ ?>