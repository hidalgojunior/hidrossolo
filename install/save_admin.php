<?php
header('Content-Type: application/json');
session_start();

try {
    $config = $_SESSION['db_config'];
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']}";
    $conn = new PDO($dsn, $config['user'], $config['pass']);
    
    // Pega os dados do POST
    $name = $_POST['admin_name'] ?? '';
    $email = $_POST['admin_email'] ?? '';
    $pass = $_POST['admin_pass'] ?? '';
    
    // Validações
    if (empty($name) || empty($email) || empty($pass)) {
        throw new Exception('Todos os campos são obrigatórios');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('E-mail inválido');
    }
    
    // Hash da senha
    $passHash = password_hash($pass, PASSWORD_DEFAULT);
    
    // Insere o administrador
    $stmt = $conn->prepare("INSERT INTO {$config['prefix']}users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->execute([$name, $email, $passHash]);
    
    // Atualiza o email nas configurações
    $stmt = $conn->prepare("UPDATE {$config['prefix']}configs SET config_value = ? WHERE config_key = 'admin_email'");
    $stmt->execute([$email]);
    
    // Cria arquivo de configuração
    $configFile = '../config.php';
    $configContent = "<?php\ndefine('DB_HOST', '{$config['host']}');\n" .
                    "define('DB_PORT', '{$config['port']}');\n" .
                    "define('DB_NAME', '{$config['name']}');\n" .
                    "define('DB_USER', '{$config['user']}');\n" .
                    "define('DB_PASS', '{$config['pass']}');\n" .
                    "define('DB_PREFIX', '{$config['prefix']}');\n";
    
    file_put_contents($configFile, $configContent);
    
    // Limpa a sessão de instalação
    unset($_SESSION['db_config']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Administrador configurado com sucesso!'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
} 