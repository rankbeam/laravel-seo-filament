<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;
}
