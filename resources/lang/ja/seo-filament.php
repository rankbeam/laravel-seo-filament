<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| rankbeam/laravel-seo-filament — 日本語
|--------------------------------------------------------------------------
|
| 初版: Claude (2026-09-07)、機械翻訳。ネイティブによるレビューは
| 未実施 — TRANSLATING.md を参照。キーはコードなので翻訳しません。
| プレースホルダー (:length, :max, …) はそのまま残します。
|
*/

return [
    'section' => [
        'title' => 'SEO',
        'description' => '検索結果やソーシャルでの共有時に、このページがどう表示されるかを設定します。',
    ],
    'fields' => [
        'title' => 'SEO タイトル',
        'description' => 'SEO ディスクリプション',
        'focus_keywords' => 'フォーカスキーワード',
        'focus_keywords_placeholder' => 'キーワードを追加',
        'focus_keywords_help' => 'このページで上位表示を狙う語句です。最初のキーワードが主キーワードとして扱われます。seo.keywords.enabled を有効にすると、監査と Pro スキャンがキーワード未設定のページを検出します。',
        'canonical' => 'canonical URL',
        'canonical_help' => '空のままにすると、自動の canonical URL（クエリパラメータを除いたページ URL）が使われます。',
        'robots' => 'robots ディレクティブ',
        'robots_placeholder' => '自動（サイトのデフォルト）',
        'og_image' => 'ソーシャル共有画像',
        'og_image_help' => 'og:image と twitter:image に使用されます。理想的なサイズ: :widthx:height px。',
        'counter' => ':length / :max 文字',
    ],
    'robots_options' => [
        'index_follow' => 'インデックスする・リンクをたどる',
        'index_nofollow' => 'インデックスする・リンクをたどらない',
        'noindex_follow' => 'インデックスしない・リンクをたどる',
        'noindex_nofollow' => 'インデックスしない・リンクをたどらない',
    ],
    'sources' => [
        'manual' => '手動',
        'content' => 'コンテンツからのフォールバック',
        'model_defaults' => 'モデル種別のデフォルト',
        'global_defaults' => 'グローバルデフォルト',
        'config' => 'サイト設定',
        'url' => 'URL から導出',
        'none' => '未設定',
    ],
    'indicators' => [
        'heading' => '有効な値とソース',
        'note' => '最後に保存した時点で、各値がどのレイヤーから供給されているかを表示します。フォームを保存すると更新されます。',
        'title' => 'タイトル',
        'description' => 'ディスクリプション',
        'og_image' => 'ソーシャル画像',
        'robots' => 'Robots',
        'canonical' => 'canonical URL',
    ],
    'preview' => [
        'tab_google' => 'Google',
        'tab_social' => 'ソーシャル',
        'serp' => '検索結果プレビュー',
        'social' => 'ソーシャル共有プレビュー',
        'no_image' => '画像なし',
        'no_description' => 'ディスクリプションがありません。',
        'note' => '現在のフォームの内容（未保存の変更を含む）を反映しています。',
        'title' => 'タイトル',
        'description' => 'ディスクリプション',
        'image' => '画像',
    ],
    'schema' => [
        'section_title' => '構造化データ',
        'section_description' => 'リッチリザルト用の schema.org JSON-LD です。下のフィールドから生成され、ページに出力されます。コードは不要です。',
        'auto_breadcrumb' => '自動パンくずリスト',
        'auto_breadcrumb_help' => 'このページの親チェーンから BreadcrumbList を生成します。設定は不要で、モデルの祖先をそのままたどります。',
        'blocks' => 'スキーマブロック',
        'add_block' => '構造化データを追加',
        'type' => '種類',
        'type_faq' => 'FAQ（Q&A）',
        'type_product' => '商品（Product）',
        'questions' => '質問',
        'add_question' => '質問を追加',
        'question' => '質問',
        'answer' => '回答',
        'product_name' => '商品名',
        'brand' => 'ブランド',
        'description' => '説明',
        'image_url' => '画像 URL',
        'sku' => 'SKU',
        'price' => '価格',
        'currency' => '通貨',
        'availability' => '在庫状況',
    ],
    'locales' => [
        'heading' => '言語',
        'badge_tooltip' => ':language: :total 項目中 :count 項目を設定済み',
    ],
];
