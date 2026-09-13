<?php
session_start();

// Verificar se o usuário está logado e tem nível de acesso 0
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] != 0) {
    header('Location: ../admin/area-restrita/dashboard.php');
    exit;
}
?>

<?php include '../includes/header.php'; ?>

    <!-- Conteúdo principal -->
    <div class="container my-5">
        <div class="row">
            <div class="col-md-12 text-center">
                <h1>Área Restrita - Acesso Completo com Logs</h1>
                <p>Bem-vindo à sua área restrita. Aqui você pode acessar todas as funcionalidades e logs do sistema.</p>
                <!-- Coloque aqui as funcionalidades específicas para usuários com acesso completo -->
            </div>
        </div>
    </div>

<?php include '../includes/footer.php'; ?><?php
