<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<?php $info = $statusLabels[$orcamento['status']] ?? ['rotulo' => $orcamento['status'], 'cor' => 'secondary']; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-1">
            Solicitação #<?= e($orcamento['id']) ?>
            <span class="badge bg-<?= e($info['cor']) ?> align-middle"><?= e($info['rotulo']) ?></span>
        </h4>
        <p class="text-muted small mb-0">
            Recebida em <?= e(date('d/m/Y \à\s H:i', strtotime((string) $orcamento['created_at']))) ?>
        </p>
    </div>
    <a href="/admin/orcamentos" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header bg-white"><h5 class="mb-0">Dados da solicitação</h5></div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr><th style="width:32%">Nome</th><td><?= e($orcamento['name']) ?></td></tr>
                        <tr>
                            <th>E-mail</th>
                            <td><a href="mailto:<?= e($orcamento['email']) ?>"><?= e($orcamento['email']) ?></a></td>
                        </tr>
                        <tr>
                            <th>Telefone</th>
                            <td>
                                <?php if (!empty($orcamento['phone'])) { ?>
                                    <a href="<?= e(company_tel_link($orcamento['phone'])) ?>"><?= e($orcamento['phone']) ?></a>
                                <?php } else { ?>
                                    <span class="text-muted">não informado</span>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr><th>Tipo de serviço</th><td><?= e($orcamento['service_type']) ?></td></tr>
                        <tr>
                            <th>Data desejada</th>
                            <td><?= e(data_iso_para_br($orcamento['preferred_date']) ?: 'não informada') ?></td>
                        </tr>
                        <tr>
                            <th>Endereço do serviço</th>
                            <td><?= e($orcamento['address'] ?: 'não informado') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white"><h5 class="mb-0">Descrição</h5></div>
            <div class="card-body">
                <p class="mb-0" style="white-space:pre-line"><?= e($orcamento['description']) ?></p>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-white"><h5 class="mb-0">Responder</h5></div>
            <div class="card-body d-grid gap-2">
                <a href="mailto:<?= e($orcamento['email']) ?>?subject=<?= e(rawurlencode('Orçamento Hidrossolo - solicitação #' . $orcamento['id'])) ?>"
                   class="btn btn-primary">
                    <i class="bi bi-envelope me-1"></i>Responder por e-mail
                </a>
                <?php if (!empty($orcamento['phone'])) { ?>
                    <a href="<?= e(company_whatsapp_link($orcamento['phone'], 'Olá! Sobre a sua solicitação de orçamento na Hidrossolo...')) ?>"
                       target="_blank" rel="noopener noreferrer" class="btn btn-success">
                        <i class="bi bi-whatsapp me-1"></i>Chamar no WhatsApp
                    </a>
                <?php } ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white"><h5 class="mb-0">Situação</h5></div>
            <div class="card-body">
                <form method="POST" action="/admin/orcamentos/status/<?= e($orcamento['id']) ?>" class="d-grid gap-2">
                    <?= csrf_field() ?>
                    <select name="status" class="form-select">
                        <?php foreach ($statusLabels as $chave => $dados) { ?>
                            <option value="<?= e($chave) ?>" <?= e($orcamento['status'] === $chave ? 'selected' : '') ?>>
                                <?= e($dados['rotulo']) ?>
                            </option>
                        <?php } ?>
                    </select>
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-check-lg me-1"></i>Salvar situação
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-danger">
            <div class="card-body">
                <form method="POST" action="/admin/orcamentos/excluir/<?= e($orcamento['id']) ?>"
                      onsubmit="return confirm('Excluir esta solicitação permanentemente?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="bi bi-trash me-1"></i>Excluir solicitação
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $view->endSection(); ?>
