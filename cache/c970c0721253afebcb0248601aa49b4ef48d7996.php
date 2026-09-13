<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hidrossolo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1e40af; --gradient: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 3rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-logo { text-align: center; margin-bottom: 2rem; }
        .login-logo img { height: 50px; }
        .login-logo h4 { color: var(--primary); font-weight: 700; margin-top: 0.5rem; }
        .btn-login { background: var(--gradient); border: none; padding: 0.75rem; font-weight: 600; border-radius: 8px; }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 5px 20px rgba(30,64,175,0.4); }
        .form-control { border-radius: 8px; padding: 0.75rem 1rem; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 0.2rem rgba(30,64,175,0.15); }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <img src="<?php echo e($logo ?? '/assets/images/hidrossolo.png'); ?>" alt="Hidrossolo">
            <h4>Área Restrita</h4>
            <p class="text-muted">Faça login para continuar</p>
        </div>

        <?php if(isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><?php echo e($_SESSION['flash_error']); ?></div>
            <?php unset($_SESSION['flash_error']) ?>
        <?php endif; ?>

        <form method="POST" action="/admin/login">
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" required autofocus placeholder="seu@email.com">
            </div>
            <div class="mb-4">
                <label class="form-label">Senha</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" required placeholder="Sua senha">
                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-login w-100 text-white">Entrar</button>
        </form>

        <div class="text-center mt-3">
            <a href="/admin/esqueci-senha" class="text-muted small">Esqueci minha senha</a>
        </div>

        <p class="text-center text-muted mt-4 mb-0" style="font-size:0.8rem">
            &copy; <?php echo e(date('Y')); ?> Hidrossolo Poços Artesianos
        </p>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script>
    document.querySelector('.toggle-password').addEventListener('click', function() {
        const input = document.getElementById(this.dataset.target);
        const icon = this.querySelector('i');
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
    </script>
</body>
</html>
<?php /**PATH /var/www/html/views/admin/login.blade.php ENDPATH**/ ?>