<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Resources\ArticleResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\ArticleResource;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;
}
