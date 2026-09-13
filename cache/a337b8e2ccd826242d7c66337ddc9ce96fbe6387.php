<footer>
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5><i class="bi bi-droplet me-2"></i>Hidrossolo</h5>
                <p>Especialistas em perfuração de poços artesianos, licenciamento e manutenção. Atendendo Marília e região com excelência desde 2005.</p>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook fs-5"></i></a>
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin fs-5"></i></a>
                </div>
            </div>
            <div class="col-lg-3">
                <h5>Links Rápidos</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="/">Home</a></li>
                    <li class="mb-2"><a href="/empresa">Empresa</a></li>
                    <li class="mb-2"><a href="/servicos">Serviços</a></li>
                    <li class="mb-2"><a href="/blog">Blog</a></li>
                    <li class="mb-2"><a href="/contato">Contato</a></li>
                </ul>
            </div>
            <div class="col-lg-3">
                <h5>Serviços</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="/servicos">Perfuração de Poços</a></li>
                    <li class="mb-2"><a href="/servicos">Licenciamento e Outorgas</a></li>
                    <li class="mb-2"><a href="/servicos">Limpeza de Poços</a></li>
                    <li class="mb-2"><a href="/servicos">Instalação de Bombas</a></li>
                    <li class="mb-2"><a href="/servicos">Manutenção</a></li>
                </ul>
            </div>
            <div class="col-lg-2">
                <h5>Contato</h5>
                <p><i class="bi bi-geo-alt me-2"></i>R. Assad Haddad, 584<br>Parque das Indústrias<br>Marília - SP</p>
                <p><i class="bi bi-telephone me-2"></i>(14) 3413-2437</p>
                <p><i class="bi bi-envelope me-2"></i>contato@hidrossolo.com.br</p>
            </div>
        </div>
        <div class="row g-3 mt-3 pt-4 border-top border-secondary">
            <div class="col-md-6">
                <h6>📬 Fique por dentro</h6>
                <form action="/newsletter" method="POST" class="d-flex gap-2">
                    <?php echo \App\Middleware\CsrfMiddleware::field(); ?>
                    <input type="email" name="email" class="form-control form-control-sm" placeholder="Seu melhor e-mail" required>
                    <button type="submit" class="btn btn-sm btn-primary" style="white-space:nowrap">Inscrever</button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p class="mb-0">&copy; <?php echo e(date('Y')); ?> Hidrossolo Poços Artesianos. Todos os direitos reservados.</p>
        </div>
    </div>
</footer>
<?php /**PATH /var/www/html/views/components/footer.blade.php ENDPATH**/ ?>