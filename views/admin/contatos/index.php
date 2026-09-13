<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Mensagens Recebidas</h4>
    <div>
        <a href="?status=all" class="btn btn-sm <?= e($status == 'all' ? 'btn-primary' : 'btn-outline-secondary') ?>">Todas (<?= e($total) ?>)</a>
        <a href="?status=new" class="btn btn-sm <?= e($status == 'new' ? 'btn-primary' : 'btn-outline-secondary') ?>">Novas (<?= e($novos) ?>)</a>
        <a href="?status=read" class="btn btn-sm <?= e($status == 'read' ? 'btn-primary' : 'btn-outline-secondary') ?>">Lidas</a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($contatos)) { ?>
            <p class="text-muted text-center py-5">Nenhuma mensagem encontrada.</p>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Mensagem</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contatos as $c) { ?>
                        <tr class="<?= e($c['status'] == 'new' ? 'table-active fw-semibold' : '') ?>">
                            <td><?= e($c['name']) ?></td>
                            <td><?= e($c['email']) ?></td>
                            <td><?= e($c['phone']) ?></td>
                            <td><?= e(str_limit($c['message'], 50)) ?></td>
                            <td><?= e(date('d/m/Y H:i', strtotime($c['created_at']))) ?></td>
                            <td><?= $c['status'] == 'new' ? '<span class="badge bg-danger">Nova</span>' : '<span class="badge bg-secondary">Lida</span>' ?></td>
                            <td><a href="/admin/contatos/<?= e($c['id']) ?>" class="btn btn-sm btn-outline-primary">Ver</a></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</div>
<?php $view->endSection(); ?>
