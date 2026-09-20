<?php

namespace App\Infrastructure\Weather;

class FakeWeatherProvider implements WeatherProvider
{
    private ?WeatherData $weather = null;

    private int $calls = 0;

    public function setWeather(WeatherData $weather): void
    {
        $this->weather = $weather;
    }

    public function getWeather(string $locationKey): WeatherData
    {
        $this->calls++;

        return $this->weather ?? new WeatherData(
            temperature: 24.0,
            rainExpected: false,
            fetchedAt: now()->toIso8601String(),
        );
    }

    public function calls(): int
    {
        return $this->calls;
    }
}
