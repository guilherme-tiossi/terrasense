<?php

namespace App\Infrastructure\Weather;

readonly class WeatherData
{
    public function __construct(
        public float $temperature,
        public bool $rainExpected,
        public string $fetchedAt,
    ) {}
}
