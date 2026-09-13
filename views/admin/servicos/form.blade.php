@extends('layouts.admin')

@section('content')
<h4 class="mb-4">{{ $servico ? 'Editar Serviço' : 'Novo Serviço' }}</h4>

<div class="card">
    <div class="card-body">
            <form method="POST" action="{{ $servico ? '/admin/servicos/editar/' . $servico['id'] : '/admin/servicos/novo' }}">

            @csrf
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Título *</label>
                    <input type="text" name="title" class="form-control" required value="{{ $servico['title'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ícone (Bootstrap Icons)</label>
                    <input type="text" name="icon" class="form-control" placeholder="Ex: tools, water, gear" value="{{ $servico['icon'] ?? '' }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Descrição Curta *</label>
                    <textarea name="description" class="form-control" rows="3" required>{{ $servico['description'] ?? '' }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Conteúdo Completo</label>
                    <textarea name="content" class="form-control editor" rows="10">{{ $servico['content'] ?? '' }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Imagem de Destaque (URL)</label>
                    <input type="text" name="featured_image" class="form-control" value="{{ $servico['featured_image'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Meta Title (SEO)</label>
                    <input type="text" name="meta_title" class="form-control" value="{{ $servico['meta_title'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Meta Description (SEO)</label>
                    <input type="text" name="meta_description" class="form-control" value="{{ $servico['meta_description'] ?? '' }}">
                </div>
                <div class="col-md-6 d-flex align-items-end gap-4 pb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="active" id="active" {{ ($servico['active'] ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="active">Ativo</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="highlight" id="highlight" {{ ($servico['highlight'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="highlight">Destaque na Home</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="/admin/servicos" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
