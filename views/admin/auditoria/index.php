<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<h4 class="mb-4">📋 Logs de Auditoria</h4>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Data</th><th>Usuário</th><th>Ação</th><th>Entidade</th><th>ID</th></tr></thead>
                <tbody>
                    <?php $__empty = true; foreach ($logs as $log) { $__empty = false; ?>
                    <tr>
                        <td><small><?= e(date('d/m/Y H:i', strtotime($log['created_at']))) ?></small></td>
                        <td><?= e($log['user_name'] ?? 'Sistema') ?></td>
                        <td><code><?= e($log['action']) ?></code></td>
                        <td><?= e($log['entity_type'] ?? '—') ?></td>
                        <td><?= e($log['entity_id'] ?? '—') ?></td>
                    </tr>
                    <?php } if ($__empty) { ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">Nenhum registro de auditoria.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($total > $limit) { ?>
<nav class="mt-3 d-flex justify-content-center"><ul class="pagination">
    <?php for ($i = 1; $i <= ceil($total / $limit); $i++) { ?>
        <li class="page-item <?= e($i == $page ? 'active' : '') ?>"><a class="page-link" href="?page=<?= e($i) ?>"><?= e($i) ?></a></li>
    <?php } ?>
</ul></nav>
<?php } ?>
<?php $view->endSection(); ?>
