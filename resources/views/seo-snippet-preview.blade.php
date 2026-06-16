@php
    /**
     * Editorial SEO preview — a tabbed (Google SERP / social card) live editor.
     *
     * Title, description and URL update live as the form changes (entangled).
     * The content/config fallbacks, the effective social image URL and its
     * KNOWN-LOCAL dimensions are computed server-side in {@see \Rankbeam\Seo\Filament\Support\SEOPreviewData}
     * (passed in as $preview); the shared warning thresholds come from the core
     * SEOWarningEvaluator so audit / preview / scan agree. A remote or otherwise
     * non-local image is measured in the browser and a failed load degrades to a
     * placeholder — it never breaks the form.
     *
     * @var array $preview              Payload from SEOPreviewData::forModel()
     * @var bool  $previewHasImageField Whether the og_image field is editable here
     */
    $statePath = $getStatePath();
    $image = $preview['image'];
    $thresholds = $preview['thresholds'];
@endphp

<div
    x-data="{
        seoTitle: $wire.$entangle('{{ $statePath }}.title'),
        seoDesc: $wire.$entangle('{{ $statePath }}.description'),
        @if ($previewHasImageField)
            ogImageState: $wire.$entangle('{{ $statePath }}.og_image'),
        @else
            ogImageState: null,
        @endif

        fallbackTitle: @js($preview['fallbackTitle']),
        fallbackDescription: @js($preview['fallbackDescription']),
        siteName: @js($preview['siteName']),
        titleSuffix: @js($preview['titleSuffix']),
        url: @js($preview['url']),
        image: @js($image),
        t: @js($thresholds),

        activeTab: 'serp',
        imgError: false,
        measuredState: null,
        measuredWidth: 0,
        measuredHeight: 0,

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

        get socialTitle() {
            return this.effectiveTitleRaw || this.siteName;
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

        get hasManualImage() {
            const s = this.ogImageState;
            if (!s) return false;
            if (Array.isArray(s)) return s.length > 0;
            if (typeof s === 'object') return Object.keys(s).length > 0;
            return ((s + '').trim() !== '');
        },

        get imageUrl() {
            return this.image.url;
        },

        get showImage() {
            return !!this.imageUrl && !this.imgError;
        },

        // Explicit image-dimension state: known-local (measured server-side),
        // browser-measured (measured here), measuring (in flight), or
        // unavailable (no image, or a failed load).
        get dimensionState() {
            if (!this.imageUrl || this.imgError) return 'unavailable';
            if (this.image.state === 'known-local') return 'known-local';
            return this.measuredState ?? 'measuring';
        },

        get dimensionWidth() {
            return this.image.state === 'known-local' ? this.image.width : this.measuredWidth;
        },

        get dimensionHeight() {
            return this.image.state === 'known-local' ? this.image.height : this.measuredHeight;
        },

        measureImage() {
            this.imgError = false;
            this.measuredState = null;
            this.measuredWidth = 0;
            this.measuredHeight = 0;

            // The server already measured a known-local file; nothing to do.
            if (this.image.state === 'known-local') return;

            const src = this.imageUrl;
            if (!src) { this.measuredState = 'unavailable'; return; }

            const probe = new Image();
            probe.onload = () => {
                this.measuredWidth = probe.naturalWidth;
                this.measuredHeight = probe.naturalHeight;
                this.measuredState = 'browser-measured';
            };
            // Tolerant of CORS / signed-URL / private-disk / temp-upload
            // failure: a broken image degrades to a placeholder, never an error.
            probe.onerror = () => { this.measuredState = 'unavailable'; this.imgError = true; };
            probe.src = src;
        },

        // 'too_small' | 'not_ideal' | null — using the shared core thresholds.
        get imageDimensionWarning() {
            if (this.image.state === 'known-local') return this.image.warning;
            if (this.measuredState !== 'browser-measured') return null;
            const w = this.dimensionWidth, h = this.dimensionHeight;
            if (w < this.t.minWidth || h < this.t.minHeight) return 'too_small';
            if (w < this.t.idealWidth || h < this.t.idealHeight) return 'not_ideal';
            return null;
        },

        get titleSourceLabel() {
            return this.hasManualTitle ? 'Manual' : (this.fallbackTitle ? 'Content fallback' : 'Not set');
        },

        get descriptionSourceLabel() {
            return this.hasManualDesc ? 'Manual' : (this.fallbackDescription ? 'Content fallback' : 'Not set');
        },

        get imageSourceLabel() {
            if (this.hasManualImage) return 'Manual';
            return this.showImage ? 'Content fallback' : 'Not set';
        },

        get warnings() {
            const w = [];
            const titleLen = (this.effectiveTitle || '').length;
            const descLen = (this.effectiveDescription || '').length;

            if (titleLen > this.t.titleMax) {
                w.push({ type: 'warning', msg: 'The title is ' + titleLen + ' characters long (recommended max: ' + this.t.titleMax + '). It may be truncated on Google.' });
            }
            if (descLen > this.t.descMax) {
                w.push({ type: 'warning', msg: 'The description is ' + descLen + ' characters long (recommended max: ' + this.t.descMax + '). It may be truncated.' });
            }

            if (!this.showImage) {
                w.push({ type: 'danger', msg: 'No image available for social previews. Add an SEO image or a content image.' });
            } else {
                const dw = this.imageDimensionWarning;
                if (dw === 'too_small') {
                    w.push({ type: 'danger', msg: 'Image too small (' + this.dimensionWidth + '×' + this.dimensionHeight + '). Social platforms require at least ' + this.t.minWidth + '×' + this.t.minHeight + ' px.' });
                } else if (dw === 'not_ideal') {
                    w.push({ type: 'info', msg: 'Image is ' + this.dimensionWidth + '×' + this.dimensionHeight + ' px. The ideal size for social platforms is ' + this.t.idealWidth + '×' + this.t.idealHeight + ' px.' });
                }
            }

            if (!this.hasManualTitle) {
                w.push({ type: 'info', msg: 'No SEO title set — the content title will be used as a fallback.' });
            }
            if (!this.hasManualDesc) {
                w.push({ type: 'info', msg: 'No SEO description set — one will be generated automatically from the content.' });
            }
            @if ($previewHasImageField)
                if (!this.hasManualImage && this.showImage) {
                    w.push({ type: 'info', msg: 'No specific SEO image — the content image will be used as a fallback.' });
                }
            @endif

            return w;
        }
    }"
    x-init="measureImage()"
    class="seo-snippet-preview"
>
    <div class="seo-preview-tabs">
        <button
            type="button"
            class="seo-preview-tab"
            :class="{ 'seo-preview-tab-active': activeTab === 'serp' }"
            @click="activeTab = 'serp'"
        >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
            </svg>
            Google
        </button>
        <button
            type="button"
            class="seo-preview-tab"
            :class="{ 'seo-preview-tab-active': activeTab === 'social' }"
            @click="activeTab = 'social'"
        >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
            </svg>
            Social
        </button>
    </div>

    {{-- Google SERP preview --}}
    <div x-show="activeTab === 'serp'" x-cloak class="seo-serp-preview">
        <div class="seo-preview-label">Search result preview</div>
        <div class="serp-card">
            <div class="serp-breadcrumb">
                <div class="serp-url-group">
                    <span class="serp-site-name" x-text="siteName"></span>
                    <span class="serp-url" x-text="url"></span>
                </div>
            </div>
            <h3 class="serp-title" x-text="truncate(effectiveTitle, t.titleMax)"></h3>
            <p class="serp-description" x-text="truncate(effectiveDescription, t.descMax) || 'No description available.'"></p>
        </div>
    </div>

    {{-- Social card preview (Facebook / X / LinkedIn) --}}
    <div x-show="activeTab === 'social'" x-cloak class="seo-social-preview">
        <div class="seo-preview-label">Social share preview</div>
        <div class="social-card">
            <div class="social-image-container">
                <template x-if="showImage">
                    <img :src="imageUrl" alt="" class="social-image" x-on:error="imgError = true" />
                </template>
                <div class="social-image-placeholder" x-show="!showImage">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.4">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                        <circle cx="8.5" cy="8.5" r="1.5" />
                        <polyline points="21 15 16 10 5 21" />
                    </svg>
                    <span>No image</span>
                </div>
            </div>
            <div class="social-body">
                <span class="social-domain" x-text="serpDomain"></span>
                <h3 class="social-title" x-text="socialTitle"></h3>
                <p class="social-description" x-text="truncate(effectiveDescription, t.descMax) || 'No description available.'"></p>
            </div>
        </div>
    </div>

    {{-- Live source labels — reflect the CURRENT form, including unsaved edits --}}
    <div class="seo-preview-sources">
        <span class="seo-preview-sources-note">Reflecting the current form (including unsaved changes).</span>
        <span class="seo-preview-source">
            <span class="seo-preview-source-field">Title</span>
            <span class="seo-preview-source-badge" :class="hasManualTitle ? 'seo-preview-badge-manual' : 'seo-preview-badge-fallback'" x-text="titleSourceLabel"></span>
        </span>
        <span class="seo-preview-source">
            <span class="seo-preview-source-field">Description</span>
            <span class="seo-preview-source-badge" :class="hasManualDesc ? 'seo-preview-badge-manual' : 'seo-preview-badge-fallback'" x-text="descriptionSourceLabel"></span>
        </span>
        <span class="seo-preview-source">
            <span class="seo-preview-source-field">Image</span>
            <span class="seo-preview-source-badge" :class="hasManualImage ? 'seo-preview-badge-manual' : (showImage ? 'seo-preview-badge-fallback' : 'seo-preview-badge-none')" x-text="imageSourceLabel"></span>
        </span>
    </div>

    {{-- SEO warnings (shared thresholds) --}}
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

    .seo-snippet-preview [x-cloak] {
        display: none !important;
    }

    .seo-snippet-preview .seo-preview-tabs {
        display: flex;
        gap: 0.5rem;
    }

    .seo-snippet-preview .seo-preview-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
        border: 1px solid rgb(229 231 235);
        background: rgb(249 250 251);
        color: rgb(107 114 128);
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .seo-snippet-preview .seo-preview-tab:hover {
        border-color: rgb(209 213 219);
        color: rgb(55 65 81);
    }

    .seo-snippet-preview .seo-preview-tab-active {
        background: rgb(239 246 255);
        border-color: rgb(147 197 253);
        color: rgb(37 99 235);
    }

    .dark .seo-snippet-preview .seo-preview-tab {
        background: rgb(31 41 55);
        border-color: rgb(55 65 81);
        color: rgb(156 163 175);
    }

    .dark .seo-snippet-preview .seo-preview-tab:hover {
        border-color: rgb(75 85 99);
        color: rgb(209 213 219);
    }

    .dark .seo-snippet-preview .seo-preview-tab-active {
        background: rgb(30 58 138);
        border-color: rgb(59 130 246);
        color: rgb(147 197 253);
    }

    .seo-snippet-preview .seo-preview-label {
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: rgb(156 163 175);
        margin-bottom: 0.5rem;
    }

    .seo-snippet-preview .serp-card {
        padding: 1rem 1.25rem;
        border-radius: 0.75rem;
        border: 1px solid rgb(229 231 235);
        background: white;
        max-width: 600px;
    }

    .dark .seo-snippet-preview .serp-card {
        background: rgb(17 24 39);
        border-color: rgb(55 65 81);
    }

    .seo-snippet-preview .serp-breadcrumb {
        margin-bottom: 0.375rem;
    }

    .seo-snippet-preview .serp-url-group {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .seo-snippet-preview .serp-site-name {
        font-size: 0.875rem;
        color: rgb(32 33 36);
        line-height: 1.3;
    }

    .dark .seo-snippet-preview .serp-site-name {
        color: rgb(209 213 219);
    }

    .seo-snippet-preview .serp-url {
        font-size: 0.75rem;
        color: rgb(77 81 86);
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dark .seo-snippet-preview .serp-url {
        color: rgb(156 163 175);
    }

    .seo-snippet-preview .serp-title {
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

    .dark .seo-snippet-preview .serp-title {
        color: rgb(138 180 248);
    }

    .seo-snippet-preview .serp-description {
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

    .dark .seo-snippet-preview .serp-description {
        color: rgb(189 193 198);
    }

    .seo-snippet-preview .social-card {
        border-radius: 0.75rem;
        border: 1px solid rgb(229 231 235);
        overflow: hidden;
        max-width: 500px;
        background: white;
    }

    .dark .seo-snippet-preview .social-card {
        background: rgb(17 24 39);
        border-color: rgb(55 65 81);
    }

    .seo-snippet-preview .social-image-container {
        width: 100%;
        aspect-ratio: 1.91 / 1;
        overflow: hidden;
        background: rgb(243 244 246);
        position: relative;
    }

    .dark .seo-snippet-preview .social-image-container {
        background: rgb(31 41 55);
    }

    .seo-snippet-preview .social-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .seo-snippet-preview .social-image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        color: rgb(156 163 175);
        font-size: 0.75rem;
    }

    .seo-snippet-preview .social-body {
        padding: 0.75rem 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.125rem;
    }

    .seo-snippet-preview .social-domain {
        font-size: 0.75rem;
        color: rgb(107 114 128);
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .dark .seo-snippet-preview .social-domain {
        color: rgb(156 163 175);
    }

    .seo-snippet-preview .social-title {
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.3;
        color: rgb(17 24 39);
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .dark .seo-snippet-preview .social-title {
        color: rgb(243 244 246);
    }

    .seo-snippet-preview .social-description {
        font-size: 0.8125rem;
        line-height: 1.4;
        color: rgb(107 114 128);
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .dark .seo-snippet-preview .social-description {
        color: rgb(156 163 175);
    }

    .seo-snippet-preview .seo-preview-sources {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem 0.875rem;
        max-width: 600px;
    }

    .seo-snippet-preview .seo-preview-sources-note {
        font-size: 0.75rem;
        color: rgb(156 163 175);
    }

    .seo-snippet-preview .seo-preview-source {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        font-size: 0.75rem;
    }

    .seo-snippet-preview .seo-preview-source-field {
        font-weight: 600;
        color: rgb(75 85 99);
    }

    .dark .seo-snippet-preview .seo-preview-source-field {
        color: rgb(209 213 219);
    }

    .seo-snippet-preview .seo-preview-source-badge {
        padding: 0.0625rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 600;
    }

    .seo-snippet-preview .seo-preview-badge-manual {
        background: rgb(220 252 231);
        color: rgb(22 101 52);
    }

    .dark .seo-snippet-preview .seo-preview-badge-manual {
        background: rgba(20 83 45 / 0.4);
        color: rgb(134 239 172);
    }

    .seo-snippet-preview .seo-preview-badge-fallback {
        background: rgb(239 246 255);
        color: rgb(30 64 175);
    }

    .dark .seo-snippet-preview .seo-preview-badge-fallback {
        background: rgba(30 58 138 / 0.3);
        color: rgb(191 219 254);
    }

    .seo-snippet-preview .seo-preview-badge-none {
        background: rgb(243 244 246);
        color: rgb(107 114 128);
    }

    .dark .seo-snippet-preview .seo-preview-badge-none {
        background: rgb(55 65 81);
        color: rgb(156 163 175);
    }

    .seo-snippet-preview .seo-warnings-panel {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid rgb(229 231 235);
        background: rgb(249 250 251);
        max-width: 600px;
    }

    .dark .seo-snippet-preview .seo-warnings-panel {
        border-color: rgb(55 65 81);
        background: rgb(31 41 55);
    }

    .seo-snippet-preview .seo-warning-item {
        padding: 0.375rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.8125rem;
        line-height: 1.4;
    }

    .seo-snippet-preview .seo-warning-danger {
        background: rgb(254 242 242);
        color: rgb(153 27 27);
    }

    .dark .seo-snippet-preview .seo-warning-danger {
        background: rgba(127 29 29 / 0.3);
        color: rgb(252 165 165);
    }

    .seo-snippet-preview .seo-warning-warning {
        background: rgb(255 251 235);
        color: rgb(120 53 15);
    }

    .dark .seo-snippet-preview .seo-warning-warning {
        background: rgba(69 26 3 / 0.3);
        color: rgb(253 224 71);
    }

    .seo-snippet-preview .seo-warning-info {
        background: rgb(239 246 255);
        color: rgb(30 64 175);
    }

    .dark .seo-snippet-preview .seo-warning-info {
        background: rgba(30 58 138 / 0.3);
        color: rgb(191 219 254);
    }
</style>
