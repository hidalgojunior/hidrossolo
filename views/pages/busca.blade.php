@extends('layouts.main')

@section('content')
<section class="section" style="min-height:60vh">
    <div class="container">
        <h1 class="mb-4">🔍 Resultados para "{{ $q }}"</h1>

        @if (empty($results['pages']) && empty($results['services']) && empty($results['posts']))
            <div class="text-center py-5">
                <i class="bi bi-search display-1 text-muted"></i>
                <p class="text-muted mt-3">Nenhum resultado encontrado para "<strong>{{ $q }}</strong>".</p>
                <a href="/" class="btn btn-primary">Voltar ao Início</a>
            </div>
        @else
            @foreach (['pages' => '📄 Páginas', 'services' => '🛠️ Serviços', 'posts' => '📰 Blog'] as $key => $label)
                @if (!empty($results[$key]))
                    <h4 class="mb-3">{{ $label }}</h4>
                    <div class="list-group mb-4">
                        @foreach ($results[$key] as $item)
                            <a href="/{{ $key === 'services' ? 'servicos' : ($key === 'posts' ? 'blog' : '') }}/{{ $item['slug'] }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $item['title'] }}</strong>
                                    <small class="text-muted">{{ $item['tipo'] }}</small>
                                </div>
                                @if (!empty($item['excerpt']))
                                    <small class="text-muted">{{ Str::limit(strip_tags($item['excerpt']), 150) }}</small>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            @endforeach
        @endif
    </div>
</section>
@endsection
