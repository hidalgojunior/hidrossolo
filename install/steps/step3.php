<div class="ui segment">
    <h2 class="ui header">
        <i class="cogs icon"></i>
        <div class="content">
            Instalação do Sistema
            <div class="sub header">Criando tabelas e configurações iniciais</div>
        </div>
    </h2>

    <div class="ui info message">
        <div class="header">Processo de Instalação</div>
        <p>O sistema irá criar todas as tabelas necessárias e configurações iniciais. Este processo pode levar alguns minutos.</p>
    </div>

    <div class="ui indicating progress" id="installProgress">
        <div class="bar">
            <div class="progress">0%</div>
        </div>
        <div class="label">Instalando: 0 de 3 tarefas completadas</div>
    </div>

    <div class="ui list" id="taskList">
        <div class="item" data-task="tables">
            <i class="circle outline icon"></i>
            <div class="content">
                <div class="header">Criando Tabelas</div>
                <div class="description">Estrutura do banco de dados</div>
            </div>
        </div>
        <div class="item" data-task="configs">
            <i class="circle outline icon"></i>
            <div class="content">
                <div class="header">Configurações Iniciais</div>
                <div class="description">Definindo parâmetros do sistema</div>
            </div>
        </div>
        <div class="item" data-task="permissions">
            <i class="circle outline icon"></i>
            <div class="content">
                <div class="header">Configurando Permissões</div>
                <div class="description">Definindo níveis de acesso</div>
            </div>
        </div>
    </div>

    <div id="installMessages"></div>

    <div class="ui buttons">
        <a href="?step=2" class="ui button">
            <i class="left arrow icon"></i> Anterior
        </a>
        <button type="button" class="ui primary button" id="startInstallation">
            <i class="play icon"></i> Iniciar Instalação
        </button>
        <button type="button" class="ui positive button next-step" disabled>
            Próximo <i class="right arrow icon"></i>
        </button>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const $progress = $('#installProgress');
    const $taskList = $('#taskList');
    const $startButton = $('#startInstallation');
    const $nextButton = $('.next-step');
    const tasks = ['tables', 'configs', 'permissions'];
    let currentTask = 0;

    // Inicializa a barra de progresso
    $progress.progress({
        total: tasks.length,
        text: {
            active: 'Instalando: {value} de {total} tarefas completadas',
            success: 'Instalação concluída com sucesso!'
        }
    });

    // Handler do botão iniciar
    $startButton.on('click', function() {
        $startButton.addClass('disabled loading');
        startInstallation();
    });

    function startInstallation() {
        // Reseta o progresso
        currentTask = 0;
        $progress.progress('reset');
        
        // Inicia a primeira tarefa
        executeNextTask();
    }

    function executeNextTask() {
        if (currentTask >= tasks.length) {
            installationComplete();
            return;
        }

        const task = tasks[currentTask];
        const $taskItem = $taskList.find(`[data-task="${task}"]`);
        
        // Atualiza ícone para loading
        $taskItem.find('.icon')
            .removeClass('circle outline check')
            .addClass('spinner loading');

        console.log('Iniciando tarefa:', task);

        // Executa a tarefa
        $.ajax({
            url: `install_${task}.php`,
            method: 'POST',
            dataType: 'json',
            cache: false
        })
        .done(function(response) {
            console.log('Resposta recebida:', response);
            
            if (response && response.success) {
                // Marca tarefa como concluída
                $taskItem.find('.icon')
                    .removeClass('spinner loading')
                    .addClass('check green');
                
                // Atualiza progresso
                $progress.progress('increment');
                
                // Próxima tarefa
                currentTask++;
                executeNextTask();
            } else {
                const errorMsg = response ? response.message : 'Erro desconhecido na instalação';
                handleError(errorMsg);
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            console.log('Detalhes do erro:', {
                status: jqXHR.status,
                statusText: jqXHR.statusText,
                responseText: jqXHR.responseText,
                textStatus: textStatus,
                errorThrown: errorThrown
            });

            let errorMessage = 'Erro na instalação';
            
            if (jqXHR.responseText) {
                try {
                    const response = JSON.parse(jqXHR.responseText);
                    errorMessage = response.message || errorMessage;
                } catch (e) {
                    errorMessage = jqXHR.responseText;
                }
            }

            handleError(errorMessage);
        });
    }

    function installationComplete() {
        $startButton.removeClass('loading');
        $nextButton.removeClass('disabled').prop('disabled', false);
        
        // Mostra modal de conclusão
        const $modal = $(`
            <div class="ui small modal">
                <div class="header">
                    <i class="check circle icon green"></i>
                    Instalação Concluída
                </div>
                <div class="content">
                    <p>A instalação do sistema foi concluída com sucesso!</p>
                    <p>Clique em "Próximo" para configurar o usuário administrador.</p>
                </div>
                <div class="actions">
                    <div class="ui positive button">OK</div>
                </div>
            </div>
        `).appendTo('body');
        
        $modal.modal({
            closable: false,
            onApprove: function() {
                window.location.href = 'install.php?step=4';
            }
        }).modal('show');
    }

    function handleError(message) {
        console.log('Tratando erro:', message);
        
        $startButton.removeClass('loading disabled');
        
        const $modal = $(`
            <div class="ui small modal">
                <div class="header">
                    <i class="times circle icon red"></i>
                    Erro na Instalação
                </div>
                <div class="content">
                    <div class="ui negative message">
                        <div class="header">Ocorreu um erro</div>
                        <p>${message}</p>
                    </div>
                    <p>Por favor, verifique o arquivo install_error.log para mais detalhes.</p>
                </div>
                <div class="actions">
                    <div class="ui positive button">OK</div>
                </div>
            </div>
        `).appendTo('body');
        
        $modal.modal({
            closable: false
        }).modal('show');
        
        // Marca tarefa atual como erro
        const $taskItem = $taskList.find(`[data-task="${tasks[currentTask]}"]`);
        $taskItem.find('.icon')
            .removeClass('spinner loading')
            .addClass('times red');
    }

    function showMessage(message, type = 'info') {
        // Traduz tipos de mensagem
        const tipos = {
            'error': 'erro',
            'success': 'sucesso',
            'info': 'informação',
            'warning': 'aviso'
        };
        
        const classes = {
            'error': 'negative',
            'success': 'positive',
            'info': 'info',
            'warning': 'warning'
        };
        
        $('#installMessages').html(`
            <div class="ui ${classes[type]} message">
                <i class="close icon"></i>
                <div class="header">${tipos[type].toUpperCase()}</div>
                <div class="content">
                    <p>${message}</p>
                </div>
            </div>
        `);
    }

    // Permite fechar mensagens
    $(document).on('click', '.message .close', function() {
        $(this).closest('.message').transition('fade');
    });
});
</script> 