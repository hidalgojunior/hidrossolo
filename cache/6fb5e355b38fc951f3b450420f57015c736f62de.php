<?php $__env->startSection('content'); ?>
<section class="hero">
    <div class="container">
        <h1>Blog</h1>
        <p class="lead">Artigos e novidades sobre poços artesianos</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if(empty($posts)): ?>
            <div class="text-center py-5">
                <i class="bi bi-journal-text fs-1 text-muted"></i>
                <h3 class="mt-3">Nenhum artigo publicado</h3>
                <p class="text-muted">Em breve teremos conteúdo aqui!</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <?php if($post['featured_image']): ?>
                            <img src="<?php echo e($post['featured_image']); ?>" class="card-img-top" alt="<?php echo e($post['title']); ?>" style="height:200px;object-fit:cover">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <small class="text-muted mb-2">
                                <i class="bi bi-calendar me-1"></i><?php echo e(date('d/m/Y', strtotime($post['published_at'] ?? $post['created_at']))); ?>

                            </small>
                            <h3 class="h5"><?php echo e($post['title']); ?></h3>
                            <p class="text-muted flex-grow-1"><?php echo e(Str::limit($post['excerpt'] ?: strip_tags($post['content']), 120)); ?></p>
                            <a href="/blog/<?php echo e($post['slug']); ?>" class="btn btn-outline-primary mt-auto">Ler Mais</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Paginação -->
            <?php if($total > $perPage): ?>
            <nav class="mt-5 d-flex justify-content-center">
                <?php $totalPages = ceil($total / $perPage) ?>
                <ul class="pagination">
                    <?php for($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo e($i == $page ? 'active' : ''); ?>">
                            <a class="page-link" href="?page=<?php echo e($i); ?>"><?php echo e($i); ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/pages/blog.blade.php ENDPATH**/ ?>