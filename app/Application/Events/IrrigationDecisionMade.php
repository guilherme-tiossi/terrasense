<?php

namespace App\Application\Events;

use App\Domain\Irrigation\Enums\IrrigationDecision;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IrrigationDecisionMade
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $userPlantId,
        public readonly IrrigationDecision $decision,
        public readonly float $currentHumidity,
        public readonly bool $rainExpected,
        public readonly string $locationKey,
    ) {}
}
