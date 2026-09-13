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
    <title><?= e($title ?? 'Área do Motorista') ?> - Hidrossolo</title>
    <link rel="stylesheet" href="<?= e(asset_v('assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset_v('assets/css/motorista.css')) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <?= $view->getSection('styles') ?>
</head>
<body class="driver">
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
    <?= $view->getSection('scripts') ?>
</body>
</html>
