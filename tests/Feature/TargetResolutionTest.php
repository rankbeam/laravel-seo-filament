<?php

declare(strict_types=1);

use Livewire\Livewire;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Article;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\PublicPage;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\ArticleResource\Pages\CreateArticle;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\ArticleResource\Pages\EditArticle;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\BrokenArticleResource\Pages\EditBrokenArticle;
use Rankbeam\Seo\Models\SEOMeta;
use Rankbeam\Seo\Services\Schema\BreadcrumbSchema;
use Rankbeam\Seo\Services\Schema\FAQSchema;

/**
 * A Filament resource for model A edits the SEO of a RELATED model B
 * (the form's own record never gets a seo_meta row). These cover the F4/F5
 * scenarios: create / edit / clear / locale / missing-target.
 */
it('edits the related model seo_meta on the edit page and never the article (F4)', function () {
    $article = Article::query()->create(['title' => 'Article A', 'slug' => 'article-a']);
    $page = PublicPage::query()->create(['article_id' => $article->id, 'path' => 'article-a']);

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->fillForm([
            'seo_meta.title' => 'Page SEO title',
            'seo_meta.description' => 'Page SEO description.',
            'seo_meta.robots' => 'noindex, follow',
            'seo_meta.canonical' => 'https://example.test/canonical',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    // The related PublicPage carries the SEO.
    $meta = $page->fresh()->seoMeta()->sole();

    expect($meta->title)->toBe('Page SEO title')
        ->and($meta->description)->toBe('Page SEO description.')
        ->and($meta->robots)->toBe('noindex, follow')
        ->and($meta->canonical)->toBe('https://example.test/canonical');

    // The seo_meta row belongs to the PublicPage, not the Article.
    expect(SEOMeta::query()->count())->toBe(1)
        ->and($meta->seoable_type)->toBe(PublicPage::class)
        ->and($meta->seoable_id)->toBe($page->id);
});

it('hydrates the related model stored values into the form (F4)', function () {
    $article = Article::query()->create(['title' => 'Article A', 'slug' => 'article-a']);
    $page = PublicPage::query()->create(['article_id' => $article->id, 'path' => 'article-a']);
    $page->saveSEO(['title' => 'Stored on the page', 'description' => 'Stored description.']);

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->assertFormSet([
            'seo_meta.title' => 'Stored on the page',
            'seo_meta.description' => 'Stored description.',
        ]);
});

it('renders the source indicators and preview against the related target, not the article (F4)', function () {
    $article = Article::query()->create(['title' => 'Article A', 'slug' => 'article-a']);
    $page = PublicPage::query()->create(['article_id' => $article->id, 'path' => 'article-a']);
    $page->saveSEO([
        'title' => 'Manual page title',
        'description' => 'Manual page description.',
    ]);

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->assertOk()
        // The effective-values panel rendered (it walks the resolver for the
        // resolved target — proving the View's model() closure resolved it)...
        ->assertSee('Effective values')
        ->assertSee('Manual')
        // ...and surfaces the TARGET PublicPage's stored value, not anything
        // derived from the Article (which has no SEO of its own).
        ->assertSee('Manual page title')
        ->assertSee('Manual page description.');
});

it('tolerates a not-yet-existing relation on the create page and writes nothing (F4)', function () {
    // Auto-create hook is irrelevant here: Article does not use HasSEO at all,
    // and its publicPage does not exist yet, so the target resolves to null.
    Livewire::test(CreateArticle::class)
        ->fillForm([
            'title' => 'New article',
            'slug' => 'new-article',
            'seo_meta.title' => 'Will be dropped — no related page yet',
            'seo_meta.description' => 'Also dropped.',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Article::query()->count())->toBe(1)
        ->and(PublicPage::query()->count())->toBe(0)
        ->and(SEOMeta::query()->count())->toBe(0);
});

it('clears a stored value on the related model when the field is emptied (F4)', function () {
    $article = Article::query()->create(['title' => 'Article A', 'slug' => 'article-a']);
    $page = PublicPage::query()->create(['article_id' => $article->id, 'path' => 'article-a']);
    $page->saveSEO(['title' => 'Remove me', 'description' => 'Keep me.']);

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->fillForm(['seo_meta.title' => ''])
        ->call('save')
        ->assertHasNoFormErrors();

    $meta = $page->fresh()->seoMeta()->sole();

    expect($meta->title)->toBeNull()
        ->and($meta->description)->toBe('Keep me.');
});

it('edits only the current-locale seo_meta row of the related model (F4)', function () {
    $article = Article::query()->create(['title' => 'Article A', 'slug' => 'article-a']);
    $page = PublicPage::query()->create(['article_id' => $article->id, 'path' => 'article-a']);
    $page->saveSEO(['title' => 'English', 'description' => 'English description.'], 'en');
    $page->saveSEO(['title' => 'Français', 'description' => 'Description française.'], 'fr');

    app()->setLocale('fr');

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->fillForm([
            'seo_meta.title' => 'Français modifié',
            'seo_meta.description' => 'Description française modifiée.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $fresh = $page->fresh();

    expect($fresh->seoMetaForLocale('en')->first()->title)->toBe('English')
        ->and($fresh->seoMetaForLocale('en')->first()->description)->toBe('English description.')
        ->and($fresh->seoMetaForLocale('fr')->first()->title)->toBe('Français modifié')
        ->and($fresh->seoMetaForLocale('fr')->first()->description)->toBe('Description française modifiée.');

    // Two rows total (en + fr), all on the PublicPage.
    expect(SEOMeta::query()->count())->toBe(2);
});

it('creates the related model seo_meta row on save when it did not exist yet (F4)', function () {
    // Disable the auto-create hook so the page starts with NO meta row; the
    // form save must then create the row on the related PublicPage.
    config()->set('seo.features.auto_create_meta', false);

    $article = Article::query()->create(['title' => 'Article A', 'slug' => 'article-a']);
    $page = PublicPage::query()->create(['article_id' => $article->id, 'path' => 'article-a']);

    expect(SEOMeta::query()->count())->toBe(0);

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->fillForm(['seo_meta.title' => 'First SEO title'])
        ->call('save')
        ->assertHasNoFormErrors();

    $meta = $page->fresh()->seoMeta()->sole();

    expect($meta->title)->toBe('First SEO title')
        ->and($meta->seoable_type)->toBe(PublicPage::class);
});

it('rejects a non-null target that lacks a seoMeta relation with a clear exception (F5)', function () {
    // BrokenArticleResource targets the Article itself, which does not use the
    // core HasSEO trait. Mounting the edit form resolves the target and must
    // throw a clear developer error rather than silently no-op. (Filament may
    // surface it during the lazy schema render, wrapping it in a ViewException;
    // the original RuntimeException message is preserved either way, so assert
    // on the message regardless of any wrapper class.)
    $article = Article::query()->create(['title' => 'Article A', 'slug' => 'article-a']);

    $thrown = null;

    try {
        Livewire::test(EditBrokenArticle::class, ['record' => $article->getRouteKey()]);
    } catch (Throwable $e) {
        $thrown = $e;
    }

    expect($thrown)->not->toBeNull()
        ->and($thrown->getMessage())->toContain('does not expose a seoMeta() relation')
        ->and($thrown->getMessage())->toContain(Article::class);
});

it('writes structured-data schema onto the related model, honoring the same target (F4)', function () {
    $article = Article::query()->create(['title' => 'Article A', 'slug' => 'article-a']);
    $page = PublicPage::query()->create(['article_id' => $article->id, 'path' => 'article-a']);

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->fillForm([
            'seo_schema.blocks' => [
                ['type' => 'faq', 'questions' => [
                    ['question' => 'What is it?', 'answer' => 'A related-page schema.'],
                ]],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $expected = FAQSchema::fromArray([
        ['question' => 'What is it?', 'answer' => 'A related-page schema.'],
    ])->toArray();

    // The schema landed on the PublicPage, not the Article.
    $meta = $page->fresh()->seoMeta()->sole();

    expect($meta->schema_jsonld)->toEqual($expected)
        ->and($meta->seoable_type)->toBe(PublicPage::class)
        ->and(SEOMeta::query()->count())->toBe(1);
});

it('builds the auto-breadcrumb from the related model ancestors, not the article (F4)', function () {
    $article = Article::query()->create(['title' => 'Article A', 'slug' => 'article-a']);
    $root = PublicPage::query()->create(['path' => '']);
    $page = PublicPage::query()->create([
        'article_id' => $article->id,
        'parent_id' => $root->id,
        'path' => 'article-a',
    ]);

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->fillForm(['seo_schema.auto_breadcrumb' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    $expected = BreadcrumbSchema::fromModelAncestors($page->fresh())?->toArray();

    expect($expected)->not->toBeNull();

    $meta = $page->fresh()->seoMeta()->sole();

    expect($meta->schema_jsonld)->toEqual($expected)
        ->and($meta->schema_jsonld['@type'])->toBe('BreadcrumbList');
});
