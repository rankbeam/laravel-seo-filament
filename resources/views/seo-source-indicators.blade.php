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

{{--
    Styling note: ships inside the package, so no Tailwind utilities. A scoped
    <style> block with Filament CSS variables (oklch) + color-mix; text hierarchy
    via opacity (inherits the panel's light/dark text colour). Badges follow the
    panel's semantic palette, so they re-theme with the brand and in dark mode.
--}}
<style>
    .seo-sources-panel {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        padding: 0.875rem 1rem;
        border-radius: 0.75rem;
        border: 1px solid color-mix(in oklch, var(--gray-500) 16%, transparent);
        background: color-mix(in oklch, var(--gray-500) 5%, transparent);
        max-width: 600px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }

    .seo-sources-header {
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        opacity: 0.7;
    }

    .seo-sources-note {
        font-size: 0.75rem;
        opacity: 0.55;
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
        opacity: 0.85;
    }

    .seo-source-value {
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        opacity: 0.55;
    }

    .seo-source-badge {
        flex-shrink: 0;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 600;
    }

    .seo-source-badge-manual {
        color: var(--success-600);
        background: color-mix(in oklch, var(--success-500) 14%, transparent);
    }

    .dark .seo-source-badge-manual {
        color: var(--success-400);
    }

    .seo-source-badge-fallback {
        color: var(--primary-600);
        background: color-mix(in oklch, var(--primary-500) 12%, transparent);
    }

    .dark .seo-source-badge-fallback {
        color: var(--primary-400);
    }

    .seo-source-badge-none {
        color: var(--gray-500);
        background: color-mix(in oklch, var(--gray-500) 12%, transparent);
    }
</style>
