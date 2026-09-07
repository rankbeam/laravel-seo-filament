<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| rankbeam/laravel-seo-filament — Deutsch
|--------------------------------------------------------------------------
|
| Erste Fassung: Claude (2026-09-05), maschinell. Muttersprachliche Prüfung
| steht noch aus — siehe TRANSLATING.md im Core-Repository.
|
*/

return [

    'section' => [
        'title' => 'SEO',
        'description' => 'So erscheint diese Seite in Suchergebnissen und beim Teilen in sozialen Netzwerken.',
    ],

    'fields' => [
        'title' => 'SEO-Title',
        'description' => 'SEO-Description',
        'focus_keywords' => 'Fokus-Keywords',
        'focus_keywords_placeholder' => 'Keyword hinzufügen',
        'focus_keywords_help' => 'Die Begriffe, für die diese Seite ranken soll. Das erste Keyword gilt als Haupt-Keyword. Aktiviere seo.keywords.enabled, damit Audit und Pro-Scan Seiten ohne Keyword melden.',
        'canonical' => 'Canonical-URL',
        'canonical_help' => 'Leer lassen für die automatische Canonical-URL (die Seiten-URL ohne Query-Parameter).',
        'robots' => 'Robots-Anweisung',
        'robots_placeholder' => 'Automatisch (Site-Standard)',
        'og_image' => 'Bild fürs Teilen in sozialen Netzwerken',
        'og_image_help' => 'Wird für og:image und twitter:image verwendet. Ideale Größe: :widthx:height px.',
        'counter' => ':length / :max Zeichen',
    ],

    'robots_options' => [
        'index_follow' => 'Indexieren, Links folgen',
        'index_nofollow' => 'Indexieren, Links nicht folgen',
        'noindex_follow' => 'Nicht indexieren, Links folgen',
        'noindex_nofollow' => 'Nicht indexieren, Links nicht folgen',
    ],

    'sources' => [
        'manual' => 'Manuell',
        'content' => 'Aus dem Inhalt',
        'model_defaults' => 'Standard des Modelltyps',
        'global_defaults' => 'Globaler Standard',
        'config' => 'Site-Konfiguration',
        'url' => 'Aus der URL abgeleitet',
        'none' => 'Nicht gesetzt',
    ],

    'indicators' => [
        'heading' => 'Effektive Werte & Quellen',
        'note' => 'Zeigt, welche Ebene jeden Wert zum Zeitpunkt des letzten Speicherns liefert. Formular speichern, um zu aktualisieren.',
        'title' => 'Title',
        'description' => 'Description',
        'og_image' => 'Social-Bild',
        'robots' => 'Robots',
        'canonical' => 'Canonical-URL',
    ],

    'preview' => [
        'tab_google' => 'Google',
        'tab_social' => 'Social',
        'serp' => 'Vorschau des Suchergebnisses',
        'social' => 'Vorschau beim Teilen',
        'no_image' => 'Kein Bild',
        'no_description' => 'Keine Beschreibung verfügbar.',
        'note' => 'Entspricht dem aktuellen Formular (inklusive ungespeicherter Änderungen).',
        'title' => 'Title',
        'description' => 'Description',
        'image' => 'Bild',
    ],

    'schema' => [
        'section_title' => 'Strukturierte Daten',
        'section_description' => 'schema.org-JSON-LD für Rich Results. Aus den Feldern unten aufgebaut und in die Seite gerendert — ganz ohne Code.',
        'auto_breadcrumb' => 'Automatische Breadcrumb',
        'auto_breadcrumb_help' => 'Erzeugt eine BreadcrumbList aus der Elternkette dieser Seite. Keine Konfiguration — folgt den Vorfahren des Modells.',
        'blocks' => 'Schema-Blöcke',
        'add_block' => 'Strukturierte Daten hinzufügen',
        'type' => 'Typ',
        'type_faq' => 'FAQ (Fragen & Antworten)',
        'type_product' => 'Produkt',
        'questions' => 'Fragen',
        'add_question' => 'Frage hinzufügen',
        'question' => 'Frage',
        'answer' => 'Antwort',
        'product_name' => 'Produktname',
        'brand' => 'Marke',
        'description' => 'Beschreibung',
        'image_url' => 'Bild-URL',
        'sku' => 'SKU',
        'price' => 'Preis',
        'currency' => 'Währung',
        'availability' => 'Verfügbarkeit',
    ],

    // The per-language tabs of the SEO section (several locales).
    'locales' => [
        'heading' => 'Sprachen',
        'badge_tooltip' => ':count von :total Feldern für :language ausgefüllt',
    ],
];
