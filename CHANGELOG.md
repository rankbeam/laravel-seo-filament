# Changelog

All notable changes to `rankbeam/laravel-seo-filament` are documented in this
file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- **Editorial SEO preview (Google SERP + social card)** — the search-only
  snippet is replaced by a tabbed, live preview. A **Google** tab renders the
  SERP result and a **Social** tab renders the share card (image + title +
  description + domain), both updating as you type (title / description / URL
  are entangled with the form). Warnings reuse the **core
  `SEOWarningEvaluator` thresholds** (title > 60, description > 160, social
  image min 200×200 / ideal 1200×630) so the audit, the preview, and the Pro
  scan never disagree. Social-image dimensions have **explicit states**:
  *known-local* (measured server-side with `getimagesize`), *browser-measured*
  (measured client-side — tolerant of CORS / signed-URL / private-disk /
  temp-upload failure), and *unavailable*; a failed remote image degrades to a
  placeholder and **never breaks the form**. The effective social image follows
  the resolver order (manual `seo_meta` → content/config fallback) via
  `SEOPreviewData`, and live source labels reflect the **current, unsaved**
  form input without claiming a value came from the database. The preview
  honors the same `target` resolver as the rest of the section (it reflects the
  related model's SEO). Default on; opt out with
  `SEOFields::make(showPreview: false)` (also `static::seoSection(showPreview: false)`).
  Scoped CSS only, dark-mode aware, Filament 4 and 5.

### Changed

- **Core 2 compatibility under the existing `rankbeam/laravel-seo` `^2.0 || ^3.0`
  constraint**: form hydration now falls back to the `seoMeta()` relation when
  `seoMetaForLocale()` is unavailable (for example core 2.0.1). Existing
  Core 2 installs no longer hydrate empty SEO/schema fields solely because the
  newer locale helper is missing.
- Removed the descriptive copy from the SEO form section header; the section now
  opens directly into the usable controls and preview.
- The published `seo-snippet-preview` view was **replaced** by the tabbed
  editor above. Apps that published it to `resources/views/vendor/seo-filament`
  must refresh or remove the stale copy — see
  [`UPGRADING.md`](UPGRADING.md).

- **Related-model target resolver** — `SEOFields::make()` and
  `SEOSchemaFields::make()` (and `static::seoSection()` /
  `static::seoSchemaSection()` via `HasSEOFields`) now accept an optional
  `target` closure, `Closure(?Model $formRecord): ?Model`, that redirects every
  SEO read and write to a **related** model instead of the form's own record —
  e.g. an entity whose canonical SEO lives on a related `PublicPage`:

  ```php
  static::seoSection(target: fn (?Model $record): ?Model => $record?->publicPage);
  static::seoSchemaSection(target: fn (?Model $record): ?Model => $record?->publicPage);
  ```

  The same resolver drives hydration, save, the source indicators, the snippet
  preview, **and** the structured-data editor, so every part of the section acts
  on one consistent model. The closure is evaluated through Filament's closure
  evaluator (so it can also inject `$record`, `$operation`, …) and tolerates
  create-form nullness: a null target reads and writes nothing and **never
  auto-creates a placeholder** related model. A non-null target that does not
  expose the core `seoMeta()` relation throws a clear developer exception. The
  related target's locale-scoped `seo_meta` is used. **Additive** — omitting
  `target` keeps today's behavior (binds the form's own record).

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
