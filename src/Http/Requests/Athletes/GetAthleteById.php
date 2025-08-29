<?php

namespace MichaelCrowcroft\EspnLaravel\Http\Requests\Athletes;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetAthleteById extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected int|string $athleteId,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/sports/football/nfl/athletes/{$this->athleteId}";
    }
}
