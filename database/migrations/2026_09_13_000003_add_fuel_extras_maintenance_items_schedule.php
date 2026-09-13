<?php

declare(strict_types=1);

use App\Core\Database;

/**
 * Migration: despesas extras no abastecimento, itens trocados na manutenção,
 * próxima revisão e agenda de manutenção com notificações.
 *
 * Data: 2026-09-13
 */
class AddFuelExtrasMaintenanceItemsSchedule
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        /* -----------------------------------------------------------------
         | 1. Despesas extras lançadas junto ao abastecimento
         * --------------------------------------------------------------- */
        $this->db->query("
            CREATE TABLE IF NOT EXISTS vehicle_fuel_extras (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                fuel_id INT NOT NULL,
                description VARCHAR(255) NOT NULL,
                amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_fuel_extras_fuel (fuel_id),
                CONSTRAINT fk_fuel_extras_fuel FOREIGN KEY (fuel_id)
                    REFERENCES vehicle_fuel (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        /* -----------------------------------------------------------------
         | 2. Itens trocados/serviços executados na manutenção
         * --------------------------------------------------------------- */
        $this->db->query("
            CREATE TABLE IF NOT EXISTS vehicle_maintenance_items (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                maintenance_id INT NOT NULL,
                description VARCHAR(255) NOT NULL,
                quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
                unit_cost DECIMAL(10,2) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_maint_items_maint (maintenance_id),
                CONSTRAINT fk_maint_items_maint FOREIGN KEY (maintenance_id)
                    REFERENCES vehicle_maintenance (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        /* -----------------------------------------------------------------
         | 3. Próxima revisão do veículo/equipamento
         * --------------------------------------------------------------- */
        $this->addColumn('vehicle_maintenance', 'next_review_date', 'DATE NULL AFTER `maintenance_date`');

        /* -----------------------------------------------------------------
         | 4. Agenda de manutenções (compromissos) + controle de avisos
         * --------------------------------------------------------------- */
        $this->db->query("
            CREATE TABLE IF NOT EXISTS fleet_schedules (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                vehicle_id INT NULL,
                title VARCHAR(255) NOT NULL,
                description VARCHAR(500) NULL,
                scheduled_date DATE NOT NULL,
                scheduled_time TIME NULL,
                type ENUM('maintenance','revision','inspection','other') NOT NULL DEFAULT 'maintenance',
                status ENUM('planned','done','cancelled') NOT NULL DEFAULT 'planned',
                notified_30_at DATETIME NULL,
                notified_15_at DATETIME NULL,
                notified_7_at DATETIME NULL,
                created_by INT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_schedule_date (scheduled_date),
                KEY idx_schedule_vehicle (vehicle_id),
                KEY idx_schedule_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        /* -----------------------------------------------------------------
         | 5. Conta administradora geral (invisível para os demais admins)
         * --------------------------------------------------------------- */
        $alvo = $this->db->fetch("SELECT id, email FROM users WHERE email = 'admin@hidrossolo.com.br'");
        $jaExiste = $this->db->fetch("SELECT id FROM users WHERE email = 'hidalgojunior@gmail.com'");

        $superadminId = (int) ($this->db->fetch("SELECT id FROM roles WHERE name = 'superadmin'")['id'] ?? 5);

        if ($alvo && !$jaExiste) {
            $this->db->update('users', [
                'email' => 'hidalgojunior@gmail.com',
                'role_id' => $superadminId,
            ], 'id = ?', [(int) $alvo['id']]);
        } elseif ($alvo === null && $jaExiste) {
            // Já migrado: garante o papel de acesso total
            $this->db->update('users', ['role_id' => $superadminId], 'email = ?', ['hidalgojunior@gmail.com']);
        }
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS fleet_schedules');
        $this->db->query('DROP TABLE IF EXISTS vehicle_maintenance_items');
        $this->db->query('DROP TABLE IF EXISTS vehicle_fuel_extras');

        if ($this->hasColumn('vehicle_maintenance', 'next_review_date')) {
            $this->db->query('ALTER TABLE `vehicle_maintenance` DROP COLUMN `next_review_date`');
        }

        $this->db->update(
            'users',
            ['email' => 'admin@hidrossolo.com.br', 'role_id' => 1],
            'email = ?',
            ['hidalgojunior@gmail.com']
        );
    }

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
        if (!$this->hasColumn($table, $column)) {
            $this->db->query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        }
    }
}
