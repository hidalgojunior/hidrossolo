<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="mb-0">Controle de Manutenções</h4>
    <div class="d-flex flex-wrap gap-2">
        <a href="/admin/manutencoes/pdf<?= $veiculoId ? '?veiculo_id=' . e($veiculoId) : '' ?>" class="btn btn-outline-danger">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
        <a href="/admin/manutencoes/xlsx<?= $veiculoId ? '?veiculo_id=' . e($veiculoId) : '' ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i>Excel
        </a>
        <a href="/admin/manutencoes/novo<?= e($veiculoId ? '?veiculo_id=' . $veiculoId : '') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Nova Manutenção
        </a>
    </div>
</div>

<!-- Filtro por veículo -->
<div class="mb-3">
    <select class="form-select d-inline-block w-auto" onchange="location = this.value ? '?veiculo_id=' + this.value : '?'">
        <option value="">Todos os veículos</option>
        <?php foreach ($veiculos as $v) { ?>
            <option value="<?= e($v['id']) ?>" <?= e($veiculoId == $v['id'] ? 'selected' : '') ?>>
                <?= e(asset_label($v)) ?>
            </option>
        <?php } ?>
    </select>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($manutencoes)) { ?>
            <p class="text-muted text-center py-5">Nenhuma manutenção registrada.</p>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Veículo</th>
                            <th>Serviço</th>
                            <th>Data</th>
                            <th>Valor</th>
                            <th>Próxima revisão</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($manutencoes as $m) { ?>
                        <?php $servico = ($servicos ?? [])[$m['service_category'] ?? 'other'] ?? null; ?>
                        <?php $itens = $itensPorManutencao[(int) $m['id']] ?? []; ?>
                        <tr>
                            <td>
                                <strong><?= e($m['plate']) ?></strong><br>
                                <small class="text-muted"><?= e($m['brand']) ?> <?= e($m['model']) ?></small>
                            </td>
                            <td>
                                <?php if ($servico) { ?>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi <?= e($servico['icon']) ?> me-1"></i><?= e($servico['label']) ?>
                                    </span>
                                <?php } ?>
                                <?php if ($itens !== []) { ?>
                                    <ul class="driver-extras mt-2 mb-0">
                                        <?php foreach ($itens as $item) { ?>
                                            <li>
                                                <?= e($item['description']) ?>
                                                <?php if ((float) $item['quantity'] > 1 || (float) $item['unit_cost'] > 0) { ?>
                                                    <span class="text-muted">
                                                        (<?= e(rtrim(rtrim(number_format((float) $item['quantity'], 2, ',', '.'), '0'), ',')) ?> x R$ <?= e(number_format((float) $item['unit_cost'], 2, ',', '.')) ?>)
                                                    </span>
                                                <?php } ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                <?php } else { ?>
                                    <div class="small text-muted mt-1"><?= e(str_limit($m['description'] ?? '', 60)) ?></div>
                                <?php } ?>
                            </td>
                            <td>
                                <?= e(date('d/m/Y', strtotime($m['maintenance_date']))) ?><br>
                                <?php if ($m['type'] === 'preventive') { ?>
                                    <span class="badge bg-info">Preventiva</span>
                                <?php } else { ?>
                                    <span class="badge bg-warning text-dark">Corretiva</span>
                                <?php } ?>
                                <small class="text-muted d-block mt-1"><?= e(number_format((float) ($m['km_at_maintenance'] ?? 0), 0, ',', '.')) ?> km</small>
                            </td>
                            <td>R$ <?= e(number_format((float) $m['cost'], 2, ',', '.')) ?></td>
                            <td>
                                <?php if (!empty($m['next_review_date'])) { ?>
                                    <?php $dias = (int) floor((strtotime($m['next_review_date']) - strtotime(date('Y-m-d'))) / 86400); ?>
                                    <span class="agenda-alert-badge <?= $dias <= 7 ? 'd7' : ($dias <= 15 ? 'd15' : 'd30') ?>">
                                        <i class="bi bi-calendar-check"></i>
                                        <?= e(date('d/m/Y', strtotime($m['next_review_date']))) ?>
                                    </span>
                                <?php } else { ?>
                                    <span class="text-muted">—</span>
                                <?php } ?>
                            </td>
                            <td class="text-end">
                                <a href="/admin/manutencoes/editar/<?= e($m['id']) ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form action="/admin/manutencoes/excluir/<?= e($m['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta manutenção?')">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="btn btn-sm btn-outline-danger">Excluir</button>
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
<?php $view->endSection(); ?>
