<?php $view->layout('layouts.main'); ?>

<?php $view->section('content'); ?>
<section class="hero" style="min-height:35vh;padding:100px 0 50px">
    <div class="container"><h1>Solicitar Orçamento</h1><p class="lead">Preencha o formulário e receba uma proposta personalizada</p></div>
</section>

<section class="section"><div class="container"><div class="row"><div class="col-lg-8 mx-auto">
<div class="card"><div class="card-body">
    <form method="POST" action="/orcamento/enviar">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nome *</label><input type="text" name="name" class="form-control" value="<?= e($_SESSION['old_input']['name'] ?? '') ?>" required></div>
            <div class="col-md-6"><label class="form-label">E-mail *</label><input type="email" name="email" class="form-control" value="<?= e($_SESSION['old_input']['email'] ?? '') ?>" required></div>
            <div class="col-md-6"><label class="form-label">Telefone</label><input type="text" name="phone" class="form-control" value="<?= e($_SESSION['old_input']['phone'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label">Tipo de Serviço *</label>
                <select name="service_type" class="form-select" required>
                    <option value="">Selecione...</option>
                    <option value="Perfuração de Poço">Perfuração de Poço</option>
                    <option value="Limpeza de Poço">Limpeza de Poço</option>
                    <option value="Manutenção">Manutenção</option>
                    <option value="Instalação de Bomba">Instalação de Bomba</option>
                    <option value="Licenciamento/Outorga">Licenciamento/Outorga</option>
                    <option value="Análise de Solo">Análise de Solo</option>
                    <option value="Outro">Outro</option>
                </select>
            </div>
            <div class="col-md-6"><label class="form-label">Endereço do Serviço</label><input type="text" name="address" class="form-control" placeholder="Rua, número, cidade"></div>
            <div class="col-md-6">
                <label class="form-label" for="preferredDate">Data Desejada</label>
                <input type="text" name="preferred_date" id="preferredDate" class="form-control"
                       placeholder="dd/mm/aaaa" maxlength="10" inputmode="numeric"
                       pattern="\d{2}/\d{2}/\d{4}" title="Use o formato dd/mm/aaaa"
                       data-date-br value="<?= e($_SESSION['old_input']['preferred_date'] ?? '') ?>">
                <small class="text-muted">Formato: dd/mm/aaaa</small>
            </div>
            <div class="col-12"><label class="form-label">Descreva o serviço *</label><textarea name="description" class="form-control" rows="4" required placeholder="Descreva o que você precisa..."><?= e($_SESSION['old_input']['description'] ?? '') ?></textarea></div>
            <div class="col-12"><button type="submit" class="btn btn-primary btn-lg">Enviar Solicitação</button></div>
        </div>
    </form>
    <?php unset($_SESSION['old_input']) ?>
</div></div>
</div></div></div></section>
<?php $view->endSection(); ?>
