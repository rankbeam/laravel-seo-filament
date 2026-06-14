<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Concerns;

use Filament\Schemas\Components\Section;
use Rankbeam\Seo\Filament\Forms\SEOFields;
use Rankbeam\Seo\Filament\Forms\SEOSchemaFields;

/**
 * Convenience for Filament resources: adds a ready-made SEO section.
 *
 * ```php
 * class PostResource extends Resource
 * {
 *     use HasSEOFields;
 *
 *     public static function form(Schema $schema): Schema
 *     {
 *         return $schema->components([
 *             TextInput::make('title'),
 *             // ...
 *             static::seoSection(),
 *         ]);
 *     }
 * }
 * ```
 *
 * The resource's model must use the core HasSEO trait.
 */
trait HasSEOFields
{
    /**
     * @param  array<int, string>|null  $only  Subset of SEOFields::FIELDS to show
     */
    public static function seoSection(?array $only = null): Section
    {
        return SEOFields::make($only);
    }

    /**
     * Optional structured-data (schema.org JSON-LD) editor section. Add it
     * alongside seoSection() when editors should attach FAQ / Product schema
     * or an automatic breadcrumb without writing code.
     */
    public static function seoSchemaSection(): Section
    {
        return SEOSchemaFields::make();
    }
}
