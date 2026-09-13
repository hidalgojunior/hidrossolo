<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Mensagens Recebidas</h4>
    <div>
        <a href="?status=all" class="btn btn-sm <?php echo e($status == 'all' ? 'btn-primary' : 'btn-outline-secondary'); ?>">Todas (<?php echo e($total); ?>)</a>
        <a href="?status=new" class="btn btn-sm <?php echo e($status == 'new' ? 'btn-primary' : 'btn-outline-secondary'); ?>">Novas (<?php echo e($novos); ?>)</a>
        <a href="?status=read" class="btn btn-sm <?php echo e($status == 'read' ? 'btn-primary' : 'btn-outline-secondary'); ?>">Lidas</a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if(empty($contatos)): ?>
            <p class="text-muted text-center py-5">Nenhuma mensagem encontrada.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Mensagem</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $contatos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="<?php echo e($c['status'] == 'new' ? 'table-active fw-semibold' : ''); ?>">
                            <td><?php echo e($c['name']); ?></td>
                            <td><?php echo e($c['email']); ?></td>
                            <td><?php echo e($c['phone']); ?></td>
                            <td><?php echo e(Str::limit($c['message'], 50)); ?></td>
                            <td><?php echo e(date('d/m/Y H:i', strtotime($c['created_at']))); ?></td>
                            <td><?php echo $c['status'] == 'new' ? '<span class="badge bg-danger">Nova</span>' : '<span class="badge bg-secondary">Lida</span>'; ?></td>
                            <td><a href="/admin/contatos/<?php echo e($c['id']); ?>" class="btn btn-sm btn-outline-primary">Ver</a></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/admin/contatos/index.blade.php ENDPATH**/ ?>