<?php

declare(strict_types=1);

namespace App\Models;

class Contact extends Model
{
    protected string $table = 'contacts';
    protected array $fillable = [
        'name', 'email', 'phone', 'message',
        'status', 'ip_address',
    ];

    /**
     * Marca como lido.
     */
    public function markAsRead(): void
    {
        $this->attributes['status'] = 'read';
        $this->db->update($this->table, ['status' => 'read'], 'id = ?', [$this->attributes['id']]);
    }

    /**
     * Marca como respondido.
     */
    public function markAsReplied(): void
    {
        $this->attributes['status'] = 'replied';
        $this->db->update($this->table, ['status' => 'replied'], 'id = ?', [$this->attributes['id']]);
    }

    /**
     * Contatos novos (não lidos).
     */
    public static function new(): array
    {
        return self::findAllBy('status', 'new', 'created_at DESC');
    }

    /**
     * Total de não lidos.
     */
    public static function unreadCount(): int
    {
        return self::count("status = 'new'");
    }
}
