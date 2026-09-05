<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| rankbeam/laravel-seo-filament — Nederlands
|--------------------------------------------------------------------------
|
| Eerste versie: Claude (2026-09-05), automatisch. Controle door een
| moedertaalspreker staat nog open — zie TRANSLATING.md in de core-repository.
|
*/

return [

    'section' => [
        'title' => 'SEO',
        'description' => 'Hoe deze pagina verschijnt in zoekresultaten en bij het delen op sociale media.',
    ],

    'fields' => [
        'title' => 'SEO-titel',
        'description' => 'SEO-beschrijving',
        'focus_keywords' => 'Focus-keywords',
        'focus_keywords_placeholder' => 'Keyword toevoegen',
        'focus_keywords_help' => 'De termen waarop deze pagina moet ranken. Het eerste keyword geldt als het belangrijkste. Zet seo.keywords.enabled aan zodat de audit en de Pro-scan pagina\'s zonder keyword melden.',
        'canonical' => 'Canonical-URL',
        'canonical_help' => 'Laat leeg voor de automatische canonical-URL (de pagina-URL zonder queryparameters).',
        'robots' => 'Robots-instructie',
        'robots_placeholder' => 'Automatisch (standaard van de site)',
        'og_image' => 'Afbeelding voor delen op sociale media',
        'og_image_help' => 'Gebruikt voor og:image en twitter:image. Ideale grootte: :widthx:height px.',
        'counter' => ':length / :max tekens',
    ],

    'robots_options' => [
        'index_follow' => 'Indexeren, links volgen',
        'index_nofollow' => 'Indexeren, links niet volgen',
        'noindex_follow' => 'Niet indexeren, links volgen',
        'noindex_nofollow' => 'Niet indexeren, links niet volgen',
    ],

    'sources' => [
        'manual' => 'Handmatig',
        'content' => 'Uit de content',
        'model_defaults' => 'Standaard van het modeltype',
        'global_defaults' => 'Globale standaard',
        'config' => 'Siteconfiguratie',
        'url' => 'Afgeleid van de URL',
        'none' => 'Niet ingesteld',
    ],

    'indicators' => [
        'heading' => 'Effectieve waarden & bronnen',
        'note' => 'Toont welke laag elke waarde levert zoals laatst opgeslagen. Sla het formulier op om te verversen.',
        'title' => 'Titel',
        'description' => 'Beschrijving',
        'og_image' => 'Social-afbeelding',
        'robots' => 'Robots',
        'canonical' => 'Canonical-URL',
    ],

    'preview' => [
        'tab_google' => 'Google',
        'tab_social' => 'Social',
        'serp' => 'Voorbeeld van het zoekresultaat',
        'social' => 'Voorbeeld bij delen',
        'no_image' => 'Geen afbeelding',
        'no_description' => 'Geen beschrijving beschikbaar.',
        'note' => 'Weerspiegelt het huidige formulier (inclusief niet-opgeslagen wijzigingen).',
        'title' => 'Titel',
        'description' => 'Beschrijving',
        'image' => 'Afbeelding',
    ],

    'schema' => [
        'section_title' => 'Gestructureerde data',
        'section_description' => 'schema.org-JSON-LD voor rich results. Opgebouwd uit de velden hieronder en in de pagina gerenderd — zonder code.',
        'auto_breadcrumb' => 'Automatische breadcrumb',
        'auto_breadcrumb_help' => 'Genereert een BreadcrumbList uit de ouderketen van deze pagina. Geen configuratie — volgt de voorouders van het model.',
        'blocks' => 'Schema-blokken',
        'add_block' => 'Gestructureerde data toevoegen',
        'type' => 'Type',
        'type_faq' => 'FAQ (vraag & antwoord)',
        'type_product' => 'Product',
        'questions' => 'Vragen',
        'add_question' => 'Vraag toevoegen',
        'question' => 'Vraag',
        'answer' => 'Antwoord',
        'product_name' => 'Productnaam',
        'brand' => 'Merk',
        'description' => 'Beschrijving',
        'image_url' => 'Afbeeldings-URL',
        'sku' => 'SKU',
        'price' => 'Prijs',
        'currency' => 'Valuta',
        'availability' => 'Beschikbaarheid',
    ],
];
