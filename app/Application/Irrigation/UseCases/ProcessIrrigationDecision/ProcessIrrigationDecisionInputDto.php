<?php

namespace App\Application\Irrigation\UseCases\ProcessIrrigationDecision;

readonly class ProcessIrrigationDecisionInputDto
{
    public function __construct(
        public int $userPlantId,
    ) {}
}
