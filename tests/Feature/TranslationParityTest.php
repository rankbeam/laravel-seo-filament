<?php

declare(strict_types=1);

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;

/*
 * Translation contract for the Filament package: every language file mirrors
 * `en` key-for-key (no missing keys, no orphans, no empty values, no lost
 * placeholders), every key referenced from src/ and the views exists in `en`,
 * and switching the app locale switches the labels.
 */

$langDir = dirname(__DIR__, 2).'/resources/lang';

function seoFilamentFlatten(array $lines, string $prefix = ''): array
{
    $flat = [];
    foreach ($lines as $key => $value) {
        $full = $prefix === '' ? (string) $key : $prefix.'.'.$key;
        if (is_array($value)) {
            $flat += seoFilamentFlatten($value, $full);
        } else {
            $flat[$full] = $value;
        }
    }

    return $flat;
}

it('keeps every language file in parity with en', function () use ($langDir) {
    $en = seoFilamentFlatten(require $langDir.'/en/seo-filament.php');
    expect($en)->not->toBeEmpty();

    foreach (glob($langDir.'/*/seo-filament.php') ?: [] as $file) {
        $locale = basename(dirname($file));
        $lines = seoFilamentFlatten(require $file);

        expect(array_diff_key($en, $lines))->toBe([], "[{$locale}] missing keys");
        expect(array_diff_key($lines, $en))->toBe([], "[{$locale}] keys not in en");

        foreach ($lines as $key => $value) {
            expect(is_string($value) && trim($value) !== '')->toBeTrue("[{$locale}] {$key} is empty");
            preg_match_all('/:[a-z_]+/', (string) $en[$key], $expected);
            foreach ($expected[0] as $placeholder) {
                expect(str_contains((string) $value, $placeholder))->toBeTrue("[{$locale}] {$key} lost placeholder {$placeholder}");
            }
        }
    }
});

it('references only keys that exist in en', function () use ($langDir) {
    $en = seoFilamentFlatten(require $langDir.'/en/seo-filament.php');
    $referenced = [];

    foreach (['src', 'resources/views'] as $dir) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__, 2).'/'.$dir));
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            preg_match_all('/seo-filament::seo-filament\\.([a-z0-9_.]+)/', (string) file_get_contents($file->getPathname()), $m);
            foreach ($m[1] as $key) {
                $referenced[$key] = true;
            }
        }
    }

    expect($referenced)->not->toBeEmpty();

    foreach (array_keys($referenced) as $key) {
        // A key ending in "." is a dynamic prefix ('sources.'.$source): assert the group exists.
        if (str_ends_with($key, '.')) {
            $group = array_filter(array_keys($en), fn (string $k): bool => str_starts_with($k, $key));
            expect($group)->not->toBeEmpty("seo-filament::seo-filament.{$key}* has no keys in en");

            continue;
        }

        expect(array_key_exists($key, $en))->toBeTrue("seo-filament::seo-filament.{$key} is referenced but not defined in en");
    }
});

it('renders labels in the app locale', function () {
    Lang::addLines(['seo-filament.fields.title' => 'Titolo SEO'], 'it', 'seo-filament');

    App::setLocale('it');
    $translated = __('seo-filament::seo-filament.fields.title');
    App::setLocale('en');

    expect($translated)->toBe('Titolo SEO')
        ->and(__('seo-filament::seo-filament.fields.title'))->toBe('SEO title');
});
