<?php

declare(strict_types=1);

use Fibonoir\LaravelSEO\Services\SEOWarningEvaluator;
use Livewire\Livewire;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\EditPost;

beforeEach(function () {
    $this->post = Post::query()->create(['title' => 'Hello World', 'slug' => 'hello-world']);
});

it('shows the empty-state counters with the fallback notice', function () {
    Livewire::test(EditPost::class, ['record' => $this->post->getRouteKey()])
        ->assertSee('0 / '.SEOWarningEvaluator::TITLE_MAX_LENGTH.' characters')
        ->assertSee('0 / '.SEOWarningEvaluator::DESCRIPTION_MAX_LENGTH.' characters')
        ->assertSee('No SEO title set')
        ->assertSee('No SEO description set');
});

it('shows a plain counter for a title within the limit', function () {
    // The full warning sentence ("The title is N characters long…") is
    // rendered server-side by the evaluator-backed helper text, so its
    // absence proves the within-limit state (the generic copy also exists
    // inside the preview's client-side JS and cannot be asserted against).
    Livewire::test(EditPost::class, ['record' => $this->post->getRouteKey()])
        ->fillForm(['seo_meta.title' => str_repeat('a', 30)])
        ->assertSee('30 / '.SEOWarningEvaluator::TITLE_MAX_LENGTH.' characters')
        ->assertDontSee('The title is 30 characters long');
});

it('warns when the title exceeds the evaluator threshold', function () {
    Livewire::test(EditPost::class, ['record' => $this->post->getRouteKey()])
        ->fillForm(['seo_meta.title' => str_repeat('a', 70)])
        ->assertSee('70 / '.SEOWarningEvaluator::TITLE_MAX_LENGTH.' characters')
        ->assertSee('The title is 70 characters long');
});

it('warns when the description exceeds the evaluator threshold', function () {
    Livewire::test(EditPost::class, ['record' => $this->post->getRouteKey()])
        ->fillForm(['seo_meta.description' => str_repeat('b', 200)])
        ->assertSee('200 / '.SEOWarningEvaluator::DESCRIPTION_MAX_LENGTH.' characters')
        ->assertSee('The description is 200 characters long');
});

it('counts multibyte characters correctly', function () {
    Livewire::test(EditPost::class, ['record' => $this->post->getRouteKey()])
        ->fillForm(['seo_meta.title' => str_repeat('à', 61)])
        ->assertSee('61 / '.SEOWarningEvaluator::TITLE_MAX_LENGTH.' characters');
});
