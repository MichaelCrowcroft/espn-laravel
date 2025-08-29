<?php

namespace Espn\Resources;

use Espn\Http\Connectors\FantasyConnector;
use Espn\Http\Requests\Fantasy\GetFantasyPlayers;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class FantasyResource extends BaseResource
{
    public function __construct(
        FantasyConnector $connector,
        protected int $defaultLimit = 2000,
    ) {
        parent::__construct($connector);
    }

    public function players(int $year, string $view, ?array $filter = null): Response
    {
        return $this->connector->send(new GetFantasyPlayers(year: $year, view: $view, filter: $filter));
    }

    public function playersWithLimit(int $year, string $view, ?int $limit = null): Response
    {
        $limit = $limit ?? $this->defaultLimit;

        return $this->players($year, $view, [
            'games' => [
                'limit' => $limit,
            ],
        ]);
    }

    // Convenience methods for common views
    public function konaPlayerInfo(int $year, ?int $limit = null): Response
    {
        return $this->playersWithLimit($year, 'kona_player_info', $limit);
    }

    public function mLiveScoring(int $year, ?int $limit = null): Response
    {
        return $this->playersWithLimit($year, 'mLiveScoring', $limit);
    }

    public function mMatchup(int $year, ?int $limit = null): Response
    {
        return $this->playersWithLimit($year, 'mMatchup', $limit);
    }

    public function mTeam(int $year, ?int $limit = null): Response
    {
        return $this->playersWithLimit($year, 'mTeam', $limit);
    }

    public function allOn(int $year, ?int $limit = null): Response
    {
        return $this->playersWithLimit($year, 'allon', $limit);
    }

    /**
     * Start a fluent players query for a given year.
     */
    public function query(int $year): FantasyPlayersQuery
    {
        return new FantasyPlayersQuery(
            connector: $this->connector,
            year: $year,
            defaultLimit: $this->defaultLimit,
        );
    }

    /**
     * Convenience helpers that return summarized arrays for common needs.
     * These delegate to the fluent query for ergonomics.
     */
    public function topScorers(int $year, ?int $week = null, int $take = 25, ?int $limit = null): array
    {
        return $this->query($year)
            ->limit($limit ?? $this->defaultLimit)
            ->scoringPeriod($week)
            ->topScorers($take);
    }

    public function topProjected(int $year, ?int $week = null, int $take = 25, ?int $limit = null): array
    {
        return $this->query($year)
            ->limit($limit ?? $this->defaultLimit)
            ->scoringPeriod($week)
            ->topProjected($take);
    }

    public function trendingAdds(int $year, int $take = 25, ?int $limit = null): array
    {
        return $this->query($year)
            ->limit($limit ?? $this->defaultLimit)
            ->trendingAdds($take);
    }

    public function trendingDrops(int $year, int $take = 25, ?int $limit = null): array
    {
        return $this->query($year)
            ->limit($limit ?? $this->defaultLimit)
            ->trendingDrops($take);
    }
}
