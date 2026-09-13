<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<?php
$content = $template['content'] ?? "<h2>CONTRATO DE PRESTAÇÃO DE SERVIÇOS</h2>\n\n<p><strong>CONTRATANTE:</strong> {{cliente}}, inscrito(a) no CPF/CNPJ sob nº {{documento}}, residente em {{endereco}}.</p>\n\n<p><strong>CONTRATADA:</strong> Hidrossolo Poços Artesianos, com endereço em {{endereco_empresa}}.</p>\n\n<h3>CLÁUSULA 1ª — DO OBJETO</h3>\n<p>O presente contrato tem por objeto {{objeto}}.</p>\n\n<h3>CLÁUSULA 2ª — DO VALOR</h3>\n<p>O valor total dos serviços é de R$ {{valor}}, a ser pago conforme acordado entre as partes.</p>\n\n<h3>CLÁUSULA 3ª — DO PRAZO</h3>\n<p>O prazo de execução inicia-se em {{data_inicio}} e encerra-se em {{data_fim}}.</p>\n\n<h3>CLÁUSULA 4ª — DO FORO</h3>\n<p>Fica eleito o foro da comarca de {{cidade}} para dirimir eventuais controvérsias.</p>\n\n<p>{{cidade}}, {{data_hoje}}.</p>\n\n<p>_______________________________________<br>{{cliente}}</p>\n<p>_______________________________________<br>Hidrossolo Poços Artesianos</p>\n";
?>
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="/admin/contratos/modelos" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    <h4 class="mb-0"><?= e($template ? 'Editar Modelo' : 'Novo Modelo') ?></h4>
</div>

<form method="POST" action="<?= e($template ? '/admin/contratos/modelos/editar/' . $template['id'] : '/admin/contratos/modelos/novo') ?>">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="name">Nome do modelo *</label>
                            <input type="text" name="name" id="name" class="form-control" required
                                   value="<?= e($template['name'] ?? '') ?>" placeholder="Ex.: Contrato de perfuração de poço">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="active" id="active"
                                       <?= e(!$template || (int) $template['active'] === 1 ? 'checked' : '') ?>>
                                <label class="form-check-label" for="active">Modelo ativo</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="description">Descrição</label>
                            <input type="text" name="description" id="description" class="form-control" maxlength="500"
                                   value="<?= e($template['description'] ?? '') ?>" placeholder="Para que serve este modelo">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>Conteúdo do contrato</strong>
                    <small class="text-muted">HTML permitido</small>
                </div>
                <div class="card-body">
                    <textarea name="content" id="content" class="form-control" rows="18" required><?= e($content) ?></textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header bg-white"><strong>Variáveis do modelo</strong></div>
                <div class="card-body">
                    <p class="text-muted small">
                        Escreva <code>{{nome_da_variavel}}</code> em qualquer ponto do texto. Ao gerar um contrato,
                        elas viram campos para preencher.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small">Inserir no texto</label>
                        <div class="d-flex gap-1">
                            <select id="varPicker" class="form-select form-select-sm">
                                <?php foreach (['cliente', 'documento', 'endereco', 'objeto', 'valor', 'data_inicio', 'data_fim', 'cidade', 'data_hoje', 'numero_contrato', 'responsavel', 'endereco_empresa'] as $sug) { ?>
                                    <option value="<?= e($sug) ?>"><?= e($sug) ?></option>
                                <?php } ?>
                            </select>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="varInsert">Inserir</button>
                        </div>
                    </div>
                    <label class="form-label small">Preenchidas automaticamente</label>
                    <div class="small text-muted">
                        <code>{{cliente}}</code>, <code>{{numero_contrato}}</code>, <code>{{valor}}</code>,
                        <code>{{data_inicio}}</code>, <code>{{data_fim}}</code>, <code>{{data_hoje}}</code>,
                        <code>{{cidade}}</code>, <code>{{empresa}}</code>.
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2"><i class="bi bi-check-lg me-1"></i>Salvar modelo</button>
                    <a href="/admin/contratos/modelos" class="btn btn-outline-secondary w-100">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
</form>
<?php $view->endSection(); ?>

<?php $view->section('scripts'); ?>
<script>
(function () {
    var textarea = document.getElementById('content');
    var picker = document.getElementById('varPicker');
    var insert = document.getElementById('varInsert');

    if (insert) {
        insert.addEventListener('click', function () {
            var variable = '{{' + picker.value + '}}';
            var start = textarea.selectionStart;
            var end = textarea.selectionEnd;
            textarea.value = textarea.value.slice(0, start) + variable + textarea.value.slice(end);
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = start + variable.length;
        });
    }
})();
</script>
<?php $view->endSection(); ?>
