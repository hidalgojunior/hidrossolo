<?php
/* Bloco da Home: servicos (partial carregado por pages/home.php) */
?>
<!-- Serviços -->
<section class="section section-alt">
    <div class="container">
        <div class="text-center">
            <span class="eyebrow eyebrow-dark">O que fazemos</span>
            <h2 class="section-title"><?= e($servicosSectionTitle ?? 'Nossos Serviços') ?></h2>
            <p class="section-subtitle"><?= e($servicosSectionSub ?? 'Soluções completas para captação de água subterrânea, do estudo inicial à manutenção.') ?></p>
        </div>

        <div class="row g-4">
            <?php foreach ($servicos as $servico) { ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="service-card h-100 d-flex flex-column">
                    <div class="icon"><i class="bi bi-<?= e($servico['icon'] ?? 'droplet') ?>"></i></div>
                    <h3><?= e($servico['title']) ?></h3>
                    <p class="flex-grow-1"><?= e(str_limit($servico['description'] ?? '', 130)) ?></p>
                    <a href="/servicos/<?= e($servico['slug']) ?>" class="btn btn-outline-primary btn-sm mt-3 align-self-center">
                        Saiba mais <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</section>
