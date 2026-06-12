<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Forms;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
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
 */
class SEOFields
{
    public const FIELDS = ['title', 'description', 'canonical', 'robots', 'og_image'];

    /** @var array<string, array<int, \Closure(Field): ?Field>> */
    protected static array $fieldModifiers = [];

    /**
     * Register a modifier applied to one named field (see self::FIELDS)
     * every time the section is built. This is the extension point for
     * add-on packages - e.g. the Pro AI suggestion actions attach
     * themselves to 'title' and 'description' through it. The closure
     * receives the built Field and returns it (returning null keeps the
     * field as passed in).
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
     */
    public static function make(?array $only = null): Section
    {
        $only ??= self::FIELDS;

        return Section::make('SEO')
            ->icon('heroicon-o-magnifying-glass')
            ->description('Search engine and social sharing metadata. Empty fields fall back to values derived from the content.')
            ->schema([
                Group::make([
                    Group::make(array_values(Arr::only(self::fields(), $only)))
                        ->columns(2)
                        ->columnSpanFull(),

                    View::make('seo-filament::seo-snippet-preview')
                        ->columnSpanFull(),

                    View::make('seo-filament::seo-source-indicators')
                        ->columnSpanFull(),
                ])
                    ->statePath('seo_meta')
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->afterStateHydrated(function (Group $component, ?Model $record) use ($only): void {
                        $meta = $record && method_exists($record, 'seoMeta') ? $record->seoMeta : null;

                        $component->getChildSchema()->fill(
                            $meta?->only($only) ?: [],
                        );
                    })
                    ->saveRelationshipsUsing(function (Group $component, Model $record) use ($only): void {
                        if (! method_exists($record, 'seoMeta')) {
                            return;
                        }

                        // getState() (not the raw state) runs the dehydration
                        // hooks, which is what persists a freshly uploaded
                        // og_image file and turns it into a storable path.
                        $state = collect($component->getChildSchema()->getState())
                            ->only($only)
                            ->map(function (mixed $value): mixed {
                                if (is_array($value)) {
                                    $value = Arr::first($value);
                                }

                                return ($value === '' || $value === null) ? null : $value;
                            })
                            ->all();

                        $existing = $record->seoMeta()->first();

                        if ($existing) {
                            $existing->update($state);
                        } elseif (array_filter($state) !== []) {
                            $record->seoMeta()->create($state + ['locale' => app()->getLocale()]);
                        }

                        $record->unsetRelation('seoMeta');
                    }),
            ])
            ->collapsible()
            ->columnSpanFull();
    }

    /**
     * @return array<string, Field>
     */
    protected static function fields(): array
    {
        $fields = static::baseFields();

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
     * @return array<string, Field>
     */
    protected static function baseFields(): array
    {
        return [
            'title' => TextInput::make('title')
                ->label('SEO title')
                ->prefixIcon('heroicon-o-document-text')
                ->maxLength(255)
                ->live(debounce: 500)
                ->helperText(fn (?string $state): HtmlString => self::titleCounter($state))
                ->columnSpan(2),

            'description' => Textarea::make('description')
                ->label('SEO description')
                ->rows(3)
                ->maxLength(500)
                ->live(debounce: 500)
                ->helperText(fn (?string $state): HtmlString => self::descriptionCounter($state))
                ->columnSpan(2),

            'canonical' => TextInput::make('canonical')
                ->label('Canonical URL')
                ->prefixIcon('heroicon-o-link')
                ->url()
                ->helperText('Leave empty for the automatic canonical URL (the page URL without query parameters).')
                ->columnSpan(2),

            'robots' => Select::make('robots')
                ->label('Robots directive')
                ->prefixIcon('heroicon-o-shield-check')
                ->native(false)
                ->placeholder('Automatic (site default)')
                ->options([
                    'index, follow' => 'Index, follow links',
                    'index, nofollow' => 'Index, don\'t follow links',
                    'noindex, follow' => 'Don\'t index, follow links',
                    'noindex, nofollow' => 'Don\'t index, don\'t follow links',
                ]),

            'og_image' => FileUpload::make('og_image')
                ->label('Social sharing image')
                ->image()
                ->directory('seo')
                ->visibility('public')
                ->helperText('Used for og:image and twitter:image. Ideal size: '
                    .SEOWarningEvaluator::IDEAL_SOCIAL_IMAGE_WIDTH.'x'
                    .SEOWarningEvaluator::IDEAL_SOCIAL_IMAGE_HEIGHT.' px.'),
        ];
    }

    protected static function titleCounter(?string $state): HtmlString
    {
        return self::counter(
            $state,
            SEOWarningEvaluator::TITLE_MAX_LENGTH,
            app(SEOWarningEvaluator::class)->evaluateTitle($state, $state),
        );
    }

    protected static function descriptionCounter(?string $state): HtmlString
    {
        return self::counter(
            $state,
            SEOWarningEvaluator::DESCRIPTION_MAX_LENGTH,
            app(SEOWarningEvaluator::class)->evaluateDescription($state, $state),
        );
    }

    /**
     * Render "n / max characters" with the core evaluator's verdict attached.
     *
     * @param  array<int, array{level: string, key: string, message: string}>  $warnings
     */
    protected static function counter(?string $state, int $max, array $warnings): HtmlString
    {
        $counter = mb_strlen($state ?? '').' / '.$max.' characters';

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
}
