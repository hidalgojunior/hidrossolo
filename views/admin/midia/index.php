<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<?php
$qs = static function (array $overrides = []) use ($q, $type, $categoria): string {
    $params = array_merge(
        ['categoria' => $categoria, 'type' => $type, 'q' => $q],
        $overrides
    );
    $params = array_filter($params, static fn($v) => $v !== '' && $v !== null);

    return '?' . http_build_query($params);
};
$totalPages = (int) ceil($total / max(1, $perPage));
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="mb-0">Biblioteca de Mídias</h4>
        <p class="text-muted small mb-0">
            Tudo o que você envia fica guardado aqui e pode ser reutilizado em serviços, páginas, blog e no logo do site.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-secondary"><?= e($total) ?> arquivo(s)</span>
        <form method="POST" action="/admin/midia/scan">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-primary btn-sm" title="Procurar imagens já existentes no servidor">
                <i class="bi bi-search me-1"></i>Escanear servidor
            </button>
        </form>
    </div>
</div>

<!-- Upload -->
<div class="media-upload-card mb-4">
    <form action="/admin/midia/upload" method="POST" enctype="multipart/form-data" id="mediaUploadForm">
        <?= csrf_field() ?>
        <div class="row g-3 align-items-end">
            <div class="col-lg-7">
                <label class="form-label mb-1">Enviar arquivos para a biblioteca</label>
                <div class="media-dropzone" data-media-dropzone id="pageDropzone">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <span>Arraste os arquivos aqui ou <strong>clique para escolher</strong></span>
                    <small>JPG, PNG, GIF, WebP, SVG, PDF, DOC, XLS — até 10MB cada (vários ao mesmo tempo)</small>
                </div>
                <input type="file" name="files[]" multiple hidden id="pageFileInput"
                       accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">            </div>
            <div class="col-lg-5">
                <label class="form-label" for="uploadCategory">Categoria</label>
                <select name="category" id="uploadCategory" class="form-select mb-3">
                    <?php foreach ($catLabels as $slug => $label) { ?>
                        <option value="<?= e($slug) ?>" <?= e($slug === 'general' ? 'selected' : '') ?>><?= e($label) ?></option>
                    <?php } ?>
                </select>

                <div class="alert alert-info mb-0 small">
                    <i class="bi bi-info-circle me-1"></i>
                    Depois de enviar, use o botão <strong>Copiar</strong> ou escolha a imagem
                    diretamente no campo de mídia dos formulários.
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Vídeo do YouTube -->
<div class="media-upload-card mb-4">
    <form action="/admin/midia/youtube" method="POST">
        <?= csrf_field() ?>
        <div class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label class="form-label mb-1" for="youtubeUrl">
                    <i class="bi bi-youtube text-danger me-1"></i>Adicionar vídeo do YouTube
                </label>
                <input type="text" name="youtube_url" id="youtubeUrl" class="form-control" required
                       placeholder="https://www.youtube.com/watch?v=... | youtu.be/... | /shorts/...">
                <small class="text-muted d-block mt-1">
                    O vídeo continua no YouTube: não ocupa espaço nem banda da hospedagem.
                </small>
            </div>
            <div class="col-lg-4">
                <label class="form-label mb-1" for="youtubeTitle">Título (opcional)</label>
                <input type="text" name="title" id="youtubeTitle" class="form-control" maxlength="255"
                       placeholder="Ex: Perfuração de poço em Marília">
            </div>
            <div class="col-lg-2">
                <label class="form-label mb-1" for="youtubeCategory">Categoria</label>
                <select name="category" id="youtubeCategory" class="form-select">
                    <?php foreach ($catLabels as $slug => $label) { ?>
                        <option value="<?= e($slug) ?>" <?= e($slug === 'videos' ? 'selected' : '') ?>><?= e($label) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-lg-1">
                <button type="submit" class="btn btn-danger w-100" title="Adicionar vídeo à biblioteca">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Filtros -->
<form method="GET" action="/admin/midia" class="media-filters">
    <input type="hidden" name="categoria" value="<?= e($categoria) ?>">
    <input type="hidden" name="type" value="<?= e($type) ?>">

    <div class="media-toolbar-search" style="max-width:280px">
        <i class="bi bi-search"></i>
        <input type="search" name="q" class="form-control form-control-sm" placeholder="Buscar por nome..." value="<?= e($q) ?>">
    </div>

    <div class="btn-group btn-group-sm" role="group" aria-label="Tipo">
        <a href="<?= e($qs(['type' => 'all', 'page' => null])) ?>" class="btn btn-sm <?= $type === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>">Todos</a>
        <a href="<?= e($qs(['type' => 'image', 'page' => null])) ?>" class="btn btn-sm <?= $type === 'image' ? 'btn-primary' : 'btn-outline-secondary' ?>">Imagens</a>
        <a href="<?= e($qs(['type' => 'video', 'page' => null])) ?>" class="btn btn-sm <?= $type === 'video' ? 'btn-primary' : 'btn-outline-secondary' ?>"><i class="bi bi-youtube me-1"></i>Vídeos</a>
        <a href="<?= e($qs(['type' => 'document', 'page' => null])) ?>" class="btn btn-sm <?= $type === 'document' ? 'btn-primary' : 'btn-outline-secondary' ?>">Documentos</a>
    </div>
</form>

<div class="media-filters">
    <a href="<?= e($qs(['categoria' => 'all', 'page' => null])) ?>" class="media-chip <?= $categoria === 'all' ? 'is-active' : '' ?>">
        Todas
    </a>
    <?php foreach ($categorias as $cat) { ?>
        <a href="<?= e($qs(['categoria' => $cat['name'], 'page' => null])) ?>"
           class="media-chip <?= $categoria === $cat['name'] ? 'is-active' : '' ?>">
            <?= e($catLabels[$cat['name']] ?? ucfirst($cat['name'])) ?>
            <span class="count"><?= e($cat['count']) ?></span>
        </a>
    <?php } ?>
</div>

<!-- Grade -->
<?php if (empty($midias)) { ?>
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            Nenhuma mídia encontrada<?= $q !== '' ? ' para "' . e($q) . '"' : '' ?>.
            <div class="small mt-1">Envie arquivos acima ou use “Escanear servidor”.</div>
        </div>
    </div>
<?php } else { ?>
    <div class="media-page-grid">
        <?php foreach ($midias as $m) { ?>
        <div class="media-card">
            <div class="media-card-thumb">
                <?php if ($m['is_image'] || $m['is_video']) { ?>
                    <button type="button" class="media-thumb-open <?= $m['is_video'] ? 'is-video' : '' ?>"
                            data-media-view
                            data-id="<?= e($m['id']) ?>"
                            data-url="<?= e($m['url']) ?>"
                            data-name="<?= e($m['name']) ?>"
                            data-alt="<?= e($m['alt_text']) ?>"
                            data-category="<?= e($catLabels[$m['category']] ?? $m['category']) ?>"
                            data-size="<?= e($m['is_video'] ? 'YouTube' : $m['size_human']) ?>"
                            data-mime="<?= e($m['mime_type']) ?>"
                            data-date="<?= e(date('d/m/Y', strtotime($m['created_at'] ?: 'now'))) ?>"
                            data-video="<?= $m['is_video'] ? '1' : '0' ?>"
                            data-embed="<?= e($m['embed_url']) ?>"
                            title="<?= e($m['is_video'] ? 'Clique para assistir' : 'Clique para ampliar') ?>">
                        <img src="<?= e($m['thumb']) ?>" alt="<?= e($m['alt_text'] ?: $m['name']) ?>" loading="lazy">
                        <span class="media-thumb-zoom"><i class="bi <?= $m['is_video'] ? 'bi-play-fill' : 'bi-arrows-fullscreen' ?>"></i></span>
                    </button>
                <?php } else { ?>
                    <a href="<?= e($m['url']) ?>" target="_blank" rel="noopener" class="media-thumb-open" title="Abrir arquivo em nova aba">
                        <i class="bi <?= e($m['icon']) ?>"></i>
                        <span class="media-thumb-zoom"><i class="bi bi-box-arrow-up-right"></i></span>
                    </a>
                <?php } ?>
                <span class="media-card-badge"><?= e($catLabels[$m['category']] ?? $m['category']) ?></span>
            </div>

            <div class="media-card-info">
                <span class="media-card-name" title="<?= e($m['name']) ?>"><?= e($m['name']) ?></span>
                <span class="media-card-meta">
                    <span><?= e($m['size_human']) ?></span>
                    <span><?= e(date('d/m/Y', strtotime($m['created_at'] ?: 'now'))) ?></span>
                </span>
            </div>

            <div class="media-card-actions">
                <button type="button" class="btn btn-outline-secondary"
                        onclick="mediaCopy('<?= e($m['url']) ?>', this)" title="Copiar caminho">
                    <i class="bi bi-clipboard"></i>
                </button>
                <button type="button" class="btn btn-outline-primary"
                        data-media-edit
                        data-id="<?= e($m['id']) ?>"
                        data-name="<?= e($m['name']) ?>"
                        data-alt="<?= e($m['alt_text']) ?>"
                        data-category="<?= e($m['category']) ?>"
                        data-url="<?= e($m['url']) ?>"
                        data-thumb="<?= e($m['thumb']) ?>"
                        data-image="<?= e($m['is_image'] ? '1' : '0') ?>"
                        data-video="<?= $m['is_video'] ? '1' : '0' ?>"
                        title="Editar">
                    <i class="bi bi-pencil"></i>
                </button>
                <form method="POST" action="/admin/midia/delete/<?= e($m['id']) ?>" class="flex-grow-1"
                      onsubmit="return confirm('Excluir este arquivo permanentemente?');">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline-danger w-100" title="Excluir">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        <?php } ?>
    </div>

    <?php if ($totalPages > 1) { ?>
    <nav class="mt-4 d-flex justify-content-center">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                <li class="page-item <?= e($i === $pagina ? 'active' : '') ?>">
                    <a class="page-link" href="<?= e($qs(['page' => $i])) ?>"><?= e($i) ?></a>
                </li>
            <?php } ?>
        </ul>
    </nav>
    <?php } ?>
<?php } ?>

<!-- Modal de edição -->
<div class="modal" id="mediaEditModal" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="" id="mediaEditForm" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar mídia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img src="" alt="" id="mediaEditPreview" class="rounded-3" style="max-height:150px;display:none">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nome de exibição</label>
                    <input type="text" name="original_name" id="mediaEditName" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Texto alternativo (acessibilidade/SEO)</label>
                    <input type="text" name="alt_text" id="mediaEditAlt" class="form-control"
                           placeholder="Ex: Equipe perfurando poço artesiano">
                </div>

                <div class="mb-3">
                    <label class="form-label">Categoria</label>
                    <select name="category" id="mediaEditCategory" class="form-select">
                        <?php foreach ($catLabels as $slug => $label) { ?>
                            <option value="<?= e($slug) ?>"><?= e($label) ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-0">
                    <label class="form-label">Caminho para usar no site</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="mediaEditUrl" class="form-control font-monospace" readonly>
                        <button type="button" class="btn btn-outline-secondary" onclick="mediaCopy(document.getElementById('mediaEditUrl').value, this)">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg me-1"></i>Salvar</button>
            </div>
        </form>
    </div>
</div>

<!-- Visualizador em tela cheia -->
<div class="media-lightbox" id="mediaLightbox" role="dialog" aria-modal="true" aria-label="Pré-visualização da mídia" hidden>
    <div class="media-lightbox-bar">
        <div class="media-lightbox-info">
            <strong id="mlbName">—</strong>
            <span class="media-lightbox-meta" id="mlbMeta"></span>
        </div>

        <div class="media-lightbox-tools">
            <button type="button" class="media-lightbox-btn" data-mlb-zoom title="Tamanho real (tecla Z)">
                <i class="bi bi-zoom-in"></i><span class="d-none d-sm-inline">Zoom</span>
            </button>
            <button type="button" class="media-lightbox-btn" data-mlb-copy title="Copiar caminho">
                <i class="bi bi-clipboard"></i>
            </button>
            <a class="media-lightbox-btn" data-mlb-open href="#" target="_blank" rel="noopener" title="Abrir em nova aba">
                <i class="bi bi-box-arrow-up-right"></i>
            </a>
            <a class="media-lightbox-btn" data-mlb-download href="#" download title="Baixar arquivo">
                <i class="bi bi-download"></i>
            </a>
            <button type="button" class="media-lightbox-btn is-close" data-mlb-close title="Fechar (Esc)">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <button type="button" class="media-lightbox-nav is-prev" data-mlb-prev aria-label="Mídia anterior">
        <i class="bi bi-chevron-left"></i>
    </button>

    <div class="media-lightbox-stage" data-mlb-stage>
        <div class="media-lightbox-spinner" data-mlb-spinner><div class="spinner-border" role="status"></div></div>
        <img id="mlbImage" src="" alt="">
        <iframe id="mlbVideo" src="" title="Player do vídeo do YouTube" hidden
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
    </div>

    <button type="button" class="media-lightbox-nav is-next" data-mlb-next aria-label="Próxima mídia">
        <i class="bi bi-chevron-right"></i>
    </button>

    <div class="media-lightbox-foot">
        <span id="mlbCounter"></span>
        <span class="d-none d-md-inline text-muted ms-2">· use ← → para navegar e Esc para fechar</span>
    </div>
</div>
<?php $view->endSection(); ?>

<?php $view->section('scripts'); ?>
<script>
// Dropzone da página
(function () {
    var dropzone = document.getElementById('pageDropzone');
    var input = document.getElementById('pageFileInput');
    var form = document.getElementById('mediaUploadForm');

    if (dropzone && input && form) {
        dropzone.addEventListener('click', function () { input.click(); });

        ['dragenter', 'dragover'].forEach(function (evt) {
            dropzone.addEventListener(evt, function (e) {
                e.preventDefault();
                dropzone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (evt) {
            dropzone.addEventListener(evt, function (e) {
                e.preventDefault();
                dropzone.classList.remove('is-dragover');
            });
        });

        dropzone.addEventListener('drop', function (e) {
            if (e.dataTransfer && e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                form.submit();
            }
        });

        input.addEventListener('change', function () {
            if (input.files.length) form.submit();
        });
    }
})();

// Copiar caminho
function mediaCopy(text, btn) {
    var done = function () {
        if (!btn) return;
        var icon = btn.querySelector('i');
        if (!icon) return;
        var original = icon.className;
        icon.className = 'bi bi-check-lg text-success';
        window.setTimeout(function () { icon.className = original; }, 1500);
    };

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(done).catch(done);
    } else {
        var tmp = document.createElement('textarea');
        tmp.value = text;
        document.body.appendChild(tmp);
        tmp.select();
        document.execCommand('copy');
        tmp.remove();
        done();
    }
}

// Modal de edição
document.querySelectorAll('[data-media-edit]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var form = document.getElementById('mediaEditForm');
        form.setAttribute('action', '/admin/midia/update/' + btn.dataset.id);

        document.getElementById('mediaEditName').value = btn.dataset.name || '';
        document.getElementById('mediaEditAlt').value = btn.dataset.alt || '';
        document.getElementById('mediaEditCategory').value = btn.dataset.category || 'general';
        document.getElementById('mediaEditUrl').value = btn.dataset.url || '';

        var preview = document.getElementById('mediaEditPreview');
        if (btn.dataset.image === '1' || btn.dataset.video === '1') {
            preview.src = btn.dataset.thumb;
            preview.style.display = 'inline-block';
        } else {
            preview.style.display = 'none';
        }

        document.getElementById('mediaEditModal').classList.add('show');
        document.body.style.overflow = 'hidden';
    });
});

// Visualizador em tela cheia (clique na miniatura)
(function () {
    var gatilhos = Array.prototype.slice.call(document.querySelectorAll('[data-media-view]'));
    var box = document.getElementById('mediaLightbox');

    if (!gatilhos.length || !box) return;

    var imagem = document.getElementById('mlbImage');
    var player = document.getElementById('mlbVideo');
    var spinner = box.querySelector('[data-mlb-spinner]');
    var nome = document.getElementById('mlbName');
    var meta = document.getElementById('mlbMeta');
    var contador = document.getElementById('mlbCounter');
    var botaoZoom = box.querySelector('[data-mlb-zoom]');
    var botaoCopy = box.querySelector('[data-mlb-copy]');
    var ligaOpen = box.querySelector('[data-mlb-open]');
    var ligaDownload = box.querySelector('[data-mlb-download]');
    var btnPrev = box.querySelector('[data-mlb-prev]');
    var btnNext = box.querySelector('[data-mlb-next]');
    var estagio = box.querySelector('[data-mlb-stage]');
    var atual = 0;
    var overflowAnterior = '';

    function atributo(btn, nome) {
        return btn.getAttribute('data-' + nome) || '';
    }

    function mostrar(indice) {
        if (indice < 0) { indice = gatilhos.length - 1; }
        if (indice >= gatilhos.length) { indice = 0; }
        atual = indice;

        var btn = gatilhos[atual];
        var url = atributo(btn, 'url');
        var ehVideo = atributo(btn, 'video') === '1';
        var embed = atributo(btn, 'embed');

        box.classList.remove('is-zoomed');
        botaoZoom.classList.remove('is-active');
        botaoZoom.querySelector('i').className = 'bi bi-zoom-in';
        estagio.scrollTop = 0;
        estagio.scrollLeft = 0;

        nome.textContent = atributo(btn, 'name') || 'Mídia';
        ligaOpen.href = url;
        contador.textContent = 'Mídia ' + (atual + 1) + ' de ' + gatilhos.length;

        var varias = gatilhos.length > 1;
        btnPrev.hidden = !varias;
        btnNext.hidden = !varias;

        // Vídeo do YouTube: o player embutido substitui a imagem
        if (ehVideo && embed) {
            imagem.hidden = true;
            imagem.removeAttribute('src');
            spinner.hidden = true;
            botaoZoom.hidden = true;
            ligaDownload.hidden = true;

            player.hidden = false;
            player.src = embed;

            var infoVideo = [];
            if (atributo(btn, 'size')) { infoVideo.push(atributo(btn, 'size')); }
            if (atributo(btn, 'category')) { infoVideo.push(atributo(btn, 'category')); }
            if (atributo(btn, 'date')) { infoVideo.push(atributo(btn, 'date')); }

            meta.textContent = infoVideo.join(' · ');
            return;
        }

        player.hidden = true;
        player.removeAttribute('src');
        imagem.hidden = false;
        botaoZoom.hidden = false;
        ligaDownload.hidden = false;

        spinner.hidden = false;
        imagem.style.opacity = '0';
        meta.textContent = 'carregando...';

        imagem.onload = function () {
            spinner.hidden = true;
            imagem.style.opacity = '1';

            var detalhes = [imagem.naturalWidth + ' × ' + imagem.naturalHeight + ' px'];

            if (atributo(btn, 'size')) { detalhes.push(atributo(btn, 'size')); }
            if (atributo(btn, 'category')) { detalhes.push(atributo(btn, 'category')); }
            if (atributo(btn, 'date')) { detalhes.push(atributo(btn, 'date')); }

            meta.textContent = detalhes.join(' · ');
        };

        imagem.onerror = function () {
            spinner.hidden = true;
            imagem.style.opacity = '1';
            meta.textContent = 'Não foi possível carregar a imagem.';
        };

        imagem.src = url;
        imagem.alt = atributo(btn, 'alt') || atributo(btn, 'name');
    }

    function alternarZoom() {
        var ampliado = box.classList.toggle('is-zoomed');
        botaoZoom.classList.toggle('is-active', ampliado);
        botaoZoom.querySelector('i').className = ampliado ? 'bi bi-zoom-out' : 'bi bi-zoom-in';
    }

    function teclado(evento) {
        if (evento.key === 'Escape') {
            fechar();
        } else if (evento.key === 'ArrowRight') {
            mostrar(atual + 1);
        } else if (evento.key === 'ArrowLeft') {
            mostrar(atual - 1);
        } else if (evento.key === 'z' || evento.key === 'Z') {
            alternarZoom();
        }
    }

    function fechar() {
        box.hidden = true;
        imagem.removeAttribute('src');
        player.hidden = true;
        player.removeAttribute('src');
        document.body.style.overflow = overflowAnterior;
        document.removeEventListener('keydown', teclado);

        if (gatilhos[atual]) { gatilhos[atual].focus(); }
    }

    function abrir(indice) {
        overflowAnterior = document.body.style.overflow;
        box.hidden = false;
        document.body.style.overflow = 'hidden';
        mostrar(indice);
        document.addEventListener('keydown', teclado);
        box.querySelector('[data-mlb-close]').focus();
    }

    gatilhos.forEach(function (btn, i) {
        btn.addEventListener('click', function (evento) {
            evento.preventDefault();
            abrir(i);
        });
    });

    box.querySelector('[data-mlb-close]').addEventListener('click', fechar);
    btnPrev.addEventListener('click', function () { mostrar(atual - 1); });
    btnNext.addEventListener('click', function () { mostrar(atual + 1); });
    botaoZoom.addEventListener('click', alternarZoom);
    imagem.addEventListener('click', alternarZoom);
    botaoCopy.addEventListener('click', function () {
        mediaCopy(atributo(gatilhos[atual], 'url'), botaoCopy);
    });

    // Clique no fundo escuro (fora da imagem) fecha
    estagio.addEventListener('click', function (evento) {
        if (evento.target === estagio) { fechar(); }
    });
})();
</script>
<?php $view->endSection(); ?>
