<?php

use MichaelCrowcroft\EspnLaravel\Http\Requests\Athletes\GetAthleteById;
use MichaelCrowcroft\EspnLaravel\Http\Requests\Athletes\GetAthletes;

it('builds athletes list endpoint and query correctly', function () {
    $req = new GetAthletes(page: 2, limit: 500);

    expect($req->resolveEndpoint())->toBe('/sports/football/nfl/athletes');

    $reflect = new ReflectionClass($req);
    $method = $reflect->getMethod('defaultQuery');
    $method->setAccessible(true);

    expect($method->invoke($req))->toBe([
        'page' => 2,
        'limit' => 500,
    ]);
});

it('builds athlete by id endpoint correctly', function () {
    $req = new GetAthleteById(15864);

    expect($req->resolveEndpoint())->toBe('/sports/football/nfl/athletes/15864');
});
