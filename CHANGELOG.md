# Changelog

All notable changes to `rankbeam/laravel-seo-filament` are documented in this
file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

Nothing yet.

## [1.2.0] - 2026-06-15

### Changed

- **Widened the core constraint to `rankbeam/laravel-seo` `^2.0 || ^3.0`** so
  the package installs against both Core 2 and Core 3 (released before Core 3
  so registry users are never left with an unsatisfiable middle).

### Added

- **Structured-data editor** — an optional `SEOSchemaFields::make()` section
  (also `static::seoSchemaSection()` via `HasSEOFields`) that lets content
  editors attach schema.org JSON-LD without code, writing into the core
  `seo_meta.schema_jsonld` column. It is pure UI binding over the core schema
  builders: a one-toggle **automatic breadcrumb**
  (`BreadcrumbSchema::fromModelAncestors()`) plus a repeater of **FAQ**
  (`FAQSchema`) and **Product** (`ProductSchema`) blocks. Every built document
  is checked by the core `SchemaValidator` and a malformed block is rejected on
  save; empty blocks are ignored. Stores a single object or a JSON-LD array
  (breadcrumb first), and **preserves verbatim** any stored schema it cannot
  represent (hand-authored `@graph`, exotic `@type`, or richer Product fields)
  so a save never clobbers code-authored schema. Stays optional — apps opt in by
  adding the section.
- **Focus keywords field** — a `TagsInput` in the SEO section persisting to
  `seo_meta.focus_keywords`. Keywords are edited as plain tags but stored in
  the core's structured `[{keyword, is_primary}]` shape (the first tag is
  marked primary), so `SEOMeta::getPrimaryKeyword()` and `SEOData` read them
  unchanged. Added to `SEOFields::FIELDS`, so it appears by default and can be
  excluded via the `$only` argument like any other field. Set
  `seo.keywords.enabled` (core config) to have the free `seo:audit` and the Pro
  scan flag pages that still lack a keyword.

## [1.1.0] - 2026-06-13

### Added

- `SEOFields::modifyFieldUsing(string $field, Closure $modifier)` — an
  extension point letting add-on packages decorate individual SEO fields
  when the section is built (used by the Pro AI suggestion actions), plus
  `SEOFields::flushFieldModifiers()` for test isolation.

## [1.0.0] - 2026-06-12

Initial release.

### Added

- `SEOFields::make(?array $only)` — a Filament form Section with title,
  description, canonical, robots, and og:image fields persisting to the core
  `seo_meta` relationship.
- `HasSEOFields` trait — adds `static::seoSection(?array $only)` to a
  resource; integrating SEO into an existing Filament resource is two lines.
- Live character counters for title (60) and description (160), driven by
  the core `SEOWarningEvaluator` thresholds.
- Google-style search snippet preview (Alpine, entangled with form state,
  server-computed fallbacks).
- Manual-vs-fallback source indicators: `SEOFieldSources` re-walks the core
  resolver layers and labels each effective value (Manual / Content fallback /
  Model-type default / Global default / Site config / Derived from URL).
- og:image `FileUpload` storing to Filament's default disk under `seo/`.
- Dual Filament support: `filament/filament ^4.0|^5.0` (Livewire 3 and 4) —
  the test suite passes unchanged on both majors.

[Unreleased]: https://github.com/rankbeam/laravel-seo-filament/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/rankbeam/laravel-seo-filament/releases/tag/v1.0.0
