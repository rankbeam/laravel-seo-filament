<?php

declare(strict_types=1);

use Filament\Schemas\Components\View;
use Illuminate\Database\Eloquent\Model;
use Rankbeam\Seo\Filament\Support\ResolvesSeoTarget;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Article;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\PublicPage;

/**
 * Direct, framework-light coverage of the shared target resolver — the spine
 * both {@see SEOFields} and {@see SEOSchemaFields} share (F4/F5).
 */

/**
 * Public harness exposing the protected static resolver for testing.
 */
function targetHarness(): object
{
    return new class
    {
        use ResolvesSeoTarget;

        public function resolve(?Closure $target, ?Model $formRecord): ?Model
        {
            // A bare View component is enough: it carries the closure evaluator.
            return self::resolveSeoTarget($target, $formRecord, View::make('x'));
        }
    };
}

it('binds the form record when no target resolver is given', function () {
    $post = Post::query()->create(['title' => 'T', 'slug' => 'p']);

    expect(targetHarness()->resolve(null, $post))->toBe($post);
});

it('returns null when no target resolver is given and there is no form record', function () {
    expect(targetHarness()->resolve(null, null))->toBeNull();
});

it('resolves a related model through the target closure', function () {
    $article = Article::query()->create(['title' => 'A', 'slug' => 'a']);
    $page = $article->publicPage()->create(['path' => 'a']);

    $resolved = targetHarness()->resolve(
        fn (?Model $record): ?Model => $record?->publicPage,
        $article->fresh(),
    );

    expect($resolved)->toBeInstanceOf(PublicPage::class)
        ->and($resolved->is($page))->toBeTrue();
});

it('tolerates a target resolver returning null (not-yet-existing relation)', function () {
    $article = Article::query()->create(['title' => 'A', 'slug' => 'a']);

    $resolved = targetHarness()->resolve(
        fn (?Model $record): ?Model => $record?->publicPage,
        $article,
    );

    expect($resolved)->toBeNull();
});

it('passes the form record to a closure typed with the concrete model class', function () {
    $article = Article::query()->create(['title' => 'A', 'slug' => 'a']);
    $page = $article->publicPage()->create(['path' => 'a']);

    // The documented signature `?Article $r` resolves by the concrete class.
    $resolved = targetHarness()->resolve(
        fn (?Article $r): ?Model => $r?->publicPage,
        $article->fresh(),
    );

    expect($resolved?->is($page))->toBeTrue();
});

it('rejects a non-null target without a seoMeta relation', function () {
    $article = Article::query()->create(['title' => 'A', 'slug' => 'a']);

    expect(fn () => targetHarness()->resolve(fn (?Model $r): ?Model => $r, $article))
        ->toThrow(RuntimeException::class, 'does not expose a seoMeta() relation');
});

it('rejects a target resolver that returns a non-model, non-null value', function () {
    $article = Article::query()->create(['title' => 'A', 'slug' => 'a']);

    expect(fn () => targetHarness()->resolve(fn (): string => 'nope', $article))
        ->toThrow(RuntimeException::class, 'must return an Eloquent model or null');
});
