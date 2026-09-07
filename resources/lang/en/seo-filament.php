<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| rankbeam/laravel-seo-filament — English strings
|--------------------------------------------------------------------------
|
| Every label, helper text, option and preview caption in the Filament SEO
| editor. Publish with:
|
|     php artisan vendor:publish --tag=seo-filament-lang
|
| The live warnings under the counters ("The title is 70 characters long…")
| come from the core package (`seo::seo.warnings.*`). See TRANSLATING.md in
| the rankbeam/laravel-seo repository for the glossary and the rules.
|
*/

return [

    'section' => [
        'title' => 'SEO',
        'description' => 'How this page appears in search results and when shared on social.',
    ],

    'fields' => [
        'title' => 'SEO title',
        'description' => 'SEO description',
        'focus_keywords' => 'Focus keywords',
        'focus_keywords_placeholder' => 'Add a keyword',
        'focus_keywords_help' => 'The terms this page should rank for. The first keyword is treated as primary. Enable seo.keywords.enabled to have the audit and the Pro scan flag pages with no keyword.',
        'canonical' => 'Canonical URL',
        'canonical_help' => 'Leave empty for the automatic canonical URL (the page URL without query parameters).',
        'robots' => 'Robots directive',
        'robots_placeholder' => 'Automatic (site default)',
        'og_image' => 'Social sharing image',
        'og_image_help' => 'Used for og:image and twitter:image. Ideal size: :widthx:height px.',
        'counter' => ':length / :max characters',
    ],

    'robots_options' => [
        'index_follow' => 'Index, follow links',
        'index_nofollow' => 'Index, don\'t follow links',
        'noindex_follow' => 'Don\'t index, follow links',
        'noindex_nofollow' => 'Don\'t index, don\'t follow links',
    ],

    // Where an effective value came from (the badges next to each field).
    'sources' => [
        'manual' => 'Manual',
        'content' => 'Content fallback',
        'model_defaults' => 'Model-type default',
        'global_defaults' => 'Global default',
        'config' => 'Site config',
        'url' => 'Derived from URL',
        'none' => 'Not set',
    ],

    'indicators' => [
        'heading' => 'Effective values & sources',
        'note' => 'Shows which layer provides each value as last saved. Save the form to refresh.',
        'title' => 'Title',
        'description' => 'Description',
        'og_image' => 'Social image',
        'robots' => 'Robots',
        'canonical' => 'Canonical URL',
    ],

    'preview' => [
        'tab_google' => 'Google',
        'tab_social' => 'Social',
        'serp' => 'Search result preview',
        'social' => 'Social share preview',
        'no_image' => 'No image',
        'no_description' => 'No description available.',
        'note' => 'Reflecting the current form (including unsaved changes).',
        'title' => 'Title',
        'description' => 'Description',
        'image' => 'Image',
    ],

    'schema' => [
        'section_title' => 'Structured data',
        'section_description' => 'schema.org JSON-LD for rich results. Built from the fields below and rendered into the page — no code required.',
        'auto_breadcrumb' => 'Automatic breadcrumb',
        'auto_breadcrumb_help' => 'Generate a BreadcrumbList from this page\'s parent chain. Zero configuration — it follows the model\'s ancestors.',
        'blocks' => 'Schema blocks',
        'add_block' => 'Add structured data',
        'type' => 'Type',
        'type_faq' => 'FAQ (Q&A)',
        'type_product' => 'Product',
        'questions' => 'Questions',
        'add_question' => 'Add question',
        'question' => 'Question',
        'answer' => 'Answer',
        'product_name' => 'Product name',
        'brand' => 'Brand',
        'description' => 'Description',
        'image_url' => 'Image URL',
        'sku' => 'SKU',
        'price' => 'Price',
        'currency' => 'Currency',
        'availability' => 'Availability',
    ],

    // The per-language tabs of the SEO section (several locales).
    'locales' => [
        'heading' => 'Languages',
        'badge_tooltip' => ':count of :total fields set for :language',
    ],
];
