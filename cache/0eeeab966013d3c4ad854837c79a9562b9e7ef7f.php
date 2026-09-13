<?php $__env->startSection('content'); ?>
<h4 class="mb-4">Gerenciar Empresa</h4>

<?php
    $sectionMap = [];
    foreach ($sections as $s) {
        $sectionMap[$s['section']] = $s['content'] ?? '';
    }
?>
<form method="POST" action="/admin/cms/empresa">
            <?php echo \App\Middleware\CsrfMiddleware::field(); ?>
    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">🏢 Conteúdo da Página</h5></div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">História / Texto Principal</label>
                <textarea name="content" class="form-control editor" rows="12"><?php echo e($page['content'] ?? ''); ?></textarea>
                <small class="text-muted">HTML permitido</small>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Imagem de Destaque (URL)</label>
                    <input type="text" name="featured_image" class="form-control" value="<?php echo e($page['featured_image'] ?? '/assets/images/empresa.jpg'); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Meta Title (SEO)</label>
                    <input type="text" name="meta_title" class="form-control" value="<?php echo e($page['meta_title'] ?? ''); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Meta Description (SEO)</label>
                    <input type="text" name="meta_description" class="form-control" value="<?php echo e($page['meta_description'] ?? ''); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">🎯 Missão, Visão e Valores</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Missão</label>
                    <textarea name="missao" class="form-control" rows="4"><?php echo e($sectionMap['missao'] ?? ''); ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Visão</label>
                    <textarea name="visao" class="form-control" rows="4"><?php echo e($sectionMap['visao'] ?? ''); ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Valores</label>
                    <textarea name="valores" class="form-control" rows="4"><?php echo e($sectionMap['valores'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg">💾 Salvar Empresa</button>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/admin/cms/empresa.blade.php ENDPATH**/ ?>