<?php

namespace Tests\Integration\Infrastructure;

use App\Infrastructure\Weather\WeatherApiProvider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class WeatherApiProviderTest extends TestCase
{
    public function test_consulta_weatherapi_e_mapeia_previsao(): void
    {
        Http::fake([
            'https://api.weatherapi.com/v1/forecast.json*' => Http::response([
                'current' => ['temp_c' => 18.9],
                'forecast' => [
                    'forecastday' => [[
                        'day' => [
                            'daily_will_it_rain' => 0,
                            'daily_chance_of_rain' => 60,
                        ],
                    ]],
                ],
            ]),
        ]);

        $weather = app(WeatherApiProvider::class)->getWeather('São Paulo');

        $this->assertSame(18.9, $weather->temperature);
        $this->assertTrue($weather->rainExpected);
        $this->assertNotEmpty($weather->fetchedAt);

        Http::assertSent(function (Request $request): bool {
            return str_starts_with($request->url(), 'https://api.weatherapi.com/v1/forecast.json?')
                && $request['q'] === 'São Paulo'
                && $request['days'] === 1
                && $request['aqi'] === 'no'
                && $request['alerts'] === 'no';
        });
    }

    public function test_rejeita_payload_invalido_da_weatherapi(): void
    {
        Http::fake([
            'https://api.weatherapi.com/v1/forecast.json*' => Http::response([
                'current' => ['temp_c' => 'invalid'],
                'forecast' => [],
            ]),
        ]);

        $this->expectException(RuntimeException::class);

        app(WeatherApiProvider::class)->getWeather('São Paulo');
    }
}
