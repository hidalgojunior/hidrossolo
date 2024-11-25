<?php
header('Content-Type: application/json');
session_start();

try {
    $host = $_POST['db_host'] ?? '';
    $port = $_POST['db_port'] ?? '3306';
    $dbname = $_POST['db_name'] ?? '';
    $user = $_POST['db_user'] ?? '';
    $pass = $_POST['db_pass'] ?? '';
    
    // Conecta sem especificar o banco
    $dsn = "mysql:host={$host};port={$port}";
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Cria o banco de dados
    $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    echo json_encode([
        'success' => true,
        'message' => 'Banco de dados criado com sucesso!'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao criar banco de dados: ' . $e->getMessage()
    ]);
} 