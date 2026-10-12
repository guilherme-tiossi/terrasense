<?php

namespace App\Application\Irrigation\UseCases\ProcessIrrigationDecision;

use App\Application\Irrigation\Events\IrrigationDecisionMade;
use App\Application\Weather\Services\GetWeatherContext\GetWeatherContext;
use App\Application\Weather\Services\GetWeatherContext\GetWeatherContextInputDto;
use App\Domain\Irrigation\Services\DecideIrrigation\DecideIrrigation;
use App\Domain\Irrigation\Services\DecideIrrigation\DecideIrrigationInput;
use App\Exceptions\AppException;
use App\Infrastructure\Models\UserPlant;

class ProcessIrrigationDecision
{
    public function __construct(
        private readonly GetWeatherContext $getWeatherContext,
        private readonly DecideIrrigation $decideIrrigation,
    ) {}

    public function execute(ProcessIrrigationDecisionInputDto $input): ProcessIrrigationDecisionOutputDto
    {
        $userPlant = UserPlant::query()
            ->with('plant')
            ->find($input->userPlantId);
        if ($userPlant === null || $userPlant->plant === null) {
            throw new AppException('UserPlant não encontrado.');
        }

        $weather = $this->getWeatherContext->execute(new GetWeatherContextInputDto(
            locationKey: $userPlant->location_key,
        ));
        $decision = $this->decideIrrigation->execute(new DecideIrrigationInput(
            currentHumidity: (float) $userPlant->current_humidity,
            minimumAllowedHumidity: (float) $userPlant->plant->minimum_allowed_humidity,
            maximumAllowedHumidity: (float) $userPlant->plant->maximum_allowed_humidity,
            rainExpected: $weather->rainExpected,
        ));

        IrrigationDecisionMade::dispatch(
            userPlantId: $userPlant->id,
            decision: $decision,
            currentHumidity: (float) $userPlant->current_humidity,
            rainExpected: $weather->rainExpected,
            locationKey: $userPlant->location_key,
        );

        return new ProcessIrrigationDecisionOutputDto(
            userPlantId: $userPlant->id,
            decision: $decision,
            currentHumidity: (float) $userPlant->current_humidity,
            rainExpected: $weather->rainExpected,
            locationKey: $userPlant->location_key,
        );
    }
}
