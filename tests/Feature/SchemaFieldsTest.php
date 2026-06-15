<?php

declare(strict_types=1);

use Livewire\Livewire;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\CreatePost;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\EditPost;
use Rankbeam\Seo\Models\SEOMeta;
use Rankbeam\Seo\Services\Schema\BreadcrumbSchema;
use Rankbeam\Seo\Services\Schema\FAQSchema;
use Rankbeam\Seo\Services\Schema\ProductSchema;

it('renders the structured-data section', function () {
    Livewire::test(CreatePost::class)
        ->assertOk()
        ->assertSee('Structured data')
        ->assertSee('Automatic breadcrumb');
});

it('builds and stores an FAQ document from the repeater', function () {
    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'FAQ post',
            'slug' => 'faq-post',
            'seo_schema.blocks' => [
                ['type' => 'faq', 'questions' => [
                    ['question' => 'What is it?', 'answer' => 'A Laravel SEO package.'],
                    ['question' => 'Is it free?', 'answer' => 'The core is MIT licensed.'],
                ]],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $expected = FAQSchema::fromArray([
        ['question' => 'What is it?', 'answer' => 'A Laravel SEO package.'],
        ['question' => 'Is it free?', 'answer' => 'The core is MIT licensed.'],
    ])->toArray();

    $meta = Post::query()->sole()->seoMeta()->sole();

    expect($meta->schema_jsonld)->toEqual($expected)
        ->and($meta->schema_jsonld['@type'])->toBe('FAQPage');

    expect(SEOMeta::query()->count())->toBe(1);
});

it('round-trips a stored FAQ document unchanged through the edit page', function () {
    $stored = FAQSchema::fromArray([
        ['question' => 'Q one?', 'answer' => 'A one.'],
        ['question' => 'Q two?', 'answer' => 'A two.'],
    ])->toArray();

    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $post->saveSEO(['schema_jsonld' => $stored]);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->fresh()->seoMeta()->sole()->schema_jsonld)->toEqual($stored);
});

it('edits schema only on the current locale seo meta row', function () {
    $english = FAQSchema::fromArray([
        ['question' => 'English question?', 'answer' => 'English answer.'],
        ['question' => 'Another English question?', 'answer' => 'Another English answer.'],
    ])->toArray();
    $french = FAQSchema::fromArray([
        ['question' => 'Question française?', 'answer' => 'Réponse française.'],
        ['question' => 'Autre question française?', 'answer' => 'Autre réponse française.'],
    ])->toArray();

    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $post->saveSEO(['schema_jsonld' => $english], 'en');
    $post->saveSEO(['schema_jsonld' => $french], 'fr');

    app()->setLocale('fr');

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'seo_schema.blocks' => [
                ['type' => 'faq', 'questions' => [
                    ['question' => 'Question française modifiée?', 'answer' => 'Réponse française modifiée.'],
                    ['question' => 'Autre question française modifiée?', 'answer' => 'Autre réponse française modifiée.'],
                ]],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $updatedFrench = FAQSchema::fromArray([
        ['question' => 'Question française modifiée?', 'answer' => 'Réponse française modifiée.'],
        ['question' => 'Autre question française modifiée?', 'answer' => 'Autre réponse française modifiée.'],
    ])->toArray();

    expect($post->fresh()->seoMetaForLocale('en')->first()->schema_jsonld)->toEqual($english)
        ->and($post->fresh()->seoMetaForLocale('fr')->first()->schema_jsonld)->toEqual($updatedFrench);
});

it('builds and stores a Product document from the repeater', function () {
    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Product post',
            'slug' => 'product-post',
            'seo_schema.blocks' => [
                [
                    'type' => 'product',
                    'name' => 'Wonder Widget',
                    'description' => 'The best widget.',
                    'image' => 'https://example.test/widget.jpg',
                    'brand' => 'Acme',
                    'sku' => 'WW-1',
                    'price' => '99.99',
                    'currency' => 'EUR',
                    'availability' => 'InStock',
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $expected = (new ProductSchema)
        ->setName('Wonder Widget')
        ->setDescription('The best widget.')
        ->setImage('https://example.test/widget.jpg')
        ->setBrand('Acme')
        ->setSku('WW-1')
        ->setPrice(99.99, 'EUR')
        ->setAvailability('InStock')
        ->toArray();

    $meta = Post::query()->sole()->seoMeta()->sole();

    expect($meta->schema_jsonld)->toEqual($expected)
        ->and($meta->schema_jsonld['@type'])->toBe('Product')
        ->and($meta->schema_jsonld['offers']['availability'])->toBe('https://schema.org/InStock');
});

it('round-trips a stored Product document unchanged through the edit page', function () {
    $stored = (new ProductSchema)
        ->setName('Round Trip')
        ->setImage('https://example.test/rt.jpg')
        ->setPrice(12.5, 'USD')
        ->setAvailability('OutOfStock')
        ->toArray();

    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $post->saveSEO(['schema_jsonld' => $stored]);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->fresh()->seoMeta()->sole()->schema_jsonld)->toEqual($stored);
});

it('generates a breadcrumb from the model ancestors with one toggle', function () {
    $parent = Post::query()->create(['title' => 'Docs', 'slug' => 'docs']);
    $child = Post::query()->create(['title' => 'Guide', 'slug' => 'guide', 'parent_id' => $parent->id]);

    Livewire::test(EditPost::class, ['record' => $child->getRouteKey()])
        ->fillForm(['seo_schema.auto_breadcrumb' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    $expected = BreadcrumbSchema::fromModelAncestors($child->fresh())->toArray();

    $stored = $child->fresh()->seoMeta()->sole()->schema_jsonld;

    expect($stored)->toEqual($expected)
        ->and($stored['@type'])->toBe('BreadcrumbList')
        ->and($stored['itemListElement'])->toHaveCount(3);
});

it('hydrates the breadcrumb toggle back on for an auto-generated breadcrumb', function () {
    $parent = Post::query()->create(['title' => 'Docs', 'slug' => 'docs']);
    $child = Post::query()->create(['title' => 'Guide', 'slug' => 'guide', 'parent_id' => $parent->id]);
    $child->saveSEO(['schema_jsonld' => BreadcrumbSchema::fromModelAncestors($child)->toArray()]);

    Livewire::test(EditPost::class, ['record' => $child->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet(['seo_schema.auto_breadcrumb' => true]);
});

it('refreshes a stale breadcrumb instead of freezing or duplicating it after an ancestor rename', function () {
    $parent = Post::query()->create(['title' => 'Docs', 'slug' => 'docs']);
    $child = Post::query()->create(['title' => 'Guide', 'slug' => 'guide', 'parent_id' => $parent->id]);
    $child->saveSEO(['schema_jsonld' => BreadcrumbSchema::fromModelAncestors($child)->toArray()]);

    // Ancestor renamed after the breadcrumb was first stored.
    $parent->update(['title' => 'Documentation']);

    // The toggle still hydrates on (provenance), and a plain save regenerates
    // the breadcrumb from the current ancestor chain - no duplicate document.
    Livewire::test(EditPost::class, ['record' => $child->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet(['seo_schema.auto_breadcrumb' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    $stored = $child->fresh()->seoMeta()->sole()->schema_jsonld;

    // Exactly one BreadcrumbList, carrying the renamed ancestor.
    expect($stored['@type'])->toBe('BreadcrumbList');

    $names = array_column($stored['itemListElement'], 'name');

    expect($names)->toContain('Documentation')
        ->and($names)->not->toContain('Docs');
});

it('preserves a foreign top-level breadcrumb on a record with ancestors across an unrelated save (F5)', function () {
    // A record that genuinely has ancestors (so it CAN generate its own
    // breadcrumb) but whose stored breadcrumb was authored elsewhere (different
    // URLs / depth than the package would produce). An unrelated form save must
    // leave it byte-for-byte intact instead of overwriting it with the
    // package's generated version.
    $parent = Post::query()->create(['title' => 'Docs', 'slug' => 'docs']);
    $child = Post::query()->create(['title' => 'Guide', 'slug' => 'guide', 'parent_id' => $parent->id]);

    $foreign = BreadcrumbSchema::fromArray([
        ['name' => 'Home', 'url' => 'https://example.test/'],
        ['name' => 'Knowledge Base', 'url' => 'https://example.test/kb'],
        ['name' => 'How-to', 'url' => 'https://example.test/kb/how-to'],
        ['name' => 'This Guide', 'url' => 'https://example.test/kb/how-to/guide'],
    ])->toArray();

    $child->saveSEO(['schema_jsonld' => $foreign]);

    // The toggle must NOT hydrate on for a breadcrumb the editor did not author.
    Livewire::test(EditPost::class, ['record' => $child->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet(['seo_schema.auto_breadcrumb' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    $stored = $child->fresh()->seoMeta()->sole()->schema_jsonld;

    // Preserved verbatim — exactly the foreign breadcrumb, no duplicate, not the
    // package's generated one.
    expect($stored)->toEqual($foreign);
});

it('round-trips the breadcrumb toggle off then on without duplicating', function () {
    $parent = Post::query()->create(['title' => 'Docs', 'slug' => 'docs']);
    $child = Post::query()->create(['title' => 'Guide', 'slug' => 'guide', 'parent_id' => $parent->id]);

    // Toggle ON: an auto-breadcrumb is generated and stored.
    Livewire::test(EditPost::class, ['record' => $child->getRouteKey()])
        ->fillForm(['seo_schema.auto_breadcrumb' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($child->fresh()->seoMeta()->sole()->schema_jsonld['@type'])->toBe('BreadcrumbList');

    // Toggle OFF: the auto-breadcrumb (recognized as the editor's own) is
    // dropped, leaving the column cleared.
    Livewire::test(EditPost::class, ['record' => $child->getRouteKey()])
        ->assertSchemaStateSet(['seo_schema.auto_breadcrumb' => true])
        ->fillForm(['seo_schema.auto_breadcrumb' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($child->fresh()->seoMeta()->sole()->schema_jsonld)->toBeNull();

    // Toggle ON again: regenerated, exactly one BreadcrumbList — never doubled.
    Livewire::test(EditPost::class, ['record' => $child->getRouteKey()])
        ->assertSchemaStateSet(['seo_schema.auto_breadcrumb' => false])
        ->fillForm(['seo_schema.auto_breadcrumb' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    $stored = $child->fresh()->seoMeta()->sole()->schema_jsonld;

    expect($stored['@type'])->toBe('BreadcrumbList')
        ->and($stored)->toEqual(BreadcrumbSchema::fromModelAncestors($child->fresh())->toArray());
});

it('rejects a Product block with no image or offer', function () {
    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Bad product',
            'slug' => 'bad-product',
            'seo_schema.blocks' => [
                ['type' => 'product', 'name' => 'No offer widget'],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['seo_schema.blocks']);

    expect(Post::query()->count())->toBe(0);
});

it('rejects an FAQ block with a question but no answer', function () {
    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Bad faq',
            'slug' => 'bad-faq',
            'seo_schema.blocks' => [
                ['type' => 'faq', 'questions' => [
                    ['question' => 'A question with no answer?', 'answer' => ''],
                ]],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['seo_schema.blocks']);

    expect(Post::query()->count())->toBe(0);
});

it('preserves stored schema it cannot represent', function () {
    $event = [
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => 'Laravel Conf',
        'startDate' => '2026-09-01',
    ];

    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $post->saveSEO(['schema_jsonld' => $event]);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'seo_schema.blocks' => [
                ['type' => 'faq', 'questions' => [
                    ['question' => 'Where?', 'answer' => 'Online.'],
                    ['question' => 'When?', 'answer' => 'September.'],
                ]],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $stored = $post->fresh()->seoMeta()->sole()->schema_jsonld;

    // Both the new FAQ block and the untouched Event are kept (as a list).
    expect($stored)->toBeArray()
        ->and(array_is_list($stored))->toBeTrue()
        ->and($stored)->toContain($event);

    $types = array_column($stored, '@type');
    expect($types)->toContain('FAQPage')->toContain('Event');
});

it('does not clobber a schema document written concurrently after the form hydrated', function () {
    // The page starts with an editor-authored FAQ.
    $faq = FAQSchema::fromArray([
        ['question' => 'Original?', 'answer' => 'Yes.'],
        ['question' => 'Second?', 'answer' => 'Also yes.'],
    ])->toArray();

    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $post->saveSEO(['schema_jsonld' => $faq]);

    // The editor opens (hydrates, snapshotting the column as it stands now).
    $component = Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertOk();

    // Meanwhile, a concurrent process (e.g. the Pro dashboard's AI schema
    // action) writes a NEW document into the same column.
    $aiArticle = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => 'AI generated headline',
    ];
    $post->saveSEO(['schema_jsonld' => [$faq, $aiArticle]]);

    // The stale form is saved without touching the schema section.
    $component->call('save')->assertHasNoFormErrors();

    $stored = $post->fresh()->seoMeta()->sole()->schema_jsonld;

    // The concurrently-added Article survives; the editor's FAQ is still there.
    expect($stored)->toBeArray()
        ->and(array_is_list($stored))->toBeTrue()
        ->and($stored)->toContain($aiArticle)
        ->and($stored)->toContain($faq);

    $types = array_column($stored, '@type');
    expect($types)->toContain('FAQPage')->toContain('Article');
});

it('clears the stored schema when everything is removed', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $post->saveSEO([
        'title' => 'Keep me',
        'schema_jsonld' => FAQSchema::fromArray([
            ['question' => 'Q?', 'answer' => 'A.'],
            ['question' => 'Q2?', 'answer' => 'A2.'],
        ])->toArray(),
    ]);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm(['seo_schema.blocks' => []])
        ->call('save')
        ->assertHasNoFormErrors();

    $meta = $post->fresh()->seoMeta()->sole();

    expect($meta->schema_jsonld)->toBeNull()
        ->and($meta->title)->toBe('Keep me');
});

it('stores multiple documents as a list (breadcrumb + FAQ)', function () {
    $parent = Post::query()->create(['title' => 'Docs', 'slug' => 'docs']);
    $child = Post::query()->create(['title' => 'Guide', 'slug' => 'guide', 'parent_id' => $parent->id]);

    Livewire::test(EditPost::class, ['record' => $child->getRouteKey()])
        ->fillForm([
            'seo_schema.auto_breadcrumb' => true,
            'seo_schema.blocks' => [
                ['type' => 'faq', 'questions' => [
                    ['question' => 'Q?', 'answer' => 'A.'],
                    ['question' => 'Q2?', 'answer' => 'A2.'],
                ]],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $stored = $child->fresh()->seoMeta()->sole()->schema_jsonld;

    expect($stored)->toBeArray()
        ->and(array_is_list($stored))->toBeTrue()
        ->and($stored)->toHaveCount(2)
        ->and($stored[0]['@type'])->toBe('BreadcrumbList')
        ->and($stored[1]['@type'])->toBe('FAQPage');
});

it('writes nothing when the schema section is left empty', function () {
    config()->set('seo.features.auto_create_meta', false);

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Plain',
            'slug' => 'plain',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Post::query()->count())->toBe(1)
        ->and(SEOMeta::query()->count())->toBe(0);
});
