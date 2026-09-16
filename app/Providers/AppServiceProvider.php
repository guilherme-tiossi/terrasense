<?php

namespace App\Providers;

use App\Application\Events\UserPlantUpdated;
use App\Application\Listeners\HandleUserPlantUpdated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(UserPlantUpdated::class, HandleUserPlantUpdated::class);
    }
}
