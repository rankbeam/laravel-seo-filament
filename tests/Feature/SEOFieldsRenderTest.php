<?php

declare(strict_types=1);

use Livewire\Livewire;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\CreatePost;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\EditPost;

it('renders the SEO section on the create page', function () {
    Livewire::test(CreatePost::class)
        ->assertOk()
        ->assertSee('SEO')
        ->assertSee('SEO title')
        ->assertSee('SEO description')
        ->assertSee('Canonical URL')
        ->assertSee('Robots directive')
        ->assertSee('Social sharing image');
});

it('renders the search snippet preview', function () {
    $post = Post::query()->create(['title' => 'Hello World', 'slug' => 'hello-world']);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertSee('Search result preview');
});

it('renders all robots directive options', function () {
    // Labels containing apostrophes are JS-encoded inside the Select's
    // Alpine options payload, so assert on the raw directive values.
    Livewire::test(CreatePost::class)
        ->assertOk()
        ->assertSee('Index, follow links')
        ->assertSeeHtml('noindex, nofollow');
});

it('hydrates stored seo_meta values into the form on edit', function () {
    $post = Post::query()->create(['title' => 'Hello World', 'slug' => 'hello-world']);
    $post->saveSEO([
        'title' => 'Stored SEO title',
        'description' => 'Stored SEO description',
        'robots' => 'noindex, follow',
        'canonical' => 'https://example.test/custom-canonical',
    ]);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet([
            'seo_meta.title' => 'Stored SEO title',
            'seo_meta.description' => 'Stored SEO description',
            'seo_meta.robots' => 'noindex, follow',
            'seo_meta.canonical' => 'https://example.test/custom-canonical',
        ]);
});
