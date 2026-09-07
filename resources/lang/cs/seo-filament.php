<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| rankbeam/laravel-seo-filament — Čeština
|--------------------------------------------------------------------------
|
| První verze: Claude (2026-09-07), strojový překlad. Kontrola rodilým
| mluvčím zatím neproběhla — viz TRANSLATING.md. Klíče jsou kódy a nepřekládají
| se; zástupné symboly (:length, :max, …) zůstávají beze změny.
|
*/

return [
    'section' => [
        'title' => 'SEO',
        'description' => 'Jak se tato stránka zobrazuje ve výsledcích vyhledávání a při sdílení na sociálních sítích.',
    ],
    'fields' => [
        'title' => 'SEO titulek',
        'description' => 'SEO popis',
        'focus_keywords' => 'Cílová klíčová slova',
        'focus_keywords_placeholder' => 'Přidat klíčové slovo',
        'focus_keywords_help' => 'Výrazy, na které se má tato stránka umisťovat. První klíčové slovo se považuje za hlavní. Zapněte seo.keywords.enabled, aby audit a sken Pro označovaly stránky bez klíčového slova.',
        'canonical' => 'Canonical URL',
        'canonical_help' => 'Ponechte prázdné pro automatickou canonical URL (URL stránky bez parametrů dotazu).',
        'robots' => 'Direktiva robots',
        'robots_placeholder' => 'Automaticky (výchozí nastavení webu)',
        'og_image' => 'Obrázek pro sdílení na sociálních sítích',
        'og_image_help' => 'Používá se pro og:image a twitter:image. Ideální velikost: :widthx:height px.',
        'counter' => ':length / :max znaků',
    ],
    'robots_options' => [
        'index_follow' => 'Indexovat, sledovat odkazy',
        'index_nofollow' => 'Indexovat, nesledovat odkazy',
        'noindex_follow' => 'Neindexovat, sledovat odkazy',
        'noindex_nofollow' => 'Neindexovat, nesledovat odkazy',
    ],
    'sources' => [
        'manual' => 'Ručně',
        'content' => 'Náhrada z obsahu',
        'model_defaults' => 'Výchozí pro typ modelu',
        'global_defaults' => 'Globální výchozí',
        'config' => 'Konfigurace webu',
        'url' => 'Odvozeno z URL',
        'none' => 'Nenastaveno',
    ],
    'indicators' => [
        'heading' => 'Výsledné hodnoty a jejich zdroje',
        'note' => 'Ukazuje, která vrstva poskytuje jednotlivé hodnoty podle posledního uložení. Pro aktualizaci formulář uložte.',
        'title' => 'Titulek',
        'description' => 'Popis',
        'og_image' => 'Obrázek pro sociální sítě',
        'robots' => 'Robots',
        'canonical' => 'Canonical URL',
    ],
    'preview' => [
        'tab_google' => 'Google',
        'tab_social' => 'Sociální sítě',
        'serp' => 'Náhled výsledku vyhledávání',
        'social' => 'Náhled sdílení na sociálních sítích',
        'no_image' => 'Bez obrázku',
        'no_description' => 'Popis není k dispozici.',
        'note' => 'Odpovídá aktuálnímu stavu formuláře (včetně neuložených změn).',
        'title' => 'Titulek',
        'description' => 'Popis',
        'image' => 'Obrázek',
    ],
    'schema' => [
        'section_title' => 'Strukturovaná data',
        'section_description' => 'JSON-LD podle schema.org pro rozšířené výsledky. Sestaví se z polí níže a vykreslí se do stránky — bez psaní kódu.',
        'auto_breadcrumb' => 'Automatická drobečková navigace',
        'auto_breadcrumb_help' => 'Vygeneruje BreadcrumbList z řetězce nadřazených položek této stránky. Bez konfigurace — řídí se předky modelu.',
        'blocks' => 'Bloky strukturovaných dat',
        'add_block' => 'Přidat strukturovaná data',
        'type' => 'Typ',
        'type_faq' => 'FAQ (otázky a odpovědi)',
        'type_product' => 'Produkt',
        'questions' => 'Otázky',
        'add_question' => 'Přidat otázku',
        'question' => 'Otázka',
        'answer' => 'Odpověď',
        'product_name' => 'Název produktu',
        'brand' => 'Značka',
        'description' => 'Popis',
        'image_url' => 'URL obrázku',
        'sku' => 'SKU',
        'price' => 'Cena',
        'currency' => 'Měna',
        'availability' => 'Dostupnost',
    ],
    'locales' => [
        'heading' => 'Jazyky',
        'badge_tooltip' => ':count z :total polí vyplněno pro :language',
    ],
];
