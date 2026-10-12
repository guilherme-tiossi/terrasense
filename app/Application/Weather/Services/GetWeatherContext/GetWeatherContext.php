<?php

namespace App\Application\Weather\Services\GetWeatherContext;

use App\Infrastructure\Cache\CacheProvider;
use App\Infrastructure\Weather\WeatherData;
use App\Infrastructure\Weather\WeatherProvider;

class GetWeatherContext
{
    public function __construct(
        private readonly WeatherProvider $weatherProvider,
        private readonly CacheProvider $cacheProvider,
    ) {}

    public function execute(GetWeatherContextInputDto $input): GetWeatherContextOutputDto
    {
        $cached = $this->cacheProvider->get($this->cacheKey($input->locationKey));

        if ($cached !== null && ($weather = $this->decodeWeather($cached)) !== null) {
            return new GetWeatherContextOutputDto(
                locationKey: $input->locationKey,
                temperature: $weather->temperature,
                rainExpected: $weather->rainExpected,
                fetchedAt: $weather->fetchedAt,
            );
        }

        $weather = $this->weatherProvider->getWeather($input->locationKey);
        $this->cacheProvider->put(
            key: $this->cacheKey($input->locationKey),
            value: json_encode([
                'temperature' => $weather->temperature,
                'rain_expected' => $weather->rainExpected,
                'fetched_at' => $weather->fetchedAt,
            ], JSON_THROW_ON_ERROR),
            ttlSeconds: (int) config('terrasense.weather_cache_ttl_seconds'),
        );

        return new GetWeatherContextOutputDto(
            locationKey: $input->locationKey,
            temperature: $weather->temperature,
            rainExpected: $weather->rainExpected,
            fetchedAt: $weather->fetchedAt,
        );
    }

    private function cacheKey(string $locationKey): string
    {
        return "weather:{$locationKey}";
    }

    private function decodeWeather(string $cached): ?WeatherData
    {
        $payload = json_decode($cached, true);

        if (
            ! is_array($payload)
            || ! isset($payload['temperature'], $payload['rain_expected'], $payload['fetched_at'])
            || ! is_numeric($payload['temperature'])
            || ! is_bool($payload['rain_expected'])
            || ! is_string($payload['fetched_at'])
        ) {
            return null;
        }

        return new WeatherData(
            temperature: (float) $payload['temperature'],
            rainExpected: $payload['rain_expected'],
            fetchedAt: $payload['fetched_at'],
        );
    }
}
