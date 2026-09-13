<?php

declare(strict_types=1);

namespace App\Models;

class User extends Model
{
    protected string $table = 'users';
    protected array $fillable = [
        'name', 'email', 'password', 'role_id', 'avatar',
        'remember_token', 'password_reset_token', 'password_reset_expires',
        'last_login', 'active',
    ];
    protected array $hidden = ['password', 'remember_token', 'password_reset_token'];

    /**
     * Busca por email.
     */
    public static function findByEmail(string $email): ?static
    {
        return self::findBy('email', $email);
    }

    /**
     * Verifica se a senha confere.
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->attributes['password'] ?? '');
    }

    /**
     * Define a senha (hash automático).
     */
    public function setPassword(string $password): self
    {
        $this->attributes['password'] = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        return $this;
    }

    /**
     * Registra o último login.
     */
    public function markLogin(): void
    {
        $this->attributes['last_login'] = date('Y-m-d H:i:s');
        $this->db->update($this->table, ['last_login' => $this->attributes['last_login']], 'id = ?', [$this->attributes['id']]);
    }

    /**
     * Busca usuários ativos.
     */
    public static function active(): array
    {
        return self::findAllBy('active', 1);
    }
}
