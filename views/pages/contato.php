<?php $view->layout('layouts.main'); ?>

<?php $view->section('content'); ?>
<section class="page-hero">
    <div class="container">
        <h1>Fale Conosco</h1>
        <p class="lead">Entre em contato e solicite seu orçamento</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-7">
                <h2 class="section-title">Envie sua Mensagem</h2>
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
                    <p><i class="bi bi-geo-alt-fill text-primary me-2"></i><strong>Endereço:</strong><br>
                    <?= e($config['address'] ?? 'R. Assad Haddad, 584') ?><br>
                    <?= e($config['city'] ?? 'Marília') ?> - <?= e($config['state'] ?? 'SP') ?><br>
                    CEP: <?= e($config['zip'] ?? '17519-700') ?></p>

                    <p><i class="bi bi-telephone-fill text-primary me-2"></i><strong>Telefone:</strong><br>
                    <?= e($config['phone'] ?? '(14) 3413-2437') ?></p>

                    <p><i class="bi bi-whatsapp text-success me-2"></i><strong>WhatsApp:</strong><br>
                    <?= e($config['whatsapp'] ?? '(14) 99123-4567') ?></p>

                    <p><i class="bi bi-envelope-fill text-primary me-2"></i><strong>E-mail:</strong><br>
                    <?= e($config['email'] ?? 'hidrossolo@hidrossolopocos.com.br') ?></p>

                    <hr>
                    <h5>Horário de Funcionamento</h5>
                    <p>Segunda a Sexta: 08h às 18h<br>Sábado: 08h às 12h</p>
                </div>

                <!-- Google Maps -->
                <div class="mt-4 rounded-4 overflow-hidden shadow-sm">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3693.5!2d-49.95!3d-22.22!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjLCsDEzJzEyLjAiUyA0OcKwNTcnMDAuMCJX!5e0!3m2!1spt-BR!2sbr!4v1234567890" 
                            width="100%" height="250" style="border:0" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $view->endSection(); ?>
