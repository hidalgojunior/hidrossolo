<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($app_name); ?> - Em Manutenção</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#1e40af,#3b82f6);min-height:100vh;display:flex;align-items:center;justify-content:center}
        .container{text-align:center;color:#fff;max-width:550px;padding:2rem}
        .logo{max-height:70px;margin-bottom:2rem}
        h1{font-size:2.5rem;font-weight:700;margin-bottom:1rem}
        p{font-size:1.15rem;opacity:.9;margin-bottom:2rem;line-height:1.7}
        .spinner{width:50px;height:50px;border:4px solid rgba(255,255,255,.25);border-top-color:#fff;border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 2rem}
        @keyframes  spin{to{transform:rotate(360deg)}}
        .info{font-size:.85rem;opacity:.6;margin-top:3rem}
    </style>
</head>
<body>
    <div class="container">
        <img src="<?php echo e($logo ?? '/assets/images/hidrossolo.png'); ?>" alt="<?php echo e($app_name); ?>" class="logo">
        <div class="spinner"></div>
        <h1>Em Manutenção</h1>
        <p><?php echo e($message); ?></p>
        <div class="info">&copy; <?php echo e(date('Y')); ?> <?php echo e($app_name); ?>. Todos os direitos reservados.</div>
    </div>
</body>
</html>
<?php /**PATH /var/www/html/views/pages/maintenance.blade.php ENDPATH**/ ?>