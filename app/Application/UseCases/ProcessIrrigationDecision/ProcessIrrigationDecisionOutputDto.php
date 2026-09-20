<?php

namespace App\Application\UseCases\ProcessIrrigationDecision;

use App\Domain\Irrigation\Enums\IrrigationDecision;

readonly class ProcessIrrigationDecisionOutputDto
{
    public function __construct(
        public int $userPlantId,
        public IrrigationDecision $decision,
        public float $currentHumidity,
        public bool $rainExpected,
        public string $locationKey,
    ) {}
}
