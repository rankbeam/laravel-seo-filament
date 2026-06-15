<?php

declare(strict_types=1);

use Rankbeam\Seo\Filament\Forms\SEOSchemaFields;
use Rankbeam\Seo\Services\Schema\ArticleSchema;
use Rankbeam\Seo\Services\Schema\FAQSchema;
use Rankbeam\Seo\Services\Schema\ProductSchema;

/*
 * Optimistic-concurrency reconciliation: SEOSchemaFields::reconcile() merges the
 * value the editor wants to save with any document a concurrent process wrote
 * into schema_jsonld after the form hydrated. A direct unit assertion (the
 * Livewire round-trip lives in SchemaFieldsTest) because the behaviour is pure.
 */

function reconcileFaqDoc(): array
{
    return FAQSchema::fromArray([
        ['question' => 'Q?', 'answer' => 'A.'],
        ['question' => 'Q2?', 'answer' => 'A2.'],
    ])->toArray();
}

function reconcileArticleDoc(): array
{
    return (new ArticleSchema)
        ->asBlogPosting()
        ->setHeadline('AI suggested headline')
        ->setImage('https://example.test/ai.jpg')
        ->setAuthor('AI')
        ->setDatePublished(new DateTimeImmutable('2026-01-01T00:00:00+00:00'))
        ->toArray();
}

it('passes the composed value through untouched when the column is unchanged', function () {
    $faq = reconcileFaqDoc();

    // Baseline == current: no concurrent write, editor's value wins verbatim.
    $result = SEOSchemaFields::reconcile($faq, $faq, [$faq]);

    expect($result)->toEqual($faq);
});

it('preserves a document a concurrent process added after hydration', function () {
    $faq = reconcileFaqDoc();        // present at hydration, still being saved
    $article = reconcileArticleDoc(); // written by an external process while the form was open

    // Form hydrated with only the FAQ; it is about to save the FAQ again.
    // The DB now holds FAQ + Article (the AI action ran concurrently).
    $result = SEOSchemaFields::reconcile($faq, [$faq, $article], [$faq]);

    expect($result)->toBeArray()
        ->and(array_is_list($result))->toBeTrue()
        ->and($result)->toContain($faq)
        ->and($result)->toContain($article);
});

it('keeps a concurrent document even when the editor clears its own content', function () {
    $faq = reconcileFaqDoc();
    $article = reconcileArticleDoc();

    // The editor removed its FAQ (composed === null), but an AI Article landed
    // concurrently. The Article must survive rather than the column being nulled.
    $result = SEOSchemaFields::reconcile(null, [$faq, $article], [$faq]);

    expect($result)->toEqual($article);
});

it('does not duplicate a document the editor is already writing', function () {
    $product = (new ProductSchema)
        ->setName('Widget')
        ->setImage('https://example.test/w.jpg')
        ->setPrice(9.99, 'USD')
        ->setAvailability('InStock')
        ->toArray();

    // The same product appears in both the editor's composed value and the
    // current column (e.g. a concurrent write of an identical doc) - no dupe.
    $result = SEOSchemaFields::reconcile($product, [$product], []);

    expect($result)->toEqual($product);
});

it('drops a baseline document the editor intentionally removed (no resurrection)', function () {
    $faq = reconcileFaqDoc();

    // The editor deleted its only document (composed null). The column still
    // shows that same document - but it is the baseline, not a concurrent
    // addition, so the deletion stands and nothing is resurrected.
    $result = SEOSchemaFields::reconcile(null, $faq, [$faq]);

    expect($result)->toBeNull();
});
