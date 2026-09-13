<?php $view->layout('layouts.main'); ?>

<?php $view->section('content'); ?>
<section class="section" style="min-height:60vh">
    <div class="container">
        <h1 class="mb-4">🔍 Resultados para "<?= e($q) ?>"</h1>

        <?php if (empty($results['pages']) && empty($results['services']) && empty($results['posts'])) { ?>
            <div class="text-center py-5">
                <i class="bi bi-search display-1 text-muted"></i>
                <p class="text-muted mt-3">Nenhum resultado encontrado para "<strong><?= e($q) ?></strong>".</p>
                <a href="/" class="btn btn-primary">Voltar ao Início</a>
            </div>
        <?php } else { ?>
            <?php foreach (['pages' => '📄 Páginas', 'services' => '🛠️ Serviços', 'posts' => '📰 Blog'] as $key => $label) { ?>
                <?php if (!empty($results[$key])) { ?>
                    <h4 class="mb-3"><?= e($label) ?></h4>
                    <div class="list-group mb-4">
                        <?php foreach ($results[$key] as $item) { ?>
                            <a href="/<?= e($key === 'services' ? 'servicos' : ($key === 'posts' ? 'blog' : '')) ?>/<?= e($item['slug']) ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between">
                                    <strong><?= e($item['title']) ?></strong>
                                    <small class="text-muted"><?= e($item['tipo']) ?></small>
                                </div>
                                <?php if (!empty($item['excerpt'])) { ?>
                                    <small class="text-muted"><?= e(str_limit(strip_tags($item['excerpt']), 150)) ?></small>
                                <?php } ?>
                            </a>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </div>
</section>
<?php $view->endSection(); ?>
