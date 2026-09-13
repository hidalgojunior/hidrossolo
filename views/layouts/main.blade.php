<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    {{-- SEO Básico --}}
    <meta name="description" content="{{ $seo['description'] ?? 'Hidrossolo Poços Artesianos - Perfuração, limpeza e manutenção de poços artesianos em Marília e região. Mais de 15 anos de experiência.' }}">
    <meta name="keywords" content="{{ $seo['keywords'] ?? 'poços artesianos, perfuração de poços, limpeza de poços, manutenção de poços, outorga, licenciamento, Hidrossolo, Marília' }}">
    <meta name="author" content="Hidrossolo Poços Artesianos">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
    <link rel="canonical" href="{{ $app_url }}{{ $_SERVER['REQUEST_URI'] }}">

    {{-- Open Graph (Facebook, LinkedIn, WhatsApp) --}}
    <meta property="og:site_name" content="{{ $app_name }}">
    <meta property="og:title" content="{{ $seo['title'] ?? $title ?? $app_name }}">
    <meta property="og:description" content="{{ $seo['description'] ?? 'Especialistas em perfuração de poços artesianos e soluções hídricas sustentáveis.' }}">
    <meta property="og:url" content="{{ $app_url }}{{ $_SERVER['REQUEST_URI'] }}">
    <meta property="og:type" content="{{ $og_type ?? 'website' }}">
    <meta property="og:image" content="{{ $app_url }}{{ $logo ?? '/assets/images/hidrossolo.png' }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="pt_BR">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['title'] ?? $title ?? $app_name }}">
    <meta name="twitter:description" content="{{ $seo['description'] ?? 'Especialistas em perfuração de poços artesianos.' }}">
    <meta name="twitter:image" content="{{ $app_url }}{{ $logo ?? '/assets/images/hidrossolo.png' }}">

    {{-- Schema.org --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "{{ $schema_type ?? 'LocalBusiness' }}",
        "name": "{{ $app_name }}",
        "url": "{{ $app_url }}",
        "logo": "{{ $app_url }}{{ $logo ?? '/assets/images/hidrossolo.png' }}",
        "description": "{{ $seo['description'] ?? 'Especialistas em perfuração de poços artesianos' }}",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "R. Assad Haddad, 584",
            "addressLocality": "Marília",
            "addressRegion": "SP",
            "postalCode": "17519-700"
        },
        "telephone": "(14) 3413-2437",
        "email": "contato@hidrossolo.com.br"
    }
    </script>

    <title>{{ $seo['title'] ?? $title ?? $app_name }} | {{ $app_name }}</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1e40af;
            --primary-dark: #1e3a8a;
            --primary-light: #3b82f6;
            --secondary: #06b6d4;
            --accent: #f59e0b;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f8fafc;
            --gradient: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            --gradient-secondary: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            background: var(--bg-light);
            scroll-behavior: smooth;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: var(--text-dark);
        }

        /* Navbar */
        .navbar { box-shadow: 0 2px 10px rgba(0,0,0,0.08); background: #fff; }
        .navbar-brand { font-family: 'Poppins', sans-serif; font-weight: 700; color: var(--primary) !important; font-size: 1.4rem; }
        .navbar-brand img { height: 40px; margin-right: 8px; }
        .nav-link { font-weight: 500; color: var(--text-dark) !important; transition: color 0.3s; }
        .nav-link:hover, .nav-link.active { color: var(--primary) !important; }

        /* Hero */
        .hero {
            background: var(--gradient);
            color: #fff;
            padding: 120px 0 80px;
            position: relative;
            overflow: hidden;
        }
        .hero::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 100px;
            background: var(--bg-light);
            transform: skewY(-3deg);
        }
        .hero h1 { font-size: 3rem; font-weight: 800; color: #fff; margin-bottom: 1rem; }
        .hero .lead { font-size: 1.3rem; opacity: 0.95; color: rgba(255,255,255,0.9); }

        /* Sections */
        .section { padding: 80px 0; }
        .section-title { font-size: 2.2rem; font-weight: 700; color: var(--primary); margin-bottom: 0.5rem; }
        .section-subtitle { color: var(--text-light); font-size: 1.1rem; margin-bottom: 3rem; }

        /* Cards */
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.06); transition: transform 0.3s, box-shadow 0.3s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .card-body { padding: 2rem; }

        /* Service Cards */
        .service-card { text-align: center; padding: 2rem 1.5rem; }
        .service-card .icon { font-size: 3rem; color: var(--primary); margin-bottom: 1rem; }
        .service-card h3 { font-size: 1.3rem; margin-bottom: 0.75rem; }

        /* Botões */
        .btn-primary { background: var(--gradient); border: none; padding: 0.75rem 2rem; font-weight: 600; border-radius: 8px; transition: transform 0.3s, box-shadow 0.3s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(30,64,175,0.4); }
        .btn-outline-primary { border: 2px solid var(--primary); color: var(--primary); padding: 0.75rem 2rem; font-weight: 600; border-radius: 8px; }
        .btn-outline-primary:hover { background: var(--gradient); border-color: transparent; color: #fff; }

        /* Footer */
        footer {
            background: #111827;
            color: #9ca3af;
            padding: 60px 0 30px;
        }
        footer h5 { color: #fff; font-weight: 600; margin-bottom: 1.2rem; }
        footer a { color: #9ca3af; text-decoration: none; transition: color 0.3s; }
        footer a:hover { color: #fff; }
        .footer-bottom { border-top: 1px solid #1f2937; margin-top: 40px; padding-top: 20px; text-align: center; font-size: 0.9rem; }

        /* WhatsApp Float */
        .whatsapp-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            width: 60px;
            height: 60px;
            background: #25D366;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(37,211,102,0.4);
            transition: transform 0.3s, box-shadow 0.3s;
            animation: pulse-whatsapp 2s infinite;
        }
        .whatsapp-float:hover { transform: scale(1.1); box-shadow: 0 6px 20px rgba(37,211,102,0.6); }
        .whatsapp-float i { font-size: 32px; color: #fff; }

        @keyframes pulse-whatsapp {
            0% { box-shadow: 0 0 0 0 rgba(37,211,102,0.4); }
            70% { box-shadow: 0 0 0 15px rgba(37,211,102,0); }
            100% { box-shadow: 0 0 0 0 rgba(37,211,102,0); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero { padding: 100px 0 60px; }
            .hero h1 { font-size: 2rem; }
            .section-title { font-size: 1.8rem; }
        }
    </style>

    @yield('styles')
</head>
<body>
    @include('components.header')

    <main>
        @yield('content')
    </main>

    @include('components.footer')
    @include('components.whatsapp')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Cookie Consent Banner -->
    @php
        $db = \App\Core\App::getInstance()->getDb();
        $lgpdSettings = [];
        $settings = $db->fetchAll("SELECT * FROM site_settings WHERE `group` = 'lgpd'");
        foreach ($settings as $s) { $lgpdSettings[$s['key']] = $s['value']; }
    @endphp
    @if (($lgpdSettings['cookie_consent_enabled'] ?? '1') == '1' && !isset($_COOKIE['cookie_consent']))
    <div id="cookie-banner" style="position:fixed;bottom:0;left:0;right:0;background:#1e293b;color:#e2e8f0;padding:1rem 2rem;z-index:9999;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;font-size:0.9rem;">
        <span style="flex:1;min-width:250px">{{ $lgpdSettings['cookie_consent_text'] ?? 'Este site utiliza cookies para melhorar sua experiência. Ao continuar navegando, você concorda com nossa Política de Privacidade.' }}</span>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap">
            <a href="/politica-privacidade" style="color:#93c5fd;text-decoration:underline;white-space:nowrap">Política de Privacidade</a>
            <button onclick="acceptCookies()" style="background:#3b82f6;color:#fff;border:none;padding:0.5rem 1.5rem;border-radius:6px;cursor:pointer;font-weight:600;white-space:nowrap">Aceitar</button>
        </div>
    </div>
    <script>
    function acceptCookies() {
        document.getElementById('cookie-banner').style.display = 'none';
        document.cookie = 'cookie_consent=1;path=/;max-age=' + (365*24*60*60);
    }
    </script>
    @endif

    @yield('scripts')
</body>
</html>
