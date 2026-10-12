<?php

namespace App\Providers;

use App\Application\Irrigation\Listeners\HandleUserPlantUpdated;
use App\Application\Readings\Events\UserPlantUpdated;
use App\Infrastructure\Cache\CacheProvider;
use App\Infrastructure\Cache\RedisCacheProvider;
use App\Infrastructure\Weather\FakeWeatherProvider;
use App\Infrastructure\Weather\WeatherApiProvider;
use App\Infrastructure\Weather\WeatherProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CacheProvider::class, RedisCacheProvider::class);

        if ($this->app->environment('testing')) {
            $this->app->singleton(FakeWeatherProvider::class);
            $this->app->bind(WeatherProvider::class, FakeWeatherProvider::class);
        } else {
            $this->app->bind(WeatherProvider::class, WeatherApiProvider::class);
        }
    }

    public function boot(): void
    {
        Event::listen(UserPlantUpdated::class, HandleUserPlantUpdated::class);
    }
}
