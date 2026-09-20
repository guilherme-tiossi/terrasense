<?php

namespace App\Application\Listeners;

use App\Application\Events\UserPlantUpdated;
use App\Application\UseCases\ProcessIrrigationDecision\ProcessIrrigationDecision;
use App\Application\UseCases\ProcessIrrigationDecision\ProcessIrrigationDecisionInputDto;

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
