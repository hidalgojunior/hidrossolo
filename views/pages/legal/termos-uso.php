<?php $view->layout('layouts.main'); ?>

<?php
$secoes = [
    ['id' => 'aceitacao', 't' => 'Aceitação'],
    ['id' => 'servicos', 't' => 'Serviços oferecidos'],
    ['id' => 'orcamentos', 't' => 'Orçamentos e propostas'],
    ['id' => 'obrigacoes', 't' => 'Uso adequado do site'],
    ['id' => 'propriedade', 't' => 'Propriedade intelectual'],
    ['id' => 'responsabilidade', 't' => 'Limitação de responsabilidade'],
    ['id' => 'links', 't' => 'Links externos'],
    ['id' => 'garantia', 't' => 'Garantia e pós-venda'],
    ['id' => 'suspensao', 't' => 'Suspensão e alterações'],
    ['id' => 'lei', 't' => 'Legislação e foro'],
    ['id' => 'contato', 't' => 'Contato'],
];
?>

<?php $view->section('styles'); ?>
<link rel="stylesheet" href="<?= e(asset_v('assets/css/legal.css')) ?>">
<?php $view->endSection(); ?>

<?php $view->section('content'); ?>
<?= $view->partial('pages.legal._hero', [
    'title' => 'Termos de Uso',
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
        <section id="aceitacao">
            <h2><span class="num">1</span> Aceitação</h2>
            <p>
                Ao navegar neste site ou utilizar nossos canais de atendimento, você concorda com estes Termos
                de Uso e com a <a href="/politica-de-privacidade">Política de Privacidade</a>. Se não concordar,
                pedimos que não utilize o site.
            </p>
        </section>

        <section id="servicos">
            <h2><span class="num">2</span> Serviços oferecidos</h2>
            <p>
                A Hidrossolo Poços Artesianos atua na perfuração, limpeza, manutenção, instalação de bombas e
                regularização de poços artesianos, além de serviços correlatos de captação de água subterrânea
                em Marília/SP e região.
            </p>
            <p>
                As informações do site têm caráter informativo e não substituem a avaliação técnica presencial,
                necessária para definir viabilidade, profundidade, vazão e orçamento.
            </p>
        </section>

        <section id="orcamentos">
            <h2><span class="num">3</span> Orçamentos e propostas</h2>
            <ul>
                <li>Toda solicitação enviada pelo site é uma <strong>proposta de contato</strong>, não um contrato firmado;</li>
                <li>Valores e prazos dependem de estudo geológico, acesso ao local e disponibilidade de equipamentos;</li>
                <li>O contrato só se perfecciona após aceite formal da proposta por ambas as partes;</li>
                <li>Condições específicas (medições, etapas, materiais e garantias) constam no instrumento contratual.</li>
            </ul>
        </section>

        <section id="obrigacoes">
            <h2><span class="num">4</span> Uso adequado do site</h2>
            <p>Ao utilizar o site, você se compromete a <strong>não</strong>:</p>
            <ul>
                <li>Enviar conteúdo ilícito, ofensivo, falso ou que viole direitos de terceiros;</li>
                <li>Tentar obter acesso não autorizado a áreas restritas, servidores ou dados;</li>
                <li>Automatizar requisições em volume que comprometa a disponibilidade do site;</li>
                <li>Utilizar o site para disseminar fraudes, <em>phishing</em>, <em>malware</em> ou spam;</li>
                <li>Reproduzir, copiar ou explorar comercialmente o conteúdo sem autorização.</li>
            </ul>
            <div class="legal-callout legal-warn">
                <i class="bi bi-shield-exclamation"></i>
                <div>
                    Ações maliciosas são registradas em log e podem ser comunicadas às autoridades competentes,
                    nos termos da Lei nº 12.737/2012 e do Marco Civil da Internet.
                </div>
            </div>
        </section>

        <section id="propriedade">
            <h2><span class="num">5</span> Propriedade intelectual</h2>
            <p>
                Textos, imagens, logotipos, fotos de obras, layout e conteúdo deste site pertencem à Hidrossolo
                ou são utilizados com autorização. É vedada a reprodução total ou parcial sem consentimento
                prévio e expresso, ressalvado o uso para fins informativos com citação da fonte.
            </p>
        </section>

        <section id="responsabilidade">
            <h2><span class="num">6</span> Limitação de responsabilidade</h2>
            <ul>
                <li>Empregamos boas práticas para manter o site disponível e livre de erros, mas não garantimos funcionamento ininterrupto;</li>
                <li>Não nos responsabilizamos por danos decorrentes de uso indevido do conteúdo informativo do site;</li>
                <li>Decisões técnicas sobre o poço devem seguir a avaliação e o laudo da nossa equipe;</li>
                <li>Não somos responsáveis por indisponibilidade de serviços de terceiros (internet, hospedagem, CDNs).</li>
            </ul>
        </section>

        <section id="links">
            <h2><span class="num">7</span> Links externos</h2>
            <p>
                O site pode conter links para páginas de terceiros (mapas, redes sociais, órgãos ambientais).
                Não controlamos esses conteúdos e recomendamos a leitura das políticas de privacidade dos
                respectivos sites.
            </p>
            <div class="legal-callout">
                <i class="bi bi-shield-lock"></i>
                <div>
                    <strong>Alerta contra phishing:</strong> a Hidrossolo <strong>nunca</strong> solicita senha,
                    código de verificação ou pagamento por e-mail, SMS ou WhatsApp. Confirme sempre pelo telefone
                    oficial <strong>(14) 3413-2437</strong> antes de qualquer pagamento.
                </div>
            </div>
        </section>

        <section id="garantia">
            <h2><span class="num">8</span> Garantia e pós-venda</h2>
            <p>
                As condições de garantia, prazos de execução, ensaios de vazão e manutenções estão descritas no
                contrato de prestação de serviços. Solicitações de suporte podem ser feitas pelos nossos canais
                de atendimento, observando os prazos contratuais e as normas técnicas aplicáveis.
            </p>
        </section>

        <section id="suspensao">
            <h2><span class="num">9</span> Suspensão e alterações</h2>
            <p>
                Podemos suspender o site para manutenção, bem como alterar estes Termos de Uso a qualquer
                momento. A versão vigente estará sempre publicada nesta página, com data de atualização.
            </p>
        </section>

        <section id="lei">
            <h2><span class="num">10</span> Legislação e foro</h2>
            <p>
                Estes Termos são regidos pela legislação brasileira, em especial pelo Código Civil, pelo Código
                de Defesa do Consumidor e pelo Marco Civil da Internet. Fica eleito o foro da comarca de
                <strong>Marília/SP</strong>, sem prejuízo do direito do consumidor previsto em lei.
            </p>
        </section>

        <section id="contato">
            <h2><span class="num">11</span> Contato</h2>
            <table class="legal-table">
                <tbody>
                    <tr><th style="width:34%">Empresa</th><td>Hidrossolo Poços Artesianos</td></tr>
                    <tr><th>Endereço</th><td>R. Assad Haddad, 584 — Parque das Indústrias, Marília/SP — CEP 17519-700</td></tr>
                    <tr><th>Telefone</th><td>(14) 3413-2437</td></tr>
                    <tr><th>E-mail</th><td>contato@hidrossolo.com.br</td></tr>
                </tbody>
            </table>
        </section>
    </article>
</div>
<?php $view->endSection(); ?>
