@extends('layouts.admin')

@section('content')
<h4 class="mb-4">Novo Abastecimento</h4>

<div class="card">
    <div class="card-body">
            <form method="POST" action="/admin/abastecimentos/novo">

            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Veículo *</label>
                    <select name="vehicle_id" class="form-select" required>
                        <option value="">Selecione...</option>
                        @foreach ($veiculos as $v)
                            <option value="{{ $v['id'] }}" {{ $veiculoId == $v['id'] ? 'selected' : '' }}>
                                {{ $v['plate'] }} - {{ $v['brand'] }} {{ $v['model'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data *</label>
                    <input type="date" name="fuel_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">KM no abastecimento</label>
                    <input type="number" name="km_at_refuel" class="form-control" placeholder="Opcional">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Litros *</label>
                    <input type="text" name="liters" class="form-control" required placeholder="0,00">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Valor Total (R$) *</label>
                    <input type="text" name="cost" class="form-control" required placeholder="0,00">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Registrar Abastecimento</button>
                    <a href="/admin/abastecimentos" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
