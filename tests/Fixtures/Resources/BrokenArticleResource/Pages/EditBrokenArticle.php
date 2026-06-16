<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Resources\BrokenArticleResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\BrokenArticleResource;

class EditBrokenArticle extends EditRecord
{
    protected static string $resource = BrokenArticleResource::class;
}
