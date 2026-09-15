<?php $view->layout('layouts.main'); ?>

<?php
$secoes = [
    ['id' => 'compromisso', 't' => 'Nosso compromisso'],
    ['id' => 'controlador', 't' => 'Quem é o controlador'],
    ['id' => 'definicoes', 't' => 'Definições'],
    ['id' => 'dados', 't' => 'Dados que coletamos'],
    ['id' => 'coleta', 't' => 'Como coletamos'],
    ['id' => 'bases-legais', 't' => 'Bases legais'],
    ['id' => 'finalidades', 't' => 'Finalidades'],
    ['id' => 'compartilhamento', 't' => 'Compartilhamento'],
    ['id' => 'internacional', 't' => 'Transferência internacional'],
    ['id' => 'retencao', 't' => 'Retenção e eliminação'],
    ['id' => 'seguranca', 't' => 'Segurança da informação'],
    ['id' => 'direitos', 't' => 'Seus direitos'],
    ['id' => 'cookies', 't' => 'Cookies'],
    ['id' => 'criancas', 't' => 'Crianças e adolescentes'],
    ['id' => 'automatizadas', 't' => 'Decisões automatizadas'],
    ['id' => 'alteracoes', 't' => 'Alterações desta política'],
    ['id' => 'lei', 't' => 'Legislação e foro'],
    ['id' => 'contato', 't' => 'Contato do encarregado'],
];
?>

<?php $view->section('styles'); ?>
<link rel="stylesheet" href="<?= e(asset_v('assets/css/legal.css')) ?>">
<?php $view->endSection(); ?>

<?php $view->section('content'); ?>
<?= $view->partial('pages.legal._hero', [
    'title' => 'Política de Privacidade',
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
        <section id="compromisso">
            <h2><span class="num">1</span> Nosso compromisso</h2>
            <p>
                A <strong>Hidrossolo Poços Artesianos</strong> respeita a sua privacidade e está comprometida em
                tratar seus dados pessoais com transparência, segurança e finalidade legítima, em conformidade com a
                <strong>Lei Geral de Proteção de Dados Pessoais (Lei nº 13.709/2018 — LGPD)</strong>,
                o Marco Civil da Internet (Lei nº 12.965/2014) e demais normas aplicáveis.
            </p>
            <p>
                Esta política explica quais dados coletamos, por que coletamos, como utilizamos, com quem
                compartilhamos, por quanto tempo guardamos e como você pode exercer os seus direitos.
            </p>
        </section>

        <section id="controlador">
            <h2><span class="num">2</span> Quem é o controlador</h2>
            <p>
                O <strong>controlador</strong> — ou seja, quem decide sobre o tratamento dos seus dados — é a
                Hidrossolo Poços Artesianos.
            </p>
            <table class="legal-table">
                <tbody>
                    <tr><th style="width:34%">Razão social</th><td>Hidrossolo Poços Artesianos</td></tr>
                    <tr><th>CNPJ</th><td>(<em>a informar no cadastro oficial da empresa</em>)</td></tr>
                    <tr><th>Endereço</th><td><?= e(implode(' — ', company_address_lines($company))) ?></td></tr>
                    <tr><th>Telefone</th><td><?= e($company['phone']) ?></td></tr>
                    <tr><th>E-mail de privacidade</th><td><?= e(lgpd_email()) ?></td></tr>
                    <tr><th>Encarregado (DPO)</th><td><?= e(lgpd_email()) ?></td></tr>
                </tbody>
            </table>
            <div class="legal-callout">
                <i class="bi bi-info-circle-fill"></i>
                <div>
                    O <strong>encarregado pelo tratamento de dados pessoais (DPO)</strong> é o canal oficial para
                    assuntos de privacidade. Você também pode usar a
                    <a href="/lgpd#solicitacao">Central LGPD</a> para registrar solicitações com protocolo.
                </div>
            </div>
        </section>

        <section id="definicoes">
            <h2><span class="num">3</span> Definições</h2>
            <ul>
                <li><strong>Dado pessoal:</strong> informação que identifica ou torna identificável uma pessoa natural.</li>
                <li><strong>Dado pessoal sensível:</strong> dado sobre origem racial, convicção religiosa, saúde, biometria, entre outros (art. 5º, II).</li>
                <li><strong>Titular:</strong> a pessoa natural a quem os dados se referem — você.</li>
                <li><strong>Tratamento:</strong> qualquer operação com dados (coleta, uso, armazenamento, compartilhamento, eliminação).</li>
                <li><strong>Controlador:</strong> quem decide sobre o tratamento. <strong>Operador:</strong> quem executa o tratamento em nome do controlador.</li>
                <li><strong>Consentimento:</strong> manifestação livre, informada e inequívoca pela qual você autoriza o tratamento.</li>
            </ul>
        </section>

        <section id="dados">
            <h2><span class="num">4</span> Dados que coletamos</h2>
            <table class="legal-table">
                <thead>
                    <tr><th>Categoria</th><th>Exemplos</th><th>Origem</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Identificação e contato</strong></td>
                        <td>Nome, telefone, e-mail, cidade, mensagem enviada</td>
                        <td>Formulários de contato, orçamento, newsletter e WhatsApp</td>
                    </tr>
                    <tr>
                        <td><strong>Dados contratuais</strong></td>
                        <td>Nome/razão social, CPF/CNPJ, endereço da obra, dados do imóvel, documentos e informações do poço</td>
                        <td>Elaboração e execução de contratos de serviço</td>
                    </tr>
                    <tr>
                        <td><strong>Dados técnicos e de navegação</strong></td>
                        <td>Endereço IP, tipo de navegador e dispositivo, páginas acessadas, data e hora</td>
                        <td>Registros de acesso do servidor (Lei nº 12.965/2014, art. 15)</td>
                    </tr>
                    <tr>
                        <td><strong>Cookies e preferências</strong></td>
                        <td>Identificador de sessão, preferência de consentimento</td>
                        <td>Navegação no site</td>
                    </tr>
                    <tr>
                        <td><strong>Registros de atendimento</strong></td>
                        <td>Protocolo, histórico de solicitações LGPD, respostas e andamentos</td>
                        <td>Central LGPD e atendimento ao cliente</td>
                    </tr>
                </tbody>
            </table>
            <div class="legal-callout legal-warn">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    <strong>Não coletamos dados sensíveis</strong> para as finalidades deste site e
                    <strong>não solicitamos</strong> dados de pagamento por e-mail, SMS ou WhatsApp.
                    Se alguém pedir isso em nome da Hidrossolo, desconfie: é tentativa de fraude.
                </div>
            </div>
        </section>

        <section id="coleta">
            <h2><span class="num">5</span> Como coletamos</h2>
            <ul>
                <li><strong>Diretamente de você:</strong> ao preencher o formulário de contato, solicitar orçamento, assinar a newsletter ou nos chamar no WhatsApp.</li>
                <li><strong>Na execução do contrato:</strong> dados necessários à perfuração, regularização e manutenção do poço.</li>
                <li><strong>Automaticamente:</strong> registros de acesso gerados pelo servidor e cookies estritamente necessários ao funcionamento do site.</li>
                <li><strong>De terceiros:</strong> informações de órgãos públicos (ex.: DAEE, CETESB) quando exigidas para licenciamento e outorga, sempre com base legal adequada.</li>
            </ul>
        </section>

        <section id="bases-legais">
            <h2><span class="num">6</span> Bases legais</h2>
            <p>Todo tratamento realizado tem fundamento nas hipóteses do art. 7º da LGPD:</p>
            <table class="legal-table">
                <thead><tr><th>Finalidade</th><th>Base legal</th></tr></thead>
                <tbody>
                    <tr><td>Responder solicitações de contato e orçamento</td><td>Consentimento (art. 7º, I) e legítimo interesse (art. 7º, IX)</td></tr>
                    <tr><td>Elaborar e executar contratos de serviços</td><td>Execução de contrato (art. 7º, V)</td></tr>
                    <tr><td>Emitir documentos fiscais e cumprir obrigações legais</td><td>Obrigação legal ou regulatória (art. 7º, II)</td></tr>
                    <tr><td>Licenciamento, outorga e regularização do poço</td><td>Cumprimento de obrigação legal e exercício regular de direitos (art. 7º, II e VI)</td></tr>
                    <tr><td>Segurança do site, prevenção a fraudes e registros de acesso</td><td>Legítimo interesse (art. 7º, IX) e obrigação legal (Marco Civil)</td></tr>
                    <tr><td>Envio de comunicações e novidades (newsletter)</td><td>Consentimento (art. 7º, I), revogável a qualquer momento</td></tr>
                    <tr><td>Atendimento de solicitações de titular (LGPD)</td><td>Cumprimento de obrigação legal (art. 7º, II)</td></tr>
                </tbody>
            </table>
        </section>

        <section id="finalidades">
            <h2><span class="num">7</span> Para que usamos seus dados</h2>
            <ul>
                <li>Atender pedidos de orçamento, dúvidas e solicitações de visita técnica;</li>
                <li>Elaborar propostas, contratos e executar os serviços contratados;</li>
                <li>Prestar suporte, acompanhar garantias e realizar manutenções;</li>
                <li>Emitir notas fiscais e cumprir obrigações contábeis, fiscais e ambientais;</li>
                <li>Manter o site seguro, estável e disponível, prevenindo acessos indevidos;</li>
                <li>Enviar comunicações autorizadas (novidades, conteúdos e promoções);</li>
                <li>Exercer direitos em processos administrativos ou judiciais.</li>
            </ul>
            <div class="legal-callout legal-ok">
                <i class="bi bi-check-circle-fill"></i>
                <div><strong>Não vendemos, alugamos ou cedemos seus dados</strong> para terceiros com finalidade de marketing.</div>
            </div>
        </section>

        <section id="compartilhamento">
            <h2><span class="num">8</span> Com quem compartilhamos</h2>
            <p>Compartilhamos dados apenas quando necessário e com quem se compromete a protegê-los:</p>
            <ul>
                <li><strong>Operadores e prestadores de serviço:</strong> hospedagem, e-mail corporativo, contabilidade, assessoria jurídica e técnicos parceiros;</li>
                <li><strong>Autoridades públicas:</strong> DAEE, CETESB, prefeituras e demais órgãos, quando exigido por lei;</li>
                <li><strong>Parceiros de execução:</strong> fornecedores de equipamentos e serviços que atuam sob nossa orientação;</li>
                <li><strong>Em caso de obrigação legal:</strong> por ordem judicial ou requisição de autoridade competente.</li>
            </ul>
            <p>
                Todos os operadores atuam mediante contrato e seguem instruções da Hidrossolo, conforme o
                art. 39 da LGPD.
            </p>
        </section>

        <section id="internacional">
            <h2><span class="num">9</span> Transferência internacional</h2>
            <p>
                Não transferimos dados para fora do Brasil como parte da nossa operação. Caso algum fornecedor
                de infraestrutura armazene dados em servidores no exterior, exigiremos garantias contratuais de
                proteção equivalentes às da LGPD (arts. 33 a 36).
            </p>
        </section>

        <section id="retencao">
            <h2><span class="num">10</span> Por quanto tempo guardamos</h2>
            <table class="legal-table">
                <thead><tr><th>Dado</th><th>Prazo</th><th>Motivo</th></tr></thead>
                <tbody>
                    <tr><td>Mensagens de contato e orçamentos</td><td>Até 2 anos</td><td>Histórico de atendimento</td></tr>
                    <tr><td>Registros de acesso (IP, data/hora)</td><td>6 meses</td><td>Marco Civil da Internet, art. 15</td></tr>
                    <tr><td>Contratos e documentos técnicos</td><td>Até 20 anos</td><td>Garantia, defesa em processos e regularização do poço</td></tr>
                    <tr><td>Documentos fiscais e contábeis</td><td>5 anos ou prazo legal maior</td><td>Legislação fiscal e civil</td></tr>
                    <tr><td>Solicitações LGPD</td><td>5 anos</td><td>Comprovação de atendimento ao titular</td></tr>
                    <tr><td>Consentimento de newsletter</td><td>Até a revogação</td><td>Prova do consentimento</td></tr>
                </tbody>
            </table>
            <p>Encerrados os prazos, os dados são eliminados ou anonimizados de forma segura.</p>
        </section>

        <section id="seguranca">
            <h2><span class="num">11</span> Segurança da informação</h2>
            <p>Adotamos medidas técnicas e administrativas compatíveis com o risco, incluindo:</p>
            <ul>
                <li>Conexão criptografada (HTTPS/HSTS) e cabeçalhos de segurança no site;</li>
                <li>Senhas armazenadas apenas em <em>hash</em> forte (bcrypt), nunca em texto puro;</li>
                <li>Controle de acesso por perfil, com privilégio mínimo necessário;</li>
                <li>Proteção contra CSRF, injeção de código e força bruta (bloqueio por tentativas);</li>
                <li>Registros de auditoria das ações administrativas e dos acessos;</li>
                <li>Backups periódicos e validação de arquivos enviados ao sistema.</li>
            </ul>
            <p>
                Em caso de incidente de segurança que possa acarretar risco ou dano relevante, comunicaremos
                você e a ANPD, conforme os arts. 48 e 49 da LGPD.
            </p>
        </section>

        <section id="direitos">
            <h2><span class="num">12</span> Seus direitos</h2>
            <p>Você pode exercer, gratuitamente, os direitos do art. 18 da LGPD:</p>
            <div class="legal-grid">
                <div class="legal-card"><i class="bi bi-patch-question"></i><h3>Confirmação e acesso</h3><p>Saber se tratamos seus dados e obter uma cópia.</p></div>
                <div class="legal-card"><i class="bi bi-pencil-square"></i><h3>Correção</h3><p>Solicitar atualização de dados incompletos ou inexatos.</p></div>
                <div class="legal-card"><i class="bi bi-shield-slash"></i><h3>Anonimização e bloqueio</h3><p>De dados desnecessários, excessivos ou tratados sem base legal.</p></div>
                <div class="legal-card"><i class="bi bi-arrow-left-right"></i><h3>Portabilidade</h3><p>Receber seus dados em formato estruturado.</p></div>
                <div class="legal-card"><i class="bi bi-trash3"></i><h3>Eliminação</h3><p>Dos dados tratados com base no consentimento.</p></div>
                <div class="legal-card"><i class="bi bi-info-square"></i><h3>Informação</h3><p>Sobre compartilhamento e a possibilidade de não consentir.</p></div>
                <div class="legal-card"><i class="bi bi-arrow-counterclockwise"></i><h3>Revogação</h3><p>Retirar o consentimento a qualquer momento.</p></div>
                <div class="legal-card"><i class="bi bi-hand-stop"></i><h3>Oposição</h3><p>Opor-se a tratamentos sem base legal adequada.</p></div>
            </div>
            <p>
                Para exercer qualquer direito, use a <a href="/lgpd#solicitacao">Central LGPD</a> ou escreva para
                <strong><?= e(lgpd_email()) ?></strong>. Responderemos em até <strong>15 dias</strong>,
                podendo solicitar comprovação de identidade para proteger os seus próprios dados.
            </p>
        </section>

        <section id="cookies">
            <h2><span class="num">13</span> Cookies</h2>
            <p>
                Utilizamos cookies estritamente necessários e, com o seu consentimento, cookies de medição para
                entender como o site é utilizado. Você pode aceitar, recusar ou revogar o consentimento a
                qualquer momento — o banner do site registra a sua escolha.
            </p>
            <p>Detalhes completos estão na <a href="/politica-de-cookies">Política de Cookies</a>.</p>
        </section>

        <section id="criancas">
            <h2><span class="num">14</span> Crianças e adolescentes</h2>
            <p>
                Nossos serviços são destinados a maiores de 18 anos. Não coletamos intencionalmente dados de
                crianças ou adolescentes sem o consentimento específico de pelo menos um dos pais ou
                responsável legal (art. 14 da LGPD). Se identificarmos esse cenário, os dados serão eliminados.
            </p>
        </section>

        <section id="automatizadas">
            <h2><span class="num">15</span> Decisões automatizadas</h2>
            <p>
                <strong>Não tomamos decisões automatizadas</strong> que afetem seus interesses, nem realizamos
                perfilamento comportamental. Todo contato comercial e análise de orçamento é conduzido por
                pessoas da nossa equipe.
            </p>
        </section>

        <section id="alteracoes">
            <h2><span class="num">16</span> Alterações desta política</h2>
            <p>
                Esta política pode ser atualizada para refletir mudanças legais, técnicas ou operacionais.
                A versão vigente estará sempre nesta página, com data de atualização e número de versão.
                Alterações relevantes serão destacadas no site e, quando aplicável, comunicadas por e-mail.
            </p>
        </section>

        <section id="lei">
            <h2><span class="num">17</span> Legislação e foro</h2>
            <p>
                Esta política é regida pela legislação brasileira, em especial pela LGPD, pelo Marco Civil da
                Internet e pelo Código de Defesa do Consumidor. Fica eleito o foro da comarca de
                <strong>Marília/SP</strong> para dirimir controvérsias, sem prejuízo do direito do consumidor
                de demandar em seu próprio domicílio.
            </p>
        </section>

        <section id="contato">
            <h2><span class="num">18</span> Contato do encarregado</h2>
            <p>Para qualquer assunto relacionado a dados pessoais:</p>
            <table class="legal-table">
                <tbody>
                    <tr><th style="width:34%">Encarregado (DPO)</th><td><?= e(lgpd_email()) ?></td></tr>
                    <tr><th>Telefone</th><td><?= e($company['phone']) ?></td></tr>
                    <tr><th>Endereço</th><td><?= e(implode(' — ', company_address_lines($company))) ?></td></tr>
                    <tr><th>Solicitações com protocolo</th><td><a href="/lgpd#solicitacao">Central LGPD</a></td></tr>
                </tbody>
            </table>
            <div class="legal-callout">
                <i class="bi bi-info-circle-fill"></i>
                <div>
                    Este documento é um modelo adaptado à LGPD e deve ser revisado pelo jurídico da empresa
                    antes da publicação oficial, incluindo a confirmação do CNPJ e do encarregado designado.
                </div>
            </div>
        </section>
    </article>
</div>
<?php $view->endSection(); ?>
