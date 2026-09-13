<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<?php
$auto = ['numero_contrato', 'numero', 'cliente', 'contratante', 'responsavel', 'data_inicio', 'data_fim',
    'valor', 'valor_total', 'valor_numerico', 'data_hoje', 'cidade', 'empresa', 'endereco_empresa',
    'telefone_empresa', 'email_empresa', 'cnpj'];

$mapaVariaveis = [];
foreach ($templates as $t) {
    $mapaVariaveis[$t['id']] = array_values(array_diff($t['variaveis'], $auto));
}
$templateSelecionado = (int) ($contrato['template_id'] ?? ($templates[0]['id'] ?? 0));
?>
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="/admin/contratos" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    <h4 class="mb-0"><?= e($contrato ? 'Editar Contrato' : 'Novo Contrato') ?></h4>
    <?php if ($contrato && !empty($contrato['content'])) { ?>
        <a href="/admin/contratos/documento/<?= e($contrato['id']) ?>" class="btn btn-sm btn-outline-secondary ms-auto">
            <i class="bi bi-eye me-1"></i>Ver documento
        </a>
    <?php } ?>
</div>

<?php if (empty($templates)) { ?>
    <div class="alert alert-warning">
        Nenhum modelo ativo encontrado. <a href="/admin/contratos/modelos/novo">Crie um modelo</a> para gerar documentos automaticamente.
    </div>
<?php } ?>

<form method="POST" action="<?= e($contrato ? '/admin/contratos/editar/' . $contrato['id'] : '/admin/contratos/novo') ?>">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header bg-white"><strong>Dados do contrato</strong></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="contract_number">Número do contrato *</label>
                            <input type="text" name="contract_number" id="contract_number" class="form-control" required
                                   value="<?= e($contrato['contract_number'] ?? '') ?>" placeholder="Ex.: 2026/001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="client_name">Cliente *</label>
                            <input type="text" name="client_name" id="client_name" class="form-control" required
                                   value="<?= e($contrato['client_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="responsible">Responsável</label>
                            <input type="text" name="responsible" id="responsible" class="form-control"
                                   value="<?= e($contrato['responsible'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="value">Valor (R$)</label>
                            <input type="text" inputmode="decimal" name="value" id="value" class="form-control"
                                   value="<?= e($contrato['value'] ?? '') ?>" placeholder="0,00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="start_date">Início</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                   value="<?= e($contrato['start_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="end_date">Término</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                   value="<?= e($contrato['end_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="status">Status</label>
                            <select name="status" id="status" class="form-select">
                                <?php foreach (['active' => 'Ativo', 'completed' => 'Concluído', 'expired' => 'Vencido', 'cancelled' => 'Cancelado'] as $k => $v) { ?>
                                    <option value="<?= e($k) ?>" <?= e(($contrato['status'] ?? 'active') === $k ? 'selected' : '') ?>><?= e($v) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="notes">Observações internas</label>
                            <textarea name="notes" id="notes" class="form-control" rows="2"><?= e($contrato['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white"><strong>Variáveis do modelo</strong></div>
                <div class="card-body">
                    <p class="text-muted small">
                        Preencha os campos abaixo. As variáveis de cliente, valor e datas já são preenchidas
                        automaticamente com os dados do contrato acima.
                    </p>
                    <div id="variaveisBox" class="row g-3"></div>
                    <p id="semVariaveis" class="text-muted small mb-0" style="display:none">
                        Este modelo não possui variáveis adicionais.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-body">
                    <label class="form-label" for="template_id">Modelo do documento</label>
                    <select name="template_id" id="template_id" class="form-select">
                        <option value="0">— Nenhum (sem geração automática) —</option>
                        <?php foreach ($templates as $t) { ?>
                            <option value="<?= e($t['id']) ?>" <?= e($templateSelecionado === (int) $t['id'] ? 'selected' : '') ?>>
                                <?= e($t['name']) ?>
                            </option>
                        <?php } ?>
                    </select>
                    <small class="text-muted d-block mt-2">
                        O documento é gerado ao salvar. Você poderá baixar o PDF em seguida.
                    </small>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-magic me-1"></i><?= e($contrato ? 'Salvar e regerar documento' : 'Criar e gerar documento') ?>
                    </button>
                    <a href="/admin/contratos" class="btn btn-outline-secondary w-100">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
</form>
<?php $view->endSection(); ?>

<?php $view->section('scripts'); ?>
<script>
(function () {
    var mapa = <?= json_attr($mapaVariaveis) ?>;
    var valores = <?= json_attr($valores) ?>;
    var select = document.getElementById('template_id');
    var box = document.getElementById('variaveisBox');
    var semVars = document.getElementById('semVariaveis');

    function titulo(key) {
        return key.replace(/[_.-]/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    }

    function render() {
        var variaveis = mapa[select.value] || [];
        box.innerHTML = '';

        if (!variaveis.length) {
            semVars.style.display = '';
            return;
        }
        semVars.style.display = 'none';

        variaveis.forEach(function (nome) {
            var col = document.createElement('div');
            col.className = 'col-md-6';

            var label = document.createElement('label');
            label.className = 'form-label';
            label.textContent = titulo(nome);

            var input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control';
            input.name = 'vars[' + nome + ']';
            input.value = valores[nome] || '';

            col.appendChild(label);
            col.appendChild(input);
            box.appendChild(col);
        });
    }

    select.addEventListener('change', render);
    render();
})();
</script>
<?php $view->endSection(); ?>
