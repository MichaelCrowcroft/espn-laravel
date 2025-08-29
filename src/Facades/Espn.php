<?php

namespace Espn\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Espn\Resources\AthletesResource athletes()
 * @method static \Espn\Resources\FantasyResource fantasy()
 */
class Espn extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'espn';
    }
}
