<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Support;

use Fibonoir\LaravelSEO\Data\SEOData;
use Fibonoir\LaravelSEO\Services\SEOComputedBuilder;
use Fibonoir\LaravelSEO\Services\SEODefaultsRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Reports which resolver layer produced each effective SEO value for a model.
 *
 * The core SEOResolver merges layers with "last non-null wins" semantics
 * (config -> global defaults -> model-type defaults -> route defaults ->
 * computed -> explicit). This inspector walks the same layers from the
 * highest priority down and records, per field, the first layer that
 * provides a value - so admin UIs can label a value as manually entered
 * versus inherited from a fallback layer.
 *
 * The route-defaults layer is intentionally skipped: inside an admin panel
 * the current request route is the panel route, never the model's public
 * route, so resolving it would attribute defaults that the public page
 * never receives.
 */
class SEOFieldSources
{
    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_CONTENT = 'content';

    public const SOURCE_MODEL_DEFAULTS = 'model_defaults';

    public const SOURCE_GLOBAL_DEFAULTS = 'global_defaults';

    public const SOURCE_CONFIG = 'config';

    public const SOURCE_URL = 'url';

    public const SOURCE_NONE = 'none';

    public const LABELS = [
        self::SOURCE_MANUAL => 'Manual',
        self::SOURCE_CONTENT => 'Content fallback',
        self::SOURCE_MODEL_DEFAULTS => 'Model-type default',
        self::SOURCE_GLOBAL_DEFAULTS => 'Global default',
        self::SOURCE_CONFIG => 'Site config',
        self::SOURCE_URL => 'Derived from URL',
        self::SOURCE_NONE => 'Not set',
    ];

    public function __construct(
        protected SEODefaultsRepository $defaults,
        protected SEOComputedBuilder $computed,
    ) {}

    /**
     * Resolve per-field provenance for a model using the HasSEO trait.
     *
     * @return array<string, array{effective: ?string, manual: ?string, fallback: ?string, source: string, source_label: string, is_manual: bool}>
     */
    public function forModel(Model $model, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        $manual = method_exists($model, 'seoMeta') ? SEOData::fromModel($model) : new SEOData;
        $computed = $this->computed->fromModel($model, $locale);
        $modelDefaults = $this->defaults->forModelType($model, $locale);
        $globalDefaults = $this->defaults->global($locale);

        $config = new SEOData(
            robots: config('seo.default_robots', 'index,follow'),
            ogImage: config('seo.default_og_image'),
        );

        $result = [];

        foreach (['title', 'description', 'og_image', 'robots', 'canonical'] as $field) {
            $result[$field] = $this->inspectField(
                $field,
                $manual,
                $computed,
                $modelDefaults,
                $globalDefaults,
                $config,
                $model,
            );
        }

        return $result;
    }

    /**
     * @return array{effective: ?string, manual: ?string, fallback: ?string, source: string, source_label: string, is_manual: bool}
     */
    protected function inspectField(
        string $field,
        SEOData $manual,
        SEOData $computed,
        ?SEOData $modelDefaults,
        ?SEOData $globalDefaults,
        SEOData $config,
        Model $model,
    ): array {
        $manualValue = $this->fieldValue($manual, $field);

        // Highest-priority layer first; the first non-null value is the one
        // the resolver's merge chain would keep.
        $layers = [
            self::SOURCE_MANUAL => $manualValue,
            self::SOURCE_CONTENT => $this->fieldValue($computed, $field),
            self::SOURCE_MODEL_DEFAULTS => $modelDefaults ? $this->fieldValue($modelDefaults, $field) : null,
            self::SOURCE_GLOBAL_DEFAULTS => $globalDefaults ? $this->fieldValue($globalDefaults, $field) : null,
            self::SOURCE_CONFIG => $this->fieldValue($config, $field),
        ];

        // SEOComputedBuilder folds config('seo.default_og_image') into its
        // image fallback chain; re-attribute that value to the config layer
        // so "Content fallback" only ever means model-derived content.
        if ($field === 'og_image' && $layers[self::SOURCE_CONTENT] !== null && $layers[self::SOURCE_CONFIG] !== null) {
            if ($this->isConfigDefaultImage($layers[self::SOURCE_CONTENT])) {
                $layers[self::SOURCE_CONTENT] = null;
            }
        }

        // The canonical has no content/defaults layers: the resolver derives
        // it from getUrlForSEO() (query-stripped) when not set manually.
        if ($field === 'canonical' && $manualValue === null && method_exists($model, 'getUrlForSEO')) {
            $url = $model->getUrlForSEO();

            if (is_string($url) && $url !== '') {
                $layers[self::SOURCE_URL] = strtok($url, '?') ?: $url;
            }
        }

        $effective = null;
        $source = self::SOURCE_NONE;
        $fallback = null;

        foreach ($layers as $layerName => $value) {
            if ($value === null || trim($value) === '') {
                continue;
            }

            if ($effective === null) {
                $effective = $value;
                $source = $layerName;
            }

            if ($fallback === null && $layerName !== self::SOURCE_MANUAL) {
                $fallback = $value;
            }

            if ($effective !== null && $fallback !== null) {
                break;
            }
        }

        // Mirror the resolver's title post-processing so the indicator shows
        // the value as it will actually render.
        if ($field === 'title' && $effective !== null) {
            $suffix = config('seo.title_suffix');

            if ($suffix && ! str_ends_with($effective, $suffix)) {
                $effective .= $suffix;
            }
        }

        return [
            'effective' => $effective,
            'manual' => $manualValue,
            'fallback' => $fallback,
            'source' => $source,
            'source_label' => self::LABELS[$source],
            'is_manual' => $source === self::SOURCE_MANUAL,
        ];
    }

    protected function fieldValue(SEOData $data, string $field): ?string
    {
        $value = match ($field) {
            'title' => $data->title,
            'description' => $data->description,
            'og_image' => $data->ogImage,
            'robots' => $data->robots,
            'canonical' => $data->canonical,
            default => null,
        };

        return ($value === null || trim($value) === '') ? null : $value;
    }

    /**
     * Whether a computed image value is just the normalized config default.
     */
    protected function isConfigDefaultImage(string $value): bool
    {
        $default = config('seo.default_og_image');

        if (! is_string($default) || $default === '') {
            return false;
        }

        if ($value === $default) {
            return true;
        }

        // SEOComputedBuilder normalizes relative URLs to absolute ones.
        return str_starts_with($default, '/') && str_ends_with($value, $default);
    }
}
