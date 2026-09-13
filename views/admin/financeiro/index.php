<?php
$view->layout('layouts.admin');

$moeda = static fn(float $v): string => 'R$ ' . number_format($v, 2, ',', '.');
$queryAtual = http_build_query(array_filter([
    'mes' => $filtros['mes'],
    'kind' => $filtros['kind'],
    'status' => $filtros['status'],
    'categoria' => $filtros['categoria'],
    'busca' => $filtros['busca'],
    'vehicle_id' => $filtros['vehicle_id'] ?: null,
], static fn($v): bool => $v !== null && $v !== ''));

$situacaoBadge = static function (array $l): array {
    if ($l['status'] === 'paid') {
        return ['bg-success', 'Baixado'];
    }
    if ($l['status'] === 'cancelled') {
        return ['bg-secondary', 'Cancelado'];
    }
    if (strtotime((string) $l['due_date']) < strtotime(date('Y-m-d'))) {
        return ['bg-danger', 'Em atraso'];
    }

    return ['bg-warning text-dark', 'Pendente'];
};
?>

<?php $view->section('content'); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-0">Fluxo de Caixa</h4>
        <small class="text-muted">Contas a pagar e a receber — <?= e(date('d/m/Y', (int) strtotime($filtros['inicio']))) ?> a <?= e(date('d/m/Y', (int) strtotime($filtros['fim']))) ?></small>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="/admin/financeiro/pdf<?= $queryAtual ? '?' . e($queryAtual) : '' ?>" class="btn btn-outline-danger">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
        <a href="/admin/financeiro/xlsx<?= $queryAtual ? '?' . e($queryAtual) : '' ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i>Excel (XLSX)
        </a>
        <a href="/admin/agenda?mes=<?= e(substr($filtros['mes'], 5, 2)) ?>&ano=<?= e(substr($filtros['mes'], 0, 4)) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-calendar3 me-1"></i>Ver no calendário
        </a>
        <a href="/admin/financeiro/novo" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Novo lançamento
        </a>
    </div>
</div>

<!-- Indicadores -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="border-left:4px solid #16a34a">
            <div>
                <div class="stat-label">A receber</div>
                <div class="stat-value"><?= e($moeda($resumo['a_receber'])) ?></div>
                <small class="text-muted"><?= e((string) $resumo['a_receber_qtd']) ?> lançamento(s)</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="border-left:4px solid #dc2626">
            <div>
                <div class="stat-label">A pagar</div>
                <div class="stat-value"><?= e($moeda($resumo['a_pagar'])) ?></div>
                <small class="text-muted"><?= e((string) $resumo['a_pagar_qtd']) ?> lançamento(s)</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="border-left:4px solid <?= $resumo['previsto'] >= 0 ? '#0891b2' : '#d97706' ?>">
            <div>
                <div class="stat-label">Saldo previsto</div>
                <div class="stat-value"><?= e($moeda($resumo['previsto'])) ?></div>
                <small class="text-muted">Realizado: <?= e($moeda($resumo['realizado'])) ?></small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="border-left:4px solid #d97706">
            <div>
                <div class="stat-label">Em atraso</div>
                <div class="stat-value"><?= e($moeda($resumo['atrasadas'])) ?></div>
                <small class="text-muted"><?= e((string) $resumo['atrasadas_qtd']) ?> conta(s) vencida(s)</small>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/admin/financeiro" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Mês</label>
                <input type="month" name="mes" class="form-control form-control-sm" value="<?= e($filtros['mes']) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Tipo</label>
                <select name="kind" class="form-select form-select-sm">
                    <option value="">Entradas e saídas</option>
                    <option value="income" <?= e($filtros['kind'] === 'income' ? 'selected' : '') ?>>Somente entradas</option>
                    <option value="expense" <?= e($filtros['kind'] === 'expense' ? 'selected' : '') ?>>Somente saídas</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Situação</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <option value="pending" <?= e($filtros['status'] === 'pending' ? 'selected' : '') ?>>Pendentes</option>
                    <option value="paid" <?= e($filtros['status'] === 'paid' ? 'selected' : '') ?>>Baixados</option>
                    <option value="late" <?= e($filtros['status'] === 'late' ? 'selected' : '') ?>>Em atraso</option>
                    <option value="cancelled" <?= e($filtros['status'] === 'cancelled' ? 'selected' : '') ?>>Cancelados</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Ativo</label>
                <select name="vehicle_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($veiculos as $v) { ?>
                        <option value="<?= e($v['id']) ?>" <?= e($filtros['vehicle_id'] == $v['id'] ? 'selected' : '') ?>>
                            <?= e(asset_label($v)) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small mb-1">Buscar</label>
                <input type="search" name="busca" class="form-control form-control-sm" value="<?= e($filtros['busca']) ?>" placeholder="Descrição, parte...">
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="/admin/financeiro" class="btn btn-sm btn-outline-secondary" title="Limpar filtros"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body p-0">
                <?php if (empty($lancamentos)) { ?>
                    <p class="text-muted text-center py-5 mb-0">Nenhum lançamento no período selecionado.</p>
                <?php } else { ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Vencimento</th>
                                    <th>Lançamento</th>
                                    <th>Fornecedor / Cliente</th>
                                    <th class="text-end">Valor</th>
                                    <th>Situação</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lancamentos as $l) { ?>
                                    <?php [$badge, $rotulo] = $situacaoBadge($l); ?>
                                    <tr>
                                        <td>
                                            <strong><?= e(date('d/m/Y', (int) strtotime((string) $l['due_date']))) ?></strong>
                                            <?php if (!empty($l['recurrence']) && $l['recurrence'] !== 'none') { ?>
                                                <div class="small text-muted"><i class="bi bi-arrow-repeat"></i> <?= e(\App\Support\Financeiro::recorrencia($l['recurrence'])) ?></div>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="bi <?= e($l['kind'] === 'income' ? 'bi-arrow-down-circle text-success' : 'bi-arrow-up-circle text-danger') ?>"></i>
                                                <div>
                                                    <strong><?= e($l['description']) ?></strong>
                                                    <div class="small text-muted">
                                                        <i class="bi <?= e(\App\Support\Financeiro::categoriaIcone($l['kind'], $l['category'])) ?>"></i>
                                                        <?= e(\App\Support\Financeiro::categoriaLabel($l['kind'], $l['category'])) ?>
                                                        <?php if (!empty($l['plate']) || !empty($l['model'])) { ?>
                                                            · <?= e(asset_label($l)) ?>
                                                        <?php } ?>
                                                        <?php if (!empty($l['document'])) { ?>
                                                            · Doc. <?= e($l['document']) ?>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="small"><?= e($l['party'] ?: '—') ?></td>
                                        <td class="text-end <?= $l['kind'] === 'income' ? 'text-success' : 'text-danger' ?>">
                                            <strong><?= e($l['kind'] === 'income' ? '+' : '-') ?> <?= e($moeda((float) $l['amount'])) ?></strong>
                                            <?php if (!empty($l['paid_at'])) { ?>
                                                <div class="small text-muted">em <?= e(date('d/m/Y', (int) strtotime((string) $l['paid_at']))) ?></div>
                                            <?php } ?>
                                        </td>
                                        <td><span class="badge <?= e($badge) ?>"><?= e($rotulo) ?></span></td>
                                        <td class="text-end text-nowrap">
                                            <?php if ($l['status'] !== 'paid') { ?>
                                                <button type="button" class="btn btn-sm btn-outline-success"
                                                        data-baixar="<?= e($l['id']) ?>"
                                                        data-descricao="<?= e($l['description']) ?>"
                                                        data-kind="<?= e($l['kind']) ?>"
                                                        data-recorrencia="<?= e($l['recurrence']) ?>"
                                                        title="<?= $l['kind'] === 'income' ? 'Registrar recebimento' : 'Registrar pagamento' ?>">
                                                    <i class="bi bi-check2-circle"></i>
                                                </button>
                                            <?php } else { ?>
                                                <form action="/admin/financeiro/status/<?= e($l['id']) ?>" method="POST" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="acao" value="reabrir">
                                                    <input type="hidden" name="redirect" value="/admin/financeiro?<?= e($queryAtual) ?>">
                                                    <button class="btn btn-sm btn-outline-secondary" title="Reabrir"><i class="bi bi-arrow-counterclockwise"></i></button>
                                                </form>
                                            <?php } ?>
                                            <a href="/admin/financeiro/editar/<?= e($l['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                            <form action="/admin/financeiro/excluir/<?= e($l['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Excluir este lançamento?')">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-sm btn-outline-danger" title="Excluir"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header"><strong>Fluxo mensal <?= e(date('Y', (int) strtotime($filtros['inicio']))) ?></strong></div>
            <div class="card-body">
                <canvas id="graficoFluxo" height="220"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>Resumo por categoria</strong></div>
            <div class="card-body">
                <?php if (empty($porCategoria)) { ?>
                    <p class="text-muted small mb-0">Sem lançamentos no período.</p>
                <?php } else { ?>
                    <ul class="driver-extras" style="font-size:.82rem">
                        <?php foreach ($porCategoria as $c) { ?>
                            <li>
                                <i class="bi <?= e(\App\Support\Financeiro::categoriaIcone($c['kind'], $c['category'])) ?>"></i>
                                <?= e(\App\Support\Financeiro::categoriaLabel($c['kind'], $c['category'])) ?>
                                <span class="<?= $c['kind'] === 'income' ? 'text-success' : 'text-danger' ?>">
                                    <?= e($c['kind'] === 'income' ? '+' : '-') ?> <?= e($moeda((float) $c['total'])) ?>
                                </span>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de baixa -->
<div class="modal fade" id="baixaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content" id="baixaForm">
            <?= csrf_field() ?>
            <input type="hidden" name="acao" value="baixar">
            <input type="hidden" name="redirect" value="/admin/financeiro?<?= e($queryAtual) ?>">
            <div class="modal-header">
                <h5 class="modal-title">Baixar lançamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3" id="baixaDescricao"></p>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Data</label>
                        <input type="date" name="paid_at" class="form-control" value="<?= e(date('Y-m-d')) ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Forma</label>
                        <select name="payment_method" class="form-select">
                            <option value="">—</option>
                            <?php foreach ($formasPagamento as $chave => $rotulo) { ?>
                                <option value="<?= e($chave) ?>"><?= e($rotulo) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-12 d-none" id="baixaRecorrencia">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="gerar_proximo" value="1" id="gerarProximo" checked>
                            <label class="form-check-label" for="gerarProximo">
                                Gerar automaticamente o próximo lançamento desta recorrência
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-success">Confirmar baixa</button>
            </div>
        </form>
    </div>
</div>
<?php $view->endSection(); ?>

<?php $view->section('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    var fluxo = <?= json_encode($fluxoMensal, JSON_UNESCAPED_UNICODE) ?>;

    var ctx = document.getElementById('graficoFluxo');
    if (ctx && window.Chart) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: fluxo.map(function (m) { return m.label.substr(0, 3); }),
                datasets: [
                    { label: 'Entradas', data: fluxo.map(function (m) { return m.entradas; }), backgroundColor: '#16a34a' },
                    { label: 'Saídas', data: fluxo.map(function (m) { return m.saidas; }), backgroundColor: '#dc2626' }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: function (v) { return 'R$ ' + Number(v).toLocaleString('pt-BR'); } } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    document.querySelectorAll('[data-baixar]').forEach(function (botao) {
        botao.addEventListener('click', function () {
            var form = document.getElementById('baixaForm');
            form.action = '/admin/financeiro/status/' + botao.dataset.baixar;
            document.getElementById('baixaDescricao').textContent =
                (botao.dataset.kind === 'income' ? 'Registrar recebimento de: ' : 'Registrar pagamento de: ') + botao.dataset.descricao;
            document.getElementById('baixaRecorrencia').classList.toggle('d-none', botao.dataset.recorrencia === 'none');

            var modal = new bootstrap.Modal(document.getElementById('baixaModal'));
            modal.show();
        });
    });
})();
</script>
<?php $view->endSection(); ?>
