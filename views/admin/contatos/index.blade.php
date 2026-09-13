@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Mensagens Recebidas</h4>
    <div>
        <a href="?status=all" class="btn btn-sm {{ $status == 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">Todas ({{ $total }})</a>
        <a href="?status=new" class="btn btn-sm {{ $status == 'new' ? 'btn-primary' : 'btn-outline-secondary' }}">Novas ({{ $novos }})</a>
        <a href="?status=read" class="btn btn-sm {{ $status == 'read' ? 'btn-primary' : 'btn-outline-secondary' }}">Lidas</a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        @if (empty($contatos))
            <p class="text-muted text-center py-5">Nenhuma mensagem encontrada.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Mensagem</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($contatos as $c)
                        <tr class="{{ $c['status'] == 'new' ? 'table-active fw-semibold' : '' }}">
                            <td>{{ $c['name'] }}</td>
                            <td>{{ $c['email'] }}</td>
                            <td>{{ $c['phone'] }}</td>
                            <td>{{ Str::limit($c['message'], 50) }}</td>
                            <td>{{ date('d/m/Y H:i', strtotime($c['created_at'])) }}</td>
                            <td>{!! $c['status'] == 'new' ? '<span class="badge bg-danger">Nova</span>' : '<span class="badge bg-secondary">Lida</span>' !!}</td>
                            <td><a href="/admin/contatos/{{ $c['id'] }}" class="btn btn-sm btn-outline-primary">Ver</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
