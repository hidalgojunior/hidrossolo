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
use App\Controllers\Admin\OrcamentosController;
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

    // Seções livres da Home (incluir, editar, reordenar e excluir)
    $router->post('/cms/home/secoes', [CMSController::class, 'storeSection']);
    $router->post('/cms/home/secoes/{id}', [CMSController::class, 'updateSection']);
    $router->post('/cms/home/secoes/{id}/mover', [CMSController::class, 'moveSection']);
    $router->post('/cms/home/secoes/{id}/excluir', [CMSController::class, 'deleteSection']);

    // Blocos da Home (exibir/ocultar e reordenar)
    // A rota "restaurar" precisa vir antes do curinga {bloco}
    $router->post('/cms/home/blocos/restaurar', [CMSController::class, 'resetBlocks']);
    $router->post('/cms/home/blocos/{bloco}/mover', [CMSController::class, 'moveBlock']);
    $router->post('/cms/home/blocos/{bloco}', [CMSController::class, 'toggleBlock']);

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

    // Orçamentos (formulário público /orcamento)
    $router->get('/orcamentos', [OrcamentosController::class, 'index']);
    $router->post('/orcamentos/status/{id}', [OrcamentosController::class, 'status']);
    $router->post('/orcamentos/excluir/{id}', [OrcamentosController::class, 'delete']);
    $router->get('/orcamentos/{id}', [OrcamentosController::class, 'show']);

    // Auditoria
    $router->get('/auditoria', [\App\Controllers\Admin\AuditoriaController::class, 'index']);

    // Notificações (avisos de manutenção, contas a vencer e segurança)
    $router->get('/notificacoes', [\App\Controllers\Admin\NotificacoesController::class, 'index']);
    $router->get('/notificacoes/abrir/{id}', [\App\Controllers\Admin\NotificacoesController::class, 'abrir']);
    $router->post('/notificacoes/ler/{id}', [\App\Controllers\Admin\NotificacoesController::class, 'ler']);
    $router->post('/notificacoes/ler-todas', [\App\Controllers\Admin\NotificacoesController::class, 'lerTodas']);
    $router->post('/notificacoes/gerar-avisos', [\App\Controllers\Admin\NotificacoesController::class, 'gerarAvisos']);
    $router->post('/notificacoes/limpar', [\App\Controllers\Admin\NotificacoesController::class, 'limpar']);
    $router->post('/notificacoes/excluir/{id}', [\App\Controllers\Admin\NotificacoesController::class, 'excluir']);

    // Veículos
    $router->get('/frota', [VeiculosController::class, 'index']);
    $router->get('/frota/pdf', [VeiculosController::class, 'pdf']);
    $router->get('/frota/xlsx', [VeiculosController::class, 'xlsx']);
    $router->get('/frota/novo', [VeiculosController::class, 'create']);
    $router->post('/frota/novo', [VeiculosController::class, 'store']);
    $router->get('/frota/relatorios', [\App\Controllers\Admin\RelatoriosFrotaController::class, 'index']);
    $router->get('/frota/relatorios/pdf', [\App\Controllers\Admin\RelatoriosFrotaController::class, 'pdf']);
    $router->get('/frota/relatorios/xlsx', [\App\Controllers\Admin\RelatoriosFrotaController::class, 'xlsx']);

    // Agenda de manutenção e compromissos
    $router->get('/agenda', [\App\Controllers\Admin\AgendaController::class, 'index']);
    $router->get('/agenda/pdf', [\App\Controllers\Admin\AgendaController::class, 'pdf']);
    $router->get('/agenda/xlsx', [\App\Controllers\Admin\AgendaController::class, 'xlsx']);
    $router->post('/agenda/novo', [\App\Controllers\Admin\AgendaController::class, 'store']);
    $router->post('/agenda/status/{id}', [\App\Controllers\Admin\AgendaController::class, 'status']);
    $router->post('/agenda/excluir/{id}', [\App\Controllers\Admin\AgendaController::class, 'delete']);

    // Fluxo de caixa (contas a pagar e a receber)
    $router->get('/financeiro', [\App\Controllers\Admin\FinanceiroController::class, 'index']);
    $router->get('/financeiro/pdf', [\App\Controllers\Admin\FinanceiroController::class, 'pdf']);
    $router->get('/financeiro/xlsx', [\App\Controllers\Admin\FinanceiroController::class, 'xlsx']);
    $router->get('/financeiro/novo', [\App\Controllers\Admin\FinanceiroController::class, 'create']);
    $router->post('/financeiro/novo', [\App\Controllers\Admin\FinanceiroController::class, 'store']);
    $router->get('/financeiro/editar/{id}', [\App\Controllers\Admin\FinanceiroController::class, 'edit']);
    $router->post('/financeiro/editar/{id}', [\App\Controllers\Admin\FinanceiroController::class, 'update']);
    $router->post('/financeiro/status/{id}', [\App\Controllers\Admin\FinanceiroController::class, 'status']);
    $router->post('/financeiro/excluir/{id}', [\App\Controllers\Admin\FinanceiroController::class, 'delete']);
    $router->get('/frota/editar/{id}', [VeiculosController::class, 'edit']);
    $router->post('/frota/editar/{id}', [VeiculosController::class, 'update']);
    $router->post('/frota/status/{id}', [VeiculosController::class, 'toggleStatus']);
    $router->post('/frota/excluir/{id}', [VeiculosController::class, 'delete']);

    // Contratos — modelos com variáveis (antes das rotas com {id})
    $router->get('/contratos/modelos', [\App\Controllers\Admin\ContratoTemplatesController::class, 'index']);
    $router->get('/contratos/modelos/novo', [\App\Controllers\Admin\ContratoTemplatesController::class, 'create']);
    $router->post('/contratos/modelos/novo', [\App\Controllers\Admin\ContratoTemplatesController::class, 'store']);
    $router->get('/contratos/modelos/editar/{id}', [\App\Controllers\Admin\ContratoTemplatesController::class, 'edit']);
    $router->post('/contratos/modelos/editar/{id}', [\App\Controllers\Admin\ContratoTemplatesController::class, 'update']);
    $router->post('/contratos/modelos/excluir/{id}', [\App\Controllers\Admin\ContratoTemplatesController::class, 'delete']);

    // Contratos
    $router->get('/contratos', [ContratosController::class, 'index']);
    $router->get('/contratos/novo', [ContratosController::class, 'create']);
    $router->post('/contratos/novo', [ContratosController::class, 'store']);
    $router->get('/contratos/editar/{id}', [ContratosController::class, 'edit']);
    $router->post('/contratos/editar/{id}', [ContratosController::class, 'update']);
    $router->post('/contratos/excluir/{id}', [ContratosController::class, 'delete']);
    $router->get('/contratos/documento/{id}', [ContratosController::class, 'documento']);
    $router->get('/contratos/pdf/{id}', [ContratosController::class, 'pdf']);

    // Segurança
    $router->get('/seguranca', [\App\Controllers\Admin\SegurancaController::class, 'index']);

    // LGPD (solicitações de titulares)
    $router->get('/lgpd', [\App\Controllers\Admin\LgpdController::class, 'index']);
    $router->post('/lgpd/responder/{id}', [\App\Controllers\Admin\LgpdController::class, 'responder']);

    // Usuários
    $router->get('/usuarios', [UsuariosController::class, 'index']);
    $router->get('/usuarios/novo', [UsuariosController::class, 'create']);
    $router->post('/usuarios/novo', [UsuariosController::class, 'store']);
    $router->get('/usuarios/editar/{id}', [UsuariosController::class, 'edit']);
    $router->post('/usuarios/editar/{id}', [UsuariosController::class, 'update']);
    $router->post('/usuarios/excluir/{id}', [UsuariosController::class, 'delete']);

    // Mídia
    $router->get('/midia', [MidiaController::class, 'index']);
    $router->get('/midia/list', [MidiaController::class, 'list']);
    $router->post('/midia/upload', [MidiaController::class, 'upload']);
    $router->post('/midia/youtube', [MidiaController::class, 'youtube']);
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
    $router->get('/manutencoes/pdf', [ManutencoesController::class, 'pdf']);
    $router->get('/manutencoes/xlsx', [ManutencoesController::class, 'xlsx']);
    $router->get('/manutencoes/novo', [ManutencoesController::class, 'create']);
    $router->post('/manutencoes/novo', [ManutencoesController::class, 'store']);
    $router->get('/manutencoes/editar/{id}', [ManutencoesController::class, 'edit']);
    $router->post('/manutencoes/editar/{id}', [ManutencoesController::class, 'update']);
    $router->post('/manutencoes/excluir/{id}', [ManutencoesController::class, 'delete']);

    // Abastecimentos
    $router->get('/abastecimentos', [AbastecimentosController::class, 'index']);
    $router->get('/abastecimentos/pdf', [AbastecimentosController::class, 'pdf']);
    $router->get('/abastecimentos/xlsx', [AbastecimentosController::class, 'xlsx']);
    $router->get('/abastecimentos/novo', [AbastecimentosController::class, 'create']);
    $router->post('/abastecimentos/novo', [AbastecimentosController::class, 'store']);
    $router->post('/abastecimentos/excluir/{id}', [AbastecimentosController::class, 'delete']);

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
