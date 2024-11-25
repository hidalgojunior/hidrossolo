<?php
header('Content-Type: application/json');
session_start();

try {
    $config = $_SESSION['db_config'];
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']}";
    $conn = new PDO($dsn, $config['user'], $config['pass']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
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
    
    // Verifica se o usuário já existe
    $stmt = $conn->prepare("SELECT id FROM {$config['prefix']}users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Atualiza o usuário existente
        $stmt = $conn->prepare("UPDATE {$config['prefix']}users 
                              SET name = ?, 
                                  password = ?, 
                                  role = 'admin' 
                              WHERE email = ?");
        $stmt->execute([$name, $passHash, $email]);
        $message = 'Administrador atualizado com sucesso!';
    } else {
        // Insere novo usuário
        $stmt = $conn->prepare("INSERT INTO {$config['prefix']}users 
                              (name, email, password, role) 
                              VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$name, $email, $passHash]);
        $message = 'Administrador criado com sucesso!';
    }
    
    // Atualiza o email nas configurações
    $stmt = $conn->prepare("INSERT INTO {$config['prefix']}configs 
                          (config_key, config_value) 
                          VALUES ('admin_email', ?) 
                          ON DUPLICATE KEY UPDATE config_value = ?");
    $stmt->execute([$email, $email]);
    
    // Cria arquivo de configuração
    $configFile = '../config.php';
    $configContent = "<?php\n" .
                    "define('DB_HOST', '{$config['host']}');\n" .
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
        'message' => $message
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
} 