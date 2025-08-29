<?php

namespace Espn\Resources;

use Espn\Http\Connectors\CoreConnector;
use Espn\Http\Requests\Athletes\GetAthleteById;
use Espn\Http\Requests\Athletes\GetAthletes;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class AthletesResource extends BaseResource
{
    public function __construct(protected CoreConnector $core)
    {
        parent::__construct($this->core);
    }

    public function page(int $page = 1, int $limit = 20000): Response
    {
        return $this->core->send(new GetAthletes(page: $page, limit: $limit));
    }

    public function get(int|string $athleteId): Response
    {
        return $this->core->send(new GetAthleteById($athleteId));
    }
}
