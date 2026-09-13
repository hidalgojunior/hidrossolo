@extends('layouts.admin')

@section('content')
<h4 class="mb-4">Visualizar Mensagem</h4>

<div class="card">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Nome:</strong> {{ $contato['name'] }}
            </div>
            <div class="col-md-6">
                <strong>E-mail:</strong> <a href="mailto:{{ $contato['email'] }}">{{ $contato['email'] }}</a>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Telefone:</strong> {{ $contato['phone'] ?: 'Não informado' }}
            </div>
            <div class="col-md-6">
                <strong>Data:</strong> {{ date('d/m/Y H:i', strtotime($contato['created_at'])) }}
            </div>
        </div>
        <hr>
        <h5>Mensagem</h5>
        <p class="mt-3">{{ nl2br(e($contato['message'])) }}</p>

        <div class="mt-4">
            <a href="/admin/contatos" class="btn btn-outline-secondary">Voltar</a>
            <a href="mailto:{{ $contato['email'] }}?subject=Resposta Hidrossolo" class="btn btn-primary ms-2">
                <i class="bi bi-reply me-2"></i>Responder por E-mail
            </a>
        </div>
    </div>
</div>
@endsection
