<?php

namespace App\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Application\UseCases\UpdateUserPlantHumidity\UpdateUserPlantHumidity;
use App\Application\UseCases\UpdateUserPlantHumidity\UpdateUserPlantHumidityInputDto;
use App\Infrastructure\Http\Middleware\ValidateHmacSignature;
use App\Infrastructure\Http\Requests\StoreReadingRequest;
use App\Infrastructure\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        // handler global de exceção
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'User plant not found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'user_plant_id' => $output->userPlantId,
            'current_humidity' => $output->currentHumidity,
            'measured_at' => $output->measuredAt,
        ]);
    }
}
