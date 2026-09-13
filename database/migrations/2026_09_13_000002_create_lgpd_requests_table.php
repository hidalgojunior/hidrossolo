<?php

declare(strict_types=1);

use App\Core\Database;

/**
 * Migration: solicitações de titular de dados (LGPD, art. 18).
 *
 * Data: 2026-09-13
 */
class CreateLgpdRequestsTable
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS lgpd_requests (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                protocol VARCHAR(30) NOT NULL,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                document VARCHAR(30) NULL,
                phone VARCHAR(30) NULL,
                request_type VARCHAR(60) NOT NULL,
                message TEXT NULL,
                consent TINYINT(1) NOT NULL DEFAULT 1,
                status ENUM('new','in_progress','answered','denied') NOT NULL DEFAULT 'new',
                response TEXT NULL,
                answered_at DATETIME NULL,
                answered_by INT NULL,
                ip_address VARCHAR(45) NULL,
                user_agent VARCHAR(500) NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_lgpd_protocol (protocol),
                KEY idx_lgpd_status (status),
                KEY idx_lgpd_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS lgpd_requests');
    }
}
