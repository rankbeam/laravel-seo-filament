<?php

declare(strict_types=1);

use Filament\Forms\Components\Field;
use Livewire\Livewire;
use Rankbeam\Seo\Filament\Forms\SEOFields;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\CreatePost;

afterEach(function () {
    SEOFields::flushFieldModifiers();
});

it('applies registered field modifiers when the section is built', function () {
    SEOFields::modifyFieldUsing('title', function (Field $field): Field {
        return $field->helperText('Modified by an add-on');
    });

    Livewire::test(CreatePost::class)
        ->assertOk()
        ->assertSee('Modified by an add-on');
});

it('ignores modifiers for fields excluded via $only', function () {
    SEOFields::modifyFieldUsing('title', fn (Field $field): Field => $field->helperText('Title add-on'));

    $section = SEOFields::make(['description']);

    expect($section)->not->toBeNull();

    // The title field (and with it the modifier) is simply absent.
    Livewire::test(CreatePost::class)->assertOk();
});

it('keeps the field when a modifier returns null', function () {
    SEOFields::modifyFieldUsing('title', function (Field $field): ?Field {
        $field->helperText('Mutated in place');

        return null;
    });

    Livewire::test(CreatePost::class)
        ->assertOk()
        ->assertSee('Mutated in place');
});
