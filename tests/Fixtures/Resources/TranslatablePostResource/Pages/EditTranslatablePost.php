<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Resources\TranslatablePostResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Rankbeam\Seo\Filament\Tests\Fixtures\Resources\TranslatablePostResource;

/**
 * Emulates the contract of a translatable plugin's edit page: a public
 * `$activeLocale` reported through Filament's `getActiveSchemaLocale()`, and
 * a switch that re-fills the form (the plugins call `$this->form->fill()` on
 * `updatedActiveLocale`). No plugin dependency — the contract is Filament's.
 */
class EditTranslatablePost extends EditRecord
{
    protected static string $resource = TranslatablePostResource::class;

    public ?string $activeLocale = 'fr';

    public function getActiveSchemaLocale(): ?string
    {
        return $this->activeLocale;
    }

    public function switchLocale(string $locale): void
    {
        $this->activeLocale = $locale;

        $this->fillForm();
    }
}
