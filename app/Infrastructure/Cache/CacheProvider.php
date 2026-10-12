<?php

namespace App\Infrastructure\Cache;

interface CacheProvider
{
    public function get(string $key): ?string;

    public function put(string $key, string $value, int $ttlSeconds): void;
}
