<?php

namespace MichaelCrowcroft\EspnLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \MichaelCrowcroft\EspnLaravel\Resources\AthletesResource athletes()
 * @method static \MichaelCrowcroft\EspnLaravel\Resources\FantasyResource fantasy()
 */
class Espn extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'espn';
    }
}
