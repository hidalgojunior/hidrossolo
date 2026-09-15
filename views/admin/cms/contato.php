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
                    <?= $view->partial('admin.components.media-field', [
                        'name' => 'site_logo',
                        'label' => 'Logo do site',
                        'value' => $settings['site_logo'] ?? '/assets/images/hidrossolo.png',
                        'help' => 'Escolha um arquivo da biblioteca ou envie um novo.',
                    ]) ?>
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
                <div class="col-md-6">
                    <label class="form-label">Telefone fixo</label>
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
        <div class="card-header bg-white"><h5 class="mb-0">� Redes Sociais</h5></div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Preencha só o que a empresa usa — o que ficar em branco não aparece.
                Os ícones são exibidos no rodapé de <strong>todas as páginas do site</strong>.
            </p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="socialInstagram"><i class="bi bi-instagram me-1"></i>Instagram</label>
                    <input type="text" name="social_instagram" id="socialInstagram" class="form-control"
                           placeholder="https://instagram.com/hidrossolo" value="<?= e($settings['social_instagram'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="socialFacebook"><i class="bi bi-facebook me-1"></i>Facebook</label>
                    <input type="text" name="social_facebook" id="socialFacebook" class="form-control"
                           placeholder="https://facebook.com/hidrossolo" value="<?= e($settings['social_facebook'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="socialYoutube"><i class="bi bi-youtube me-1"></i>YouTube</label>
                    <input type="text" name="social_youtube" id="socialYoutube" class="form-control"
                           placeholder="https://youtube.com/@hidrossolo" value="<?= e($settings['social_youtube'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="socialLinkedin"><i class="bi bi-linkedin me-1"></i>LinkedIn</label>
                    <input type="text" name="social_linkedin" id="socialLinkedin" class="form-control"
                           placeholder="https://linkedin.com/company/hidrossolo" value="<?= e($settings['social_linkedin'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="socialTiktok"><i class="bi bi-tiktok me-1"></i>TikTok</label>
                    <input type="text" name="social_tiktok" id="socialTiktok" class="form-control"
                           placeholder="https://tiktok.com/@hidrossolo" value="<?= e($settings['social_tiktok'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="socialX"><i class="bi bi-twitter-x me-1"></i>X (Twitter)</label>
                    <input type="text" name="social_x" id="socialX" class="form-control"
                           placeholder="https://x.com/hidrossolo" value="<?= e($settings['social_x'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="socialOutras">Outras redes</label>
                    <textarea name="social_outras" id="socialOutras" class="form-control" rows="3"
                              placeholder="Pinterest | https://pinterest.com/hidrossolo&#10;Google Meu Negócio | https://g.page/hidrossolo"><?= e($settings['social_outras'] ?? '') ?></textarea>
                    <small class="text-muted">Uma por linha, no formato <strong>Nome | endereço</strong>.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">�📝 Formulário de Contato</h5></div>
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
