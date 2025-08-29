<?php

namespace Espn\Http\Requests\Athletes;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetAthletes extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected int $page = 1,
        protected int $limit = 20000,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/sports/football/nfl/athletes';
    }

    protected function defaultQuery(): array
    {
        return [
            'page' => $this->page,
            'limit' => $this->limit,
        ];
    }
}
