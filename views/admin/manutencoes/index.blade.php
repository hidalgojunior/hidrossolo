@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Controle de Manutenções</h4>
    <a href="/admin/manutencoes/novo{{ $veiculoId ? '?veiculo_id=' . $veiculoId : '' }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nova Manutenção
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

<div class="card">
    <div class="card-body p-0">
        @if (empty($manutencoes))
            <p class="text-muted text-center py-5">Nenhuma manutenção registrada.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Veículo</th>
                            <th>Tipo</th>
                            <th>Data</th>
                            <th>KM</th>
                            <th>Oficina</th>
                            <th>Valor</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($manutencoes as $m)
                        <tr>
                            <td><strong>{{ $m['plate'] }}</strong><br><small>{{ $m['brand'] }} {{ $m['model'] }}</small></td>
                            <td>
                                @if ($m['type'] === 'preventive')
                                    <span class="badge bg-info">Preventiva</span>
                                @else
                                    <span class="badge bg-warning text-dark">Corretiva</span>
                                @endif
                            </td>
                            <td>{{ date('d/m/Y', strtotime($m['maintenance_date'])) }}</td>
                            <td>{{ number_format($m['km_at_maintenance'] ?? 0, 0, ',', '.') }} km</td>
                            <td>{{ $m['workshop'] ?: '—' }}</td>
                            <td>R$ {{ number_format($m['cost'], 2, ',', '.') }}</td>
                            <td class="text-end">
                                <a href="/admin/manutencoes/editar/{{ $m['id'] }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form action="/admin/manutencoes/excluir/{{ $m['id'] }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta manutenção?')">
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
