<?php $view->layout('layouts.main'); ?>

<?php
$secoes = [
    ['id' => 'compromisso', 't' => 'Nosso compromisso'],
    ['id' => 'principios', 't' => 'Princípios e governança'],
    ['id' => 'direitos', 't' => 'Seus direitos'],
    ['id' => 'solicitacao', 't' => 'Fazer uma solicitação'],
    ['id' => 'prazos', 't' => 'Prazos e como respondemos'],
    ['id' => 'seguranca', 't' => 'Segurança e incidentes'],
    ['id' => 'canais', 't' => 'Canais de contato'],
];
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
?>

<?php $view->section('styles'); ?>
<link rel="stylesheet" href="<?= e(asset_v('assets/css/legal.css')) ?>">
<?php $view->endSection(); ?>

<?php $view->section('content'); ?>
<?= $view->partial('pages.legal._hero', [
    'title' => 'Central LGPD',
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
                <a href="#solicitacao" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-send"></i> Abrir solicitação
                </a>
            </div>
        </div>
    </aside>

    <article class="legal-content">
        <section id="compromisso">
            <h2><span class="num">1</span> Nosso compromisso</h2>
            <p>
                Esta é a central de privacidade da <strong>Hidrossolo Poços Artesianos</strong>. Aqui você entende,
                em linguagem simples, como protegemos seus dados e exerce os direitos garantidos pela
                <strong>LGPD (Lei nº 13.709/2018)</strong>.
            </p>
            <div class="legal-callout legal-ok">
                <i class="bi bi-shield-check"></i>
                <div>
                    <strong>Encarregado de Dados (DPO):</strong> <?= e(lgpd_email()) ?> —
                    canal oficial para qualquer assunto de privacidade.
                </div>
            </div>
            <p>
                Para o detalhamento completo do tratamento, consulte a
                <a href="/politica-de-privacidade">Política de Privacidade</a> e a
                <a href="/politica-de-cookies">Política de Cookies</a>.
            </p>
        </section>

        <section id="principios">
            <h2><span class="num">2</span> Princípios e governança</h2>
            <p>Seguimos os princípios do art. 6º da LGPD em todas as operações:</p>
            <div class="legal-grid">
                <div class="legal-card"><i class="bi bi-bullseye"></i><h3>Finalidade</h3><p>Tratamento para propósitos legítimos, específicos e informados.</p></div>
                <div class="legal-card"><i class="bi bi-arrows-collapse"></i><h3>Necessidade</h3><p>Somente o mínimo de dados necessário para cada finalidade.</p></div>
                <div class="legal-card"><i class="bi bi-eye"></i><h3>Transparência</h3><p>Informações claras sobre o que fazemos com seus dados.</p></div>
                <div class="legal-card"><i class="bi bi-lock"></i><h3>Segurança</h3><p>Medidas técnicas e administrativas proporcionais ao risco.</p></div>
                <div class="legal-card"><i class="bi bi-person-check"></i><h3>Livre acesso</h3><p>Consulta facilitada sobre o tratamento e seus direitos.</p></div>
                <div class="legal-card"><i class="bi bi-arrow-repeat"></i><h3>Qualidade</h3><p>Dados exatos, claros e atualizados.</p></div>
            </div>

            <h3>Práticas de governança adotadas</h3>
            <ul>
                <li><strong>Registro das operações (ROPA):</strong> mapa das finalidades, bases legais e prazos de retenção;</li>
                <li><strong>Controle de acesso por perfil:</strong> cada função acessa apenas o necessário;</li>
                <li><strong>Trilha de auditoria:</strong> registro de acessos e ações administrativas relevantes;</li>
                <li><strong>Atendimento ao titular:</strong> solicitações com protocolo e prazo controlado;</li>
                <li><strong>Revisão periódica:</strong> políticas e medidas de segurança atualizadas.</li>
            </ul>
        </section>

        <section id="direitos">
            <h2><span class="num">3</span> Seus direitos</h2>
            <p>Você pode solicitar, gratuitamente, a qualquer momento:</p>
            <table class="legal-table">
                <thead><tr><th>Direito</th><th>O que significa na prática</th></tr></thead>
                <tbody>
                    <tr><td><strong>Confirmação</strong></td><td>Saber se tratamos algum dado seu.</td></tr>
                    <tr><td><strong>Acesso</strong></td><td>Receber uma cópia dos dados que mantemos sobre você.</td></tr>
                    <tr><td><strong>Correção</strong></td><td>Corrigir dados incompletos, inexatos ou desatualizados.</td></tr>
                    <tr><td><strong>Anonimização / bloqueio / eliminação</strong></td><td>De dados desnecessários, excessivos ou tratados em desconformidade.</td></tr>
                    <tr><td><strong>Portabilidade</strong></td><td>Receber seus dados em formato estruturado para levar a outro fornecedor.</td></tr>
                    <tr><td><strong>Eliminação</strong></td><td>Excluir dados tratados com base no consentimento.</td></tr>
                    <tr><td><strong>Informação sobre compartilhamento</strong></td><td>Saber com quem compartilhamos seus dados.</td></tr>
                    <tr><td><strong>Revogação do consentimento</strong></td><td>Retirar a autorização a qualquer momento.</td></tr>
                    <tr><td><strong>Oposição</strong></td><td>Opor-se a tratamentos sem base legal adequada.</td></tr>
                </tbody>
            </table>
            <div class="legal-callout">
                <i class="bi bi-info-circle-fill"></i>
                <div>
                    Alguns pedidos podem ser parcialmente atendidos: a lei autoriza a manutenção de dados quando
                    houver obrigação legal, fiscal ou necessidade de exercício regular de direitos
                    (ex.: guarda de documentos fiscais e técnicos).
                </div>
            </div>
        </section>

        <section id="solicitacao">
            <h2><span class="num">4</span> Fazer uma solicitação</h2>
            <p>
                Preencha o formulário abaixo. Você receberá um <strong>número de protocolo</strong> para
                acompanhar o atendimento.
            </p>

            <form method="POST" action="/lgpd/solicitacao" class="legal-form">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Nome completo *</label>
                        <input type="text" name="name" id="name" class="form-control" required maxlength="255"
                               value="<?= e($old['name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">E-mail para contato *</label>
                        <input type="email" name="email" id="email" class="form-control" required maxlength="255"
                               value="<?= e($old['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="document">CPF/CNPJ</label>
                        <input type="text" name="document" id="document" class="form-control" maxlength="30"
                               placeholder="Opcional" value="<?= e($old['document'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="phone">Telefone</label>
                        <input type="tel" name="phone" id="phone" class="form-control" maxlength="30"
                               placeholder="Opcional" value="<?= e($old['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="request_type">Tipo de solicitação *</label>
                        <select name="request_type" id="request_type" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach (($tipos ?? []) as $valor => $rotulo) { ?>
                                <option value="<?= e($valor) ?>" <?= ($old['request_type'] ?? '') === $valor ? 'selected' : '' ?>>
                                    <?= e($rotulo) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="message">Descreva sua solicitação *</label>
                        <textarea name="message" id="message" class="form-control" rows="5" required
                                  placeholder="Conte o que você precisa. Se for sobre um contrato ou orçamento, informe o número ou a data."><?= e($old['message'] ?? '') ?></textarea>
                    </div>

                    <!-- honeypot anti-robô -->
                    <div class="d-none" aria-hidden="true">
                        <label for="website">Não preencha</label>
                        <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="consent" id="consent" value="1"
                                   <?= isset($old['consent']) ? 'checked' : '' ?> required>
                            <label class="form-check-label" for="consent">
                                Declaro que as informações são verdadeiras e autorizo o contato da Hidrossolo
                                para responder a esta solicitação, conforme a
                                <a href="/politica-de-privacidade" target="_blank" rel="noopener">Política de Privacidade</a>. *
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-send"></i> Enviar solicitação
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <section id="prazos">
            <h2><span class="num">5</span> Prazos e como respondemos</h2>
            <ol>
                <li><strong>Registro imediato:</strong> você recebe um protocolo no ato do envio;</li>
                <li><strong>Confirmação de recebimento:</strong> em até 2 dias úteis;</li>
                <li><strong>Resposta conclusiva:</strong> em até <strong>15 dias</strong> corridos a contar do pedido, conforme a LGPD;</li>
                <li><strong>Comprovação de identidade:</strong> podemos solicitar documentos para evitar que terceiros acessem seus dados;</li>
                <li><strong>Complexidade:</strong> se o pedido exigir análise técnica ou jurídica, informaremos o novo prazo com justificativa.</li>
            </ol>
            <div class="legal-callout legal-warn">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    <strong>Não cobramos nenhuma taxa</strong> para atender solicitações de titular. Se alguém
                    pedir pagamento em nome da Hidrossolo para "liberar seus dados", é golpe — denuncie pelos
                    nossos canais oficiais.
                </div>
            </div>
        </section>

        <section id="seguranca">
            <h2><span class="num">6</span> Segurança e incidentes</h2>
            <p>Medidas em vigor no ambiente digital da Hidrossolo:</p>
            <ul>
                <li>Criptografia de transporte (HTTPS/TLS) e cabeçalhos de segurança;</li>
                <li>Senhas protegidas com <em>hash</em> bcrypt e política de senha forte;</li>
                <li>Bloqueio automático após tentativas de login malsucedidas;</li>
                <li>Proteção contra CSRF, XSS e injeção de comandos;</li>
                <li>Validação de arquivos enviados e bloqueio de execução na pasta pública;</li>
                <li>Registros de auditoria e monitoramento de acessos administrativos;</li>
                <li>Backups periódicos com restauração testada.</li>
            </ul>
            <p>
                Se ocorrer um incidente com risco relevante, comunicaremos os titulares afetados e a
                <strong>ANPD</strong>, conforme os arts. 48 e 49 da LGPD.
            </p>
        </section>

        <section id="canais">
            <h2><span class="num">7</span> Canais de contato</h2>
            <table class="legal-table">
                <tbody>
                    <tr><th style="width:34%">Encarregado (DPO)</th><td><?= e(lgpd_email()) ?></td></tr>
                    <tr><th>Telefone</th><td><?= e($company['phone']) ?></td></tr>
                    <tr><th>WhatsApp</th><td><?= e($company['whatsapp']) ?></td></tr>
                    <tr><th>Endereço</th><td><?= e(implode(' — ', company_address_lines($company))) ?></td></tr>
                    <tr><th>Autoridade Nacional (ANPD)</th><td><a href="https://www.gov.br/anpd" target="_blank" rel="noopener noreferrer">www.gov.br/anpd</a></td></tr>
                </tbody>
            </table>
            <div class="legal-callout">
                <i class="bi bi-info-circle-fill"></i>
                <div>
                    Este conteúdo é um modelo de conformidade e deve ser validado pelo jurídico da empresa,
                    incluindo a designação formal do encarregado (DPO) e o preenchimento do CNPJ.
                </div>
            </div>
        </section>
    </article>
</div>
<?php $view->endSection(); ?>
