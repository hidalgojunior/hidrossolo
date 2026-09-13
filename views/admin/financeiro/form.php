<?php
$view->layout('layouts.admin');

$valor = static function ($v): string {
    return $v === null || $v === '' ? '' : number_format((float) $v, 2, ',', '.');
};

$kind = (string) ($lancamento['kind'] ?? 'expense');
$categoriaAtual = (string) ($lancamento['category'] ?? '');
?>

<?php $view->section('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><?= e($lancamento ? 'Editar Lançamento' : 'Novo Lançamento') ?></h4>
        <small class="text-muted">Contas a pagar e a receber — entradas e saídas da empresa</small>
    </div>
    <a href="/admin/financeiro" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
</div>

<?php if (!empty($_SESSION['flash_error'])) { ?>
    <div class="alert alert-danger"><?= e($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php } ?>

<form method="POST" action="<?= e($lancamento ? '/admin/financeiro/editar/' . $lancamento['id'] : '/admin/financeiro/novo') ?>">
    <?= csrf_field() ?>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label d-block">Tipo de lançamento *</label>
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="kind" id="kindExpense" value="expense" <?= e($kind === 'expense' ? 'checked' : '') ?>>
                        <label class="btn btn-outline-danger" for="kindExpense">
                            <i class="bi bi-arrow-up-circle me-1"></i>Saída (conta a pagar)
                        </label>

                        <input type="radio" class="btn-check" name="kind" id="kindIncome" value="income" <?= e($kind === 'income' ? 'checked' : '') ?>>
                        <label class="btn btn-outline-success" for="kindIncome">
                            <i class="bi bi-arrow-down-circle me-1"></i>Entrada (conta a receber)
                        </label>
                    </div>
                </div>

                <div class="col-md-8">
                    <label class="form-label">Descrição *</label>
                    <input type="text" name="description" class="form-control" required
                           value="<?= e($lancamento['description'] ?? '') ?>"
                           placeholder="Ex.: Energia elétrica — unidade Marília / Nota fiscal 1234">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Valor (R$) *</label>
                    <input type="text" name="amount" class="form-control" required
                           value="<?= e($valor($lancamento['amount'] ?? null)) ?>" placeholder="0,00">
                </div>

                <!-- Categoria: saída -->
                <div class="col-md-4" data-kind-group="expense">
                    <label class="form-label">Categoria *</label>
                    <select name="category" class="form-select" data-kind-category="expense">
                        <?php foreach ($categoriasDespesa as $chave => $cat) { ?>
                            <option value="<?= e($chave) ?>" <?= e($kind === 'expense' && $categoriaAtual === $chave ? 'selected' : '') ?>>
                                <?= e($cat['label']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <!-- Categoria: entrada -->
                <div class="col-md-4" data-kind-group="income">
                    <label class="form-label">Categoria *</label>
                    <select name="category" class="form-select" data-kind-category="income" disabled>
                        <?php foreach ($categoriasReceita as $chave => $cat) { ?>
                            <option value="<?= e($chave) ?>" <?= e($kind === 'income' && $categoriaAtual === $chave ? 'selected' : '') ?>>
                                <?= e($cat['label']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Vencimento *</label>
                    <input type="date" name="due_date" class="form-control" required
                           value="<?= e($lancamento['due_date'] ?? $dataSugerida) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Fornecedor / Cliente</label>
                    <input type="text" name="party" class="form-control" value="<?= e($lancamento['party'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Documento / NF</label>
                    <input type="text" name="document" class="form-control" value="<?= e($lancamento['document'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Ativo vinculado</label>
                    <select name="vehicle_id" class="form-select">
                        <option value="">Não vinculado</option>
                        <?php foreach ($veiculos as $v) { ?>
                            <option value="<?= e($v['id']) ?>" <?= e(($lancamento['vehicle_id'] ?? null) == $v['id'] ? 'selected' : '') ?>>
                                <?= e(asset_label($v)) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Forma de pagamento</label>
                    <select name="payment_method" class="form-select">
                        <option value="">—</option>
                        <?php foreach ($formasPagamento as $chave => $rotulo) { ?>
                            <option value="<?= e($chave) ?>" <?= e(($lancamento['payment_method'] ?? '') === $chave ? 'selected' : '') ?>>
                                <?= e($rotulo) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Repetição</label>
                    <select name="recurrence" class="form-select">
                        <?php foreach ($recorrencias as $chave => $rotulo) { ?>
                            <option value="<?= e($chave) ?>" <?= e(($lancamento['recurrence'] ?? 'none') === $chave ? 'selected' : '') ?>>
                                <?= e($rotulo) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Já está pago / recebido?</label>
                    <input type="date" name="paid_at" class="form-control" value="<?= e($lancamento['paid_at'] ?? '') ?>">
                    <div class="form-text">Deixe vazio se ainda está pendente.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Valor efetivamente pago</label>
                    <input type="text" name="paid_amount" class="form-control"
                           value="<?= e($valor($lancamento['paid_amount'] ?? null)) ?>" placeholder="Igual ao previsto">
                    <div class="form-text">Use quando houver juros ou desconto.</div>
                </div>

                <div class="col-12">
                    <label class="form-label">Observações</label>
                    <textarea name="notes" class="form-control" rows="2"><?= e($lancamento['notes'] ?? '') ?></textarea>
                </div>

                <?php if (!$lancamento) { ?>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="criar_proximo" value="1" id="criarProximo">
                            <label class="form-check-label" for="criarProximo">
                                Já criar também o próximo lançamento desta repetição
                            </label>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-5">
        <button class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Salvar lançamento</button>
        <a href="/admin/financeiro" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
<?php $view->endSection(); ?>

<?php $view->section('scripts'); ?>
<script>
(function () {
    function atualizar() {
        var kind = document.querySelector('input[name="kind"]:checked').value;

        document.querySelectorAll('[data-kind-group]').forEach(function (grupo) {
            var combinando = grupo.dataset.kindGroup === kind;
            grupo.classList.toggle('d-none', !combinando);
            var select = grupo.querySelector('select');
            if (select) { select.disabled = !combinando; }
        });
    }

    document.querySelectorAll('input[name="kind"]').forEach(function (radio) {
        radio.addEventListener('change', atualizar);
    });

    atualizar();
})();
</script>
<?php $view->endSection(); ?>
