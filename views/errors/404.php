<?php $view->layout('layouts.main'); ?>

<?php $view->section('content'); ?>
<section class="page-hero" style="padding-bottom:4rem">
    <div class="container text-center">
        <span class="eyebrow">Erro 404</span>
        <h1 style="font-size:clamp(3.5rem,12vw,6rem);line-height:1;margin-bottom:.25rem">404</h1>
        <p class="lead mb-0">Este poço veio seco.</p>
    </div>
</section>

<section class="section" style="padding-top:2.5rem">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <p class="text-muted mb-4">
                    A página que você procura não existe, foi removida ou ainda não recebeu conteúdo.
                    Mas água boa é o que não falta por aqui — escolha um caminho abaixo e siga em frente.
                </p>

                <form action="/busca" method="GET" class="mx-auto mb-4" style="max-width:460px">
                    <div class="input-group">
                        <input type="search" name="q" class="form-control" placeholder="O que você procura? Ex.: perfuração, outorga..."
                               aria-label="Buscar no site" value="<?= e($_GET['q'] ?? '') ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </form>

                <div class="d-flex flex-wrap gap-2 justify-content-center mb-5">
                    <a href="/" class="btn btn-primary btn-lg"><i class="bi bi-house-door"></i> Voltar ao início</a>
                    <a href="/servicos" class="btn btn-outline-primary btn-lg"><i class="bi bi-droplet"></i> Nossos serviços</a>
                    <a href="https://wa.me/5514991234567" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-whatsapp"></i> Falar com a equipe
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-12 col-md-6 col-lg-3">
                <a href="/servicos" class="service-card h-100 d-block text-decoration-none">
                    <div class="icon"><i class="bi bi-droplet"></i></div>
                    <h3>Perfuração</h3>
                    <p>Poços artesianos com estudo geológico e regularização.</p>
                </a>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <a href="/servicos" class="service-card h-100 d-block text-decoration-none">
                    <div class="icon"><i class="bi bi-tools"></i></div>
                    <h3>Limpeza e manutenção</h3>
                    <p>Recupere a vazão do seu poço com quem entende do assunto.</p>
                </a>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <a href="/empresa" class="service-card h-100 d-block text-decoration-none">
                    <div class="icon"><i class="bi bi-people"></i></div>
                    <h3>Quem somos</h3>
                    <p>Mais de 20 anos perfurando em Marília e região.</p>
                </a>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <a href="/orcamento" class="service-card h-100 d-block text-decoration-none">
                    <div class="icon"><i class="bi bi-send"></i></div>
                    <h3>Orçamento</h3>
                    <p>Solicite uma avaliação sem compromisso.</p>
                </a>
            </div>
        </div>
    </div>
</section>
<?php $view->endSection(); ?>
