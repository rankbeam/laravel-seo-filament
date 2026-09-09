# Upgrading

This file documents changes that may require action when upgrading
`rankbeam/laravel-seo-filament`. Versions not listed are drop-in.

## 1.12.0

### Translatable Edit/Create pages

Use the Rankbeam `Translatable` adapter import in each Edit/Create page that
uses the Lara Zeus locale switcher. See the [integration instructions](README.md#lara-zeus--spatie-page-switcher).
The upstream switch reads form state through save hooks: older Rankbeam
versions can write the outgoing language's SEO into the newly selected
language even without Save. This release prevents those writes. The adapters
also preserve unsaved per-language drafts and save them transactionally.

No database migration is needed. Check existing translations if editors used
the old switcher; an upgrade cannot reconstruct values already overwritten.
The core dependency remains `^3.17` (raised in 1.11.0).

## Earlier preview upgrade

### The SEO preview view was replaced — refresh any published copy

The search-only snippet preview is replaced by a tabbed **Google SERP / social
card** live editor. The package view that backs it,
`seo-filament::seo-snippet-preview`, was rewritten.

**You only need to act if you published the package views** (i.e. you ran
`php artisan vendor:publish --tag=seo-filament-views`). A published copy in
`resources/views/vendor/seo-filament/seo-snippet-preview.blade.php` **shadows**
the package view, so the old single-tab preview would keep rendering and you
would not get the new SERP/social editor.

Pick one:

- **Drop the override** (recommended unless you customized it) — delete
  `resources/views/vendor/seo-filament/seo-snippet-preview.blade.php` so the
  package view is used again.
- **Re-publish and re-apply your changes** — overwrite the stale copy:

  ```bash
  php artisan vendor:publish --tag=seo-filament-views --force
  ```

  then port any local customizations onto the new view.

If you never published the views, no action is needed — the new preview is
picked up automatically.

### Opting out of the preview

The preview is on by default. To omit it (for example on a resource where the
section is embedded somewhere the preview does not fit):

```php
SEOFields::make(showPreview: false);
// or, via the trait:
static::seoSection(showPreview: false);
```

The manual-vs-fallback source-indicators panel is unaffected by this flag.
