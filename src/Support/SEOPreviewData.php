<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Rankbeam\Seo\Services\SEOWarningEvaluator;

/**
 * Server-side payload for the editorial SEO snippet preview (SERP + social).
 *
 * The preview is *live* on the client (title / description entangled with the
 * form so they update as you type), but three things can only be resolved
 * server-side and are computed here:
 *
 *  - the content/config FALLBACK title, description, and social image — the
 *    same resolver-ordered chain the public page would use, so the preview
 *    matches what actually renders (this delegates to {@see SEOFieldSources},
 *    which mirrors the core resolver's "last non-null wins" merge);
 *  - a loadable URL for the effective social image (a Filament upload is
 *    stored as a bare disk path like `seo/cover.jpg`, which is not a usable
 *    `<img src>` until mapped through its disk);
 *  - KNOWN-LOCAL image dimensions via `getimagesize`, with the verdict
 *    (too-small / not-ideal) decided by the shared {@see SEOWarningEvaluator}
 *    thresholds so audit, preview, and scan never disagree.
 *
 * Image-dimension state is explicit:
 *  - `known-local`  — the file resolved to a local path and was measured here;
 *  - `unknown`      — remote / unresolvable locally, so the client measures it
 *                     in the browser (tolerant of CORS / signed-URL / private
 *                     disk / temp-upload failure — a failed image never breaks
 *                     the form);
 *  - `unavailable`  — there is no effective image at all.
 *
 * The payload reflects the model's *saved* state. Whether a value is manual or
 * a fallback in the *current, unsaved* form is decided live on the client from
 * the entangled field state — this class never claims a form value came from
 * the database.
 */
class SEOPreviewData
{
    public function __construct(
        protected SEOFieldSources $sources,
    ) {}

    /**
     * Build the preview payload for a model (or null on a create form / a
     * null-resolved related target).
     *
     * @return array{
     *     siteName: string,
     *     titleSuffix: string,
     *     url: string,
     *     fallbackTitle: string,
     *     fallbackDescription: string,
     *     image: array{url: ?string, source: string, state: string, width: ?int, height: ?int, warning: ?string},
     *     thresholds: array{titleMax: int, descMax: int, minWidth: int, minHeight: int, idealWidth: int, idealHeight: int},
     * }
     */
    public function forModel(?Model $model, ?string $locale = null): array
    {
        $base = [
            'siteName' => (string) config('seo.site_name', config('app.name', '')),
            'titleSuffix' => (string) (config('seo.title_suffix', '') ?? ''),
            'url' => $this->resolveUrl($model),
            'fallbackTitle' => '',
            'fallbackDescription' => '',
            'image' => $this->emptyImage(),
            'thresholds' => $this->thresholds(),
        ];

        if (! $model instanceof Model || ! $model->exists || ! method_exists($model, 'seoMeta')) {
            return $base;
        }

        $sources = $this->sources->forModel($model, $locale);

        $base['fallbackTitle'] = (string) ($sources['title']['fallback'] ?? '');
        $base['fallbackDescription'] = (string) ($sources['description']['fallback'] ?? '');
        $base['image'] = $this->resolveImage($sources['og_image'] ?? []);

        return $base;
    }

    /**
     * The shared editorial thresholds, sourced from the core evaluator so the
     * preview, the `seo:audit`, and the Pro scan all agree.
     *
     * @return array{titleMax: int, descMax: int, minWidth: int, minHeight: int, idealWidth: int, idealHeight: int}
     */
    public function thresholds(): array
    {
        return [
            'titleMax' => SEOWarningEvaluator::TITLE_MAX_LENGTH,
            'descMax' => SEOWarningEvaluator::DESCRIPTION_MAX_LENGTH,
            'minWidth' => SEOWarningEvaluator::MIN_SOCIAL_IMAGE_WIDTH,
            'minHeight' => SEOWarningEvaluator::MIN_SOCIAL_IMAGE_HEIGHT,
            'idealWidth' => SEOWarningEvaluator::IDEAL_SOCIAL_IMAGE_WIDTH,
            'idealHeight' => SEOWarningEvaluator::IDEAL_SOCIAL_IMAGE_HEIGHT,
        ];
    }

    /**
     * The page URL with any query string stripped (the SERP card shows a clean
     * URL), falling back to the app URL when the model exposes none.
     */
    protected function resolveUrl(?Model $model): string
    {
        if ($model instanceof Model && $model->exists && method_exists($model, 'getUrlForSEO')) {
            $url = (string) $model->getUrlForSEO();

            if ($url !== '') {
                return strtok($url, '?') ?: $url;
            }
        }

        return (string) config('app.url');
    }

    /**
     * Resolve the effective social image from the resolver-ordered og_image
     * provenance (manual seo_meta first, then the content/config fallback).
     *
     * @param  array{effective?: ?string, manual?: ?string, fallback?: ?string}  $info
     * @return array{url: ?string, source: string, state: string, width: ?int, height: ?int, warning: ?string}
     */
    protected function resolveImage(array $info): array
    {
        $manual = $this->blankToNull($info['manual'] ?? null);
        $fallback = $this->blankToNull($info['fallback'] ?? null);

        if ($manual !== null) {
            return $this->describeImage($this->toImageUrl($manual), 'manual');
        }

        if ($fallback !== null) {
            // Fallback values come from the resolver already normalized
            // (absolute URL or a root-relative path), so they are usable as-is.
            return $this->describeImage($fallback, 'fallback');
        }

        return $this->emptyImage();
    }

    /**
     * Describe an image URL: its known-local dimensions (if resolvable to a
     * local file) and the shared-threshold verdict.
     *
     * @return array{url: ?string, source: string, state: string, width: ?int, height: ?int, warning: ?string}
     */
    protected function describeImage(string $url, string $source): array
    {
        $dimensions = $this->localDimensions($url);

        if ($dimensions === null) {
            // Could be remote, signed, on a private disk, or otherwise not
            // locally readable — defer measurement to the browser.
            return [
                'url' => $url,
                'source' => $source,
                'state' => 'unknown',
                'width' => null,
                'height' => null,
                'warning' => null,
            ];
        }

        return [
            'url' => $url,
            'source' => $source,
            'state' => 'known-local',
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
            'warning' => $this->dimensionWarning($dimensions['width'], $dimensions['height']),
        ];
    }

    /**
     * @return array{url: null, source: string, state: string, width: null, height: null, warning: null}
     */
    protected function emptyImage(): array
    {
        return [
            'url' => null,
            'source' => 'none',
            'state' => 'unavailable',
            'width' => null,
            'height' => null,
            'warning' => null,
        ];
    }

    /**
     * The dimension verdict using the shared evaluator thresholds (identical
     * semantics to {@see SEOWarningEvaluator::evaluateImage()}).
     */
    protected function dimensionWarning(int $width, int $height): ?string
    {
        if ($width < SEOWarningEvaluator::MIN_SOCIAL_IMAGE_WIDTH || $height < SEOWarningEvaluator::MIN_SOCIAL_IMAGE_HEIGHT) {
            return 'too_small';
        }

        if ($width < SEOWarningEvaluator::IDEAL_SOCIAL_IMAGE_WIDTH || $height < SEOWarningEvaluator::IDEAL_SOCIAL_IMAGE_HEIGHT) {
            return 'not_ideal';
        }

        return null;
    }

    /**
     * Turn a manually stored og_image value into a loadable URL. A Filament
     * upload is persisted as a bare disk path (e.g. `seo/cover.jpg`) and must
     * be mapped through its disk; values that are already absolute or
     * root-relative are used verbatim.
     */
    protected function toImageUrl(string $value): string
    {
        if ($this->isAbsoluteOrRooted($value)) {
            return $value;
        }

        try {
            return Storage::disk($this->uploadDisk())->url($value);
        } catch (\Throwable) {
            return $value;
        }
    }

    /**
     * Detect the dimensions of a locally resolvable image, mirroring the core
     * evaluator but also resolving bare Filament-upload paths through the
     * upload disk (the core resolver absolutizes those via `url()`, which does
     * not point at the public disk). Remote images are never fetched.
     *
     * @return array{width: int, height: int}|null
     */
    protected function localDimensions(string $value): ?array
    {
        $path = $this->localPath($value);

        if ($path === null || ! is_file($path)) {
            return null;
        }

        $size = @getimagesize($path);

        if (! is_array($size)) {
            return null;
        }

        return [
            'width' => (int) ($size[0] ?? 0),
            'height' => (int) ($size[1] ?? 0),
        ];
    }

    /**
     * Map an image URL/path to a local filesystem path, if possible. Absolute
     * URLs resolve only when their host matches app.url (no remote fetches).
     */
    protected function localPath(string $value): ?string
    {
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            $parsed = parse_url($value);
            $appParsed = parse_url((string) config('app.url'));

            if (($parsed['host'] ?? null) !== ($appParsed['host'] ?? null)) {
                return null;
            }

            $path = $parsed['path'] ?? null;

            if (! is_string($path) || $path === '') {
                return null;
            }

            return $this->mapPublicPath($path);
        }

        if (str_starts_with($value, '//')) {
            // Protocol-relative — treated as remote (host unknown), not local.
            return null;
        }

        if (str_starts_with($value, '/')) {
            return $this->mapPublicPath($value);
        }

        // A bare path is a disk-relative upload key.
        try {
            return Storage::disk($this->uploadDisk())->path($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Map a root-relative web path to a local file: `/storage/...` resolves to
     * the public disk, anything else to `public/`.
     */
    protected function mapPublicPath(string $path): string
    {
        if (str_starts_with($path, '/storage/')) {
            return Storage::disk('public')->path(substr($path, strlen('/storage/')));
        }

        return public_path(ltrim($path, '/'));
    }

    protected function isAbsoluteOrRooted(string $value): bool
    {
        return str_starts_with($value, 'http://')
            || str_starts_with($value, 'https://')
            || str_starts_with($value, '//')
            || str_starts_with($value, '/');
    }

    /**
     * The disk Filament's og_image FileUpload writes to (it does not override
     * the field disk, so the panel/Filament default applies).
     */
    protected function uploadDisk(): string
    {
        return (string) config('filament.default_filesystem_disk', config('filesystems.default', 'public'));
    }

    protected function blankToNull(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return trim($value) === '' ? null : $value;
    }
}
