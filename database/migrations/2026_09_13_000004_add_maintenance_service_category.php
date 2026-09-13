<?php

declare(strict_types=1);

use App\Core\Database;

/**
 * Migration: tipo de serviço da manutenção (inclui troca de pneus).
 *
 * Data: 2026-09-13
 */
class AddMaintenanceServiceCategory
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        if (!$this->hasColumn('vehicle_maintenance', 'service_category')) {
            $this->db->query(
                "ALTER TABLE `vehicle_maintenance`
                 ADD COLUMN `service_category` VARCHAR(50) NULL AFTER `type`"
            );
        }
    }

    public function down(): void
    {
        if ($this->hasColumn('vehicle_maintenance', 'service_category')) {
            $this->db->query('ALTER TABLE `vehicle_maintenance` DROP COLUMN `service_category`');
        }
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
}
