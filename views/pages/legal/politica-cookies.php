<?php $view->layout('layouts.main'); ?>

<?php
$secoes = [
    ['id' => 'o-que-sao', 't' => 'O que são cookies'],
    ['id' => 'tipos', 't' => 'Tipos de cookies'],
    ['id' => 'usamos', 't' => 'Cookies que utilizamos'],
    ['id' => 'consentimento', 't' => 'Consentimento e revogação'],
    ['id' => 'gerenciar', 't' => 'Como gerenciar no navegador'],
    ['id' => 'terceiros', 't' => 'Cookies de terceiros'],
    ['id' => 'alteracoes', 't' => 'Alterações'],
    ['id' => 'contato', 't' => 'Contato'],
];
?>

<?php $view->section('styles'); ?>
<link rel="stylesheet" href="<?= e(asset_v('assets/css/legal.css')) ?>">
<?php $view->endSection(); ?>

<?php $view->section('content'); ?>
<?= $view->partial('pages.legal._hero', [
    'title' => 'Política de Cookies',
    'subtitle' => $subtitle,
    'atualizadoEm' => $atualizadoEm,
    'versao' => $versao,
]) ?>

<div class="container legal-layout">
    <aside class="legal-toc">
        <div class="legal-toc-card">
            <h2>Nesta página</h2>
            <ol>
                <?php foreach ($secoes as $s) { ?>
                    <li><a href="#<?= e($s['id']) ?>"><?= e($s['t']) ?></a></li>
                <?php } ?>
            </ol>
            <div class="legal-actions">
                <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="window.print()">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
            </div>
        </div>
    </aside>

    <article class="legal-content">
        <section id="o-que-sao">
            <h2><span class="num">1</span> O que são cookies</h2>
            <p>
                Cookies são pequenos arquivos de texto gravados no seu navegador quando você visita um site.
                Eles permitem reconhecer o seu dispositivo, manter preferências e garantir o funcionamento
                correto de recursos como login e área restrita.
            </p>
            <p>
                Também usamos, de forma limitada, <em>localStorage</em> para guardar preferências de interface,
                como o tema claro/escuro do painel.
            </p>
        </section>

        <section id="tipos">
            <h2><span class="num">2</span> Tipos de cookies</h2>
            <div class="legal-grid">
                <div class="legal-card">
                    <i class="bi bi-shield-lock"></i>
                    <h3>Estritamente necessários</h3>
                    <p>Imprescindíveis para o site funcionar (segurança, sessão, consentimento). Não podem ser desativados.</p>
                </div>
                <div class="legal-card">
                    <i class="bi bi-sliders"></i>
                    <h3>De preferência</h3>
                    <p>Guardam escolhas como tema e idioma para melhorar sua experiência.</p>
                </div>
                <div class="legal-card">
                    <i class="bi bi-graph-up"></i>
                    <h3>De medição</h3>
                    <p>Ajudam a entender como o site é usado. Só são ativados com o seu consentimento.</p>
                </div>
                <div class="legal-card">
                    <i class="bi bi-megaphone"></i>
                    <h3>De marketing</h3>
                    <p>Usados para publicidade personalizada. <strong>Atualmente não utilizamos.</strong></p>
                </div>
            </div>
        </section>

        <section id="usamos">
            <h2><span class="num">3</span> Cookies que utilizamos</h2>
            <table class="legal-table">
                <thead>
                    <tr><th>Nome</th><th>Categoria</th><th>Finalidade</th><th>Duração</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>hidrossolo_session</code></td>
                        <td>Essencial</td>
                        <td>Identifica a sessão autenticada do usuário (área restrita e painel administrativo), com proteção CSRF.</td>
                        <td>Sessão (expira ao fechar o navegador ou por inatividade)</td>
                    </tr>
                    <tr>
                        <td><code>cookie_consent</code></td>
                        <td>Essencial / Preferência</td>
                        <td>Registra que você viu e respondeu ao aviso de cookies.</td>
                        <td>12 meses</td>
                    </tr>
                    <tr>
                        <td><code>localStorage: theme</code></td>
                        <td>Preferência</td>
                        <td>Guarda o tema (claro/escuro) escolhido no painel.</td>
                        <td>Até você limpar os dados do navegador</td>
                    </tr>
                </tbody>
            </table>
            <div class="legal-callout legal-ok">
                <i class="bi bi-check-circle-fill"></i>
                <div>
                    <strong>Não utilizamos cookies de publicidade</strong>, rastreamento entre sites ou
                    ferramentas de análise de terceiros no momento. Se isso mudar, esta política será atualizada
                    e o consentimento será solicitado novamente.
                </div>
            </div>
        </section>

        <section id="consentimento">
            <h2><span class="num">4</span> Consentimento e revogação</h2>
            <p>
                Ao acessar o site pela primeira vez, exibimos um aviso com a opção de aceitar os cookies não
                essenciais. A sua escolha é registrada e pode ser alterada a qualquer momento.
            </p>
            <div class="legal-callout">
                <i class="bi bi-arrow-counterclockwise"></i>
                <div>
                    <strong>Revogar agora:</strong> clique no botão abaixo para apagar a sua preferência e ver o
                    aviso novamente na próxima página.
                    <div class="mt-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="revogarCookies">
                            <i class="bi bi-x-circle"></i> Revogar consentimento de cookies
                        </button>
                    </div>
                </div>
            </div>
            <p class="text-muted small">
                A revogação não afeta a legalidade dos tratamentos realizados anteriormente, conforme o
                art. 8º, § 5º da LGPD.
            </p>
        </section>

        <section id="gerenciar">
            <h2><span class="num">5</span> Como gerenciar no navegador</h2>
            <p>Você pode bloquear ou excluir cookies nas configurações do navegador:</p>
            <ul>
                <li><strong>Chrome:</strong> Configurações → Privacidade e segurança → Cookies e outros dados;</li>
                <li><strong>Edge:</strong> Configurações → Cookies e permissões do site;</li>
                <li><strong>Firefox:</strong> Configurações → Privacidade e segurança → Cookies e dados do site;</li>
                <li><strong>Safari:</strong> Preferências → Privacidade → Gerenciar dados do site.</li>
            </ul>
            <div class="legal-callout legal-warn">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    Bloquear cookies essenciais pode impedir o funcionamento de partes do site, como o login na
                    área restrita.
                </div>
            </div>
        </section>

        <section id="terceiros">
            <h2><span class="num">6</span> Cookies de terceiros</h2>
            <p>
                O site carrega bibliotecas de interface (ícones e gráficos) por meio de CDN para melhorar
                desempenho. Essas requisições podem registrar o seu IP no provedor da CDN, sem finalidade de
                identificação. Mapas incorporados, quando presentes, são fornecidos pelo Google e seguem a
                política de privacidade do próprio Google.
            </p>
        </section>

        <section id="alteracoes">
            <h2><span class="num">7</span> Alterações</h2>
            <p>
                Esta política pode ser atualizada. A versão vigente, com data de revisão, permanece sempre
                disponível nesta página.
            </p>
        </section>

        <section id="contato">
            <h2><span class="num">8</span> Contato</h2>
            <table class="legal-table">
                <tbody>
                    <tr><th style="width:34%">Encarregado (DPO)</th><td>privacidade@hidrossolo.com.br</td></tr>
                    <tr><th>Telefone</th><td>(14) 3413-2437</td></tr>
                    <tr><th>Solicitações com protocolo</th><td><a href="/lgpd#solicitacao">Central LGPD</a></td></tr>
                </tbody>
            </table>
        </section>
    </article>
</div>
<?php $view->endSection(); ?>

<?php $view->section('scripts'); ?>
<script>
document.getElementById('revogarCookies')?.addEventListener('click', function () {
    document.cookie = 'cookie_consent=;path=/;max-age=0';
    alert('Consentimento revogado. O aviso de cookies aparecerá novamente.');
    window.location.reload();
});
</script>
<?php $view->endSection(); ?>
