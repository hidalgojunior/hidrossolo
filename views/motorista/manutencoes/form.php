<?php
$view->layout('layouts.motorista');

use App\Support\FleetCatalog;

$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

$veiculos = $veiculos ?? [];
$vehiclesList = array_values(array_filter($veiculos, static fn(array $v): bool => ($v['category'] ?? 'vehicle') === 'vehicle'));
$equipmentList = array_values(array_filter($veiculos, static fn(array $v): bool => ($v['category'] ?? '') === 'equipment'));
$servicos = $servicos ?? FleetCatalog::servicos();
$sugestoes = $sugestoes ?? [];
$itensOld = is_array($old['items'] ?? null) ? $old['items'] : [];
$categoriaOld = (string) ($old['service_category'] ?? 'revision');
?>

<?php $view->section('content'); ?>

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="/motorista/manutencoes" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0">Nova manutenção</h5>
</div>

<form method="POST" action="/motorista/manutencoes/nova" class="driver-form driver-card" data-offline-queue>
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label" for="vehicle_id">Veículo / Equipamento *</label>
        <select name="vehicle_id" id="vehicle_id" class="form-select" required>
            <option value="">Selecione...</option>
            <?php if ($vehiclesList !== []) { ?>
                <optgroup label="Veículos">
                    <?php foreach ($vehiclesList as $v) { ?>
                        <option value="<?= e($v['id']) ?>" data-km="<?= e($v['current_km'] ?? '') ?>"
                                <?= (string) ($old['vehicle_id'] ?? '') === (string) $v['id'] ? 'selected' : '' ?>>
                            <?= e(asset_label($v)) ?>
                        </option>
                    <?php } ?>
                </optgroup>
            <?php } ?>
            <?php if ($equipmentList !== []) { ?>
                <optgroup label="Equipamentos / Geradores">
                    <?php foreach ($equipmentList as $v) { ?>
                        <option value="<?= e($v['id']) ?>" data-km="<?= e($v['current_km'] ?? '') ?>"
                                <?= (string) ($old['vehicle_id'] ?? '') === (string) $v['id'] ? 'selected' : '' ?>>
                            <?= e(asset_label($v)) ?>
                        </option>
                    <?php } ?>
                </optgroup>
            <?php } ?>
        </select>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <label class="form-label" for="service_category">O que foi feito? *</label>
            <select name="service_category" id="service_category" class="form-select" required>
                <?php foreach ($servicos as $chave => $info) { ?>
                    <option value="<?= e($chave) ?>" <?= $categoriaOld === $chave ? 'selected' : '' ?>>
                        <?= e($info['label']) ?>
                    </option>
                <?php } ?>
            </select>
            <div class="driver-chip-row mt-2" data-sugestoes-categoria>
                <button type="button" class="driver-chip" data-quick="tires">
                    <i class="bi bi-circle-square"></i> Troca de pneus
                </button>
                <button type="button" class="driver-chip" data-quick="oil">
                    <i class="bi bi-droplet-half"></i> Óleo e filtros
                </button>
                <button type="button" class="driver-chip" data-quick="brakes">
                    <i class="bi bi-exclamation-octagon"></i> Freios
                </button>
                <button type="button" class="driver-chip" data-quick="revision">
                    <i class="bi bi-clipboard-check"></i> Revisão
                </button>
            </div>
        </div>

        <div class="col-6">
            <label class="form-label" for="type">Tipo *</label>
            <select name="type" id="type" class="form-select" required>
                <option value="preventive" <?= ($old['type'] ?? '') === 'preventive' ? 'selected' : '' ?>>Preventiva</option>
                <option value="corrective" <?= ($old['type'] ?? '') === 'corrective' ? 'selected' : '' ?>>Corretiva</option>
            </select>
        </div>
        <div class="col-6">
            <label class="form-label" for="maintenance_date">Data *</label>
            <input type="date" name="maintenance_date" id="maintenance_date" class="form-control" required
                   value="<?= e($old['maintenance_date'] ?? date('Y-m-d')) ?>">
        </div>

        <div class="col-12">
            <label class="form-label" for="workshop">Oficina / Fornecedor</label>
            <input type="text" name="workshop" id="workshop" class="form-control" maxlength="255"
                   placeholder="Onde o serviço foi feito" value="<?= e($old['workshop'] ?? '') ?>">
        </div>

        <div class="col-6">
            <label class="form-label" for="km_at_maintenance">KM atual</label>
            <input type="number" min="0" name="km_at_maintenance" id="km_at_maintenance" class="form-control"
                   placeholder="Opcional" value="<?= e($old['km_at_maintenance'] ?? '') ?>">
        </div>
        <div class="col-6">
            <label class="form-label" for="hours_at_maintenance">Horímetro (h)</label>
            <input type="text" inputmode="decimal" name="hours_at_maintenance" id="hours_at_maintenance"
                   class="form-control" placeholder="Opcional" value="<?= e($old['hours_at_maintenance'] ?? '') ?>">
        </div>

        <div class="col-6">
            <label class="form-label" for="cost">Valor gasto (R$) *</label>
            <input type="text" inputmode="decimal" name="cost" id="cost" class="form-control" required
                   placeholder="0,00" value="<?= e($old['cost'] ?? '') ?>">
        </div>
        <div class="col-6">
            <label class="form-label" for="next_review_date">Próxima revisão</label>
            <input type="date" name="next_review_date" id="next_review_date" class="form-control"
                   value="<?= e($old['next_review_date'] ?? '') ?>">
            <small class="text-muted">Gera avisos automáticos</small>
        </div>

        <div class="col-12">
            <label class="form-label" for="description">Resumo do serviço *</label>
            <textarea name="description" id="description" class="form-control" rows="2" required
                      placeholder="Ex.: troca dos 2 pneus traseiros"><?= e($old['description'] ?? '') ?></textarea>
        </div>
    </div>

    <!-- Itens trocados -->
    <div class="driver-repeater mt-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong><i class="bi bi-list-check me-1"></i> Itens trocados / serviços</strong>
            <button type="button" class="btn btn-sm btn-outline-primary" data-add-row>
                <i class="bi bi-plus-lg"></i> Adicionar
            </button>
        </div>
        <p class="text-muted small mb-2" data-hint>
            Descreva cada item (peças, pneus, fluidos). A quantidade e o valor são opcionais.
        </p>

        <div data-rows>
            <?php foreach ($itensOld as $i => $item) { ?>
                <div class="driver-repeater-row" data-row>
                    <input type="text" name="items[<?= e($i) ?>][description]" class="form-control form-control-sm"
                           maxlength="255" placeholder="Item" value="<?= e($item['description'] ?? '') ?>">
                    <input type="text" inputmode="decimal" name="items[<?= e($i) ?>][quantity]"
                           class="form-control form-control-sm qty" placeholder="Qtd" value="<?= e($item['quantity'] ?? '') ?>">
                    <input type="text" inputmode="decimal" name="items[<?= e($i) ?>][unit_cost]"
                           class="form-control form-control-sm" placeholder="Unit. R$" value="<?= e($item['unit_cost'] ?? '') ?>">
                    <button type="button" class="btn btn-sm btn-outline-danger" data-remove-row aria-label="Remover">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            <?php } ?>
        </div>

        <div class="text-end small text-muted mt-1">
            Total dos itens: <strong data-total-itens>—</strong>
        </div>
    </div>

    <div class="mb-0 mt-3">
        <label class="form-label" for="notes">Observações</label>
        <textarea name="notes" id="notes" class="form-control" rows="2"><?= e($old['notes'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary driver-submit mt-3">
        <i class="bi bi-check-lg me-1"></i> Salvar manutenção
    </button>
</form>

<template id="itemRowTemplate">
    <div class="driver-repeater-row" data-row>
        <input type="text" class="form-control form-control-sm" maxlength="255" placeholder="Item" data-name="description">
        <input type="text" inputmode="decimal" class="form-control form-control-sm qty" placeholder="Qtd" data-name="quantity">
        <input type="text" inputmode="decimal" class="form-control form-control-sm" placeholder="Unit. R$" data-name="unit_cost">
        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-row aria-label="Remover">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</template>

<?php $view->endSection(); ?>

<?php $view->section('scripts'); ?>
<script>
(function () {
    var form = document.querySelector('[data-offline-queue]');
    var rowsBox = form.querySelector('[data-rows]');
    var template = document.getElementById('itemRowTemplate');
    var hint = form.querySelector('[data-hint]');
    var sugestoes = <?= json_attr($sugestoes) ?>;
    var categoria = document.getElementById('service_category');

    function renumber() {
        rowsBox.querySelectorAll('[data-row]').forEach(function (row, index) {
            row.querySelectorAll('[data-name]').forEach(function (input) {
                input.name = 'items[' + index + '][' + input.getAttribute('data-name') + ']';
            });
        });
        updateTotal();
    }

    function addRow(valor) {
        var node = template.content.firstElementChild.cloneNode(true);
        if (valor) node.querySelector('[data-name=description]').value = valor;
        rowsBox.appendChild(node);
        renumber();
        return node;
    }

    form.querySelector('[data-add-row]').addEventListener('click', function () { addRow(); });

    rowsBox.addEventListener('click', function (event) {
        var btn = event.target.closest('[data-remove-row]');
        if (!btn) return;
        btn.closest('[data-row]').remove();
        renumber();
    });

    function parseMoney(value) {
        if (!value) return 0;
        return Number(String(value).replace(/\./g, '').replace(',', '.')) || 0;
    }

    function updateTotal() {
        var total = 0;
        rowsBox.querySelectorAll('[data-row]').forEach(function (row) {
            var qtd = parseMoney(row.querySelector('.qty').value) || 1;
            var unit = parseMoney(row.querySelector('input[placeholder="Unit. R$"]').value);
            total += qtd * unit;
        });
        form.querySelector('[data-total-itens]').textContent =
            'R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    }

    rowsBox.addEventListener('input', updateTotal);

    // Atalhos de categoria + sugestões de itens (inclui pneus)
    function aplicarSugestoes() {
        var lista = sugestoes[categoria.value];
        hint.textContent = lista
            ? 'Sugestões para este serviço: ' + lista.slice(0, 4).join(' · ')
            : 'Descreva cada item (peças, pneus, fluidos). A quantidade e o valor são opcionais.';
    }

    document.querySelectorAll('[data-quick]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            categoria.value = btn.getAttribute('data-quick');
            aplicarSugestoes();

            // Preenche automaticamente as sugestões em linhas novas
            var lista = sugestoes[categoria.value] || [];
            lista.slice(0, 4).forEach(function (item) {
                var jaExiste = false;
                rowsBox.querySelectorAll('[data-name=description]').forEach(function (i) {
                    if (i.value.trim().toLowerCase() === item.toLowerCase()) jaExiste = true;
                });
                if (!jaExiste) addRow(item);
            });
        });
    });

    categoria.addEventListener('change', aplicarSugestoes);
    aplicarSugestoes();

    if (!rowsBox.querySelector('[data-row]')) addRow();
    renumber();
})();
</script>
<?php $view->endSection(); ?>
