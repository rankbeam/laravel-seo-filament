<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Rankbeam\Seo\Filament\Concerns\HasSEOFields;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\Post;

/**
 * Opt-out fixture: the SEO section with the tabbed preview disabled via
 * `showPreview: false`. The source-indicators panel still renders.
 */
class PostNoPreviewResource extends Resource
{
    use HasSEOFields;

    protected static ?string $model = Post::class;

    protected static ?string $slug = 'no-preview-posts';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title'),
            TextInput::make('slug')->required(),
            static::seoSection(showPreview: false),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => PostNoPreviewResource\Pages\ListPostNoPreviews::route('/'),
            'edit' => PostNoPreviewResource\Pages\EditPostNoPreview::route('/{record}/edit'),
        ];
    }
}
