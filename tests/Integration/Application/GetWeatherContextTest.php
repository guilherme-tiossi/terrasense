<?php

namespace Tests\Integration\Application;

use App\Application\Weather\Services\GetWeatherContext\GetWeatherContext;
use App\Application\Weather\Services\GetWeatherContext\GetWeatherContextInputDto;
use App\Infrastructure\Weather\FakeWeatherProvider;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class GetWeatherContextTest extends TestCase
{
    private GetWeatherContext $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(GetWeatherContext::class);
        Redis::del('weather:sao_paulo');
    }

    public function test_armazena_contexto_meteorologico_no_redis(): void
    {
        $this->configureFakeWeather(24.5, true, '2026-09-15T18:00:00Z');

        $output = $this->service->execute(new GetWeatherContextInputDto(
            locationKey: 'sao_paulo',
        ));

        $this->assertSame('sao_paulo', $output->locationKey);
        $this->assertSame(24.5, $output->temperature);
        $this->assertTrue($output->rainExpected);
        $this->assertSame('2026-09-15T18:00:00Z', $output->fetchedAt);

        $cached = json_decode(Redis::get('weather:sao_paulo'), true);

        $this->assertSame(24.5, $cached['temperature']);
        $this->assertTrue($cached['rain_expected']);
        $this->assertSame('2026-09-15T18:00:00Z', $cached['fetched_at']);
        $this->assertGreaterThan(0, Redis::ttl('weather:sao_paulo'));
        $this->assertSame(1, app(FakeWeatherProvider::class)->calls());
    }

    public function test_retorna_contexto_do_redis_sem_consultar_provider(): void
    {
        $this->configureFakeWeather(30.0, false, 'provider-time');
        Redis::setex('weather:sao_paulo', 1800, json_encode([
            'temperature' => 18.9,
            'rain_expected' => true,
            'fetched_at' => 'cache-time',
        ], JSON_THROW_ON_ERROR));

        $output = $this->service->execute(new GetWeatherContextInputDto(
            locationKey: 'sao_paulo',
        ));

        $this->assertSame(18.9, $output->temperature);
        $this->assertTrue($output->rainExpected);
        $this->assertSame('cache-time', $output->fetchedAt);
        $this->assertSame(0, app(FakeWeatherProvider::class)->calls());
    }

    public function test_ignora_cache_malformado_e_consulta_provider(): void
    {
        $this->configureFakeWeather(21.5, false, 'provider-time');
        Redis::set('weather:sao_paulo', json_encode([
            'temperature' => 'invalid',
        ], JSON_THROW_ON_ERROR));

        $output = $this->service->execute(new GetWeatherContextInputDto(
            locationKey: 'sao_paulo',
        ));

        $this->assertSame(21.5, $output->temperature);
        $this->assertSame(1, app(FakeWeatherProvider::class)->calls());
    }
}
