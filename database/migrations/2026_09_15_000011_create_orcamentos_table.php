<?php

declare(strict_types=1);

use App\Core\Database;
use App\Support\Orcamentos;

/**
 * Migration: tabela de solicitações de orçamento.
 *
 * O formulário `/orcamento` já gravava em `orcamentos`, mas a tabela nunca
 * havia sido criada — o envio resultava em erro. O DDL é compartilhado com
 * `App\Support\Orcamentos` para não divergir do schema.
 *
 * Data: 2026-09-15
 */
class CreateOrcamentosTable
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        if (!$this->existe()) {
            $this->db->query(Orcamentos::DDL);
        }
    }

    public function down(): void
    {
        if ($this->existe()) {
            $this->db->query('DROP TABLE `' . Orcamentos::TABELA . '`');
        }
    }

    private function existe(): bool
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS c FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
            [Orcamentos::TABELA]
        );

        return ((int) ($row['c'] ?? 0)) > 0;
    }
}
