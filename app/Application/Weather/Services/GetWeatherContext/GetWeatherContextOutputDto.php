<?php

namespace App\Application\Weather\Services\GetWeatherContext;

readonly class GetWeatherContextOutputDto
{
    public function __construct(
        public string $locationKey,
        public float $temperature,
        public bool $rainExpected,
        public string $fetchedAt,
    ) {}
}
