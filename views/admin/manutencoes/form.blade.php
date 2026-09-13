@extends('layouts.admin')

@section('content')
<h4 class="mb-4">{{ $manutencao ? 'Editar Manutenção' : 'Nova Manutenção' }}</h4>

<div class="card">
    <div class="card-body">
            <form method="POST" action="{{ $manutencao ? '/admin/manutencoes/editar/' . $manutencao['id'] : '/admin/manutencoes/novo' }}">

            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Veículo *</label>
                    <select name="vehicle_id" class="form-select" required>
                        <option value="">Selecione...</option>
                        @foreach ($veiculos as $v)
                            <option value="{{ $v['id'] }}" {{ ($manutencao['vehicle_id'] ?? $veiculoId) == $v['id'] ? 'selected' : '' }}>
                                {{ $v['plate'] }} - {{ $v['brand'] }} {{ $v['model'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo *</label>
                    <select name="type" class="form-select" required>
                        <option value="preventive" {{ ($manutencao['type'] ?? '') === 'preventive' ? 'selected' : '' }}>Preventiva</option>
                        <option value="corrective" {{ ($manutencao['type'] ?? '') === 'corrective' ? 'selected' : '' }}>Corretiva</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data *</label>
                    <input type="date" name="maintenance_date" class="form-control" required value="{{ $manutencao['maintenance_date'] ?? date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">KM no momento</label>
                    <input type="number" name="km_at_maintenance" class="form-control" value="{{ $manutencao['km_at_maintenance'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Valor (R$) *</label>
                    <input type="text" name="cost" class="form-control" required value="{{ isset($manutencao) ? number_format($manutencao['cost'], 2, ',', '.') : '' }}" placeholder="0,00">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Oficina</label>
                    <input type="text" name="workshop" class="form-control" value="{{ $manutencao['workshop'] ?? '' }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Descrição</label>
                    <textarea name="description" class="form-control" rows="2">{{ $manutencao['description'] ?? '' }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Observações</label>
                    <textarea name="notes" class="form-control" rows="2">{{ $manutencao['notes'] ?? '' }}</textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="/admin/manutencoes" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
