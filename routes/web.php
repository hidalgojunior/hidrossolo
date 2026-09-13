<?php

/**
 * Rotas do Site Institucional
 */

use App\Core\App;
use App\Controllers\HomeController;
use App\Controllers\EmpresaController;
use App\Controllers\ServicosController;
use App\Controllers\ContatoController;
use App\Controllers\BlogController;
use App\Controllers\PaginasController;
use App\Controllers\LgpdController;
use App\Controllers\NewsletterController;
use App\Controllers\OrcamentoController;
use App\Middleware\CsrfMiddleware;
use App\Middleware\MaintenanceMiddleware;

$router = App::getInstance()->getRouter();

// Sitemap XML (fora de qualquer grupo - sempre acessível)
$router->get('/sitemap.xml', function () {
    header('Content-Type: application/xml; charset=utf-8');
    $db = \App\Core\App::getInstance()->getDb();
    $base = $_ENV['APP_URL'] ?? 'http://localhost:8083';
    $pages = $db->fetchAll("SELECT slug, updated_at FROM pages WHERE status='published'");
    $posts = $db->fetchAll("SELECT slug, updated_at FROM posts WHERE status='published'");
    $services = $db->fetchAll("SELECT slug, updated_at FROM services WHERE active=1");
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    echo "<url><loc>{$base}/</loc><priority>1.0</priority></url>\n";
    echo "<url><loc>{$base}/empresa</loc><priority>0.8</priority></url>\n";
    echo "<url><loc>{$base}/servicos</loc><priority>0.9</priority></url>\n";
    echo "<url><loc>{$base}/blog</loc><priority>0.8</priority></url>\n";
    echo "<url><loc>{$base}/contato</loc><priority>0.7</priority></url>\n";
    foreach ($pages as $p) echo "<url><loc>{$base}/{$p['slug']}</loc><lastmod>{$p['updated_at']}</lastmod><priority>0.6</priority></url>\n";
    foreach ($services as $s) echo "<url><loc>{$base}/servicos/{$s['slug']}</loc><lastmod>{$s['updated_at']}</lastmod><priority>0.7</priority></url>\n";
    foreach ($posts as $p) echo "<url><loc>{$base}/blog/{$p['slug']}</loc><lastmod>{$p['updated_at']}</lastmod><priority>0.6</priority></url>\n";
    echo '</urlset>';
    exit;
});

// Middleware de manutenção em todas as rotas públicas
$router->group(['middleware' => [MaintenanceMiddleware::class]], function ($router) {

// Home
$router->get('/', [HomeController::class, 'index']);

// Empresa
$router->get('/empresa', [EmpresaController::class, 'index']);

// Serviços
$router->get('/servicos', [ServicosController::class, 'index']);
$router->get('/servicos/{slug}', [ServicosController::class, 'detalhe']);

// Contato
$router->get('/contato', [ContatoController::class, 'index']);
$router->post('/contato/enviar', [ContatoController::class, 'enviar'], [CsrfMiddleware::class]);
$router->get('/obrigado', [ContatoController::class, 'obrigado']);

// Newsletter
$router->post('/newsletter', [NewsletterController::class, 'subscribe'], [CsrfMiddleware::class]);

// Orçamento
$router->get('/orcamento', [OrcamentoController::class, 'index']);
$router->post('/orcamento/enviar', [OrcamentoController::class, 'enviar'], [CsrfMiddleware::class]);

// Blog
$router->get('/blog', [BlogController::class, 'index']);
$router->get('/blog/{slug}', [BlogController::class, 'detalhe']);

// Busca
$router->get('/busca', [PaginasController::class, 'busca']);

// Páginas dinâmicas (sempre por último)
$router->get('/{slug}', [PaginasController::class, 'show']);

}); // Fim do grupo MaintenanceMiddleware
