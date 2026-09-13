<?php $__env->startSection('content'); ?>
<!-- Hero -->
<section class="hero">
    <div class="container">
        <h1>Sobre a Hidrossolo</h1>
        <p class="lead">Conheça nossa história e compromisso com a excelência</p>
    </div>
</section>

<!-- Conteúdo -->
<section class="section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6">
                <h2 class="section-title">Nossa História</h2>
                <?php if($page && $page['content']): ?>
                    <?php echo $page['content']; ?>

                <?php else: ?>
                    <p>A <strong>Hidrossolo Poços Artesianos</strong> atua há mais de 20 anos no mercado de perfuração de poços artesianos, oferecendo soluções completas para captação de água subterrânea em Marília e região.</p>
                    <p>Com equipe técnica altamente qualificada e equipamentos modernos, realizamos desde o estudo geológico inicial até a instalação completa do sistema de bombeamento, sempre seguindo as normas técnicas e ambientais vigentes.</p>
                <?php endif; ?>
            </div>
            <div class="col-lg-6">
                <img src="<?php echo e($page['featured_image'] ?? '/assets/images/empresa.jpg'); ?>" alt="Hidrossolo" class="img-fluid rounded-4 shadow">
            </div>
        </div>
    </div>
</section>

<!-- Missão, Visão, Valores -->
<section class="section bg-white">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card service-card h-100">
                    <div class="icon"><i class="bi bi-bullseye"></i></div>
                    <h3>Missão</h3>
                    <p><?php echo e($sectionContents['missao'] ?? 'Oferecer soluções sustentáveis em captação de água subterrânea, garantindo qualidade, segurança e satisfação dos nossos clientes.'); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card service-card h-100">
                    <div class="icon"><i class="bi bi-eye"></i></div>
                    <h3>Visão</h3>
                    <p><?php echo e($sectionContents['visao'] ?? 'Ser referência regional em perfuração de poços artesianos, reconhecida pela excelência técnica e responsabilidade ambiental.'); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card service-card h-100">
                    <div class="icon"><i class="bi bi-star"></i></div>
                    <h3>Valores</h3>
                    <p><?php echo e($sectionContents['valores'] ?? 'Ética, transparência, compromisso com o meio ambiente, inovação constante e respeito às pessoas.'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/pages/empresa.blade.php ENDPATH**/ ?>