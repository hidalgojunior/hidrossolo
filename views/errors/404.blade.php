@extends('layouts.main')

@section('content')
<section class="section" style="min-height:70vh;display:flex;align-items:center">
    <div class="container text-center">
        <h1 class="display-1 fw-bold" style="color:var(--primary)">404</h1>
        <h2>Página não encontrada</h2>
        <p class="text-muted">A página que você procura não existe ou foi removida.</p>
        <a href="/" class="btn btn-primary btn-lg mt-3">Voltar para Home</a>
    </div>
</section>
@endsection
