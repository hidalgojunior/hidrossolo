<?php
/* Bloco da Home: parceiros (partial carregado por pages/home.php) */
?>
<!-- Parceiros / divulgações -->
<?php if (!empty($banners)) { ?>
<section class="section section-alt">
    <div class="container">
        <h2 class="section-title text-center">Parceiros &amp; Divulgações</h2>
        <div class="row g-4 justify-content-center mt-2">
            <?php foreach ($banners as $banner) { ?>
            <div class="col-12 col-md-4 col-lg-3">
                <a href="<?= e($banner['link_url'] ?? '#') ?>" target="_blank" rel="noopener" class="card h-100 text-decoration-none">
                    <?php if (!empty($banner['image'])) { ?>
                        <img src="<?= e($banner['image']) ?>" class="card-img-top p-3" alt="<?= e($banner['title']) ?>" style="height:140px;object-fit:contain">
                    <?php } ?>
                    <div class="card-body text-center">
                        <h5 class="card-title"><?= e($banner['title']) ?></h5>
                        <?php if (!empty($banner['subtitle'])) { ?>
                            <p class="card-text text-muted small mb-0"><?= e($banner['subtitle']) ?></p>
                        <?php } ?>
                    </div>
                </a>
            </div>
            <?php } ?>
        </div>
    </div>
</section>
<?php } ?>
