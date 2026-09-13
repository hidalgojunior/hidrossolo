<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Gestão de Contratos</h4>
    <a href="/admin/contratos/novo" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Novo Contrato</a>
</div>

<?php if (!empty($vencendo)) { ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>Atenção:</strong> <?= e(count($vencendo)) ?> contrato(s) próximo(s) do vencimento.
</div>
<?php } ?>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($contratos)) { ?>
            <p class="text-muted text-center py-5">Nenhum contrato cadastrado.</p>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nº</th>
                            <th>Cliente</th>
                            <th>Responsável</th>
                            <th>Início</th>
                            <th>Término</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contratos as $c) { ?>
                        <tr class="<?= e($c['status'] === 'active' && strtotime($c['end_date']) < strtotime('+30 days') ? 'table-warning' : '') ?>">
                            <td><strong><?= e($c['contract_number']) ?></strong></td>
                            <td><?= e($c['client_name']) ?></td>
                            <td><?= e($c['responsible']) ?></td>
                            <td><?= e(date('d/m/Y', strtotime($c['start_date']))) ?></td>
                            <td><?= e(date('d/m/Y', strtotime($c['end_date']))) ?></td>
                            <td>R$ <?= e(number_format($c['value'], 2, ',', '.')) ?></td>
                            <td><?= match($c['status']) {
                                'active' => '<span class="badge bg-success">Ativo</span>',
                                'expired' => '<span class="badge bg-danger">Vencido</span>',
                                'cancelled' => '<span class="badge bg-secondary">Cancelado</span>',
                                default => '<span class="badge bg-info">Concluído</span>'
                            } ?></td>
                            <td class="text-end">
                                <a href="/admin/contratos/editar/<?= e($c['id']) ?>" class="btn btn-sm btn-outline-primary">Editar</a>
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
