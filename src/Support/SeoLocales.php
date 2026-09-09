<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Support;

use Filament\Schemas\Components\Component;
use Rankbeam\Seo\I18n\Hreflang;

/**
 * Which locales the SEO editor edits, and what to call them.
 *
 * The core stores one `seo_meta` row per (model, locale). Historically the
 * Filament section edited exactly one of those rows — the app locale's — so a
 * site publishing in three languages could only reach two of its rows by
 * switching the whole panel's language. This class decides the editor's
 * locale set, in this order:
 *
 *  1. an explicit list passed to `SEOFields::make(locales: [...])`;
 *  2. the page's **active schema locale** when the Livewire page exposes one
 *     (Filament's `getActiveSchemaLocale()`, implemented by the spatie
 *     translatable plugins' `Translatable` page concern) — the editor then
 *     follows the page's own locale switcher, one row at a time;
 *  3. `config('seo-filament.locales')`;
 *  4. the app locale alone — the pre-1.9 behaviour, byte-identical.
 *
 * A single locale renders the classic single editor (state `seo_meta.title`);
 * two or more render one tab per locale (state `seo_meta.{locale}.title`).
 */
final class SeoLocales
{
    /** A locale-switcher's state read must never persist the outgoing editor. */
    public static function isSwitching(Component $component): bool
    {
        $livewire = $component->getLivewire();
        if (method_exists($livewire, 'isSwitchingSeoLocale')) {
            return $livewire->isSwitchingSeoLocale();
        }
        if (method_exists($livewire, 'getOldActiveLocale')) {
            $old = $livewire->getOldActiveLocale();
        } elseif (in_array('LaraZeus\\SpatieTranslatable\\Resources\\Concerns\\HasActiveLocaleSwitcher', class_uses_recursive($livewire), true)) {
            // Plugin v1 (Filament 4) keeps this state protected and has no getter.
            $old = (new \ReflectionProperty($livewire, 'oldActiveLocale'))->getValue($livewire);
        } else {
            return false;
        }
        $current = self::pageLocale($component);

        return is_string($old) && $old !== '' && $current !== null && $old !== $current;
    }

    /** The locale attached to the actual editor field, including single-locale editors. */
    public static function forField(Component $component): ?string
    {
        $locale = $component->getMeta('seo_locale');

        return is_string($locale) && $locale !== '' ? $locale : self::pageLocale($component);
    }

    /**
     * The ordered, de-duplicated locale list the editor should offer.
     *
     * @param  array<int, string>|null  $explicit  The developer's list, or null
     * @return array{0: array<int, string>, 1: bool} [locales, followsPage]
     */
    public static function resolve(?array $explicit, Component $component): array
    {
        $explicit = self::clean($explicit);

        if ($explicit !== []) {
            return [$explicit, false];
        }

        $page = self::pageLocale($component);

        if ($page !== null) {
            return [[$page], true];
        }

        $configured = self::clean(config('seo-filament.locales'));

        if ($configured !== []) {
            return [$configured, false];
        }

        return [[app()->getLocale()], false];
    }

    /**
     * The locale the Livewire page is currently editing, when it has one.
     * Filament 4/5 expose `getActiveSchemaLocale()` on every schema-bearing
     * component (null by default); translatable plugins override it with the
     * locale their header switcher selected. Duck-typed on purpose: no
     * dependency on any one plugin.
     */
    public static function pageLocale(Component $component): ?string
    {
        try {
            $livewire = $component->getLivewire();
        } catch (\Throwable) {
            return null;
        }

        foreach (['getActiveSchemaLocale', 'getActiveFormsLocale'] as $method) {
            if (! method_exists($livewire, $method)) {
                continue;
            }

            $locale = $livewire->{$method}();

            if (is_string($locale) && trim($locale) !== '') {
                return trim($locale);
            }

            // The v4 method exists and returned null: the page has no active
            // locale. Do not fall through to the deprecated alias, which
            // would only return the same null.
            return null;
        }

        return null;
    }

    /**
     * The human label for a locale tab, in the panel's language — "Italiano"
     * for `it` when the panel runs in Italian, "Italian" when it runs in
     * English — through ext-intl when it is loaded. Without intl (or for a
     * code intl does not know) the BCP 47 code itself is the label, so a tab
     * never renders blank.
     */
    public static function label(string $locale, ?string $displayLocale = null): string
    {
        $code = Hreflang::fromLocale($locale) ?? $locale;

        if (class_exists(\Locale::class)) {
            $name = \Locale::getDisplayName($locale, $displayLocale ?? app()->getLocale());

            if (is_string($name) && $name !== '' && $name !== $locale) {
                return $name;
            }
        }

        return $code;
    }

    /**
     * Trim, drop blanks and duplicates, keep order.
     *
     * @return array<int, string>
     */
    private static function clean(mixed $locales): array
    {
        if (! is_array($locales)) {
            return [];
        }

        $clean = [];

        foreach ($locales as $locale) {
            if (! is_string($locale)) {
                continue;
            }

            $locale = trim($locale);

            if ($locale === '' || in_array($locale, $clean, true)) {
                continue;
            }

            $clean[] = $locale;
        }

        return $clean;
    }
}
