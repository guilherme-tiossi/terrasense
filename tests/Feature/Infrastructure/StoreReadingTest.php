<?php

namespace Tests\Feature\Infrastructure;

use App\Application\Events\UserPlantUpdated;
use App\Infrastructure\Models\Plant;
use App\Infrastructure\Models\User;
use App\Infrastructure\Models\UserPlant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class StoreReadingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private UserPlant $userPlant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['hmac_secret' => 'store-reading-secret']);

        $plant = Plant::create([
            'name' => 'Tomate',
            'minimum_allowed_humidity' => 30.00,
            'maximum_allowed_humidity' => 70.00,
        ]);

        $this->userPlant = UserPlant::create([
            'user_id' => $this->user->id,
            'plant_id' => $plant->id,
            'location_key' => 'sao_paulo',
            'current_humidity' => 50.00,
        ]);
    }

    public function test_atualiza_user_plant_com_payload_valido_e_autenticacao_hmac(): void
    {
        Event::fake([UserPlantUpdated::class]);

        $payload = [
            'user_plant_id' => $this->userPlant->id,
            'humidity' => 35.5,
            'measured_at' => '2026-09-15T18:00:00Z',
        ];

        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        $response = $this->call(
            'POST',
            '/api/readings',
            [],
            [],
            [],
            $this->transformHeadersToServerVars($this->hmacHeaders($this->user, 'POST', '/api/readings', $body)),
            $body,
        );

        $response->assertOk()
            ->assertJson([
                'user_plant_id' => $this->userPlant->id,
                'current_humidity' => 35.5,
                'measured_at' => '2026-09-15T18:00:00Z',
            ]);

        $this->assertDatabaseHas('user_plants', [
            'id' => $this->userPlant->id,
            'current_humidity' => '35.50',
        ]);

        Event::assertDispatched(UserPlantUpdated::class);
    }

    public function test_rejeita_payload_invalido(): void
    {
        $payload = [
            'user_plant_id' => $this->userPlant->id,
            'humidity' => 150,
            'measured_at' => 'invalid-date',
        ];

        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        $response = $this->call(
            'POST',
            '/api/readings',
            [],
            [],
            [],
            $this->transformHeadersToServerVars($this->hmacHeaders($this->user, 'POST', '/api/readings', $body)),
            $body,
        );

        $response->assertUnprocessable();
    }

    public function test_retorna_404_quando_user_plant_nao_pertence_ao_usuario(): void
    {
        $otherUser = User::factory()->create(['hmac_secret' => 'other-secret']);

        $payload = [
            'user_plant_id' => $this->userPlant->id,
            'humidity' => 35.5,
            'measured_at' => '2026-09-15T18:00:00Z',
        ];

        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        $response = $this->call(
            'POST',
            '/api/readings',
            [],
            [],
            [],
            $this->transformHeadersToServerVars($this->hmacHeaders($otherUser, 'POST', '/api/readings', $body)),
            $body,
        );

        $response->assertNotFound();
    }
}
