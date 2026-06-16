<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Rankbeam\Seo\Filament\Concerns\HasSEOFields;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Article;

/**
 * T4 acceptance fixture: a resource for {@see Article} that edits the SEO of a
 * RELATED model (its PublicPage). The same `target` resolver drives both the
 * SEO fields and the structured-data section, so they act on one consistent
 * model. Article itself never gets a seo_meta row.
 */
class ArticleResource extends Resource
{
    use HasSEOFields;

    protected static ?string $model = Article::class;

    /**
     * The single source of truth for "which model carries this article's SEO".
     */
    public static function seoTarget(): \Closure
    {
        return fn (?Model $record): ?Model => $record?->publicPage;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title'),
            TextInput::make('slug')->required(),
            static::seoSection(target: static::seoTarget()),
            static::seoSchemaSection(target: static::seoTarget()),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ArticleResource\Pages\ListArticles::route('/'),
            'create' => ArticleResource\Pages\CreateArticle::route('/create'),
            'edit' => ArticleResource\Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
