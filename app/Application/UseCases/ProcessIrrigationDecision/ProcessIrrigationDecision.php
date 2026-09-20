<?php

namespace App\Application\UseCases\ProcessIrrigationDecision;

use App\Application\Events\IrrigationDecisionMade;
use App\Application\Weather\Services\GetWeatherContext\GetWeatherContext;
use App\Application\Weather\Services\GetWeatherContext\GetWeatherContextInputDto;
use App\Domain\Irrigation\Enums\IrrigationDecision;
use App\Domain\Irrigation\Services\DecideIrrigation\DecideIrrigation;
use App\Domain\Irrigation\Services\DecideIrrigation\DecideIrrigationInput;
use App\Infrastructure\Models\UserPlant;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
            throw new ModelNotFoundException('UserPlant não encontrado.');
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

        event(new IrrigationDecisionMade(
            userPlantId: $userPlant->id,
            decision: $decision,
            currentHumidity: (float) $userPlant->current_humidity,
            rainExpected: $weather->rainExpected,
            locationKey: $userPlant->location_key,
        ));

        return new ProcessIrrigationDecisionOutputDto(
            userPlantId: $userPlant->id,
            decision: $decision,
            currentHumidity: (float) $userPlant->current_humidity,
            rainExpected: $weather->rainExpected,
            locationKey: $userPlant->location_key,
        );
    }
}
