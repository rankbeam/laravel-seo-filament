<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;
}
