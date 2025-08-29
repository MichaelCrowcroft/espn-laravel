<?php

namespace MichaelCrowcroft\EspnLaravel\Http\Connectors;

use Saloon\Http\Connector;

class FantasyConnector extends Connector
{
    public function __construct(
        protected ?string $baseUrl = null,
        protected int $timeout = 30,
        protected int $connectTimeout = 10,
    ) {}

    public function resolveBaseUrl(): string
    {
        return rtrim($this->baseUrl ?? 'https://lm-api-reads.fantasy.espn.com/apis/v3', '/');
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function defaultConfig(): array
    {
        return [
            'timeout' => $this->timeout,
            'connect_timeout' => $this->connectTimeout,
        ];
    }
}
