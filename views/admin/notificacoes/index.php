<?php
$view->layout('layouts.admin');

$queryAtual = $filtro !== 'todas' ? '?filtro=' . $filtro : '';

$quando = static function (?string $data): string {
    if (empty($data)) {
        return '';
    }

    $timestamp = strtotime($data);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'agora mesmo';
    }
    if ($diff < 3600) {
        return 'há ' . (int) floor($diff / 60) . ' min';
    }
    if ($diff < 86400) {
        return 'há ' . (int) floor($diff / 3600) . ' h';
    }
    if ($diff < 604800) {
        return 'há ' . (int) floor($diff / 86400) . ' dia(s)';
    }

    return date('d/m/Y \à\s H:i', $timestamp);
};

$estilo = static function (string $tipo): array {
    return match ($tipo) {
        'warning' => ['bi-exclamation-triangle-fill', '#d97706', '#fef3c7'],
        'success' => ['bi-check-circle-fill', '#16a34a', '#dcfce7'],
        'error' => ['bi-x-octagon-fill', '#dc2626', '#fee2e2'],
        default => ['bi-info-circle-fill', '#0891b2', '#e0f2fe'],
    };
};
?>

<?php $view->section('content'); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-0">Notificações</h4>
        <small class="text-muted">
            <?= e((string) $resumo['nao_lidas']) ?> não lida(s) de <?= e((string) $resumo['total']) ?> ·
            <?= e((string) $resumo['alertas']) ?> alerta(s)
        </small>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <form action="/admin/notificacoes/gerar-avisos" method="POST" class="d-inline">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect" value="/admin/notificacoes<?= e($queryAtual) ?>">
            <button class="btn btn-outline-primary">
                <i class="bi bi-arrow-repeat me-1"></i>Gerar avisos de manutenção
            </button>
        </form>
        <form action="/admin/notificacoes/ler-todas" method="POST" class="d-inline">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect" value="/admin/notificacoes<?= e($queryAtual) ?>">
            <button class="btn btn-outline-secondary" <?= $resumo['nao_lidas'] === 0 ? 'disabled' : '' ?>>
                <i class="bi bi-check2-all me-1"></i>Marcar todas como lidas
            </button>
        </form>
        <form action="/admin/notificacoes/limpar" method="POST" class="d-inline" onsubmit="return confirm('Remover todas as notificações já lidas?')">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect" value="/admin/notificacoes<?= e($queryAtual) ?>">
            <button class="btn btn-outline-danger">
                <i class="bi bi-trash3 me-1"></i>Limpar lidas
            </button>
        </form>
    </div>
</div>

<?php if ($resumo['contas_atrasadas'] > 0) { ?>
    <div class="alert alert-danger d-flex flex-wrap align-items-center gap-2">
        <i class="bi bi-exclamation-octagon-fill"></i>
        <span>
            Existem <strong><?= e((string) $resumo['contas_atrasadas']) ?> conta(s) em atraso</strong>
            totalizando <strong>R$ <?= e(number_format((float) $resumo['contas_atrasadas_valor'], 2, ',', '.')) ?></strong>.
        </span>
        <a href="/admin/financeiro?status=late" class="btn btn-sm btn-danger ms-auto">Ver contas vencidas</a>
    </div>
<?php } ?>

<?php if ($resumo['contas_semana'] > 0) { ?>
    <div class="alert alert-warning d-flex flex-wrap align-items-center gap-2">
        <i class="bi bi-calendar-event"></i>
        <span><strong><?= e((string) $resumo['contas_semana']) ?> conta(s)</strong> vencem nos próximos 7 dias.</span>
        <a href="/admin/financeiro" class="btn btn-sm btn-warning ms-auto">Abrir fluxo de caixa</a>
    </div>
<?php } ?>

<!-- Filtros -->
<ul class="nav nav-pills mb-3 gap-1">
    <?php foreach (['todas' => 'Todas', 'nao_lidas' => 'Não lidas', 'lidas' => 'Lidas'] as $chave => $rotulo) { ?>
        <li class="nav-item">
            <a class="nav-link <?= e($filtro === $chave ? 'active' : '') ?>"
               href="/admin/notificacoes<?= $chave === 'todas' ? '' : '?filtro=' . $chave ?>">
                <?= e($rotulo) ?>
            </a>
        </li>
    <?php } ?>
</ul>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($notificacoes)) { ?>
            <div class="text-center py-5">
                <i class="bi bi-bell-slash" style="font-size:2.2rem;color:#94a3b8"></i>
                <p class="text-muted mt-3 mb-0">
                    <?= $filtro === 'nao_lidas' ? 'Nenhuma notificação pendente. Tudo em dia!' : 'Nenhuma notificação por aqui.' ?>
                </p>
            </div>
        <?php } else { ?>
            <ul class="list-unstyled mb-0">
                <?php foreach ($notificacoes as $n) { ?>
                    <?php [$icone, $cor, $fundo] = $estilo((string) $n['type']); ?>
                    <?php $lida = !empty($n['read_at']); ?>
                    <li class="d-flex gap-3 align-items-start p-3 border-bottom <?= $lida ? '' : 'bg-light' ?>">
                        <span class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                              style="width:38px;height:38px;border-radius:10px;background:<?= e($fundo) ?>;color:<?= e($cor) ?>">
                            <i class="bi <?= e($icone) ?>"></i>
                        </span>

                        <div class="flex-grow-1" style="min-width:0">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <strong><?= e($n['title']) ?></strong>
                                <?php if (!$lida) { ?>
                                    <span class="badge bg-primary">Nova</span>
                                <?php } ?>
                                <small class="text-muted"><?= e($quando((string) $n['created_at'])) ?></small>
                            </div>

                            <p class="mb-2 small text-muted"><?= nl2br(e($n['message'])) ?></p>

                            <div class="d-flex flex-wrap gap-2">
                                <a href="/admin/notificacoes/abrir/<?= e($n['id']) ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
                                </a>

                                <form action="/admin/notificacoes/ler/<?= e($n['id']) ?>" method="POST" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="redirect" value="/admin/notificacoes<?= e($queryAtual) ?>">
                                    <input type="hidden" name="marcar" value="<?= $lida ? 'nao_lida' : 'lida' ?>">
                                    <button class="btn btn-sm btn-outline-secondary">
                                        <i class="bi <?= $lida ? 'bi-envelope' : 'bi-envelope-open' ?> me-1"></i>
                                        <?= $lida ? 'Marcar como não lida' : 'Marcar como lida' ?>
                                    </button>
                                </form>

                                <form action="/admin/notificacoes/excluir/<?= e($n['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Remover esta notificação?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="redirect" value="/admin/notificacoes<?= e($queryAtual) ?>">
                                    <button class="btn btn-sm btn-outline-danger" title="Remover"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
    </div>
</div>

<?php if ($total > count($notificacoes)) { ?>
    <p class="text-muted small mt-3 mb-0">Exibindo as <?= e((string) count($notificacoes)) ?> notificações mais recentes de <?= e((string) $total) ?>.</p>
<?php } ?>
<?php $view->endSection(); ?>
