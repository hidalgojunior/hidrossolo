<?php

/**
 * Hidrossolo Poços Artesianos - Entry Point Público
 */

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Inicializar aplicação
$app = \App\Core\App::getInstance();

// Carregar rotas web
require_once __DIR__ . '/../routes/web.php';

// Carregar rotas da API
require_once __DIR__ . '/../routes/api.php';

// Executar
$app->run();
