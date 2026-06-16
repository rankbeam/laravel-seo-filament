<?php

declare(strict_types=1);

use Rankbeam\Seo\Filament\Support\SEOPreviewData;

/**
 * Render the preview view directly (no Livewire) for both the
 * og_image-editable and og_image-excluded branches, proving the
 * `@if($previewHasImageField)` Blade branches inside the Alpine `x-data`
 * both produce a renderable component.
 */
function renderPreview(bool $hasImageField): string
{
    return view('seo-filament::seo-snippet-preview', [
        'getStatePath' => fn (): string => 'seo_meta',
        'preview' => app(SEOPreviewData::class)->forModel(null),
        'previewHasImageField' => $hasImageField,
    ])->render();
}

it('renders both tabs and entangles og_image when the image field is editable', function () {
    $html = renderPreview(true);

    expect($html)
        ->toContain('Search result preview')
        ->toContain('Social share preview')
        ->toContain("activeTab = 'serp'")
        ->toContain("activeTab = 'social'")
        ->toContain("\$entangle('seo_meta.og_image')");
});

it('renders without the og_image entangle when the image field is excluded', function () {
    $html = renderPreview(false);

    expect($html)
        ->toContain('Search result preview')
        ->toContain('Social share preview')
        ->toContain('ogImageState: null')
        ->not->toContain("\$entangle('seo_meta.og_image')");
});

it('scopes every style rule under the preview root (no global selectors)', function () {
    $html = renderPreview(true);

    // Extract the <style> block and assert every rule is descendant-scoped
    // under .seo-snippet-preview (directly, or via the .dark theme hook).
    preg_match('/<style>(.*?)<\/style>/s', $html, $m);
    $css = $m[1] ?? '';

    expect($css)->not->toBe('');

    // Every selector line (one ending in `{`) must start with the scope class
    // or the dark-mode hook that contains it.
    preg_match_all('/^\s*([^@{}\n][^{}\n]*)\{/m', $css, $selectors);

    foreach ($selectors[1] as $selector) {
        $selector = trim($selector);

        expect(
            str_starts_with($selector, '.seo-snippet-preview')
            || str_starts_with($selector, '.dark .seo-snippet-preview')
        )->toBeTrue("Unscoped selector found: {$selector}");
    }
});
