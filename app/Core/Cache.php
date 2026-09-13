<?php

declare(strict_types=1);

namespace App\Core;

class Cache
{
    private static ?\Redis $redis = null;

    public static function getRedis(): ?\Redis
    {
        if (self::$redis === null) {
            try {
                self::$redis = new \Redis();
                self::$redis->connect(
                    $_ENV['REDIS_HOST'] ?? 'redis',
                    (int)($_ENV['REDIS_PORT'] ?? 6379),
                    1
                );
            } catch (\Throwable) {
                return null;
            }
        }
        return self::$redis;
    }

    public static function get(string $key): mixed
    {
        $redis = self::getRedis();
        if (!$redis) return null;

        $value = $redis->get('hidrossolo:' . $key);
        return $value ? json_decode($value, true) : null;
    }

    public static function set(string $key, mixed $value, int $ttl = 3600): void
    {
        $redis = self::getRedis();
        if (!$redis) return;

        $redis->setex('hidrossolo:' . $key, $ttl, json_encode($value));
    }

    public static function delete(string $key): void
    {
        $redis = self::getRedis();
        if (!$redis) return;
        $redis->del('hidrossolo:' . $key);
    }

    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $cached = self::get($key);
        if ($cached !== null) return $cached;

        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }
}
