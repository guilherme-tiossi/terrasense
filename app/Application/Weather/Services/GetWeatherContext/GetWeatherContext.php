<?php

namespace App\Application\Weather\Services\GetWeatherContext;

use App\Infrastructure\Weather\WeatherProvider;
use Illuminate\Support\Facades\Redis;

class GetWeatherContext
{
    public function __construct(
        private readonly WeatherProvider $weatherProvider,
    ) {}

    public function execute(GetWeatherContextInputDto $input): GetWeatherContextOutputDto
    {
        $cached = Redis::get($this->cacheKey($input->locationKey));

        if (is_string($cached)) {
            $payload = json_decode($cached, true);

            if (
                is_array($payload)
                && isset($payload['temperature'], $payload['rain_expected'], $payload['fetched_at'])
                && is_numeric($payload['temperature'])
                && is_bool($payload['rain_expected'])
                && is_string($payload['fetched_at'])
            ) {
                return new GetWeatherContextOutputDto(
                    locationKey: $input->locationKey,
                    temperature: (float) $payload['temperature'],
                    rainExpected: $payload['rain_expected'],
                    fetchedAt: $payload['fetched_at'],
                );
            }
        }

        $weather = $this->weatherProvider->getWeather($input->locationKey);

        Redis::setex(
            $this->cacheKey($input->locationKey),
            config('terrasense.weather_cache_ttl_seconds'),
            json_encode([
                'temperature' => $weather->temperature,
                'rain_expected' => $weather->rainExpected,
                'fetched_at' => $weather->fetchedAt,
            ], JSON_THROW_ON_ERROR),
        );

        return new GetWeatherContextOutputDto(
            locationKey: $input->locationKey,
            temperature: $weather->temperature,
            rainExpected: $weather->rainExpected,
            fetchedAt: $weather->fetchedAt,
        );
    }

    public function cacheKey(string $locationKey): string
    {
        return "weather:{$locationKey}";
    }
}
