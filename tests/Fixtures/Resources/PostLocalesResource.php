<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Rankbeam\Seo\Filament\Concerns\HasSEOFields;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;

/**
 * Multi-locale fixture: the SEO section with an explicit locale list, so it
 * renders one tab per language, each bound to `seo_meta.{locale}`.
 */
class PostLocalesResource extends Resource
{
    use HasSEOFields;

    protected static ?string $model = Post::class;

    protected static ?string $slug = 'locale-posts';

    public const LOCALES = ['en', 'it', 'ja'];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title'),
            TextInput::make('slug')->required(),
            static::seoSection(locales: self::LOCALES),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => PostLocalesResource\Pages\ListPostLocales::route('/'),
            'create' => PostLocalesResource\Pages\CreatePostLocales::route('/create'),
            'edit' => PostLocalesResource\Pages\EditPostLocales::route('/{record}/edit'),
        ];
    }
}
