<?php

declare(strict_types=1);

namespace App\Core;

class MigrationRunner
{
    private Database $db;
    private string $migrationsPath;
    private string $table = 'migrations';

    public function __construct()
    {
        $this->db = App::getInstance()->getDb();
        $this->migrationsPath = dirname(__DIR__, 2) . '/database/migrations';
    }

    /**
     * Inicializa a tabela de migrations.
     */
    public function init(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT NOT NULL DEFAULT 1,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Executa migrations pendentes.
     */
    public function migrate(): array
    {
        $this->init();

        $executed = $this->getExecutedMigrations();
        $files = $this->getMigrationFiles();
        $pending = array_diff($files, $executed);

        if (empty($pending)) {
            return ['message' => 'Nenhuma migration pendente.', 'executed' => 0];
        }

        sort($pending);

        $batch = $this->getNextBatch();
        $count = 0;

        foreach ($pending as $migration) {
            $this->runMigration($migration, $batch);
            $count++;
        }

        Logger::info("{$count} migration(s) executada(s) no batch {$batch}");

        return [
            'message' => "{$count} migration(s) executada(s).",
            'executed' => $count,
            'batch' => $batch,
        ];
    }

    /**
     * Rollback do último batch.
     */
    public function rollback(): array
    {
        $this->init();

        $lastBatch = $this->getLastBatch();
        if ($lastBatch === 0) {
            return ['message' => 'Nenhuma migration para reverter.', 'rolled_back' => 0];
        }

        $migrations = $this->db->fetchAll(
            "SELECT migration FROM {$this->table} WHERE batch = ? ORDER BY id DESC",
            [$lastBatch]
        );

        $count = 0;
        foreach ($migrations as $m) {
            $this->rollbackMigration($m['migration']);
            $count++;
        }

        $this->db->delete($this->table, 'batch = ?', [$lastBatch]);

        Logger::info("{$count} migration(s) revertida(s) do batch {$lastBatch}");

        return [
            'message' => "{$count} migration(s) revertida(s).",
            'rolled_back' => $count,
            'batch' => $lastBatch,
        ];
    }

    /**
     * Mostra status das migrations.
     */
    public function status(): array
    {
        $this->init();

        $executed = $this->getExecutedMigrations();
        $files = $this->getMigrationFiles();

        $status = [];
        foreach ($files as $file) {
            $status[$file] = in_array($file, $executed) ? 'executada' : 'pendente';
        }

        return $status;
    }

    // ========================================
    // INTERNAL
    // ========================================

    private function getMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            mkdir($this->migrationsPath, 0755, true);
            return [];
        }

        $files = glob($this->migrationsPath . '/*.php');
        return array_map('basename', $files);
    }

    private function getExecutedMigrations(): array
    {
        $rows = $this->db->fetchAll("SELECT migration FROM {$this->table}");
        return array_column($rows, 'migration');
    }

    private function getNextBatch(): int
    {
        return $this->getLastBatch() + 1;
    }

    private function getLastBatch(): int
    {
        $row = $this->db->fetch("SELECT MAX(batch) as max_batch FROM {$this->table}");
        return (int) ($row['max_batch'] ?? 0);
    }

    private function runMigration(string $filename, int $batch): void
    {
        $filepath = $this->migrationsPath . '/' . $filename;

        // A migration define uma classe com método up()
        require_once $filepath;

        $className = $this->getClassName($filename);
        if (!class_exists($className)) {
            throw new \RuntimeException("Classe {$className} não encontrada em {$filename}");
        }

        $instance = new $className($this->db);

        Logger::info("Executando migration: {$filename}");
        $instance->up();

        $this->db->insert($this->table, [
            'migration' => $filename,
            'batch' => $batch,
        ]);
    }

    private function rollbackMigration(string $filename): void
    {
        $filepath = $this->migrationsPath . '/' . $filename;

        require_once $filepath;

        $className = $this->getClassName($filename);
        if (!class_exists($className)) {
            throw new \RuntimeException("Classe {$className} não encontrada em {$filename}");
        }

        $instance = new $className($this->db);

        Logger::info("Revertendo migration: {$filename}");
        $instance->down();
    }

    /**
     * Extrai o nome da classe do nome do arquivo.
     * Ex: 2024_01_01_000001_create_users_table.php -> CreateUsersTable
     */
    private function getClassName(string $filename): string
    {
        // Remove timestamp e extensão
        $name = preg_replace('/^\d+_\d+_\d+_\d+_/', '', $filename);
        $name = str_replace('.php', '', $name);

        // Converter snake_case para PascalCase
        $parts = explode('_', $name);
        $parts = array_map('ucfirst', $parts);

        return implode('', $parts);
    }
}
