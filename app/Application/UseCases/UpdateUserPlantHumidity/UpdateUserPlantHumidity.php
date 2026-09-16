<?php

namespace App\Application\UseCases\UpdateUserPlantHumidity;

use App\Application\Events\UserPlantUpdated;
use App\Infrastructure\Models\UserPlant;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdateUserPlantHumidity
{
    public function execute(UpdateUserPlantHumidityInputDto $input): UpdateUserPlantHumidityOutputDto
    {
        $userPlant = UserPlant::query()
            ->where('id', $input->userPlantId)
            ->where('user_id', $input->userId)
            ->first() ?? throw new ModelNotFoundException('Planta não encontrada.');

        $userPlant->update(['current_humidity' => $input->humidity]);

        event(new UserPlantUpdated(
            userPlantId: $userPlant->id
        ));

        return new UpdateUserPlantHumidityOutputDto(
            userPlantId: $userPlant->id,
            currentHumidity: $userPlant->current_humidity,
            measuredAt: $input->measuredAt,
        );
    }
}
