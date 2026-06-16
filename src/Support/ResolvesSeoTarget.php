<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Support;

use Filament\Schemas\Components\Component;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Shared resolution of the model whose SEO a Filament section edits.
 *
 * By default an SEO section edits the form's OWN record (the resource model).
 * A `target` resolver — `Closure(?Model $formRecord): ?Model` — redirects every
 * read and write to a RELATED model instead (e.g. an entity whose canonical SEO
 * lives on a related PublicPage). The closure is evaluated through Filament's
 * closure evaluator, so it can also depend on `$record`, `$operation`, etc.
 *
 * The same resolver drives hydration, save, the source indicators, the preview,
 * AND the structured-data editor, so all of them act on one consistent target.
 *
 * Contract:
 *  - No resolver (null) => today's behaviour: bind the form's own record.
 *  - A resolver returning null (create form, not-yet-existing relation) is
 *    tolerated — nothing is read or written and no placeholder is created.
 *  - A resolver returning a non-null model that does NOT expose the core
 *    HasSEO `seoMeta()` relation is a developer error and throws.
 */
trait ResolvesSeoTarget
{
    /**
     * Resolve the target model a section should act on, given the form record.
     *
     * @param  \Closure|null  $target  the per-section target resolver, or null
     * @param  Model|null  $formRecord  the resource's own record (null on create)
     */
    protected static function resolveSeoTarget(?\Closure $target, ?Model $formRecord, Component $component): ?Model
    {
        if ($target === null) {
            return $formRecord;
        }

        // Evaluate the resolver through Filament so it can declare the usual
        // injectables. The form's own record is supplied explicitly so the
        // documented signatures all resolve to it: `$record` (by name), a typed
        // `?Model $r` (by the base Model class), and a typed `?Post $r` (by the
        // record's concrete class). Supplying it explicitly is also what stops
        // a `View`/`Group` `model()` closure from recursing into that
        // component's own getRecord() while resolving.
        $typedInjections = [Model::class => $formRecord];

        if ($formRecord instanceof Model) {
            $typedInjections[$formRecord::class] = $formRecord;
        }

        $resolved = $component->evaluate(
            $target,
            ['record' => $formRecord],
            $typedInjections,
        );

        if ($resolved === null) {
            return null;
        }

        if (! $resolved instanceof Model) {
            throw new RuntimeException(
                'A Rankbeam SEO `target` resolver must return an Eloquent model or null, got '
                .get_debug_type($resolved).'.'
            );
        }

        static::assertSeoTarget($resolved);

        return $resolved;
    }

    /**
     * The form record evaluated against the target resolver, derived from the
     * component's CONTAINER record so it can be used as a `View`/`Group`
     * `model()` closure without recursing through that component's own
     * `getRecord()`.
     */
    protected static function resolveSeoTargetFromContainer(?\Closure $target, Component $component): ?Model
    {
        $formRecord = $component->getContainer()->getRecord();

        return static::resolveSeoTarget(
            $target,
            $formRecord instanceof Model ? $formRecord : null,
            $component,
        );
    }

    /**
     * A non-null target must speak the core HasSEO contract. Never auto-create
     * a placeholder related model — that is the developer's responsibility.
     */
    protected static function assertSeoTarget(Model $target): void
    {
        if (! method_exists($target, 'seoMeta')) {
            throw new RuntimeException(
                'The Rankbeam SEO `target` ['.$target::class.'] does not expose a seoMeta() '
                .'relation. The target model must use the core Rankbeam\Seo\Traits\HasSEO trait.'
            );
        }
    }
}
