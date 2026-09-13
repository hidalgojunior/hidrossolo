@extends('layouts.admin')

@section('content')
<h4 class="mb-4">Configurações de SEO</h4>

<div class="card">
    <div class="card-body">
            <form method="POST" action="/admin/seo">

            @csrf
            <h5>Metadados Globais</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Título do Site</label>
                    <input type="text" name="site_title" class="form-control" value="{{ $settings['site_title'] ?? $config['seo']['title'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Meta Description</label>
                    <input type="text" name="meta_description" class="form-control" value="{{ $settings['meta_description'] ?? '' }}" maxlength="160">
                    <small class="text-muted">Máximo 160 caracteres</small>
                </div>
                <div class="col-12">
                    <label class="form-label">Meta Keywords</label>
                    <input type="text" name="meta_keywords" class="form-control" value="{{ $settings['meta_keywords'] ?? '' }}">
                </div>
            </div>

            <h5>Open Graph</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">OG Title</label>
                    <input type="text" name="og_title" class="form-control" value="{{ $settings['og_title'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">OG Image URL</label>
                    <input type="text" name="og_image" class="form-control" value="{{ $settings['og_image'] ?? '' }}">
                </div>
                <div class="col-12">
                    <label class="form-label">OG Description</label>
                    <textarea name="og_description" class="form-control" rows="2">{{ $settings['og_description'] ?? '' }}</textarea>
                </div>
            </div>

            <h5>Integrações</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Google Analytics (ID)</label>
                    <input type="text" name="google_analytics" class="form-control" placeholder="G-XXXXXXXXXX" value="{{ $settings['google_analytics'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Google Search Console (Verificação)</label>
                    <input type="text" name="google_verification" class="form-control" value="{{ $settings['google_verification'] ?? '' }}">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Salvar Configurações</button>
        </form>
    </div>
</div>
@endsection
