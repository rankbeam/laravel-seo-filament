@php
    use Illuminate\Support\Str;
    use Rankbeam\Seo\Filament\Support\SEOFieldSources;

    $record = $getRecord();

    $sources = $record && $record->exists ? app(SEOFieldSources::class)->forModel($record) : null;

    $fieldLabels = [
        'title' => 'Title',
        'description' => 'Description',
        'og_image' => 'Social image',
        'robots' => 'Robots',
        'canonical' => 'Canonical URL',
    ];
@endphp

@if ($sources !== null)
    <div class="seo-sources-panel">
        <div class="seo-sources-header">Effective values &amp; sources</div>
        <div class="seo-sources-note">Shows which layer provides each value as last saved. Save the form to refresh.</div>

        @foreach ($sources as $field => $info)
            <div class="seo-source-row">
                <span class="seo-source-field">{{ $fieldLabels[$field] ?? $field }}</span>
                <span class="seo-source-value" title="{{ $info['effective'] }}">
                    {{ $info['effective'] !== null ? Str::limit($info['effective'], 80) : '—' }}
                </span>
                <span class="seo-source-badge {{ $info['is_manual'] ? 'seo-source-badge-manual' : ($info['source'] === 'none' ? 'seo-source-badge-none' : 'seo-source-badge-fallback') }}">
                    {{ $info['source_label'] }}
                </span>
            </div>
        @endforeach
    </div>
@endif

<style>
    .seo-sources-panel {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid rgb(229 231 235);
        background: rgb(249 250 251);
        max-width: 600px;
    }

    .dark .seo-sources-panel {
        border-color: rgb(55 65 81);
        background: rgb(31 41 55);
    }

    .seo-sources-header {
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: rgb(107 114 128);
    }

    .dark .seo-sources-header {
        color: rgb(156 163 175);
    }

    .seo-sources-note {
        font-size: 0.75rem;
        color: rgb(156 163 175);
        margin-bottom: 0.25rem;
    }

    .seo-source-row {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        font-size: 0.8125rem;
        line-height: 1.4;
    }

    .seo-source-field {
        flex: 0 0 7rem;
        font-weight: 600;
        color: rgb(55 65 81);
    }

    .dark .seo-source-field {
        color: rgb(209 213 219);
    }

    .seo-source-value {
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: rgb(107 114 128);
    }

    .dark .seo-source-value {
        color: rgb(156 163 175);
    }

    .seo-source-badge {
        flex-shrink: 0;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 600;
    }

    .seo-source-badge-manual {
        background: rgb(220 252 231);
        color: rgb(22 101 52);
    }

    .dark .seo-source-badge-manual {
        background: rgba(20 83 45 / 0.4);
        color: rgb(134 239 172);
    }

    .seo-source-badge-fallback {
        background: rgb(239 246 255);
        color: rgb(30 64 175);
    }

    .dark .seo-source-badge-fallback {
        background: rgba(30 58 138 / 0.3);
        color: rgb(191 219 254);
    }

    .seo-source-badge-none {
        background: rgb(243 244 246);
        color: rgb(107 114 128);
    }

    .dark .seo-source-badge-none {
        background: rgb(55 65 81);
        color: rgb(156 163 175);
    }
</style>
