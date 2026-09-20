<?php

namespace App\Application\Weather\Services\GetWeatherContext;

readonly class GetWeatherContextInputDto
{
    public function __construct(
        public string $locationKey,
    ) {}
}
