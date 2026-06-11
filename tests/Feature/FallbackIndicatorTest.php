<?php

declare(strict_types=1);

use Livewire\Livewire;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\CreatePost;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\EditPost;

it('labels content-derived values as fallbacks on the edit page', function () {
    $post = Post::query()->create([
        'title' => 'Hello World',
        'slug' => 'hello-world',
        'excerpt' => 'A short excerpt.',
    ]);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertSee('Effective values')
        ->assertSee('Content fallback')
        ->assertSee('Derived from URL')
        ->assertSee('Site config');
});

it('labels manually entered values as manual', function () {
    $post = Post::query()->create(['title' => 'Hello World', 'slug' => 'hello-world']);
    $post->saveSEO([
        'title' => 'Manual SEO title',
        'description' => 'Manual SEO description.',
    ]);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertSee('Manual')
        ->assertSee('Manual SEO title');
});

it('shows the effective title with the configured suffix', function () {
    $post = Post::query()->create(['title' => 'Hello World', 'slug' => 'hello-world']);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertSee('Hello World | Test Site');
});

it('omits the indicators panel on the create page', function () {
    Livewire::test(CreatePost::class)
        ->assertOk()
        ->assertDontSee('Effective values');
});
