<?php
/** Rodapé institucional. */
$year = date('Y');
?>
<footer class="site-footer">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4">
                <h5><i class="bi bi-droplet me-2"></i>Hidrossolo</h5>
                <p>Especialistas em perfuração de poços artesianos, licenciamento e manutenção. Atendendo Marília e região com excelência desde 2005.</p>
                <?php $redes = company_social_links($company); ?>
                <?php if ($redes !== []) { ?>
                <div class="d-flex flex-wrap gap-3 mt-3">
                    <?php foreach ($redes as $rede) { ?>
                    <a href="<?= e($rede['url']) ?>" target="_blank" rel="noopener noreferrer"
                       aria-label="<?= e($rede['nome']) ?>" title="<?= e($rede['nome']) ?>">
                        <i class="bi <?= e($rede['icone']) ?> fs-5"></i>
                    </a>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>

            <div class="col-lg-2">
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

            <div class="col-lg-3">
                <h5>Contato</h5>
                <?php $endereco = company_address_lines($company); ?>
                <?php if ($endereco !== []) { ?>
                <p><i class="bi bi-geo-alt me-2"></i><a href="<?= e(company_maps_link($company)) ?>" target="_blank" rel="noopener noreferrer" title="Abrir no mapa"><?= implode('<br>', array_map('e', $endereco)) ?></a></p>
                <?php } ?>
                <?php $telefones = company_lines($company['phone'] ?? ''); ?>
                <?php if ($telefones !== []) { ?>
                <p><i class="bi bi-telephone me-2"></i><?= implode('<br>', array_map(static fn (string $t): string => '<a href="' . e(company_tel_link($t)) . '">' . e($t) . '</a>', $telefones)) ?></p>
                <?php } ?>
                <?php if (($company['whatsapp'] ?? '') !== '') { ?>
                <p><i class="bi bi-whatsapp me-2"></i><a href="<?= e(company_whatsapp_link($company['whatsapp'])) ?>" target="_blank" rel="noopener noreferrer"><?= e($company['whatsapp']) ?></a></p>
                <?php } ?>
                <?php if (($company['email'] ?? '') !== '') { ?>
                <p><i class="bi bi-envelope me-2"></i><a href="mailto:<?= e($company['email']) ?>"><?= e($company['email']) ?></a></p>
                <?php } ?>
                <?php $horario = company_lines($company['working_hours'] ?? ''); ?>
                <?php if ($horario !== []) { ?>
                <p><i class="bi bi-clock me-2"></i><?= implode('<br>', array_map('e', $horario)) ?></p>
                <?php } ?>
            </div>
        </div>

        <div class="row g-3 mt-4 pt-4 border-top border-secondary">
            <div class="col-md-6">
                <h6><i class="bi bi-envelope-paper me-1"></i> Fique por dentro</h6>
                <form action="/newsletter" method="POST" class="d-flex gap-2 flex-wrap">
                    <?= csrf_field() ?>
                    <input type="email" name="email" class="form-control form-control-sm"
                           placeholder="Seu melhor e-mail" required style="flex:1;min-width:200px">
                    <button type="submit" class="btn btn-sm btn-primary" style="white-space:nowrap">Inscrever</button>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-bottom-credits">
                <p class="mb-0">&copy; <?= e($year) ?> Hidrossolo Poços Artesianos. Todos os direitos reservados.</p>
                <p class="mb-0 footer-bottom-dev">
                    Desenvolvido por
                    <a href="mailto:hidalgojunior@gmail.com" title="Falar com o desenvolvedor">Arnaldo Martins Hidalgo Junior</a>
                </p>
            </div>
            <p class="mb-0 footer-bottom-links">
                <a href="/politica-de-privacidade">Privacidade</a>
                <span class="footer-divider" aria-hidden="true">•</span>
                <a href="/politica-de-cookies">Cookies</a>
                <span class="footer-divider" aria-hidden="true">•</span>
                <a href="/termos-de-uso">Termos de Uso</a>
                <span class="footer-divider" aria-hidden="true">•</span>
                <a href="/lgpd">LGPD</a>
                <span class="footer-divider" aria-hidden="true">•</span>
                <a href="/admin/login" class="footer-admin-link" title="Acesso restrito à equipe Hidrossolo">
                    <i class="bi bi-lock-fill"></i> Área Restrita
                </a>
            </p>
        </div>
    </div>
</footer>
