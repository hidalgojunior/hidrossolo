<?php

/**
 * Rotas das páginas legais (privacidade, cookies, termos e LGPD).
 *
 * Carregado ANTES de routes/web.php para não ser capturado pelo catch-all /{slug}.
 */

use App\Core\App;
use App\Controllers\LegalController;
use App\Middleware\CsrfMiddleware;

$router = App::getInstance()->getRouter();

$router->get('/politica-de-privacidade', [LegalController::class, 'privacidade']);
$router->get('/politica-privacidade', [LegalController::class, 'privacidade']); // alias
$router->get('/politica-de-cookies', [LegalController::class, 'cookies']);
$router->get('/termos-de-uso', [LegalController::class, 'termos']);
$router->get('/lgpd', [LegalController::class, 'lgpd']);
$router->post('/lgpd/solicitacao', [LegalController::class, 'solicitacao'], [CsrfMiddleware::class]);
