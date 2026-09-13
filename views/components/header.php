<?php
/** Cabeçalho / navegação principal do site. */
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$isActive = static function (string $path) use ($uri): string {
    if ($path === '/') {
        return $uri === '/' ? 'active' : '';
    }
    return str_contains($uri, $path) ? 'active' : '';
};
?>
<nav class="navbar navbar-expand-lg fixed-top bg-white">
    <div class="container">
        <a class="navbar-brand" href="/">
            <img src="<?= e($logo ?? '/assets/images/hidrossolo.png') ?>" alt="Hidrossolo Poços Artesianos">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain"
                aria-controls="navbarMain" aria-expanded="false" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
            <span class="navbar-toggler-icon"></span>
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link <?= $isActive('/') ?>" href="/">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= $isActive('/empresa') ?>" href="/empresa">Empresa</a></li>
                <li class="nav-item"><a class="nav-link <?= $isActive('/servicos') ?>" href="/servicos">Serviços</a></li>
                <li class="nav-item"><a class="nav-link <?= $isActive('/blog') ?>" href="/blog">Blog</a></li>
                <li class="nav-item"><a class="nav-link <?= $isActive('/contato') ?>" href="/contato">Contato</a></li>
                <li class="nav-item"><a class="nav-link <?= $isActive('/orcamento') ?>" href="/orcamento">Orçamento</a></li>
                <li class="nav-item d-none d-lg-flex align-items-center ms-2">
                    <form action="/busca" method="GET" class="d-flex" role="search">
                        <input type="search" name="q" class="form-control form-control-sm"
                               placeholder="Buscar..." aria-label="Buscar"
                               value="<?= e($_GET['q'] ?? '') ?>" style="width:170px">
                        <button type="submit" class="btn btn-sm btn-outline-primary ms-1" aria-label="Buscar">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </li>
            </ul>

            <a href="/orcamento" class="btn btn-primary ms-3 d-none d-lg-inline-flex">
                <i class="bi bi-send"></i> Solicitar Orçamento
            </a>
            <a href="/orcamento" class="btn btn-primary w-100 mt-3 d-lg-none">Solicitar Orçamento</a>
        </div>
    </div>
</nav>
