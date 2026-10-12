<?php

namespace App\Domain\Irrigation\Services\DecideIrrigation;

readonly class DecideIrrigationInput
{
    public function __construct(
        public float $currentHumidity,
        public float $minimumAllowedHumidity,
        public float $maximumAllowedHumidity,
        public bool $rainExpected,
    ) {
        if (
            $this->currentHumidity < 0
            || $this->currentHumidity > 100
            || $this->minimumAllowedHumidity < 0
            || $this->minimumAllowedHumidity > 100
            || $this->maximumAllowedHumidity < 0
            || $this->maximumAllowedHumidity > 100
            || $this->minimumAllowedHumidity > $this->maximumAllowedHumidity
        ) {
            throw new InvalidIrrigationConfiguration(
                'Os limites de umidade devem estar entre 0 e 100, com o mínimo menor ou igual ao máximo.',
            );
        }
    }
}
