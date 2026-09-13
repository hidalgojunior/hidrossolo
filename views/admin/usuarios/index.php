<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Gerenciar Usuários</h4>
    <a href="/admin/usuarios/novo" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Novo Usuário</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Perfil</th>
                        <th>Último Acesso</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u) { ?>
                    <tr>
                        <td><strong><?= e($u['name']) ?></strong></td>
                        <td><?= e($u['email']) ?></td>
                        <td><span class="badge bg-info"><?= e($u['role_name']) ?></span></td>
                        <td><?= e($u['last_login'] ? date('d/m/Y H:i', strtotime($u['last_login'])) : '—') ?></td>
                        <td><?= $u['active'] ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>' ?></td>
                        <td class="text-end">
                            <a href="/admin/usuarios/editar/<?= e($u['id']) ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $view->endSection(); ?>
