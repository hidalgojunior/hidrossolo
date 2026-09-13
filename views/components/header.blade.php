<nav class="navbar navbar-expand-lg fixed-top bg-white">
    <div class="container">
        <a class="navbar-brand" href="/">
            <img src="{{ $logo ?? '/assets/images/hidrossolo.png' }}" alt="Hidrossolo">
            Hidrossolo
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link {{ $_SERVER['REQUEST_URI'] == '/' ? 'active' : '' }}" href="/">Home</a></li>
                <li class="nav-item"><a class="nav-link {{ str_contains($_SERVER['REQUEST_URI'], '/empresa') ? 'active' : '' }}" href="/empresa">Empresa</a></li>
                <li class="nav-item"><a class="nav-link {{ str_contains($_SERVER['REQUEST_URI'], '/servicos') ? 'active' : '' }}" href="/servicos">Serviços</a></li>
                <li class="nav-item"><a class="nav-link {{ str_contains($_SERVER['REQUEST_URI'], '/blog') ? 'active' : '' }}" href="/blog">Blog</a></li>
                <li class="nav-item"><a class="nav-link {{ str_contains($_SERVER['REQUEST_URI'], '/contato') ? 'active' : '' }}" href="/contato">Contato</a></li>
                <li class="nav-item"><a class="nav-link {{ str_contains($_SERVER['REQUEST_URI'], '/orcamento') ? 'active' : '' }}" href="/orcamento">Orçamento</a></li>
                <li class="nav-item d-none d-lg-flex align-items-center ms-2">
                    <form action="/busca" method="GET" class="d-flex">
                        <input type="search" name="q" class="form-control form-control-sm" placeholder="Buscar..." value="{{ $_GET['q'] ?? '' }}" style="width:160px">
                        <button type="submit" class="btn btn-sm btn-outline-primary ms-1"><i class="bi bi-search"></i></button>
                    </form>
                </li>
            </ul>
            <a href="/contato" class="btn btn-primary ms-3 d-none d-lg-inline-block">Solicitar Orçamento</a>
        </div>
    </div>
</nav>
