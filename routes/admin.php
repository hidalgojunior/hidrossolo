<?php

/**
 * Rotas da Área Administrativa
 */

use App\Core\App;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\CMSController;
use App\Controllers\Admin\ServicosAdminController;
use App\Controllers\Admin\VeiculosController;
use App\Controllers\Admin\ContratosController;
use App\Controllers\Admin\UsuariosController;
use App\Controllers\Admin\MidiaController;
use App\Controllers\Admin\MenusController;
use App\Controllers\Admin\ContatosController;
use App\Controllers\Admin\ManutencoesController;
use App\Controllers\Admin\AbastecimentosController;
use App\Controllers\Admin\SEOController;
use App\Controllers\Admin\UploadController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

$router = App::getInstance()->getRouter();

// Login (público)
$router->get('/admin/login', [AuthController::class, 'loginForm']);
$router->post('/admin/login', [AuthController::class, 'login']);
$router->get('/admin/logout', [AuthController::class, 'logout']);

// Recuperação de senha (público)
$router->get('/admin/esqueci-senha', [AuthController::class, 'forgotForm']);
$router->post('/admin/esqueci-senha', [AuthController::class, 'forgotSend']);
$router->get('/admin/reset-senha/{token}', [AuthController::class, 'resetForm']);
$router->post('/admin/reset-senha', [AuthController::class, 'resetPassword']);

// Download de arquivos de documentação (público, mas lista restrita)
$router->get('/admin/docs/{file}', function (string $file) {
    $allowed = ['Hidrossolo-API.postman_collection.json', 'DATABASE_DFD.md'];
    if (!in_array($file, $allowed)) {
        http_response_code(404);
        exit('Arquivo não encontrado');
    }
    $root = defined('PROJECT_ROOT') ? PROJECT_ROOT : dirname(__DIR__, 2);
    $path = $root . '/docs/' . $file;
    // Fallback: tenta o caminho absoluto do container
    if (!file_exists($path)) {
        $path = '/var/www/html/docs/' . $file;
    }
    if (!file_exists($path)) {
        http_response_code(404);
        exit('Arquivo não encontrado');
    }
    $mime = str_ends_with($file, '.json') ? 'application/json' : 'text/markdown';
    header('Content-Type: ' . $mime . '; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $file . '"');
    readfile($path);
    exit;
});

// Rotas protegidas
$router->group(['prefix' => 'admin', 'middleware' => [AuthMiddleware::class, CsrfMiddleware::class]], function ($router) {
    // Dashboard
    $router->get('/', [DashboardController::class, 'index']);
    $router->get('/dashboard', [DashboardController::class, 'index']);

    // CMS - Home
    $router->get('/cms/home', [CMSController::class, 'home']);
    $router->post('/cms/home', [CMSController::class, 'updateHome']);

    // CMS - Empresa
    $router->get('/cms/empresa', [CMSController::class, 'empresa']);
    $router->post('/cms/empresa', [CMSController::class, 'updateEmpresa']);

    // CMS - Contato
    $router->get('/cms/contato', [CMSController::class, 'contato']);
    $router->post('/cms/contato', [CMSController::class, 'updateContato']);

    // CMS - LGPD
    $router->get('/cms/lgpd', [CMSController::class, 'lgpd']);
    $router->post('/cms/lgpd', [CMSController::class, 'updateLgpd']);

    // Serviços
    $router->get('/servicos', [ServicosAdminController::class, 'index']);
    $router->get('/servicos/novo', [ServicosAdminController::class, 'create']);
    $router->post('/servicos/novo', [ServicosAdminController::class, 'store']);
    $router->get('/servicos/editar/{id}', [ServicosAdminController::class, 'edit']);
    $router->post('/servicos/editar/{id}', [ServicosAdminController::class, 'update']);
    $router->post('/servicos/excluir/{id}', [ServicosAdminController::class, 'delete']);

    // Contatos
    $router->get('/contatos', [ContatosController::class, 'index']);
    $router->get('/contatos/{id}', [ContatosController::class, 'show']);

    // Auditoria
    $router->get('/auditoria', [\App\Controllers\Admin\AuditoriaController::class, 'index']);

    // Veículos
    $router->get('/frota', [VeiculosController::class, 'index']);
    $router->get('/frota/novo', [VeiculosController::class, 'create']);
    $router->post('/frota/novo', [VeiculosController::class, 'store']);
    $router->get('/frota/editar/{id}', [VeiculosController::class, 'edit']);
    $router->post('/frota/editar/{id}', [VeiculosController::class, 'update']);

    // Contratos
    $router->get('/contratos', [ContratosController::class, 'index']);
    $router->get('/contratos/novo', [ContratosController::class, 'create']);
    $router->post('/contratos/novo', [ContratosController::class, 'store']);

    // Usuários
    $router->get('/usuarios', [UsuariosController::class, 'index']);
    $router->get('/usuarios/novo', [UsuariosController::class, 'create']);
    $router->post('/usuarios/novo', [UsuariosController::class, 'store']);

    // Mídia
    $router->get('/midia', [MidiaController::class, 'index']);
    $router->get('/midia/list', [MidiaController::class, 'list']);
    $router->post('/midia/upload', [MidiaController::class, 'upload']);
    $router->post('/midia/scan', [MidiaController::class, 'scan']);
    $router->post('/midia/update/{id}', [MidiaController::class, 'update']);
    $router->post('/midia/delete/{id}', [MidiaController::class, 'delete']);

    // Menus
    $router->get('/menus', [MenusController::class, 'index']);
    $router->post('/menus', [MenusController::class, 'store']);

    // Perfil
    $router->get('/perfil', [AuthController::class, 'perfil']);
    $router->post('/perfil', [AuthController::class, 'updatePerfil']);

    // API Docs (apenas superadmin)
    $router->get('/api-docs', function () {
        $db = \App\Core\App::getInstance()->getDb();
        $user = $db->fetch("SELECT u.id, r.name as role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?", [$_SESSION['user_id'] ?? 0]);
        if (!$user || $user['role'] !== 'superadmin') {
            http_response_code(403);
            echo \App\Core\View::render('errors.403');
            exit;
        }
        echo \App\Core\View::render('admin.api-docs', [
            'title' => 'API Documentation',
            'app_url' => $_ENV['APP_URL'] ?? 'http://localhost:8083',
        ]);
    });

    // Manutenções
    $router->get('/manutencoes', [ManutencoesController::class, 'index']);
    $router->get('/manutencoes/novo', [ManutencoesController::class, 'create']);
    $router->post('/manutencoes/novo', [ManutencoesController::class, 'store']);
    $router->get('/manutencoes/editar/{id}', [ManutencoesController::class, 'edit']);
    $router->post('/manutencoes/editar/{id}', [ManutencoesController::class, 'update']);
    $router->post('/manutencoes/excluir/{id}', [ManutencoesController::class, 'delete']);

    // Abastecimentos
    $router->get('/abastecimentos', [AbastecimentosController::class, 'index']);
    $router->get('/abastecimentos/novo', [AbastecimentosController::class, 'create']);
    $router->post('/abastecimentos/novo', [AbastecimentosController::class, 'store']);

    // SEO
    $router->get('/seo', [SEOController::class, 'index']);
    $router->post('/seo', [SEOController::class, 'update']);

    // Submenus - gerenciamento de itens
    $router->get('/menus/{id}/itens', [MenusController::class, 'items']);
    $router->post('/menus/{id}/itens', [MenusController::class, 'storeItem']);
    $router->put('/menus/{id}/itens/{itemId}', [MenusController::class, 'updateItem']);
    $router->post('/menus/{id}/itens/{itemId}/delete', [MenusController::class, 'deleteItem']);
    $router->post('/menus/{id}/reorder', [MenusController::class, 'reorder']);

    // Upload do editor HTML
    $router->post('/upload/editor', [UploadController::class, 'editorUpload']);
});
