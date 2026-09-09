# Changelog

All notable changes to `rankbeam/laravel-seo-filament` are documented in this
file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.12.0] - 2026-09-09

### Fixed

- Guard metadata and structured-data save hooks during Lara Zeus locale switches, which otherwise write outgoing values into the newly selected language before Save.
- Keep title/description input state current during fast switches on Livewire 3, using its default request debounce.

### Added

- Optional Lara Zeus Edit/Create adapters preserve per-language content, metadata, schema and upload drafts; validate all visited languages; and save them in a database transaction. Invalid drafts open their language, inactive parent fields retain their dehydration hooks, and Create Another starts with empty translation drafts.
- Integration and upgrade instructions for Filament 4 / plugin 1 and Filament 5 / plugin 2. The plugin remains optional.

## [1.11.0] - 2026-09-08

### Fixed

- Preview title/description fallbacks, preview URLs and canonical provenance read content hooks in the editor's locale while source labels stay in the panel language.

### Added

- Every SEO field carries its content locale, exposed through `SeoLocales::forField()`. Pro actions can follow locale tabs, a single configured locale or the page's active schema locale without inferring the language from a field path. Requires core `^3.17`.

## [1.10.0] - 2026-09-07

### Added

- **Seven more languages for the editor**: Japanese (`ja`), Simplified Chinese (`zh_CN`), Traditional Chinese (`zh_TW`), Korean (`ko`), Greek (`el`), Ukrainian (`uk`) and Czech (`cs`) — first passes, native review wanted; parity-tested with the first ten. The locale-tab labels (1.9) already named these languages through ext-intl; now the rest of the section speaks them too.

## [1.9.0] - 2026-09-07

### Added

- **One tab per language.** The core keeps one `seo_meta` row per (model, locale); until now the section could only reach the app locale's row. Pass the locales a page is published in — `static::seoSection(locales: ['en', 'it', 'ja'])`, `SEOFields::make(locales: [...])`, or once for every resource in the new `config/seo-filament.php` (`php artisan vendor:publish --tag=seo-filament-config`) — and the section renders one tab per language, labelled with the language's name in the panel's language (ext-intl; the code without it) and badged with the number of fields set in that version. Each tab edits its own row with its own live counters (the core `LengthPolicy` for that language's script: an empty Japanese title shows `0 / 30` next to an English `0 / 60` on the same page), its own SERP / social preview and its own fallback indicators. All tabs are validated and saved together; a language nothing was entered for never gets a placeholder row. Form state is `seo_meta.{locale}.title` with several locales and stays `seo_meta.title` with one, so existing tests and the Pro field actions are unaffected.
- **Follows a translatable plugin's locale.** When the page exposes an active schema locale (Filament's `getActiveSchemaLocale()`, which the spatie translatable plugins' page concern implements) and the section was given no locale list, it edits that locale's row and re-hydrates on every switch of the page's header locale switcher, with no tabs of its own — the structured-data section follows the same locale. Duck-typed on Filament's method, no plugin dependency.
- `Rankbeam\Seo\Filament\Support\SeoLocales` — the locale resolution (explicit → page → config → app locale) and the language labels, reusable by add-ons.

### Fixed

- The source indicators for a non-app locale described the **app locale's** manual values (the manual layer ignored the locale it was asked for). They now read the requested locale's row.

## [1.8.0] - 2026-09-06

### Changed

- **Script-aware character counters and preview budgets.** The live "n / max characters" counters under the title and description fields, and the SERP / social preview's truncation and warnings, now take their budget from the core `Rankbeam\Seo\I18n\LengthPolicy` (core 3.15): 60 / 160 for Latin text as before, ~30 / ~80 for Chinese, Japanese and Korean, per `config('seo.length_policy')`; the app locale is the hint for an empty field. Lengths are counted in graphemes, so a Thai syllable or an emoji counts as one. The same numbers `seo:audit` and the Pro scan report, so the editor can never contradict them. Requires `rankbeam/laravel-seo` **^3.15**.
- The canonical field accepts internationalised URLs (an IDN host, a Unicode path) — it already did through Laravel's `url` rule; now pinned by a test, matching the core audit's Unicode-aware `invalid_canonical` check.

## [1.7.0] - 2026-09-05

### Added

- **Nine languages for the editor:** Italian (`it`), German (`de`), French (`fr`), Spanish (`es`), Brazilian Portuguese (`pt_BR`), Dutch (`nl`), Turkish (`tr`), Russian (`ru`) and Polish (`pl`). Italian reviewed by the maintainer; the others are first passes awaiting a native review (see `TRANSLATING.md` in the core repository). Held to the parity test. Apps running in English see no change.

## [1.6.0] - 2026-09-05

### Added

- **Translatable editor (i18n foundation).** Every label, helper text, option, source badge and preview caption in the SEO section and the structured-data section now goes through Laravel translation lines in the `seo-filament` namespace (`resources/lang/en/seo-filament.php`), published with `php artisan vendor:publish --tag=seo-filament-lang`. The editor follows `app()->getLocale()`; English output is unchanged. The live counter warnings come from the core package (`seo::seo.warnings.*`, core ≥ 3.13). A parity test fails CI when a language file misses a key, carries an orphan key, an empty value or a lost placeholder. First step of the multilingual program; the locale switcher for per-locale metadata is the next one.

## [1.5.0] - 2026-07-04

### Added

- **Side-by-side live preview.** On wide screens (`lg` and up) the SEO section
  now lays out as a two-column grid — the form fields on the left, the live
  SERP/social preview in a **sticky** column on the right — so the snippet
  updates beside the fields as you type. Below `lg` it stacks as before, and
  with the preview opted out (`showPreview: false`) the fields take the full
  width unchanged. The section also gained a short description ("How this page
  appears in search results and when shared on social").

### Changed

- **Validation warnings restyled** from left-accent pills to bordered cards with
  a per-type SVG icon (info / warning / danger), for a clearer read that still
  follows the panel palette via CSS variables + `color-mix` in both light and
  dark mode. **Visual only** — no API, payload, or behavior change; published
  views may need a refresh.
- The effective-values (source-indicators) panel no longer caps at `600px`, so
  it spans the editor width alongside the new two-column layout.

## [1.4.0] - 2026-06-30

### Changed

- **SEO editor section visual refresh** — the SERP/social preview and the
  effective-values panel were repolished to feel native to the host panel.
  Brand accents are now **theme-aware**: instead of a hardcoded blue, the
  preview and indicators follow the panel's Filament **primary** color (with
  `success`/`gray` for the supporting states) via CSS variables and
  `color-mix`, so the section adopts the app's palette and reads correctly in
  both light and dark mode. The SERP result card gained a **favicon and subtle
  depth**; the Google/Social switch became a **segmented control**; and the
  validation warnings became cleaner **left-accent pills**. Authentic Google
  and social-network colors are intentionally still hardcoded so the previews
  stay faithful to the real surfaces. **Visual only** — no API, payload, or
  behavior change; published views may need a refresh (see
  [`UPGRADING.md`](UPGRADING.md)).

## [1.3.0] - 2026-06-16

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

[Unreleased]: https://github.com/rankbeam/laravel-seo-filament/compare/v1.9.0...HEAD
[1.9.0]: https://github.com/rankbeam/laravel-seo-filament/compare/v1.8.0...v1.9.0
[1.4.0]: https://github.com/rankbeam/laravel-seo-filament/compare/v1.3.0...v1.4.0
[1.3.0]: https://github.com/rankbeam/laravel-seo-filament/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/rankbeam/laravel-seo-filament/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/rankbeam/laravel-seo-filament/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/rankbeam/laravel-seo-filament/releases/tag/v1.0.0
