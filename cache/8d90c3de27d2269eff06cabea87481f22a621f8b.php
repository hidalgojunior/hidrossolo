<?php $__env->startSection('content'); ?>
<!-- Hero -->
<section class="hero">
    <div class="container text-center">
        <h1><?php echo nl2br(e($hero['title'] ?? 'Soluções Completas em<br>Poços Artesianos')); ?></h1>
        <?php if(!empty($hero['subtitle'])): ?>
            <p class="lead"><?php echo e($hero['subtitle']); ?></p>
        <?php endif; ?>
        <div class="mt-4">
            <a href="<?php echo e($hero['link_url'] ?? '/contato'); ?>" class="btn btn-light btn-lg me-3 fw-semibold"><?php echo e($hero['content'] ?? 'Solicitar Orçamento'); ?></a>
            <a href="/servicos" class="btn btn-outline-light btn-lg">Nossos Serviços</a>
        </div>
    </div>
</section>

<!-- Banners / Divulgação -->
<?php if(!empty($banners)): ?>
<section class="section bg-white">
    <div class="container">
        <h2 class="section-title text-center mb-4">Parceiros & Divulgações</h2>
        <div class="row g-4 justify-content-center">
            <?php $__currentLoopData = $banners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $banner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-4 col-lg-3">
                <a href="<?php echo e($banner['link_url'] ?? '#'); ?>" target="_blank" rel="noopener" class="card banner-card h-100 text-decoration-none" style="transition:transform 0.2s">
                    <?php if($banner['image']): ?>
                        <img src="<?php echo e($banner['image']); ?>" class="card-img-top p-3" alt="<?php echo e($banner['title']); ?>" style="height:140px;object-fit:contain">
                    <?php endif; ?>
                    <div class="card-body text-center">
                        <h5 class="card-title text-dark"><?php echo e($banner['title']); ?></h5>
                        <?php if($banner['subtitle']): ?>
                            <p class="card-text text-muted small"><?php echo e($banner['subtitle']); ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Diferenciais -->
<?php if(!empty($diferenciais)): ?>
<section class="section">
    <div class="container">
        <div class="row g-4">
            <?php $__currentLoopData = $diferenciais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-4">
                <div class="card service-card h-100">
                    <div class="icon text-primary fs-1 mb-3">
                        <?php if($dif['image']): ?>
                            <img src="<?php echo e($dif['image']); ?>" alt="<?php echo e($dif['title']); ?>" style="width:64px;height:64px">
                        <?php else: ?>
                            <i class="bi bi-check-circle"></i>
                        <?php endif; ?>
                    </div>
                    <h3><?php echo e($dif['title']); ?></h3>
                    <p class="text-muted"><?php echo e($dif['content']); ?></p>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Serviços em Destaque -->
<?php if(!empty($servicos)): ?>
<section class="section bg-white">
    <div class="container">
        <h2 class="section-title text-center"><?php echo e($servicosSectionTitle ?? 'Nossos Serviços'); ?></h2>
        <p class="section-subtitle text-center"><?php echo e($servicosSectionSub ?? 'Soluções completas para captação de água subterrânea'); ?></p>
        <div class="row g-4">
            <?php $__currentLoopData = $servicos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $servico): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-4">
                <div class="card service-card h-100">
                    <?php if($servico['icon']): ?>
                        <div class="icon"><i class="bi bi-<?php echo e($servico['icon']); ?>"></i></div>
                    <?php endif; ?>
                    <h3><?php echo e($servico['title']); ?></h3>
                    <p class="text-muted"><?php echo e(Str::limit($servico['description'], 120)); ?></p>
                    <a href="/servicos/<?php echo e($servico['slug']); ?>" class="btn btn-outline-primary btn-sm mt-2">Saiba Mais</a>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="text-center mt-4">
            <a href="/servicos" class="btn btn-primary">Ver Todos os Serviços</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="section" style="background: var(--gradient); color: #fff;">
    <div class="container text-center">
        <h2 style="color:#fff"><?php echo e($ctaSection['title'] ?? 'Precisa de um Poço Artesiano?'); ?></h2>
        <p class="lead mb-4" style="color:rgba(255,255,255,0.9)"><?php echo e($ctaSection['subtitle'] ?? 'Solicite um orçamento sem compromisso.'); ?></p>
        <a href="<?php echo e($ctaSection['link_url'] ?? '/contato'); ?>" class="btn btn-light btn-lg fw-semibold">
            <i class="bi bi-whatsapp me-2"></i><?php echo e($ctaSection['content'] ?? 'Solicitar Orçamento'); ?>

        </a>
    </div>
</section>

<!-- Depoimentos -->
<?php if(!empty($depoimentos)): ?>
<section class="section bg-white">
    <div class="container">
        <h2 class="section-title text-center">O que dizem nossos clientes</h2>
        <p class="section-subtitle text-center">A confiança de quem já contratou nossos serviços</p>
        <div class="row g-4">
            <?php $__currentLoopData = $depoimentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-4">
                <div class="card h-100 p-4">
                    <div class="text-warning mb-3">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    </div>
                    <p class="fst-italic">"<?php echo e($dep['content']); ?>"</p>
                    <strong><?php echo e($dep['title']); ?></strong>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/pages/home.blade.php ENDPATH**/ ?>