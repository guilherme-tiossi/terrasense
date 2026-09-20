<?php

namespace Tests\Unit\Domain\Irrigation\Services\DecideIrrigation;

use App\Domain\Irrigation\Enums\IrrigationDecision;
use App\Domain\Irrigation\Services\DecideIrrigation\DecideIrrigation;
use App\Domain\Irrigation\Services\DecideIrrigation\DecideIrrigationInput;
use PHPUnit\Framework\TestCase;

class DecideIrrigationTest extends TestCase
{
    private DecideIrrigation $decideIrrigation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->decideIrrigation = new DecideIrrigation;
    }

    public function test_umidade_dentro_da_faixa_retorna_no_action(): void
    {
        $decision = $this->decideIrrigation->execute(new DecideIrrigationInput(45, 30, 70, true));

        $this->assertSame(IrrigationDecision::NoAction, $decision);
    }

    public function test_umidade_no_limite_minimo_retorna_no_action(): void
    {
        $decision = $this->decideIrrigation->execute(new DecideIrrigationInput(30, 30, 70, false));

        $this->assertSame(IrrigationDecision::NoAction, $decision);
    }

    public function test_umidade_no_limite_maximo_retorna_no_action(): void
    {
        $decision = $this->decideIrrigation->execute(new DecideIrrigationInput(70, 30, 70, false));

        $this->assertSame(IrrigationDecision::NoAction, $decision);
    }

    public function test_umidade_baixa_com_chuva_prevista_retorna_wait(): void
    {
        $decision = $this->decideIrrigation->execute(new DecideIrrigationInput(25, 30, 70, true));

        $this->assertSame(IrrigationDecision::Wait, $decision);
    }

    public function test_umidade_baixa_sem_chuva_prevista_retorna_irrigate(): void
    {
        $decision = $this->decideIrrigation->execute(new DecideIrrigationInput(25, 30, 70, false));

        $this->assertSame(IrrigationDecision::Irrigate, $decision);
    }

    public function test_umidade_acima_da_faixa_retorna_no_action(): void
    {
        $decision = $this->decideIrrigation->execute(new DecideIrrigationInput(75, 30, 70, false));

        $this->assertSame(IrrigationDecision::NoAction, $decision);
    }
}
