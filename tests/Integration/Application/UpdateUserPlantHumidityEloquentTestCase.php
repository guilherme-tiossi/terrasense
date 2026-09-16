<?php

namespace Tests\Integration\Application;

use App\Application\UseCases\UpdateUserPlantHumidity\UpdateUserPlantHumidity;
use App\Infrastructure\Models\Plant;
use App\Infrastructure\Models\User;
use App\Infrastructure\Models\UserPlant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class UpdateUserPlantHumidityEloquentTestCase extends TestCase
{
    use RefreshDatabase;

    protected UpdateUserPlantHumidity $useCase;

    protected User $user;

    protected UserPlant $userPlant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCase = new UpdateUserPlantHumidity;

        $this->user = User::factory()->create(['hmac_secret' => 'integration-secret']);

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
}
