<?php

namespace App\Application\Irrigation\Listeners;

use App\Application\Irrigation\UseCases\ProcessIrrigationDecision\ProcessIrrigationDecision;
use App\Application\Irrigation\UseCases\ProcessIrrigationDecision\ProcessIrrigationDecisionInputDto;
use App\Application\Readings\Events\UserPlantUpdated;

class HandleUserPlantUpdated
{
    public function __construct(
        private readonly ProcessIrrigationDecision $useCase,
    ) {}

    public function handle(UserPlantUpdated $event): void
    {
        $this->useCase->execute(new ProcessIrrigationDecisionInputDto(
            userPlantId: $event->userPlantId,
        ));
    }
}
