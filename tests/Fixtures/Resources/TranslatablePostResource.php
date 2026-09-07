<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Rankbeam\Seo\Filament\Concerns\HasSEOFields;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;

/**
 * "Translatable plugin" fixture: the page exposes an active schema locale the
 * way the spatie translatable plugins' page concern does (Filament's
 * `getActiveSchemaLocale()`), with NO explicit locale list on the section —
 * so the SEO section must follow the page's locale, one row at a time, and
 * render no tabs of its own. Both the SEO and the structured-data sections
 * are present to prove they follow the same locale.
 */
class TranslatablePostResource extends Resource
{
    use HasSEOFields;

    protected static ?string $model = Post::class;

    protected static ?string $slug = 'translatable-posts';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title'),
            TextInput::make('slug')->required(),
            static::seoSection(),
            static::seoSchemaSection(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => TranslatablePostResource\Pages\ListTranslatablePosts::route('/'),
            'edit' => TranslatablePostResource\Pages\EditTranslatablePost::route('/{record}/edit'),
        ];
    }
}
