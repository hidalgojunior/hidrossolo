<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<?php
$statusLabel = [
    'active' => ['Ativo', 'bg-success'],
    'expired' => ['Vencido', 'bg-secondary'],
    'cancelled' => ['Cancelado', 'bg-danger'],
    'completed' => ['Concluído', 'bg-info'],
];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="mb-0">Contratos</h4>
        <p class="text-muted small mb-0">Gere contratos a partir de modelos com variáveis e exporte em PDF.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/admin/contratos/modelos" class="btn btn-outline-primary"><i class="bi bi-file-earmark-ruled me-1"></i>Modelos</a>
        <a href="/admin/contratos/novo" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Novo contrato</a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($contratos)) { ?>
            <p class="text-muted text-center py-5 mb-0">Nenhum contrato cadastrado.</p>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th>Modelo</th>
                            <th>Vigência</th>
                            <th class="text-end">Valor</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contratos as $c) { ?>
                            <?php [$label, $cor] = $statusLabel[$c['status']] ?? ['—', 'bg-secondary']; ?>
                            <tr>
                                <td><strong><?= e($c['contract_number']) ?></strong></td>
                                <td>
                                    <?= e($c['client_name']) ?>
                                    <?php if (!empty($c['content'])) { ?>
                                        <div><small class="text-success"><i class="bi bi-file-earmark-check"></i> documento gerado</small></div>
                                    <?php } ?>
                                </td>
                                <td><?= e($c['template_name'] ?? '—') ?></td>
                                <td>
                                    <small>
                                        <?= e($c['start_date'] ? date('d/m/Y', strtotime($c['start_date'])) : '—') ?>
                                        →
                                        <?= e($c['end_date'] ? date('d/m/Y', strtotime($c['end_date'])) : '—') ?>
                                    </small>
                                </td>
                                <td class="text-end">R$ <?= e(number_format((float) $c['value'], 2, ',', '.')) ?></td>
                                <td><span class="badge <?= e($cor) ?>"><?= e($label) ?></span></td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <?php if (!empty($c['content'])) { ?>
                                            <a href="/admin/contratos/documento/<?= e($c['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Ver documento">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="/admin/contratos/pdf/<?= e($c['id']) ?>" class="btn btn-sm btn-outline-danger" title="Baixar PDF">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                        <?php } ?>
                                        <a href="/admin/contratos/editar/<?= e($c['id']) ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form action="/admin/contratos/excluir/<?= e($c['id']) ?>" method="POST" class="d-inline"
                                              onsubmit="return confirm('Excluir este contrato? Os arquivos anexos também serão apagados.')">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                        </form>
                                    </div>
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
