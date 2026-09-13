<?php $view->layout('layouts.main'); ?>

<?php $view->section('content'); ?>
<section class="hero">
    <div class="container">
        <h1><?= e($page['title']) ?></h1>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($page['featured_image']) { ?>
                    <img src="<?= e($page['featured_image']) ?>" alt="<?= e($page['title']) ?>" class="img-fluid rounded-4 shadow mb-4 w-100" style="max-height:400px;object-fit:cover">
                <?php } ?>

                <div class="content">
                    <?= $page['content'] ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $view->endSection(); ?>
