<?php $__env->startSection('content'); ?>
<section class="section" style="min-height:60vh">
    <div class="container">
        <h1 class="mb-4">🔍 Resultados para "<?php echo e($q); ?>"</h1>

        <?php if(empty($results['pages']) && empty($results['services']) && empty($results['posts'])): ?>
            <div class="text-center py-5">
                <i class="bi bi-search display-1 text-muted"></i>
                <p class="text-muted mt-3">Nenhum resultado encontrado para "<strong><?php echo e($q); ?></strong>".</p>
                <a href="/" class="btn btn-primary">Voltar ao Início</a>
            </div>
        <?php else: ?>
            <?php $__currentLoopData = ['pages' => '📄 Páginas', 'services' => '🛠️ Serviços', 'posts' => '📰 Blog']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(!empty($results[$key])): ?>
                    <h4 class="mb-3"><?php echo e($label); ?></h4>
                    <div class="list-group mb-4">
                        <?php $__currentLoopData = $results[$key]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="/<?php echo e($key === 'services' ? 'servicos' : ($key === 'posts' ? 'blog' : '')); ?>/<?php echo e($item['slug']); ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between">
                                    <strong><?php echo e($item['title']); ?></strong>
                                    <small class="text-muted"><?php echo e($item['tipo']); ?></small>
                                </div>
                                <?php if(!empty($item['excerpt'])): ?>
                                    <small class="text-muted"><?php echo e(Str::limit(strip_tags($item['excerpt']), 150)); ?></small>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/pages/busca.blade.php ENDPATH**/ ?>