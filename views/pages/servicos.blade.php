@extends('layouts.main')

@section('content')
<section class="hero">
    <div class="container">
        <h1>Nossos Serviços</h1>
        <p class="lead">Soluções completas em captação de água subterrânea</p>
    </div>
</section>

<section class="section">
    <div class="container">
        @if (empty($servicos))
            <div class="text-center py-5">
                <i class="bi bi-tools fs-1 text-muted"></i>
                <h3 class="mt-3">Serviços em breve</h3>
                <p class="text-muted">Estamos cadastrando nossos serviços. Volte em breve!</p>
            </div>
        @else
            <div class="row g-4">
                @foreach ($servicos as $servico)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        @if ($servico['featured_image'])
                            <img src="{{ $servico['featured_image'] }}" class="card-img-top" alt="{{ $servico['title'] }}" style="height:220px;object-fit:cover">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:220px">
                                <i class="bi bi-{{ $servico['icon'] ?? 'droplet' }} fs-1 text-primary"></i>
                            </div>
                        @endif
                        <div class="card-body d-flex flex-column">
                            <h3 class="h5">{{ $servico['title'] }}</h3>
                            <p class="text-muted flex-grow-1">{{ Str::limit($servico['description'], 150) }}</p>
                            <a href="/servicos/{{ $servico['slug'] }}" class="btn btn-outline-primary mt-auto">Ver Detalhes</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

<!-- CTA -->
<section class="section" style="background: var(--gradient); color: #fff;">
    <div class="container text-center">
        <h2 style="color:#fff">Não encontrou o que precisa?</h2>
        <p class="lead mb-4" style="color:rgba(255,255,255,0.9)">Entre em contato conosco e teremos prazer em ajudar.</p>
        <a href="/contato" class="btn btn-light btn-lg fw-semibold">Fale Conosco</a>
    </div>
</section>
@endsection
