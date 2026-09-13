<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Itens do Menu: <?= e($menu['name']) ?></h4>
    <a href="/admin/menus" class="btn btn-outline-secondary">Voltar</a>
</div>

<!-- Form adicionar item -->
<div class="card mb-4">
    <div class="card-header bg-white"><h5 class="mb-0">Adicionar Item</h5></div>
    <div class="card-body">
            <form method="POST" action="/admin/menus/<?= e($menu['id']) ?>/itens">

            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Título *</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">URL *</label>
                    <input type="text" name="url" class="form-control" required placeholder="/pagina ou https://...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Item Pai (submenu)</label>
                    <select name="parent_id" class="form-select">
                        <option value="">Nenhum (principal)</option>
                        <?php foreach ($items as $item) { ?>
                            <option value="<?= e($item['id']) ?>"><?= e($item['title']) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Ordem</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Adicionar Item</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Links sugeridos -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card"><div class="card-body">
            <h6>Páginas</h6>
            <?php foreach ($pages as $p) { ?>
                <code class="d-block mb-1" style="cursor:pointer" onclick="navigator.clipboard.writeText('/<?= e($p['slug']) ?>')" title="Clique para copiar">/<?= e($p['slug']) ?> — <?= e($p['title']) ?></code>
            <?php } ?>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card"><div class="card-body">
            <h6>Serviços</h6>
            <?php foreach ($servicos as $s) { ?>
                <code class="d-block mb-1" style="cursor:pointer" onclick="navigator.clipboard.writeText('/servicos/<?= e($s['slug']) ?>')" title="Clique para copiar">/servicos/<?= e($s['slug']) ?> — <?= e($s['title']) ?></code>
            <?php } ?>
        </div></div>
    </div>
</div>

<!-- Itens atuais -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($items)) { ?>
            <p class="text-muted text-center py-4">Nenhum item neste menu.</p>
        <?php } else { ?>
            <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Título</th>
                        <th>URL</th>
                        <th>Submenu de</th>
                        <th style="width:80px">Ordem</th>
                        <th style="width:80px">Ativo</th>
                        <th style="width:100px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item) { ?>
                    <tr ondblclick="editItem(<?= e($item['id']) ?>, '<?= e(e($item['title'])) ?>', '<?= e(e($item['url'])) ?>', '<?= e($item['parent_id']) ?>', <?= e($item['sort_order']) ?>)" title="Duplo clique para editar">
                        <td>
                            <?php if ($item['parent_id']) { ?> ↳ <?php } ?>
                            <strong><?= e($item['title']) ?></strong>
                        </td>
                        <td><code><?= e($item['url']) ?></code></td>
                        <td><?= e($item['parent_id'] ? collect($items)->firstWhere('id', $item['parent_id'])['title'] ?? '—' : 'Principal') ?></td>
                        <td><?= e($item['sort_order']) ?></td>
                        <td><?= $item['active'] ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-secondary">Não</span>' ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editItem(<?= e($item['id']) ?>, '<?= e(e($item['title'])) ?>', '<?= e(e($item['url'])) ?>', '<?= e($item['parent_id']) ?>', <?= e($item['sort_order']) ?>)" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="/admin/menus/<?= e($menu['id']) ?>/itens/<?= e($item['id']) ?>/delete" style="display:inline" onsubmit="return confirm('Remover este item?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger" title="Remover"><i class="bi bi-trash"></i></button>
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

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
// Drag & drop para reordenar itens do menu
const tbody = document.querySelector('table tbody');
if (tbody) {
    new Sortable(tbody, {
        handle: 'tr',
        animation: 150,
        onEnd: function() {
            const ids = [...tbody.querySelectorAll('tr')].map(tr => tr.dataset.id).filter(Boolean);
            fetch('/admin/menus/<?= e($menu['id']) ?>/reorder', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?= e($csrf_token) ?>'},
                body: JSON.stringify({order: ids})
            });
        }
    });
    tbody.querySelectorAll('tr').forEach((tr, i) => { tr.dataset.id = i; tr.style.cursor = 'grab'; });
}
</script>
<?php $view->endSection(); ?>

<!-- Modal Editar Item -->
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
    <form method="POST" id="editItemForm">
        <?= csrf_field() ?>
        <div class="modal-header"><h5 class="modal-title">Editar Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="_method" value="PUT">
            <div class="mb-3"><label class="form-label">Título</label><input type="text" name="title" id="editTitle" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">URL</label><input type="text" name="url" id="editUrl" class="form-control" required></div>
            <div class="mb-3">
                <label class="form-label">Item Pai</label>
                <select name="parent_id" id="editParent" class="form-select">
                    <option value="">Nenhum</option>
                    <?php foreach ($items as $item) { ?>
                        <option value="<?= e($item['id']) ?>"><?= e($item['title']) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="mb-3"><label class="form-label">Ordem</label><input type="number" name="sort_order" id="editOrder" class="form-control"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
    </div></div>
</div>

<script>
function editItem(id, title, url, parentId, sortOrder) {
    document.getElementById('editItemForm').action = '/admin/menus/<?= e($menu['id']) ?>/itens/' + id;
    document.getElementById('editTitle').value = title;
    document.getElementById('editUrl').value = url;
    document.getElementById('editParent').value = parentId || '';
    document.getElementById('editOrder').value = sortOrder;
    document.querySelector('#editItemModal').classList.add('show');
}
</script>
