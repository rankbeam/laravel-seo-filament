<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Resources\Pages\EditRecord\Concerns;

use Illuminate\Database\Eloquent\Model;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable as PluginTranslatable;
use Rankbeam\Seo\Filament\Concerns\InteractsWithSeoLocaleDrafts;

/** Optional Lara Zeus adapter: replace its Edit page trait import with this one. */
trait Translatable
{
    use InteractsWithSeoLocaleDrafts, PluginTranslatable {
        InteractsWithSeoLocaleDrafts::updatedActiveLocale insteadof PluginTranslatable;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $this->fillSeoTranslatableRecord($record, $data, 'mutateFormDataBeforeSave');
        $record->save();

        return $record;
    }
}
