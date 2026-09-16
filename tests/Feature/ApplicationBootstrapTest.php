<?php

namespace Tests\Feature;

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
}
