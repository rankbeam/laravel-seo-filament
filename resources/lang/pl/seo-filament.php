<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| rankbeam/laravel-seo-filament — Polski
|--------------------------------------------------------------------------
|
| Pierwsza wersja: Claude (2026-09-05), automatyczna. Weryfikacja przez
| native speakera w toku — zob. TRANSLATING.md w repozytorium core.
|
*/

return [

    'section' => [
        'title' => 'SEO',
        'description' => 'Jak ta strona wygląda w wynikach wyszukiwania i po udostępnieniu w mediach społecznościowych.',
    ],

    'fields' => [
        'title' => 'Tytuł SEO',
        'description' => 'Opis SEO',
        'focus_keywords' => 'Słowa kluczowe',
        'focus_keywords_placeholder' => 'Dodaj słowo kluczowe',
        'focus_keywords_help' => 'Frazy, na które ta strona ma się pozycjonować. Pierwsze słowo kluczowe jest traktowane jako główne. Włącz seo.keywords.enabled, aby audyt i skan Pro oznaczały strony bez słowa kluczowego.',
        'canonical' => 'Adres canonical',
        'canonical_help' => 'Zostaw puste, aby użyć automatycznego adresu canonical (URL strony bez parametrów zapytania).',
        'robots' => 'Dyrektywa robots',
        'robots_placeholder' => 'Automatycznie (domyślne ustawienie witryny)',
        'og_image' => 'Obraz do udostępniania w mediach społecznościowych',
        'og_image_help' => 'Używany jako og:image i twitter:image. Idealny rozmiar: :widthx:height px.',
        'counter' => ':length / :max znaków',
    ],

    'robots_options' => [
        'index_follow' => 'Indeksuj, podążaj za linkami',
        'index_nofollow' => 'Indeksuj, nie podążaj za linkami',
        'noindex_follow' => 'Nie indeksuj, podążaj za linkami',
        'noindex_nofollow' => 'Nie indeksuj, nie podążaj za linkami',
    ],

    'sources' => [
        'manual' => 'Ręcznie',
        'content' => 'Z treści',
        'model_defaults' => 'Domyślne dla typu modelu',
        'global_defaults' => 'Domyślne globalne',
        'config' => 'Konfiguracja witryny',
        'url' => 'Wyprowadzone z URL',
        'none' => 'Nie ustawiono',
    ],

    'indicators' => [
        'heading' => 'Wartości efektywne i źródła',
        'note' => 'Pokazuje, która warstwa dostarcza każdą wartość według ostatniego zapisu. Zapisz formularz, aby odświeżyć.',
        'title' => 'Tytuł',
        'description' => 'Opis',
        'og_image' => 'Obraz społecznościowy',
        'robots' => 'Robots',
        'canonical' => 'Adres canonical',
    ],

    'preview' => [
        'tab_google' => 'Google',
        'tab_social' => 'Social',
        'serp' => 'Podgląd wyniku wyszukiwania',
        'social' => 'Podgląd udostępnienia',
        'no_image' => 'Brak obrazu',
        'no_description' => 'Brak dostępnego opisu.',
        'note' => 'Odzwierciedla bieżący formularz (łącznie z niezapisanymi zmianami).',
        'title' => 'Tytuł',
        'description' => 'Opis',
        'image' => 'Obraz',
    ],

    'schema' => [
        'section_title' => 'Dane strukturalne',
        'section_description' => 'JSON-LD schema.org dla wyników rozszerzonych. Budowany z pól poniżej i renderowany na stronie — bez kodu.',
        'auto_breadcrumb' => 'Automatyczna ścieżka nawigacyjna',
        'auto_breadcrumb_help' => 'Generuje BreadcrumbList z łańcucha rodziców tej strony. Zero konfiguracji — podąża za przodkami modelu.',
        'blocks' => 'Bloki schematu',
        'add_block' => 'Dodaj dane strukturalne',
        'type' => 'Typ',
        'type_faq' => 'FAQ (pytania i odpowiedzi)',
        'type_product' => 'Produkt',
        'questions' => 'Pytania',
        'add_question' => 'Dodaj pytanie',
        'question' => 'Pytanie',
        'answer' => 'Odpowiedź',
        'product_name' => 'Nazwa produktu',
        'brand' => 'Marka',
        'description' => 'Opis',
        'image_url' => 'URL obrazu',
        'sku' => 'SKU',
        'price' => 'Cena',
        'currency' => 'Waluta',
        'availability' => 'Dostępność',
    ],

    // The per-language tabs of the SEO section (several locales).
    'locales' => [
        'heading' => 'Języki',
        'badge_tooltip' => 'Wypełniono :count z :total pól dla :language',
    ],
];
