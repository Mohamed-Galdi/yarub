<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        // Never load a developer's real integrations or database credentials in tests.
        $app->loadEnvironmentFrom('.env.auth-testing');
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
