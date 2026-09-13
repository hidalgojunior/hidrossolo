<?php $__env->startSection('content'); ?>
<h4 class="mb-4">📋 Logs de Auditoria</h4>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Data</th><th>Usuário</th><th>Ação</th><th>Entidade</th><th>ID</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><small><?php echo e(date('d/m/Y H:i', strtotime($log['created_at']))); ?></small></td>
                        <td><?php echo e($log['user_name'] ?? 'Sistema'); ?></td>
                        <td><code><?php echo e($log['action']); ?></code></td>
                        <td><?php echo e($log['entity_type'] ?? '—'); ?></td>
                        <td><?php echo e($log['entity_id'] ?? '—'); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">Nenhum registro de auditoria.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if($total > $limit): ?>
<nav class="mt-3 d-flex justify-content-center"><ul class="pagination">
    <?php for($i = 1; $i <= ceil($total / $limit); $i++): ?>
        <li class="page-item <?php echo e($i == $page ? 'active' : ''); ?>"><a class="page-link" href="?page=<?php echo e($i); ?>"><?php echo e($i); ?></a></li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/admin/auditoria/index.blade.php ENDPATH**/ ?>