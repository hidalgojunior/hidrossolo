@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Gerenciar Home</h4>
</div>

@php
    $sectionMap = [];
    foreach ($sections as $s) {
        $sectionMap[$s['section']][] = $s;
    }
    $hero = $sectionMap['hero'][0] ?? null;
    $servSection = $sectionMap['servicos_section'][0] ?? null;
    $cta = $sectionMap['cta'][0] ?? null;
@endphp
<form method="POST" action="/admin/cms/home">
            @csrf
    <!-- Hero -->
    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">🎯 Hero (Banner Principal)</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Título Principal</label>
                    <input type="text" name="hero_title" class="form-control" value="{{ $hero['title'] ?? 'Soluções Completas em Poços Artesianos' }}">
                    <small class="text-muted">Use &lt;br&gt; para quebrar linha</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Subtítulo</label>
                    <input type="text" name="hero_subtitle" class="form-control" value="{{ $hero['subtitle'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Texto do Botão Principal</label>
                    <input type="text" name="hero_cta_text" class="form-control" value="{{ $hero['content'] ?? 'Solicitar Orçamento' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Link do Botão</label>
                    <input type="text" name="hero_cta_url" class="form-control" value="{{ $hero['link_url'] ?? '/contato' }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Seção Serviços -->
    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">📦 Seção de Serviços (Home)</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Título da Seção</label>
                    <input type="text" name="servicos_title" class="form-control" value="{{ $servSection['title'] ?? 'Nossos Serviços' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Subtítulo</label>
                    <input type="text" name="servicos_subtitle" class="form-control" value="{{ $servSection['subtitle'] ?? 'Soluções completas para captação de água subterrânea' }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Diferenciais -->
    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">⭐ Diferenciais</h5></div>
        <div class="card-body" id="dif-container">
            @php $difList = $sectionMap['diferenciais'] ?? []; @endphp
            @foreach ($difList as $i => $item)
            <div class="row g-3 mb-3 dif-item border-bottom pb-3">
                <div class="col-md-3">
                    <label class="form-label">Título</label>
                    <input type="text" name="section_diferenciais[{{ $i }}][title]" class="form-control" value="{{ $item['title'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ícone/Imagem URL</label>
                    <input type="text" name="section_diferenciais[{{ $i }}][image]" class="form-control" value="{{ $item['image'] }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Descrição</label>
                    <textarea name="section_diferenciais[{{ $i }}][content]" class="form-control" rows="2">{{ $item['content'] }}</textarea>
                </div>
            </div>
            @endforeach
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addDifItem()">+ Adicionar Diferencial</button>
        </div>
    </div>

    <!-- Depoimentos -->
    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">💬 Depoimentos</h5></div>
        <div class="card-body" id="dep-container">
            @php $depList = $sectionMap['depoimentos'] ?? []; @endphp
            @foreach ($depList as $i => $item)
            <div class="row g-3 mb-3 dep-item border-bottom pb-3">
                <div class="col-md-4">
                    <label class="form-label">Nome do Cliente</label>
                    <input type="text" name="section_depoimentos[{{ $i }}][title]" class="form-control" value="{{ $item['title'] }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Depoimento</label>
                    <textarea name="section_depoimentos[{{ $i }}][content]" class="form-control" rows="2">{{ $item['content'] }}</textarea>
                </div>
            </div>
            @endforeach
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addDepItem()">+ Adicionar Depoimento</button>
        </div>
    </div>

    <!-- Banners / Divulgação -->
    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">📢 Banners / Divulgação</h5></div>
        <div class="card-body" id="banner-container">
            <p class="text-muted small mb-3">Adicione banners para divulgar sites parceiros ou serviços. Aparecem entre os diferenciais e a seção de serviços.</p>
            @php $bannerList = $sectionMap['banners'] ?? []; @endphp
            @foreach ($bannerList as $i => $item)
            <div class="row g-3 mb-3 banner-item border-bottom pb-3">
                <div class="col-md-3">
                    <label class="form-label">Título</label>
                    <input type="text" name="section_banners[{{ $i }}][title]" class="form-control" value="{{ $item['title'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Subtítulo</label>
                    <input type="text" name="section_banners[{{ $i }}][subtitle]" class="form-control" value="{{ $item['subtitle'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">URL da Imagem</label>
                    <input type="text" name="section_banners[{{ $i }}][image]" class="form-control" value="{{ $item['image'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Link</label>
                    <input type="text" name="section_banners[{{ $i }}][link_url]" class="form-control" value="{{ $item['link_url'] }}" placeholder="https://...">
                </div>
            </div>
            @endforeach
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
                    <input type="text" name="cta_title" class="form-control" value="{{ $cta['title'] ?? 'Precisa de um Poço Artesiano?' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Subtítulo</label>
                    <input type="text" name="cta_subtitle" class="form-control" value="{{ $cta['subtitle'] ?? 'Solicite um orçamento sem compromisso.' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Texto do Botão</label>
                    <input type="text" name="cta_btn_text" class="form-control" value="{{ $cta['content'] ?? 'Solicitar Orçamento' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Link do Botão</label>
                    <input type="text" name="cta_btn_url" class="form-control" value="{{ $cta['link_url'] ?? '/contato' }}">
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg">💾 Salvar Home</button>
</form>

<script>
let difCount = {{ count($difList ?? []) }};
let depCount = {{ count($depList ?? []) }};
function addItem(containerId, prefix, countRef, fields) {
    let h = '<div class="row g-3 mb-3 border-bottom pb-3">';
    fields.forEach(f => {
        h += `<div class="${f.col}"><label class="form-label">${f.label}</label>`;
        if (f.type === 'textarea') {
            h += `<textarea name="section_${prefix}[${countRef}][${f.name}]" class="form-control" rows="2"></textarea>`;
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
}
function addDifItem() {
    addItem('dif-container', 'diferenciais', difCount++, [
        {col:'col-md-3', label:'Título', name:'title', type:'text'},
        {col:'col-md-3', label:'Ícone/URL', name:'image', type:'text'},
        {col:'col-md-6', label:'Descrição', name:'content', type:'textarea'}
    ]);
}
function addDepItem() {
    addItem('dep-container', 'depoimentos', depCount++, [
        {col:'col-md-4', label:'Nome do Cliente', name:'title', type:'text'},
        {col:'col-md-8', label:'Depoimento', name:'content', type:'textarea'}
    ]);
}
let bannerCount = {{ count($bannerList ?? []) }};
function addBannerItem() {
    addItem('banner-container', 'banners', bannerCount++, [
        {col:'col-md-3', label:'Título', name:'title', type:'text'},
        {col:'col-md-3', label:'Subtítulo', name:'subtitle', type:'text'},
        {col:'col-md-3', label:'URL da Imagem', name:'image', type:'text'},
        {col:'col-md-3', label:'Link', name:'link_url', type:'text'}
    ]);
}
</script>
@endsection
