<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| rankbeam/laravel-seo-filament — Русский
|--------------------------------------------------------------------------
|
| Первая версия: Claude (2026-09-05), автоматическая. Проверка носителем языка
| ещё не выполнена — см. TRANSLATING.md в репозитории core.
|
*/

return [

    'section' => [
        'title' => 'SEO',
        'description' => 'Как эта страница выглядит в результатах поиска и при публикации в соцсетях.',
    ],

    'fields' => [
        'title' => 'SEO-заголовок',
        'description' => 'SEO-описание',
        'focus_keywords' => 'Ключевые слова',
        'focus_keywords_placeholder' => 'Добавить ключевое слово',
        'focus_keywords_help' => 'Запросы, по которым должна ранжироваться эта страница. Первое ключевое слово считается основным. Включите seo.keywords.enabled, чтобы аудит и Pro-сканирование отмечали страницы без ключевого слова.',
        'canonical' => 'Canonical-URL',
        'canonical_help' => 'Оставьте пустым для автоматического canonical-URL (URL страницы без параметров запроса).',
        'robots' => 'Директива robots',
        'robots_placeholder' => 'Автоматически (значение сайта по умолчанию)',
        'og_image' => 'Изображение для соцсетей',
        'og_image_help' => 'Используется для og:image и twitter:image. Идеальный размер: :widthx:height px.',
        'counter' => ':length / :max символов',
    ],

    'robots_options' => [
        'index_follow' => 'Индексировать, переходить по ссылкам',
        'index_nofollow' => 'Индексировать, не переходить по ссылкам',
        'noindex_follow' => 'Не индексировать, переходить по ссылкам',
        'noindex_nofollow' => 'Не индексировать, не переходить по ссылкам',
    ],

    'sources' => [
        'manual' => 'Вручную',
        'content' => 'Из контента',
        'model_defaults' => 'Значение по умолчанию для типа модели',
        'global_defaults' => 'Глобальное значение по умолчанию',
        'config' => 'Конфигурация сайта',
        'url' => 'Получено из URL',
        'none' => 'Не задано',
    ],

    'indicators' => [
        'heading' => 'Итоговые значения и источники',
        'note' => 'Показывает, какой уровень даёт каждое значение на момент последнего сохранения. Сохраните форму, чтобы обновить.',
        'title' => 'Заголовок',
        'description' => 'Описание',
        'og_image' => 'Изображение для соцсетей',
        'robots' => 'Robots',
        'canonical' => 'Canonical-URL',
    ],

    'preview' => [
        'tab_google' => 'Google',
        'tab_social' => 'Соцсети',
        'serp' => 'Предпросмотр результата поиска',
        'social' => 'Предпросмотр публикации в соцсетях',
        'no_image' => 'Нет изображения',
        'no_description' => 'Описание отсутствует.',
        'note' => 'Отражает текущую форму (включая несохранённые изменения).',
        'title' => 'Заголовок',
        'description' => 'Описание',
        'image' => 'Изображение',
    ],

    'schema' => [
        'section_title' => 'Структурированные данные',
        'section_description' => 'JSON-LD schema.org для расширенных результатов. Собирается из полей ниже и выводится на страницу — без кода.',
        'auto_breadcrumb' => 'Автоматические хлебные крошки',
        'auto_breadcrumb_help' => 'Создаёт BreadcrumbList из цепочки родителей этой страницы. Без настройки — следует за предками модели.',
        'blocks' => 'Блоки схемы',
        'add_block' => 'Добавить структурированные данные',
        'type' => 'Тип',
        'type_faq' => 'FAQ (вопросы и ответы)',
        'type_product' => 'Товар',
        'questions' => 'Вопросы',
        'add_question' => 'Добавить вопрос',
        'question' => 'Вопрос',
        'answer' => 'Ответ',
        'product_name' => 'Название товара',
        'brand' => 'Бренд',
        'description' => 'Описание',
        'image_url' => 'URL изображения',
        'sku' => 'SKU',
        'price' => 'Цена',
        'currency' => 'Валюта',
        'availability' => 'Наличие',
    ],

    // The per-language tabs of the SEO section (several locales).
    'locales' => [
        'heading' => 'Языки',
        'badge_tooltip' => 'Заполнено :count из :total полей для :language',
    ],
];
