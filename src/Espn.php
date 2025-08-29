<?php

namespace MichaelCrowcroft\EspnLaravel;

use MichaelCrowcroft\EspnLaravel\Http\Connectors\CoreConnector;
use MichaelCrowcroft\EspnLaravel\Http\Connectors\FantasyConnector;
use MichaelCrowcroft\EspnLaravel\Resources\AthletesResource;
use MichaelCrowcroft\EspnLaravel\Resources\FantasyResource;

class Espn
{
    protected array $config;

    protected CoreConnector $core;
    protected FantasyConnector $fantasy;

    public function __construct(array $config)
    {
        $this->config = $config;

        $this->core = new CoreConnector(
            baseUrl: $config['core']['base_url'] ?? null,
            timeout: $config['timeout'] ?? 30,
            connectTimeout: $config['connect_timeout'] ?? 10,
        );

        $this->fantasy = new FantasyConnector(
            baseUrl: $config['fantasy']['base_url'] ?? null,
            timeout: $config['timeout'] ?? 30,
            connectTimeout: $config['connect_timeout'] ?? 10,
        );
    }

    public function athletes(): AthletesResource
    {
        return new AthletesResource($this->core);
    }

    public function fantasy(): FantasyResource
    {
        return new FantasyResource($this->fantasy, (int)($this->config['fantasy']['default_limit'] ?? 2000));
    }
}
