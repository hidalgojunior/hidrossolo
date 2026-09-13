<?php $view->layout('layouts.main'); ?>

<?php $view->section('content'); ?>
<section class="page-hero">
    <div class="container">
        <h1><?= e($post['title']) ?></h1>
        <p class="lead">
            <i class="bi bi-calendar me-1"></i><?= e(date('d/m/Y', strtotime($post['published_at'] ?? $post['created_at']))) ?>
            <?php if ($post['author_name']) { ?>
                &nbsp;·&nbsp; <i class="bi bi-person me-1"></i><?= e($post['author_name']) ?>
            <?php } ?>
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <?php if ($post['featured_image']) { ?>
                    <img src="<?= e($post['featured_image']) ?>" alt="<?= e($post['title']) ?>" class="img-fluid rounded-4 shadow mb-4 w-100" style="max-height:400px;object-fit:cover">
                <?php } ?>

                <div class="content fs-5">
                    <?= $post['content'] ?>
                </div>
            </div>

            <div class="col-lg-4">
                <?php if (!empty($recentes)) { ?>
                <div class="card p-4 sticky-top" style="top:100px">
                    <h5>Posts Recentes</h5>
                    <?php foreach ($recentes as $r) { ?>
                    <a href="/blog/<?= e($r['slug']) ?>" class="text-decoration-none">
                        <div class="d-flex gap-3 mb-3">
                            <?php if ($r['featured_image']) { ?>
                                <img src="<?= e($r['featured_image']) ?>" style="width:80px;height:60px;object-fit:cover;border-radius:8px">
                            <?php } ?>
                            <div>
                                <strong class="text-dark"><?= e($r['title']) ?></strong>
                                <br><small class="text-muted"><?= e(date('d/m/Y', strtotime($r['published_at'] ?? $r['created_at']))) ?></small>
                            </div>
                        </div>
                    </a>
                    <?php } ?>
                </div>
                <?php } ?>

                <div class="card p-4 mt-4">
                    <h5>Precisa de Ajuda?</h5>
                    <p class="text-muted">Fale com nossos especialistas</p>
                    <a href="/contato" class="btn btn-primary w-100">Solicitar Orçamento</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $view->endSection(); ?>
