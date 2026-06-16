<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Rankbeam\Seo\Filament\Support\SEOPreviewData;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;
use Rankbeam\Seo\Services\SEOWarningEvaluator;

/**
 * T5 — the server-side preview payload. The dimension-detection and
 * manual-vs-fallback image-source logic is the testable "warning payload"
 * behind the live preview; it must use the shared evaluator thresholds.
 */
beforeEach(function () {
    $this->diskRoot = sys_get_temp_dir().'/seo-preview-'.uniqid();

    config(['filesystems.disks.public' => [
        'driver' => 'local',
        'root' => $this->diskRoot,
        'url' => config('app.url').'/storage',
        'visibility' => 'public',
    ]]);
});

afterEach(function () {
    if (isset($this->diskRoot) && is_dir($this->diskRoot)) {
        File::deleteDirectory($this->diskRoot);
    }
});

function writeImage(string $path, int $width, int $height): void
{
    $absolute = Storage::disk('public')->path($path);
    File::ensureDirectoryExists(dirname($absolute));

    $image = imagecreatetruecolor($width, $height);
    imagepng($image, $absolute);
    imagedestroy($image);
}

function previewFor(Post $post): array
{
    return app(SEOPreviewData::class)->forModel($post->fresh(), 'en');
}

it('exposes the shared evaluator thresholds verbatim', function () {
    $thresholds = app(SEOPreviewData::class)->thresholds();

    expect($thresholds)->toMatchArray([
        'titleMax' => SEOWarningEvaluator::TITLE_MAX_LENGTH,
        'descMax' => SEOWarningEvaluator::DESCRIPTION_MAX_LENGTH,
        'minWidth' => SEOWarningEvaluator::MIN_SOCIAL_IMAGE_WIDTH,
        'minHeight' => SEOWarningEvaluator::MIN_SOCIAL_IMAGE_HEIGHT,
        'idealWidth' => SEOWarningEvaluator::IDEAL_SOCIAL_IMAGE_WIDTH,
        'idealHeight' => SEOWarningEvaluator::IDEAL_SOCIAL_IMAGE_HEIGHT,
    ]);
});

it('returns an app-url, image-less payload for a null model (create form)', function () {
    $preview = app(SEOPreviewData::class)->forModel(null);

    expect($preview['url'])->toBe((string) config('app.url'))
        ->and($preview['fallbackTitle'])->toBe('')
        ->and($preview['fallbackDescription'])->toBe('')
        ->and($preview['image']['url'])->toBeNull()
        ->and($preview['image']['source'])->toBe('none')
        ->and($preview['image']['state'])->toBe('unavailable');
});

it('strips the query string from the page URL', function () {
    $post = Post::query()->create(['title' => 'T', 'slug' => 'my-post']);

    expect(previewFor($post)['url'])->toBe('https://example.test/blog/my-post');
});

it('surfaces the content title and description as fallbacks', function () {
    $post = Post::query()->create([
        'title' => 'Content title',
        'slug' => 'p',
        'excerpt' => 'A short excerpt.',
    ]);

    $preview = previewFor($post);

    expect($preview['fallbackTitle'])->toBe('Content title')
        ->and($preview['fallbackDescription'])->toBe('A short excerpt.');
});

it('measures a known-local manual og:image and reports ideal dimensions with no warning', function () {
    writeImage('seo/cover.png', 1200, 630);

    $post = Post::query()->create(['title' => 'T', 'slug' => 'p']);
    $post->saveSEO(['og_image' => 'seo/cover.png']);

    $image = previewFor($post)['image'];

    expect($image['source'])->toBe('manual')
        ->and($image['state'])->toBe('known-local')
        ->and($image['width'])->toBe(1200)
        ->and($image['height'])->toBe(630)
        ->and($image['warning'])->toBeNull()
        ->and($image['url'])->toBe(config('app.url').'/storage/seo/cover.png');
})->skip(! extension_loaded('gd'), 'GD is required to generate fixture images.');

it('flags a too-small local image using the shared minimum threshold', function () {
    writeImage('seo/tiny.png', 100, 100);

    $post = Post::query()->create(['title' => 'T', 'slug' => 'p']);
    $post->saveSEO(['og_image' => 'seo/tiny.png']);

    $image = previewFor($post)['image'];

    expect($image['state'])->toBe('known-local')
        ->and($image['warning'])->toBe('too_small');
})->skip(! extension_loaded('gd'), 'GD is required to generate fixture images.');

it('flags a below-ideal local image as not-ideal using the shared ideal threshold', function () {
    writeImage('seo/medium.png', 800, 400);

    $post = Post::query()->create(['title' => 'T', 'slug' => 'p']);
    $post->saveSEO(['og_image' => 'seo/medium.png']);

    $image = previewFor($post)['image'];

    expect($image['state'])->toBe('known-local')
        ->and($image['warning'])->toBe('not_ideal');
})->skip(! extension_loaded('gd'), 'GD is required to generate fixture images.');

it('prefers the manual og:image over the content fallback (resolver order)', function () {
    writeImage('seo/manual.png', 1200, 630);

    $post = Post::query()->create([
        'title' => 'T',
        'slug' => 'p',
        'featured_image' => '/img/content.jpg',
    ]);
    $post->saveSEO(['og_image' => 'seo/manual.png']);

    $image = previewFor($post)['image'];

    expect($image['source'])->toBe('manual')
        ->and($image['url'])->toBe(config('app.url').'/storage/seo/manual.png');
})->skip(! extension_loaded('gd'), 'GD is required to generate fixture images.');

it('falls back to the content image when no manual og:image is set', function () {
    $post = Post::query()->create([
        'title' => 'T',
        'slug' => 'p',
        'featured_image' => '/img/content.jpg',
    ]);

    $image = previewFor($post)['image'];

    // No manual image -> the content layer wins. The core resolver absolutizes
    // the content image (normalizeImageUrl), so the preview shows that exact,
    // resolver-matching URL; a non-existent local file defers measurement to
    // the browser (state "unknown").
    expect($image['source'])->toBe('fallback')
        ->and($image['url'])->toBe(config('app.url').'/img/content.jpg')
        ->and($image['state'])->toBe('unknown')
        ->and($image['warning'])->toBeNull();
});

it('does not fetch a remote image and defers it to the browser', function () {
    $post = Post::query()->create(['title' => 'T', 'slug' => 'p']);
    $post->saveSEO(['og_image' => 'https://cdn.example.com/share.png']);

    $image = previewFor($post)['image'];

    expect($image['source'])->toBe('manual')
        ->and($image['url'])->toBe('https://cdn.example.com/share.png')
        ->and($image['state'])->toBe('unknown')
        ->and($image['width'])->toBeNull();
});

it('reports no image when nothing resolves', function () {
    $post = Post::query()->create(['title' => 'T', 'slug' => 'p']);

    $image = previewFor($post)['image'];

    expect($image['source'])->toBe('none')
        ->and($image['url'])->toBeNull()
        ->and($image['state'])->toBe('unavailable');
});
