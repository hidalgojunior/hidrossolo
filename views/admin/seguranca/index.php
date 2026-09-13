<?php $view->layout('layouts.admin'); ?>

<?php $view->section('content'); ?>
<div class="mb-3">
    <h4 class="mb-0">Governança &amp; Segurança</h4>
    <p class="text-muted small mb-0">Monitoramento das proteções contra invasão, força bruta e phishing.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-4">
        <div class="stat-card h-100">
            <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-shield-exclamation"></i></div>
            <div>
                <div class="stat-value"><?= e($falhas24h) ?></div>
                <div class="stat-label">Falhas de login (24h)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="stat-card h-100">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-lock"></i></div>
            <div>
                <div class="stat-value"><?= e($bloqueios24h) ?></div>
                <div class="stat-label">Bloqueios aplicados (24h)</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="stat-card h-100">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-person-check"></i></div>
            <div>
                <div class="stat-value"><?= e($sucessos24h) ?></div>
                <div class="stat-label">Logins bem-sucedidos (24h)</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-white"><strong>Verificações de proteção</strong></div>
            <div class="list-group list-group-flush">
                <?php foreach ($checks as $check) { ?>
                    <div class="list-group-item d-flex gap-3 align-items-start">
                        <?php if ($check['ok']) { ?>
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <?php } else { ?>
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                        <?php } ?>
                        <div>
                            <strong><?= e($check['titulo']) ?></strong>
                            <div class="text-muted small"><?= e($check['detalhe']) ?></div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header bg-white"><strong>Usuários</strong></div>
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between"><span>Total de contas</span><strong><?= e($usuarios['total']) ?></strong></div>
                <div class="list-group-item d-flex justify-content-between"><span>Contas ativas</span><strong><?= e($usuarios['ativos']) ?></strong></div>
                <div class="list-group-item d-flex justify-content-between"><span>Sem troca de senha há 180 dias</span><strong><?= e($usuarios['sem_senha_recente']) ?></strong></div>
                <div class="list-group-item d-flex justify-content-between"><span>Nunca acessaram</span><strong><?= e($usuarios['nunca_logaram']) ?></strong></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white"><strong>IPs com mais falhas (24h)</strong></div>
            <div class="card-body p-0">
                <?php if (empty($ipsSuspeitos)) { ?>
                    <p class="text-muted text-center py-4 mb-0">Nenhum IP suspeito. 🎉</p>
                <?php } else { ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light"><tr><th>IP</th><th class="text-end">Falhas</th><th>Última</th></tr></thead>
                            <tbody>
                                <?php foreach ($ipsSuspeitos as $ip) { ?>
                                    <tr>
                                        <td class="font-monospace"><?= e($ip['ip_address']) ?></td>
                                        <td class="text-end"><?= e($ip['tentativas']) ?></td>
                                        <td><?= e(date('d/m H:i', strtotime($ip['ultima']))) ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php if ($usuariosSemSenhaRecente !== []) { ?>
<div class="card mb-4">
    <div class="card-header bg-white"><strong>Contas que precisam trocar a senha</strong></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Usuário</th><th>E-mail</th><th>Último login</th><th>Última troca</th></tr></thead>
            <tbody>
                <?php foreach ($usuariosSemSenhaRecente as $u) { ?>
                    <tr>
                        <td><?= e($u['name']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td><?= e($u['last_login'] ? date('d/m/Y', strtotime($u['last_login'])) : 'nunca') ?></td>
                        <td><?= e($u['password_changed_at'] ? date('d/m/Y', strtotime($u['password_changed_at'])) : 'desconhecida') ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php } ?>

<div class="card">
    <div class="card-header bg-white"><strong>Últimos eventos de autenticação</strong></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr><th>Quando</th><th>E-mail</th><th>IP</th><th>Resultado</th><th>Motivo</th></tr>
            </thead>
            <tbody>
                <?php foreach ($ultimosEventos as $e) { ?>
                    <tr>
                        <td><?= e(date('d/m H:i', strtotime($e['created_at']))) ?></td>
                        <td><?= e($e['email'] ?? '—') ?></td>
                        <td class="font-monospace"><?= e($e['ip_address']) ?></td>
                        <td>
                            <?php if ((int) $e['successful'] === 1) { ?>
                                <span class="badge bg-success">Sucesso</span>
                            <?php } else { ?>
                                <span class="badge bg-danger">Falha</span>
                            <?php } ?>
                        </td>
                        <td class="text-muted small"><?= e($e['reason'] ?? '—') ?></td>
                    </tr>
                <?php } ?>
                <?php if (empty($ultimosEventos)) { ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Sem eventos registrados.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php $view->endSection(); ?>
