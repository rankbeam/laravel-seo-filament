<?php

declare(strict_types=1);

use Rankbeam\Seo\Filament\Support\SEOFieldSources;
use Rankbeam\Seo\Filament\Support\SEOPreviewData;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;

class LocalePreviewPost extends Post
{
    protected $table = 'posts';

    public function getSEOTitle(): ?string
    {
        return app()->getLocale() === 'ja' ? '日本語のタイトル' : 'Titolo italiano';
    }

    public function getSEODescription(): ?string
    {
        return app()->getLocale() === 'ja' ? '日本語の説明文です。' : 'Descrizione italiana.';
    }

    public function getUrlForSEO(): string
    {
        return 'https://example.test/'.app()->getLocale().'/article?preview=1';
    }
}

it('localizes preview fallbacks and URLs while source labels remain English', function () {
    $post = LocalePreviewPost::query()->create(['title' => 'English database title', 'slug' => 'localized']);
    $post->saveSEO(['title' => 'English manual title'], 'en');
    $english = $post->seoMeta;

    $preview = app(SEOPreviewData::class)->forModel($post, 'ja');
    $sources = app(SEOFieldSources::class)->forModel($post, 'it');
    expect($preview['fallbackTitle'])->toBe('日本語のタイトル')
        ->and($preview['fallbackDescription'])->toBe('日本語の説明文です。')
        ->and($preview['url'])->toBe('https://example.test/ja/article')
        ->and($preview['thresholds']['titleMax'])->toBe(30)
        ->and($sources['title']['source_label'])->toBe('Content fallback')
        ->and($sources['canonical']['effective'])->toBe('https://example.test/it/article')
        ->and($post->seoMeta)->toBe($english)
        ->and(app()->getLocale())->toBe('en');
});
