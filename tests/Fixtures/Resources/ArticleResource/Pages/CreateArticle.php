<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Resources\ArticleResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\ArticleResource;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;
}
