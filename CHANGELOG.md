# Changelog

All notable changes to `rankbeam/laravel-seo-filament` are documented in this
file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
