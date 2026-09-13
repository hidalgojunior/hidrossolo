<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title ?? 'Admin'); ?> - Hidrossolo</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Summernote Editor -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --sidebar-active: #1e40af;
            --primary: #1e40af;
        }

        body { font-family: 'Inter', sans-serif; background: #f1f5f9; overflow-x: hidden; }

        /* Overlay (mobile) */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1030;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        /* Sidebar off-canvas */
        .sidebar {
            background: var(--sidebar-bg);
            width: 270px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transform: translateX(0);
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
            box-shadow: 2px 0 20px rgba(0,0,0,0.15);
        }
        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            color: #fff;
            font-weight: 700;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .sidebar-brand img { height: 35px; }
        .sidebar-close {
            display: none;
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            margin-left: auto;
        }
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 0.5rem 0;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.15) transparent;
        }
        .sidebar-nav::-webkit-scrollbar { width: 5px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 10px; }
        .sidebar-nav .nav-item { list-style: none; }
        .sidebar-nav .nav-link {
            color: #94a3b8;
            padding: 0.7rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: all 0.15s;
            font-size: 0.93rem;
            border-left: 3px solid transparent;
        }
        .sidebar-nav .nav-link:hover {
            background: var(--sidebar-hover);
            color: #e2e8f0;
        }
        .sidebar-nav .nav-link.active {
            background: rgba(30,64,175,0.25);
            color: #fff;
            border-left-color: #3b82f6;
        }
        .sidebar-nav .nav-link i { font-size: 1.15rem; width: 24px; text-align: center; }
        .sidebar-nav .section-title {
            color: #64748b;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 1.2rem 1.5rem 0.4rem;
            font-weight: 700;
        }
        .sidebar-footer {
            padding: 0.75rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.08);
            flex-shrink: 0;
        }
        .sidebar-footer .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #94a3b8;
            font-size: 0.85rem;
        }
        .sidebar-footer .user-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: var(--sidebar-active);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 0.85rem;
        }

        /* Main Content */
        .main-content {
            margin-left: 270px;
            padding: 1.5rem;
            min-height: 100vh;
            transition: margin-left 0.3s;
        }
        .topbar {
            background: #fff;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            gap: 1rem;
        }
        .topbar .btn-menu { display: none; }
        .topbar .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .card { border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .stat-card {
            background: #fff; border-radius: 12px; padding: 1.5rem;
            display: flex; align-items: center; gap: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .stat-card .stat-icon {
            width: 50px; height: 50px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
        }
        .stat-card .stat-value { font-size: 1.8rem; font-weight: 700; }
        .stat-card .stat-label { color: #64748b; font-size: 0.9rem; }
        .alert { border: none; border-radius: 10px; }

        /* Desktop: sidebar sempre visível */
        @media (min-width: 992px) {
            .sidebar-overlay { display: none !important; }
        }

        /* Mobile / Tablet: off-canvas */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: none;
            }
            .sidebar.show {
                transform: translateX(0);
                box-shadow: 4px 0 30px rgba(0,0,0,0.3);
            }
            .sidebar-close { display: block; }
            .main-content { margin-left: 0; }
            .topbar .btn-menu { display: inline-flex; }
        }

        /* Dark Mode */
        body.dark-mode { background: #0f172a; color: #e2e8f0; }
        body.dark-mode .topbar { background: #1e293b; }
        body.dark-mode .card { background: #1e293b; }
        body.dark-mode .card-header { background: #1e293b !important; color: #e2e8f0; }
        body.dark-mode .stat-card { background: #1e293b; }
        body.dark-mode .stat-card .stat-label { color: #94a3b8; }
        body.dark-mode .text-muted { color: #94a3b8 !important; }
        body.dark-mode .table { color: #e2e8f0; }
        body.dark-mode .table-light { background: #334155; }
        body.dark-mode .form-control, body.dark-mode .form-select { background: #334155; color: #e2e8f0; border-color: #475569; }
        body.dark-mode .btn-outline-secondary { color: #94a3b8; border-color: #475569; }
        body.dark-mode .alert-success { background: #064e3b; color: #a7f3d0; }
        body.dark-mode .alert-danger { background: #7f1d1d; color: #fecaca; }
        body.dark-mode .list-group-item { background: #1e293b; color: #e2e8f0; border-color: #334155; }
        body.dark-mode .dropdown-menu { background: #1e293b; }
        body.dark-mode .dropdown-item { color: #e2e8f0; }
        body.dark-mode .dropdown-item:hover { background: #334155; }
        body.dark-mode .modal-content { background: #1e293b; }
        body.dark-mode .page-link { background: #334155; color: #e2e8f0; }
        body.dark-mode .bg-white { background: #1e293b !important; }
        body.dark-mode .text-dark { color: #e2e8f0 !important; }
        body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, body.dark-mode h4, body.dark-mode h5 { color: #e2e8f0; }
    </style>

    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body>
    <!-- Overlay para mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- Sidebar Off-Canvas -->
    <?php echo $__env->make('admin.components.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Topbar -->
        <div class="topbar">
            <button class="btn btn-outline-secondary btn-menu" onclick="toggleSidebar()" title="Menu">
                <i class="bi bi-list"></i>
            </button>
            <div>
                <h5 class="mb-0"><?php echo e($title ?? 'Dashboard'); ?></h5>
            </div>
            <div class="user-menu">
                <button class="btn btn-light btn-sm me-2" onclick="toggleTheme()" title="Alternar tema" id="themeToggle">
                    <i class="bi bi-moon-stars"></i>
                </button>
                <?php if(($unread_notifications ?? 0) > 0): ?>
                <a href="/admin/notificacoes" class="btn btn-light position-relative" title="Notificações">
                    <i class="bi bi-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?php echo e($unread_notifications); ?></span>
                </a>
                <?php endif; ?>
                <span class="text-muted d-none d-md-inline"><?php echo e($_SESSION['user_name'] ?? 'Usuário'); ?></span>
                <div class="dropdown">
                    <button class="btn btn-light rounded-circle" style="width:40px;height:40px" data-bs-toggle="dropdown" title="Usuário">
                        <i class="bi bi-person"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/admin/perfil"><i class="bi bi-gear me-2"></i>Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/admin/logout"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if(isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i><?php echo e($_SESSION['flash_success']); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_success']) ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i><?php echo e($_SESSION['flash_error']); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']) ?>
        <?php endif; ?>

        <!-- Content -->
        <?php echo $__env->yieldContent('content'); ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Summernote + jQuery (required) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-pt-BR.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.editor').summernote({
            lang: 'pt-BR',
            height: 350,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']],
            ],
            callbacks: {
                onImageUpload: function(files) {
                    var formData = new FormData();
                    formData.append('file', files[0]);
                    $.ajax({
                        url: '/admin/upload/editor',
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(resp) {
                            if (resp.url) {
                                $('.editor').summernote('insertImage', resp.url);
                            } else {
                                alert('Erro ao enviar imagem: ' + (resp.error || 'Desconhecido'));
                            }
                        },
                        error: function() {
                            alert('Erro ao fazer upload da imagem.');
                        }
                    });
                }
            }
        });
    });
    </script>
    <script>
        // Off-canvas sidebar
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
        }

        function closeSidebar() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }

        // Fechar ao clicar fora (em links do sidebar)
        sidebar.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) closeSidebar();
            });
        });

        // Toggle tema escuro
        function toggleTheme() {
            document.body.classList.toggle('dark-mode');
            const icon = document.querySelector('#themeToggle i');
            const isDark = document.body.classList.contains('dark-mode');
            icon.className = isDark ? 'bi bi-sun' : 'bi bi-moon-stars';
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
            document.querySelector('#themeToggle i').className = 'bi bi-sun';
        }

        // Toggle visibilidade de senhas (olho)
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.dataset.target);
                const icon = btn.querySelector('i');
                if (!input || !icon) return;
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        });
    </script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH /var/www/html/views/layouts/admin.blade.php ENDPATH**/ ?>