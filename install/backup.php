<?php
header('Content-Type: application/json');
session_start();

try {
    $config = $_SESSION['db_config'] ?? require('../config.php');
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']}";
    $conn = new PDO($dsn, $config['user'], $config['pass']);
    
    $tables = [
        'users',
        'configs',
        'roles'
    ];
    
    $backup = [];
    
    foreach ($tables as $table) {
        $tableName = $config['prefix'] . $table;
        $stmt = $conn->query("SELECT * FROM `{$tableName}`");
        $backup[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Salva backup em JSON
    $backupFile = 'backup_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents("backups/{$backupFile}", json_encode($backup, JSON_PRETTY_PRINT));
    
    return [
        'success' => true,
        'message' => 'Backup realizado com sucesso!',
        'file' => $backupFile
    ];

} catch (Exception $e) {
    return [
        'success' => false,
        'message' => 'Erro ao realizar backup: ' . $e->getMessage()
    ];
} 