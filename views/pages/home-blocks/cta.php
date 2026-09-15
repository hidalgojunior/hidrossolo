<?php
/* Bloco da Home: cta (partial carregado por pages/home.php) */
?>
<!-- CTA final -->
<section class="section">
    <div class="container">
        <div class="banner-card text-center">
            <h2><?= e($ctaSection['title'] ?? 'Precisa de um poço artesiano?') ?></h2>
            <p class="mb-4" style="color:rgba(255,255,255,.9)"><?= e($ctaSection['subtitle'] ?? 'Fale com a nossa equipe e receba um orçamento sem compromisso.') ?></p>
            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <a href="<?= e($ctaSection['link_url'] ?? '/orcamento') ?>" class="btn btn-light btn-lg">
                    <i class="bi bi-send"></i> <?= e($ctaSection['content'] ?? 'Solicitar Orçamento') ?>
                </a>
                <a href="<?= e(company_whatsapp_link($company['whatsapp'] ?? '')) ?>" target="_blank" rel="noopener" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-whatsapp"></i> WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>
