<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<?php
$statusInfo = [
    'new' => ['Nova', 'bg-danger'],
    'in_progress' => ['Em andamento', 'bg-warning'],
    'answered' => ['Respondida', 'bg-success'],
    'denied' => ['Negada', 'bg-secondary'],
];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="mb-0">Solicitações LGPD</h4>
        <p class="text-muted small mb-0">
            Pedidos enviados pelos titulares pela <a href="/lgpd" target="_blank" rel="noopener">Central LGPD</a>.
            Prazo legal de resposta: 15 dias.
        </p>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-inbox"></i></div>
            <div><div class="stat-value"><?= e((int) ($totais['total'] ?? 0)) ?></div><div class="stat-label">Total recebidas</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100">
            <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-exclamation-circle"></i></div>
            <div><div class="stat-value"><?= e((int) ($totais['novos'] ?? 0)) ?></div><div class="stat-label">Aguardando</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-hourglass-split"></i></div>
            <div><div class="stat-value"><?= e((int) ($totais['em_andamento'] ?? 0)) ?></div><div class="stat-label">Em andamento</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-check2-circle"></i></div>
            <div><div class="stat-value"><?= e((int) ($totais['respondidos'] ?? 0)) ?></div><div class="stat-label">Respondidas</div></div>
        </div>
    </div>
</div>

<?php if ((int) ($totais['atrasados'] ?? 0) > 0) { ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <strong><?= e((int) $totais['atrasados']) ?> solicitação(ões)</strong> aguardando há mais de 14 dias — risco de descumprimento do prazo legal.
    </div>
<?php } ?>

<div class="media-filters mb-3">
    <a href="?status=all" class="media-chip <?= $status === 'all' ? 'is-active' : '' ?>">Todas</a>
    <?php foreach ($statusInfo as $key => [$label, $cor]) { ?>
        <a href="?status=<?= e($key) ?>" class="media-chip <?= $status === $key ? 'is-active' : '' ?>"><?= e($label) ?></a>
    <?php } ?>
</div>

<?php if (empty($solicitacoes)) { ?>
    <div class="card"><div class="card-body text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>Nenhuma solicitação registrada.
    </div></div>
<?php } else { ?>
    <div class="row g-3">
        <?php foreach ($solicitacoes as $s) { ?>
            <?php [$label, $cor] = $statusInfo[$s['status']] ?? ['—', 'bg-secondary']; ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <span class="badge <?= e($cor) ?> me-2"><?= e($label) ?></span>
                                <strong><?= e($s['name']) ?></strong>
                                <small class="text-muted">· <?= e($s['email']) ?>
                                    <?php if (!empty($s['document'])) { ?> · <?= e($s['document']) ?><?php } ?>
                                </small>
                            </div>
                            <div class="text-end small text-muted">
                                <div class="font-monospace"><?= e($s['protocol']) ?></div>
                                <div><?= e(date('d/m/Y H:i', strtotime($s['created_at']))) ?></div>
                            </div>
                        </div>

                        <p class="mb-2">
                            <strong>Tipo:</strong> <?= e($tipos[$s['request_type']] ?? $s['request_type']) ?>
                        </p>
                        <p class="text-muted small mb-3" style="white-space:pre-line"><?= e($s['message'] ?? '') ?></p>

                        <form method="POST" action="/admin/lgpd/responder/<?= e($s['id']) ?>" class="row g-2 align-items-end">
                            <?= csrf_field() ?>
                            <div class="col-md-2">
                                <label class="form-label small">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <?php foreach ($statusInfo as $key => [$l2, $c2]) { ?>
                                        <option value="<?= e($key) ?>" <?= $s['status'] === $key ? 'selected' : '' ?>><?= e($l2) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small">Resposta ao titular</label>
                                <input type="text" name="response" class="form-control form-control-sm"
                                       value="<?= e($s['response'] ?? '') ?>" placeholder="Resumo do que foi respondido">
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary btn-sm w-100">Salvar</button>
                            </div>
                        </form>

                        <?php if (!empty($s['answered_at'])) { ?>
                            <div class="small text-muted mt-2">
                                Respondida em <?= e(date('d/m/Y H:i', strtotime($s['answered_at']))) ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
<?php } ?>
<?php $view->endSection(); ?>
