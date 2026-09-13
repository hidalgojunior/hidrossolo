<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="mb-0">Modelos de Contrato</h4>
        <p class="text-muted small mb-0">
            Use coringas como <code>{{cliente}}</code>, <code>{{valor}}</code>, <code>{{data_inicio}}</code> e gere novos contratos a partir do modelo.
        </p>
    </div>
    <a href="/admin/contratos/modelos/novo" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Novo modelo</a>
</div>

<?php if (empty($templates)) { ?>
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-file-earmark-ruled fs-1 d-block mb-2"></i>
            Nenhum modelo cadastrado ainda.
            <div class="small mt-1">Crie um modelo para padronizar seus contratos.</div>
        </div>
    </div>
<?php } else { ?>
    <div class="row g-3">
        <?php foreach ($templates as $t) { ?>
            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <h5 class="card-title mb-0"><?= e($t['name']) ?></h5>
                            <?php if ((int) $t['active'] === 1) { ?>
                                <span class="badge bg-success">Ativo</span>
                            <?php } else { ?>
                                <span class="badge bg-secondary">Inativo</span>
                            <?php } ?>
                        </div>

                        <?php if (!empty($t['description'])) { ?>
                            <p class="text-muted small mb-2"><?= e($t['description']) ?></p>
                        <?php } ?>

                        <div class="mb-3">
                            <?php if (empty($t['variaveis'])) { ?>
                                <span class="text-muted small">Nenhuma variável detectada.</span>
                            <?php } else { ?>
                                <?php foreach ($t['variaveis'] as $var) { ?>
                                    <span class="badge bg-light text-dark border me-1 mb-1 font-monospace" style="font-size:.68rem">{{<?= e($var) ?>}}</span>
                                <?php } ?>
                            <?php } ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><?= e($t['usos']) ?> contrato(s) gerado(s)</small>
                            <div class="d-flex gap-1">
                                <a href="/admin/contratos/modelos/editar/<?= e($t['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <form method="POST" action="/admin/contratos/modelos/excluir/<?= e($t['id']) ?>"
                                      onsubmit="return confirm('Excluir este modelo?');">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger" <?= (int) $t['usos'] > 0 ? 'disabled' : '' ?>>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
<?php } ?>
<?php $view->endSection(); ?>
