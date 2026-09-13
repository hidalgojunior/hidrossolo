@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Gestão de Contratos</h4>
    <a href="/admin/contratos/novo" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Novo Contrato</a>
</div>

@if (!empty($vencendo))
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>Atenção:</strong> {{ count($vencendo) }} contrato(s) próximo(s) do vencimento.
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        @if (empty($contratos))
            <p class="text-muted text-center py-5">Nenhum contrato cadastrado.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nº</th>
                            <th>Cliente</th>
                            <th>Responsável</th>
                            <th>Início</th>
                            <th>Término</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($contratos as $c)
                        <tr class="{{ $c['status'] === 'active' && strtotime($c['end_date']) < strtotime('+30 days') ? 'table-warning' : '' }}">
                            <td><strong>{{ $c['contract_number'] }}</strong></td>
                            <td>{{ $c['client_name'] }}</td>
                            <td>{{ $c['responsible'] }}</td>
                            <td>{{ date('d/m/Y', strtotime($c['start_date'])) }}</td>
                            <td>{{ date('d/m/Y', strtotime($c['end_date'])) }}</td>
                            <td>R$ {{ number_format($c['value'], 2, ',', '.') }}</td>
                            <td>{!! match($c['status']) {
                                'active' => '<span class="badge bg-success">Ativo</span>',
                                'expired' => '<span class="badge bg-danger">Vencido</span>',
                                'cancelled' => '<span class="badge bg-secondary">Cancelado</span>',
                                default => '<span class="badge bg-info">Concluído</span>'
                            } !!}</td>
                            <td class="text-end">
                                <a href="/admin/contratos/editar/{{ $c['id'] }}" class="btn btn-sm btn-outline-primary">Editar</a>
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
