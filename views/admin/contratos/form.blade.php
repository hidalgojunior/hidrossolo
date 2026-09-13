@extends('layouts.admin')

@section('content')
<h4 class="mb-4">{{ $contrato ? 'Editar Contrato' : 'Novo Contrato' }}</h4>

<div class="card">
    <div class="card-body">
            <form method="POST" action="{{ $contrato ? '/admin/contratos/editar/' . $contrato['id'] : '/admin/contratos/novo' }}">

            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Número do Contrato *</label>
                    <input type="text" name="contract_number" class="form-control" required value="{{ $contrato['contract_number'] ?? '' }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Cliente *</label>
                    <input type="text" name="client_name" class="form-control" required value="{{ $contrato['client_name'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Responsável</label>
                    <input type="text" name="responsible" class="form-control" value="{{ $contrato['responsible'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data de Início *</label>
                    <input type="date" name="start_date" class="form-control" required value="{{ $contrato['start_date'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data de Término *</label>
                    <input type="date" name="end_date" class="form-control" required value="{{ $contrato['end_date'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Valor (R$) *</label>
                    <input type="text" name="value" class="form-control" required value="{{ isset($contrato) ? number_format($contrato['value'], 2, ',', '.') : '' }}" placeholder="0,00">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ ($contrato['status'] ?? 'active') == 'active' ? 'selected' : '' }}>Ativo</option>
                        <option value="completed" {{ ($contrato['status'] ?? '') == 'completed' ? 'selected' : '' }}>Concluído</option>
                        <option value="cancelled" {{ ($contrato['status'] ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                        <option value="expired" {{ ($contrato['status'] ?? '') == 'expired' ? 'selected' : '' }}>Vencido</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Observações</label>
                    <textarea name="notes" class="form-control" rows="3">{{ $contrato['notes'] ?? '' }}</textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="/admin/contratos" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
