<?php

/**
 * Rotas da API
 */

use App\Core\App;
use App\Controllers\Api\ApiController;
use App\Middleware\AuthMiddleware;

$router = App::getInstance()->getRouter();

// =============================================
// API PÚBLICA (sem autenticação)
// =============================================

// Status do sistema
$router->get('/api/status', [ApiController::class, 'status']);

// Contato público
$router->post('/api/contato', [ApiController::class, 'enviarContato']);

// =============================================
// API AUTENTICADA (token Bearer)
// =============================================

$router->group(['prefix' => 'api', 'middleware' => [AuthMiddleware::class]], function ($router) {

    // --- Dashboard / Estatísticas ---
    $router->get('/dashboard', [ApiController::class, 'dashboard']);

    // --- Serviços ---
    $router->get('/servicos', [ApiController::class, 'servicos']);
    $router->get('/servicos/{id}', [ApiController::class, 'servicoDetalhe']);

    // --- Contatos / Mensagens ---
    $router->get('/contatos', [ApiController::class, 'contatos']);
    $router->get('/contatos/{id}', [ApiController::class, 'contatoDetalhe']);

    // --- Veículos / Frota ---
    $router->get('/frota', [ApiController::class, 'frota']);
    $router->get('/frota/{id}', [ApiController::class, 'veiculoDetalhe']);
    $router->get('/frota/{id}/manutencoes', [ApiController::class, 'veiculoManutencoes']);
    $router->get('/frota/{id}/abastecimentos', [ApiController::class, 'veiculoAbastecimentos']);

    // --- Contratos ---
    $router->get('/contratos', [ApiController::class, 'contratos']);
    $router->get('/contratos/{id}', [ApiController::class, 'contratoDetalhe']);

    // --- Páginas / CMS ---
    $router->get('/paginas', [ApiController::class, 'paginas']);
    $router->get('/paginas/{slug}', [ApiController::class, 'paginaDetalhe']);

    // --- Blog ---
    $router->get('/blog', [ApiController::class, 'blog']);
    $router->get('/blog/{slug}', [ApiController::class, 'postDetalhe']);

    // --- Mídia ---
    $router->get('/midia', [ApiController::class, 'midia']);

    // --- Configurações do site ---
    $router->get('/configuracoes', [ApiController::class, 'configuracoes']);
});
