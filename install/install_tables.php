<?php
// Ativa log de erros em arquivo
ini_set('log_errors', 1);
ini_set('error_log', 'install_error.log');

// Headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
session_start();

// Log para debug
error_log("Iniciando instalação das tabelas");
error_log("Dados da sessão: " . print_r($_SESSION, true));

try {
    if (empty($_SESSION['db_config'])) {
        throw new Exception('Configuração do banco de dados não encontrada na sessão');
    }

    $config = $_SESSION['db_config'];
    error_log("Configuração recuperada: " . print_r($config, true));

    // Conecta ao banco
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']}";
    $conn = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    error_log("Conexão estabelecida com sucesso");

    // Primeira tabela para teste
    $sql = "CREATE TABLE IF NOT EXISTS `{$config['prefix']}users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `email` varchar(100) NOT NULL,
        `password` varchar(255) NOT NULL,
        `role` varchar(20) NOT NULL DEFAULT 'user',
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    error_log("Tentando criar tabela users");
    $conn->exec($sql);
    error_log("Tabela users criada com sucesso");

    // Tabela de roles/permissões
    $sql = "CREATE TABLE IF NOT EXISTS `{$config['prefix']}roles` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `role_name` varchar(50) NOT NULL,
        `role_description` varchar(255),
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `role_name` (`role_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    error_log("Tentando criar tabela roles");
    $conn->exec($sql);
    error_log("Tabela roles criada com sucesso");

    $response = [
        'success' => true,
        'message' => 'Tabela de usuários e roles criadas com sucesso!'
    ];

    error_log("Enviando resposta: " . json_encode($response));
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Erro na instalação: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    $response = [
        'success' => false,
        'message' => 'Erro na instalação: ' . $e->getMessage()
    ];

    echo json_encode($response);
}

exit; 