<?php $__env->startSection('content'); ?>
<h4 class="mb-4">📡 API Documentation</h4>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Info -->
        <div class="card mb-4">
            <div class="card-body">
                <h5><i class="bi bi-info-circle me-2 text-primary"></i>Informações Gerais</h5>
                <table class="table table-sm mt-3">
                    <tr><td style="width:160px"><strong>Base URL</strong></td><td><code><?php echo e($app_url); ?>/api</code></td></tr>
                    <tr><td><strong>Formato</strong></td><td><code>application/json</code></td></tr>
                    <tr><td><strong>Collection Postman</strong></td><td><a href="/admin/docs/Hidrossolo-API.postman_collection.json" class="btn btn-sm btn-outline-primary"><i class="bi bi-download me-1"></i>Download</a></td></tr>
                    <tr><td><strong>DFD Banco</strong></td><td><a href="/admin/docs/DATABASE_DFD.md" class="btn btn-sm btn-outline-secondary"><i class="bi bi-diagram-3 me-1"></i>Ver Diagrama</a></td></tr>
                </table>
            </div>
        </div>

        <!-- Autenticação -->
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white"><h5 class="mb-0">🔑 Autenticação Bearer Token</h5></div>
            <div class="card-body">
                <p>Todos os endpoints autenticados exigem um <strong>token Bearer</strong> no header <code>Authorization</code>.</p>

                <div class="alert alert-info mb-3">
                    <strong>🔑 Seu Token:</strong>
                    <div class="input-group mt-2">
                        <input type="text" class="form-control font-monospace" value="hidrossolo_api_2026_token_admin" id="apiToken" readonly>
                        <button class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('apiToken').value)"><i class="bi bi-clipboard"></i> Copiar</button>
                    </div>
                </div>

                <h6>📋 Exemplos de uso:</h6>
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-curl">cURL</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-js">JavaScript</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-python">Python</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-postman">Postman</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tab-curl">
                        <pre class="bg-dark text-light p-3 rounded small"><code># Status (público - sem token)
curl <?php echo e($app_url); ?>/api/status

# Dashboard (autenticado - com Bearer token)
curl -H "Authorization: Bearer hidrossolo_api_2026_token_admin" \
     <?php echo e($app_url); ?>/api/dashboard

# Listar serviços
curl -H "Authorization: Bearer hidrossolo_api_2026_token_admin" \
     <?php echo e($app_url); ?>/api/servicos

# Contatos não lidos
curl -H "Authorization: Bearer hidrossolo_api_2026_token_admin" \
     <?php echo e($app_url); ?>/api/contatos?status=new

# Enviar contato (público - sem token)
curl -X POST <?php echo e($app_url); ?>/api/contato \
     -H "Content-Type: application/json" \
     -d '{"name":"João","email":"joao@email.com","message":"Olá!"}'</code></pre>
                    </div>
                    <div class="tab-pane fade" id="tab-js">
                        <pre class="bg-dark text-light p-3 rounded small"><code>// JavaScript (fetch)
const BASE = '<?php echo e($app_url); ?>/api';
const TOKEN = 'hidrossolo_api_2026_token_admin';

// Status público
fetch(`${BASE}/status`)
  .then(r => r.json())
  .then(console.log);

// Dashboard autenticado
fetch(`${BASE}/dashboard`, {
  headers: { 'Authorization': `Bearer ${TOKEN}` }
})
  .then(r => r.json())
  .then(console.log);

// Enviar contato
fetch(`${BASE}/contato`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ name: 'João', email: 'joao@x.com', message: 'Teste' })
})
  .then(r => r.json())
  .then(console.log);</code></pre>
                    </div>
                    <div class="tab-pane fade" id="tab-python">
                        <pre class="bg-dark text-light p-3 rounded small"><code># Python (requests)
import requests

BASE = '<?php echo e($app_url); ?>/api'
TOKEN = 'hidrossolo_api_2026_token_admin'
HEADERS = {'Authorization': f'Bearer {TOKEN}'}

# Status público
r = requests.get(f'{BASE}/status')
print(r.json())

# Dashboard autenticado
r = requests.get(f'{BASE}/dashboard', headers=HEADERS)
print(r.json())

# Listar serviços
r = requests.get(f'{BASE}/servicos', headers=HEADERS)
print(r.json())

# Enviar contato
r = requests.post(f'{BASE}/contato', json={
    'name': 'João', 'email': 'joao@x.com', 'message': 'Teste'
})
print(r.json())</code></pre>
                    </div>
                    <div class="tab-pane fade" id="tab-postman">
                        <pre class="bg-dark text-light p-3 rounded small"><code>No Postman, configure na aba "Authorization":

  Type:   Bearer Token
  Token:  hidrossolo_api_2026_token_admin

Ou importe a Collection do botão Download acima —
o token já está pré-configurado como variável {{token}}.</code></pre>
                    </div>
                </div>

                <hr>
                <p class="mb-0 small text-muted">
                    ⚠️ Endpoints públicos (<code>/api/status</code>, <code>/api/contato</code>) não precisam de token.<br>
                    🔐 Endpoints autenticados retornam <code>401</code> se o token estiver ausente ou inválido.
                </p>
            </div>
        </div>

        <!-- Endpoints Públicos -->
        <div class="card mb-4">
            <div class="card-header bg-white"><h5 class="mb-0">🌐 Endpoints Públicos</h5></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th style="width:80px">Método</th><th>Endpoint</th><th>Descrição</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td><code>/api/status</code></td>
                            <td>Status do sistema, versão, timestamp</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-warning text-dark">POST</span></td>
                            <td><code>/api/contato</code></td>
                            <td>Enviar formulário de contato</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Endpoints Autenticados -->
        <div class="card mb-4">
            <div class="card-header bg-white"><h5 class="mb-0">🔐 Endpoints Autenticados</h5></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th style="width:80px">Método</th><th>Endpoint</th><th>Descrição</th></tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="3" class="bg-light fw-semibold small">📊 Dashboard</td></tr>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td><code>/api/dashboard</code></td>
                            <td>Contadores e listas resumidas</td>
                        </tr>

                        <tr><td colspan="3" class="bg-light fw-semibold small">🛠️ Serviços</td></tr>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td><code>/api/servicos</code></td>
                            <td>Lista serviços ativos</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td><code>/api/servicos/{id}</code></td>
                            <td>Detalhes + imagens do serviço</td>
                        </tr>

                        <tr><td colspan="3" class="bg-light fw-semibold small">📩 Contatos</td></tr>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td><code>/api/contatos</code></td>
                            <td>Lista mensagens (?status=new|read|replied)</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td><code>/api/contatos/{id}</code></td>
                            <td>Detalhe da mensagem</td>
                        </tr>

                        <tr><td colspan="3" class="bg-light fw-semibold small">🚛 Frota</td></tr>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td><code>/api/frota</code></td>
                            <td>Lista veículos (?status=active)</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td><code>/api/frota/{id}</code></td>
                            <td>Detalhe do veículo</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td><code>/api/frota/{id}/manutencoes</code></td>
                            <td>Histórico de manutenções</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td><code>/api/frota/{id}/abastecimentos</code></td>
                            <td>Histórico de abastecimentos</td>
                        </tr>

                        <tr><td colspan="3" class="bg-light fw-semibold small">📄 Contratos</td></tr>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td><code>/api/contratos</code></td>
                            <td>Lista contratos (?status=active)</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td><code>/api/contratos/{id}</code></td>
                            <td>Detalhe + arquivos anexos</td>
                        </tr>

                        <tr><td colspan="3" class="bg-light fw-semibold small">📝 CMS</td></tr>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td><code>/api/paginas</code></td>
                            <td>Lista páginas</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td><code>/api/paginas/{slug}</code></td>
                            <td>Detalhe + seções</td>
                        </tr>

                        <tr><td colspan="3" class="bg-light fw-semibold small">📰 Blog</td></tr>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td><code>/api/blog</code></td>
                            <td>Lista posts publicados</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td><code>/api/blog/{slug}</code></td>
                            <td>Detalhe do post</td>
                        </tr>

                        <tr><td colspan="3" class="bg-light fw-semibold small">🖼️ Mídia</td></tr>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td><code>/api/midia</code></td>
                            <td>Lista arquivos (?category=general)</td>
                        </tr>

                        <tr><td colspan="3" class="bg-light fw-semibold small">⚙️ Configurações</td></tr>
                        <tr>
                            <td><span class="badge bg-success">GET</span></td>
                            <td><code>/api/configuracoes</code></td>
                            <td>Dados da empresa + SEO + settings</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Exemplo -->
        <div class="card mb-4">
            <div class="card-header bg-white"><h5 class="mb-0">🧪 Teste Rápido</h5></div>
            <div class="card-body">
                <p class="small text-muted mb-2">🔓 Públicos (sem token):</p>
                <button class="btn btn-success btn-sm w-100 mb-2" onclick="testEndpoint('/api/status')">
                    <i class="bi bi-play-fill me-1"></i> GET /api/status
                </button>
                <hr class="my-2">
                <p class="small text-muted mb-2">🔐 Autenticados (Bearer token):</p>
                <button class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="testEndpointAuth('/api/dashboard')">
                    <i class="bi bi-play-fill me-1"></i> GET /api/dashboard
                </button>
                <button class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="testEndpointAuth('/api/servicos')">
                    <i class="bi bi-play-fill me-1"></i> GET /api/servicos
                </button>
                <button class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="testEndpointAuth('/api/contatos')">
                    <i class="bi bi-play-fill me-1"></i> GET /api/contatos
                </button>
                <button class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="testEndpointAuth('/api/paginas')">
                    <i class="bi bi-play-fill me-1"></i> GET /api/paginas
                </button>
                <button class="btn btn-outline-primary btn-sm w-100" onclick="testEndpointAuth('/api/configuracoes')">
                    <i class="bi bi-play-fill me-1"></i> GET /api/configuracoes
                </button>

                <hr>
                <pre id="api-result" class="bg-dark text-light p-2 rounded" style="max-height:300px;overflow-y:auto;font-size:0.75rem;display:none"></pre>
            </div>
        </div>

        <!-- DFD - Schemas das Tabelas -->
        <div class="card">
            <div class="card-header bg-white"><h5 class="mb-0">🗄️ Schemas das Tabelas</h5></div>
            <div class="card-body p-0">
                <div class="accordion accordion-flush" id="tableSchemas">
<?php
$schemas = [
'users' => ['id INT PK', 'name VARCHAR(255)', 'email VARCHAR(255) UNIQUE', 'password VARCHAR(255)', 'role_id INT FK→roles', 'avatar VARCHAR(255)', 'api_token VARCHAR(80) UNIQUE', 'remember_token VARCHAR(100)', 'last_login DATETIME', 'active TINYINT(1)', 'created_at TIMESTAMP', 'updated_at TIMESTAMP'],
'roles' => ['id INT PK', 'name VARCHAR(50) UNIQUE', 'description VARCHAR(255)', 'created_at TIMESTAMP', 'updated_at TIMESTAMP'],
'pages' => ['id INT PK', 'title VARCHAR(255)', 'slug VARCHAR(255) UNIQUE', 'content LONGTEXT', 'excerpt VARCHAR(500)', 'meta_title/meta_description/meta_keywords VARCHAR', 'featured_image VARCHAR(500)', 'status ENUM(draft,published)', 'show_in_menu TINYINT(1)', 'sort_order INT', 'created_by INT FK→users', 'created_at/updated_at TIMESTAMP'],
'page_contents' => ['id INT PK', 'page_id INT FK→pages CASCADE', 'section VARCHAR(100)', 'title/subtitle VARCHAR(255)', 'content TEXT', 'image VARCHAR(500)', 'link_url VARCHAR(500)', 'link_text VARCHAR(255)', 'sort_order INT', 'created_at/updated_at TIMESTAMP'],
'services' => ['id INT PK', 'title VARCHAR(255)', 'slug VARCHAR(255) UNIQUE', 'description TEXT', 'content LONGTEXT', 'icon VARCHAR(100)', 'featured_image VARCHAR(500)', 'meta_title/meta_description/meta_keywords', 'active TINYINT(1)', 'highlight TINYINT(1)', 'sort_order INT', 'created_at/updated_at TIMESTAMP'],
'service_images' => ['id INT PK', 'service_id INT FK→services CASCADE', 'image_path VARCHAR(500)', 'alt_text VARCHAR(255)', 'sort_order INT', 'created_at TIMESTAMP'],
'posts' => ['id INT PK', 'title VARCHAR(255)', 'slug VARCHAR(255) UNIQUE', 'content LONGTEXT', 'excerpt VARCHAR(500)', 'featured_image VARCHAR(500)', 'meta_title/meta_description/meta_keywords', 'status ENUM(draft,published)', 'author_id INT FK→users', 'published_at DATETIME', 'created_at/updated_at TIMESTAMP'],
'vehicles' => ['id INT PK', 'plate VARCHAR(10) UNIQUE', 'brand/model VARCHAR(100)', 'year INT', 'renavam/chassis VARCHAR', 'fuel_type ENUM(gasoline,ethanol,diesel,flex,electric)', 'current_km INT', 'status ENUM(active,maintenance,inactive)', 'notes TEXT', 'created_at/updated_at TIMESTAMP'],
'vehicle_maintenance' => ['id INT PK', 'vehicle_id INT FK→vehicles CASCADE', 'type ENUM(preventive,corrective)', 'workshop VARCHAR(255)', 'maintenance_date DATE', 'km_at_maintenance INT', 'cost DECIMAL(10,2)', 'description/notes TEXT', 'attachment VARCHAR(500)', 'created_at/updated_at TIMESTAMP'],
'vehicle_fuel' => ['id INT PK', 'vehicle_id INT FK→vehicles CASCADE', 'fuel_date DATE', 'liters DECIMAL(8,2)', 'cost DECIMAL(10,2)', 'km_at_refuel INT', 'created_at TIMESTAMP'],
'contracts' => ['id INT PK', 'contract_number VARCHAR(100) UNIQUE', 'client_name VARCHAR(255)', 'responsible VARCHAR(255)', 'start_date/end_date DATE', 'value DECIMAL(12,2)', 'status ENUM(active,expired,cancelled,completed)', 'notes TEXT', 'created_at/updated_at TIMESTAMP'],
'contract_files' => ['id INT PK', 'contract_id INT FK→contracts CASCADE', 'filename VARCHAR(500)', 'original_name VARCHAR(500)', 'file_path VARCHAR(500)', 'mime_type VARCHAR(100)', 'file_size INT', 'uploaded_at TIMESTAMP'],
'contacts' => ['id INT PK', 'name VARCHAR(255)', 'email VARCHAR(255)', 'phone VARCHAR(50)', 'message TEXT', 'status ENUM(new,read,replied)', 'ip_address VARCHAR(45)', 'created_at/updated_at TIMESTAMP'],
'menus' => ['id INT PK', 'name VARCHAR(100)', 'location VARCHAR(100)', 'created_at/updated_at TIMESTAMP'],
'menu_items' => ['id INT PK', 'menu_id INT FK→menus CASCADE', 'parent_id INT FK→menu_items', 'title VARCHAR(255)', 'url VARCHAR(500)', 'target ENUM(_self,_blank)', 'icon VARCHAR(100)', 'sort_order INT', 'active TINYINT(1)', 'created_at/updated_at TIMESTAMP'],
'media_library' => ['id INT PK', 'filename VARCHAR(500)', 'original_name VARCHAR(500)', 'mime_type VARCHAR(100)', 'file_size INT', 'file_path VARCHAR(500)', 'thumbnail_path VARCHAR(500)', 'alt_text VARCHAR(255)', 'category VARCHAR(100)', 'uploaded_by INT FK→users', 'created_at TIMESTAMP'],
'site_settings' => ['id INT PK', '`key` VARCHAR(100) UNIQUE', '`value` TEXT', '`type` VARCHAR(50)', '`group` VARCHAR(50)', 'created_at/updated_at TIMESTAMP'],
'notifications' => ['id INT PK', 'user_id INT FK→users CASCADE', 'title VARCHAR(255)', 'message TEXT', 'type ENUM(info,warning,success,error)', 'link VARCHAR(500)', 'read_at DATETIME', 'created_at TIMESTAMP'],
'audit_logs' => ['id INT PK', 'user_id INT FK→users', 'action VARCHAR(100)', 'entity_type VARCHAR(100)', 'entity_id INT', 'old_values JSON', 'new_values JSON', 'ip_address VARCHAR(45)', 'user_agent VARCHAR(500)', 'created_at TIMESTAMP'],
];
?>

<?php $__currentLoopData = $schemas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table => $columns): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#sch-<?php echo e($table); ?>" style="font-size:0.85rem">
                                <code class="me-2"><?php echo e($table); ?></code>
                                <span class="badge bg-secondary ms-2"><?php echo e(count($columns)); ?> cols</span>
                            </button>
                        </h2>
                        <div id="sch-<?php echo e($table); ?>" class="accordion-collapse collapse" data-bs-parent="#tableSchemas">
                            <div class="accordion-body p-2" style="font-size:0.75rem">
                                <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="py-1 border-bottom <?php echo e($loop->last ? 'border-bottom-0' : ''); ?>">
                                    <code class="text-primary"><?php echo e(explode(' ', $col)[0]); ?></code>
                                    <span class="text-muted ms-1"><?php echo e(preg_replace('/^[^\s]+\s/', '', $col)); ?></span>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const API_TOKEN = 'hidrossolo_api_2026_token_admin';

function testEndpoint(url) {
    const pre = document.getElementById('api-result');
    pre.style.display = 'block';
    pre.textContent = 'Carregando...';
    fetch(url)
        .then(r => r.json())
        .then(data => { pre.textContent = JSON.stringify(data, null, 2); })
        .catch(err => { pre.textContent = 'Erro: ' + err.message; });
}

function testEndpointAuth(url) {
    const pre = document.getElementById('api-result');
    pre.style.display = 'block';
    pre.textContent = 'Carregando (Bearer token)...';
    fetch(url, {
        headers: { 'Authorization': 'Bearer ' + API_TOKEN }
    })
        .then(r => r.json())
        .then(data => { pre.textContent = JSON.stringify(data, null, 2); })
        .catch(err => { pre.textContent = 'Erro: ' + err.message; });
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/admin/api-docs.blade.php ENDPATH**/ ?>