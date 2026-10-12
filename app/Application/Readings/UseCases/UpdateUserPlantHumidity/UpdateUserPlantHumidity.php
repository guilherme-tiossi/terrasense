<?php

namespace App\Application\Readings\UseCases\UpdateUserPlantHumidity;

use App\Application\Readings\Events\UserPlantUpdated;
use App\Exceptions\AppException;
use App\Infrastructure\Models\UserPlant;

class UpdateUserPlantHumidity
{
    public function execute(UpdateUserPlantHumidityInputDto $input): UpdateUserPlantHumidityOutputDto
    {
        $userPlant = UserPlant::query()
            ->where('id', $input->userPlantId)
            ->where('user_id', $input->userId)
            ->first();

        if ($userPlant === null) {
            throw new AppException('Planta não encontrada.');
        }

        $userPlant->update(['current_humidity' => $input->humidity]);
        $userPlant->refresh();

        UserPlantUpdated::dispatch(userPlantId: $userPlant->id);

        return new UpdateUserPlantHumidityOutputDto(
            userPlantId: $userPlant->id,
            currentHumidity: (float) $userPlant->current_humidity,
            measuredAt: $input->measuredAt,
        );
    }
}
