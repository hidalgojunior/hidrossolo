<?php
$view->layout('layouts.main');

/* ---------------------------------------------------------------------------
 | Conteúdo de fallback (usado quando o CMS ainda não tem registros)
 * ------------------------------------------------------------------------- */
$servicosFallback = [
    ['title' => 'Perfuração de Poços Artesianos', 'slug' => 'perfuracao-de-pocos', 'icon' => 'droplet', 'description' => 'Perfuração com equipamentos modernos e estudo geológico prévio, garantindo vazão e qualidade da água.'],
    ['title' => 'Limpeza e Reabilitação', 'slug' => 'limpeza-de-pocos', 'icon' => 'tools', 'description' => 'Remoção de sedimentos e incrustações para recuperar a vazão original do seu poço.'],
    ['title' => 'Licenciamento e Outorga', 'slug' => 'licenciamento-e-outorga', 'icon' => 'file-earmark-text', 'description' => 'Assessoria completa junto aos órgãos ambientais (DAEE, CETESB) para regularização do poço.'],
    ['title' => 'Instalação de Bombas', 'slug' => 'instalacao-de-bombas', 'icon' => 'gear', 'description' => 'Dimensionamento e instalação de bombas submersas, quadros elétricos e automação.'],
    ['title' => 'Manutenção Preventiva', 'slug' => 'manutencao', 'icon' => 'wrench', 'description' => 'Planos de manutenção que evitam paradas e prolongam a vida útil do sistema de captação.'],
    ['title' => 'Análise e Potabilidade', 'slug' => 'analise-da-agua', 'icon' => 'clipboard', 'description' => 'Coleta e análise laboratorial da água, com laudo técnico e recomendações de tratamento.'],
];

$diferenciaisFallback = [
    ['title' => 'Equipe Especializada', 'content' => 'Técnicos e geólogos experientes em captação de água subterrânea.'],
    ['title' => 'Equipamentos Modernos', 'content' => 'Maquinário próprio de perfuração, com manutenção e tecnologia atualizadas.'],
    ['title' => 'Responsabilidade Ambiental', 'content' => 'Atuação em conformidade com as normas técnicas e ambientais vigentes.'],
];

$stats = [
    ['value' => '+20', 'label' => 'Anos de experiência', 'icon' => 'calendar'],
    ['value' => '+1.500', 'label' => 'Poços perfurados', 'icon' => 'droplet'],
    ['value' => '100%', 'label' => 'Regularização ambiental', 'icon' => 'shield-check'],
    ['value' => '+50', 'label' => 'Municípios atendidos', 'icon' => 'geo-alt'],
];

$servicos = !empty($servicos) ? $servicos : $servicosFallback;
$diferenciais = !empty($diferenciais) ? $diferenciais : $diferenciaisFallback;
$heroTitle = !empty($hero['title']) ? $hero['title'] : 'Soluções completas em poços artesianos';
$heroSubtitle = !empty($hero['subtitle']) ? $hero['subtitle'] : 'Perfuração, licenciamento, limpeza e manutenção com equipe especializada em Marília e região.';
?>

<?php $view->section('content'); ?>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="eyebrow">Água subterrânea com responsabilidade técnica</span>
                <h1><?= e($heroTitle) ?></h1>
                <p class="lead"><?= e($heroSubtitle) ?></p>

                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="<?= e($hero['link_url'] ?? '/orcamento') ?>" class="btn btn-light btn-lg">
                        <i class="bi bi-send"></i> <?= e($hero['content'] ?? 'Solicitar Orçamento') ?>
                    </a>
                    <a href="/servicos" class="btn btn-outline-light btn-lg">Nossos Serviços</a>
                </div>

                <div class="d-flex flex-wrap gap-4 mt-4 text-white" style="opacity:.9">
                    <span><i class="bi bi-check-circle me-1"></i>Atendimento em toda a região</span>
                    <span><i class="bi bi-check-circle me-1"></i>Orçamento sem compromisso</span>
                </div>
            </div>

            <div class="col-lg-5 d-none d-lg-block">
                <img src="/assets/images/plataforma.jpg" alt="Equipe Hidrossolo em operação"
                     class="img-fluid rounded-4 shadow" style="box-shadow:0 25px 60px rgba(2,6,23,.45)">
            </div>
        </div>
    </div>
</section>

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

<!-- Serviços -->
<section class="section section-alt">
    <div class="container">
        <div class="text-center">
            <span class="eyebrow eyebrow-dark">O que fazemos</span>
            <h2 class="section-title"><?= e($servicosSectionTitle ?? 'Nossos Serviços') ?></h2>
            <p class="section-subtitle"><?= e($servicosSectionSub ?? 'Soluções completas para captação de água subterrânea, do estudo inicial à manutenção.') ?></p>
        </div>

        <div class="row g-4">
            <?php foreach ($servicos as $servico) { ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="service-card h-100 d-flex flex-column">
                    <div class="icon"><i class="bi bi-<?= e($servico['icon'] ?? 'droplet') ?>"></i></div>
                    <h3><?= e($servico['title']) ?></h3>
                    <p class="flex-grow-1"><?= e(str_limit($servico['description'] ?? '', 130)) ?></p>
                    <a href="/servicos/<?= e($servico['slug']) ?>" class="btn btn-outline-primary btn-sm mt-3 align-self-center">
                        Saiba mais <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</section>

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

<!-- Parceiros / divulgações -->
<?php if (!empty($banners)) { ?>
<section class="section section-alt">
    <div class="container">
        <h2 class="section-title text-center">Parceiros &amp; Divulgações</h2>
        <div class="row g-4 justify-content-center mt-2">
            <?php foreach ($banners as $banner) { ?>
            <div class="col-12 col-md-4 col-lg-3">
                <a href="<?= e($banner['link_url'] ?? '#') ?>" target="_blank" rel="noopener" class="card h-100 text-decoration-none">
                    <?php if (!empty($banner['image'])) { ?>
                        <img src="<?= e($banner['image']) ?>" class="card-img-top p-3" alt="<?= e($banner['title']) ?>" style="height:140px;object-fit:contain">
                    <?php } ?>
                    <div class="card-body text-center">
                        <h5 class="card-title"><?= e($banner['title']) ?></h5>
                        <?php if (!empty($banner['subtitle'])) { ?>
                            <p class="card-text text-muted small mb-0"><?= e($banner['subtitle']) ?></p>
                        <?php } ?>
                    </div>
                </a>
            </div>
            <?php } ?>
        </div>
    </div>
</section>
<?php } ?>

<!-- Seções livres criadas no CMS (aparecem antes do CTA final) -->
<?php foreach (($secoes ?? []) as $indiceSecao => $secao) { ?>
<section class="section<?= $indiceSecao % 2 === 1 ? ' section-alt' : '' ?>" id="secao-<?= e($secao['id']) ?>">
    <div class="container">
        <div class="text-center mb-4">
            <?php if (!empty($secao['subtitle'])) { ?>
                <span class="eyebrow eyebrow-dark"><?= e($secao['subtitle']) ?></span>
            <?php } ?>
            <h2 class="section-title"><?= e($secao['title']) ?></h2>
        </div>

        <div class="row g-4 align-items-center">
            <?php if (!empty($secao['image'])) { ?>
                <div class="col-lg-5">
                    <img src="<?= e($secao['image']) ?>" alt="<?= e($secao['title']) ?>"
                         class="img-fluid rounded-3 shadow-sm" loading="lazy">
                </div>
            <?php } ?>

            <div class="<?= !empty($secao['image']) ? 'col-lg-7' : 'col-12' ?>">
                <div class="section-content">
                    <?= $secao['content'] ?: '<p class="text-muted mb-0">' . e($secao['subtitle'] ?? '') . '</p>' ?>
                </div>

                <?php if (!empty($secao['link_url'])) { ?>
                    <a href="<?= e($secao['link_url']) ?>" class="btn btn-outline-primary mt-3">
                        <?= e($secao['link_text'] ?: 'Saiba mais') ?>
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>
</section>
<?php } ?>

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
                <a href="https://wa.me/5514991234567" target="_blank" rel="noopener" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-whatsapp"></i> WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>

<?php $view->endSection(); ?>
