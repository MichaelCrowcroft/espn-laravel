# ESPN Laravel SDK (Saloon)

## Installation

You can install the package via composer:

```bash
composer require michaelcrowcroft/espn-laravel
```

(Optional) You can publish the config file with:

```bash
php artisan vendor:publish --tag="espn-laravel-config"
```

## Usage

```php
use Espn\Facades\Espn;

// Athletes (Core v3)
$athletes = Espn::athletes()->page(page: 1, limit: 20000);
$mahomes  = Espn::athletes()->get(15864); // Example athlete ID

// Fantasy players (reads v3)
$playersAllOn     = Espn::fantasy()->allOn(2024); // view=allon
$konaInfo         = Espn::fantasy()->konaPlayerInfo(2024, limit: 2000);
$customFilter     = Espn::fantasy()->players(
    2024,
    'mLiveScoring',
    ['games' => ['limit' => 2000]] // becomes X-Fantasy-Filter header
);

// Saloon Response usage
$json = $athletes->json();
```

### Fluent Helpers for Fantasy (easy website stats)

```php
use Espn\Facades\Espn;

// Top actual scorers (weekly or overall best available)
$topWeekly = Espn::fantasy()->topScorers(year: 2024, week: 1, take: 25);

// Top projected scorers (weekly or best available projection)
$topProjected = Espn::fantasy()->topProjected(year: 2024, week: 2, take: 25);

// Trending by ownership change (best-effort from kona_player_info)
$trendingAdds  = Espn::fantasy()->trendingAdds(year: 2024, take: 25);
$trendingDrops = Espn::fantasy()->trendingDrops(year: 2024, take: 25);

// Each returns an array of simple player summaries:
// [
//   ['id' => 123, 'name' => 'Player Name', 'team' => 'KC', 'position' => 'TE', 'points' => 27.8],
//   ...
// ]

// Fluent query builder for more control
$query = Espn::fantasy()
    ->query(2024)
    ->positions(['RB', 'WR'])   // simple post-filter by position
    ->scoringPeriod(1)          // weekly focus
    ->limit(1500);              // X-Fantasy-Filter games.limit

$topRbwrs = $query->topScorers(take: 20);
```

Notes:
- The helpers use common ESPN views (mLiveScoring and kona_player_info) and return simple arrays for direct UI use.
- Trending methods are based on ownership change fields when provided by the API. If a dataset lacks ownership deltas, results may be empty.
- For full control, use `Espn::fantasy()->players(...)` with a custom X-Fantasy-Filter.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [:author_name](https://github.com/:author_username)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
