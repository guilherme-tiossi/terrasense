<?php

return [
    'hmac_tolerance_seconds' => (int) env('TERRASENSE_HMAC_TOLERANCE_SECONDS', 300),
    'weather_cache_ttl_seconds' => (int) env('TERRASENSE_WEATHER_CACHE_TTL_SECONDS', 1800),
];
