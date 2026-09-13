<?php

/**
 * Hidrossolo Poços Artesianos - Entry Point Admin
 */

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload
require_once __DIR__ . '/../../vendor/autoload.php';

// Inicializar aplicação
$app = \App\Core\App::getInstance();

// Carregar rotas admin
require_once __DIR__ . '/../../routes/admin.php';

// Executar
$app->run();
