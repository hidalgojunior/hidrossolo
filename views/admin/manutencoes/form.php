<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<h4 class="mb-4"><?= e($manutencao ? 'Editar Manutenção' : 'Nova Manutenção') ?></h4>

<div class="card">
    <div class="card-body">
            <form method="POST" action="<?= e($manutencao ? '/admin/manutencoes/editar/' . $manutencao['id'] : '/admin/manutencoes/novo') ?>">

            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Veículo / Equipamento *</label>
                    <select name="vehicle_id" class="form-select" required>
                        <option value="">Selecione...</option>
                        <optgroup label="Veículos">
                            <?php foreach ($veiculos as $v) { if (($v['category'] ?? 'vehicle') === 'vehicle') { ?>
                                <option value="<?= e($v['id']) ?>" <?= e(($manutencao['vehicle_id'] ?? $veiculoId) == $v['id'] ? 'selected' : '') ?>>
                                    <?= e(asset_label($v)) ?>
                                </option>
                            <?php } } ?>
                        </optgroup>
                        <optgroup label="Equipamentos">
                            <?php foreach ($veiculos as $v) { if (($v['category'] ?? 'vehicle') === 'equipment') { ?>
                                <option value="<?= e($v['id']) ?>" <?= e(($manutencao['vehicle_id'] ?? $veiculoId) == $v['id'] ? 'selected' : '') ?>>
                                    <?= e(asset_label($v)) ?>
                                </option>
                            <?php } } ?>
                        </optgroup>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo *</label>
                    <select name="type" class="form-select" required>
                        <option value="preventive" <?= e(($manutencao['type'] ?? '') === 'preventive' ? 'selected' : '') ?>>Preventiva</option>
                        <option value="corrective" <?= e(($manutencao['type'] ?? '') === 'corrective' ? 'selected' : '') ?>>Corretiva</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data *</label>
                    <input type="text" name="maintenance_date" class="form-control" required data-date-br
                           placeholder="dd/mm/aaaa" value="<?= e(data_iso_para_br($manutencao['maintenance_date'] ?? date('Y-m-d'))) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">O que foi feito? *</label>
                    <select name="service_category" class="form-select" required>
                        <?php foreach (($servicos ?? []) as $chave => $servico) { ?>
                            <option value="<?= e($chave) ?>" <?= e(($manutencao['service_category'] ?? 'revision') === $chave ? 'selected' : '') ?>>
                                <?= e($servico['label']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Próxima revisão</label>
                    <input type="text" name="next_review_date" class="form-control" data-date-br
                           placeholder="dd/mm/aaaa" value="<?= e(data_iso_para_br($manutencao['next_review_date'] ?? '')) ?>">
                    <div class="form-text">Gera avisos automáticos em 30, 15 e 7 dias.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">KM no momento</label>
                    <input type="number" name="km_at_maintenance" class="form-control" value="<?= e($manutencao['km_at_maintenance'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Valor (R$) *</label>
                    <input type="text" name="cost" class="form-control" required value="<?= e(isset($manutencao) ? number_format($manutencao['cost'], 2, ',', '.') : '') ?>" placeholder="0,00">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Oficina</label>
                    <input type="text" name="workshop" class="form-control" value="<?= e($manutencao['workshop'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <hr class="my-2">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <strong><i class="bi bi-list-check me-1"></i>Itens trocados / serviços executados</strong>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-add-row>
                            <i class="bi bi-plus-lg"></i> Adicionar item
                        </button>
                    </div>
                    <div data-rows>
                        <?php foreach (($itens ?? []) as $item) { ?>
                            <div class="driver-repeater-row" data-row>
                                <input type="text" name="items[<?= e((int) $item['id']) ?>][description]" class="form-control form-control-sm" value="<?= e($item['description']) ?>" placeholder="Item">
                                <input type="text" name="items[<?= e((int) $item['id']) ?>][quantity]" class="form-control form-control-sm" value="<?= e(rtrim(rtrim(number_format((float) $item['quantity'], 2, ',', '.'), '0'), ',')) ?>" placeholder="Qtd">
                                <input type="text" name="items[<?= e((int) $item['id']) ?>][unit_cost]" class="form-control form-control-sm" value="<?= e(number_format((float) $item['unit_cost'], 2, ',', '.')) ?>" placeholder="Unit. R$">
                                <button type="button" class="btn btn-sm btn-outline-danger" data-remove-row title="Remover"><i class="bi bi-x-lg"></i></button>
                            </div>
                        <?php } ?>
                    </div>
                    <template id="itemRowTemplate">
                        <div class="driver-repeater-row" data-row>
                            <input type="text" name="items[__i__][description]" class="form-control form-control-sm" placeholder="Ex.: Pneu traseiro direito">
                            <input type="text" name="items[__i__][quantity]" class="form-control form-control-sm" placeholder="Qtd">
                            <input type="text" name="items[__i__][unit_cost]" class="form-control form-control-sm" placeholder="Unit. R$">
                            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-row title="Remover"><i class="bi bi-x-lg"></i></button>
                        </div>
                    </template>
                </div>
                <div class="col-12">
                    <label class="form-label">Descrição</label>
                    <textarea name="description" class="form-control" rows="2"><?= e($manutencao['description'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Observações</label>
                    <textarea name="notes" class="form-control" rows="2"><?= e($manutencao['notes'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="/admin/manutencoes" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $view->endSection(); ?>

<?php $view->section('scripts'); ?>
<script>
(function () {
    const rows = document.querySelector('[data-rows]');
    const tpl = document.getElementById('itemRowTemplate');
    let seq = Date.now();

    document.querySelector('[data-add-row]')?.addEventListener('click', function () {
        const html = tpl.innerHTML.replace(/__i__/g, 'n' + (seq++));
        rows.insertAdjacentHTML('beforeend', html);
    });

    document.addEventListener('click', function (ev) {
        const btn = ev.target.closest('[data-remove-row]');
        if (btn) {
            btn.closest('[data-row]').remove();
        }
    });
})();
</script>
<?php $view->endSection(); ?>
