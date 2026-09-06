<?php

declare(strict_types=1);

use Rankbeam\Seo\Filament\Support\SEOPreviewData;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;

/*
|--------------------------------------------------------------------------
| Preview budgets follow the script of the effective value (core 3.15)
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    config(['seo.length_policy' => null, 'seo.features.auto_create_meta' => false]);
});

it('keeps the Latin budgets with no values and a Latin locale', function () {
    $thresholds = app(SEOPreviewData::class)->thresholds(null, null, 'en');

    expect($thresholds['titleMax'])->toBe(60)
        ->and($thresholds['descMax'])->toBe(160);
});

it('picks the CJK budgets from the value script, or from the locale when the value is empty', function () {
    $fromText = app(SEOPreviewData::class)->thresholds('検索エンジン最適化', '搜索引擎优化', 'en');
    $fromLocale = app(SEOPreviewData::class)->thresholds(null, null, 'ja');

    expect($fromText['titleMax'])->toBe(30)
        ->and($fromText['descMax'])->toBe(80)
        ->and($fromLocale['titleMax'])->toBe(30)
        ->and($fromLocale['descMax'])->toBe(80);
});

it('builds the payload budgets from the saved manual title', function () {
    $post = Post::query()->create(['title' => 'Hello World', 'slug' => 'hello-world']);
    $post->saveSEO(['title' => '検索エンジン最適化の完全ガイド', 'description' => 'A Latin description.']);

    $preview = app(SEOPreviewData::class)->forModel($post->fresh(), 'en');

    expect($preview['thresholds']['titleMax'])->toBe(30)
        ->and($preview['thresholds']['descMax'])->toBe(160);
});

it('falls back to the content title script when nothing is saved', function () {
    $post = Post::query()->create(['title' => '東京の完全ガイド', 'slug' => 'tokyo']);

    $preview = app(SEOPreviewData::class)->forModel($post->fresh(), 'en');

    expect($preview['fallbackTitle'])->toContain('東京')
        ->and($preview['thresholds']['titleMax'])->toBe(30);
});
