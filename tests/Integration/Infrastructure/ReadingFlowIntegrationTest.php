<?php

namespace Tests\Integration\Infrastructure;

use App\Application\Events\IrrigationDecisionMade;
use App\Domain\Irrigation\Enums\IrrigationDecision;
use App\Infrastructure\Models\Plant;
use App\Infrastructure\Models\User;
use App\Infrastructure\Models\UserPlant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class ReadingFlowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fluxo_completo_gera_decisao_de_irrigacao(): void
    {
        Event::fake([IrrigationDecisionMade::class]);

        $user = User::factory()->create(['hmac_secret' => 'flow-secret']);
        $plant = Plant::create([
            'name' => 'Tomate',
            'minimum_allowed_humidity' => 30.00,
            'maximum_allowed_humidity' => 70.00,
        ]);
        $userPlant = UserPlant::create([
            'user_id' => $user->id,
            'plant_id' => $plant->id,
            'location_key' => 'sao_paulo',
            'current_humidity' => 50.00,
        ]);

        Redis::del('weather:sao_paulo');
        $this->configureFakeWeather(18.0, false);

        $payload = [
            'user_plant_id' => $userPlant->id,
            'humidity' => 25.0,
            'measured_at' => '2026-09-15T18:00:00Z',
        ];
        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        $response = $this->call(
            'POST',
            '/api/readings',
            [],
            [],
            [],
            $this->transformHeadersToServerVars(
                $this->hmacHeaders($user, 'POST', '/api/readings', $body),
            ),
            $body,
        );

        $response->assertOk();
        $this->assertDatabaseHas('user_plants', [
            'id' => $userPlant->id,
            'current_humidity' => '25.00',
        ]);
        $this->assertNotNull(Redis::get('weather:sao_paulo'));

        Event::assertDispatched(IrrigationDecisionMade::class, function (IrrigationDecisionMade $event) use ($userPlant): bool {
            return $event->userPlantId === $userPlant->id
                && $event->decision === IrrigationDecision::Irrigate
                && $event->currentHumidity === 25.0
                && $event->locationKey === 'sao_paulo';
        });
    }
}
