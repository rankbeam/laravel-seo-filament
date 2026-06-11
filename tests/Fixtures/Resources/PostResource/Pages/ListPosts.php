<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;
}
