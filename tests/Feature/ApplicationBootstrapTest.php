<?php

namespace Tests\Feature;

use App\Infrastructure\Weather\WeatherApiProvider;
use App\Infrastructure\Weather\WeatherProvider;
use App\Providers\AppServiceProvider;
use Tests\TestCase;

class ApplicationBootstrapTest extends TestCase
{
    public function test_health_endpoint_responds_successfully(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
    }

    public function test_hexagonal_directories_exist(): void
    {
        $this->assertDirectoryExists(app_path('Domain'));
        $this->assertDirectoryExists(app_path('Application'));
        $this->assertDirectoryExists(app_path('Infrastructure'));
    }

    public function test_production_usa_provider_de_clima_real(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');

        try {
            (new AppServiceProvider($this->app))->register();

            $this->assertInstanceOf(WeatherApiProvider::class, app(WeatherProvider::class));
        } finally {
            $this->app->detectEnvironment(fn (): string => 'testing');
            (new AppServiceProvider($this->app))->register();
        }
    }
}
