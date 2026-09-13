@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Controle de Abastecimento</h4>
    <a href="/admin/abastecimentos/novo{{ $veiculoId ? '?veiculo_id=' . $veiculoId : '' }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Novo Abastecimento
    </a>
</div>

<!-- Filtro por veículo -->
<div class="mb-3">
    <select class="form-select d-inline-block w-auto" onchange="location = this.value ? '?veiculo_id=' + this.value : '?'">
        <option value="">Todos os veículos</option>
        @foreach ($veiculos as $v)
            <option value="{{ $v['id'] }}" {{ $veiculoId == $v['id'] ? 'selected' : '' }}>
                {{ $v['plate'] }} - {{ $v['brand'] }} {{ $v['model'] }}
            </option>
        @endforeach
    </select>
</div>

<!-- Resumo -->
@if (!empty($abastecimentos) && $veiculoId)
@php
    $totalLitros = array_sum(array_column($abastecimentos, 'liters'));
    $totalCusto = array_sum(array_column($abastecimentos, 'cost'));
    $mediaConsumo = $totalLitros > 0 && count($abastecimentos) > 1
        ? round((max(array_column($abastecimentos, 'km_at_refuel')) - min(array_column($abastecimentos, 'km_at_refuel'))) / $totalLitros, 1)
        : 0;
@endphp
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="stat-card"><div><div class="stat-label">Total Abastecido</div><div class="stat-value">{{ number_format($totalLitros, 1, ',', '.') }} L</div></div></div></div>
    <div class="col-md-3"><div class="stat-card"><div><div class="stat-label">Custo Total</div><div class="stat-value">R$ {{ number_format($totalCusto, 2, ',', '.') }}</div></div></div></div>
    <div class="col-md-3"><div class="stat-card"><div><div class="stat-label">Custo Médio/L</div><div class="stat-value">R$ {{ $totalLitros > 0 ? number_format($totalCusto / $totalLitros, 2, ',', '.') : '0,00' }}</div></div></div></div>
    <div class="col-md-3"><div class="stat-card"><div><div class="stat-label">Média Consumo</div><div class="stat-value">{{ $mediaConsumo }} km/L</div></div></div></div>
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        @if (empty($abastecimentos))
            <p class="text-muted text-center py-5">Nenhum abastecimento registrado.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Veículo</th>
                            <th>Data</th>
                            <th>Litros</th>
                            <th>Valor</th>
                            <th>R$/L</th>
                            <th>KM</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($abastecimentos as $a)
                        <tr>
                            <td><strong>{{ $a['plate'] }}</strong></td>
                            <td>{{ date('d/m/Y', strtotime($a['fuel_date'])) }}</td>
                            <td>{{ number_format($a['liters'], 1, ',', '.') }} L</td>
                            <td>R$ {{ number_format($a['cost'], 2, ',', '.') }}</td>
                            <td>R$ {{ $a['liters'] > 0 ? number_format($a['cost'] / $a['liters'], 2, ',', '.') : '—' }}</td>
                            <td>{{ $a['km_at_refuel'] ? number_format($a['km_at_refuel'], 0, ',', '.') . ' km' : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
