<?php
/* Bloco da Home: secoes (partial carregado por pages/home.php) */
?>
<!-- Seções livres criadas no CMS (aparecem antes do CTA final) -->
<?php foreach (($secoes ?? []) as $indiceSecao => $secao) { ?>
<section class="section<?= $indiceSecao % 2 === 1 ? ' section-alt' : '' ?>" id="secao-<?= e($secao['id']) ?>">
    <div class="container">
        <div class="text-center mb-4">
            <?php if (!empty($secao['subtitle'])) { ?>
                <span class="eyebrow eyebrow-dark"><?= e($secao['subtitle']) ?></span>
            <?php } ?>
            <h2 class="section-title"><?= e($secao['title']) ?></h2>
        </div>

        <div class="row g-4 align-items-center">
            <?php if (!empty($secao['image'])) { ?>
                <div class="col-lg-5">
                    <img src="<?= e($secao['image']) ?>" alt="<?= e($secao['title']) ?>"
                         class="img-fluid rounded-3 shadow-sm" loading="lazy">
                </div>
            <?php } ?>

            <div class="<?= !empty($secao['image']) ? 'col-lg-7' : 'col-12' ?>">
                <div class="section-content">
                    <?= $secao['content'] ?: '<p class="text-muted mb-0">' . e($secao['subtitle'] ?? '') . '</p>' ?>
                </div>

                <?php if (!empty($secao['link_url'])) { ?>
                    <a href="<?= e($secao['link_url']) ?>" class="btn btn-outline-primary mt-3">
                        <?= e($secao['link_text'] ?: 'Saiba mais') ?>
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>
</section>
<?php } ?>
