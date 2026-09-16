<?php

namespace Tests\Integration\Application;

use App\Application\Events\UserPlantUpdated;
use App\Application\Listeners\HandleUserPlantUpdated;
use Tests\TestCase;

class HandleUserPlantUpdatedTest extends TestCase
{
    public function test_listener_esta_registrado(): void
    {
        $listeners = app('events')->getListeners(UserPlantUpdated::class);

        $this->assertNotEmpty($listeners);
    }

    public function test_listener_executa_sem_erro(): void
    {
        $listener = new HandleUserPlantUpdated;

        $listener->handle(new UserPlantUpdated(
            userPlantId: 1
        ));

        $this->assertTrue(true);
    }
}
