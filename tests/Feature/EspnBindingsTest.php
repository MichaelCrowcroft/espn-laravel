<?php

use Espn\Espn;
use Espn\Facades\Espn as EspnFacade;
use Espn\Resources\AthletesResource;
use Espn\Resources\FantasyResource;

it('resolves espn as a singleton from the container', function () {
    $one = app(Espn::class);
    $two = app(Espn::class);

    expect($one)
        ->toBeInstanceOf(Espn::class)
        ->and($two)
        ->toBeInstanceOf(Espn::class)
        ->and($one)
        ->toBe($two); // singleton
});

it('aliases espn binding to the string key', function () {
    $alias = app('espn');

    expect($alias)->toBeInstanceOf(Espn::class);
});

it('facade exposes resources', function () {
    expect(EspnFacade::athletes())
        ->toBeInstanceOf(AthletesResource::class)
        ->and(EspnFacade::fantasy())
        ->toBeInstanceOf(FantasyResource::class);
});

it('loads default package config', function () {
    expect(config('espn.core.base_url'))
        ->toBe('https://sports.core.api.espn.com/v3')
        ->and(config('espn.fantasy.base_url'))
        ->toBe('https://lm-api-reads.fantasy.espn.com/apis/v3');
});
