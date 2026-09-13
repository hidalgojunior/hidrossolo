<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Hidrossolo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1e40af; --gradient: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #1e293b 0%, #334155 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card { background:#fff; border-radius:16px; padding:2.5rem; width:100%; max-width:420px; box-shadow:0 20px 60px rgba(0,0,0,0.3); }
        .btn-primary { background:var(--gradient); border:none; padding:0.75rem; font-weight:600; border-radius:8px; }
        .form-control { border-radius:8px; padding:0.75rem 1rem; }
    </style>
</head>
<body>
<div class="card">
    <div class="text-center mb-4">
        <img src="{{ $logo ?? '/assets/images/hidrossolo.png' }}" alt="Hidrossolo" style="height:45px">
        <h4 class="mt-2" style="color:var(--primary)">Recuperar Senha</h4>
        <p class="text-muted">Digite seu e-mail para receber o link de redefinição.</p>
    </div>

    @if (isset($_SESSION['flash_success']))
        <div class="alert alert-success">{{ $_SESSION['flash_success'] }}</div>
        @php unset($_SESSION['flash_success']) @endphp
    @endif

    @if (isset($_SESSION['flash_error']))
        <div class="alert alert-danger">{{ $_SESSION['flash_error'] }}</div>
        @php unset($_SESSION['flash_error']) @endphp
    @endif

    @if (isset($_SESSION['reset_link']))
        <div class="alert alert-info">
            <strong>Link de recuperação (dev):</strong><br>
            <a href="{{ $_SESSION['reset_link'] }}">{{ $_SESSION['reset_link'] }}</a>
        </div>
        @php unset($_SESSION['reset_link'], $_SESSION['reset_email']) @endphp
    @endif

    <form method="POST" action="/admin/esqueci-senha">
        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" class="form-control" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary w-100 text-white">Enviar Link</button>
    </form>

    <p class="text-center mt-3"><a href="/admin/login">Voltar ao login</a></p>
</div>
</body>
</html>
