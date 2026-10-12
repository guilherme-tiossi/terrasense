<?php

namespace App\Application\Readings\UseCases\UpdateUserPlantHumidity;

readonly class UpdateUserPlantHumidityOutputDto
{
    public function __construct(
        public int $userPlantId,
        public float $currentHumidity,
        public string $measuredAt,
    ) {}
}
