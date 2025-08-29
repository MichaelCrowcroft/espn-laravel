<?php

use Espn\Http\Requests\Fantasy\GetFantasyPlayers;

it('builds fantasy players endpoint and query correctly', function () {
    $req = new GetFantasyPlayers(year: 2024, view: 'mLiveScoring');

    expect($req->resolveEndpoint())->toBe('/games/ffl/seasons/2024/players');

    $reflect = new ReflectionClass($req);
    $query = $reflect->getMethod('defaultQuery');
    $query->setAccessible(true);

    expect($query->invoke($req))->toBe([
        'view' => 'mLiveScoring',
    ]);
});

it('adds X-Fantasy-Filter header when filter provided', function () {
    $filter = ['games' => ['limit' => 1234]];
    $req = new GetFantasyPlayers(year: 2024, view: 'allon', filter: $filter);

    $reflect = new ReflectionClass($req);
    $headers = $reflect->getMethod('defaultHeaders');
    $headers->setAccessible(true);

    $result = $headers->invoke($req);

    expect($result)
        ->toHaveKeys(['Accept', 'X-Fantasy-Filter'])
        ->and($result['Accept'])->toBe('application/json')
        ->and($result['X-Fantasy-Filter'])->toBe(json_encode($filter, JSON_THROW_ON_ERROR));
});
