<?php $__env->startSection('content'); ?>
<h4 class="mb-4"><?php echo e($usuario ? 'Editar Usuário' : 'Novo Usuário'); ?></h4>

<div class="card">
    <div class="card-body">
            <form method="POST" action="<?php echo e($usuario ? '/admin/usuarios/editar/' . $usuario['id'] : '/admin/usuarios/novo'); ?>">

            <?php echo \App\Middleware\CsrfMiddleware::field(); ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nome *</label>
                    <input type="text" name="name" class="form-control" required value="<?php echo e($usuario['name'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">E-mail *</label>
                    <input type="email" name="email" class="form-control" required value="<?php echo e($usuario['email'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Senha <?php echo e($usuario ? '(deixe em branco para manter)' : '*'); ?></label>
                    <div class="input-group">
                        <input type="password" name="password" id="user_password" class="form-control" <?php echo e($usuario ? '' : 'required'); ?> minlength="6">
                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="user_password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Perfil *</label>
                    <select name="role_id" class="form-select" required>
                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($role['id']); ?>" <?php echo e(($usuario['role_id'] ?? '') == $role['id'] ? 'selected' : ''); ?>>
                                <?php echo e(ucfirst($role['name'])); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end pb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="active" id="active" <?php echo e(($usuario['active'] ?? true) ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="active">Ativo</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="/admin/usuarios" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/admin/usuarios/form.blade.php ENDPATH**/ ?>