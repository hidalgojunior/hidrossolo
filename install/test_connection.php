<?php
header('Content-Type: application/json');
session_start();

try {
    $host = $_POST['db_host'] ?? '';
    $port = $_POST['db_port'] ?? '3306';
    $dbname = $_POST['db_name'] ?? '';
    $user = $_POST['db_user'] ?? '';
    $pass = $_POST['db_pass'] ?? '';
    $prefix = $_POST['db_prefix'] ?? 'hds_';
    
    // Primeiro tenta conectar sem especificar o banco
    $dsn = "mysql:host={$host};port={$port}";
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Verifica se o banco existe
    $stmt = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        echo json_encode([
            'success' => false,
            'type' => 'database_not_found',
            'message' => 'Banco de dados não existe. Deseja criar?'
        ]);
        exit;
    }
    
    // Se chegou aqui, testa a conexão com o banco
    $conn = new PDO("mysql:host={$host};port={$port};dbname={$dbname}", $user, $pass);
    
    // Salva na sessão
    $_SESSION['db_config'] = [
        'host' => $host,
        'port' => $port,
        'name' => $dbname,
        'user' => $user,
        'pass' => $pass,
        'prefix' => $prefix
    ];
    
    echo json_encode([
        'success' => true,
        'message' => 'Conexão estabelecida com sucesso!'
    ]);
} catch (Exception $e) {
    error_log("Erro na conexão: " . $e->getMessage()); // Log
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro na conexão: ' . $e->getMessage()
    ]);
} 