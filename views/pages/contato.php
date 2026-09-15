<?php $view->layout('layouts.main'); ?>

<?php $view->section('content'); ?>
<section class="page-hero">
    <div class="container">
        <h1>Fale Conosco</h1>
        <p class="lead"><?= e($company['form_text']) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-7">
                <h2 class="section-title"><?= e($company['form_title']) ?></h2>
                <form action="/contato/enviar" method="POST" class="mt-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome *</label>
                            <input type="text" name="nome" class="form-control" required value="<?= e($_SESSION['old_input']['nome'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefone</label>
                            <input type="tel" name="telefone" class="form-control" value="<?= e($_SESSION['old_input']['telefone'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">E-mail *</label>
                            <input type="email" name="email" class="form-control" required value="<?= e($_SESSION['old_input']['email'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mensagem *</label>
                            <textarea name="mensagem" class="form-control" rows="5" required><?= e($_SESSION['old_input']['mensagem'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send me-2"></i>Enviar Mensagem
                            </button>
                        </div>
                    </div>
                </form>
                <?php unset($_SESSION['old_input']) ?>
            </div>

            <div class="col-lg-5">
                <div class="card p-4">
                    <h4>Informações de Contato</h4>
                    <hr>
                    <?php $endereco = company_address_lines($company); ?>
                    <?php if ($endereco !== []) { ?>
                    <p><i class="bi bi-geo-alt-fill text-primary me-2"></i><strong>Endereço:</strong><br>
                    <a href="<?= e(company_maps_link($company)) ?>" target="_blank" rel="noopener noreferrer" title="Abrir no mapa"><?= implode('<br>', array_map('e', $endereco)) ?></a></p>
                    <?php } ?>

                    <?php $telefones = company_lines($company['phone'] ?? ''); ?>
                    <?php if ($telefones !== []) { ?>
                    <p><i class="bi bi-telephone-fill text-primary me-2"></i><strong>Telefone fixo:</strong><br>
                    <?= implode('<br>', array_map(static fn (string $t): string => '<a href="' . e(company_tel_link($t)) . '">' . e($t) . '</a>', $telefones)) ?></p>
                    <?php } ?>

                    <?php if (($company['whatsapp'] ?? '') !== '') { ?>
                    <p><i class="bi bi-whatsapp text-success me-2"></i><strong>WhatsApp:</strong><br>
                    <a href="<?= e(company_whatsapp_link($company['whatsapp'])) ?>" target="_blank" rel="noopener noreferrer"><?= e($company['whatsapp']) ?></a></p>
                    <?php } ?>

                    <?php if (($company['email'] ?? '') !== '') { ?>
                    <p><i class="bi bi-envelope-fill text-primary me-2"></i><strong>E-mail:</strong><br>
                    <a href="mailto:<?= e($company['email']) ?>"><?= e($company['email']) ?></a></p>
                    <?php } ?>

                    <?php $horario = company_lines($company['working_hours'] ?? ''); ?>
                    <?php if ($horario !== []) { ?>
                    <hr>
                    <h5>Horário de Funcionamento</h5>
                    <p><?= implode('<br>', array_map('e', $horario)) ?></p>
                    <?php } ?>
                </div>

                <!-- Google Maps (busca pelo endereço real cadastrado) -->
                <div class="mt-4 rounded-4 overflow-hidden shadow-sm">
                    <iframe src="<?= e('https://www.google.com/maps?q=' . rawurlencode(implode(', ', $endereco)) . '&output=embed') ?>"
                            width="100%" height="250" style="border:0" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Mapa com a localização da Hidrossolo"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $view->endSection(); ?>
