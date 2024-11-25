<?php
header('Content-Type: application/json');
session_start();

try {
    $config = $_SESSION['db_config'];
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']}";
    $conn = new PDO($dsn, $config['user'], $config['pass']);
    
    // Cria permissões básicas
    $permissions = [
        ['admin', 'Administrador'],
        ['manager', 'Gerente'],
        ['user', 'Usuário']
    ];
    
    foreach ($permissions as $perm) {
        $stmt = $conn->prepare("INSERT INTO {$config['prefix']}roles (role_name, role_description) VALUES (?, ?)");
        $stmt->execute($perm);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Permissões configuradas!'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao configurar permissões: ' . $e->getMessage()
    ]);
} 