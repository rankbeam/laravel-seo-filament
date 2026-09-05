# Rankbeam SEO for Filament

[![Tests](https://github.com/rankbeam/laravel-seo-filament/actions/workflows/tests.yml/badge.svg)](https://github.com/rankbeam/laravel-seo-filament/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/rankbeam/laravel-seo-filament.svg?style=flat-square)](https://packagist.org/packages/rankbeam/laravel-seo-filament)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.md)

`rankbeam/laravel-seo-filament` is the free Laravel SEO package for Filament 4 and 5.
Add its complete SEO editor to any resource in two lines. Editors get live Google and
social previews, focus keywords, canonical and robots controls, social-image validation,
and clear indicators showing whether each value is manual or inherited from Rankbeam's
fallback chain.

Free and MIT licensed. Values are stored through
[`rankbeam/laravel-seo`](https://github.com/rankbeam/laravel-seo), with no extra columns
on your resource tables.

[Filament guide](https://docs.rankbeam.dev/guide/filament) ·
[Full documentation](https://docs.rankbeam.dev/) ·
[Packagist](https://packagist.org/packages/rankbeam/laravel-seo-filament)

## Installation

```bash
composer require rankbeam/laravel-seo-filament
```

Installing this package pulls in the core (`rankbeam/laravel-seo`). If you have
not set the core up yet:

```bash
php artisan vendor:publish --tag=seo-config
php artisan migrate
```

## What you get

- Live Google and social previews while editors type.
- Title, description, focus keywords, canonical, robots, and social-image fields.
- Manual-versus-fallback source indicators for every effective value.
- Optional schema.org fields for breadcrumbs, FAQs, and products.
- Filament 4 and 5 support, tested in CI.

## Requirements

| Dependency | Version |
|---|---|
| PHP | 8.2 – 8.4 |
| Filament | **4.x or 5.x** (both tested in CI; the test suite passes unchanged on both) |
| Core package | `rankbeam/laravel-seo` ^2.0 \|\| ^3.0 |

Core 2 installs are supported under this constraint: when the newer
`seoMetaForLocale()` helper is not available, the forms hydrate through the
older `seoMeta()` relation.

<div class="filament-hidden">

### Contributing / local development

This repository consumes the core through a sibling path repository
(`../laravel-seo`); CI checks out both repositories side by side.

</div>

## Usage

The model behind your resource must use the core `HasSEO` trait:

```php
use Rankbeam\Seo\Traits\HasSEO;

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
(`title`, `description`, `focus_keywords`, `canonical`, `robots`, `og_image`).

The tabbed Google/social preview is shown by default. Pass `showPreview: false` to omit it
(the source-indicators panel is unaffected):

```php
static::seoSection(showPreview: false);   // or SEOFields::make(showPreview: false)
```

Without the trait, `SEOFields::make()` returns the same section directly.

> **Upgrading:** the preview view was replaced by the tabbed editor. If you published the
> package views, refresh or remove the stale copy — see [`UPGRADING.md`](UPGRADING.md).

## Structured data (optional)

Add a second, optional section so editors can attach schema.org JSON-LD without code:

```php
static::seoSchemaSection(),     // or SEOSchemaFields::make()
```

It writes into the core `seo_meta.schema_jsonld` column and is pure UI binding over the
core schema builders — a one-toggle **automatic breadcrumb**
(`BreadcrumbSchema::fromModelAncestors()`) plus a repeater of **FAQ** (`FAQSchema`) and
**Product** (`ProductSchema`) blocks. Every built document is validated by the core
`SchemaValidator`; a malformed block (e.g. a Product with no offer) is rejected on save.
Schema it can't represent (a hand-authored `@graph`, an exotic `@type`, richer Product
fields) is preserved verbatim. See the
[Filament guide](https://docs.rankbeam.dev/guide/filament) for details.

<div class="filament-hidden">

## Testing

```bash
composer update --with "filament/filament:~4.0" && vendor/bin/pest   # Filament 4 leg
composer update --with "filament/filament:~5.0" && vendor/bin/pest   # Filament 5 leg
```

The suite covers render, live counter states, create + edit save round-trips
(including og:image upload), field clearing, URL validation, source-indicator
attribution for every resolver layer, focus-keyword round-trips, the editorial
preview (SERP + social tabs, the `showPreview` opt-out, the server-side preview
payload — manual-vs-fallback image source, known-local dimension measurement,
remote-image deferral, and shared-threshold warnings), and the structured-data
editor (FAQ / Product build + round-trip, automatic breadcrumb, validation
rejection, optimistic-concurrency reconciliation, and custom-schema preservation).

### Testbench note (provider order)

If you boot Filament in orchestra/testbench yourself, register Filament's
`SupportServiceProvider` **before** `LivewireServiceProvider`. Filament rebinds Livewire's
`DataStore` (which drops Livewire's registered instance); only Livewire's later mechanism
registration pins the resolved override as the shared instance. Package discovery produces
this order naturally in a real app — a hand-rolled provider list might not. The symptom of
getting it wrong is `ViewErrorBag::put(): Argument #2 ($bag) must be of type MessageBag,
null given` on every Livewire test.

</div>

## Translations

The editor follows `app()->getLocale()`. Publish the strings with `php artisan vendor:publish --tag=seo-filament-lang` to override a label, or contribute a language — rules and glossary in the core repository's [TRANSLATING.md](https://github.com/rankbeam/laravel-seo/blob/master/TRANSLATING.md).

## Guides

- [Laravel Filament SEO: previews, canonicals and fallback sources](https://blog.rankbeam.dev/posts/laravel-filament-seo)
- [Laravel SEO: the complete guide](https://blog.rankbeam.dev/posts/laravel-seo-guide)
- [Best Laravel SEO packages compared](https://blog.rankbeam.dev/posts/laravel-seo-packages-compared)

## License

MIT — see [LICENSE.md](LICENSE.md).
