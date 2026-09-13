<?php

declare(strict_types=1);

use App\Core\Database;

/**
 * Migration: Frota/Equipamentos, Motorista, Modelos de Contrato e Segurança.
 *
 * Data: 2026-09-13
 */
class AddFleetDriverContractsSecurity
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        /* -----------------------------------------------------------------
         | 1. Veículos x Equipamentos (geradores etc. reaproveitam a tabela)
         * --------------------------------------------------------------- */
        $this->addColumn('vehicles', 'category', "ENUM('vehicle','equipment') NOT NULL DEFAULT 'vehicle' AFTER `status`");
        $this->addColumn('vehicles', 'equipment_type', "VARCHAR(100) NULL AFTER `category`");
        $this->addColumn('vehicles', 'parent_vehicle_id', "INT NULL AFTER `equipment_type`");
        $this->addColumn('vehicles', 'current_hours', "DECIMAL(10,1) NULL AFTER `current_km`");

        $this->addIndex('vehicles', 'idx_vehicles_category', '`category`');
        $this->addForeignKey('vehicles', 'fk_vehicles_parent', '`parent_vehicle_id`', 'vehicles', '`id`', 'SET NULL');

        /* -----------------------------------------------------------------
         | 2. Lançamentos de abastecimento/manutenção por motorista
         * --------------------------------------------------------------- */
        $this->addColumn('vehicle_fuel', 'user_id', 'INT NULL AFTER `km_at_refuel`');
        $this->addColumn('vehicle_fuel', 'hours_at_refuel', 'DECIMAL(10,1) NULL AFTER `user_id`');
        $this->addColumn('vehicle_fuel', 'notes', 'VARCHAR(500) NULL AFTER `hours_at_refuel`');
        $this->addIndex('vehicle_fuel', 'idx_fuel_user', '`user_id`');

        $this->addColumn('vehicle_maintenance', 'user_id', 'INT NULL AFTER `description`');
        $this->addColumn('vehicle_maintenance', 'hours_at_maintenance', 'DECIMAL(10,1) NULL AFTER `user_id`');
        $this->addIndex('vehicle_maintenance', 'idx_maint_user', '`user_id`');

        /* -----------------------------------------------------------------
         | 3. Modelos de contrato com variáveis ({{cliente}}, {{valor}}...)
         * --------------------------------------------------------------- */
        $this->db->query("
            CREATE TABLE IF NOT EXISTS contract_templates (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                description VARCHAR(500) NULL,
                content LONGTEXT NOT NULL,
                variables JSON NULL,
                active TINYINT(1) NOT NULL DEFAULT 1,
                created_by INT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_templates_active (active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->addColumn('contracts', 'template_id', 'INT NULL AFTER `status`');
        $this->addColumn('contracts', 'content', 'LONGTEXT NULL AFTER `template_id`');
        $this->addColumn('contracts', 'variables', 'JSON NULL AFTER `content`');
        $this->addColumn('contracts', 'generated_at', 'DATETIME NULL AFTER `variables`');
        $this->addColumn('contracts', 'created_by', 'INT NULL AFTER `generated_at`');
        $this->addIndex('contracts', 'idx_contracts_template', '`template_id`');

        /* -----------------------------------------------------------------
         | 4. Segurança: tentativas de login e hardening de usuários
         * --------------------------------------------------------------- */
        $this->db->query("
            CREATE TABLE IF NOT EXISTS login_attempts (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                email VARCHAR(255) NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent VARCHAR(500) NULL,
                successful TINYINT(1) NOT NULL DEFAULT 0,
                reason VARCHAR(100) NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_login_email_time (email, created_at),
                KEY idx_login_ip_time (ip_address, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Corrige coluna referenciada pelo AuthMiddleware (API token) que não existia
        $this->addColumn('users', 'api_token', 'VARCHAR(64) NULL AFTER `remember_token`');
        $this->addColumn('users', 'password_changed_at', 'DATETIME NULL AFTER `password_reset_expires`');
        $this->addIndex('users', 'uniq_users_api_token', '`api_token`', true);

        /* -----------------------------------------------------------------
         | 5. Papel "motorista"
         * --------------------------------------------------------------- */
        $exists = $this->db->fetch("SELECT id FROM roles WHERE name = 'motorista'");

        if (!$exists) {
            $this->db->insert('roles', [
                'name' => 'motorista',
                'description' => 'Motorista - Lança abastecimentos e manutenções dos veículos/equipamentos',
            ]);
        }
    }

    public function down(): void
    {
        $this->db->query("DROP TABLE IF EXISTS login_attempts");
        $this->db->query("DROP TABLE IF EXISTS contract_templates");

        foreach ([
            'vehicles' => ['category', 'equipment_type', 'parent_vehicle_id', 'current_hours'],
            'vehicle_fuel' => ['user_id', 'hours_at_refuel', 'notes'],
            'vehicle_maintenance' => ['user_id', 'hours_at_maintenance'],
            'contracts' => ['template_id', 'content', 'variables', 'generated_at', 'created_by'],
            'users' => ['api_token', 'password_changed_at'],
        ] as $table => $columns) {
            foreach ($columns as $column) {
                $this->dropColumn($table, $column);
            }
        }

        $this->db->delete('roles', 'name = ?', ['motorista']);
    }

    /* =====================================================================
     | Helpers idempotentes (MySQL não tem ADD COLUMN IF NOT EXISTS)
     * =================================================================== */

    private function hasColumn(string $table, string $column): bool
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS c FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
            [$table, $column]
        );

        return ((int) ($row['c'] ?? 0)) > 0;
    }

    private function addColumn(string $table, string $column, string $definition): void
    {
        if (!$this->hasColumn($table, $column) && $this->tableExists($table)) {
            $this->db->query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        }
    }

    private function dropColumn(string $table, string $column): void
    {
        if ($this->hasColumn($table, $column)) {
            $this->db->query("ALTER TABLE `{$table}` DROP COLUMN `{$column}`");
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS c FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?",
            [$table, $index]
        );

        return ((int) ($row['c'] ?? 0)) > 0;
    }

    private function addIndex(string $table, string $index, string $columns, bool $unique = false): void
    {
        if (!$this->hasIndex($table, $index) && $this->tableExists($table)) {
            $kind = $unique ? 'UNIQUE' : '';
            $this->db->query("ALTER TABLE `{$table}` ADD {$kind} INDEX `{$index}` ({$columns})");
        }
    }

    private function addForeignKey(string $table, string $name, string $column, string $refTable, string $refColumn, string $onDelete): void
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS c FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?",
            [$table, $name]
        );

        if (((int) ($row['c'] ?? 0)) === 0 && $this->tableExists($table)) {
            $this->db->query(
                "ALTER TABLE `{$table}` ADD CONSTRAINT `{$name}`
                 FOREIGN KEY ({$column}) REFERENCES `{$refTable}` ({$refColumn}) ON DELETE {$onDelete}"
            );
        }
    }

    private function tableExists(string $table): bool
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS c FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
            [$table]
        );

        return ((int) ($row['c'] ?? 0)) > 0;
    }
}
