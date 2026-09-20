<?php

namespace App\Infrastructure\Weather;

interface WeatherProvider
{
    public function getWeather(string $locationKey): WeatherData;
}
