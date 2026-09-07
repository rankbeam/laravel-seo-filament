<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostLocalesResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\PostLocalesResource;

/**
 * The explicit-locales section on a page that ALSO exposes an active schema
 * locale (a translatable plugin): the developer's list must win — tabs, not
 * follow mode.
 */
class EditPostLocalesOnTranslatablePage extends EditRecord
{
    protected static string $resource = PostLocalesResource::class;

    public ?string $activeLocale = 'fr';

    public function getActiveSchemaLocale(): ?string
    {
        return $this->activeLocale;
    }
}
