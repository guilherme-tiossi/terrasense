<?php

namespace Tests\Integration\Application;

use App\Application\Events\UserPlantUpdated;
use App\Application\UseCases\UpdateUserPlantHumidity\UpdateUserPlantHumidity;
use App\Application\UseCases\UpdateUserPlantHumidity\UpdateUserPlantHumidityInputDto;
use App\Infrastructure\Models\Plant;
use App\Infrastructure\Models\User;
use App\Infrastructure\Models\UserPlant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UpdateUserPlantHumidityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private UserPlant $userPlant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $plant = Plant::create([
            'name' => 'Tomate',
            'minimum_allowed_humidity' => 30.00,
            'maximum_allowed_humidity' => 70.00,
        ]);
        $this->userPlant = UserPlant::create([
            'user_id' => $this->user->id,
            'plant_id' => $plant->id,
            'location_key' => 'sao_paulo',
            'current_humidity' => 45.00,
        ]);
    }

    public function test_atualiza_umidade_e_dispara_user_plant_updated(): void
    {
        Event::fake([UserPlantUpdated::class]);

        $output = (new UpdateUserPlantHumidity)->execute(new UpdateUserPlantHumidityInputDto(
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
        Event::assertDispatched(UserPlantUpdated::class);
    }

    public function test_nao_atualiza_user_plant_de_outro_usuario(): void
    {
        $otherUser = User::factory()->create();
        $this->expectException(ModelNotFoundException::class);

        (new UpdateUserPlantHumidity)->execute(new UpdateUserPlantHumidityInputDto(
            userId: $otherUser->id,
            userPlantId: $this->userPlant->id,
            humidity: 35.5,
            measuredAt: '2026-09-15T18:00:00Z',
        ));
    }
}
