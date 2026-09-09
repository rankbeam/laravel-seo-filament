<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Concerns;

use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Rankbeam\Seo\Filament\Support\SeoLocales;

/** Shared implementation for the optional Lara Zeus Edit/Create adapters. */
trait InteractsWithSeoLocaleDrafts
{
    /** Raw form drafts, never an Eloquent attribute or another database store. */
    #[Locked]
    public array $seoLocaleDrafts = [];

    /** Visited parent fields in their raw editor representation. */
    #[Locked]
    public array $seoTranslatedDrafts = [];

    protected bool $switchingSeoLocale = false;

    protected bool $seoLocaleDraftsFlushed = false;

    public function hasDatabaseTransactions(): bool
    {
        return true;
    }

    public function isSwitchingSeoLocale(): bool
    {
        return $this->switchingSeoLocale;
    }

    /** Replace the upstream switch's getState() calls, which write relations. */
    public function updatedActiveLocale(): void
    {
        if (! in_array($this->activeLocale, $this->getTranslatableLocales(), true)) {
            $this->activeLocale = $this->oldActiveLocale;
            throw ValidationException::withMessages(['activeLocale' => __('validation.in', ['attribute' => 'locale'])]);
        }
        if (blank($this->oldActiveLocale)) {
            return;
        }

        $this->switchingSeoLocale = true;
        try {
            $this->resetValidation();
            $raw = $this->seoRawFormState();
            $attributes = static::getResource()::getTranslatableAttributes();
            $this->captureSeoDraft($this->oldActiveLocale);
            $this->otherLocaleData[$this->oldActiveLocale] = Arr::only($raw, $attributes);
            // A model update can build the cached form before activeLocale changes.
            // Rebuild its locale-bound fields and preview for the destination.
            $this->cacheSchema('form', null);
            $incoming = [
                ...Arr::except($raw, $attributes),
                ...(isset($this->seoTranslatedDrafts[$this->activeLocale]) ? [] : ($this->otherLocaleData[$this->activeLocale] ?? [])),
            ];
            $pendingFiles = [];
            foreach (Arr::dot($incoming) as $path => $value) {
                if ($value instanceof TemporaryUploadedFile) {
                    $pendingFiles[$path] = $value;
                    Arr::forget($incoming, $path);
                }
            }
            $this->form->fill($incoming);
            $editorPaths = array_keys($this->seoEditorGroups($this->form));
            foreach ($pendingFiles as $path => $file) {
                $absolute = $this->form->getStatePath().'.'.$path;
                if (! array_filter($editorPaths, fn ($editor) => str_starts_with($absolute, $editor.'.'))) {
                    data_set($this, $absolute, $file);
                }
            }
            // Assign raw state AFTER hydration: upload drafts can still hold
            // TemporaryUploadedFile objects and repeaters retain their UUIDs.
            foreach ($this->seoTranslatedDrafts[$this->activeLocale] ?? [] as $key => $value) {
                data_set($this, $this->form->getStatePath().'.'.$key, $value);
            }
            $this->restoreSeoDraft($this->form, $this->activeLocale);
            unset($this->otherLocaleData[$this->activeLocale]);
            if (filament('spatie-translatable')->getPersistLocale()) {
                session()->put('spatie_translatable_active_locale', $this->activeLocale);
            }
        } finally {
            $this->switchingSeoLocale = false;
        }
    }

    /** Fill translated attributes without upstream getState()/fill() side effects. */
    private function fillSeoTranslatableRecord(Model $record, array $data, string $mutator): void
    {
        $attributes = static::getResource()::getTranslatableAttributes();
        $record->setLocale($this->activeLocale);
        $record->fill(Arr::except($data, $attributes));
        foreach (Arr::only($data, $attributes) as $key => $value) {
            $record->setTranslation($key, $this->activeLocale, $value);
        }
        foreach ($this->otherLocaleData as $locale => $localeData) {
            $localeData = $this->{$mutator}($localeData);
            foreach (Arr::only($localeData, $attributes) as $key => $value) {
                $record->setTranslation($key, $locale, $value);
            }
        }
    }

    protected function callHook(string $hook): void
    {
        if ($hook === 'beforeValidate') {
            parent::callHook($hook);
            $this->seoLocaleDraftsFlushed = false;
            $this->captureSeoDraft($this->activeLocale);
            $this->replaySeoDrafts('validate');

            return;
        }
        if (in_array($hook, ['beforeSave', 'beforeCreate'], true)) {
            $this->replaySeoDrafts('prepare');
        }
        if (in_array($hook, ['afterSave', 'afterCreate'], true)) {
            $this->replaySeoDrafts('persist');
            $this->seoLocaleDraftsFlushed = true;
        }
        parent::callHook($hook);
        if ($hook === 'afterValidate') {
            // Active uploads have now passed beforeStateDehydrated and may
            // be stored paths; retain that state for a failed-save retry.
            $this->captureSeoDraft($this->activeLocale);
        }
    }

    protected function commitDatabaseTransaction(): void
    {
        parent::commitDatabaseTransaction();
        if ($this->seoLocaleDraftsFlushed) {
            $this->seoLocaleDrafts = [];
            $this->seoTranslatedDrafts = [];
            // Create-and-create-another must not reuse the previous record's translations.
            if ($this instanceof CreateRecord) {
                $this->otherLocaleData = [];
            }
            $this->seoLocaleDraftsFlushed = false;
        }
    }

    private function seoRawFormState(): array
    {
        $raw = $this->form->getRawState();

        return $raw instanceof Arrayable ? $raw->toArray() : $raw;
    }

    /** @return array<string, Group> */
    private function seoEditorGroups(Schema $form): array
    {
        $groups = [];
        foreach ($form->getFlatComponents(withHidden: true) as $component) {
            if ($component instanceof Group && $component->getMeta('rankbeam_locale_editor')) {
                $groups[$component->getStatePath()] = $component;
            }
        }

        return $groups;
    }

    private function seoDraftLocale(Group $group, string $locale): string
    {
        if ($group->getMeta('rankbeam_locale_editor') === 'metadata') {
            [, $followsPage] = SeoLocales::resolve($group->getMeta('rankbeam_explicit_locales'), $group);
            if (! $followsPage) {
                return '*'; // Explicit locale tabs are one shared editor.
            }
        }

        return $locale;
    }

    private function captureSeoDraft(string $locale, ?Schema $form = null): void
    {
        $form ??= $this->form;
        $state = $form->getRawState();
        $this->seoTranslatedDrafts[$locale] = Arr::only(
            $state instanceof Arrayable ? $state->toArray() : $state,
            static::getResource()::getTranslatableAttributes(),
        );
        foreach ($this->seoEditorGroups($form) as $path => $group) {
            $this->seoLocaleDrafts[$this->seoDraftLocale($group, $locale)][$path] = $group->getRawState();
        }
    }

    private function restoreSeoDraft(Schema $form, string $locale): void
    {
        foreach ($this->seoEditorGroups($form) as $path => $group) {
            $drafts = $this->seoLocaleDrafts[$this->seoDraftLocale($group, $locale)] ?? [];
            if (array_key_exists($path, $drafts)) {
                $group->state($drafts[$path]);
            }
        }
    }

    /** Validate all drafts, prepare parent fields on Save, then persist SEO. */
    private function replaySeoDrafts(string $stage): void
    {
        $active = $this->activeLocale;
        $raw = $this->seoRawFormState();
        $path = $this->form->getStatePath();
        $record = $this->record;
        $recordLocale = $record && method_exists($record, 'getLocale') ? $record->getLocale() : null;
        $failedLocale = null;
        $locales = array_unique([...array_keys($this->seoTranslatedDrafts), ...array_keys($this->seoLocaleDrafts)]);
        try {
            foreach ($locales as $locale) {
                if ($locale === '*' || ($stage !== 'validate' && $locale === $active)) {
                    continue; // The normal form handles current/shared fields.
                }
                if (! in_array($locale, $this->getTranslatableLocales(), true)) {
                    continue;
                }
                $this->activeLocale = $locale;
                // Components capture their locale when built: replay must use
                // the destination locale's validators, not the cached form.
                $form = $this->form($this->defaultForm(Schema::make($this)));
                if ($stage === 'persist' && $this->getRecord()) {
                    $form->model($this->getRecord());
                }
                data_set($this, $path, [
                    ...Arr::except($raw, static::getResource()::getTranslatableAttributes()),
                    ...($this->seoTranslatedDrafts[$locale] ?? []),
                ]);
                $this->restoreSeoDraft($form, $locale);
                try {
                    if ($stage === 'validate') {
                        // No upload dehydration or relationship hooks on validation.
                        $form->validate();
                    } elseif ($stage === 'prepare') {
                        // Run field transformations/uploads only on explicit Save.
                        // getState(false) never invokes relationship persistence.
                        $state = $form->getRawState();
                        $form->callBeforeStateDehydrated($state);
                        $this->otherLocaleData[$locale] = Arr::only(
                            $form->getState(shouldCallHooksBefore: false),
                            static::getResource()::getTranslatableAttributes(),
                        );
                    } else {
                        foreach ($this->seoEditorGroups($form) as $groupPath => $group) {
                            if ($this->seoDraftLocale($group, $locale) !== '*'
                                && array_key_exists($groupPath, $this->seoLocaleDrafts[$locale] ?? [])) {
                                $group->saveRelationships();
                            }
                        }
                    }
                } catch (ValidationException $exception) {
                    $failedLocale = $locale;
                    throw $exception;
                } finally {
                    if ($stage !== 'validate') {
                        // A file may have moved before a later SQL write failed.
                        // Preserve its stored path even when the transaction rolls back.
                        $this->captureSeoDraft($locale, $form);
                    }
                }
            }
        } finally {
            $this->activeLocale = $active;
            if ($recordLocale !== null) {
                $record->setLocale($recordLocale);
            }
            data_set($this, $path, $raw);
            if ($failedLocale !== null && $failedLocale !== $active) {
                $this->updatingActiveLocale();
                $this->activeLocale = $failedLocale;
                $this->updatedActiveLocale();
            }
        }
    }
}
