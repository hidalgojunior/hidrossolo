#!/usr/bin/env php
<?php

/**
 * Hidrossolo - CLI de Migrations
 *
 * Uso:
 *   php migrate.php           # Executa migrations pendentes
 *   php migrate.php rollback  # Reverte último batch
 *   php migrate.php status    # Mostra status
 *   php migrate.php fresh     # Rollback + migrate (cuidado!)
 */

require __DIR__ . '/vendor/autoload.php';

use App\Core\App;
use App\Core\MigrationRunner;

// Inicializar app
$app = App::getInstance();

// Comando
$command = $argv[1] ?? 'migrate';

$runner = new MigrationRunner();

echo "\n🧊 Hidrossolo - Migration Runner\n";
echo str_repeat('─', 50) . "\n";

try {
    switch ($command) {
        case 'migrate':
            $result = $runner->migrate();
            break;
        case 'rollback':
            $result = $runner->rollback();
            break;
        case 'status':
            $status = $runner->status();
            if (empty($status)) {
                echo "📭 Nenhuma migration encontrada.\n";
            } else {
                foreach ($status as $migration => $state) {
                    $icon = $state === 'executada' ? '✅' : '⏳';
                    echo "{$icon} {$migration} ({$state})\n";
                }
            }
            echo "\n";
            exit(0);
        case 'fresh':
            echo "⚠️  Isso irá REVERTER todas as migrations e executá-las novamente.\n";
            echo "   Digite 'SIM' para confirmar: ";
            $confirm = trim(fgets(STDIN));
            if ($confirm !== 'SIM') {
                echo "❌ Cancelado.\n";
                exit(0);
            }

            // Rollback até não haver mais
            while (true) {
                $rb = $runner->rollback();
                if ($rb['rolled_back'] === 0) break;
            }
            $result = $runner->migrate();
            break;
        default:
            echo "❌ Comando desconhecido: {$command}\n";
            echo "   Comandos disponíveis: migrate, rollback, status, fresh\n\n";
            exit(1);
    }

    echo "✅ {$result['message']}\n";
    echo str_repeat('─', 50) . "\n\n";

} catch (\Throwable $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "   Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    exit(1);
}
