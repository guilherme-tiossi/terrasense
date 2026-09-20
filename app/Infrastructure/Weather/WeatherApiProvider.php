<?php

namespace App\Infrastructure\Weather;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WeatherApiProvider implements WeatherProvider
{
    public function getWeather(string $locationKey): WeatherData
    {
        $response = $this->request()
            ->get(config('services.weatherapi.forecast_url'), [
                'key' => config('services.weatherapi.key'),
                'q' => $locationKey,
                'days' => 1,
                'aqi' => 'no',
                'alerts' => 'no',
            ])
            ->throw();

        $payload = $response->json();
        $forecast = data_get($payload, 'forecast.forecastday.0.day');
        $temperature = data_get($payload, 'current.temp_c');

        if (! is_array($forecast) || ! is_numeric($temperature)) {
            throw new RuntimeException('Resposta inválida da WeatherAPI.');
        }

        return new WeatherData(
            temperature: (float) $temperature,
            rainExpected: (bool) ($forecast['daily_will_it_rain'] ?? false)
                || (int) ($forecast['daily_chance_of_rain'] ?? 0) > 0,
            fetchedAt: now()->toIso8601String(),
        );
    }

    private function request(): PendingRequest
    {
        return Http::acceptJson()->timeout(config('services.weatherapi.timeout_seconds'));
    }
}
