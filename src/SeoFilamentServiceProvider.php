<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament;

use Illuminate\Support\ServiceProvider;

class SeoFilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/seo-filament.php', 'seo-filament');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'seo-filament');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'seo-filament');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/seo-filament'),
            ], 'seo-filament-views');

            $this->publishes([
                __DIR__.'/../resources/lang' => $this->app->langPath('vendor/seo-filament'),
            ], 'seo-filament-lang');

            $this->publishes([
                __DIR__.'/../config/seo-filament.php' => config_path('seo-filament.php'),
            ], 'seo-filament-config');
        }
    }
}
