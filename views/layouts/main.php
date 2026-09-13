<?php
/**
 * Layout principal do site institucional.
 * Motor: App\Core\View — template PHP puro.
 */
$seo = $seo ?? [];
$app_name = $app_name ?? 'Hidrossolo';
$app_url = $app_url ?? '';
$logo = $logo ?? '/assets/images/hidrossolo.png';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$pageTitle = $seo['title'] ?? $title ?? $app_name;
$metaDescription = $seo['description'] ?? 'Hidrossolo Poços Artesianos - Perfuração, limpeza e manutenção de poços artesianos em Marília e região. Mais de 15 anos de experiência.';
$metaKeywords = $seo['keywords'] ?? 'poços artesianos, perfuração de poços, limpeza de poços, manutenção de poços, outorga, licenciamento, Hidrossolo, Marília';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta name="keywords" content="<?= e($metaKeywords) ?>">
    <meta name="author" content="Hidrossolo Poços Artesianos">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
    <link rel="canonical" href="<?= e($app_url . $requestUri) ?>">

    <!-- Open Graph -->
    <meta property="og:site_name" content="<?= e($app_name) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($seo['description'] ?? 'Especialistas em perfuração de poços artesianos e soluções hídricas sustentáveis.') ?>">
    <meta property="og:url" content="<?= e($app_url . $requestUri) ?>">
    <meta property="og:type" content="<?= e($og_type ?? 'website') ?>">
    <meta property="og:image" content="<?= e($app_url . $logo) ?>">
    <meta property="og:locale" content="pt_BR">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($seo['description'] ?? 'Especialistas em perfuração de poços artesianos.') ?>">
    <meta name="twitter:image" content="<?= e($app_url . $logo) ?>">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "<?= e($schema_type ?? 'LocalBusiness') ?>",
        "name": "<?= e($app_name) ?>",
        "url": "<?= e($app_url) ?>",
        "logo": "<?= e($app_url . $logo) ?>",
        "description": "<?= e($seo['description'] ?? 'Especialistas em perfuração de poços artesianos') ?>",
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

    <title><?= e($pageTitle) ?> | <?= e($app_name) ?></title>

    <link rel="icon" href="/assets/images/hidrossolo.png" type="image/png">
    <link rel="stylesheet" href="/assets/css/app.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <?= $view->getSection('styles') ?>
</head>
<body>
    <?= $view->partial('components.header') ?>

    <main>
        <?= $view->getSection('content') ?>
    </main>

    <?= $view->partial('components.footer') ?>
    <?= $view->partial('components.whatsapp') ?>

    <script src="/assets/js/ui.js" defer></script>

    <?php
    // Banner de consentimento de cookies (LGPD)
    $db = \App\Core\App::getInstance()->getDb();
    $lgpdSettings = [];
    try {
        $settings = $db->fetchAll("SELECT * FROM site_settings WHERE `group` = 'lgpd'");
        foreach ($settings as $s) {
            $lgpdSettings[$s['key']] = $s['value'];
        }
    } catch (\Throwable) {
        $lgpdSettings = [];
    }
    ?>
    <?php if (($lgpdSettings['cookie_consent_enabled'] ?? '1') == '1' && !isset($_COOKIE['cookie_consent'])) { ?>
    <div id="cookie-banner" class="cookie-banner" role="dialog" aria-live="polite">
        <span><?= e($lgpdSettings['cookie_consent_text'] ?? 'Este site utiliza cookies para melhorar sua experiência. Ao continuar navegando, você concorda com nossa Política de Privacidade.') ?></span>
        <div class="cookie-banner-actions">
            <a href="/politica-privacidade">Política de Privacidade</a>
            <button type="button" onclick="acceptCookies()">Aceitar</button>
        </div>
    </div>
    <style>
        .cookie-banner {
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 9999;
            display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between;
            padding: 1rem 1.5rem;
            background: #0b1220; color: #e2e8f0;
            box-shadow: 0 -8px 30px rgba(2, 6, 23, .35);
            font-size: .875rem;
        }
        .cookie-banner span { flex: 1 1 320px; }
        .cookie-banner-actions { display: flex; gap: .75rem; align-items: center; flex-wrap: wrap; }
        .cookie-banner a { color: #93c5fd; text-decoration: underline; }
        .cookie-banner button {
            padding: .55rem 1.4rem; border: 0; border-radius: 8px; cursor: pointer;
            background: linear-gradient(135deg, #1e40af, #3b82f6); color: #fff; font-weight: 700;
            font-family: inherit;
        }
    </style>
    <script>
    function acceptCookies() {
        document.getElementById('cookie-banner').style.display = 'none';
        document.cookie = 'cookie_consent=1;path=/;max-age=' + (365 * 24 * 60 * 60);
    }
    </script>
    <?php } ?>

    <?= $view->getSection('scripts') ?>
</body>
</html>
