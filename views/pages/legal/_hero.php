<?php
/** Cabeçalho padrão das páginas legais. */
$title = $title ?? '';
$subtitle = $subtitle ?? '';
$atualizadoEm = $atualizadoEm ?? '';
$versao = $versao ?? '';
?>
<section class="legal-hero">
    <div class="container">
        <span class="eyebrow">Documento legal · Hidrossolo</span>
        <h1><?= e($title) ?></h1>
        <p><?= e($subtitle) ?></p>
        <div class="legal-meta">
            <span class="legal-badge"><i class="bi bi-patch-check"></i> Versão <?= e($versao) ?></span>
            <span class="legal-badge"><i class="bi bi-calendar-check"></i> Atualizado em <?= e($atualizadoEm) ?></span>
            <span class="legal-badge"><i class="bi bi-shield-check"></i> LGPD · Lei nº 13.709/2018</span>
        </div>
    </div>
</section>
