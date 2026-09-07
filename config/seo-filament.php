<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| rankbeam/laravel-seo-filament
|--------------------------------------------------------------------------
|
| Publish with:
|
|     php artisan vendor:publish --tag=seo-filament-config
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Locales the SEO editor offers
    |--------------------------------------------------------------------------
    |
    | The core keeps one seo_meta row per (model, locale). List the locales
    | your pages are published in and every SEO section renders one tab per
    | language — each editing its own row, with counters for that language's
    | script, its own preview and its own fallback indicators:
    |
    |     'locales' => ['en', 'it', 'ja'],
    |
    | A single resource can override this with SEOFields::make(locales: [...]).
    | When the page runs a translatable plugin (Filament's
    | getActiveSchemaLocale() returns the locale its header switcher selected)
    | the section follows that locale instead and this list is not consulted.
    |
    | Null (the default) = edit the app locale's row only, as before 1.9.
    |
    */

    'locales' => null,

];
