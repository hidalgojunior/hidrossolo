<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Controle de Frota</h4>
    <a href="/admin/frota/novo" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Novo Veículo</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if(empty($veiculos)): ?>
            <p class="text-muted text-center py-5">Nenhum veículo cadastrado.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Placa</th>
                            <th>Marca/Modelo</th>
                            <th>Ano</th>
                            <th>Combustível</th>
                            <th>KM Atual</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><strong><?php echo e($v['plate']); ?></strong></td>
                            <td><?php echo e($v['brand']); ?> <?php echo e($v['model']); ?></td>
                            <td><?php echo e($v['year']); ?></td>
                            <td><?php echo e($v['fuel_type']); ?></td>
                            <td><?php echo e(number_format($v['current_km'], 0, ',', '.')); ?> km</td>
                            <td><?php echo match($v['status']) {
                                'active' => '<span class="badge bg-success">Ativo</span>',
                                'maintenance' => '<span class="badge bg-warning">Em Manutenção</span>',
                                default => '<span class="badge bg-secondary">Inativo</span>'
                            }; ?></td>
                            <td class="text-end">
                                <a href="/admin/frota/editar/<?php echo e($v['id']); ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/admin/veiculos/index.blade.php ENDPATH**/ ?>