<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| rankbeam/laravel-seo-filament — Français
|--------------------------------------------------------------------------
|
| Première version : Claude (2026-09-05), automatique. Relecture par un
| locuteur natif à faire — voir TRANSLATING.md dans le dépôt core.
|
*/

return [

    'section' => [
        'title' => 'SEO',
        'description' => 'Comment cette page apparaît dans les résultats de recherche et lorsqu\'elle est partagée sur les réseaux sociaux.',
    ],

    'fields' => [
        'title' => 'Titre SEO',
        'description' => 'Description SEO',
        'focus_keywords' => 'Mots-clés principaux',
        'focus_keywords_placeholder' => 'Ajouter un mot-clé',
        'focus_keywords_help' => 'Les termes sur lesquels cette page doit se positionner. Le premier mot-clé est considéré comme principal. Activez seo.keywords.enabled pour que l\'audit et le scan Pro signalent les pages sans mot-clé.',
        'canonical' => 'URL canonical',
        'canonical_help' => 'Laissez vide pour l\'URL canonical automatique (l\'URL de la page sans paramètres de requête).',
        'robots' => 'Directive robots',
        'robots_placeholder' => 'Automatique (valeur par défaut du site)',
        'og_image' => 'Image de partage sur les réseaux sociaux',
        'og_image_help' => 'Utilisée pour og:image et twitter:image. Taille idéale : :widthx:height px.',
        'counter' => ':length / :max caractères',
    ],

    'robots_options' => [
        'index_follow' => 'Indexer, suivre les liens',
        'index_nofollow' => 'Indexer, ne pas suivre les liens',
        'noindex_follow' => 'Ne pas indexer, suivre les liens',
        'noindex_nofollow' => 'Ne pas indexer, ne pas suivre les liens',
    ],

    'sources' => [
        'manual' => 'Manuel',
        'content' => 'Issu du contenu',
        'model_defaults' => 'Valeur par défaut du type de modèle',
        'global_defaults' => 'Valeur par défaut globale',
        'config' => 'Configuration du site',
        'url' => 'Dérivé de l\'URL',
        'none' => 'Non défini',
    ],

    'indicators' => [
        'heading' => 'Valeurs effectives et sources',
        'note' => 'Indique quel niveau fournit chaque valeur au dernier enregistrement. Enregistrez le formulaire pour actualiser.',
        'title' => 'Titre',
        'description' => 'Description',
        'og_image' => 'Image sociale',
        'robots' => 'Robots',
        'canonical' => 'URL canonical',
    ],

    'preview' => [
        'tab_google' => 'Google',
        'tab_social' => 'Réseaux sociaux',
        'serp' => 'Aperçu du résultat de recherche',
        'social' => 'Aperçu du partage social',
        'no_image' => 'Aucune image',
        'no_description' => 'Aucune description disponible.',
        'note' => 'Reflète le formulaire actuel (modifications non enregistrées comprises).',
        'title' => 'Titre',
        'description' => 'Description',
        'image' => 'Image',
    ],

    'schema' => [
        'section_title' => 'Données structurées',
        'section_description' => 'JSON-LD schema.org pour les résultats enrichis. Construit à partir des champs ci-dessous et rendu dans la page — sans code.',
        'auto_breadcrumb' => 'Fil d\'Ariane automatique',
        'auto_breadcrumb_help' => 'Génère une BreadcrumbList à partir de la chaîne des parents de cette page. Zéro configuration : elle suit les ancêtres du modèle.',
        'blocks' => 'Blocs de schéma',
        'add_block' => 'Ajouter des données structurées',
        'type' => 'Type',
        'type_faq' => 'FAQ (questions-réponses)',
        'type_product' => 'Produit',
        'questions' => 'Questions',
        'add_question' => 'Ajouter une question',
        'question' => 'Question',
        'answer' => 'Réponse',
        'product_name' => 'Nom du produit',
        'brand' => 'Marque',
        'description' => 'Description',
        'image_url' => 'URL de l\'image',
        'sku' => 'SKU',
        'price' => 'Prix',
        'currency' => 'Devise',
        'availability' => 'Disponibilité',
    ],
];
