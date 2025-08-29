<?php

use MichaelCrowcroft\EspnLaravel\Http\Connectors\CoreConnector;
use MichaelCrowcroft\EspnLaravel\Http\Connectors\FantasyConnector;

it('core connector resolves default base url and trims trailing slash', function () {
    $default = new CoreConnector();
    expect($default->resolveBaseUrl())->toBe('https://sports.core.api.espn.com/v3');

    $custom = new CoreConnector(baseUrl: 'https://example.com/v3/');
    expect($custom->resolveBaseUrl())->toBe('https://example.com/v3');
});

it('fantasy connector resolves default base url and trims trailing slash', function () {
    $default = new FantasyConnector();
    expect($default->resolveBaseUrl())->toBe('https://lm-api-reads.fantasy.espn.com/apis/v3');

    $custom = new FantasyConnector(baseUrl: 'https://example.com/apis/v3/');
    expect($custom->resolveBaseUrl())->toBe('https://example.com/apis/v3');
});
