<?php
session_start();

// Verificar se o usuário está logado e tem nível de acesso 1
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] != 1) {
    header('Location: area-restrita.php');
    exit;
}
?>

<?php include '../includes/header.php'; ?>

    <!-- Conteúdo principal -->
    <div class="container my-5">
        <div class="row">
            <div class="col-md-12 text-center">
                <h1>Área Restrita - Acesso Completo sem Logs</h1>
                <p>Bem-vindo à sua área restrita. Aqui você pode acessar todas as funcionalidades, exceto logs.</p>
                <!-- Coloque aqui as funcionalidades específicas para usuários com acesso completo sem logs -->
            </div>
        </div>
    </div>

<?php include '../includes/footer.php'; ?><?php
