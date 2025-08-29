<?php

use Espn\Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Pest configuration
|--------------------------------------------------------------------------
|
| Bind the package TestCase to all Feature tests. Unit tests don't need
| Laravel's container, so they run without the TestCase by default.
|
*/

uses(TestCase::class)->in('Feature');
