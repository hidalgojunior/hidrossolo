<?php

/**
 * Rotas da Área do Motorista.
 *
 * Acesso restrito ao papel "motorista" (e admin/superadmin para suporte).
 * O motorista lança apenas abastecimentos e manutenções.
 */

use App\Core\App;
use App\Controllers\Motorista\PainelController;
use App\Controllers\Motorista\AbastecimentoController;
use App\Controllers\Motorista\ManutencaoController;
use App\Controllers\Admin\AuthController;
use App\Middleware\CsrfMiddleware;
use App\Middleware\MotoristaMiddleware;

$router = App::getInstance()->getRouter();

// Sair (fora do grupo: precisa funcionar mesmo sem passar pelo middleware)
$router->get('/motorista/logout', [AuthController::class, 'logout']);

// Base sem barra final
$router->get('/motorista', [PainelController::class, 'index'], [MotoristaMiddleware::class]);

$router->group(['prefix' => 'motorista', 'middleware' => [MotoristaMiddleware::class, CsrfMiddleware::class]], function ($router) {
    $router->get('/', [PainelController::class, 'index']);

    // Abastecimentos
    $router->get('/abastecimentos', [AbastecimentoController::class, 'index']);
    $router->get('/abastecimentos/novo', [AbastecimentoController::class, 'create']);
    $router->post('/abastecimentos/novo', [AbastecimentoController::class, 'store']);

    // Manutenções
    $router->get('/manutencoes', [ManutencaoController::class, 'index']);
    $router->get('/manutencoes/nova', [ManutencaoController::class, 'create']);
    $router->post('/manutencoes/nova', [ManutencaoController::class, 'store']);
});
