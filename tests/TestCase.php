<?php

namespace MichaelCrowcroft\EspnLaravel\Tests;

use MichaelCrowcroft\EspnLaravel\EspnServiceProvider;
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
