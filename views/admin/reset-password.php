<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - Hidrossolo</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        :root { --primary: #1e40af; --gradient: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); }
        body { font-family: var(--font-sans); background: linear-gradient(135deg, #1e293b 0%, #334155 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card { background:#fff; border-radius:16px; padding:2.5rem; width:100%; max-width:420px; box-shadow:0 20px 60px rgba(0,0,0,0.3); }
        .btn-primary { background:var(--gradient); border:none; padding:0.75rem; font-weight:600; border-radius:8px; }
        .form-control { border-radius:8px; padding:0.75rem 1rem; }
    </style>
</head>
<body>
<div class="card">
    <div class="text-center mb-4">
        <img src="<?= e($logo ?? '/assets/images/hidrossolo.png') ?>" alt="Hidrossolo" style="height:45px">
        <h4 class="mt-2" style="color:var(--primary)">Redefinir Senha</h4>
        <p class="text-muted"><?= e($email) ?></p>
    </div>

    <?php if (isset($_SESSION['flash_error'])) { ?>
        <div class="alert alert-danger"><?= e($_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']) ?>
    <?php } ?>

    <form method="POST" action="/admin/reset-senha">
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="mb-3">
            <label class="form-label">Nova Senha</label>
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control" required minlength="6">
                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label">Confirmar Nova Senha</label>
            <div class="input-group">
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6">
                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirm_password">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 text-white">Redefinir Senha</button>
    </form>
</div>

<script>
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        const icon = btn.querySelector('i');
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
});
</script>
</body>
</html>
