<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Biblioteca de Mídias</h4>
    <div>
        <form method="POST" action="/admin/midia/scan" style="display:inline">
            <?php echo \App\Middleware\CsrfMiddleware::field(); ?>
            <button type="submit" class="btn btn-outline-primary btn-sm me-2" title="Escanear imagens existentes no servidor">
                <i class="bi bi-search me-1"></i>Escanear Imagens
            </button>
        </form>
        <small class="text-muted"><?php echo e($total); ?> arquivo(s)</small>
    </div>
</div>

<!-- Upload -->
<div class="card mb-4">
    <div class="card-body">
        <form action="/admin/midia/upload" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Selecionar Arquivo</label>
                <input type="file" name="file" class="form-control" required accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
            </div>
            <div class="col-md-4">
                <label class="form-label">Categoria</label>
                <input type="text" name="category" class="form-control" placeholder="Ex: banners, servicos, blog" value="general">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-cloud-upload me-2"></i>Enviar</button>
            </div>
        </form>
    </div>
</div>

<!-- Filtro categorias -->
<div class="mb-3">
    <a href="?categoria=all" class="btn btn-sm <?php echo e($categoria == 'all' ? 'btn-primary' : 'btn-outline-secondary'); ?> me-1">Todas</a>
    <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="?categoria=<?php echo e($cat['category']); ?>" class="btn btn-sm <?php echo e($categoria == $cat['category'] ? 'btn-primary' : 'btn-outline-secondary'); ?> me-1">
            <?php echo e($cat['category']); ?>

        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<!-- Grid de mídias -->
<div class="row g-3">
    <?php if(empty($midias)): ?>
        <div class="col-12 text-center py-5 text-muted">Nenhuma mídia encontrada.</div>
    <?php else: ?>
        <?php $__currentLoopData = $midias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-3 col-lg-2">
            <div class="card h-100">
                <?php if(str_starts_with($m['mime_type'], 'image/')): ?>
                    <img src="<?php echo e($m['thumbnail_path'] ?? $m['file_path']); ?>" class="card-img-top" style="height:120px;object-fit:cover" alt="<?php echo e($m['original_name']); ?>">
                <?php else: ?>
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:120px">
                        <i class="bi bi-file-earmark fs-1 text-muted"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body p-2">
                    <small class="text-truncate d-block" title="<?php echo e($m['original_name']); ?>"><?php echo e($m['original_name']); ?></small>
                    <small class="text-muted"><?php echo e(number_format($m['file_size'] / 1024, 1)); ?> KB</small>
                    <div class="input-group input-group-sm mt-1">
                        <input type="text" class="form-control form-control-sm font-monospace" value="<?php echo e($m['file_path']); ?>" readonly style="font-size:0.65rem" id="url-<?php echo e($m['id']); ?>">
                        <button class="btn btn-outline-secondary btn-sm" onclick="copyUrl(<?php echo e($m['id']); ?>)" title="Copiar URL">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <form method="POST" action="/admin/midia/delete/<?php echo e($m['id']); ?>" class="mt-1" onsubmit="return confirm('Excluir esta mídia permanentemente?')">
                        <?php echo \App\Middleware\CsrfMiddleware::field(); ?>
                        <button class="btn btn-outline-danger btn-sm w-100" title="Excluir"><i class="bi bi-trash me-1"></i>Excluir</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
</div>

<?php if($total > $perPage): ?>
<nav class="mt-4 d-flex justify-content-center">
    <?php $totalPages = ceil($total / $perPage) ?>
    <ul class="pagination">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo e($i == $pagina ? 'active' : ''); ?>">
                <a class="page-link" href="?categoria=<?php echo e($categoria); ?>&page=<?php echo e($i); ?>"><?php echo e($i); ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function copyUrl(id) {
    const input = document.getElementById('url-' + id);
    input.select();
    navigator.clipboard.writeText(input.value);
    // Feedback visual
    const btn = input.nextElementSibling;
    const icon = btn.querySelector('i');
    icon.className = 'bi bi-check-lg text-success';
    setTimeout(() => icon.className = 'bi bi-clipboard', 1500);
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/admin/midia/index.blade.php ENDPATH**/ ?>