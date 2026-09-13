<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\App;
use App\Core\Database;
use PDO;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected bool $timestamps = true;

    protected array $attributes = [];
    protected array $original = [];

    protected Database $db;

    public function __construct(array $attributes = [])
    {
        $this->db = App::getInstance()->getDb();
        $this->fill($attributes);
    }

    /**
     * Preenche os atributos do model.
     * Sempre inclui a chave primária e timestamps, mesmo se não estiverem no fillable.
     */
    public function fill(array $attributes): self
    {
        $alwaysAllowed = [$this->primaryKey, 'created_at', 'updated_at'];

        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable) || in_array($key, $alwaysAllowed) || empty($this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        $this->original = $this->attributes;
        return $this;
    }

    /**
     * Retorna um atributo.
     */
    public function __get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Define um atributo.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Verifica se um atributo existe.
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Converte para array (removendo hidden).
     */
    public function toArray(): array
    {
        $data = $this->attributes;
        foreach ($this->hidden as $hidden) {
            unset($data[$hidden]);
        }
        return $data;
    }

    // ========================================
    // QUERY METHODS (Static)
    // ========================================

    protected static function query(): Database
    {
        return App::getInstance()->getDb();
    }

    protected static function getTable(): string
    {
        return (new static())->table;
    }

    /**
     * Busca todos os registros.
     */
    public static function all(): array
    {
        $rows = self::query()->fetchAll("SELECT * FROM " . self::getTable() . " ORDER BY id DESC");
        return array_map(fn($row) => new static($row), $rows);
    }

    /**
     * Busca por ID.
     */
    public static function find(int $id): ?static
    {
        $row = self::query()->fetch("SELECT * FROM " . self::getTable() . " WHERE id = ?", [$id]);
        return $row ? new static($row) : null;
    }

    /**
     * Busca por campo específico.
     */
    public static function findBy(string $column, mixed $value): ?static
    {
        $row = self::query()->fetch(
            "SELECT * FROM " . self::getTable() . " WHERE {$column} = ? LIMIT 1",
            [$value]
        );
        return $row ? new static($row) : null;
    }

    /**
     * Busca múltiplos por campo.
     */
    public static function findAllBy(string $column, mixed $value, string $orderBy = 'id DESC'): array
    {
        $rows = self::query()->fetchAll(
            "SELECT * FROM " . self::getTable() . " WHERE {$column} = ? ORDER BY {$orderBy}",
            [$value]
        );
        return array_map(fn($row) => new static($row), $rows);
    }

    /**
     * WHERE customizado.
     */
    public static function where(string $column, string $operator, mixed $value): array
    {
        $rows = self::query()->fetchAll(
            "SELECT * FROM " . self::getTable() . " WHERE {$column} {$operator} ?",
            [$value]
        );
        return array_map(fn($row) => new static($row), $rows);
    }

    /**
     * Conta registros.
     */
    public static function count(string $where = '1', array $params = []): int
    {
        $row = self::query()->fetch(
            "SELECT COUNT(*) as total FROM " . self::getTable() . " WHERE {$where}",
            $params
        );
        return (int) ($row['total'] ?? 0);
    }

    // ========================================
    // INSTANCE METHODS
    // ========================================

    /**
     * Salva (insert ou update).
     */
    public function save(): bool
    {
        if (!empty($this->attributes[$this->primaryKey])) {
            return $this->update();
        }
        return $this->insert();
    }

    /**
     * Insere um novo registro.
     */
    protected function insert(): bool
    {
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            $this->attributes['created_at'] = $now;
            $this->attributes['updated_at'] = $now;
        }

        $data = array_intersect_key($this->attributes, array_flip($this->fillable));
        if (empty($data)) {
            $data = $this->attributes;
            unset($data[$this->primaryKey]);
        }

        $id = $this->db->insert($this->table, $data);
        if ($id) {
            $this->attributes[$this->primaryKey] = $id;
            return true;
        }
        return false;
    }

    /**
     * Atualiza um registro existente.
     */
    protected function update(): bool
    {
        if ($this->timestamps) {
            $this->attributes['updated_at'] = date('Y-m-d H:i:s');
        }

        $data = array_intersect_key($this->attributes, array_flip($this->fillable));
        if (empty($data)) {
            $data = $this->attributes;
            unset($data[$this->primaryKey]);
        }

        $affected = $this->db->update(
            $this->table,
            $data,
            "{$this->primaryKey} = ?",
            [$this->attributes[$this->primaryKey]]
        );
        return $affected > 0;
    }

    /**
     * Deleta o registro.
     */
    public function delete(): bool
    {
        if (empty($this->attributes[$this->primaryKey])) {
            return false;
        }

        $affected = $this->db->delete(
            $this->table,
            "{$this->primaryKey} = ?",
            [$this->attributes[$this->primaryKey]]
        );
        return $affected > 0;
    }

    /**
     * Retorna true se o model foi modificado desde o último fill/save.
     */
    public function isDirty(): bool
    {
        return $this->attributes !== $this->original;
    }
}
