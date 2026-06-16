<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures;

use Filament\Panel;
use Filament\PanelProvider;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\ArticleResource;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\BrokenArticleResource;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostNoPreviewResource;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostResource;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->resources([
                PostResource::class,
                PostNoPreviewResource::class,
                ArticleResource::class,
                BrokenArticleResource::class,
            ]);
    }
}
