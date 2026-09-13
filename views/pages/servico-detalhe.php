<?php $view->layout('layouts.main'); ?>

<?php $view->section('content'); ?>
<section class="page-hero">
    <div class="container">
        <h1><?= e($servico['title']) ?></h1>
        <p class="lead"><?= e($servico['description']) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <?php if ($servico['featured_image']) { ?>
                    <img src="<?= e($servico['featured_image']) ?>" alt="<?= e($servico['title']) ?>" class="img-fluid rounded-4 shadow mb-4 w-100" style="max-height:400px;object-fit:cover">
                <?php } ?>

                <div class="content">
                    <?= $servico['content'] ?: '<p>' . nl2br(e($servico['description'])) . '</p>' ?>
                </div>

                <?php if (!empty($galeria)) { ?>
                <h3 class="mt-5 mb-3">Galeria</h3>
                <div class="row g-3">
                    <?php foreach ($galeria as $img) { ?>
                    <div class="col-md-4">
                        <img src="<?= e($img['image_path']) ?>" alt="<?= e($img['alt_text']) ?>" class="img-fluid rounded-3 shadow-sm" style="height:180px;object-fit:cover;width:100%">
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>

            <div class="col-lg-4">
                <div class="card p-4 sticky-top" style="top:100px">
                    <h4>Solicitar Orçamento</h4>
                    <p class="text-muted">Interessado neste serviço? Entre em contato!</p>
                    <a href="/contato" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-envelope me-2"></i>Solicitar Orçamento
                    </a>
                    <a href="https://wa.me/5514991234567" class="btn btn-success w-100" target="_blank">
                        <i class="bi bi-whatsapp me-2"></i>WhatsApp
                    </a>
                </div>

                <?php if (!empty($outros)) { ?>
                <div class="mt-4">
                    <h5>Outros Serviços</h5>
                    <?php foreach ($outros as $s) { ?>
                    <a href="/servicos/<?= e($s['slug']) ?>" class="text-decoration-none">
                        <div class="card p-3 mb-2">
                            <strong><?= e($s['title']) ?></strong>
                            <small class="text-muted"><?= e(str_limit($s['description'], 80)) ?></small>
                        </div>
                    </a>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</section>
<?php $view->endSection(); ?>
