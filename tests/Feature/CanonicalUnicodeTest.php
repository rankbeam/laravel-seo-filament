<?php

declare(strict_types=1);

use Livewire\Livewire;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\EditPost;

/*
|--------------------------------------------------------------------------
| The canonical field accepts internationalised URLs (URL policy, core 3.15)
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->post = Post::query()->create(['title' => 'Hello World', 'slug' => 'hello-world']);
});

it('accepts an IDN host and a Unicode path', function (string $canonical) {
    Livewire::test(EditPost::class, ['record' => $this->post->getRouteKey()])
        ->fillForm(['seo_meta.canonical' => $canonical])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($this->post->fresh()->seoMeta?->canonical)->toBe($canonical);
})->with([
    'IDN host' => ['https://münchen.example/straße'],
    'CJK path' => ['https://example.jp/検索/結果'],
    'percent-encoded path' => ['https://example.com/stra%C3%9Fe'],
]);

it('still rejects a value that is not a URL', function () {
    Livewire::test(EditPost::class, ['record' => $this->post->getRouteKey()])
        ->fillForm(['seo_meta.canonical' => 'not a url'])
        ->call('save')
        ->assertHasFormErrors(['seo_meta.canonical']);
});
