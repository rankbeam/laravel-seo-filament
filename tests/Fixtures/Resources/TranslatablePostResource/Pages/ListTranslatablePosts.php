<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Resources\TranslatablePostResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\TranslatablePostResource;

class ListTranslatablePosts extends ListRecords
{
    protected static string $resource = TranslatablePostResource::class;
}
