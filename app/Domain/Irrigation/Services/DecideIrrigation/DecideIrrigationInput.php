<?php

namespace App\Domain\Irrigation\Services\DecideIrrigation;

readonly class DecideIrrigationInput
{
    public function __construct(
        public float $currentHumidity,
        public float $minimumAllowedHumidity,
        public float $maximumAllowedHumidity,
        public bool $rainExpected,
    ) {}
}
