<?php
session_start();

// Determina a etapa atual
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Verifica se a etapa é válida (1 a 4)
if ($step < 1 || $step > 4) {
    header('Location: install.php?step=1');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Hidrossolo</title>
    
    <!-- jQuery DEVE vir ANTES do Semantic UI -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js"></script>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
    <style>
        .container { padding: 2em; }
        .step-content { display: none; }
        .step-content.active { display: block; }
        #installMessages { margin-top: 2em; }
        .ui.progress { margin: 2em 0; }
        .ui.list .item { padding: 1em 0; }
        .ui.progress {
            margin: 2em 0;
        }
        
        .ui.list .item {
            padding: 1em 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .ui.list .item:last-child {
            border-bottom: none;
        }
        
        .ui.list .item .icon {
            font-size: 1.5em;
            width: 1.5em;
            height: 1.5em;
            margin-right: 1em;
        }
        
        .ui.list .item .header {
            font-weight: bold;
            margin-bottom: 0.5em;
        }
        
        .ui.list .item .description {
            color: rgba(0,0,0,0.6);
        }
        
        #installMessages {
            margin: 2em 0;
        }
        
        .ui.buttons {
            margin-top: 2em;
        }
        
        .ui.button.disabled {
            opacity: 0.45 !important;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="ui container">
        <!-- Passos da instalação -->
        <div class="ui ordered steps">
            <a href="install.php?step=1" class="<?= $step > 1 ? 'completed' : ($step == 1 ? 'active' : '') ?> step">
                <div class="content">
                    <div class="title">Requisitos</div>
                    <div class="description">Verificar Sistema</div>
                </div>
            </a>
            <a href="install.php?step=2" class="<?= $step > 2 ? 'completed' : ($step == 2 ? 'active' : '') ?> step">
                <div class="content">
                    <div class="title">Conexão</div>
                    <div class="description">Banco de Dados</div>
                </div>
            </a>
            <a href="#" class="<?= $step > 3 ? 'completed' : ($step == 3 ? 'active' : '') ?> step <?= $step < 3 ? 'disabled' : '' ?>">
                <div class="content">
                    <div class="title">Instalação</div>
                    <div class="description">Criar Tabelas</div>
                </div>
            </a>
            <div class="<?= $step == 4 ? 'active' : '' ?> step">
                <div class="content">
                    <div class="title">Finalização</div>
                    <div class="description">Configurar Admin</div>
                </div>
            </div>
        </div>

        <!-- Conteúdo das etapas -->
        <div class="ui segment">
            <?php
            $stepFile = "steps/step{$step}.php";
            if (file_exists($stepFile)) {
                ob_start();
                include $stepFile;
                $content = ob_get_clean();
                echo $content;
            } else {
                echo "<div class='ui error message'>Etapa não encontrada.</div>";
            }
            ?>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js"></script>
    <script>
    $(document).ready(function() {
        // Handler do botão próximo
        $('.next-step').on('click', function() {
            const currentStep = <?= $step ?>;
            window.location.href = `install.php?step=${currentStep + 1}`;
        });

        // Handler do botão anterior
        $('.prev-step, .step.completed').on('click', function(e) {
            const targetStep = $(this).data('step') || <?= $step - 1 ?>;
            if (targetStep >= 1) {
                window.location.href = `install.php?step=${targetStep}`;
            }
            e.preventDefault();
        });

        // Desabilita cliques em steps futuros
        $('.step:not(.completed)').on('click', function(e) {
            if (!$(this).hasClass('active')) {
                e.preventDefault();
            }
        });
    });
    </script>
</body>
</html> 