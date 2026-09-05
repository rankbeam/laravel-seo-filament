<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| rankbeam/laravel-seo-filament — Italiano
|--------------------------------------------------------------------------
|
| Prima stesura: Claude (2026-09-05). Revisione madrelingua: Valentin Goxhaj.
| Le chiavi non si traducono; i segnaposto restano invariati.
|
*/

return [

    'section' => [
        'title' => 'SEO',
        'description' => 'Come appare questa pagina nei risultati di ricerca e quando viene condivisa sui social.',
    ],

    'fields' => [
        'title' => 'Titolo SEO',
        'description' => 'Descrizione SEO',
        'focus_keywords' => 'Parole chiave principali',
        'focus_keywords_placeholder' => 'Aggiungi una parola chiave',
        'focus_keywords_help' => 'I termini per cui questa pagina dovrebbe posizionarsi. La prima parola chiave è considerata principale. Attiva seo.keywords.enabled perché l\'audit e la scansione Pro segnalino le pagine senza parola chiave.',
        'canonical' => 'URL canonical',
        'canonical_help' => 'Lascia vuoto per l\'URL canonical automatico (l\'URL della pagina senza parametri di query).',
        'robots' => 'Direttiva robots',
        'robots_placeholder' => 'Automatica (impostazione del sito)',
        'og_image' => 'Immagine per la condivisione social',
        'og_image_help' => 'Usata per og:image e twitter:image. Dimensione ideale: :widthx:height px.',
        'counter' => ':length / :max caratteri',
    ],

    'robots_options' => [
        'index_follow' => 'Indicizza, segui i link',
        'index_nofollow' => 'Indicizza, non seguire i link',
        'noindex_follow' => 'Non indicizzare, segui i link',
        'noindex_nofollow' => 'Non indicizzare, non seguire i link',
    ],

    'sources' => [
        'manual' => 'Manuale',
        'content' => 'Ricavato dal contenuto',
        'model_defaults' => 'Predefinito del tipo di modello',
        'global_defaults' => 'Predefinito globale',
        'config' => 'Configurazione del sito',
        'url' => 'Ricavato dall\'URL',
        'none' => 'Non impostato',
    ],

    'indicators' => [
        'heading' => 'Valori effettivi e origine',
        'note' => 'Mostra quale livello fornisce ogni valore all\'ultimo salvataggio. Salva il form per aggiornare.',
        'title' => 'Titolo',
        'description' => 'Descrizione',
        'og_image' => 'Immagine social',
        'robots' => 'Robots',
        'canonical' => 'URL canonical',
    ],

    'preview' => [
        'tab_google' => 'Google',
        'tab_social' => 'Social',
        'serp' => 'Anteprima del risultato di ricerca',
        'social' => 'Anteprima della condivisione social',
        'no_image' => 'Nessuna immagine',
        'no_description' => 'Nessuna descrizione disponibile.',
        'note' => 'Riflette il form attuale (comprese le modifiche non salvate).',
        'title' => 'Titolo',
        'description' => 'Descrizione',
        'image' => 'Immagine',
    ],

    'schema' => [
        'section_title' => 'Dati strutturati',
        'section_description' => 'JSON-LD schema.org per i rich result. Costruito dai campi qui sotto e inserito nella pagina, senza scrivere codice.',
        'auto_breadcrumb' => 'Breadcrumb automatico',
        'auto_breadcrumb_help' => 'Genera una BreadcrumbList dalla catena dei genitori di questa pagina. Zero configurazione: segue gli antenati del modello.',
        'blocks' => 'Blocchi schema',
        'add_block' => 'Aggiungi dati strutturati',
        'type' => 'Tipo',
        'type_faq' => 'FAQ (domande e risposte)',
        'type_product' => 'Prodotto',
        'questions' => 'Domande',
        'add_question' => 'Aggiungi domanda',
        'question' => 'Domanda',
        'answer' => 'Risposta',
        'product_name' => 'Nome del prodotto',
        'brand' => 'Marca',
        'description' => 'Descrizione',
        'image_url' => 'URL dell\'immagine',
        'sku' => 'SKU',
        'price' => 'Prezzo',
        'currency' => 'Valuta',
        'availability' => 'Disponibilità',
    ],
];
