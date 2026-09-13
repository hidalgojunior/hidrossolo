@extends('layouts.admin')

@section('content')
<h4 class="mb-4">{{ $veiculo ? 'Editar Veículo' : 'Novo Veículo' }}</h4>

<div class="card mb-4">
    <div class="card-body">
            <form method="POST" action="{{ $veiculo ? '/admin/frota/editar/' . $veiculo['id'] : '/admin/frota/novo' }}">

            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Placa *</label>
                    <input type="text" name="plate" class="form-control" required maxlength="10" value="{{ $veiculo['plate'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Marca *</label>
                    <input type="text" name="brand" class="form-control" required value="{{ $veiculo['brand'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Modelo *</label>
                    <input type="text" name="model" class="form-control" required value="{{ $veiculo['model'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ano *</label>
                    <input type="number" name="year" class="form-control" required value="{{ $veiculo['year'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Renavam</label>
                    <input type="text" name="renavam" class="form-control" value="{{ $veiculo['renavam'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Chassi</label>
                    <input type="text" name="chassis" class="form-control" value="{{ $veiculo['chassis'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Combustível</label>
                    <select name="fuel_type" class="form-select">
                        @foreach (['diesel' => 'Diesel', 'gasoline' => 'Gasolina', 'ethanol' => 'Etanol', 'flex' => 'Flex'] as $k => $v)
                            <option value="{{ $k }}" {{ ($veiculo['fuel_type'] ?? 'diesel') == $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">KM Atual</label>
                    <input type="number" name="current_km" class="form-control" value="{{ $veiculo['current_km'] ?? 0 }}">
                </div>
                @if ($veiculo)
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ $veiculo['status'] == 'active' ? 'selected' : '' }}>Ativo</option>
                        <option value="maintenance" {{ $veiculo['status'] == 'maintenance' ? 'selected' : '' }}>Em Manutenção</option>
                        <option value="inactive" {{ $veiculo['status'] == 'inactive' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
                @endif
                <div class="col-12">
                    <label class="form-label">Observações</label>
                    <textarea name="notes" class="form-control" rows="2">{{ $veiculo['notes'] ?? '' }}</textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="/admin/frota" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>

@if ($veiculo && !empty($manutencoes))
<div class="card mt-4">
    <div class="card-header bg-white"><h5 class="mb-0">Histórico de Manutenções</h5></div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Data</th><th>Tipo</th><th>Oficina</th><th>KM</th><th>Valor</th><th>Descrição</th></tr></thead>
            <tbody>
                @foreach ($manutencoes as $m)
                <tr>
                    <td>{{ date('d/m/Y', strtotime($m['maintenance_date'])) }}</td>
                    <td>{{ $m['type'] === 'preventive' ? 'Preventiva' : 'Corretiva' }}</td>
                    <td>{{ $m['workshop'] }}</td>
                    <td>{{ number_format($m['km_at_maintenance'] ?? 0, 0, ',', '.') }} km</td>
                    <td>R$ {{ number_format($m['cost'], 2, ',', '.') }}</td>
                    <td>{{ Str::limit($m['description'], 50) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
