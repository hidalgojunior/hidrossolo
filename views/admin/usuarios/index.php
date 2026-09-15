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
                        <?php
                        $ehVoceMesmo = (int) $u['id'] === (int) ($_SESSION['user_id'] ?? 0);
                        $ehContaGeral = strtolower((string) $u['email']) === 'hidalgojunior@gmail.com';
                        ?>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="/admin/usuarios/editar/<?= e($u['id']) ?>" class="btn btn-sm btn-outline-primary">Editar</a>

                                <?php if ($ehVoceMesmo || $ehContaGeral) { ?>
                                    <button class="btn btn-sm btn-outline-danger" disabled
                                            title="<?= $ehVoceMesmo ? 'Você não pode excluir o seu próprio usuário.' : 'A conta administradora geral não pode ser excluída.' ?>">
                                        Excluir
                                    </button>
                                <?php } else { ?>
                                    <form action="/admin/usuarios/excluir/<?= e($u['id']) ?>" method="POST" class="d-inline"
                                          onsubmit="return confirm('Excluir este usuário? Não há como desfazer.')">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                    </form>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $view->endSection(); ?>
