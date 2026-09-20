<?php

namespace Tests\Integration\Application;

use App\Application\Events\IrrigationDecisionMade;
use App\Application\UseCases\ProcessIrrigationDecision\ProcessIrrigationDecision;
use App\Application\UseCases\ProcessIrrigationDecision\ProcessIrrigationDecisionInputDto;
use App\Domain\Irrigation\Enums\IrrigationDecision;
use App\Infrastructure\Models\Plant;
use App\Infrastructure\Models\User;
use App\Infrastructure\Models\UserPlant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class ProcessIrrigationDecisionTest extends TestCase
{
    use RefreshDatabase;

    private UserPlant $userPlant;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $plant = Plant::create([
            'name' => 'Tomate',
            'minimum_allowed_humidity' => 30.00,
            'maximum_allowed_humidity' => 70.00,
        ]);

        $this->userPlant = UserPlant::create([
            'user_id' => $user->id,
            'plant_id' => $plant->id,
            'location_key' => 'sao_paulo',
            'current_humidity' => 25.00,
        ]);

        Redis::del('weather:sao_paulo');
    }

    public function test_umidade_baixa_com_chuva_prevista_retorna_wait(): void
    {
        Event::fake([IrrigationDecisionMade::class]);
        $this->configureFakeWeather(22.0, true);

        $output = app(ProcessIrrigationDecision::class)->execute(
            new ProcessIrrigationDecisionInputDto($this->userPlant->id),
        );

        $this->assertSame(IrrigationDecision::Wait, $output->decision);

        Event::assertDispatched(IrrigationDecisionMade::class, function (IrrigationDecisionMade $event): bool {
            return $event->userPlantId === $this->userPlant->id
                && $event->decision === IrrigationDecision::Wait
                && $event->rainExpected === true;
        });
    }

    public function test_umidade_baixa_sem_chuva_retorna_irrigate(): void
    {
        Event::fake([IrrigationDecisionMade::class]);
        $this->configureFakeWeather(22.0, false);

        $output = app(ProcessIrrigationDecision::class)->execute(
            new ProcessIrrigationDecisionInputDto($this->userPlant->id),
        );

        $this->assertSame(IrrigationDecision::Irrigate, $output->decision);
    }

    public function test_umidade_adequada_retorna_no_action(): void
    {
        Event::fake([IrrigationDecisionMade::class]);
        $this->userPlant->update(['current_humidity' => 45.00]);
        $this->configureFakeWeather(22.0, false);

        $output = app(ProcessIrrigationDecision::class)->execute(
            new ProcessIrrigationDecisionInputDto($this->userPlant->id),
        );

        $this->assertSame(IrrigationDecision::NoAction, $output->decision);
    }
}
