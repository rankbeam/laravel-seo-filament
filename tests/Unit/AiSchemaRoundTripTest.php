<?php

declare(strict_types=1);

use Rankbeam\Seo\Filament\Forms\SEOSchemaFields;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;
use Rankbeam\Seo\Services\Schema\ArticleSchema;
use Rankbeam\Seo\Services\Schema\BreadcrumbSchema;
use Rankbeam\Seo\Services\Schema\ProductSchema;

/*
 * Carry-forward proof: a document the Pro AI schema suggestion builds with
 * the core builders and writes into seo_meta.schema_jsonld must round-trip
 * through this editor unchanged - a Product becomes an editable block, an
 * Article (no editor block) is preserved verbatim, and an ancestor breadcrumb
 * flips the toggle. The editor's own compose() on the next save must never
 * clobber an AI-suggested document.
 */

it('round-trips an AI-suggested Product through the editor as an editable block', function () {
    // Exactly what Rankbeam\Seo\Pro\Ai\Schema\SchemaProposalBuilder emits for a
    // Product proposal (the same core ProductSchema setters).
    $document = (new ProductSchema)
        ->setName('Acme Pro Grinder')
        ->setDescription('A burr grinder for home espresso.')
        ->setImage('https://example.test/grinder.jpg')
        ->setBrand('Acme')
        ->setSku('APG-1')
        ->setPrice(299.0, 'USD')
        ->setAvailability('InStock')
        ->toArray();

    $state = SEOSchemaFields::decompose(null, $document);

    expect($state['blocks'])->toHaveCount(1)
        ->and($state['blocks'][0]['type'])->toBe('product')
        ->and($state['custom'])->toBe([]);

    expect(SEOSchemaFields::compose(null, $state))->toEqual($document);
});

it('round-trips an AI-suggested Article verbatim as custom (no editor block to clobber it)', function () {
    // Resembles SchemaProposalBuilder's Article output (core ArticleSchema).
    $document = (new ArticleSchema)
        ->asBlogPosting()
        ->setHeadline('Why the Acme Pro Grinder Wins')
        ->setDescription('An editorial take on the grinder.')
        ->setImage('https://example.test/cover.jpg')
        ->setAuthor('Jane Doe')
        ->setDatePublished(new DateTimeImmutable('2026-01-01T00:00:00+00:00'))
        ->toArray();

    $state = SEOSchemaFields::decompose(null, $document);

    expect($state['blocks'])->toBe([])
        ->and($state['custom'])->toBe([$document]);

    expect(SEOSchemaFields::compose(null, $state))->toEqual($document);
});

it('round-trips an AI-suggested ancestor breadcrumb through the toggle', function () {
    $parent = Post::query()->create(['title' => 'Docs', 'slug' => 'docs']);
    $child = Post::query()->create(['title' => 'Guide', 'slug' => 'guide', 'parent_id' => $parent->id]);

    $document = BreadcrumbSchema::fromModelAncestors($child)->toArray();

    $state = SEOSchemaFields::decompose($child, $document);

    expect($state['auto_breadcrumb'])->toBeTrue()
        ->and($state['blocks'])->toBe([])
        ->and($state['custom'])->toBe([]);

    expect(SEOSchemaFields::compose($child, $state))->toEqual($document);
});

it('appends an AI-suggested document beside existing editor content without clobbering it', function () {
    // A page that already has a Product block, then an Article suggestion is
    // applied (stored as a second document). Re-opening the editor must keep
    // BOTH - the Product editable, the Article preserved as custom.
    $product = (new ProductSchema)
        ->setName('Acme Pro Grinder')
        ->setImage('https://example.test/grinder.jpg')
        ->setPrice(299.0, 'USD')
        ->setAvailability('InStock')
        ->toArray();

    $article = (new ArticleSchema)
        ->asBlogPosting()
        ->setHeadline('Why the Acme Pro Grinder Wins')
        ->setImage('https://example.test/cover.jpg')
        ->setAuthor('Jane Doe')
        ->setDatePublished(new DateTimeImmutable('2026-01-01T00:00:00+00:00'))
        ->toArray();

    $state = SEOSchemaFields::decompose(null, [$product, $article]);

    expect($state['blocks'])->toHaveCount(1)
        ->and($state['blocks'][0]['type'])->toBe('product')
        ->and($state['custom'])->toBe([$article]);

    expect(SEOSchemaFields::compose(null, $state))->toEqual([$product, $article]);
});
