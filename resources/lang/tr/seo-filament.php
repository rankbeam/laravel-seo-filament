<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| rankbeam/laravel-seo-filament — Türkçe
|--------------------------------------------------------------------------
|
| İlk sürüm: Claude (2026-09-05), otomatik. Anadil konuşuru incelemesi
| bekleniyor — bkz. core deposundaki TRANSLATING.md.
|
*/

return [

    'section' => [
        'title' => 'SEO',
        'description' => 'Bu sayfanın arama sonuçlarında ve sosyal medyada paylaşıldığında nasıl göründüğü.',
    ],

    'fields' => [
        'title' => 'SEO başlığı',
        'description' => 'SEO açıklaması',
        'focus_keywords' => 'Odak anahtar kelimeler',
        'focus_keywords_placeholder' => 'Anahtar kelime ekle',
        'focus_keywords_help' => 'Bu sayfanın sıralanması gereken terimler. İlk anahtar kelime birincil kabul edilir. Denetimin ve Pro taramasının anahtar kelimesi olmayan sayfaları işaretlemesi için seo.keywords.enabled ayarını açın.',
        'canonical' => 'Canonical URL',
        'canonical_help' => 'Otomatik canonical URL (sorgu parametreleri olmadan sayfa URL\'si) için boş bırakın.',
        'robots' => 'Robots yönergesi',
        'robots_placeholder' => 'Otomatik (site varsayılanı)',
        'og_image' => 'Sosyal paylaşım görseli',
        'og_image_help' => 'og:image ve twitter:image için kullanılır. İdeal boyut: :widthx:height px.',
        'counter' => ':length / :max karakter',
    ],

    'robots_options' => [
        'index_follow' => 'Dizine ekle, bağlantıları izle',
        'index_nofollow' => 'Dizine ekle, bağlantıları izleme',
        'noindex_follow' => 'Dizine ekleme, bağlantıları izle',
        'noindex_nofollow' => 'Dizine ekleme, bağlantıları izleme',
    ],

    'sources' => [
        'manual' => 'Elle girilmiş',
        'content' => 'İçerikten türetilmiş',
        'model_defaults' => 'Model türü varsayılanı',
        'global_defaults' => 'Genel varsayılan',
        'config' => 'Site yapılandırması',
        'url' => 'URL\'den türetilmiş',
        'none' => 'Ayarlanmamış',
    ],

    'indicators' => [
        'heading' => 'Geçerli değerler ve kaynaklar',
        'note' => 'Son kayıtta her değeri hangi katmanın sağladığını gösterir. Yenilemek için formu kaydedin.',
        'title' => 'Başlık',
        'description' => 'Açıklama',
        'og_image' => 'Sosyal görsel',
        'robots' => 'Robots',
        'canonical' => 'Canonical URL',
    ],

    'preview' => [
        'tab_google' => 'Google',
        'tab_social' => 'Sosyal',
        'serp' => 'Arama sonucu önizlemesi',
        'social' => 'Sosyal paylaşım önizlemesi',
        'no_image' => 'Görsel yok',
        'no_description' => 'Açıklama yok.',
        'note' => 'Mevcut formu yansıtır (kaydedilmemiş değişiklikler dâhil).',
        'title' => 'Başlık',
        'description' => 'Açıklama',
        'image' => 'Görsel',
    ],

    'schema' => [
        'section_title' => 'Yapılandırılmış veri',
        'section_description' => 'Zengin sonuçlar için schema.org JSON-LD. Aşağıdaki alanlardan oluşturulur ve sayfaya işlenir — kod gerekmez.',
        'auto_breadcrumb' => 'Otomatik breadcrumb',
        'auto_breadcrumb_help' => 'Bu sayfanın üst öğe zincirinden bir BreadcrumbList oluşturur. Sıfır yapılandırma — modelin atalarını izler.',
        'blocks' => 'Şema blokları',
        'add_block' => 'Yapılandırılmış veri ekle',
        'type' => 'Tür',
        'type_faq' => 'SSS (soru-cevap)',
        'type_product' => 'Ürün',
        'questions' => 'Sorular',
        'add_question' => 'Soru ekle',
        'question' => 'Soru',
        'answer' => 'Cevap',
        'product_name' => 'Ürün adı',
        'brand' => 'Marka',
        'description' => 'Açıklama',
        'image_url' => 'Görsel URL\'si',
        'sku' => 'SKU',
        'price' => 'Fiyat',
        'currency' => 'Para birimi',
        'availability' => 'Stok durumu',
    ],
];
