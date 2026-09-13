@extends('layouts.admin')

@section('content')
<h4 class="mb-4">📋 Logs de Auditoria</h4>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Data</th><th>Usuário</th><th>Ação</th><th>Entidade</th><th>ID</th></tr></thead>
                <tbody>
                    @forelse ($logs as $log)
                    <tr>
                        <td><small>{{ date('d/m/Y H:i', strtotime($log['created_at'])) }}</small></td>
                        <td>{{ $log['user_name'] ?? 'Sistema' }}</td>
                        <td><code>{{ $log['action'] }}</code></td>
                        <td>{{ $log['entity_type'] ?? '—' }}</td>
                        <td>{{ $log['entity_id'] ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">Nenhum registro de auditoria.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if ($total > $limit)
<nav class="mt-3 d-flex justify-content-center"><ul class="pagination">
    @for ($i = 1; $i <= ceil($total / $limit); $i++)
        <li class="page-item {{ $i == $page ? 'active' : '' }}"><a class="page-link" href="?page={{ $i }}">{{ $i }}</a></li>
    @endfor
</ul></nav>
@endif
@endsection
