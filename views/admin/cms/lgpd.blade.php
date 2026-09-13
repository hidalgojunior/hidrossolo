@extends('layouts.admin')

@section('content')
<h4 class="mb-4">LGPD - Políticas Legais</h4>

<form method="POST" action="/admin/cms/lgpd">
            @csrf
    <!-- Modo Manutenção -->
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark"><h5 class="mb-0">🚧 Modo Manutenção</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Modo Manutenção</label>
                    <select name="maintenance_mode" class="form-select">
                        <option value="1" {{ ($settings['maintenance_mode'] ?? '0') == '1' ? 'selected' : '' }}>🔴 Ativado</option>
                        <option value="0" {{ ($settings['maintenance_mode'] ?? '0') == '0' ? 'selected' : '' }}>🟢 Desativado</option>
                    </select>
                    <small class="text-muted">Quando ativo, visitantes veem página de manutenção. Admins logados continuam com acesso normal.</small>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Mensagem de Manutenção</label>
                    <input type="text" name="maintenance_message" class="form-control" value="{{ $settings['maintenance_message'] ?? 'Estamos realizando melhorias no site. Voltaremos em breve!' }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Configurações -->
    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">⚙️ Configurações de Consentimento</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Banner de Cookies</label>
                    <select name="cookie_consent_enabled" class="form-select">
                        <option value="1" {{ ($settings['cookie_consent_enabled'] ?? '1') == '1' ? 'selected' : '' }}>Ativado</option>
                        <option value="0" {{ ($settings['cookie_consent_enabled'] ?? '') == '0' ? 'selected' : '' }}>Desativado</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">E-mail DPO (Encarregado LGPD)</label>
                    <input type="email" name="lgpd_contact_email" class="form-control" value="{{ $settings['lgpd_contact_email'] ?? $config['email'] ?? '' }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Texto do Banner de Cookies</label>
                    <input type="text" name="cookie_consent_text" class="form-control" value="{{ $settings['cookie_consent_text'] ?? 'Este site utiliza cookies para melhorar sua experiência. Ao continuar navegando, você concorda com nossa Política de Privacidade.' }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Política de Privacidade -->
    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">🔒 Política de Privacidade</h5></div>
        <div class="card-body">
            <textarea name="content_politica-privacidade" class="form-control editor" rows="10">{{ $pages['politica-privacidade']['content'] ?? '<h4>Política de Privacidade</h4>
<p>A Hidrossolo Poços Artesianos está comprometida com a proteção dos seus dados pessoais. Esta política descreve como coletamos, usamos e protegemos suas informações.</p>
<h5>Dados Coletados</h5>
<p>Coletamos apenas os dados necessários para prestação dos nossos serviços: nome, e-mail, telefone e mensagens enviadas através do formulário de contato.</p>
<h5>Finalidade</h5>
<p>Seus dados são utilizados exclusivamente para responder suas solicitações de orçamento e contato.</p>
<h5>Seus Direitos</h5>
<p>Você pode solicitar a exclusão ou alteração dos seus dados a qualquer momento pelo e-mail de contato.</p>' }}</textarea>
        </div>
    </div>

    <!-- Política de Cookies -->
    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">🍪 Política de Cookies</h5></div>
        <div class="card-body">
            <textarea name="content_politica-cookies" class="form-control editor" rows="8">{{ $pages['politica-cookies']['content'] ?? '<h4>Política de Cookies</h4>
<p>Utilizamos cookies essenciais para o funcionamento do site e cookies de análise para melhorar sua experiência.</p>
<h5>Tipos de Cookies</h5>
<ul><li><strong>Essenciais:</strong> necessários para o funcionamento do site (sessão, autenticação).</li><li><strong>Analíticos:</strong> utilizados para entender como os visitantes interagem com o site.</li></ul>
<p>Você pode desabilitar cookies nas configurações do seu navegador.</p>' }}</textarea>
        </div>
    </div>

    <!-- Termos de Uso -->
    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">📄 Termos de Uso</h5></div>
        <div class="card-body">
            <textarea name="content_termos-uso" class="form-control editor" rows="8">{{ $pages['termos-uso']['content'] ?? '<h4>Termos de Uso</h4>
<p>Ao acessar o site da Hidrossolo Poços Artesianos, você concorda com os seguintes termos:</p>
<h5>Uso do Site</h5>
<p>O conteúdo deste site é para fins informativos. Não nos responsabilizamos por decisões tomadas com base nas informações aqui contidas.</p>
<h5>Propriedade Intelectual</h5>
<p>Todo o conteúdo, incluindo textos, imagens e logotipos, é propriedade da Hidrossolo.</p>' }}</textarea>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg">💾 Salvar LGPD</button>
</form>
@endsection
