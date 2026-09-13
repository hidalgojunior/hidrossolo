@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Itens do Menu: {{ $menu['name'] }}</h4>
    <a href="/admin/menus" class="btn btn-outline-secondary">Voltar</a>
</div>

<!-- Form adicionar item -->
<div class="card mb-4">
    <div class="card-header bg-white"><h5 class="mb-0">Adicionar Item</h5></div>
    <div class="card-body">
            <form method="POST" action="/admin/menus/{{ $menu['id'] }}/itens">

            @csrf
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
                        @foreach ($items as $item)
                            <option value="{{ $item['id'] }}">{{ $item['title'] }}</option>
                        @endforeach
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
            @foreach ($pages as $p)
                <code class="d-block mb-1" style="cursor:pointer" onclick="navigator.clipboard.writeText('/{{ $p['slug'] }}')" title="Clique para copiar">/{{ $p['slug'] }} — {{ $p['title'] }}</code>
            @endforeach
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card"><div class="card-body">
            <h6>Serviços</h6>
            @foreach ($servicos as $s)
                <code class="d-block mb-1" style="cursor:pointer" onclick="navigator.clipboard.writeText('/servicos/{{ $s['slug'] }}')" title="Clique para copiar">/servicos/{{ $s['slug'] }} — {{ $s['title'] }}</code>
            @endforeach
        </div></div>
    </div>
</div>

<!-- Itens atuais -->
<div class="card">
    <div class="card-body p-0">
        @if (empty($items))
            <p class="text-muted text-center py-4">Nenhum item neste menu.</p>
        @else
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
                    @foreach ($items as $item)
                    <tr ondblclick="editItem({{ $item['id'] }}, '{{ e($item['title']) }}', '{{ e($item['url']) }}', '{{ $item['parent_id'] }}', {{ $item['sort_order'] }})" title="Duplo clique para editar">
                        <td>
                            @if ($item['parent_id']) ↳ @endif
                            <strong>{{ $item['title'] }}</strong>
                        </td>
                        <td><code>{{ $item['url'] }}</code></td>
                        <td>{{ $item['parent_id'] ? collect($items)->firstWhere('id', $item['parent_id'])['title'] ?? '—' : 'Principal' }}</td>
                        <td>{{ $item['sort_order'] }}</td>
                        <td>{!! $item['active'] ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-secondary">Não</span>' !!}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editItem({{ $item['id'] }}, '{{ e($item['title']) }}', '{{ e($item['url']) }}', '{{ $item['parent_id'] }}', {{ $item['sort_order'] }})" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="/admin/menus/{{ $menu['id'] }}/itens/{{ $item['id'] }}/delete" style="display:inline" onsubmit="return confirm('Remover este item?')">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger" title="Remover"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
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
            fetch('/admin/menus/{{ $menu['id'] }}/reorder', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ $csrf_token }}'},
                body: JSON.stringify({order: ids})
            });
        }
    });
    tbody.querySelectorAll('tr').forEach((tr, i) => { tr.dataset.id = i; tr.style.cursor = 'grab'; });
}
</script>
@endsection

<!-- Modal Editar Item -->
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
    <form method="POST" id="editItemForm">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Editar Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="_method" value="PUT">
            <div class="mb-3"><label class="form-label">Título</label><input type="text" name="title" id="editTitle" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">URL</label><input type="text" name="url" id="editUrl" class="form-control" required></div>
            <div class="mb-3">
                <label class="form-label">Item Pai</label>
                <select name="parent_id" id="editParent" class="form-select">
                    <option value="">Nenhum</option>
                    @foreach ($items as $item)
                        <option value="{{ $item['id'] }}">{{ $item['title'] }}</option>
                    @endforeach
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
    document.getElementById('editItemForm').action = '/admin/menus/{{ $menu['id'] }}/itens/' + id;
    document.getElementById('editTitle').value = title;
    document.getElementById('editUrl').value = url;
    document.getElementById('editParent').value = parentId || '';
    document.getElementById('editOrder').value = sortOrder;
    new bootstrap.Modal('#editItemModal').show();
}
</script>
