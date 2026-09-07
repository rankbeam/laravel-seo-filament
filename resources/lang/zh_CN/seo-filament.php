<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| rankbeam/laravel-seo-filament — 简体中文
|--------------------------------------------------------------------------
|
| 首版：Claude（2026-09-07），机器翻译。尚未经母语者审校 —
| 见 TRANSLATING.md。键是代码，不翻译；占位符 (:length, :max, …) 保持原样。
|
*/

return [
    'section' => [
        'title' => 'SEO',
        'description' => '此页面在搜索结果中以及在社交平台分享时的呈现方式。',
    ],
    'fields' => [
        'title' => 'SEO 标题',
        'description' => 'SEO 描述',
        'focus_keywords' => '焦点关键词',
        'focus_keywords_placeholder' => '添加关键词',
        'focus_keywords_help' => '此页面希望获得排名的搜索词。第一个关键词会被视为主关键词。启用 seo.keywords.enabled 后，审计和 Pro 扫描会标记出没有关键词的页面。',
        'canonical' => 'Canonical URL',
        'canonical_help' => '留空则使用自动生成的 canonical URL（去掉查询参数后的页面 URL）。',
        'robots' => 'Robots 指令',
        'robots_placeholder' => '自动（站点默认）',
        'og_image' => '社交分享图片',
        'og_image_help' => '用于 og:image 和 twitter:image。理想尺寸：:widthx:height px。',
        'counter' => ':length / :max 个字符',
    ],
    'robots_options' => [
        'index_follow' => '索引，跟踪链接',
        'index_nofollow' => '索引，不跟踪链接',
        'noindex_follow' => '不索引，跟踪链接',
        'noindex_nofollow' => '不索引，不跟踪链接',
    ],
    'sources' => [
        'manual' => '手动设置',
        'content' => '内容回退',
        'model_defaults' => '模型类型默认值',
        'global_defaults' => '全局默认值',
        'config' => '站点配置',
        'url' => '由 URL 推导',
        'none' => '未设置',
    ],
    'indicators' => [
        'heading' => '生效值与来源',
        'note' => '显示上次保存时每个值由哪一层提供。保存表单后刷新。',
        'title' => '标题',
        'description' => '描述',
        'og_image' => '社交图片',
        'robots' => 'Robots',
        'canonical' => 'Canonical URL',
    ],
    'preview' => [
        'tab_google' => 'Google',
        'tab_social' => '社交',
        'serp' => '搜索结果预览',
        'social' => '社交分享预览',
        'no_image' => '无图片',
        'no_description' => '暂无描述。',
        'note' => '反映当前表单的内容（包括未保存的更改）。',
        'title' => '标题',
        'description' => '描述',
        'image' => '图片',
    ],
    'schema' => [
        'section_title' => '结构化数据',
        'section_description' => '用于富媒体搜索结果的 schema.org JSON-LD。根据下方字段生成并渲染到页面中，无需编写代码。',
        'auto_breadcrumb' => '自动面包屑',
        'auto_breadcrumb_help' => '根据此页面的父级链生成 BreadcrumbList。零配置，自动跟随模型的祖先层级。',
        'blocks' => 'Schema 块',
        'add_block' => '添加结构化数据',
        'type' => '类型',
        'type_faq' => 'FAQ（问答）',
        'type_product' => '产品',
        'questions' => '问题',
        'add_question' => '添加问题',
        'question' => '问题',
        'answer' => '答案',
        'product_name' => '产品名称',
        'brand' => '品牌',
        'description' => '描述',
        'image_url' => '图片 URL',
        'sku' => 'SKU',
        'price' => '价格',
        'currency' => '货币',
        'availability' => '库存状态',
    ],
    'locales' => [
        'heading' => '语言',
        'badge_tooltip' => ':language 已设置 :count / :total 个字段',
    ],
];
