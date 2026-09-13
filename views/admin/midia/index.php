<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Biblioteca de Mídias</h4>
    <div>
        <form method="POST" action="/admin/midia/scan" style="display:inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-primary btn-sm me-2" title="Escanear imagens existentes no servidor">
                <i class="bi bi-search me-1"></i>Escanear Imagens
            </button>
        </form>
        <small class="text-muted"><?= e($total) ?> arquivo(s)</small>
    </div>
</div>

<!-- Upload -->
<div class="card mb-4">
    <div class="card-body">
        <form action="/admin/midia/upload" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Selecionar Arquivo</label>
                <input type="file" name="file" class="form-control" required accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
            </div>
            <div class="col-md-4">
                <label class="form-label">Categoria</label>
                <input type="text" name="category" class="form-control" placeholder="Ex: banners, servicos, blog" value="general">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-cloud-upload me-2"></i>Enviar</button>
            </div>
        </form>
    </div>
</div>

<!-- Filtro categorias -->
<div class="mb-3">
    <a href="?categoria=all" class="btn btn-sm <?= e($categoria == 'all' ? 'btn-primary' : 'btn-outline-secondary') ?> me-1">Todas</a>
    <?php foreach ($categorias as $cat) { ?>
        <a href="?categoria=<?= e($cat['category']) ?>" class="btn btn-sm <?= e($categoria == $cat['category'] ? 'btn-primary' : 'btn-outline-secondary') ?> me-1">
            <?= e($cat['category']) ?>
        </a>
    <?php } ?>
</div>

<!-- Grid de mídias -->
<div class="row g-3">
    <?php if (empty($midias)) { ?>
        <div class="col-12 text-center py-5 text-muted">Nenhuma mídia encontrada.</div>
    <?php } else { ?>
        <?php foreach ($midias as $m) { ?>
        <div class="col-md-3 col-lg-2">
            <div class="card h-100">
                <?php if (str_starts_with($m['mime_type'], 'image/')) { ?>
                    <img src="<?= e($m['thumbnail_path'] ?? $m['file_path']) ?>" class="card-img-top" style="height:120px;object-fit:cover" alt="<?= e($m['original_name']) ?>">
                <?php } else { ?>
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:120px">
                        <i class="bi bi-file-earmark fs-1 text-muted"></i>
                    </div>
                <?php } ?>
                <div class="card-body p-2">
                    <small class="text-truncate d-block" title="<?= e($m['original_name']) ?>"><?= e($m['original_name']) ?></small>
                    <small class="text-muted"><?= e(number_format($m['file_size'] / 1024, 1)) ?> KB</small>
                    <div class="input-group input-group-sm mt-1">
                        <input type="text" class="form-control form-control-sm font-monospace" value="<?= e($m['file_path']) ?>" readonly style="font-size:0.65rem" id="url-<?= e($m['id']) ?>">
                        <button class="btn btn-outline-secondary btn-sm" onclick="copyUrl(<?= e($m['id']) ?>)" title="Copiar URL">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <form method="POST" action="/admin/midia/delete/<?= e($m['id']) ?>" class="mt-1" onsubmit="return confirm('Excluir esta mídia permanentemente?')">
                        <?= csrf_field() ?>
                        <button class="btn btn-outline-danger btn-sm w-100" title="Excluir"><i class="bi bi-trash me-1"></i>Excluir</button>
                    </form>
                </div>
            </div>
        </div>
        <?php } ?>
    <?php } ?>
</div>

<?php if ($total > $perPage) { ?>
<nav class="mt-4 d-flex justify-content-center">
    <?php $totalPages = ceil($total / $perPage) ?>
    <ul class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
            <li class="page-item <?= e($i == $pagina ? 'active' : '') ?>">
                <a class="page-link" href="?categoria=<?= e($categoria) ?>&page=<?= e($i) ?>"><?= e($i) ?></a>
            </li>
        <?php } ?>
    </ul>
</nav>
<?php } ?>
<?php $view->endSection(); ?>

<?php $view->section('scripts'); ?>
<script>
function copyUrl(id) {
    const input = document.getElementById('url-' + id);
    input.select();
    navigator.clipboard.writeText(input.value);
    // Feedback visual
    const btn = input.nextElementSibling;
    const icon = btn.querySelector('i');
    icon.className = 'bi bi-check-lg text-success';
    setTimeout(() => icon.className = 'bi bi-clipboard', 1500);
}
</script>
<?php $view->endSection(); ?>
