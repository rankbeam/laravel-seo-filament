<?php

declare(strict_types=1);

use Livewire\Livewire;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\EditPost;

/*
|--------------------------------------------------------------------------
| Script-aware counters (core LengthPolicy, 3.15)
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    config(['seo.length_policy' => null]);
    $this->post = Post::query()->create(['title' => 'Hello World', 'slug' => 'hello-world']);
});

it('judges a Japanese title against the CJK budget', function () {
    $title = mb_substr(str_repeat('検索エンジン最適化の完全ガイド', 4), 0, 45);

    Livewire::test(EditPost::class, ['record' => $this->post->getRouteKey()])
        ->fillForm(['seo_meta.title' => $title])
        ->assertSee('45 / 30 characters')
        ->assertSee('The title is 45 characters long');
});

it('keeps the Latin budget for a Latin title of the same length', function () {
    Livewire::test(EditPost::class, ['record' => $this->post->getRouteKey()])
        ->fillForm(['seo_meta.title' => str_repeat('a', 45)])
        ->assertSee('45 / 60 characters')
        ->assertDontSee('The title is 45 characters long');
});

it('judges a Chinese description against the CJK budget', function () {
    Livewire::test(EditPost::class, ['record' => $this->post->getRouteKey()])
        ->fillForm(['seo_meta.description' => str_repeat('搜索引擎优化', 17)])
        ->assertSee('102 / 80 characters')
        ->assertSee('The description is 102 characters long');
});

it('counts graphemes, so Thai vowel marks are not counted twice', function () {
    Livewire::test(EditPost::class, ['record' => $this->post->getRouteKey()])
        ->fillForm(['seo_meta.title' => str_repeat('สี', 40)]) // 80 codepoints, 40 graphemes
        ->assertSee('40 / 60 characters')
        ->assertDontSee('The title is 40 characters long');
});

it('uses the app locale as the hint for an empty field', function () {
    app()->setLocale('ja');

    Livewire::test(EditPost::class, ['record' => $this->post->getRouteKey()])
        ->assertSee('0 / 30 '.__('seo-filament::seo-filament.fields.counter', ['length' => '', 'max' => ''], 'en') === '' ? '0 / 30' : '0 / 30');
});

it('honours a configured per-script row', function () {
    config(['seo.length_policy' => ['cjk' => ['title_max' => 50]]]);

    Livewire::test(EditPost::class, ['record' => $this->post->getRouteKey()])
        ->fillForm(['seo_meta.title' => str_repeat('日', 45)])
        ->assertSee('45 / 50 characters')
        ->assertDontSee('The title is 45 characters long');
});
