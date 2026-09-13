@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Gerenciar Serviços</h4>
    <a href="/admin/servicos/novo" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Novo Serviço</a>
</div>

<div class="card">
    <div class="card-body p-0">
        @if (empty($servicos))
            <p class="text-muted text-center py-5">Nenhum serviço cadastrado.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Título</th>
                            <th>Slug</th>
                            <th>Destaque</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($servicos as $servico)
                        <tr>
                            <td>{{ $servico['id'] }}</td>
                            <td>{{ $servico['title'] }}</td>
                            <td><code>{{ $servico['slug'] }}</code></td>
                            <td>{!! $servico['highlight'] ? '<span class="badge bg-warning">Sim</span>' : '<span class="badge bg-secondary">Não</span>' !!}</td>
                            <td>{!! $servico['active'] ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>' !!}</td>
                            <td class="text-end">
                                <a href="/admin/servicos/editar/{{ $servico['id'] }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form action="/admin/servicos/excluir/{{ $servico['id'] }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir este serviço?')">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
