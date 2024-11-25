<?php
header('Content-Type: application/json');
session_start();

try {
    $config = $_SESSION['db_config'];
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']}";
    $conn = new PDO($dsn, $config['user'], $config['pass']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Primeiro limpa a tabela de roles
    $conn->exec("TRUNCATE TABLE {$config['prefix']}roles");
    
    // Cria permissões básicas
    $permissions = [
        ['admin', 'Administrador do Sistema'],
        ['editor', 'Editor de Conteúdo'],
        ['author', 'Autor de Posts'],
        ['user', 'Usuário Comum']
    ];
    
    // Prepara a inserção
    $stmt = $conn->prepare("INSERT INTO {$config['prefix']}roles (role_name, role_description) VALUES (?, ?)");
    
    // Insere cada permissão
    foreach ($permissions as $perm) {
        try {
            $stmt->execute($perm);
        } catch (PDOException $e) {
            // Se já existe, ignora e continua
            if ($e->getCode() != '23000') {
                throw $e;
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Permissões configuradas com sucesso!'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao configurar permissões: ' . $e->getMessage()
    ]);
} 