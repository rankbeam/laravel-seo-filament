<?php

declare(strict_types=1);

use Rankbeam\Seo\Models\SEOMeta;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\CreatePost;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\EditPost;

it('round-trips SEO values through the create page', function () {
    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'My first post',
            'slug' => 'my-first-post',
            'content' => 'Some content.',
            'seo_meta.title' => 'Custom SEO title',
            'seo_meta.description' => 'Custom SEO description.',
            'seo_meta.robots' => 'noindex, follow',
            'seo_meta.canonical' => 'https://example.test/custom',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $post = Post::query()->sole();
    $meta = $post->seoMeta()->sole();

    expect($meta->title)->toBe('Custom SEO title')
        ->and($meta->description)->toBe('Custom SEO description.')
        ->and($meta->robots)->toBe('noindex, follow')
        ->and($meta->canonical)->toBe('https://example.test/custom');

    // The HasSEO created-hook record and the form save must not duplicate.
    expect(SEOMeta::query()->count())->toBe(1);
});

it('round-trips SEO values through the edit page', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $post->saveSEO(['title' => 'Old title']);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'seo_meta.title' => 'New title',
            'seo_meta.description' => 'New description.',
            'seo_meta.robots' => 'index, nofollow',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $meta = $post->fresh()->seoMeta()->sole();

    expect($meta->title)->toBe('New title')
        ->and($meta->description)->toBe('New description.')
        ->and($meta->robots)->toBe('index, nofollow');
});

it('edits only the current locale seo meta row', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $post->saveSEO(['title' => 'English title', 'description' => 'English description.'], 'en');
    $post->saveSEO(['title' => 'Titre français', 'description' => 'Description française.'], 'fr');

    app()->setLocale('fr');

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'seo_meta.title' => 'Titre français modifié',
            'seo_meta.description' => 'Description française modifiée.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->fresh()->seoMetaForLocale('en')->first()->title)->toBe('English title')
        ->and($post->fresh()->seoMetaForLocale('en')->first()->description)->toBe('English description.')
        ->and($post->fresh()->seoMetaForLocale('fr')->first()->title)->toBe('Titre français modifié')
        ->and($post->fresh()->seoMetaForLocale('fr')->first()->description)->toBe('Description française modifiée.');
});

it('clears a stored value when the field is emptied', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $post->saveSEO(['title' => 'To be removed', 'description' => 'Keep me.']);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm(['seo_meta.title' => ''])
        ->call('save')
        ->assertHasNoFormErrors();

    $meta = $post->fresh()->seoMeta()->sole();

    expect($meta->title)->toBeNull()
        ->and($meta->description)->toBe('Keep me.');
});

it('saves nothing when no SEO fields are filled and no meta exists', function () {
    // Disable the auto-create hook so the form is the only writer.
    config()->set('seo.features.auto_create_meta', false);

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Plain post',
            'slug' => 'plain-post',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Post::query()->count())->toBe(1)
        ->and(SEOMeta::query()->count())->toBe(0);
});

it('validates the canonical field as a URL', function () {
    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Post',
            'slug' => 'post',
            'seo_meta.canonical' => 'not-a-url',
        ])
        ->call('create')
        ->assertHasFormErrors(['seo_meta.canonical']);
});

it('stores an uploaded og image and persists its path', function () {
    Storage::fake('public');

    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'seo_meta.og_image' => UploadedFile::fake()->image('og.jpg', 1200, 630),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $meta = $post->fresh()->seoMeta()->sole();

    expect($meta->og_image)->toBeString()
        ->and($meta->og_image)->toStartWith('seo/');

    Storage::disk('public')->assertExists($meta->og_image);
});
