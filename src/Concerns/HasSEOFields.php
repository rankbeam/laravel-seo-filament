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
     * @param  \Closure|null  $target  Closure(?Model $formRecord): ?Model — edit the SEO
     *                                 of a RELATED model instead of the resource's own
     *                                 record. Null binds the resource's record (default).
     * @param  bool  $showPreview  Render the tabbed (Google SERP / social card) live
     *                             preview. Default on; pass false to omit it.
     */
    public static function seoSection(?array $only = null, ?\Closure $target = null, bool $showPreview = true): Section
    {
        return SEOFields::make($only, $target, $showPreview);
    }

    /**
     * Optional structured-data (schema.org JSON-LD) editor section. Add it
     * alongside seoSection() when editors should attach FAQ / Product schema
     * or an automatic breadcrumb without writing code.
     *
     * @param  \Closure|null  $target  Closure(?Model $formRecord): ?Model — write schema
     *                                 onto a RELATED model. Pass the SAME resolver you
     *                                 passed to seoSection() so both act on one target.
     */
    public static function seoSchemaSection(?\Closure $target = null): Section
    {
        return SEOSchemaFields::make($target);
    }
}
