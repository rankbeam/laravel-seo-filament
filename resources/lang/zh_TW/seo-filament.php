<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| rankbeam/laravel-seo-filament — 繁體中文
|--------------------------------------------------------------------------
|
| 首版：Claude（2026-09-07），機器翻譯。尚未經母語者審校 —
| 見 TRANSLATING.md。鍵是代碼，不翻譯；佔位符 (:length, :max, …) 保持原樣。
|
*/

return [
    'section' => [
        'title' => 'SEO',
        'description' => '此頁面在搜尋結果中，以及分享到社群時的呈現方式。',
    ],
    'fields' => [
        'title' => 'SEO 標題',
        'description' => 'SEO 描述',
        'focus_keywords' => '焦點關鍵字',
        'focus_keywords_placeholder' => '新增關鍵字',
        'focus_keywords_help' => '此頁面希望取得排名的字詞。第一個關鍵字會視為主要關鍵字。啟用 seo.keywords.enabled 後，稽核與 Pro 掃描會標記沒有關鍵字的頁面。',
        'canonical' => 'Canonical URL',
        'canonical_help' => '留空即使用自動產生的 canonical URL（不含查詢參數的頁面 URL）。',
        'robots' => 'Robots 指令',
        'robots_placeholder' => '自動（網站預設）',
        'og_image' => '社群分享圖片',
        'og_image_help' => '用於 og:image 與 twitter:image。理想尺寸：:widthx:height px。',
        'counter' => ':length / :max 個字元',
    ],
    'robots_options' => [
        'index_follow' => '索引，追蹤連結',
        'index_nofollow' => '索引，不追蹤連結',
        'noindex_follow' => '不索引，追蹤連結',
        'noindex_nofollow' => '不索引，不追蹤連結',
    ],
    'sources' => [
        'manual' => '手動設定',
        'content' => '內容遞補',
        'model_defaults' => '模型類型預設值',
        'global_defaults' => '全域預設值',
        'config' => '網站設定檔',
        'url' => '由 URL 推導',
        'none' => '未設定',
    ],
    'indicators' => [
        'heading' => '生效值與來源',
        'note' => '顯示上次儲存時各值由哪一層提供。儲存表單即可更新。',
        'title' => '標題',
        'description' => '描述',
        'og_image' => '社群圖片',
        'robots' => 'Robots',
        'canonical' => 'Canonical URL',
    ],
    'preview' => [
        'tab_google' => 'Google',
        'tab_social' => '社群',
        'serp' => '搜尋結果預覽',
        'social' => '社群分享預覽',
        'no_image' => '沒有圖片',
        'no_description' => '沒有可用的描述。',
        'note' => '反映目前表單的內容（包含尚未儲存的變更）。',
        'title' => '標題',
        'description' => '描述',
        'image' => '圖片',
    ],
    'schema' => [
        'section_title' => '結構化資料',
        'section_description' => '用於複合式搜尋結果的 schema.org JSON-LD。由下方欄位組成並直接輸出到頁面中，不需要寫任何程式碼。',
        'auto_breadcrumb' => '自動麵包屑',
        'auto_breadcrumb_help' => '依此頁面的上層鏈產生 BreadcrumbList。零設定，會自動沿著模型的祖先層級建立。',
        'blocks' => 'Schema 區塊',
        'add_block' => '新增結構化資料',
        'type' => '類型',
        'type_faq' => '常見問題（FAQ）',
        'type_product' => '產品',
        'questions' => '問題',
        'add_question' => '新增問題',
        'question' => '問題',
        'answer' => '答案',
        'product_name' => '產品名稱',
        'brand' => '品牌',
        'description' => '描述',
        'image_url' => '圖片 URL',
        'sku' => 'SKU',
        'price' => '價格',
        'currency' => '幣別',
        'availability' => '供貨狀態',
    ],
    'locales' => [
        'heading' => '語言',
        'badge_tooltip' => ':language 已設定 :count / :total 個欄位',
    ],
];
