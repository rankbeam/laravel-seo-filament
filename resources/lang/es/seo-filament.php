<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| rankbeam/laravel-seo-filament — Español
|--------------------------------------------------------------------------
|
| Primera versión: Claude (2026-09-05), automática. Pendiente de revisión por
| un hablante nativo — ver TRANSLATING.md en el repositorio core.
|
*/

return [

    'section' => [
        'title' => 'SEO',
        'description' => 'Cómo aparece esta página en los resultados de búsqueda y al compartirla en redes sociales.',
    ],

    'fields' => [
        'title' => 'Título SEO',
        'description' => 'Descripción SEO',
        'focus_keywords' => 'Palabras clave principales',
        'focus_keywords_placeholder' => 'Añadir una palabra clave',
        'focus_keywords_help' => 'Los términos por los que esta página debería posicionarse. La primera palabra clave se considera la principal. Activa seo.keywords.enabled para que la auditoría y el escaneo Pro señalen las páginas sin palabra clave.',
        'canonical' => 'URL canonical',
        'canonical_help' => 'Déjalo vacío para la URL canonical automática (la URL de la página sin parámetros de consulta).',
        'robots' => 'Directiva robots',
        'robots_placeholder' => 'Automática (valor por defecto del sitio)',
        'og_image' => 'Imagen para compartir en redes sociales',
        'og_image_help' => 'Se usa para og:image y twitter:image. Tamaño ideal: :widthx:height px.',
        'counter' => ':length / :max caracteres',
    ],

    'robots_options' => [
        'index_follow' => 'Indexar, seguir enlaces',
        'index_nofollow' => 'Indexar, no seguir enlaces',
        'noindex_follow' => 'No indexar, seguir enlaces',
        'noindex_nofollow' => 'No indexar, no seguir enlaces',
    ],

    'sources' => [
        'manual' => 'Manual',
        'content' => 'Derivado del contenido',
        'model_defaults' => 'Valor por defecto del tipo de modelo',
        'global_defaults' => 'Valor por defecto global',
        'config' => 'Configuración del sitio',
        'url' => 'Derivado de la URL',
        'none' => 'Sin definir',
    ],

    'indicators' => [
        'heading' => 'Valores efectivos y origen',
        'note' => 'Muestra qué capa aporta cada valor según el último guardado. Guarda el formulario para actualizar.',
        'title' => 'Título',
        'description' => 'Descripción',
        'og_image' => 'Imagen social',
        'robots' => 'Robots',
        'canonical' => 'URL canonical',
    ],

    'preview' => [
        'tab_google' => 'Google',
        'tab_social' => 'Redes sociales',
        'serp' => 'Vista previa del resultado de búsqueda',
        'social' => 'Vista previa al compartir',
        'no_image' => 'Sin imagen',
        'no_description' => 'No hay descripción disponible.',
        'note' => 'Refleja el formulario actual (incluidos los cambios sin guardar).',
        'title' => 'Título',
        'description' => 'Descripción',
        'image' => 'Imagen',
    ],

    'schema' => [
        'section_title' => 'Datos estructurados',
        'section_description' => 'JSON-LD de schema.org para resultados enriquecidos. Se construye a partir de los campos de abajo y se renderiza en la página, sin escribir código.',
        'auto_breadcrumb' => 'Migas de pan automáticas',
        'auto_breadcrumb_help' => 'Genera una BreadcrumbList a partir de la cadena de padres de esta página. Sin configuración: sigue los ancestros del modelo.',
        'blocks' => 'Bloques de schema',
        'add_block' => 'Añadir datos estructurados',
        'type' => 'Tipo',
        'type_faq' => 'FAQ (preguntas y respuestas)',
        'type_product' => 'Producto',
        'questions' => 'Preguntas',
        'add_question' => 'Añadir pregunta',
        'question' => 'Pregunta',
        'answer' => 'Respuesta',
        'product_name' => 'Nombre del producto',
        'brand' => 'Marca',
        'description' => 'Descripción',
        'image_url' => 'URL de la imagen',
        'sku' => 'SKU',
        'price' => 'Precio',
        'currency' => 'Moneda',
        'availability' => 'Disponibilidad',
    ],
];
