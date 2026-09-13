<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Gerenciar Home</h4>
</div>

<!-- Blocos da Home: exibir/ocultar e reordenar -->
<div class="card mb-4">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-0">🧱 Blocos da Home</h5>
            <small class="text-muted">Marque para exibir, desmarque para remover da página e use as setas para mudar a ordem.</small>
        </div>
        <form method="POST" action="/admin/cms/home/blocos/restaurar"
              onsubmit="return confirm('Restaurar a ordem e a exibição padrão de todos os blocos?')">
            <?= csrf_field() ?>
            <button class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurar padrão
            </button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:70px">Ordem</th>
                        <th>Bloco</th>
                        <th style="width:110px" class="text-center">Exibir</th>
                        <th style="width:120px" class="text-end">Mover</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($blocos ?? []) as $indiceBloco => $bloco) { ?>
                        <?php $chave = (string) $bloco['block']; ?>
                        <tr class="<?= empty($bloco['enabled']) ? 'opacity-50' : '' ?>">
                            <td><span class="badge bg-secondary"><?= e((int) $bloco['sort_order']) ?></span></td>
                            <td>
                                <i class="bi <?= e($catalogoBlocos[$chave]['icon'] ?? 'bi-square') ?> me-1 text-primary"></i>
                                <strong><?= e($catalogoBlocos[$chave]['label'] ?? $chave) ?></strong>
                                <?php if (!empty($catalogoBlocos[$chave]['hint'])) { ?>
                                    <div class="small text-muted"><?= e($catalogoBlocos[$chave]['hint']) ?></div>
                                <?php } ?>
                            </td>
                            <td class="text-center">
                                <form method="POST" action="/admin/cms/home/blocos/<?= e($chave) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="enabled" value="<?= empty($bloco['enabled']) ? '1' : '0' ?>">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                               id="bloco-<?= e($chave) ?>"
                                               <?= empty($bloco['enabled']) ? '' : 'checked' ?>
                                               onchange="this.form.submit()"
                                               title="<?= empty($bloco['enabled']) ? 'Exibir na Home' : 'Remover da Home' ?>">
                                    </div>
                                </form>
                            </td>
                            <td class="text-end text-nowrap">
                                <form method="POST" action="/admin/cms/home/blocos/<?= e($chave) ?>/mover" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="direcao" value="cima">
                                    <button class="btn btn-sm btn-outline-secondary" title="Mover para cima" <?= $indiceBloco === 0 ? 'disabled' : '' ?>>
                                        <i class="bi bi-arrow-up"></i>
                                    </button>
                                </form>
                                <form method="POST" action="/admin/cms/home/blocos/<?= e($chave) ?>/mover" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="direcao" value="baixo">
                                    <button class="btn btn-sm btn-outline-secondary" title="Mover para baixo" <?= $indiceBloco === count($blocos) - 1 ? 'disabled' : '' ?>>
                                        <i class="bi bi-arrow-down"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
    $sectionMap = [];
    foreach ($sections as $s) {
        $sectionMap[$s['section']][] = $s;
    }
    $hero = $sectionMap['hero'][0] ?? null;
    $servSection = $sectionMap['servicos_section'][0] ?? null;
    $cta = $sectionMap['cta'][0] ?? null;
?>
<form method="POST" action="/admin/cms/home">
            <?= csrf_field() ?>
    <!-- Hero -->
    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">🎯 Hero (Banner Principal)</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Título Principal</label>
                    <input type="text" name="hero_title" class="form-control" value="<?= e($hero['title'] ?? 'Soluções Completas em Poços Artesianos') ?>">
                    <small class="text-muted">Use &lt;br&gt; para quebrar linha</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Subtítulo</label>
                    <input type="text" name="hero_subtitle" class="form-control" value="<?= e($hero['subtitle'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Texto do Botão Principal</label>
                    <input type="text" name="hero_cta_text" class="form-control" value="<?= e($hero['content'] ?? 'Solicitar Orçamento') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Link do Botão</label>
                    <input type="text" name="hero_cta_url" class="form-control" value="<?= e($hero['link_url'] ?? '/contato') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">📊 Estatísticas</h5>
            <small class="text-muted">Números exibidos na faixa logo abaixo do banner principal.</small>
        </div>
        <div class="card-body" id="stats-container">
            <?php $statsList = $sectionMap['estatisticas'] ?? []; ?>
            <?php foreach ($statsList as $i => $item) { ?>
            <div class="row g-3 mb-3 stats-item border-bottom pb-3">
                <div class="col-md-3">
                    <label class="form-label">Valor</label>
                    <input type="text" name="section_estatisticas[<?= e($i) ?>][title]" class="form-control" value="<?= e($item['title']) ?>" placeholder="+20">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Rótulo</label>
                    <input type="text" name="section_estatisticas[<?= e($i) ?>][subtitle]" class="form-control" value="<?= e($item['subtitle']) ?>" placeholder="Anos de experiência">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ícone</label>
                    <input type="text" name="section_estatisticas[<?= e($i) ?>][content]" class="form-control" value="<?= e($item['content']) ?>" list="iconesStats" placeholder="calendar">
                    <div class="form-text">Nome do ícone do Bootstrap Icons, sem o “bi-”.</div>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.stats-item').remove()" title="Remover">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <?php } ?>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addStatItem()">+ Adicionar Estatística</button>
        </div>
    </div>

    <datalist id="iconesStats">
        <option value="calendar"></option>
        <option value="droplet"></option>
        <option value="shield-check"></option>
        <option value="geo-alt"></option>
        <option value="people"></option>
        <option value="truck"></option>
        <option value="award"></option>
        <option value="graph-up"></option>
        <option value="check2-circle"></option>
        <option value="star"></option>
        <option value="tools"></option>
        <option value="water"></option>
    </datalist>

    <!-- Seção Serviços -->
    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">📦 Seção de Serviços (Home)</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Título da Seção</label>
                    <input type="text" name="servicos_title" class="form-control" value="<?= e($servSection['title'] ?? 'Nossos Serviços') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Subtítulo</label>
                    <input type="text" name="servicos_subtitle" class="form-control" value="<?= e($servSection['subtitle'] ?? 'Soluções completas para captação de água subterrânea') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Diferenciais -->
    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">⭐ Diferenciais</h5></div>
        <div class="card-body" id="dif-container">
            <?php $difList = $sectionMap['diferenciais'] ?? []; ?>
            <?php foreach ($difList as $i => $item) { ?>
            <div class="row g-3 mb-3 dif-item border-bottom pb-3">
                <div class="col-md-3">
                    <label class="form-label">Título</label>
                    <input type="text" name="section_diferenciais[<?= e($i) ?>][title]" class="form-control" value="<?= e($item['title']) ?>">
                </div>
                <div class="col-md-3">
                    <?= $view->partial('admin.components.media-field', [
                        'name' => "section_diferenciais[{$i}][image]",
                        'label' => 'Ícone / Imagem',
                        'value' => $item['image'],
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Descrição</label>
                    <textarea name="section_diferenciais[<?= e($i) ?>][content]" class="form-control" rows="2"><?= e($item['content']) ?></textarea>
                </div>
            </div>
            <?php } ?>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addDifItem()">+ Adicionar Diferencial</button>
        </div>
    </div>

    <!-- Depoimentos -->
    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">💬 Depoimentos</h5></div>
        <div class="card-body" id="dep-container">
            <?php $depList = $sectionMap['depoimentos'] ?? []; ?>
            <?php foreach ($depList as $i => $item) { ?>
            <div class="row g-3 mb-3 dep-item border-bottom pb-3">
                <div class="col-md-4">
                    <label class="form-label">Nome do Cliente</label>
                    <input type="text" name="section_depoimentos[<?= e($i) ?>][title]" class="form-control" value="<?= e($item['title']) ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Depoimento</label>
                    <textarea name="section_depoimentos[<?= e($i) ?>][content]" class="form-control" rows="2"><?= e($item['content']) ?></textarea>
                </div>
            </div>
            <?php } ?>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addDepItem()">+ Adicionar Depoimento</button>
        </div>
    </div>

    <!-- Banners / Divulgação -->
    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">📢 Banners / Divulgação</h5></div>
        <div class="card-body" id="banner-container">
            <p class="text-muted small mb-3">Adicione banners para divulgar sites parceiros ou serviços. Aparecem entre os diferenciais e a seção de serviços.</p>
            <?php $bannerList = $sectionMap['banners'] ?? []; ?>
            <?php foreach ($bannerList as $i => $item) { ?>
            <div class="row g-3 mb-3 banner-item border-bottom pb-3">
                <div class="col-md-3">
                    <label class="form-label">Título</label>
                    <input type="text" name="section_banners[<?= e($i) ?>][title]" class="form-control" value="<?= e($item['title']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Subtítulo</label>
                    <input type="text" name="section_banners[<?= e($i) ?>][subtitle]" class="form-control" value="<?= e($item['subtitle']) ?>">
                </div>
                <div class="col-md-3">
                    <?= $view->partial('admin.components.media-field', [
                        'name' => "section_banners[{$i}][image]",
                        'label' => 'Imagem do Banner',
                        'value' => $item['image'],
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Link</label>
                    <input type="text" name="section_banners[<?= e($i) ?>][link_url]" class="form-control" value="<?= e($item['link_url']) ?>" placeholder="https://...">
                </div>
            </div>
            <?php } ?>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addBannerItem()">+ Adicionar Banner</button>
        </div>
    </div>

    <!-- CTA -->
    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">📣 CTA (Call to Action)</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Título do CTA</label>
                    <input type="text" name="cta_title" class="form-control" value="<?= e($cta['title'] ?? 'Precisa de um Poço Artesiano?') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Subtítulo</label>
                    <input type="text" name="cta_subtitle" class="form-control" value="<?= e($cta['subtitle'] ?? 'Solicite um orçamento sem compromisso.') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Texto do Botão</label>
                    <input type="text" name="cta_btn_text" class="form-control" value="<?= e($cta['content'] ?? 'Solicitar Orçamento') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Link do Botão</label>
                    <input type="text" name="cta_btn_url" class="form-control" value="<?= e($cta['link_url'] ?? '/contato') ?>">
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg">💾 Salvar Home</button>
</form>

<!-- Seções livres: incluir, editar, reordenar e excluir -->
<div class="card mt-4">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-0">🧩 Seções da Home</h5>
            <small class="text-muted">Blocos livres exibidos no fim da página, logo antes do bloco de contato.</small>
        </div>
        <button type="button" class="btn btn-primary btn-sm" onclick="abrirSecao(null)">
            <i class="bi bi-plus-lg me-1"></i>Adicionar seção
        </button>
    </div>
    <div class="card-body p-0">
        <?php if (empty($secoes)) { ?>
            <p class="text-muted text-center py-4 mb-0">
                Nenhuma seção adicional. Clique em <strong>Adicionar seção</strong> para criar blocos como
                “Onde atendemos”, “Como funciona”, “Perguntas frequentes” etc.
            </p>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:70px">Ordem</th>
                            <th>Seção</th>
                            <th style="width:200px" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($secoes as $indice => $secao) { ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= e((int) $secao['sort_order']) ?></span></td>
                                <td>
                                    <div class="d-flex align-items-start gap-2">
                                        <?php if (!empty($secao['image'])) { ?>
                                            <img src="<?= e($secao['image']) ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:6px">
                                        <?php } ?>
                                        <div style="min-width:0">
                                            <strong><?= e($secao['title']) ?></strong>
                                            <?php if (!empty($secao['subtitle'])) { ?>
                                                <div class="small text-muted"><?= e(str_limit($secao['subtitle'], 110)) ?></div>
                                            <?php } ?>
                                            <?php if (!empty($secao['link_url'])) { ?>
                                                <div class="small text-muted">
                                                    <i class="bi bi-link-45deg"></i>
                                                    <?= e($secao['link_text'] ?: 'Saiba mais') ?> → <?= e($secao['link_url']) ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end text-nowrap">
                                    <form method="POST" action="/admin/cms/home/secoes/<?= e($secao['id']) ?>/mover" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="direcao" value="cima">
                                        <button class="btn btn-sm btn-outline-secondary" title="Mover para cima" <?= $indice === 0 ? 'disabled' : '' ?>>
                                            <i class="bi bi-arrow-up"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="/admin/cms/home/secoes/<?= e($secao['id']) ?>/mover" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="direcao" value="baixo">
                                        <button class="btn btn-sm btn-outline-secondary" title="Mover para baixo" <?= $indice === count($secoes) - 1 ? 'disabled' : '' ?>>
                                            <i class="bi bi-arrow-down"></i>
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-secao='<?= json_attr([
                                                'id' => (int) $secao['id'],
                                                'title' => (string) $secao['title'],
                                                'subtitle' => (string) ($secao['subtitle'] ?? ''),
                                                'content' => (string) ($secao['content'] ?? ''),
                                                'image' => (string) ($secao['image'] ?? ''),
                                                'link_url' => (string) ($secao['link_url'] ?? ''),
                                                'link_text' => (string) ($secao['link_text'] ?? ''),
                                                'sort_order' => (int) $secao['sort_order'],
                                            ]) ?>'
                                            onclick="abrirSecao(this)">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <form method="POST" action="/admin/cms/home/secoes/<?= e($secao['id']) ?>/excluir" class="d-inline"
                                          onsubmit="return confirm('Remover a seção “<?= e($secao['title']) ?>” da Home?')">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-danger" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</div>

<!-- Modal: seção da Home -->
<div class="modal" id="secaoModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="/admin/cms/home/secoes" id="secaoForm" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title" id="secaoTitulo"><i class="bi bi-plus-square me-2"></i>Nova seção</h5>
                <button type="button" class="btn-close" onclick="fecharSecao()" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Título *</label>
                        <input type="text" name="title" id="secaoTitle" class="form-control" required maxlength="255" placeholder="Ex.: Onde atendemos">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ordem</label>
                        <input type="number" name="sort_order" id="secaoOrdem" class="form-control" value="0">
                        <div class="form-text">Use as setas na lista para reordenar.</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Subtítulo</label>
                        <input type="text" name="subtitle" id="secaoSubtitle" class="form-control" maxlength="255">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Conteúdo</label>
                        <textarea name="content" id="secaoContent" class="form-control" rows="5"
                                  placeholder="Texto da seção. Aceita HTML simples (listas, negrito, parágrafos)."></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Imagem (opcional)</label>
                        <div class="media-field" data-media-field data-media-type="image" id="secaoMediaField">
                            <div class="media-field-body">
                                <div class="media-field-preview" data-media-preview><i class="bi bi-image"></i></div>
                                <div class="media-field-main">
                                    <input type="text" class="form-control form-control-sm" name="image" id="secaoImage" data-media-input autocomplete="off">
                                    <div class="media-field-actions">
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-media-open><i class="bi bi-images me-1"></i> Biblioteca</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-media-upload><i class="bi bi-cloud-upload me-1"></i> Enviar</button>
                                        <button type="button" class="btn btn-outline-danger btn-sm" data-media-clear><i class="bi bi-x-lg"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Texto do link</label>
                        <input type="text" name="link_text" id="secaoLinkText" class="form-control" maxlength="255" placeholder="Ex.: Fale conosco">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">URL do link</label>
                        <input type="text" name="link_url" id="secaoLinkUrl" class="form-control" maxlength="500" placeholder="/orcamento">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" onclick="fecharSecao()">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg me-1"></i>Salvar seção</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirSecao(botao) {
    var form = document.getElementById('secaoForm');
    var modal = document.getElementById('secaoModal');
    var dados = botao && botao.dataset.secao ? JSON.parse(botao.dataset.secao) : null;

    if (dados) {
        form.setAttribute('action', '/admin/cms/home/secoes/' + dados.id);
        document.getElementById('secaoTitulo').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Editar seção';
    } else {
        form.setAttribute('action', '/admin/cms/home/secoes');
        document.getElementById('secaoTitulo').innerHTML = '<i class="bi bi-plus-square me-2"></i>Nova seção';
    }

    document.getElementById('secaoTitle').value = dados ? dados.title : '';
    document.getElementById('secaoSubtitle').value = dados ? dados.subtitle : '';
    document.getElementById('secaoContent').value = dados ? dados.content : '';
    document.getElementById('secaoImage').value = dados ? dados.image : '';
    document.getElementById('secaoLinkText').value = dados ? dados.link_text : '';
    document.getElementById('secaoLinkUrl').value = dados ? dados.link_url : '';
    document.getElementById('secaoOrdem').value = dados ? dados.sort_order : 0;

    var preview = document.querySelector('#secaoMediaField [data-media-preview]');
    if (preview) {
        preview.innerHTML = dados && dados.image
            ? '<img src="' + dados.image + '" alt="">'
            : '<i class="bi bi-image"></i>';
    }

    if (window.MediaPicker && typeof window.MediaPicker.initFields === 'function') {
        window.MediaPicker.initFields(document.getElementById('secaoMediaField'));
    }

    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function fecharSecao() {
    document.getElementById('secaoModal').classList.remove('show');
    document.body.style.overflow = '';
}

document.getElementById('secaoModal').addEventListener('click', function (evento) {
    if (evento.target === this) { fecharSecao(); }
});

document.addEventListener('keydown', function (evento) {
    if (evento.key === 'Escape' && document.getElementById('secaoModal').classList.contains('show')) {
        fecharSecao();
    }
});
</script>

<script>
let statsCount = <?= e(count($statsList ?? [])) ?>;
function addStatItem() {
    var h = '<div class="row g-3 mb-3 stats-item border-bottom pb-3">'
        + '<div class="col-md-3"><label class="form-label">Valor</label>'
        + '<input type="text" name="section_estatisticas[' + statsCount + '][title]" class="form-control" placeholder="+20"></div>'
        + '<div class="col-md-4"><label class="form-label">Rótulo</label>'
        + '<input type="text" name="section_estatisticas[' + statsCount + '][subtitle]" class="form-control" placeholder="Anos de experiência"></div>'
        + '<div class="col-md-4"><label class="form-label">Ícone</label>'
        + '<input type="text" name="section_estatisticas[' + statsCount + '][content]" class="form-control" list="iconesStats" placeholder="calendar">'
        + '<div class="form-text">Nome do ícone do Bootstrap Icons, sem o “bi-”.</div></div>'
        + '<div class="col-md-1 d-flex align-items-end">'
        + '<button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest(\'.stats-item\').remove()" title="Remover"><i class="bi bi-trash"></i></button></div>'
        + '</div>';

    var container = document.getElementById('stats-container');
    var t = document.createElement('template');
    t.innerHTML = h.trim();
    container.insertBefore(t.content.firstChild, container.lastElementChild);
    statsCount++;
}

let difCount = <?= e(count($difList ?? [])) ?>;
let depCount = <?= e(count($depList ?? [])) ?>;
function addItem(containerId, prefix, countRef, fields) {
    let h = '<div class="row g-3 mb-3 border-bottom pb-3">';
    fields.forEach(f => {
        h += `<div class="${f.col}"><label class="form-label">${f.label}</label>`;
        if (f.type === 'textarea') {
            h += `<textarea name="section_${prefix}[${countRef}][${f.name}]" class="form-control" rows="2"></textarea>`;
        } else if (f.type === 'media') {
            h += '<div class="media-field" data-media-field data-media-type="image">'
               + '<div class="media-field-body">'
               + '<div class="media-field-preview" data-media-preview><i class="bi bi-image"></i></div>'
               + '<div class="media-field-main">'
               + `<input type="text" class="form-control form-control-sm" name="section_${prefix}[${countRef}][${f.name}]" data-media-input autocomplete="off">`
               + '<div class="media-field-actions">'
               + '<button type="button" class="btn btn-outline-primary btn-sm" data-media-open><i class="bi bi-images me-1"></i> Biblioteca</button>'
               + '<button type="button" class="btn btn-outline-secondary btn-sm" data-media-upload><i class="bi bi-cloud-upload me-1"></i> Enviar</button>'
               + '<button type="button" class="btn btn-outline-danger btn-sm" data-media-clear><i class="bi bi-x-lg"></i></button>'
               + '</div></div></div></div>';
        } else {
            h += `<input type="text" name="section_${prefix}[${countRef}][${f.name}]" class="form-control">`;
        }
        h += '</div>';
    });
    h += '</div>';
    let t = document.createElement('template');
    t.innerHTML = h.trim();
    let container = document.getElementById(containerId);
    container.insertBefore(t.content.firstChild, container.lastElementChild);

    if (window.MediaPicker) window.MediaPicker.initFields(container);
}
function addDifItem() {
    addItem('dif-container', 'diferenciais', difCount++, [
        {col:'col-md-3', label:'Título', name:'title', type:'text'},
        {col:'col-md-3', label:'Ícone/Imagem', name:'image', type:'media'},
        {col:'col-md-6', label:'Descrição', name:'content', type:'textarea'}
    ]);
}
function addDepItem() {
    addItem('dep-container', 'depoimentos', depCount++, [
        {col:'col-md-4', label:'Nome do Cliente', name:'title', type:'text'},
        {col:'col-md-8', label:'Depoimento', name:'content', type:'textarea'}
    ]);
}
let bannerCount = <?= e(count($bannerList ?? [])) ?>;
function addBannerItem() {
    addItem('banner-container', 'banners', bannerCount++, [
        {col:'col-md-3', label:'Título', name:'title', type:'text'},
        {col:'col-md-3', label:'Subtítulo', name:'subtitle', type:'text'},
        {col:'col-md-3', label:'Imagem', name:'image', type:'media'},
        {col:'col-md-3', label:'Link', name:'link_url', type:'text'}
    ]);
}
</script>
<?php $view->endSection(); ?>
