@extends('layouts.main')

@section('content')
<section class="hero">
    <div class="container">
        <h1>{{ $servico['title'] }}</h1>
        <p class="lead">{{ $servico['description'] }}</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                @if ($servico['featured_image'])
                    <img src="{{ $servico['featured_image'] }}" alt="{{ $servico['title'] }}" class="img-fluid rounded-4 shadow mb-4 w-100" style="max-height:400px;object-fit:cover">
                @endif

                <div class="content">
                    {!! $servico['content'] ?: '<p>' . nl2br(e($servico['description'])) . '</p>' !!}
                </div>

                @if (!empty($galeria))
                <h3 class="mt-5 mb-3">Galeria</h3>
                <div class="row g-3">
                    @foreach ($galeria as $img)
                    <div class="col-md-4">
                        <img src="{{ $img['image_path'] }}" alt="{{ $img['alt_text'] }}" class="img-fluid rounded-3 shadow-sm" style="height:180px;object-fit:cover;width:100%">
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="card p-4 sticky-top" style="top:100px">
                    <h4>Solicitar Orçamento</h4>
                    <p class="text-muted">Interessado neste serviço? Entre em contato!</p>
                    <a href="/contato" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-envelope me-2"></i>Solicitar Orçamento
                    </a>
                    <a href="https://wa.me/5514991234567" class="btn btn-success w-100" target="_blank">
                        <i class="bi bi-whatsapp me-2"></i>WhatsApp
                    </a>
                </div>

                @if (!empty($outros))
                <div class="mt-4">
                    <h5>Outros Serviços</h5>
                    @foreach ($outros as $s)
                    <a href="/servicos/{{ $s['slug'] }}" class="text-decoration-none">
                        <div class="card p-3 mb-2">
                            <strong>{{ $s['title'] }}</strong>
                            <small class="text-muted">{{ Str::limit($s['description'], 80) }}</small>
                        </div>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
