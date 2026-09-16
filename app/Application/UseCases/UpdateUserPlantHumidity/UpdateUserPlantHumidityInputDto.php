<?php

namespace App\Application\UseCases\UpdateUserPlantHumidity;

readonly class UpdateUserPlantHumidityInputDto
{
    public function __construct(
        public int $userId,
        public int $userPlantId,
        public float $humidity,
        public string $measuredAt,
    ) {}
}
