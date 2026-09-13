<?php

/**
 * Hidrossolo Poços Artesianos - Entry Point Admin
 */

// Autoload
require_once __DIR__ . '/../../vendor/autoload.php';

// Segurança: headers + hardening de sessão (antes de qualquer saída)
\App\Core\Security::boot();

// Inicializar aplicação
$app = \App\Core\App::getInstance();

// Carregar rotas admin
require_once __DIR__ . '/../../routes/admin.php';

// Executar
$app->run();
