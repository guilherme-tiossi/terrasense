<?php

namespace App\Application\UseCases\UpdateUserPlantHumidity;

readonly class UpdateUserPlantHumidityOutputDto
{
    public function __construct(
        public int $userPlantId,
        public float $currentHumidity,
        public string $measuredAt,
    ) {}
}
