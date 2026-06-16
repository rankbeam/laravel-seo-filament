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
 * A deliberately MISCONFIGURED resource (F5): its `target` resolves to a model
 * that does NOT use the core HasSEO trait (the Article itself). Resolution must
 * reject it with a clear developer exception rather than silently misbehaving.
 */
class BrokenArticleResource extends Resource
{
    use HasSEOFields;

    protected static ?string $model = Article::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title'),
            // Target the Article itself, which has no seoMeta() relation.
            static::seoSection(target: fn (?Model $record): ?Model => $record),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => BrokenArticleResource\Pages\ListBrokenArticles::route('/'),
            'edit' => BrokenArticleResource\Pages\EditBrokenArticle::route('/{record}/edit'),
        ];
    }
}
