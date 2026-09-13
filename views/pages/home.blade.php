@extends('layouts.main')

@section('content')
<!-- Hero -->
<section class="hero">
    <div class="container text-center">
        <h1>{!! nl2br(e($hero['title'] ?? 'Soluções Completas em<br>Poços Artesianos')) !!}</h1>
        @if (!empty($hero['subtitle']))
            <p class="lead">{{ $hero['subtitle'] }}</p>
        @endif
        <div class="mt-4">
            <a href="{{ $hero['link_url'] ?? '/contato' }}" class="btn btn-light btn-lg me-3 fw-semibold">{{ $hero['content'] ?? 'Solicitar Orçamento' }}</a>
            <a href="/servicos" class="btn btn-outline-light btn-lg">Nossos Serviços</a>
        </div>
    </div>
</section>

<!-- Banners / Divulgação -->
@if (!empty($banners))
<section class="section bg-white">
    <div class="container">
        <h2 class="section-title text-center mb-4">Parceiros & Divulgações</h2>
        <div class="row g-4 justify-content-center">
            @foreach ($banners as $banner)
            <div class="col-md-4 col-lg-3">
                <a href="{{ $banner['link_url'] ?? '#' }}" target="_blank" rel="noopener" class="card banner-card h-100 text-decoration-none" style="transition:transform 0.2s">
                    @if ($banner['image'])
                        <img src="{{ $banner['image'] }}" class="card-img-top p-3" alt="{{ $banner['title'] }}" style="height:140px;object-fit:contain">
                    @endif
                    <div class="card-body text-center">
                        <h5 class="card-title text-dark">{{ $banner['title'] }}</h5>
                        @if ($banner['subtitle'])
                            <p class="card-text text-muted small">{{ $banner['subtitle'] }}</p>
                        @endif
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Diferenciais -->
@if (!empty($diferenciais))
<section class="section">
    <div class="container">
        <div class="row g-4">
            @foreach ($diferenciais as $dif)
            <div class="col-md-4">
                <div class="card service-card h-100">
                    <div class="icon text-primary fs-1 mb-3">
                        @if ($dif['image'])
                            <img src="{{ $dif['image'] }}" alt="{{ $dif['title'] }}" style="width:64px;height:64px">
                        @else
                            <i class="bi bi-check-circle"></i>
                        @endif
                    </div>
                    <h3>{{ $dif['title'] }}</h3>
                    <p class="text-muted">{{ $dif['content'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Serviços em Destaque -->
@if (!empty($servicos))
<section class="section bg-white">
    <div class="container">
        <h2 class="section-title text-center">{{ $servicosSectionTitle ?? 'Nossos Serviços' }}</h2>
        <p class="section-subtitle text-center">{{ $servicosSectionSub ?? 'Soluções completas para captação de água subterrânea' }}</p>
        <div class="row g-4">
            @foreach ($servicos as $servico)
            <div class="col-md-4">
                <div class="card service-card h-100">
                    @if ($servico['icon'])
                        <div class="icon"><i class="bi bi-{{ $servico['icon'] }}"></i></div>
                    @endif
                    <h3>{{ $servico['title'] }}</h3>
                    <p class="text-muted">{{ Str::limit($servico['description'], 120) }}</p>
                    <a href="/servicos/{{ $servico['slug'] }}" class="btn btn-outline-primary btn-sm mt-2">Saiba Mais</a>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="/servicos" class="btn btn-primary">Ver Todos os Serviços</a>
        </div>
    </div>
</section>
@endif

<!-- CTA -->
<section class="section" style="background: var(--gradient); color: #fff;">
    <div class="container text-center">
        <h2 style="color:#fff">{{ $ctaSection['title'] ?? 'Precisa de um Poço Artesiano?' }}</h2>
        <p class="lead mb-4" style="color:rgba(255,255,255,0.9)">{{ $ctaSection['subtitle'] ?? 'Solicite um orçamento sem compromisso.' }}</p>
        <a href="{{ $ctaSection['link_url'] ?? '/contato' }}" class="btn btn-light btn-lg fw-semibold">
            <i class="bi bi-whatsapp me-2"></i>{{ $ctaSection['content'] ?? 'Solicitar Orçamento' }}
        </a>
    </div>
</section>

<!-- Depoimentos -->
@if (!empty($depoimentos))
<section class="section bg-white">
    <div class="container">
        <h2 class="section-title text-center">O que dizem nossos clientes</h2>
        <p class="section-subtitle text-center">A confiança de quem já contratou nossos serviços</p>
        <div class="row g-4">
            @foreach ($depoimentos as $dep)
            <div class="col-md-4">
                <div class="card h-100 p-4">
                    <div class="text-warning mb-3">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    </div>
                    <p class="fst-italic">"{{ $dep['content'] }}"</p>
                    <strong>{{ $dep['title'] }}</strong>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
