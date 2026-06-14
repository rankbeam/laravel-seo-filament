<?php

declare(strict_types=1);

use Livewire\Livewire;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\CreatePost;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\EditPost;

it('renders the focus keywords field', function () {
    Livewire::test(CreatePost::class)
        ->assertOk()
        ->assertSee('Focus keywords');
});

it('persists tags as the structured focus_keywords shape, first marked primary', function () {
    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'My first post',
            'slug' => 'my-first-post',
            'seo_meta.focus_keywords' => ['laravel seo', 'meta tags'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $meta = Post::query()->sole()->seoMeta()->sole();

    expect($meta->focus_keywords)->toBe([
        ['keyword' => 'laravel seo', 'is_primary' => true],
        ['keyword' => 'meta tags', 'is_primary' => false],
    ]);
});

it('hydrates stored structured keywords back into the tags input as plain strings', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $post->saveSEO([
        'focus_keywords' => [
            ['keyword' => 'primary kw', 'is_primary' => true],
            ['keyword' => 'secondary kw', 'is_primary' => false],
        ],
    ]);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet([
            'seo_meta.focus_keywords' => ['primary kw', 'secondary kw'],
        ]);
});

it('round-trips edited keywords through the edit page', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $post->saveSEO([
        'focus_keywords' => [['keyword' => 'old keyword', 'is_primary' => true]],
    ]);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'seo_meta.focus_keywords' => ['fresh keyword', 'another one'],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $meta = $post->fresh()->seoMeta()->sole();

    expect($meta->focus_keywords)->toBe([
        ['keyword' => 'fresh keyword', 'is_primary' => true],
        ['keyword' => 'another one', 'is_primary' => false],
    ]);
});

it('clears stored keywords when the field is emptied', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $post->saveSEO([
        'title' => 'Keep me',
        'focus_keywords' => [['keyword' => 'to be removed', 'is_primary' => true]],
    ]);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm(['seo_meta.focus_keywords' => []])
        ->call('save')
        ->assertHasNoFormErrors();

    $meta = $post->fresh()->seoMeta()->sole();

    expect($meta->focus_keywords)->toBeNull()
        ->and($meta->title)->toBe('Keep me');
});

it('keeps the saved keywords readable by the core primary-keyword helper', function () {
    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Post',
            'slug' => 'post',
            'seo_meta.focus_keywords' => ['main term', 'other term'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $post = Post::query()->sole()->fresh();

    expect($post->getPrimaryKeyword())->toBe(['keyword' => 'main term', 'is_primary' => true]);
});
