<?php
/* Bloco da Home: diferenciais (partial carregado por pages/home.php) */
?>
<!-- Diferenciais + imagem -->
<section class="section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <img src="/assets/images/perfuracao-3.jpg" alt="Perfuração de poço artesiano" class="img-fluid rounded-4 shadow">
            </div>
            <div class="col-lg-6">
                <span class="eyebrow eyebrow-dark">Por que a Hidrossolo</span>
                <h2 class="section-title">Técnica, segurança e resultado</h2>
                <p class="text-muted">Do estudo geológico à entrega do poço funcionando, conduzimos cada etapa com rigor técnico e transparência.</p>

                <?php foreach ($diferenciais as $dif) { ?>
                <div class="d-flex gap-3 mt-3">
                    <span class="stat-icon bg-success bg-opacity-10 text-success" style="width:44px;height:44px;font-size:1.2rem;flex:0 0 44px">
                        <i class="bi bi-check-circle"></i>
                    </span>
                    <div>
                        <strong><?= e($dif['title']) ?></strong>
                        <p class="text-muted mb-0 small"><?= e($dif['content']) ?></p>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</section>
