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
| PHP | ^8.2 (Laravel 13 requires PHP 8.3+; PHP 8.5 requires Laravel 12 or 13) |
| Filament | **4.x or 5.x** (both tested in CI; the test suite passes unchanged on both) |
| Core package | `rankbeam/laravel-seo` ^3.17 |

These are the constraints for the current release. Upgrade older core installs
to a compatible 3.x version before upgrading the editor. See the
[installation guide](https://docs.rankbeam.dev/guide/installation) for runtime
coverage and optional dependencies.

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

### Several languages

The core keeps one `seo_meta` row per (model, locale). Pass the locales a page is published in
and the section renders **one tab per language** — each editing its own row, with counters for
that language's script (an empty Japanese title shows `0 / 30`, an English one `0 / 60`), its
own preview and its own fallback indicators, badged with the number of fields set:

```php
static::seoSection(locales: ['en', 'it', 'ja']);   // or SEOFields::make(locales: [...])
```

Or once for every resource, in the published config:

```bash
php artisan vendor:publish --tag=seo-filament-config
```

```php
// config/seo-filament.php
'locales' => ['en', 'it', 'ja'],
```

Untouched languages never get a placeholder row. When the page runs a translatable plugin
(Filament's `getActiveSchemaLocale()` — the spatie translatable plugins implement it), the
section follows the page's own locale switcher instead of showing tabs. With neither, it edits
the app locale's row, as before.

> **Upgrading:** the preview view was replaced by the tabbed editor. If you published the
> package views, refresh or remove the stale copy — see [`UPGRADING.md`](UPGRADING.md).


### Lara Zeus / Spatie page switcher

With `lara-zeus/spatie-translatable` **1.x on Filament 4** or **2.x on
Filament 5**, use Rankbeam's page adapters for Edit and Create. Replace only
the page trait imports; keep the plugin's resource/list traits, panel plugin
and `LocaleSwitcher` action:

```php
// In your EditPost page:
use Rankbeam\Seo\Filament\Resources\Pages\EditRecord\Concerns\Translatable;

// In your CreatePost page (a separate file):
use Rankbeam\Seo\Filament\Resources\Pages\CreateRecord\Concerns\Translatable;
```

Each page still declares `use Translatable;` inside its class. The plugin
remains an optional application dependency. Use its latest patched version;
the integration fixture covers plugin 1.0.4 / Filament 4.13.1 and plugin
2.0.1 / Filament 5.8.1.

Switching keeps unsaved parent content, SEO metadata and structured-data drafts
in the editor. Save validates every visited language and saves them together
in a database transaction. A validation error opens the language that needs
attention. Uploads are stored on Save; leaving or reloading the page discards
unsaved drafts. Saving a draft does not translate missing content for you.

These lossless 2× captures show the same local test application's English
operator interface editing Italian and Japanese content. The screenshots use
Filament 5.8.1, Lara Zeus 2.0.1 and Rankbeam Filament 1.12.0. They demonstrate
content-locale previews and counters, not native-language quality approval.
The captures also show optional Pro 2.40.1 controls: AI suggestions and scan
scores require Pro and are not included in the free editor.

[![Italian content in the editor](https://raw.githubusercontent.com/rankbeam/laravel-seo-filament/master/docs/images/editor-it.png)](https://raw.githubusercontent.com/rankbeam/laravel-seo-filament/master/docs/images/editor-it.png)

[![Japanese content in the editor](https://raw.githubusercontent.com/rankbeam/laravel-seo-filament/master/docs/images/editor-ja.png)](https://raw.githubusercontent.com/rankbeam/laravel-seo-filament/master/docs/images/editor-ja.png)

The adapters preserve the normal before/after hooks and form-data mutators.
If your page overrides `handleRecordCreation()`, `handleRecordUpdate()`,
`callHook()` or transaction methods, integrate the adapter behavior in that
customization and test its save flow. Database transactions do not roll back
filesystem writes; applications should retain their usual orphan-file cleanup.

For custom live text fields on Livewire 3, prefer `->live()` or
`->live(onBlur: true)` over an explicit debounce: the latter delays local model
state and can lose the last keystrokes during a quick locale switch. Rankbeam's
title and description fields use the default request debounce.

The upstream page traits alone refill forms during a switch. Rankbeam guards
against their accidental metadata writes, but those traits do not preserve SEO
drafts; migrate Edit/Create pages to the adapters. Explicit `locales:` tabs
remain a shared editor and take precedence over the page switcher.

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

The editor's labels follow `app()->getLocale()` (the *content* locales it edits are a separate matter — see [Several languages](#several-languages)). Publish the strings with `php artisan vendor:publish --tag=seo-filament-lang` to override a label, or contribute a language — rules and glossary in the core repository's [TRANSLATING.md](https://github.com/rankbeam/laravel-seo/blob/master/TRANSLATING.md). Seventeen languages ship: English, Italian, German, French, Spanish, Brazilian Portuguese, Dutch, Turkish, Russian, Polish and, since 1.10, Japanese, Simplified and Traditional Chinese (`zh_CN`, `zh_TW`), Korean, Greek, Ukrainian and Czech.

## Guides

- [Laravel Filament SEO: previews, canonicals and fallback sources](https://blog.rankbeam.dev/posts/laravel-filament-seo)
- [Laravel SEO: the complete guide](https://blog.rankbeam.dev/posts/laravel-seo-guide)
- [Best Laravel SEO packages compared](https://blog.rankbeam.dev/posts/laravel-seo-packages-compared)

## License

MIT — see [LICENSE.md](LICENSE.md).
