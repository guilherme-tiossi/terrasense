<?php

namespace App\Providers;

use App\Application\Events\UserPlantUpdated;
use App\Application\Listeners\HandleUserPlantUpdated;
use App\Infrastructure\Weather\FakeWeatherProvider;
use App\Infrastructure\Weather\WeatherApiProvider;
use App\Infrastructure\Weather\WeatherProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
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
