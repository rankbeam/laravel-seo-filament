<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| rankbeam/laravel-seo-filament — 한국어
|--------------------------------------------------------------------------
|
| 초판: Claude (2026-09-07), 기계 번역. 원어민 검수는 아직 —
| TRANSLATING.md 참고. 키는 코드이므로 번역하지 않습니다.
| 플레이스홀더 (:length, :max, …) 는 그대로 둡니다.
|
*/

return [
    'section' => [
        'title' => 'SEO',
        'description' => '검색 결과와 소셜 공유 시 이 페이지가 표시되는 방식입니다.',
    ],
    'fields' => [
        'title' => 'SEO 제목',
        'description' => 'SEO 설명',
        'focus_keywords' => '포커스 키워드',
        'focus_keywords_placeholder' => '키워드 추가',
        'focus_keywords_help' => '이 페이지가 상위 노출을 목표로 하는 검색어입니다. 첫 번째 키워드가 주 키워드로 취급됩니다. seo.keywords.enabled를 활성화하면 감사(audit)와 Pro 스캔이 키워드가 없는 페이지를 표시합니다.',
        'canonical' => 'Canonical URL',
        'canonical_help' => '비워 두면 자동 canonical URL(쿼리 매개변수를 제외한 페이지 URL)이 사용됩니다.',
        'robots' => 'Robots 지시문',
        'robots_placeholder' => '자동(사이트 기본값)',
        'og_image' => '소셜 공유 이미지',
        'og_image_help' => 'og:image와 twitter:image에 사용됩니다. 이상적인 크기: :widthx:height px.',
        'counter' => ':length / :max자',
    ],
    'robots_options' => [
        'index_follow' => '색인, 링크 팔로우',
        'index_nofollow' => '색인, 링크 팔로우 안 함',
        'noindex_follow' => '색인 안 함, 링크 팔로우',
        'noindex_nofollow' => '색인 안 함, 링크 팔로우 안 함',
    ],
    'sources' => [
        'manual' => '수동 입력',
        'content' => '콘텐츠 대체값',
        'model_defaults' => '모델 유형 기본값',
        'global_defaults' => '전역 기본값',
        'config' => '사이트 설정',
        'url' => 'URL에서 파생',
        'none' => '설정 안 됨',
    ],
    'indicators' => [
        'heading' => '적용 값 및 출처',
        'note' => '마지막 저장 시점을 기준으로 각 값을 제공하는 계층을 표시합니다. 폼을 저장하면 갱신됩니다.',
        'title' => '제목',
        'description' => '설명',
        'og_image' => '소셜 이미지',
        'robots' => 'Robots',
        'canonical' => 'Canonical URL',
    ],
    'preview' => [
        'tab_google' => 'Google',
        'tab_social' => '소셜',
        'serp' => '검색 결과 미리보기',
        'social' => '소셜 공유 미리보기',
        'no_image' => '이미지 없음',
        'no_description' => '설명이 없습니다.',
        'note' => '현재 폼 내용(저장하지 않은 변경 사항 포함)을 반영합니다.',
        'title' => '제목',
        'description' => '설명',
        'image' => '이미지',
    ],
    'schema' => [
        'section_title' => '구조화 데이터',
        'section_description' => '리치 결과를 위한 schema.org JSON-LD입니다. 아래 필드로 구성되어 페이지에 렌더링되며, 코드 작성은 필요 없습니다.',
        'auto_breadcrumb' => '자동 breadcrumb',
        'auto_breadcrumb_help' => '이 페이지의 상위 체인에서 BreadcrumbList를 생성합니다. 설정이 전혀 필요 없으며 모델의 상위 항목을 따라갑니다.',
        'blocks' => 'Schema 블록',
        'add_block' => '구조화 데이터 추가',
        'type' => '유형',
        'type_faq' => 'FAQ(질문과 답변)',
        'type_product' => '제품',
        'questions' => '질문 목록',
        'add_question' => '질문 추가',
        'question' => '질문',
        'answer' => '답변',
        'product_name' => '제품명',
        'brand' => '브랜드',
        'description' => '설명',
        'image_url' => '이미지 URL',
        'sku' => 'SKU',
        'price' => '가격',
        'currency' => '통화',
        'availability' => '재고 상태',
    ],
    'locales' => [
        'heading' => '언어',
        'badge_tooltip' => ':language — :total개 필드 중 :count개 설정됨',
    ],
];
