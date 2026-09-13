<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<h4 class="mb-4"><?= e($contrato ? 'Editar Contrato' : 'Novo Contrato') ?></h4>

<div class="card">
    <div class="card-body">
            <form method="POST" action="<?= e($contrato ? '/admin/contratos/editar/' . $contrato['id'] : '/admin/contratos/novo') ?>">

            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Número do Contrato *</label>
                    <input type="text" name="contract_number" class="form-control" required value="<?= e($contrato['contract_number'] ?? '') ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Cliente *</label>
                    <input type="text" name="client_name" class="form-control" required value="<?= e($contrato['client_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Responsável</label>
                    <input type="text" name="responsible" class="form-control" value="<?= e($contrato['responsible'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data de Início *</label>
                    <input type="date" name="start_date" class="form-control" required value="<?= e($contrato['start_date'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data de Término *</label>
                    <input type="date" name="end_date" class="form-control" required value="<?= e($contrato['end_date'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Valor (R$) *</label>
                    <input type="text" name="value" class="form-control" required value="<?= e(isset($contrato) ? number_format($contrato['value'], 2, ',', '.') : '') ?>" placeholder="0,00">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= e(($contrato['status'] ?? 'active') == 'active' ? 'selected' : '') ?>>Ativo</option>
                        <option value="completed" <?= e(($contrato['status'] ?? '') == 'completed' ? 'selected' : '') ?>>Concluído</option>
                        <option value="cancelled" <?= e(($contrato['status'] ?? '') == 'cancelled' ? 'selected' : '') ?>>Cancelado</option>
                        <option value="expired" <?= e(($contrato['status'] ?? '') == 'expired' ? 'selected' : '') ?>>Vencido</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Observações</label>
                    <textarea name="notes" class="form-control" rows="3"><?= e($contrato['notes'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="/admin/contratos" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $view->endSection(); ?>
