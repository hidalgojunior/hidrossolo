<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Gerenciar Usuários</h4>
    <a href="/admin/usuarios/novo" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Novo Usuário</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Perfil</th>
                        <th>Último Acesso</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><strong><?php echo e($u['name']); ?></strong></td>
                        <td><?php echo e($u['email']); ?></td>
                        <td><span class="badge bg-info"><?php echo e($u['role_name']); ?></span></td>
                        <td><?php echo e($u['last_login'] ? date('d/m/Y H:i', strtotime($u['last_login'])) : '—'); ?></td>
                        <td><?php echo $u['active'] ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>'; ?></td>
                        <td class="text-end">
                            <a href="/admin/usuarios/editar/<?php echo e($u['id']); ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/admin/usuarios/index.blade.php ENDPATH**/ ?>