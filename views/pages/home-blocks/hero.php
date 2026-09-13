<?php
/* Bloco da Home: hero (partial carregado por pages/home.php) */
?>
<!-- Hero -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="eyebrow">Água subterrânea com responsabilidade técnica</span>
                <h1><?= e($heroTitle) ?></h1>
                <p class="lead"><?= e($heroSubtitle) ?></p>

                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="<?= e($hero['link_url'] ?? '/orcamento') ?>" class="btn btn-light btn-lg">
                        <i class="bi bi-send"></i> <?= e($hero['content'] ?? 'Solicitar Orçamento') ?>
                    </a>
                    <a href="/servicos" class="btn btn-outline-light btn-lg">Nossos Serviços</a>
                </div>

                <div class="d-flex flex-wrap gap-4 mt-4 text-white" style="opacity:.9">
                    <span><i class="bi bi-check-circle me-1"></i>Atendimento em toda a região</span>
                    <span><i class="bi bi-check-circle me-1"></i>Orçamento sem compromisso</span>
                </div>
            </div>

            <div class="col-lg-5 d-none d-lg-block">
                <img src="/assets/images/plataforma.jpg" alt="Equipe Hidrossolo em operação"
                     class="img-fluid rounded-4 shadow" style="box-shadow:0 25px 60px rgba(2,6,23,.45)">
            </div>
        </div>
    </div>
</section>
