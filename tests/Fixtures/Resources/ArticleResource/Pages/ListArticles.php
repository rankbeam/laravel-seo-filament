<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Resources\ArticleResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\ArticleResource;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;
}
