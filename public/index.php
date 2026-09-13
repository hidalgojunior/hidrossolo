<?php

/**
 * Hidrossolo Poços Artesianos - Entry Point Público
 */

// Autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Segurança: headers + hardening de sessão (antes de qualquer saída)
\App\Core\Security::boot();

// Inicializar aplicação
$app = \App\Core\App::getInstance();

// Rotas específicas primeiro (evita que o catch-all /{slug} do site as capture)
require_once __DIR__ . '/../routes/motorista.php';
require_once __DIR__ . '/../routes/legal.php';

// Carregar rotas web
require_once __DIR__ . '/../routes/web.php';

// Carregar rotas da API
require_once __DIR__ . '/../routes/api.php';

// Executar
$app->run();
