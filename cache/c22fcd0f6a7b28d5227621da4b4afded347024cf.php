<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Gerenciar Menus</h4>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="/admin/menus" method="POST">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Nome do Menu</label>
                    <input type="text" name="name" class="form-control" required placeholder="Ex: Menu Principal">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Localização</label>
                    <input type="text" name="location" class="form-control" required placeholder="Ex: header, footer">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Criar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <?php $__currentLoopData = $menus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between">
                <h5 class="mb-0"><?php echo e($menu['name']); ?></h5>
                <span class="badge bg-secondary"><?php echo e($menu['location']); ?></span>
            </div>
            <div class="card-body">
                <p class="text-muted">Gerencie os itens deste menu.</p>
                <a href="/admin/menus/<?php echo e($menu['id']); ?>" class="btn btn-sm btn-outline-primary">Editar Itens</a>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/admin/menus/index.blade.php ENDPATH**/ ?>