<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\Database;

/**
 * Solicitações de orçamento.
 *
 * O DDL da tabela fica aqui para ser exatamente o mesmo em três lugares:
 *   - `database/schema.sql` (instalação nova);
 *   - `database/migrations/2026_09_15_000011_create_orcamentos_table.php`;
 *   - `OrcamentoController`, porque a hospedagem compartilhada não tem acesso
 *     a terminal e, portanto, `php migrate.php` não roda em produção.
 *
 * Assim evita-se que as três cópias saiam de sincronia.
 */
final class Orcamentos
{
    public const TABELA = 'orcamentos';

    /**
     * Cria a tabela quando ela ainda nao existe (idempotente).
     *
     * A hospedagem compartilhada nao tem terminal, entao `php migrate.php` nao
     * roda em producao: a tabela e criada na primeira vez que o formulario
     * publico ou a lista do admin e usada. Em ambientes com linha de comando a
     * migration 2026_09_15_000011_create_orcamentos_table faz o mesmo trabalho.
     */
    public static function garantir(Database $db): void
    {
        try {
            $existe = $db->fetch(
                "SELECT COUNT(*) AS c FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
                [self::TABELA]
            );

            if ((int) ($existe['c'] ?? 0) === 0) {
                $db->query(self::DDL);
            }
        } catch (\Throwable) {
            // Sem permissao de DDL: as consultas seguintes reportam o erro.
        }
    }

    /** Somente colunas usadas por OrcamentoController::enviar(). */
    public const DDL = <<<'SQL'
        CREATE TABLE IF NOT EXISTS `orcamentos` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            service_type VARCHAR(100) NOT NULL,
            description TEXT NOT NULL,
            address VARCHAR(500) DEFAULT NULL,
            preferred_date DATE DEFAULT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'novo',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL;
}
