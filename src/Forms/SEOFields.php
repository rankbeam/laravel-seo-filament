<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Forms;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Rankbeam\Seo\Data\SEOData;
use Rankbeam\Seo\Filament\Support\ResolvesSeoTarget;
use Rankbeam\Seo\Filament\Support\SeoLocales;
use Rankbeam\Seo\Filament\Support\SEOPreviewData;
use Rankbeam\Seo\I18n\LengthPolicy;
use Rankbeam\Seo\Models\SEOMeta;
use Rankbeam\Seo\Services\SEOWarningEvaluator;

/**
 * A drop-in SEO section for any Filament resource form.
 *
 * Edits the seo_meta record behind the core package's HasSEO trait:
 * title and description with live character counters (thresholds from the
 * core SEOWarningEvaluator), canonical URL, robots directive, Open Graph
 * image, a live search-snippet preview, and per-field indicators showing
 * which resolver layer (manual, content fallback, defaults, config)
 * produced each effective value.
 *
 * ```php
 * use Rankbeam\Seo\Filament\Forms\SEOFields;
 *
 * public static function form(Schema $schema): Schema
 * {
 *     return $schema->components([
 *         // ... your fields ...
 *         SEOFields::make(),
 *     ]);
 * }
 * ```
 *
 * The model behind the form must use the core HasSEO trait.
 *
 * ## Several languages
 *
 * The core keeps one `seo_meta` row per (model, locale). Pass the locales a
 * page is published in and the section renders one tab per language, each
 * editing its own row with its own counters (the core LengthPolicy for that
 * language's script), its own preview and its own fallback indicators:
 *
 * ```php
 * SEOFields::make(locales: ['en', 'it', 'ja'])
 * // or once for every resource: config('seo-filament.locales')
 * ```
 *
 * When the page runs a translatable plugin (Filament's
 * `getActiveSchemaLocale()` returns the locale its header switcher selected)
 * the section follows that locale instead of showing tabs of its own. With
 * neither, it edits the app locale's row exactly as before.
 *
 * Form state: `seo_meta.title` with one locale (unchanged since 1.0), and
 * `seo_meta.{locale}.title` with several.
 */
class SEOFields
{
    use ResolvesSeoTarget;

    public const FIELDS = ['title', 'description', 'focus_keywords', 'canonical', 'robots', 'og_image'];

    /** @var array<string, array<int, \Closure(Field): ?Field>> */
    protected static array $fieldModifiers = [];

    /**
     * Register a modifier applied to one named field (see self::FIELDS)
     * every time the section is built. This is the extension point for
     * add-on packages - e.g. the Pro AI suggestion actions attach
     * themselves to 'title' and 'description' through it. The closure
     * receives the built Field and returns it (returning null keeps the
     * field as passed in). With several locales the modifier runs once per
     * locale tab, on that tab's field.
     */
    public static function modifyFieldUsing(string $field, \Closure $modifier): void
    {
        static::$fieldModifiers[$field][] = $modifier;
    }

    /**
     * Drop all registered field modifiers (used between tests).
     */
    public static function flushFieldModifiers(): void
    {
        static::$fieldModifiers = [];
    }

    /**
     * @param  array<int, string>|null  $only  Subset of self::FIELDS to show
     * @param  \Closure|null  $target  Closure(?Model $formRecord): ?Model — edit the
     *                                 SEO of a RELATED model instead of the form's own
     *                                 record. Null binds the form's record (default).
     * @param  bool  $showPreview  Render the tabbed (Google SERP / social card) live
     *                             preview. Default on; pass false to omit it.
     * @param  array<int, string>|null  $locales  The locales to edit, one tab each
     *                                            (`['en', 'it', 'ja']`). Null = the
     *                                            page's active locale when a translatable
     *                                            plugin provides one, else
     *                                            `config('seo-filament.locales')`, else
     *                                            the app locale alone.
     */
    public static function make(?array $only = null, ?\Closure $target = null, bool $showPreview = true, ?array $locales = null): Section
    {
        $only ??= self::FIELDS;

        $modelResolver = fn (View $component): ?Model => self::resolveSeoTargetFromContainer($target, $component);

        return Section::make(__('seo-filament::seo-filament.section.title'))
            ->icon('heroicon-o-magnifying-glass')
            ->description(__('seo-filament::seo-filament.section.description'))
            ->schema([
                Group::make()
                    ->statePath('seo_meta')
                    ->dehydrated(false)
                    ->columnSpanFull()
                    // The locale set is decided when the schema is built, not
                    // when make() runs: a translatable plugin's active locale
                    // lives on the Livewire page, which a component only knows
                    // once it is mounted in a form.
                    ->schema(function (Group $component) use ($only, $showPreview, $locales, $modelResolver): array {
                        [$list] = SeoLocales::resolve($locales, $component);

                        if (count($list) === 1) {
                            return self::localeEditor($only, $showPreview, $modelResolver, $list[0]);
                        }

                        return [self::localeTabs($list, $only, $showPreview, $modelResolver)];
                    })
                    ->afterStateHydrated(function (Group $component, ?Model $record) use ($only, $target, $locales): void {
                        $target = self::resolveSeoTarget($target, $record, $component);
                        [$list] = SeoLocales::resolve($locales, $component);

                        $state = [];

                        foreach ($list as $locale) {
                            $meta = $target instanceof Model ? self::currentMeta($target, $locale) : null;
                            $row = self::rowToState($meta, $only);

                            if (count($list) === 1) {
                                $state = $row;
                            } else {
                                $state[$locale] = $row;
                            }
                        }

                        $component->getChildSchema()->fill($state);
                    })
                    ->saveRelationshipsUsing(function (Group $component, ?Model $record) use ($only, $target, $locales): void {
                        $target = self::resolveSeoTarget($target, $record, $component);

                        // Null target (create form / not-yet-existing relation)
                        // or a model without the HasSEO contract: nothing to
                        // write, and we never auto-create a placeholder.
                        if (! $target instanceof Model || ! method_exists($target, 'seoMeta')) {
                            return;
                        }

                        // getState() (not the raw state) runs the dehydration
                        // hooks, which is what persists a freshly uploaded
                        // og_image file and turns it into a storable path.
                        $state = $component->getChildSchema()->getState();
                        [$list] = SeoLocales::resolve($locales, $component);

                        foreach ($list as $locale) {
                            $row = count($list) === 1 ? $state : ($state[$locale] ?? []);

                            self::persistRow($target, $locale, self::stateToRow(is_array($row) ? $row : [], $only));
                        }

                        $target->unsetRelation('seoMeta');
                    }),
            ])
            ->collapsible()
            ->columnSpanFull();
    }

    /**
     * One language's editor: the fields (with counters for that language's
     * script), the live preview and the source indicators, all resolved for
     * `$locale`. Returned as a list so it can be the Group's own schema (one
     * locale, state `seo_meta.*`) or a Tab's (state `seo_meta.{locale}.*`).
     *
     * @param  array<int, string>  $only
     * @return array<int, Component>
     */
    protected static function localeEditor(array $only, bool $showPreview, \Closure $modelResolver, string $locale): array
    {
        $fields = Group::make(array_values(Arr::only(self::fields($locale), $only)))
            ->columns(2);

        $preview = $showPreview
            ? View::make('seo-filament::seo-snippet-preview')
                ->model($modelResolver)
                ->viewData(fn (?Model $record): array => [
                    'preview' => app(SEOPreviewData::class)->forModel($record, $locale),
                    'previewHasImageField' => in_array('og_image', $only, true),
                ])
                ->columnSpanFull()
            : null;

        // Side-by-side on wide screens: the form fields on the left, the live
        // search / social preview alongside on the right, so the snippet
        // updates as you type. With no preview, the fields take the full width.
        $editor = $preview !== null
            ? Grid::make()
                ->columns(['default' => 1, 'lg' => 12])
                ->schema([
                    $fields->columnSpan(['default' => 1, 'lg' => 7]),
                    Group::make([$preview])
                        ->columnSpan(['default' => 1, 'lg' => 5])
                        ->extraAttributes(['style' => 'position: sticky; top: 1.5rem; align-self: start;']),
                ])
                ->columnSpanFull()
            : $fields->columnSpanFull();

        return [
            $editor,

            View::make('seo-filament::seo-source-indicators')
                ->model($modelResolver)
                ->viewData(['locale' => $locale])
                ->columnSpanFull(),
        ];
    }

    /**
     * One tab per locale, each a full {@see localeEditor()} bound to
     * `seo_meta.{locale}`. Tabs, not a select-and-hide switcher, so every
     * language's fields stay in the form state, are validated on save and
     * are persisted together — a hidden field would drop out of all three.
     *
     * The tab label is the language's name in the panel's language (ext-intl
     * when loaded, the code otherwise); the badge counts the fields set in
     * that language right now, so an editor sees at a glance which versions
     * are still empty.
     *
     * @param  array<int, string>  $locales
     * @param  array<int, string>  $only
     */
    protected static function localeTabs(array $locales, array $only, bool $showPreview, \Closure $modelResolver): Tabs
    {
        $tabs = [];

        foreach ($locales as $locale) {
            $tabs[] = Tab::make(SeoLocales::label($locale))
                ->statePath($locale)
                ->badge(function (Get $get) use ($only, $locale): ?string {
                    $filled = self::filledCount($get, $only, $locale);

                    return $filled > 0 ? (string) $filled : null;
                })
                ->badgeColor('success')
                ->badgeTooltip(function (Get $get) use ($only, $locale): string {
                    return __('seo-filament::seo-filament.locales.badge_tooltip', [
                        'count' => self::filledCount($get, $only, $locale),
                        'total' => count($only),
                        'language' => SeoLocales::label($locale),
                    ]);
                })
                ->schema(self::localeEditor($only, $showPreview, $modelResolver, $locale));
        }

        // Open on the panel's language when it is one of the tabs.
        $active = array_search(app()->getLocale(), $locales, true);

        return Tabs::make(__('seo-filament::seo-filament.locales.heading'))
            ->tabs($tabs)
            ->activeTab($active === false ? 1 : $active + 1)
            ->contained(false)
            ->columnSpanFull();
    }

    /**
     * How many of the editable fields carry a value in the current form state
     * of one locale. A `Get` resolves paths relative to the component's
     * CONTAINER (the `seo_meta` group), not to the tab's own statePath, so
     * the locale is spelled out.
     *
     * @param  array<int, string>  $only
     */
    protected static function filledCount(Get $get, array $only, string $locale): int
    {
        $filled = 0;

        foreach ($only as $field) {
            $value = $get($locale.'.'.$field);

            if (is_array($value) ? array_filter($value) !== [] : ($value !== null && $value !== '')) {
                $filled++;
            }
        }

        return $filled;
    }

    /**
     * A stored row as form state: only the shown fields, focus keywords
     * flattened from the stored [{keyword, is_primary}] objects into the plain
     * strings the TagsInput edits.
     *
     * @param  array<int, string>  $only
     * @return array<string, mixed>
     */
    protected static function rowToState(?Model $meta, array $only): array
    {
        $state = $meta?->only($only) ?: [];

        if (array_key_exists('focus_keywords', $state)) {
            $state['focus_keywords'] = self::keywordsToStrings($state['focus_keywords']);
        }

        return $state;
    }

    /**
     * Form state as a storable row: empty strings become null, a single-file
     * upload collapses to its path, focus keywords go back to the structured
     * shape the core reads.
     *
     * @param  array<string, mixed>  $state
     * @param  array<int, string>  $only
     * @return array<string, mixed>
     */
    protected static function stateToRow(array $state, array $only): array
    {
        return collect($state)
            ->only($only)
            ->map(function (mixed $value, string $key): mixed {
                // focus_keywords is legitimately an array of
                // strings — turn it back into the stored
                // [{keyword, is_primary}] shape, not the first
                // element (that collapse is for file uploads).
                if ($key === 'focus_keywords') {
                    $keywords = self::stringsToKeywords($value);

                    return $keywords === [] ? null : $keywords;
                }

                if (is_array($value)) {
                    $value = Arr::first($value);
                }

                return ($value === '' || $value === null) ? null : $value;
            })
            ->all();
    }

    /**
     * Write one locale's row: update the existing row (an emptied field clears
     * its column), create one only when something was entered — an untouched
     * language never gets a placeholder row.
     *
     * @param  array<string, mixed>  $row
     */
    protected static function persistRow(Model $target, string $locale, array $row): void
    {
        $existing = self::currentMeta($target, $locale);

        if ($existing) {
            $existing->update($row);
        } elseif (array_filter($row) !== []) {
            $target->seoMeta()->create($row + ['locale' => $locale]);
        }
    }

    /**
     * @return array<string, Field>
     */
    protected static function fields(?string $locale = null): array
    {
        $fields = static::baseFields($locale);

        foreach (static::$fieldModifiers as $name => $modifiers) {
            if (! isset($fields[$name])) {
                continue;
            }

            foreach ($modifiers as $modifier) {
                $fields[$name] = $modifier($fields[$name]) ?? $fields[$name];
            }
        }

        return $fields;
    }

    /**
     * @param  string|null  $locale  The language this set of fields edits — the
     *                               script hint for the counters' budget while
     *                               a field is still empty. Null = app locale.
     * @return array<string, Field>
     */
    protected static function baseFields(?string $locale = null): array
    {
        return [
            'title' => TextInput::make('title')
                ->label(__('seo-filament::seo-filament.fields.title'))
                ->prefixIcon('heroicon-o-document-text')
                ->maxLength(255)
                ->live(debounce: 500)
                ->helperText(fn (?string $state): HtmlString => self::titleCounter($state, $locale))
                ->columnSpan(2),

            'description' => Textarea::make('description')
                ->label(__('seo-filament::seo-filament.fields.description'))
                ->rows(3)
                ->maxLength(500)
                ->live(debounce: 500)
                ->helperText(fn (?string $state): HtmlString => self::descriptionCounter($state, $locale))
                ->columnSpan(2),

            'focus_keywords' => TagsInput::make('focus_keywords')
                ->label(__('seo-filament::seo-filament.fields.focus_keywords'))
                ->placeholder(__('seo-filament::seo-filament.fields.focus_keywords_placeholder'))
                ->helperText(__('seo-filament::seo-filament.fields.focus_keywords_help'))
                ->columnSpan(2),

            'canonical' => TextInput::make('canonical')
                ->label(__('seo-filament::seo-filament.fields.canonical'))
                ->prefixIcon('heroicon-o-link')
                ->url()
                ->helperText(__('seo-filament::seo-filament.fields.canonical_help'))
                ->columnSpan(2),

            'robots' => Select::make('robots')
                ->label(__('seo-filament::seo-filament.fields.robots'))
                ->prefixIcon('heroicon-o-shield-check')
                ->native(false)
                ->placeholder(__('seo-filament::seo-filament.fields.robots_placeholder'))
                ->options([
                    'index, follow' => __('seo-filament::seo-filament.robots_options.index_follow'),
                    'index, nofollow' => __('seo-filament::seo-filament.robots_options.index_nofollow'),
                    'noindex, follow' => __('seo-filament::seo-filament.robots_options.noindex_follow'),
                    'noindex, nofollow' => __('seo-filament::seo-filament.robots_options.noindex_nofollow'),
                ])
                ->columnSpan(2),

            'og_image' => FileUpload::make('og_image')
                ->label(__('seo-filament::seo-filament.fields.og_image'))
                ->image()
                ->directory('seo')
                ->visibility('public')
                ->helperText(__('seo-filament::seo-filament.fields.og_image_help', [
                    'width' => SEOWarningEvaluator::IDEAL_SOCIAL_IMAGE_WIDTH,
                    'height' => SEOWarningEvaluator::IDEAL_SOCIAL_IMAGE_HEIGHT,
                ]))
                ->columnSpan(2),
        ];
    }

    /**
     * The live "n / max characters" counter under the title field. The budget
     * comes from the core {@see LengthPolicy} for the script of the value
     * being typed (60 for Latin, ~30 for CJK) with the field's locale as the
     * hint for an empty field, and the count is in graphemes — the same
     * numbers the audit and the Pro scan report.
     */
    protected static function titleCounter(?string $state, ?string $locale = null): HtmlString
    {
        $locale ??= app()->getLocale();
        $policy = LengthPolicy::for($state, $locale);

        return self::counter(
            $policy->length($state),
            $policy->titleMax,
            app(SEOWarningEvaluator::class)->evaluateTitle($state, $state, $locale),
        );
    }

    protected static function currentMeta(Model $target, string $locale): ?Model
    {
        return method_exists($target, 'seoMetaForLocale')
            ? $target->seoMetaForLocale($locale)->first()
            : $target->seoMeta()->where('locale', $locale)->first();
    }

    protected static function descriptionCounter(?string $state, ?string $locale = null): HtmlString
    {
        $locale ??= app()->getLocale();
        $policy = LengthPolicy::for($state, $locale);

        return self::counter(
            $policy->length($state),
            $policy->descriptionMax,
            app(SEOWarningEvaluator::class)->evaluateDescription($state, $state, $locale),
        );
    }

    /**
     * Render "n / max characters" with the core evaluator's verdict attached.
     *
     * @param  int  $length  The value's length in graphemes
     * @param  int  $max  The script-aware budget
     * @param  array<int, array{level: string, key: string, message: string}>  $warnings
     */
    protected static function counter(int $length, int $max, array $warnings): HtmlString
    {
        $counter = __('seo-filament::seo-filament.fields.counter', ['length' => $length, 'max' => $max]);

        foreach ($warnings as $warning) {
            if (str_ends_with($warning['key'], '_too_long')) {
                return new HtmlString(
                    '<span style="color: #d97706; font-weight: 500;">'
                    .e($counter.' — '.$warning['message'])
                    .'</span>'
                );
            }

            if (str_ends_with($warning['key'], '_is_fallback')) {
                return new HtmlString(e($counter.' — '.$warning['message']));
            }
        }

        return new HtmlString(e($counter));
    }

    /**
     * Flatten the stored focus-keyword shape into plain strings for the
     * TagsInput. Accepts both the structured [{keyword, is_primary}] objects
     * the core stores and a plain string array (defensive).
     *
     * @return array<int, string>
     */
    protected static function keywordsToStrings(mixed $state): array
    {
        if (! is_array($state)) {
            return [];
        }

        return array_values(array_filter(array_map(static function (mixed $keyword): ?string {
            if (is_array($keyword)) {
                $value = $keyword['keyword'] ?? null;

                return is_string($value) && trim($value) !== '' ? $value : null;
            }

            return is_string($keyword) && trim($keyword) !== '' ? $keyword : null;
        }, $state)));
    }

    /**
     * Turn the TagsInput's plain strings back into the stored structured shape
     * the core reads ({@see SEOData::$focusKeywords},
     * {@see SEOMeta::getPrimaryKeyword()}). The first
     * keyword is marked primary.
     *
     * @return array<int, array{keyword: string, is_primary: bool}>
     */
    protected static function stringsToKeywords(mixed $state): array
    {
        if (! is_array($state)) {
            return [];
        }

        $keywords = [];

        foreach ($state as $value) {
            $value = is_string($value) ? trim($value) : '';

            if ($value === '') {
                continue;
            }

            $keywords[] = ['keyword' => $value, 'is_primary' => $keywords === []];
        }

        return $keywords;
    }
}
