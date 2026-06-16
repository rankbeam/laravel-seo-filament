<?php

declare(strict_types=1);

use Livewire\Livewire;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Article;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\PublicPage;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\ArticleResource\Pages\EditArticle;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostNoPreviewResource\Pages\EditPostNoPreview;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\CreatePost;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\EditPost;

/**
 * T5 — the editorial preview is a tabbed (Google SERP / social card) live
 * editor that replaces the old search-only snippet.
 */
it('renders both preview tabs and the social card on the edit page', function () {
    $post = Post::query()->create(['title' => 'Hello World', 'slug' => 'hello-world']);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertSee('Search result preview')
        ->assertSee('Social share preview')
        ->assertSeeHtml("activeTab = 'serp'")
        ->assertSeeHtml("activeTab = 'social'");
});

it('renders the live source labels that reflect the current form', function () {
    $post = Post::query()->create(['title' => 'Hello World', 'slug' => 'hello-world']);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertSee('Reflecting the current form')
        // The badge values are computed client-side; the field scaffolding and
        // the label getters are present in the rendered Alpine component.
        ->assertSeeHtml('titleSourceLabel')
        ->assertSeeHtml('imageSourceLabel');
});

it('still renders the preview on the create page (no record)', function () {
    Livewire::test(CreatePost::class)
        ->assertOk()
        ->assertSee('Search result preview')
        ->assertSee('Social share preview');
});

it('wires the shared evaluator thresholds into the preview', function () {
    $post = Post::query()->create(['title' => 'Hello World', 'slug' => 'hello-world']);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        // The shared thresholds object is wired into the Alpine component
        // (its exact values == the core constants is asserted in the unit test).
        ->assertSeeHtml('titleMax')
        ->assertSeeHtml('idealWidth');
});

it('feeds the saved og:image into the preview payload', function () {
    $post = Post::query()->create(['title' => 'Hello World', 'slug' => 'hello-world']);
    $post->saveSEO(['og_image' => 'https://cdn.example.com/share.png']);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertSeeHtml('cdn.example.com')
        ->assertSeeHtml('share.png');
});

it('reflects the related target image in the preview, not the form record', function () {
    $article = Article::query()->create(['title' => 'Article A', 'slug' => 'article-a']);
    $page = PublicPage::query()->create(['article_id' => $article->id, 'path' => 'article-a']);
    $page->saveSEO(['og_image' => 'https://cdn.example.com/page-share.png']);

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->assertOk()
        ->assertSee('Social share preview')
        ->assertSeeHtml('page-share.png');
});

it('omits the preview when showPreview is false but keeps the source indicators', function () {
    $post = Post::query()->create(['title' => 'Hello World', 'slug' => 'hello-world']);

    Livewire::test(EditPostNoPreview::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertDontSee('Search result preview')
        ->assertDontSee('Social share preview')
        // The separate source-indicators panel is unaffected by showPreview.
        ->assertSee('Effective values');
});
