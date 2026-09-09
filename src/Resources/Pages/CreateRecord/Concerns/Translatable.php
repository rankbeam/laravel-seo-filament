<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Resources\Pages\CreateRecord\Concerns;

use Illuminate\Database\Eloquent\Model;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable as PluginTranslatable;
use Rankbeam\Seo\Filament\Concerns\InteractsWithSeoLocaleDrafts;

/** Optional Lara Zeus adapter: replace its Create page trait import with this one. */
trait Translatable
{
    use InteractsWithSeoLocaleDrafts, PluginTranslatable {
        InteractsWithSeoLocaleDrafts::updatedActiveLocale insteadof PluginTranslatable;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Passing translated strings to the constructor would create a phantom
        // translation in the operator's application locale before setting content locale.
        $record = new ($this->getModel());
        $this->fillSeoTranslatableRecord($record, $data, 'mutateFormDataBeforeCreate');
        if (method_exists($this, 'getParentRecord') && ($parent = $this->getParentRecord())) {
            return $this->associateRecordWithParent($record, $parent);
        }
        $record->save();

        return $record;
    }
}
