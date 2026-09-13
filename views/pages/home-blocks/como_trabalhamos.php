<?php
/* Bloco da Home: como_trabalhamos (partial carregado por pages/home.php) */
?>
<!-- Como trabalhamos -->
<section class="section section-alt">
    <div class="container">
        <div class="text-center">
            <span class="eyebrow eyebrow-dark">Processo</span>
            <h2 class="section-title">Como trabalhamos</h2>
            <p class="section-subtitle">Um fluxo claro, do primeiro contato à entrega.</p>
        </div>

        <div class="row g-4">
            <?php
            $etapas = [
                ['n' => '01', 't' => 'Diagnóstico', 'd' => 'Visita técnica e levantamento das necessidades e do terreno.'],
                ['n' => '02', 't' => 'Projeto e licenças', 'd' => 'Estudo geológico, dimensionamento e regularização ambiental.'],
                ['n' => '03', 't' => 'Execução', 'd' => 'Perfuração e instalação com equipamentos e equipe próprios.'],
                ['n' => '04', 't' => 'Entrega e suporte', 'd' => 'Testes de vazão, laudos e manutenção preventiva contínua.'],
            ];
            foreach ($etapas as $etapa) { ?>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="stat-value text-primary" style="font-size:1.6rem"><?= e($etapa['n']) ?></div>
                        <h4 class="h5 mt-2"><?= e($etapa['t']) ?></h4>
                        <p class="text-muted small mb-0"><?= e($etapa['d']) ?></p>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</section>
