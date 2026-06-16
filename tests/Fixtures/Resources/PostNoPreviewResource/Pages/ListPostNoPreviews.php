<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostNoPreviewResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostNoPreviewResource;

class ListPostNoPreviews extends ListRecords
{
    protected static string $resource = PostNoPreviewResource::class;
}
