<div class="ui segment">
    <h2 class="ui header">
        <i class="server icon"></i>
        <div class="content">
            Requisitos do Sistema
            <div class="sub header">Verificando compatibilidade do servidor</div>
        </div>
    </h2>

    <div class="ui info message">
        <div class="header">Verificação de Requisitos</div>
        <p>O sistema está verificando se seu servidor atende aos requisitos mínimos.</p>
    </div>

    <div class="ui relaxed list">
        <?php
        $requirements = [
            'PHP 7.4+' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'PDO MySQL' => extension_loaded('pdo_mysql'),
            'Permissão de Escrita' => is_writable('../'),
            'JSON' => extension_loaded('json'),
            'cURL' => extension_loaded('curl')
        ];

        foreach ($requirements as $name => $met): ?>
            <div class="item">
                <i class="large <?= $met ? 'green check' : 'red times' ?> icon"></i>
                <div class="content">
                    <div class="header"><?= $name ?></div>
                    <div class="description"><?= $met ? 'Requisito atendido' : 'Requisito não atendido' ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php
    // Se todos os requisitos foram atendidos
    if (!in_array(false, $requirements)) {
        $_SESSION['step1_completed'] = true;
    }
    ?>

    <div class="ui buttons" style="margin-top: 2em;">
        <button type="button" class="ui primary button next-step" <?= !in_array(false, $requirements) ? '' : 'disabled' ?>>
            Próximo <i class="right arrow icon"></i>
        </button>
    </div>
</div>
