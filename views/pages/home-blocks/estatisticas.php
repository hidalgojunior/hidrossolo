<?php
/* Bloco da Home: estatisticas (partial carregado por pages/home.php) */
?>
<!-- Estatísticas -->
<section class="section section-stats">
    <div class="container">
        <div class="row g-4 stats-overlap">
            <?php foreach ($stats as $stat) { ?>
            <div class="col-6 col-lg-3">
                <div class="stat-card h-100">
                    <div class="d-flex align-items-center gap-3">
                        <span class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-<?= e($stat['icon']) ?>"></i>
                        </span>
                        <div>
                            <div class="stat-value"><?= e($stat['value']) ?></div>
                            <div class="stat-label"><?= e($stat['label']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</section>
