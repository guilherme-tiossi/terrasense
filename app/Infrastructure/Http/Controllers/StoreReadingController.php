<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Readings\UseCases\UpdateUserPlantHumidity\UpdateUserPlantHumidity;
use App\Application\Readings\UseCases\UpdateUserPlantHumidity\UpdateUserPlantHumidityInputDto;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use App\Infrastructure\Http\Middleware\ValidateHmacSignature;
use App\Infrastructure\Http\Requests\StoreReadingRequest;
use App\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class StoreReadingController extends Controller
{
    public function __invoke(
        StoreReadingRequest $request,
        UpdateUserPlantHumidity $useCase,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->attributes->get(ValidateHmacSignature::USER_ATTRIBUTE);

        try {
            $output = $useCase->execute(new UpdateUserPlantHumidityInputDto(
                userId: $user->id,
                userPlantId: $request->integer('user_plant_id'),
                humidity: $request->float('humidity'),
                measuredAt: $request->string('measured_at')->toString(),
            ));
        } catch (AppException) {
            return response()->json(['message' => 'Planta do usuário não encontrada.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'user_plant_id' => $output->userPlantId,
            'current_humidity' => $output->currentHumidity,
            'measured_at' => $output->measuredAt,
        ]);
    }
}
