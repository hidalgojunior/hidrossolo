<?php
// Remove o session_start daqui pois já está no install.php
?>
<div class="ui segment">
    <h2 class="ui header">
        <i class="database icon"></i>
        <div class="content">
            Conexão com o Banco de Dados
            <div class="sub header">Configure a conexão com o banco de dados MySQL</div>
        </div>
    </h2>

    <div class="ui info message">
        <div class="header">Configuração do Banco de Dados</div>
        <p>Preencha as informações de conexão com seu banco de dados MySQL.</p>
    </div>

    <div id="dbMessages"></div>

    <form class="ui form" id="dbForm">
        <div class="field">
            <label>Host</label>
            <input type="text" name="db_host" value="localhost" required>
        </div>
        
        <div class="field">
            <label>Porta</label>
            <input type="text" name="db_port" value="3306" required>
        </div>
        
        <div class="field">
            <label>Nome do Banco</label>
            <input type="text" name="db_name" value="hidrossolo" required>
        </div>
        
        <div class="field">
            <label>Usuário</label>
            <input type="text" name="db_user" value="root" required>
        </div>
        
        <div class="field">
            <label>Senha</label>
            <input type="password" name="db_pass">
        </div>
        
        <div class="field">
            <label>Prefixo das Tabelas</label>
            <input type="text" name="db_prefix" value="hds_">
        </div>

        <div class="ui buttons">
            <a href="?step=1" class="ui button">
                <i class="left arrow icon"></i> Anterior
            </a>
            <button type="button" class="ui primary button" id="testConnection">
                <i class="plug icon"></i> Testar Conexão
            </button>
            <button type="button" class="ui positive button next-step" disabled>
                Próximo <i class="right arrow icon"></i>
            </button>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    $('#testConnection').on('click', function() {
        const $button = $(this);
        const $form = $('#dbForm');
        const $nextBtn = $('.next-step');
        
        $button.addClass('loading disabled');
        $('#dbMessages').empty();
        
        testConnection();
        
        function testConnection() {
            $.ajax({
                url: 'test_connection.php',
                method: 'POST',
                data: $form.serialize(),
                dataType: 'json'
            })
            .done(function(response) {
                if (response.success) {
                    showMessage(response.message, 'success');
                    $nextBtn.removeClass('disabled').prop('disabled', false);
                } else {
                    if (response.type === 'database_not_found') {
                        showConfirmDialog(response.message, function() {
                            createDatabase();
                        });
                    } else {
                        showMessage(response.message, 'error');
                        $nextBtn.addClass('disabled').prop('disabled', true);
                    }
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                showMessage('Erro ao testar conexão: ' + errorThrown, 'error');
                $nextBtn.addClass('disabled').prop('disabled', true);
            })
            .always(function() {
                $button.removeClass('loading disabled');
            });
        }
        
        function createDatabase() {
            $button.addClass('loading disabled');
            
            $.ajax({
                url: 'create_database.php',
                method: 'POST',
                data: $form.serialize(),
                dataType: 'json'
            })
            .done(function(response) {
                if (response.success) {
                    showMessage(response.message, 'success');
                    // Testa a conexão novamente
                    testConnection();
                } else {
                    showMessage(response.message, 'error');
                    $nextBtn.addClass('disabled').prop('disabled', true);
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                showMessage('Erro ao criar banco de dados: ' + errorThrown, 'error');
                $nextBtn.addClass('disabled').prop('disabled', true);
            })
            .always(function() {
                $button.removeClass('loading disabled');
            });
        }
        
        function showConfirmDialog(message, callback) {
            const $modal = $(`
                <div class="ui small modal">
                    <div class="header">Criar Banco de Dados</div>
                    <div class="content">
                        <p>${message}</p>
                    </div>
                    <div class="actions">
                        <div class="ui cancel button">Cancelar</div>
                        <div class="ui positive button">Criar</div>
                    </div>
                </div>
            `).appendTo('body');
            
            $modal.modal({
                closable: false,
                onApprove: function() {
                    callback();
                    return true;
                },
                onHidden: function() {
                    $modal.remove();
                }
            }).modal('show');
        }
        
        function showMessage(message, type) {
            $('#dbMessages').html(`
                <div class="ui ${type} message">
                    <i class="close icon"></i>
                    <div class="content">
                        <p>${message}</p>
                    </div>
                </div>
            `);
        }
    });
});
</script> 