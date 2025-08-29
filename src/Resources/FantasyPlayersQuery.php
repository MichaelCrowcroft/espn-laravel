<?php

namespace Espn\Resources;

use Espn\Http\Connectors\FantasyConnector;
use Espn\Http\Requests\Fantasy\GetFantasyPlayers;
use Saloon\Http\Response;

/**
 * Fluent builder for querying Fantasy players with helpful shortcuts
 * for common website use-cases (top scorers, projections, trending).
 */
class FantasyPlayersQuery
{
    public function __construct(
        protected FantasyConnector $connector,
        protected int $year,
        protected int $defaultLimit = 2000,
    ) {}

    // Query state
    protected string $view = 'kona_player_info';
    protected ?int $limit = null;
    protected ?int $scoringPeriodId = null;
    protected ?array $positions = null; // for post-filtering convenience
    protected ?array $extraFilter = null; // raw filter passthrough

    // Fluent configuration
    public function view(string $view): self
    {
        $this->view = $view;
        return $this;
    }

    public function viewKona(): self
    {
        return $this->view('kona_player_info');
    }

    public function viewLive(): self
    {
        return $this->view('mLiveScoring');
    }

    public function viewAllOn(): self
    {
        return $this->view('allon');
    }

    public function limit(?int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function scoringPeriod(?int $scoringPeriodId): self
    {
        $this->scoringPeriodId = $scoringPeriodId;
        return $this;
    }

    /**
     * Provide raw X-Fantasy-Filter values (merged into defaults).
     * @param array<string,mixed> $filter
     */
    public function filter(array $filter): self
    {
        $this->extraFilter = $filter;
        return $this;
    }

    /**
     * Capture preferred positions for local post-filtering convenience.
     * Accepts an array of position abbreviations like ["QB","RB","WR","TE"].
     * @param array<int,string>|string $positions
     */
    public function positions(array|string $positions): self
    {
        $this->positions = is_array($positions) ? $positions : [$positions];
        return $this;
    }

    /**
     * Build an ESPN X-Fantasy-Filter array based on configured options.
     * Note: ESPN's filter schema is loosely documented and can vary by view.
     * We conservatively set only safe, widely supported keys.
     * @return array<string,mixed>
     */
    protected function buildFilter(): array
    {
        $filter = $this->extraFilter ?? [];

        $limit = $this->limit ?? $this->defaultLimit;
        if ($limit > 0) {
            $filter['games']['limit'] = $limit;
        }

        // Scoring period can be respected by certain views in the stats array;
        // include as a hint for services that support it.
        if ($this->scoringPeriodId !== null) {
            $filter['scoringPeriodId'] = $this->scoringPeriodId;
        }

        return $filter;
    }

    /**
     * Execute the request and get the raw Saloon response.
     */
    public function get(): Response
    {
        $filter = $this->buildFilter();

        return $this->connector->send(new GetFantasyPlayers(
            year: $this->year,
            view: $this->view,
            filter: empty($filter) ? null : $filter,
        ));
    }

    /**
     * Derived helpers
     * These helpers return simplified arrays, suitable for directly rendering in UIs.
     */

    /**
     * Get top actual scorers for the configured week (or best available).
     * @return array<int,array<string,mixed>>
     */
    public function topScorers(int $take = 25): array
    {
        $response = $this->view === 'mLiveScoring' ? $this->get() : (clone $this)->viewLive()->get();
        $players = $this->extractPlayers($response->json());

        $ranked = [];
        foreach ($players as $p) {
            $points = $this->extractPoints($p, projected: false, preferPeriod: $this->scoringPeriodId);
            if ($points === null) {
                continue;
            }
            if ($this->positions && !$this->matchesPositions($p, $this->positions)) {
                continue;
            }
            $ranked[] = $this->summarizePlayer($p, points: $points);
        }

        usort($ranked, fn ($a, $b) => ($b['points'] <=> $a['points']));
        return array_slice($ranked, 0, $take);
    }

    /**
     * Get top projected scorers for the configured week (or best available projection).
     * @return array<int,array<string,mixed>>
     */
    public function topProjected(int $take = 25): array
    {
        $response = $this->view === 'kona_player_info' ? $this->get() : (clone $this)->viewKona()->get();
        $players = $this->extractPlayers($response->json());

        $ranked = [];
        foreach ($players as $p) {
            $points = $this->extractPoints($p, projected: true, preferPeriod: $this->scoringPeriodId);
            if ($points === null) {
                continue;
            }
            if ($this->positions && !$this->matchesPositions($p, $this->positions)) {
                continue;
            }
            $ranked[] = $this->summarizePlayer($p, projected: $points);
        }

        usort($ranked, fn ($a, $b) => ($b['projected'] <=> $a['projected']));
        return array_slice($ranked, 0, $take);
    }

    /**
     * Players trending up by ownership change (adds).
     * @return array<int,array<string,mixed>>
     */
    public function trendingAdds(int $take = 25): array
    {
        $response = $this->view === 'kona_player_info' ? $this->get() : (clone $this)->viewKona()->get();
        $players = $this->extractPlayers($response->json());

        $ranked = [];
        foreach ($players as $p) {
            $delta = $this->extractOwnershipChange($p);
            if ($delta === null || $delta <= 0) {
                continue;
            }
            if ($this->positions && !$this->matchesPositions($p, $this->positions)) {
                continue;
            }
            $ranked[] = $this->summarizePlayer($p, ownershipChange: $delta);
        }

        usort($ranked, fn ($a, $b) => ($b['ownership_change'] <=> $a['ownership_change']));
        return array_slice($ranked, 0, $take);
    }

    /**
     * Players trending down by ownership change (drops).
     * @return array<int,array<string,mixed>>
     */
    public function trendingDrops(int $take = 25): array
    {
        $response = $this->view === 'kona_player_info' ? $this->get() : (clone $this)->viewKona()->get();
        $players = $this->extractPlayers($response->json());

        $ranked = [];
        foreach ($players as $p) {
            $delta = $this->extractOwnershipChange($p);
            if ($delta === null || $delta >= 0) {
                continue;
            }
            if ($this->positions && !$this->matchesPositions($p, $this->positions)) {
                continue;
            }
            $ranked[] = $this->summarizePlayer($p, ownershipChange: $delta);
        }

        usort($ranked, fn ($a, $b) => ($a['ownership_change'] <=> $b['ownership_change'])); // most negative first
        return array_slice($ranked, 0, $take);
    }

    // -----------------
    // Extraction helpers
    // -----------------

    /**
     * Normalize the players list from the ESPN response payloads.
     * @param mixed $json
     * @return array<int,array<string,mixed>>
     */
    protected function extractPlayers(mixed $json): array
    {
        if (is_array($json)) {
            // Some responses return directly a list of players, others under a 'players' key.
            if (isset($json['players']) && is_array($json['players'])) {
                return array_values(array_filter($json['players'], 'is_array'));
            }
            // If the array is a list, assume it's already the players list
            $isList = array_is_list($json);
            if ($isList) {
                return array_values(array_filter($json, 'is_array'));
            }
        }

        return [];
    }

    /**
     * Attempt to pull points from various stat shapes.
     */
    protected function extractPoints(array $player, bool $projected = false, ?int $preferPeriod = null): ?float
    {
        $stats = $player['stats'] ?? $player['player']['stats'] ?? null;
        if (!is_array($stats)) {
            return null;
        }

        $best = null;
        foreach ($stats as $item) {
            if (!is_array($item)) {
                continue;
            }

            // ESPN convention: statSourceId 1 = actual, 2 = projected
            if (isset($item['statSourceId'])) {
                if ($projected && (int)$item['statSourceId'] !== 2) continue;
                if (!$projected && (int)$item['statSourceId'] === 2) continue;
            }

            if ($preferPeriod !== null && isset($item['scoringPeriodId']) && (int)$item['scoringPeriodId'] !== $preferPeriod) {
                continue;
            }

            $val = null;
            foreach (['appliedTotal', 'appliedStatTotal', 'total', 'points', 'value'] as $key) {
                if (isset($item[$key]) && is_numeric($item[$key])) {
                    $val = (float)$item[$key];
                    break;
                }
            }

            if ($val !== null) {
                $best = max($best ?? $val, $val);
            }
        }

        return $best;
    }

    /**
     * Ownership change, if available, typically lives on a nested ownership node
     * with fields like percentChange/percentChange7day. Returns percentage delta.
     */
    protected function extractOwnershipChange(array $player): ?float
    {
        $ownership = $player['ownership'] ?? $player['player']['ownership'] ?? null;
        if (!is_array($ownership)) {
            return null;
        }

        foreach (['percentChange', 'percentChange7day', 'ownershipChange', 'percentOwnedDelta'] as $k) {
            if (isset($ownership[$k]) && is_numeric($ownership[$k])) {
                return (float)$ownership[$k];
            }
        }

        return null;
    }

    /**
     * Basic post-filter by positions using common abbrev fields.
     * Accepts array of strings like ["QB","RB",...].
     */
    protected function matchesPositions(array $player, array $positions): bool
    {
        $pos = null;
        // Try handful of common places
        if (isset($player['defaultPosition'])) {
            $pos = $player['defaultPosition'];
        } elseif (isset($player['position'])) {
            $pos = $player['position'];
        } elseif (isset($player['player']['defaultPosition'])) {
            $pos = $player['player']['defaultPosition'];
        } elseif (isset($player['player']['position'])) {
            $pos = $player['player']['position'];
        }

        if (!is_string($pos)) {
            return true; // cannot determine, don't exclude
        }

        return in_array(strtoupper($pos), array_map('strtoupper', $positions), true);
    }

    /**
     * Summarize a player into a small, UI-friendly array.
     * Keys: id, name, team, position, points, projected, ownership_change
     * Only populated keys are included.
     * @return array<string,mixed>
     */
    protected function summarizePlayer(
        array $player,
        ?float $points = null,
        ?float $projected = null,
        ?float $ownershipChange = null,
    ): array {
        // Id & name
        $id = $player['id'] ?? $player['player']['id'] ?? null;
        $name = $player['fullName'] ?? $player['name'] ?? $player['player']['fullName'] ?? $player['player']['name'] ?? null;

        // Team abbrev (best effort)
        $team = $player['team']['abbrev'] ?? $player['proTeam'] ?? $player['player']['team']['abbrev'] ?? null;
        // Position abbrev
        $position = $player['defaultPosition'] ?? $player['position'] ?? $player['player']['defaultPosition'] ?? $player['player']['position'] ?? null;

        $out = array_filter([
            'id' => $id,
            'name' => $name,
            'team' => $team,
            'position' => $position,
        ], fn ($v) => $v !== null && $v !== '');

        if ($points !== null) {
            $out['points'] = round($points, 2);
        }
        if ($projected !== null) {
            $out['projected'] = round($projected, 2);
        }
        if ($ownershipChange !== null) {
            $out['ownership_change'] = round($ownershipChange, 2);
        }

        return $out;
    }
}

