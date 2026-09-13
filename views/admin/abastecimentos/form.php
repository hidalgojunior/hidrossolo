<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<h4 class="mb-4">Novo Abastecimento</h4>

<div class="card">
    <div class="card-body">
            <form method="POST" action="/admin/abastecimentos/novo">

            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Veículo / Equipamento *</label>
                    <select name="vehicle_id" class="form-select" required>
                        <option value="">Selecione...</option>
                        <optgroup label="Veículos">
                            <?php foreach ($veiculos as $v) { if (($v['category'] ?? 'vehicle') === 'vehicle') { ?>
                                <option value="<?= e($v['id']) ?>" <?= e($veiculoId == $v['id'] ? 'selected' : '') ?>><?= e(asset_label($v)) ?></option>
                            <?php } } ?>
                        </optgroup>
                        <optgroup label="Equipamentos">
                            <?php foreach ($veiculos as $v) { if (($v['category'] ?? 'vehicle') === 'equipment') { ?>
                                <option value="<?= e($v['id']) ?>" <?= e($veiculoId == $v['id'] ? 'selected' : '') ?>><?= e(asset_label($v)) ?></option>
                            <?php } } ?>
                        </optgroup>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data *</label>
                    <input type="date" name="fuel_date" class="form-control" required value="<?= e(date('Y-m-d')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">KM no abastecimento</label>
                    <input type="number" name="km_at_refuel" class="form-control" placeholder="Opcional">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Litros *</label>
                    <input type="text" name="liters" class="form-control" required placeholder="0,00">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Valor Total (R$) *</label>
                    <input type="text" name="cost" class="form-control" required placeholder="0,00">
                </div>
                <div class="col-12">
                    <hr class="my-2">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div>
                            <strong><i class="bi bi-receipt me-1"></i>Outras despesas deste abastecimento</strong>
                            <div class="form-text mb-0">Ex.: Arla 32, lavagem, pedágio, óleo, aditivo.</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-add-row>
                            <i class="bi bi-plus-lg"></i> Adicionar despesa
                        </button>
                    </div>
                    <div data-rows></div>
                    <template id="extraRowTemplate">
                        <div class="driver-repeater-row" data-row>
                            <input type="text" name="extras[__i__][description]" class="form-control form-control-sm" placeholder="Descrição da despesa">
                            <input type="text" name="extras[__i__][amount]" class="form-control form-control-sm" placeholder="Valor R$" data-money>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-row title="Remover"><i class="bi bi-x-lg"></i></button>
                        </div>
                    </template>
                    <div class="text-end mt-2">
                        <strong>Total de despesas extras: <span data-extras-total>R$ 0,00</span></strong>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Registrar Abastecimento</button>
                    <a href="/admin/abastecimentos" class="btn btn-outline-secondary">Cancelar</a>
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
    const tpl = document.getElementById('extraRowTemplate');
    const totalEl = document.querySelector('[data-extras-total]');
    let seq = 0;

    function toNumber(value) {
        const n = parseFloat(String(value).replace(/\./g, '').replace(',', '.'));
        return isNaN(n) ? 0 : n;
    }

    function atualizarTotal() {
        let total = 0;
        rows.querySelectorAll('input[name$="[amount]"]').forEach(function (input) {
            total += toNumber(input.value);
        });
        totalEl.textContent = 'R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    document.querySelector('[data-add-row]')?.addEventListener('click', function () {
        rows.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__i__/g, 'n' + (seq++)));
        atualizarTotal();
    });

    document.addEventListener('input', function (ev) {
        if (ev.target.matches('input[name$="[amount]"]')) {
            atualizarTotal();
        }
    });

    document.addEventListener('click', function (ev) {
        const btn = ev.target.closest('[data-remove-row]');
        if (btn) {
            btn.closest('[data-row]').remove();
            atualizarTotal();
        }
    });
})();
</script>
<?php $view->endSection(); ?>
