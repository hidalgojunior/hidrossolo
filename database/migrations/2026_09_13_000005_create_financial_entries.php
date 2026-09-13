<?php

declare(strict_types=1);

use App\Core\Database;

/**
 * Migration: fluxo de caixa da empresa (contas a pagar e a receber).
 *
 * Data: 2026-09-13
 */
class CreateFinancialEntries
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `financial_entries` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `kind` ENUM('income','expense') NOT NULL DEFAULT 'expense',
                `category` VARCHAR(60) NOT NULL DEFAULT 'outros',
                `description` VARCHAR(255) NOT NULL,
                `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `due_date` DATE NOT NULL,
                `paid_at` DATE NULL,
                `paid_amount` DECIMAL(12,2) NULL,
                `payment_method` VARCHAR(40) NULL,
                `status` ENUM('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
                `party` VARCHAR(160) NULL,
                `document` VARCHAR(60) NULL,
                `vehicle_id` INT UNSIGNED NULL,
                `maintenance_id` INT UNSIGNED NULL,
                `fleet_schedule_id` INT UNSIGNED NULL,
                `recurrence` ENUM('none','weekly','monthly','quarterly','yearly') NOT NULL DEFAULT 'none',
                `notes` TEXT NULL,
                `created_by` INT UNSIGNED NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_fe_due` (`due_date`),
                KEY `idx_fe_status` (`status`),
                KEY `idx_fe_kind` (`kind`),
                KEY `idx_fe_category` (`category`),
                KEY `idx_fe_vehicle` (`vehicle_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS `financial_entries`');
    }
}
