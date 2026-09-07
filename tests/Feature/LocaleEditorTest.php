<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Rankbeam\Seo\Filament\Support\SeoLocales;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostLocalesResource\Pages\CreatePostLocales;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostLocalesResource\Pages\EditPostLocales;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostLocalesResource\Pages\EditPostLocalesOnTranslatablePage;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\EditPost;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\TranslatablePostResource\Pages\EditTranslatablePost;
use Rankbeam\Seo\Models\SEOMeta;

/*
|--------------------------------------------------------------------------
| The locale editor (1.9): one seo_meta row per language, one tab each
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    config(['seo.length_policy' => null, 'seo-filament.locales' => null]);
});

/*
| Explicit locales → tabs
*/

it('renders one tab per locale, labelled with the language name', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);

    Livewire::test(EditPostLocales::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertSee(SeoLocales::label('en'))
        ->assertSee(SeoLocales::label('it'))
        ->assertSee(SeoLocales::label('ja'))
        // Three editors, one per tab.
        ->assertSeeHtml('seo_meta.en.title')
        ->assertSeeHtml('seo_meta.it.title')
        ->assertSeeHtml('seo_meta.ja.title');
});

it('hydrates every locale row into its own tab', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $post->saveSEO(['title' => 'English title', 'description' => 'English description.'], 'en');
    $post->saveSEO(['title' => 'Titolo italiano', 'robots' => 'noindex, follow'], 'it');

    Livewire::test(EditPostLocales::class, ['record' => $post->getRouteKey()])
        ->assertSchemaStateSet([
            'seo_meta.en.title' => 'English title',
            'seo_meta.en.description' => 'English description.',
            'seo_meta.it.title' => 'Titolo italiano',
            'seo_meta.it.robots' => 'noindex, follow',
            'seo_meta.ja.title' => null,
        ]);
});

it('saves each locale to its own row and never creates a row for an untouched language', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    // The HasSEO created-hook made the app-locale (en) row already.
    expect(SEOMeta::query()->count())->toBe(1);

    Livewire::test(EditPostLocales::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'seo_meta.en.title' => 'English title',
            'seo_meta.it.title' => 'Titolo italiano',
            'seo_meta.it.description' => 'Descrizione italiana.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $post = $post->fresh();

    expect($post->seoMetaForLocale('en')->first()->title)->toBe('English title')
        ->and($post->seoMetaForLocale('it')->first()->title)->toBe('Titolo italiano')
        ->and($post->seoMetaForLocale('it')->first()->description)->toBe('Descrizione italiana.')
        ->and($post->seoMetaForLocale('ja')->first())->toBeNull()
        ->and(SEOMeta::query()->count())->toBe(2);
});

it('clears a value in one locale without touching the others', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $post->saveSEO(['title' => 'English title'], 'en');
    $post->saveSEO(['title' => 'Titolo italiano', 'description' => 'Resta.'], 'it');

    Livewire::test(EditPostLocales::class, ['record' => $post->getRouteKey()])
        ->fillForm(['seo_meta.it.title' => ''])
        ->call('save')
        ->assertHasNoFormErrors();

    $post = $post->fresh();

    expect($post->seoMetaForLocale('it')->first()->title)->toBeNull()
        ->and($post->seoMetaForLocale('it')->first()->description)->toBe('Resta.')
        ->and($post->seoMetaForLocale('en')->first()->title)->toBe('English title');
});

it('round-trips focus keywords per locale in the stored structured shape', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);

    Livewire::test(EditPostLocales::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'seo_meta.en.focus_keywords' => ['laravel seo', 'meta tags'],
            'seo_meta.it.focus_keywords' => ['seo laravel'],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $post = $post->fresh();

    expect($post->seoMetaForLocale('en')->first()->focus_keywords)->toBe([
        ['keyword' => 'laravel seo', 'is_primary' => true],
        ['keyword' => 'meta tags', 'is_primary' => false],
    ])->and($post->seoMetaForLocale('it')->first()->focus_keywords)->toBe([
        ['keyword' => 'seo laravel', 'is_primary' => true],
    ]);

    Livewire::test(EditPostLocales::class, ['record' => $post->getRouteKey()])
        ->assertSchemaStateSet([
            'seo_meta.en.focus_keywords' => ['laravel seo', 'meta tags'],
            'seo_meta.it.focus_keywords' => ['seo laravel'],
        ]);
});

it('stores an uploaded social image on the tab locale row only', function () {
    Storage::fake('public');
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);

    Livewire::test(EditPostLocales::class, ['record' => $post->getRouteKey()])
        ->fillForm(['seo_meta.it.og_image' => UploadedFile::fake()->image('og-it.jpg', 1200, 630)])
        ->call('save')
        ->assertHasNoFormErrors();

    $post = $post->fresh();
    $italian = $post->seoMetaForLocale('it')->first();

    expect($italian->og_image)->toBeString()->toStartWith('seo/')
        ->and($post->seoMetaForLocale('en')->first()->og_image)->toBeNull()
        ->and($post->seoMetaForLocale('ja')->first())->toBeNull();

    Storage::disk('public')->assertExists($italian->og_image);
});

it('validates every tab, not only the visible one', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);

    Livewire::test(EditPostLocales::class, ['record' => $post->getRouteKey()])
        ->fillForm(['seo_meta.ja.canonical' => 'not-a-url'])
        ->call('save')
        ->assertHasFormErrors(['seo_meta.ja.canonical']);
});

it('writes the tabs through the create page too', function () {
    config()->set('seo.features.auto_create_meta', false);

    Livewire::test(CreatePostLocales::class)
        ->fillForm([
            'title' => 'New',
            'slug' => 'new',
            'seo_meta.ja.title' => '日本語のタイトル',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $post = Post::query()->sole();

    expect($post->seoMetaForLocale('ja')->first()->title)->toBe('日本語のタイトル')
        ->and(SEOMeta::query()->count())->toBe(1);
});

/*
| Per-locale counters, previews and indicators
*/

it('gives each tab the length budget of its own language', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);

    // Empty fields: the tab's locale is the script hint — 60 for English and
    // Italian, 30 for Japanese — on the same page at the same time.
    Livewire::test(EditPostLocales::class, ['record' => $post->getRouteKey()])
        ->assertSee('0 / 60 characters')
        ->assertSee('0 / 30 characters');
});

it('judges a Japanese title in the Japanese tab against the CJK budget', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $title = mb_substr(str_repeat('検索エンジン最適化の完全ガイド', 4), 0, 45);

    Livewire::test(EditPostLocales::class, ['record' => $post->getRouteKey()])
        ->fillForm(['seo_meta.ja.title' => $title])
        ->assertSee('45 / 30 characters')
        ->assertSee('The title is 45 characters long');
});

it('labels each tab with the count of fields set in that language', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $post->saveSEO(['title' => 'Titolo', 'description' => 'Descrizione.', 'robots' => 'index, follow'], 'it');

    Livewire::test(EditPostLocales::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertSee(__('seo-filament::seo-filament.locales.badge_tooltip', [
            'count' => 3,
            'total' => 6,
            'language' => SeoLocales::label('it'),
        ]))
        // A language with nothing set carries no badge at all (and so no
        // tooltip) — the empty versions are the ones without a number.
        ->assertDontSee(__('seo-filament::seo-filament.locales.badge_tooltip', [
            'count' => 0,
            'total' => 6,
            'language' => SeoLocales::label('ja'),
        ]));
});

it('shows each language its own manual values in the source indicators', function () {
    $post = Post::query()->create(['title' => 'Hello World', 'slug' => 'hello-world', 'excerpt' => 'A short excerpt.']);
    $post->saveSEO(['title' => 'Manual English title'], 'en');
    $post->saveSEO(['title' => 'Titolo manuale italiano'], 'it');

    Livewire::test(EditPostLocales::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertSee('Manual English title')
        ->assertSee('Titolo manuale italiano');
});

/*
| Config-driven locales
*/

it('reads the locale list from config when the section is given none', function () {
    config(['seo-filament.locales' => ['en', 'de']]);
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertSeeHtml('seo_meta.en.title')
        ->assertSeeHtml('seo_meta.de.title')
        ->fillForm(['seo_meta.de.title' => 'Deutscher Titel'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->fresh()->seoMetaForLocale('de')->first()->title)->toBe('Deutscher Titel');
});

it('keeps the single-locale state shape when config lists one locale', function () {
    config(['seo-filament.locales' => ['en']]);
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $post->saveSEO(['title' => 'English title'], 'en');

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertSchemaStateSet(['seo_meta.title' => 'English title']);
});

/*
| Following a translatable plugin's active locale
*/

it('follows the page active locale instead of rendering tabs', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $post->saveSEO(['title' => 'English title'], 'en');
    $post->saveSEO(['title' => 'Titre français'], 'fr');

    // The page starts on `fr`; the app locale is `en`.
    Livewire::test(EditTranslatablePost::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet(['seo_meta.title' => 'Titre français'])
        ->assertDontSeeHtml('seo_meta.fr.title')
        ->assertDontSeeHtml('seo_meta.en.title')
        ->fillForm(['seo_meta.title' => 'Titre modifié'])
        ->call('save')
        ->assertHasNoFormErrors();

    $post = $post->fresh();

    expect($post->seoMetaForLocale('fr')->first()->title)->toBe('Titre modifié')
        ->and($post->seoMetaForLocale('en')->first()->title)->toBe('English title');
});

it('re-hydrates the row of the newly selected page locale on a switch', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);
    $post->saveSEO(['title' => 'English title'], 'en');
    $post->saveSEO(['title' => 'Titre français'], 'fr');

    Livewire::test(EditTranslatablePost::class, ['record' => $post->getRouteKey()])
        ->assertSchemaStateSet(['seo_meta.title' => 'Titre français'])
        ->call('switchLocale', 'en')
        ->assertSchemaStateSet(['seo_meta.title' => 'English title'])
        ->fillForm(['seo_meta.title' => 'English edited'])
        ->call('save')
        ->assertHasNoFormErrors();

    $post = $post->fresh();

    expect($post->seoMetaForLocale('en')->first()->title)->toBe('English edited')
        ->and($post->seoMetaForLocale('fr')->first()->title)->toBe('Titre français');
});

it('gives the followed locale its own counters, preview and indicators', function () {
    $post = Post::query()->create(['title' => 'Hello World', 'slug' => 'hello-world']);
    $post->saveSEO(['title' => 'Titre manuel'], 'fr');

    Livewire::test(EditTranslatablePost::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertSee('Titre manuel')
        ->call('switchLocale', 'ja')
        // An empty Japanese field is judged against the CJK budget.
        ->assertSee('0 / 30 characters');
});

it('writes structured data to the page active locale row as well', function () {
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);

    Livewire::test(EditTranslatablePost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'seo_schema.blocks' => [[
                'type' => 'faq',
                'questions' => [['question' => 'Pourquoi ?', 'answer' => 'Parce que.']],
            ]],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $post = $post->fresh();

    expect($post->seoMetaForLocale('fr')->first()?->schema_jsonld)->toBeArray()
        ->and($post->seoMetaForLocale('fr')->first()->schema_jsonld['@type'] ?? null)->toBe('FAQPage')
        ->and($post->seoMetaForLocale('en')->first()->schema_jsonld)->toBeNull();
});

it('lets an explicit locale list override the page locale', function () {
    // Explicit locales on the section win over the page: the developer asked
    // for tabs. The config list, by contrast, yields to the page locale.
    config(['seo-filament.locales' => ['en', 'de']]);
    $post = Post::query()->create(['title' => 'Hello', 'slug' => 'hello']);

    Livewire::test(EditTranslatablePost::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertDontSeeHtml('seo_meta.de.title')
        ->assertSeeHtml('seo_meta.title');

    // Same page contract (active locale `fr`), section given an explicit list:
    // tabs for the list, no follow mode, no tab for the page's `fr`.
    Livewire::test(EditPostLocalesOnTranslatablePage::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertSeeHtml('seo_meta.en.title')
        ->assertSeeHtml('seo_meta.ja.title')
        ->assertDontSeeHtml('seo_meta.fr.title')
        ->fillForm(['seo_meta.it.title' => 'Titolo'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->fresh()->seoMetaForLocale('it')->first()->title)->toBe('Titolo')
        ->and($post->fresh()->seoMetaForLocale('fr')->first())->toBeNull();
});

/*
| Unit: the locale resolution and labels
*/

it('labels a locale with its language name when intl is available, else its code', function () {
    if (! class_exists(Locale::class)) {
        expect(SeoLocales::label('it'))->toBe('it');

        return;
    }

    expect(SeoLocales::label('it', 'en'))->toBe('Italian')
        ->and(SeoLocales::label('it', 'it'))->toBe('italiano')
        ->and(SeoLocales::label('pt_BR', 'en'))->toBe('Portuguese (Brazil)')
        // A code intl cannot name still yields a non-blank label (intl spells
        // the parts, "xx (YY)"; without intl it is the BCP 47 code).
        ->and(SeoLocales::label('xx_YY', 'en'))->toContain('xx');
});
