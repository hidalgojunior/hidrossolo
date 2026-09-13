<nav class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="<?= e($logo ?? '/assets/images/hidrossolo.png') ?>" alt="Hidrossolo">
        <span>Hidrossolo</span>
        <button class="sidebar-close" onclick="closeSidebar()" title="Fechar menu">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <ul class="sidebar-nav">
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin') && !str_contains($_SERVER['REQUEST_URI'], '/admin/') || $_SERVER['REQUEST_URI'] == '/admin' ? 'active' : '') ?>" 
               href="/admin">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>

        <li class="section-title">Conteúdo</li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/cms/home') ? 'active' : '') ?>" 
               href="/admin/cms/home">
                <i class="bi bi-house-gear"></i> Home
            </a>
        </li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/cms/empresa') ? 'active' : '') ?>" 
               href="/admin/cms/empresa">
                <i class="bi bi-building"></i> Empresa
            </a>
        </li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/cms/contato') ? 'active' : '') ?>" 
               href="/admin/cms/contato">
                <i class="bi bi-telephone"></i> Contato
            </a>
        </li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/cms/lgpd') ? 'active' : '') ?>" 
               href="/admin/cms/lgpd">
                <i class="bi bi-shield-check"></i> LGPD
            </a>
        </li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/servicos') ? 'active' : '') ?>" 
               href="/admin/servicos">
                <i class="bi bi-tools"></i> Serviços
            </a>
        </li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/menus') ? 'active' : '') ?>" 
               href="/admin/menus">
                <i class="bi bi-list-ul"></i> Menus
            </a>
        </li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/midia') ? 'active' : '') ?>" 
               href="/admin/midia">
                <i class="bi bi-images"></i> Mídias
            </a>
        </li>

        <li class="section-title">Operacional</li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/frota') && !str_contains($_SERVER['REQUEST_URI'], '/admin/frota/editar') && !str_contains($_SERVER['REQUEST_URI'], '/admin/frota/relatorios') ? 'active' : '') ?>" 
               href="/admin/frota">
                <i class="bi bi-truck"></i> Frota &amp; Equipamentos
            </a>
        </li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/frota/relatorios') ? 'active' : '') ?>" 
               href="/admin/frota/relatorios">
                <i class="bi bi-graph-up"></i> Relatórios de consumo
            </a>
        </li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/manutencoes') ? 'active' : '') ?>" 
               href="/admin/manutencoes">
                <i class="bi bi-wrench"></i> Manutenções
            </a>
        </li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/abastecimentos') ? 'active' : '') ?>" 
               href="/admin/abastecimentos">
                <i class="bi bi-fuel-pump"></i> Abastecimento
            </a>
        </li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/contratos') && !str_contains($_SERVER['REQUEST_URI'], '/admin/contratos/modelos') ? 'active' : '') ?>" 
               href="/admin/contratos">
                <i class="bi bi-file-earmark-text"></i> Contratos
            </a>
        </li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/contratos/modelos') ? 'active' : '') ?>" 
               href="/admin/contratos/modelos">
                <i class="bi bi-file-earmark-ruled"></i> Modelos de contrato
            </a>
        </li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/contatos') ? 'active' : '') ?>" 
               href="/admin/contatos">
                <i class="bi bi-envelope"></i> Mensagens
            </a>
        </li>

        <li class="section-title">Sistema</li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/seo') ? 'active' : '') ?>" 
               href="/admin/seo">
                <i class="bi bi-search"></i> SEO
            </a>
        </li>
        <?php
            $isSuperAdmin = false;
            try {
                $db = \App\Core\App::getInstance()->getDb();
                $u = $db->fetch("SELECT r.name FROM users u JOIN roles r ON u.role_id=r.id WHERE u.id=?", [$_SESSION['user_id']??0]);
                $isSuperAdmin = ($u['name'] ?? '') === 'superadmin';
            } catch(\Throwable) {}
        ?>
        <?php if ($isSuperAdmin) { ?>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/api-docs') ? 'active' : '') ?>" 
               href="/admin/api-docs">
                <i class="bi bi-code-slash"></i> API Docs
            </a>
        </li>
        <?php } ?>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/seguranca') ? 'active' : '') ?>" 
               href="/admin/seguranca">
                <i class="bi bi-shield-lock"></i> Segurança
            </a>
        </li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/lgpd') ? 'active' : '') ?>" 
               href="/admin/lgpd">
                <i class="bi bi-person-check"></i> Solicitações LGPD
            </a>
        </li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/auditoria') ? 'active' : '') ?>" 
               href="/admin/auditoria">
                <i class="bi bi-shield-check"></i> Auditoria
            </a>
        </li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/usuarios') ? 'active' : '') ?>" 
               href="/admin/usuarios">
                <i class="bi bi-people"></i> Usuários
            </a>
        </li>
        <li>
            <a class="nav-link <?= e(str_contains($_SERVER['REQUEST_URI'], '/admin/perfil') ? 'active' : '') ?>" 
               href="/admin/perfil">
                <i class="bi bi-person-gear"></i> Meu Perfil
            </a>
        </li>
        <li>
            <a class="nav-link text-danger" href="/admin/logout">
                <i class="bi bi-box-arrow-right"></i> Sair
            </a>
        </li>
    </ul>
</nav>
