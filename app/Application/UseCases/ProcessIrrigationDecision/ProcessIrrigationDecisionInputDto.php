<?php

namespace App\Application\UseCases\ProcessIrrigationDecision;

readonly class ProcessIrrigationDecisionInputDto
{
    public function __construct(
        public int $userPlantId,
    ) {}
}
