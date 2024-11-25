<div class="ui segment">
    <h2 class="ui header">Entre em Contato</h2>
    <form class="ui form" method="POST" action="processa_contato.php">
        <div class="field">
            <label>Nome</label>
            <input type="text" name="nome" required>
        </div>
        <div class="field">
            <label>E-mail</label>
            <input type="email" name="email" required>
        </div>
        <div class="field">
            <label>Assunto</label>
            <input type="text" name="assunto" required>
        </div>
        <div class="field">
            <label>Mensagem</label>
            <textarea name="mensagem" required></textarea>
        </div>
        <button class="ui primary button" type="submit">Enviar Mensagem</button>
    </form>
</div> 