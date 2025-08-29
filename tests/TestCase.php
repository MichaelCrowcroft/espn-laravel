<?php

namespace Espn\Tests;

use Espn\EspnServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            EspnServiceProvider::class,
        ];
    }
}
