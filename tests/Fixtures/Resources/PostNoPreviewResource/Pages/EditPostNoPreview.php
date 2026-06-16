<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostNoPreviewResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostNoPreviewResource;

class EditPostNoPreview extends EditRecord
{
    protected static string $resource = PostNoPreviewResource::class;
}
