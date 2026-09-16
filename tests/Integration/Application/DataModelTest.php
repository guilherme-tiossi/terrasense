<?php

namespace Tests\Integration\Application;

use App\Infrastructure\Models\Plant;
use App\Infrastructure\Models\User;
use App\Infrastructure\Models\UserPlant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_plant_relationship_persists_humidity_and_plant_limits(): void
    {
        $user = User::factory()->create();
        $plant = Plant::create([
            'name' => 'Tomate',
            'minimum_allowed_humidity' => 30.00,
            'maximum_allowed_humidity' => 70.00,
        ]);

        $userPlant = UserPlant::create([
            'user_id' => $user->id,
            'plant_id' => $plant->id,
            'location_key' => 'sao_paulo',
            'current_humidity' => 45.50,
        ]);

        $this->assertDatabaseHas('user_plants', [
            'id' => $userPlant->id,
            'user_id' => $user->id,
            'plant_id' => $plant->id,
            'location_key' => 'sao_paulo',
            'current_humidity' => '45.50',
        ]);

        $loaded = UserPlant::with(['user', 'plant'])->findOrFail($userPlant->id);

        $this->assertSame('Tomate', $loaded->plant->name);
        $this->assertSame('30.00', $loaded->plant->minimum_allowed_humidity);
        $this->assertSame('70.00', $loaded->plant->maximum_allowed_humidity);
        $this->assertSame('sao_paulo', $loaded->location_key);
        $this->assertSame('test-hmac-secret', $loaded->user->hmac_secret);
    }

    public function test_same_plant_can_have_multiple_user_plants_in_different_locations(): void
    {
        $user = User::factory()->create();
        $plant = Plant::create([
            'name' => 'Tomate',
            'minimum_allowed_humidity' => 30.00,
            'maximum_allowed_humidity' => 70.00,
        ]);

        UserPlant::create([
            'user_id' => $user->id,
            'plant_id' => $plant->id,
            'location_key' => 'sao_paulo',
            'current_humidity' => 40.00,
        ]);

        UserPlant::create([
            'user_id' => $user->id,
            'plant_id' => $plant->id,
            'location_key' => 'curitiba',
            'current_humidity' => 55.00,
        ]);

        $this->assertSame(2, UserPlant::where('plant_id', $plant->id)->count());
    }
}
