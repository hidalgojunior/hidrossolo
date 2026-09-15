<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-0">Orçamentos Recebidos</h4>
        <p class="text-muted small mb-0">
            Solicitações enviadas pelo formulário público <code>/orcamento</code>.
        </p>
    </div>
    <div class="d-flex flex-wrap gap-1">
        <a href="?status=all" class="btn btn-sm <?= e($status === 'all' ? 'btn-primary' : 'btn-outline-secondary') ?>">
            Todas (<?= e($total) ?>)
        </a>
        <?php foreach ($statusLabels as $chave => $info) { ?>
            <a href="?status=<?= e($chave) ?>"
               class="btn btn-sm <?= e($status === $chave ? 'btn-primary' : 'btn-outline-secondary') ?>">
                <?= e($info['rotulo']) ?> (<?= e($porStatus[$chave] ?? 0) ?>)
            </a>
        <?php } ?>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($orcamentos)) { ?>
            <p class="text-muted text-center py-5 mb-0">Nenhuma solicitação encontrada.</p>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Contato</th>
                            <th>Serviço</th>
                            <th>Data desejada</th>
                            <th>Recebido em</th>
                            <th>Situação</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orcamentos as $o) { ?>
                        <?php $info = $statusLabels[$o['status']] ?? ['rotulo' => $o['status'], 'cor' => 'secondary']; ?>
                        <tr class="<?= e($o['status'] === 'novo' ? 'table-active fw-semibold' : '') ?>">
                            <td><?= e($o['name']) ?></td>
                            <td>
                                <span class="d-block"><?= e($o['email']) ?></span>
                                <?php if (!empty($o['phone'])) { ?>
                                    <small class="text-muted"><?= e($o['phone']) ?></small>
                                <?php } ?>
                            </td>
                            <td><?= e($o['service_type']) ?></td>
                            <td><?= e(data_iso_para_br($o['preferred_date']) ?: '—') ?></td>
                            <td><?= e(date('d/m/Y H:i', strtotime((string) $o['created_at']))) ?></td>
                            <td><span class="badge bg-<?= e($info['cor']) ?>"><?= e($info['rotulo']) ?></span></td>
                            <td>
                                <a href="/admin/orcamentos/<?= e($o['id']) ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</div>
<?php $view->endSection(); ?>
