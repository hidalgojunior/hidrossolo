<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<h4 class="mb-4"><?= e($usuario ? 'Editar Usuário' : 'Novo Usuário') ?></h4>

<div class="card">
    <div class="card-body">
            <form method="POST" action="<?= e($usuario ? '/admin/usuarios/editar/' . $usuario['id'] : '/admin/usuarios/novo') ?>">

            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nome *</label>
                    <input type="text" name="name" class="form-control" required value="<?= e($usuario['name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">E-mail *</label>
                    <input type="email" name="email" class="form-control" required value="<?= e($usuario['email'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Senha <?= e($usuario ? '(deixe em branco para manter)' : '*') ?></label>
                    <div class="input-group">
                        <input type="password" name="password" id="user_password" class="form-control" <?= e($usuario ? '' : 'required') ?> minlength="6">
                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="user_password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Perfil *</label>
                    <select name="role_id" class="form-select" required>
                        <?php foreach ($roles as $role) { ?>
                            <option value="<?= e($role['id']) ?>" <?= e(($usuario['role_id'] ?? '') == $role['id'] ? 'selected' : '') ?>>
                                <?= e(ucfirst($role['name'])) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end pb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="active" id="active" <?= e(($usuario['active'] ?? true) ? 'checked' : '') ?>>
                        <label class="form-check-label" for="active">Ativo</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="/admin/usuarios" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $view->endSection(); ?>
