<?php

namespace Tests\Integration\Application;

use App\Application\Events\UserPlantUpdated;
use App\Application\UseCases\UpdateUserPlantHumidity\UpdateUserPlantHumidityInputDto;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;

class UpdateUserPlantHumidityTest extends UpdateUserPlantHumidityEloquentTestCase
{
    public function test_atualiza_umidade_e_dispara_user_plant_updated(): void
    {
        Event::fake([UserPlantUpdated::class]);

        $output = $this->useCase->execute(new UpdateUserPlantHumidityInputDto(
            userId: $this->user->id,
            userPlantId: $this->userPlant->id,
            humidity: 35.5,
            measuredAt: '2026-09-15T18:00:00Z',
        ));

        $this->assertSame($this->userPlant->id, $output->userPlantId);
        $this->assertSame(35.5, $output->currentHumidity);
        $this->assertDatabaseHas('user_plants', [
            'id' => $this->userPlant->id,
            'current_humidity' => '35.50',
        ]);

        Event::assertDispatched(UserPlantUpdated::class, function (UserPlantUpdated $event): bool {
            return $event->userPlantId === $this->userPlant->id;
        });
    }

    public function test_nao_atualiza_user_plant_de_outro_usuario(): void
    {
        $otherUser = \App\Infrastructure\Models\User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        $this->useCase->execute(new UpdateUserPlantHumidityInputDto(
            userId: $otherUser->id,
            userPlantId: $this->userPlant->id,
            humidity: 35.5,
            measuredAt: '2026-09-15T18:00:00Z',
        ));
    }
}
