<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| rankbeam/laravel-seo-filament — Português (Brasil)
|--------------------------------------------------------------------------
|
| Primeira versão: Claude (2026-09-05), automática. Revisão por falante nativo
| pendente — veja TRANSLATING.md no repositório core.
|
*/

return [

    'section' => [
        'title' => 'SEO',
        'description' => 'Como esta página aparece nos resultados de busca e ao ser compartilhada nas redes sociais.',
    ],

    'fields' => [
        'title' => 'Título SEO',
        'description' => 'Descrição SEO',
        'focus_keywords' => 'Palavras-chave principais',
        'focus_keywords_placeholder' => 'Adicionar palavra-chave',
        'focus_keywords_help' => 'Os termos para os quais esta página deve ranquear. A primeira palavra-chave é tratada como principal. Ative seo.keywords.enabled para que a auditoria e o scan Pro sinalizem páginas sem palavra-chave.',
        'canonical' => 'URL canonical',
        'canonical_help' => 'Deixe em branco para a URL canonical automática (a URL da página sem parâmetros de consulta).',
        'robots' => 'Diretiva robots',
        'robots_placeholder' => 'Automática (padrão do site)',
        'og_image' => 'Imagem para compartilhamento social',
        'og_image_help' => 'Usada para og:image e twitter:image. Tamanho ideal: :widthx:height px.',
        'counter' => ':length / :max caracteres',
    ],

    'robots_options' => [
        'index_follow' => 'Indexar, seguir links',
        'index_nofollow' => 'Indexar, não seguir links',
        'noindex_follow' => 'Não indexar, seguir links',
        'noindex_nofollow' => 'Não indexar, não seguir links',
    ],

    'sources' => [
        'manual' => 'Manual',
        'content' => 'Derivado do conteúdo',
        'model_defaults' => 'Padrão do tipo de modelo',
        'global_defaults' => 'Padrão global',
        'config' => 'Configuração do site',
        'url' => 'Derivado da URL',
        'none' => 'Não definido',
    ],

    'indicators' => [
        'heading' => 'Valores efetivos e origens',
        'note' => 'Mostra qual camada fornece cada valor conforme o último salvamento. Salve o formulário para atualizar.',
        'title' => 'Título',
        'description' => 'Descrição',
        'og_image' => 'Imagem social',
        'robots' => 'Robots',
        'canonical' => 'URL canonical',
    ],

    'preview' => [
        'tab_google' => 'Google',
        'tab_social' => 'Redes sociais',
        'serp' => 'Prévia do resultado de busca',
        'social' => 'Prévia do compartilhamento social',
        'no_image' => 'Sem imagem',
        'no_description' => 'Nenhuma descrição disponível.',
        'note' => 'Reflete o formulário atual (incluindo alterações não salvas).',
        'title' => 'Título',
        'description' => 'Descrição',
        'image' => 'Imagem',
    ],

    'schema' => [
        'section_title' => 'Dados estruturados',
        'section_description' => 'JSON-LD do schema.org para rich results. Montado a partir dos campos abaixo e renderizado na página — sem código.',
        'auto_breadcrumb' => 'Breadcrumb automático',
        'auto_breadcrumb_help' => 'Gera uma BreadcrumbList a partir da cadeia de pais desta página. Zero configuração: segue os ancestrais do modelo.',
        'blocks' => 'Blocos de schema',
        'add_block' => 'Adicionar dados estruturados',
        'type' => 'Tipo',
        'type_faq' => 'FAQ (perguntas e respostas)',
        'type_product' => 'Produto',
        'questions' => 'Perguntas',
        'add_question' => 'Adicionar pergunta',
        'question' => 'Pergunta',
        'answer' => 'Resposta',
        'product_name' => 'Nome do produto',
        'brand' => 'Marca',
        'description' => 'Descrição',
        'image_url' => 'URL da imagem',
        'sku' => 'SKU',
        'price' => 'Preço',
        'currency' => 'Moeda',
        'availability' => 'Disponibilidade',
    ],

    // The per-language tabs of the SEO section (several locales).
    'locales' => [
        'heading' => 'Idiomas',
        'badge_tooltip' => ':count de :total campos preenchidos para :language',
    ],
];
