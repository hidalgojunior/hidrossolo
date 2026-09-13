<?php $view->layout('layouts.main'); ?>

<?php $view->section('content'); ?>
<section class="page-hero">
    <div class="container">
        <h1>Blog</h1>
        <p class="lead">Artigos e novidades sobre poços artesianos</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (empty($posts)) { ?>
            <div class="text-center py-5">
                <i class="bi bi-journal-text fs-1 text-muted"></i>
                <h3 class="mt-3">Nenhum artigo publicado</h3>
                <p class="text-muted">Em breve teremos conteúdo aqui!</p>
            </div>
        <?php } else { ?>
            <div class="row g-4">
                <?php foreach ($posts as $post) { ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <?php if ($post['featured_image']) { ?>
                            <img src="<?= e($post['featured_image']) ?>" class="card-img-top" alt="<?= e($post['title']) ?>" style="height:200px;object-fit:cover">
                        <?php } ?>
                        <div class="card-body d-flex flex-column">
                            <small class="text-muted mb-2">
                                <i class="bi bi-calendar me-1"></i><?= e(date('d/m/Y', strtotime($post['published_at'] ?? $post['created_at']))) ?>
                            </small>
                            <h3 class="h5"><?= e($post['title']) ?></h3>
                            <p class="text-muted flex-grow-1"><?= e(str_limit($post['excerpt'] ?: strip_tags($post['content']), 120)) ?></p>
                            <a href="/blog/<?= e($post['slug']) ?>" class="btn btn-outline-primary mt-auto">Ler Mais</a>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

            <!-- Paginação -->
            <?php if ($total > $perPage) { ?>
            <nav class="mt-5 d-flex justify-content-center">
                <?php $totalPages = ceil($total / $perPage) ?>
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                        <li class="page-item <?= e($i == $page ? 'active' : '') ?>">
                            <a class="page-link" href="?page=<?= e($i) ?>"><?= e($i) ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </nav>
            <?php } ?>
        <?php } ?>
    </div>
</section>
<?php $view->endSection(); ?>
