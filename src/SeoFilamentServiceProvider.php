<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament;

use Illuminate\Support\ServiceProvider;

class SeoFilamentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'seo-filament');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/seo-filament'),
            ], 'seo-filament-views');
        }
    }
}
