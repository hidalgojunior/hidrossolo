<?php
// scripts/install.php

echo "Bem-vindo à instalação do Hidrossolo CMS\n";
echo "Desenvolvido por hidalgojunior\n\n";


// Função para criar o arquivo de configuração do banco de dados
function createDatabaseConfig($host, $dbname, $username, $password) {
    $config = <<<EOT
<?php
return [
    'host' => '$host',
    'dbname' => '$dbname',
    'username' => '$username',
    'password' => '$password'
];
EOT;
    file_put_contents(__DIR__ . '/../src/config/database.php', $config);
}

// Coletar informações do banco de dados
echo "Por favor, forneça as informações do banco de dados:\n";
$dbHost = readline("Host do banco de dados (padrão: localhost): ") ?: 'localhost';
$dbName = readline("Nome do banco de dados: ");
$dbUser = readline("Usuário do banco de dados: ");
$dbPass = readline("Senha do banco de dados: ");

// Criar arquivo de configuração
createDatabaseConfig($dbHost, $dbName, $dbUser, $dbPass);

// Criar banco de dados e tabelas
try {
    $pdo = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Criar banco de dados
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
    $pdo->exec("USE `$dbName`");

    // Criar tabelas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            access_level INT NOT NULL
        )
    ");

    // Adicione mais comandos CREATE TABLE conforme necessário

    echo "Banco de dados e tabelas criados com sucesso.\n";
} catch(PDOException $e) {
    die("Erro na configuração do banco de dados: " . $e->getMessage());
}

// Configurar permissões de diretórios
$directories = ['src/views/cache', 'public/uploads'];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    chmod($dir, 0755);
    echo "Diretório $dir criado e permissões configuradas.\n";
}

echo "\nInstalação concluída com sucesso!\n";
echo "Obrigado por escolher o Hidrossolo CMS.\n";
echo "Se precisar de ajuda, visite: https://github.com/hidalgojunior/hidrossolo/issues\n";
echo "Você pode iniciar o servidor PHP embutido com: php -S localhost:8000 -t public\n";