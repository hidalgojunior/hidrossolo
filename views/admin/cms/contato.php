<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<h4 class="mb-4">Gerenciar Página de Contato</h4>

<form method="POST" action="/admin/cms/contato">
            <?= csrf_field() ?>
    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">🏷️ Identidade Visual</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">URL do Logo</label>
                    <input type="text" name="site_logo" class="form-control" value="<?= e($settings['site_logo'] ?? '/assets/images/hidrossolo.png') ?>">
                    <small class="text-muted">Faça upload na <a href="/admin/midia">Biblioteca de Mídias</a> e cole a URL aqui</small>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <img src="<?= e($settings['site_logo'] ?? '/assets/images/hidrossolo.png') ?>" alt="Logo preview" style="max-height:60px">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">📞 Informações de Contato</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Endereço</label>
                    <input type="text" name="address" class="form-control" value="<?= e($settings['address'] ?? $config['address'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="phone" class="form-control" value="<?= e($settings['phone'] ?? $config['phone'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">WhatsApp</label>
                    <input type="text" name="whatsapp" class="form-control" value="<?= e($settings['whatsapp'] ?? $config['whatsapp'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">E-mail</label>
                    <input type="text" name="email" class="form-control" value="<?= e($settings['email'] ?? $config['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Horário de Funcionamento</label>
                    <input type="text" name="working_hours" class="form-control" value="<?= e($settings['working_hours'] ?? 'Seg a Sex: 08h às 18h | Sáb: 08h às 12h') ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">📝 Formulário de Contato</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Título do Formulário</label>
                    <input type="text" name="form_title" class="form-control" value="<?= e($settings['form_title'] ?? 'Envie sua Mensagem') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Texto Descritivo</label>
                    <input type="text" name="form_text" class="form-control" value="<?= e($settings['form_text'] ?? 'Entre em contato e solicite seu orçamento') ?>">
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg">💾 Salvar Contato</button>
</form>
<?php $view->endSection(); ?>
