<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<div class="text-center py-5">
    <h1 class="display-1 fw-bold text-muted">403</h1>
    <h3>Acesso Restrito</h3>
    <p class="text-muted">Você não tem permissão para acessar esta página. Apenas super administradores.</p>
    <a href="/admin/dashboard" class="btn btn-primary mt-3">Voltar ao Dashboard</a>
</div>
<?php $view->endSection(); ?>
