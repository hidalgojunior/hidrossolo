<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Gerenciar Serviços</h4>
    <a href="/admin/servicos/novo" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Novo Serviço</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if(empty($servicos)): ?>
            <p class="text-muted text-center py-5">Nenhum serviço cadastrado.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Título</th>
                            <th>Slug</th>
                            <th>Destaque</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $servicos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $servico): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($servico['id']); ?></td>
                            <td><?php echo e($servico['title']); ?></td>
                            <td><code><?php echo e($servico['slug']); ?></code></td>
                            <td><?php echo $servico['highlight'] ? '<span class="badge bg-warning">Sim</span>' : '<span class="badge bg-secondary">Não</span>'; ?></td>
                            <td><?php echo $servico['active'] ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>'; ?></td>
                            <td class="text-end">
                                <a href="/admin/servicos/editar/<?php echo e($servico['id']); ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form action="/admin/servicos/excluir/<?php echo e($servico['id']); ?>" method="POST" class="d-inline" onsubmit="return confirm('Excluir este serviço?')">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
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

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/admin/servicos/index.blade.php ENDPATH**/ ?>