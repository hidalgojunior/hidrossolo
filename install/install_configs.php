<?php
header('Content-Type: application/json');
session_start();

try {
    $config = $_SESSION['db_config'];
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']}";
    $conn = new PDO($dsn, $config['user'], $config['pass']);
    
    // Configurações iniciais
    $configs = [
        'site_name' => 'Hidrossolo',
        'site_url' => 'http://localhost/hidrossolo',
        'admin_email' => '',
        'timezone' => 'America/Sao_Paulo'
    ];
    
    // Verifica e insere cada configuração
    foreach ($configs as $key => $value) {
        // Verifica se já existe
        $stmt = $conn->prepare("SELECT COUNT(*) FROM {$config['prefix']}configs WHERE config_key = ?");
        $stmt->execute([$key]);
        $exists = $stmt->fetchColumn();
        
        if (!$exists) {
            $stmt = $conn->prepare("INSERT INTO {$config['prefix']}configs (config_key, config_value) VALUES (?, ?)");
            $stmt->execute([$key, $value]);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Configurações iniciais criadas com sucesso!'
    ]);

} catch (PDOException $e) {
    $message = match($e->getCode()) {
        '23000' => 'Erro: Algumas configurações já existem no banco de dados.',
        '42S02' => 'Erro: A tabela de configurações não foi encontrada.',
        default => 'Erro ao criar configurações: ' . $e->getMessage()
    };
    
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
} 