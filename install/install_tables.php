<?php
header('Content-Type: application/json');
session_start();

try {
    $config = $_SESSION['db_config'];
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']}";
    $conn = new PDO($dsn, $config['user'], $config['pass']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Array com os comandos SQL para criar as tabelas
    $tables = [
        // Tabela de usuários
        "CREATE TABLE IF NOT EXISTS `{$config['prefix']}users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `email` varchar(100) NOT NULL,
            `password` varchar(255) NOT NULL,
            `role` varchar(20) NOT NULL DEFAULT 'user',
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        // Tabela de configurações
        "CREATE TABLE IF NOT EXISTS `{$config['prefix']}configs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `config_key` varchar(50) NOT NULL,
            `config_value` text,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `config_key` (`config_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        // Tabela de permissões
        "CREATE TABLE IF NOT EXISTS `{$config['prefix']}roles` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `role_name` varchar(50) NOT NULL,
            `role_description` varchar(100),
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `role_name` (`role_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ];
    
    // Executa cada comando
    foreach ($tables as $sql) {
        $conn->exec($sql);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Tabelas criadas com sucesso!'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao criar tabelas: ' . $e->getMessage()
    ]);
} 