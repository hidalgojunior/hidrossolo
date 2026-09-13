<?php
/**
 * Layout da Área do Motorista (mobile-first, simplificado).
 */
$uri = $_SERVER['REQUEST_URI'] ?? '/motorista';
$isActive = static function (string $path) use ($uri): string {
    if ($path === '/motorista') {
        return rtrim($uri, '/') === '/motorista' ? 'active' : '';
    }

    return str_contains($uri, $path) ? 'active' : '';
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="robots" content="noindex, nofollow">

    <!-- PWA -->
    <meta name="theme-color" content="#101a2e">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Hidrossolo">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/assets/pwa/icon-192.png">
    <link rel="icon" href="/assets/pwa/icon-192.png" type="image/png">

    <title><?= e($title ?? 'Área do Motorista') ?> - Hidrossolo</title>
    <link rel="stylesheet" href="<?= e(asset_v('assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset_v('assets/css/motorista.css')) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <?= $view->getSection('styles') ?>
</head>
<body class="driver">
    <div class="offline-banner" role="status">
        <i class="bi bi-wifi-off"></i>
        Sem conexão. Os lançamentos ficam salvos no aparelho e são enviados automaticamente.
    </div>

    <div class="pending-banner" role="status">
        <i class="bi bi-cloud-arrow-up"></i>
        <span data-pending-count>0</span> lançamento(s) aguardando envio.
    </div>

    <div class="pwa-install" data-pwa-install style="display:none">
        <i class="bi bi-phone"></i>
        <span>Instale o app para lançar abastecimentos mesmo sem sinal.</span>
        <button type="button" data-pwa-install-btn>Instalar</button>
        <button type="button" class="close" data-pwa-install-dismiss aria-label="Fechar">&#10005;</button>
    </div>

    <header class="driver-topbar">
        <a class="driver-brand" href="/motorista">
            <img src="<?= e($logo ?? '/assets/images/hidrossolo.png') ?>" alt="Hidrossolo">
            <span>Área do Motorista</span>
        </a>
        <div class="driver-user">
            <span class="d-none d-sm-inline"><i class="bi bi-person-circle me-1"></i><?= e($_SESSION['user_name'] ?? 'Motorista') ?></span>
            <a href="/motorista/logout" class="btn btn-sm btn-outline-light" title="Sair">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </header>

    <nav class="driver-tabs">
        <a href="/motorista" class="driver-tab <?= $isActive('/motorista') ?>">
            <i class="bi bi-speedometer2"></i> Painel
        </a>
        <a href="/motorista/abastecimentos" class="driver-tab <?= $isActive('/motorista/abastecimentos') ?>">
            <i class="bi bi-fuel-pump"></i> Abastecimentos
        </a>
        <a href="/motorista/manutencoes" class="driver-tab <?= $isActive('/motorista/manutencoes') ?>">
            <i class="bi bi-tools"></i> Manutenções
        </a>
    </nav>

    <main class="driver-main">
        <?php if (isset($_SESSION['flash_success'])) { ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-1"></i><?= e($_SESSION['flash_success']) ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php } ?>

        <?php if (isset($_SESSION['flash_error'])) { ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-1"></i><?= e($_SESSION['flash_error']) ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php } ?>

        <?= $view->getSection('content') ?>
    </main>

    <script src="<?= e(asset_v('assets/js/ui.js')) ?>" defer></script>
    <script src="<?= e(asset_v('assets/js/motorista-pwa.js')) ?>" defer></script>
    <?= $view->getSection('scripts') ?>
</body>
</html>
