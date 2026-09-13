<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Gestão de Contratos</h4>
    <a href="/admin/contratos/novo" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Novo Contrato</a>
</div>

<?php if(!empty($vencendo)): ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>Atenção:</strong> <?php echo e(count($vencendo)); ?> contrato(s) próximo(s) do vencimento.
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <?php if(empty($contratos)): ?>
            <p class="text-muted text-center py-5">Nenhum contrato cadastrado.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nº</th>
                            <th>Cliente</th>
                            <th>Responsável</th>
                            <th>Início</th>
                            <th>Término</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $contratos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="<?php echo e($c['status'] === 'active' && strtotime($c['end_date']) < strtotime('+30 days') ? 'table-warning' : ''); ?>">
                            <td><strong><?php echo e($c['contract_number']); ?></strong></td>
                            <td><?php echo e($c['client_name']); ?></td>
                            <td><?php echo e($c['responsible']); ?></td>
                            <td><?php echo e(date('d/m/Y', strtotime($c['start_date']))); ?></td>
                            <td><?php echo e(date('d/m/Y', strtotime($c['end_date']))); ?></td>
                            <td>R$ <?php echo e(number_format($c['value'], 2, ',', '.')); ?></td>
                            <td><?php echo match($c['status']) {
                                'active' => '<span class="badge bg-success">Ativo</span>',
                                'expired' => '<span class="badge bg-danger">Vencido</span>',
                                'cancelled' => '<span class="badge bg-secondary">Cancelado</span>',
                                default => '<span class="badge bg-info">Concluído</span>'
                            }; ?></td>
                            <td class="text-end">
                                <a href="/admin/contratos/editar/<?php echo e($c['id']); ?>" class="btn btn-sm btn-outline-primary">Editar</a>
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

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/admin/contratos/index.blade.php ENDPATH**/ ?>