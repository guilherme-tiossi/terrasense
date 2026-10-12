<?php

namespace Tests\Integration\Application;

use App\Application\Irrigation\Events\IrrigationDecisionMade;
use App\Application\Irrigation\Listeners\HandleUserPlantUpdated;
use App\Application\Readings\Events\UserPlantUpdated;
use App\Domain\Irrigation\Enums\IrrigationDecision;
use App\Infrastructure\Models\Plant;
use App\Infrastructure\Models\User;
use App\Infrastructure\Models\UserPlant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class HandleUserPlantUpdatedTest extends TestCase
{
    use RefreshDatabase;

    public function test_listener_esta_registrado(): void
    {
        $listeners = app('events')->getListeners(UserPlantUpdated::class);

        $this->assertNotEmpty($listeners);
    }

    public function test_listener_chama_fluxo_de_decisao(): void
    {
        Event::fake([IrrigationDecisionMade::class]);

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
            'current_humidity' => 20.00,
        ]);

        Redis::del('weather:sao_paulo');
        $this->configureFakeWeather(20.0, true);

        app(HandleUserPlantUpdated::class)->handle(new UserPlantUpdated(
            userPlantId: $userPlant->id,
        ));

        Event::assertDispatched(IrrigationDecisionMade::class, function (IrrigationDecisionMade $event) use ($userPlant): bool {
            return $event->userPlantId === $userPlant->id
                && $event->decision === IrrigationDecision::Wait;
        });
    }
}
