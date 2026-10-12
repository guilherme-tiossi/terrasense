<?php

namespace App\Infrastructure\Cache;

use Illuminate\Support\Facades\Redis;

class RedisCacheProvider implements CacheProvider
{
    public function get(string $key): ?string
    {
        $value = Redis::get($key);

        return is_string($value) ? $value : null;
    }

    public function put(string $key, string $value, int $ttlSeconds): void
    {
        Redis::setex($key, $ttlSeconds, $value);
    }
}
