@php
    use Rankbeam\Seo\Services\SEOWarningEvaluator;
    use Rankbeam\Seo\Filament\Support\SEOFieldSources;

    $record = $getRecord();
    $statePath = $getStatePath();

    $sources = $record && $record->exists ? app(SEOFieldSources::class)->forModel($record) : null;

    $fallbackTitle = $sources['title']['fallback'] ?? '';
    $fallbackDescription = $sources['description']['fallback'] ?? '';

    $siteName = config('seo.site_name', config('app.name', ''));
    $titleSuffix = config('seo.title_suffix', '') ?? '';

    $url = $record && $record->exists && method_exists($record, 'getUrlForSEO')
        ? (strtok((string) $record->getUrlForSEO(), '?') ?: (string) $record->getUrlForSEO())
        : (string) config('app.url');
@endphp

<div
    x-data="{
        seoTitle: $wire.$entangle('{{ $statePath }}.title'),
        seoDesc: $wire.$entangle('{{ $statePath }}.description'),
        fallbackTitle: @js($fallbackTitle),
        fallbackDescription: @js($fallbackDescription),
        siteName: @js($siteName),
        titleSuffix: @js($titleSuffix),
        url: @js($url),
        titleMax: @js(SEOWarningEvaluator::TITLE_MAX_LENGTH),
        descMax: @js(SEOWarningEvaluator::DESCRIPTION_MAX_LENGTH),

        truncate(text, max) {
            if (!text) return '';
            return text.length > max ? text.substring(0, max - 3) + '...' : text;
        },

        get effectiveTitleRaw() {
            const manual = ((this.seoTitle ?? '') + '').trim();
            return manual || this.fallbackTitle || '';
        },

        get effectiveTitle() {
            const raw = this.effectiveTitleRaw;
            if (!raw) return this.siteName;
            if (this.titleSuffix && !raw.endsWith(this.titleSuffix)) return raw + this.titleSuffix;
            return raw;
        },

        get effectiveDescription() {
            const manual = ((this.seoDesc ?? '') + '').trim();
            return manual || this.fallbackDescription || '';
        },

        get serpDomain() {
            try {
                return new URL(this.url).hostname;
            } catch {
                return this.url;
            }
        },

        get hasManualTitle() {
            return ((this.seoTitle ?? '') + '').trim() !== '';
        },

        get hasManualDesc() {
            return ((this.seoDesc ?? '') + '').trim() !== '';
        },

        get warnings() {
            const w = [];
            const titleLen = (this.effectiveTitle || '').length;
            const descLen = (this.effectiveDescription || '').length;

            if (titleLen > this.titleMax) {
                w.push({ type: 'warning', msg: 'The title is ' + titleLen + ' characters long (recommended max: ' + this.titleMax + '). It may be truncated on Google.' });
            }
            if (descLen > this.descMax) {
                w.push({ type: 'warning', msg: 'The description is ' + descLen + ' characters long (recommended max: ' + this.descMax + '). It may be truncated.' });
            }
            if (!this.hasManualTitle) {
                w.push({ type: 'info', msg: 'No SEO title set — the content title will be used as a fallback.' });
            }
            if (!this.hasManualDesc) {
                w.push({ type: 'info', msg: 'No SEO description set — one will be generated automatically from the content.' });
            }
            return w;
        }
    }"
    class="seo-snippet-preview"
>
    <div class="serp-label">Search result preview</div>

    <div class="serp-card">
        <div class="serp-breadcrumb">
            <div class="serp-url-group">
                <span class="serp-site-name" x-text="siteName"></span>
                <span class="serp-url" x-text="url"></span>
            </div>
        </div>
        <h3 class="serp-title" x-text="truncate(effectiveTitle, titleMax)"></h3>
        <p class="serp-description" x-text="truncate(effectiveDescription, descMax) || 'No description available.'"></p>
    </div>

    <div x-show="warnings.length > 0" x-cloak class="seo-warnings-panel">
        <template x-for="(warn, idx) in warnings" :key="idx">
            <div class="seo-warning-item" :class="'seo-warning-' + warn.type">
                <span x-text="warn.msg"></span>
            </div>
        </template>
    </div>
</div>

<style>
    .seo-snippet-preview {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .serp-label {
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: rgb(156 163 175);
    }

    .serp-card {
        padding: 1rem 1.25rem;
        border-radius: 0.75rem;
        border: 1px solid rgb(229 231 235);
        background: white;
        max-width: 600px;
    }

    .dark .serp-card {
        background: rgb(17 24 39);
        border-color: rgb(55 65 81);
    }

    .serp-breadcrumb {
        margin-bottom: 0.375rem;
    }

    .serp-url-group {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .serp-site-name {
        font-size: 0.875rem;
        color: rgb(32 33 36);
        line-height: 1.3;
    }

    .dark .serp-site-name {
        color: rgb(209 213 219);
    }

    .serp-url {
        font-size: 0.75rem;
        color: rgb(77 81 86);
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dark .serp-url {
        color: rgb(156 163 175);
    }

    .serp-title {
        font-size: 1.25rem;
        font-weight: 400;
        line-height: 1.3;
        color: rgb(26 13 171);
        margin: 0.25rem 0;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .dark .serp-title {
        color: rgb(138 180 248);
    }

    .serp-description {
        font-size: 0.875rem;
        line-height: 1.58;
        color: rgb(77 81 86);
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .dark .serp-description {
        color: rgb(189 193 198);
    }

    .seo-warnings-panel {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid rgb(229 231 235);
        background: rgb(249 250 251);
        max-width: 600px;
    }

    .dark .seo-warnings-panel {
        border-color: rgb(55 65 81);
        background: rgb(31 41 55);
    }

    .seo-warning-item {
        padding: 0.375rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.8125rem;
        line-height: 1.4;
    }

    .seo-warning-warning {
        background: rgb(255 251 235);
        color: rgb(120 53 15);
    }

    .dark .seo-warning-warning {
        background: rgba(69 26 3 / 0.3);
        color: rgb(253 224 71);
    }

    .seo-warning-info {
        background: rgb(239 246 255);
        color: rgb(30 64 175);
    }

    .dark .seo-warning-info {
        background: rgba(30 58 138 / 0.3);
        color: rgb(191 219 254);
    }
</style>
