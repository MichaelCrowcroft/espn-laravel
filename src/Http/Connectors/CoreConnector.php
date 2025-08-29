<?php

namespace Espn\Http\Connectors;

use Saloon\Http\Connector;

class CoreConnector extends Connector
{
    public function __construct(
        protected ?string $baseUrl = null,
        protected int $timeout = 30,
        protected int $connectTimeout = 10,
    ) {}

    public function resolveBaseUrl(): string
    {
        return rtrim($this->baseUrl ?? 'https://sports.core.api.espn.com/v3', '/');
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
