@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Biblioteca de Mídias</h4>
    <div>
        <form method="POST" action="/admin/midia/scan" style="display:inline">
            @csrf
            <button type="submit" class="btn btn-outline-primary btn-sm me-2" title="Escanear imagens existentes no servidor">
                <i class="bi bi-search me-1"></i>Escanear Imagens
            </button>
        </form>
        <small class="text-muted">{{ $total }} arquivo(s)</small>
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
    <a href="?categoria=all" class="btn btn-sm {{ $categoria == 'all' ? 'btn-primary' : 'btn-outline-secondary' }} me-1">Todas</a>
    @foreach ($categorias as $cat)
        <a href="?categoria={{ $cat['category'] }}" class="btn btn-sm {{ $categoria == $cat['category'] ? 'btn-primary' : 'btn-outline-secondary' }} me-1">
            {{ $cat['category'] }}
        </a>
    @endforeach
</div>

<!-- Grid de mídias -->
<div class="row g-3">
    @if (empty($midias))
        <div class="col-12 text-center py-5 text-muted">Nenhuma mídia encontrada.</div>
    @else
        @foreach ($midias as $m)
        <div class="col-md-3 col-lg-2">
            <div class="card h-100">
                @if (str_starts_with($m['mime_type'], 'image/'))
                    <img src="{{ $m['thumbnail_path'] ?? $m['file_path'] }}" class="card-img-top" style="height:120px;object-fit:cover" alt="{{ $m['original_name'] }}">
                @else
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:120px">
                        <i class="bi bi-file-earmark fs-1 text-muted"></i>
                    </div>
                @endif
                <div class="card-body p-2">
                    <small class="text-truncate d-block" title="{{ $m['original_name'] }}">{{ $m['original_name'] }}</small>
                    <small class="text-muted">{{ number_format($m['file_size'] / 1024, 1) }} KB</small>
                    <div class="input-group input-group-sm mt-1">
                        <input type="text" class="form-control form-control-sm font-monospace" value="{{ $m['file_path'] }}" readonly style="font-size:0.65rem" id="url-{{ $m['id'] }}">
                        <button class="btn btn-outline-secondary btn-sm" onclick="copyUrl({{ $m['id'] }})" title="Copiar URL">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <form method="POST" action="/admin/midia/delete/{{ $m['id'] }}" class="mt-1" onsubmit="return confirm('Excluir esta mídia permanentemente?')">
                        @csrf
                        <button class="btn btn-outline-danger btn-sm w-100" title="Excluir"><i class="bi bi-trash me-1"></i>Excluir</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    @endif
</div>

@if ($total > $perPage)
<nav class="mt-4 d-flex justify-content-center">
    @php $totalPages = ceil($total / $perPage) @endphp
    <ul class="pagination">
        @for ($i = 1; $i <= $totalPages; $i++)
            <li class="page-item {{ $i == $pagina ? 'active' : '' }}">
                <a class="page-link" href="?categoria={{ $categoria }}&page={{ $i }}">{{ $i }}</a>
            </li>
        @endfor
    </ul>
</nav>
@endif
@endsection

@section('scripts')
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
@endsection
