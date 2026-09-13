@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Gerenciar Menus</h4>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="/admin/menus" method="POST">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Nome do Menu</label>
                    <input type="text" name="name" class="form-control" required placeholder="Ex: Menu Principal">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Localização</label>
                    <input type="text" name="location" class="form-control" required placeholder="Ex: header, footer">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Criar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    @foreach ($menus as $menu)
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between">
                <h5 class="mb-0">{{ $menu['name'] }}</h5>
                <span class="badge bg-secondary">{{ $menu['location'] }}</span>
            </div>
            <div class="card-body">
                <p class="text-muted">Gerencie os itens deste menu.</p>
                <a href="/admin/menus/{{ $menu['id'] }}" class="btn btn-sm btn-outline-primary">Editar Itens</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
