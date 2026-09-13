<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<h4 class="mb-4"><?= e($title) ?></h4>

<div class="card">
    <div class="card-body">
        <h5>Perfil do Usuário</h5>
        <hr>
        <form method="POST" action="/admin/perfil">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nome</label>
                    <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">E-mail</label>
                    <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nova Senha</label>
                    <div class="input-group">
                        <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Deixe em branco para manter">
                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="new_password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirmar Nova Senha</label>
                    <div class="input-group">
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirm_password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </div>
        </form>
    </div>
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
<?php $view->endSection(); ?>
