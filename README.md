# rankbeam/laravel-seo-filament

Filament form components for the [Rankbeam Laravel SEO core](../laravel-seo) (currently
published as `fibonoir/laravel-seo`; the vendor rename to `rankbeam` lands with core v2.0.0).

Adds a complete, production-pattern SEO section to any Filament resource form:

- **SEO title & description** with live character counters — thresholds (60/160) come from
  the core `SEOWarningEvaluator`, so the admin UI and the audit layer can never disagree.
- **Canonical URL** field (empty = automatic canonical, query string stripped).
- **Robots directive** select (empty = site default).
- **Social sharing image** upload (og:image / twitter:image).
- **Live search-snippet preview** that mirrors the resolver's fallback chain while you type.
- **Manual-vs-fallback indicators**: a per-field panel showing the effective value and the
  resolver layer that produced it (Manual / Content fallback / Model-type default /
  Global default / Site config / Derived from URL).

Values persist to the core package's `seo_meta` record via the `HasSEO` trait's
relationship — no extra columns on your tables.

## Requirements

| Dependency | Version |
|---|---|
| PHP | 8.2 – 8.4 |
| Filament | **4.x or 5.x** (both tested in CI; the test suite passes unchanged on both) |
| Core package | `fibonoir/laravel-seo` (master / upcoming v2.0.0) |

## Installation

```bash
composer require rankbeam/laravel-seo-filament
```

> **Local development:** the package consumes the core via a sibling path repository
> (`../laravel-seo`). CI checks out both repositories side by side; CI goes green once the
> carved core (T1) is pushed to the canonical repo.

## Usage

The model behind your resource must use the core `HasSEO` trait:

```php
use Fibonoir\LaravelSEO\Traits\HasSEO;

class Post extends Model
{
    use HasSEO;
}
```

Then add the section to the resource — two lines:

```php
use Rankbeam\Seo\Filament\Concerns\HasSEOFields;

class PostResource extends Resource
{
    use HasSEOFields;                       // 1

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title'),
            // ...
            static::seoSection(),           // 2
        ]);
    }
}
```

`static::seoSection(['title', 'description'])` limits the section to a subset of fields
(`title`, `description`, `canonical`, `robots`, `og_image`).

Without the trait, `SEOFields::make()` returns the same section directly.

## Testing

```bash
composer update --with "filament/filament:~4.0" && vendor/bin/pest   # Filament 4 leg
composer update --with "filament/filament:~5.0" && vendor/bin/pest   # Filament 5 leg
```

31 tests / 105 assertions: render, live counter states, create + edit save round-trips
(including og:image upload), field clearing, URL validation, and source-indicator
attribution for every resolver layer.

### Testbench note (provider order)

If you boot Filament in orchestra/testbench yourself, register Filament's
`SupportServiceProvider` **before** `LivewireServiceProvider`. Filament rebinds Livewire's
`DataStore` (which drops Livewire's registered instance); only Livewire's later mechanism
registration pins the resolved override as the shared instance. Package discovery produces
this order naturally in a real app — a hand-rolled provider list might not. The symptom of
getting it wrong is `ViewErrorBag::put(): Argument #2 ($bag) must be of type MessageBag,
null given` on every Livewire test.

## License

MIT — see [LICENSE.md](LICENSE.md).
