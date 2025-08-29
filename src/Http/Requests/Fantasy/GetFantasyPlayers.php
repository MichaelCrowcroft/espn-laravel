<?php

namespace Espn\Http\Requests\Fantasy;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetFantasyPlayers extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param array<string,mixed>|null $filter Will be JSON encoded into X-Fantasy-Filter header
     */
    public function __construct(
        protected int $year,
        protected string $view,
        protected ?array $filter = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/games/ffl/seasons/{$this->year}/players";
    }

    protected function defaultQuery(): array
    {
        return [
            'view' => $this->view,
        ];
    }

    protected function defaultHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
        ];

        if (!empty($this->filter)) {
            $headers['X-Fantasy-Filter'] = json_encode($this->filter, JSON_THROW_ON_ERROR);
        }

        return $headers;
    }
}
