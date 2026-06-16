<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Resources\BrokenArticleResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\BrokenArticleResource;

class ListBrokenArticles extends ListRecords
{
    protected static string $resource = BrokenArticleResource::class;
}
