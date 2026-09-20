<?php

namespace App\Domain\Irrigation\Services\DecideIrrigation;

use App\Domain\Irrigation\Enums\IrrigationDecision;

class DecideIrrigation
{
    public function execute(DecideIrrigationInput $input): IrrigationDecision
    {
        if ($input->currentHumidity >= $input->minimumAllowedHumidity
            && $input->currentHumidity <= $input->maximumAllowedHumidity) {
            return IrrigationDecision::NoAction;
        }

        if ($input->currentHumidity < $input->minimumAllowedHumidity) {
            return $input->rainExpected
                ? IrrigationDecision::Wait
                : IrrigationDecision::Irrigate;
        }

        return IrrigationDecision::NoAction;
    }
}
