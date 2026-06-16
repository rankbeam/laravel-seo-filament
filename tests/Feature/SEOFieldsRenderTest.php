<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Livewire\Livewire;
use Rankbeam\Seo\Filament\Forms\SEOFields;
use Rankbeam\Seo\Filament\Forms\SEOSchemaFields;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\CreatePost;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages\EditPost;
use Rankbeam\Seo\Models\SEOMeta;

it('renders the SEO section on the create page', function () {
    Livewire::test(CreatePost::class)
        ->assertOk()
        ->assertSee('SEO')
        ->assertSee('SEO title')
        ->assertSee('SEO description')
        ->assertSee('Canonical URL')
        ->assertSee('Robots directive')
        ->assertSee('Social sharing image');
});

it('renders the search snippet preview', function () {
    $post = Post::query()->create(['title' => 'Hello World', 'slug' => 'hello-world']);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertSee('Search result preview');
});

it('renders all robots directive options', function () {
    // Labels containing apostrophes are JS-encoded inside the Select's
    // Alpine options payload, so assert on the raw directive values.
    Livewire::test(CreatePost::class)
        ->assertOk()
        ->assertSee('Index, follow links')
        ->assertSeeHtml('noindex, nofollow');
});

it('hydrates stored seo_meta values into the form on edit', function () {
    $post = Post::query()->create(['title' => 'Hello World', 'slug' => 'hello-world']);
    $post->saveSEO([
        'title' => 'Stored SEO title',
        'description' => 'Stored SEO description',
        'robots' => 'noindex, follow',
        'canonical' => 'https://example.test/custom-canonical',
    ]);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet([
            'seo_meta.title' => 'Stored SEO title',
            'seo_meta.description' => 'Stored SEO description',
            'seo_meta.robots' => 'noindex, follow',
            'seo_meta.canonical' => 'https://example.test/custom-canonical',
        ]);
});

it('can read seo meta from a core-2-style target without seoMetaForLocale()', function () {
    $target = new class extends Model
    {
        protected $table = 'posts';

        protected $guarded = [];

        public function seoMeta(): MorphOne
        {
            return $this->morphOne(SEOMeta::class, 'seoable');
        }
    };
    $target->forceFill(['title' => 'Legacy', 'slug' => 'legacy'])->save();

    $meta = $target->seoMeta()->create([
        'locale' => 'en',
        'title' => 'Legacy title',
        'schema_jsonld' => ['@context' => 'https://schema.org', '@type' => 'FAQPage'],
    ]);

    $seoCurrentMeta = new ReflectionMethod(SEOFields::class, 'currentMeta');
    $schemaCurrentMeta = new ReflectionMethod(SEOSchemaFields::class, 'currentMeta');
    $seoCurrentMeta->setAccessible(true);
    $schemaCurrentMeta->setAccessible(true);

    expect($seoCurrentMeta->invoke(null, $target, 'en')->is($meta))->toBeTrue()
        ->and($schemaCurrentMeta->invoke(null, $target, 'en')->is($meta))->toBeTrue();
});
