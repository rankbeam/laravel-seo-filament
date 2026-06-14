<?php

declare(strict_types=1);

use Rankbeam\Seo\Filament\Forms\SEOSchemaFields;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;
use Rankbeam\Seo\Services\Schema\BreadcrumbSchema;
use Rankbeam\Seo\Services\Schema\FAQSchema;
use Rankbeam\Seo\Services\Schema\ProductSchema;

it('returns empty state for null or empty stored schema', function () {
    expect(SEOSchemaFields::decompose(null, null))
        ->toBe(['auto_breadcrumb' => false, 'blocks' => [], 'custom' => []]);

    expect(SEOSchemaFields::decompose(null, []))
        ->toBe(['auto_breadcrumb' => false, 'blocks' => [], 'custom' => []]);
});

it('decomposes a single FAQ document into an editable block', function () {
    $stored = FAQSchema::fromArray([
        ['question' => 'One?', 'answer' => 'First.'],
        ['question' => 'Two?', 'answer' => 'Second.'],
    ])->toArray();

    $state = SEOSchemaFields::decompose(null, $stored);

    expect($state['auto_breadcrumb'])->toBeFalse()
        ->and($state['custom'])->toBe([])
        ->and($state['blocks'])->toBe([
            ['type' => 'faq', 'questions' => [
                ['question' => 'One?', 'answer' => 'First.'],
                ['question' => 'Two?', 'answer' => 'Second.'],
            ]],
        ]);
});

it('decomposes a Product document into editable fields with the availability token stripped', function () {
    $stored = (new ProductSchema)
        ->setName('Widget')
        ->setImage('https://example.test/w.jpg')
        ->setBrand('Acme')
        ->setSku('SKU1')
        ->setPrice(19.95, 'GBP')
        ->setAvailability('InStock')
        ->toArray();

    $state = SEOSchemaFields::decompose(null, $stored);

    expect($state['blocks'])->toHaveCount(1);

    $block = $state['blocks'][0];

    expect($block['type'])->toBe('product')
        ->and($block['name'])->toBe('Widget')
        ->and($block['image'])->toBe('https://example.test/w.jpg')
        ->and($block['brand'])->toBe('Acme')
        ->and($block['sku'])->toBe('SKU1')
        ->and($block['currency'])->toBe('GBP')
        ->and($block['availability'])->toBe('InStock')
        ->and((float) $block['price'])->toBe(19.95);
});

it('recognizes an auto-generated breadcrumb and flips the toggle', function () {
    $parent = Post::query()->create(['title' => 'Docs', 'slug' => 'docs']);
    $child = Post::query()->create(['title' => 'Guide', 'slug' => 'guide', 'parent_id' => $parent->id]);

    $stored = BreadcrumbSchema::fromModelAncestors($child)->toArray();

    $state = SEOSchemaFields::decompose($child, $stored);

    expect($state['auto_breadcrumb'])->toBeTrue()
        ->and($state['blocks'])->toBe([])
        ->and($state['custom'])->toBe([]);
});

it('preserves a document it cannot represent as custom', function () {
    $event = [
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => 'Conf',
    ];

    $state = SEOSchemaFields::decompose(null, $event);

    expect($state['blocks'])->toBe([])
        ->and($state['custom'])->toBe([$event]);
});

it('preserves a Product carrying fields the editor cannot represent', function () {
    $stored = (new ProductSchema)
        ->setName('Rich product')
        ->setImage('https://example.test/p.jpg')
        ->setPrice(10.0, 'USD')
        ->setAggregateRating(4.5, 120)
        ->toArray();

    $state = SEOSchemaFields::decompose(null, $stored);

    // aggregateRating has no editor field, so the document is kept verbatim
    // rather than silently dropped on the next save.
    expect($state['blocks'])->toBe([])
        ->and($state['custom'])->toBe([$stored]);
});

it('splits a stored list into editable blocks and preserved custom docs', function () {
    $faq = FAQSchema::fromArray([
        ['question' => 'A?', 'answer' => 'B.'],
        ['question' => 'C?', 'answer' => 'D.'],
    ])->toArray();

    $event = ['@context' => 'https://schema.org', '@type' => 'Event', 'name' => 'Conf'];

    $state = SEOSchemaFields::decompose(null, [$faq, $event]);

    expect($state['blocks'])->toBe([
        ['type' => 'faq', 'questions' => [
            ['question' => 'A?', 'answer' => 'B.'],
            ['question' => 'C?', 'answer' => 'D.'],
        ]],
    ])->and($state['custom'])->toBe([$event]);
});
