<?php

declare(strict_types=1);

use Rankbeam\Seo\Models\SEODefault;
use Rankbeam\Seo\Filament\Support\SEOFieldSources;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;

function sourcesFor(Post $post): array
{
    return app(SEOFieldSources::class)->forModel($post->fresh(), 'en');
}

it('attributes a stored seo_meta title to the manual layer', function () {
    $post = Post::query()->create(['title' => 'Content title', 'slug' => 'p']);
    $post->saveSEO(['title' => 'Manual title']);

    $sources = sourcesFor($post);

    expect($sources['title']['source'])->toBe(SEOFieldSources::SOURCE_MANUAL)
        ->and($sources['title']['is_manual'])->toBeTrue()
        ->and($sources['title']['manual'])->toBe('Manual title')
        ->and($sources['title']['effective'])->toBe('Manual title | Test Site')
        ->and($sources['title']['fallback'])->toBe('Content title');
});

it('attributes a model-derived title to the content layer', function () {
    $post = Post::query()->create(['title' => 'Content title', 'slug' => 'p']);

    $sources = sourcesFor($post);

    expect($sources['title']['source'])->toBe(SEOFieldSources::SOURCE_CONTENT)
        ->and($sources['title']['is_manual'])->toBeFalse()
        ->and($sources['title']['effective'])->toBe('Content title | Test Site');
});

it('attributes a description from the excerpt to the content layer', function () {
    $post = Post::query()->create(['title' => 'T', 'slug' => 'p', 'excerpt' => 'An excerpt.']);

    $sources = sourcesFor($post);

    expect($sources['description']['source'])->toBe(SEOFieldSources::SOURCE_CONTENT)
        ->and($sources['description']['effective'])->toBe('An excerpt.');
});

it('reports a missing description as not set', function () {
    $post = Post::query()->create(['title' => 'T', 'slug' => 'p']);

    $sources = sourcesFor($post);

    expect($sources['description']['source'])->toBe(SEOFieldSources::SOURCE_NONE)
        ->and($sources['description']['effective'])->toBeNull();
});

it('attributes the robots directive to site config when nothing else sets it', function () {
    $post = Post::query()->create(['title' => 'T', 'slug' => 'p']);

    $sources = sourcesFor($post);

    expect($sources['robots']['source'])->toBe(SEOFieldSources::SOURCE_CONFIG)
        ->and($sources['robots']['effective'])->toBe('index,follow');
});

it('attributes a manual robots directive to the manual layer', function () {
    $post = Post::query()->create(['title' => 'T', 'slug' => 'p']);
    $post->saveSEO(['robots' => 'noindex, nofollow']);

    $sources = sourcesFor($post);

    expect($sources['robots']['source'])->toBe(SEOFieldSources::SOURCE_MANUAL)
        ->and($sources['robots']['effective'])->toBe('noindex, nofollow');
});

it('derives the canonical from the model URL with the query string stripped', function () {
    $post = Post::query()->create(['title' => 'T', 'slug' => 'my-post']);

    $sources = sourcesFor($post);

    expect($sources['canonical']['source'])->toBe(SEOFieldSources::SOURCE_URL)
        ->and($sources['canonical']['effective'])->toBe('https://example.test/blog/my-post');
});

it('keeps a manual canonical verbatim', function () {
    $post = Post::query()->create(['title' => 'T', 'slug' => 'my-post']);
    $post->saveSEO(['canonical' => 'https://example.test/keep?page=2']);

    $sources = sourcesFor($post);

    expect($sources['canonical']['source'])->toBe(SEOFieldSources::SOURCE_MANUAL)
        ->and($sources['canonical']['effective'])->toBe('https://example.test/keep?page=2');
});

it('attributes a model image field to the content layer', function () {
    $post = Post::query()->create(['title' => 'T', 'slug' => 'p', 'featured_image' => '/img/cover.jpg']);

    $sources = sourcesFor($post);

    expect($sources['og_image']['source'])->toBe(SEOFieldSources::SOURCE_CONTENT);
});

it('re-attributes the config default og image to the config layer', function () {
    config()->set('seo.default_og_image', '/default-og.jpg');

    $post = Post::query()->create(['title' => 'T', 'slug' => 'p']);

    $sources = sourcesFor($post);

    // SEOComputedBuilder folds the config default into its own fallback
    // chain; the inspector must not mislabel it as content-derived.
    expect($sources['og_image']['source'])->toBe(SEOFieldSources::SOURCE_CONFIG);
});

it('attributes seo_defaults rows for the model class to the model-defaults layer', function () {
    SEODefault::query()->create([
        'scope' => Post::class,
        'locale' => 'en',
        'title_template' => 'Posts on {site_name}',
    ]);

    $post = Post::query()->create(['slug' => 'untitled']);

    $sources = sourcesFor($post);

    expect($sources['title']['source'])->toBe(SEOFieldSources::SOURCE_MODEL_DEFAULTS)
        ->and($sources['title']['effective'])->toBe('Posts on Test Site | Test Site');
});

it('attributes global seo_defaults rows to the global-defaults layer', function () {
    SEODefault::query()->create([
        'scope' => 'global',
        'locale' => 'en',
        'og_image_default' => '/global-og.jpg',
    ]);

    $post = Post::query()->create(['title' => 'T', 'slug' => 'p']);

    $sources = sourcesFor($post);

    expect($sources['og_image']['source'])->toBe(SEOFieldSources::SOURCE_GLOBAL_DEFAULTS)
        ->and($sources['og_image']['effective'])->toBe('/global-og.jpg');
});
