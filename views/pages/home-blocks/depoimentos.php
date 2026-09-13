<?php
/* Bloco da Home: depoimentos (partial carregado por pages/home.php) */
?>
<!-- Depoimentos (quando houver) -->
<?php if (!empty($depoimentos)) { ?>
<section class="section">
    <div class="container">
        <div class="text-center">
            <span class="eyebrow eyebrow-dark">Depoimentos</span>
            <h2 class="section-title">O que dizem nossos clientes</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($depoimentos as $dep) { ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-warning mb-2">
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                        <p class="fst-italic">"<?= e($dep['content']) ?>"</p>
                        <strong><?= e($dep['title']) ?></strong>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</section>
<?php } ?>
