<div class="ui segment">
    <h2 class="ui header">
        <i class="user icon"></i>
        <div class="content">
            Configuração do Administrador
            <div class="sub header">Configure a conta do administrador do sistema</div>
        </div>
    </h2>

    <div class="ui info message">
        <div class="header">Configuração Final</div>
        <p>Configure os dados do usuário administrador do sistema.</p>
    </div>

    <form class="ui form" id="adminForm">
        <div class="field">
            <label>Nome</label>
            <input type="text" name="admin_name" required>
        </div>
        
        <div class="field">
            <label>E-mail</label>
            <input type="email" name="admin_email" required>
        </div>
        
        <div class="field">
            <label>Senha</label>
            <input type="password" name="admin_pass" required>
        </div>
        
        <div class="field">
            <label>Confirmar Senha</label>
            <input type="password" name="admin_pass_confirm" required>
        </div>

        <div id="adminMessages"></div>

        <div class="ui buttons">
            <a href="?step=3" class="ui button">
                <i class="left arrow icon"></i> Anterior
            </a>
            <button type="submit" class="ui primary button">
                <i class="save icon"></i> Finalizar Instalação
            </button>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#adminForm').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $button = $form.find('button[type="submit"]');
        
        // Validação das senhas
        const pass = $form.find('[name="admin_pass"]').val();
        const passConfirm = $form.find('[name="admin_pass_confirm"]').val();
        
        if (pass !== passConfirm) {
            showMessage('As senhas não conferem!', 'error');
            return;
        }
        
        $button.addClass('loading disabled');
        
        $.ajax({
            url: 'save_admin.php',
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                // Mostra modal de sucesso e redireciona
                const $modal = $(`
                    <div class="ui small modal">
                        <div class="header">
                            <i class="check circle icon green"></i>
                            Instalação Concluída
                        </div>
                        <div class="content">
                            <div class="ui success message">
                                <div class="header">Administrador configurado com sucesso!</div>
                                <p>O sistema está pronto para uso.</p>
                            </div>
                            <p>Você será redirecionado para a página inicial em alguns segundos...</p>
                        </div>
                    </div>
                `).appendTo('body');
                
                $modal.modal({
                    closable: false,
                    onHidden: function() {
                        window.location.href = '../index.php';
                    }
                }).modal('show');
                
                // Redireciona após 3 segundos
                setTimeout(function() {
                    $modal.modal('hide');
                }, 3000);
            } else {
                showMessage(response.message, 'error');
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            showMessage('Erro ao salvar administrador: ' + errorThrown, 'error');
        })
        .always(function() {
            $button.removeClass('loading disabled');
        });
    });
    
    function showMessage(message, type = 'info') {
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
        
        $('#adminMessages').html(`
            <div class="ui ${classes[type]} message">
                <i class="close icon"></i>
                <div class="header">${tipos[type].toUpperCase()}</div>
                <p>${message}</p>
            </div>
        `);
    }
    
    // Permite fechar mensagens
    $(document).on('click', '.message .close', function() {
        $(this).closest('.message').transition('fade');
    });
});
</script> 