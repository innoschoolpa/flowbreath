<?php

// 프로젝트 루트 경로 정의
define('PROJECT_ROOT', dirname(__DIR__));

// 언어 디렉토리 생성
$langDir = PROJECT_ROOT . '/src/Lang';

if (!file_exists($langDir)) {
    if (mkdir($langDir, 0755, true)) {
        echo "Created directory: $langDir\n";
    } else {
        echo "Failed to create directory: $langDir\n";
    }
} else {
    echo "Directory already exists: $langDir\n";
}

// 언어 파일 생성
$langFiles = [
    'ko.json' => [
        'common' => [
            'site_name' => 'FlowBreath.io',
            'search' => '검색',
            'view_all' => '전체 보기',
            'read_more' => '자세히 보기',
            'login' => '로그인',
            'logout' => '로그아웃',
            'register' => '회원가입',
            'profile' => '내 정보',
            'anonymous' => '익명'
        ],
        'home' => [
            'hero' => [
                'title' => '호흡을 위한 최고의 자료, FlowBreath.io',
                'subtitle' => '호흡 건강, 운동, 명상, 치료 등 다양한 호흡 자료를 쉽고 빠르게 찾아보세요.',
                'search_placeholder' => '자료, 태그, 키워드로 검색...'
            ],
            'recent_resources' => [
                'title' => '최근 등록된 호흡 자료',
                'no_results' => '검색 결과가 없습니다.'
            ],
            'popular_tags' => [
                'title' => '인기 태그'
            ]
        ],
        'resources' => [
            'title' => '자료',
            'tags' => '태그',
            'author' => '작성자',
            'date' => '등록일',
            'content' => '내용',
            'language' => '언어',
            'status' => [
                'draft' => '임시저장',
                'published' => '발행됨',
                'private' => '비공개'
            ],
            'form' => [
                'title' => '제목',
                'content' => '내용',
                'description' => '설명',
                'language' => '언어',
                'visibility' => '공개 여부',
                'status' => '상태',
                'submit' => '저장',
                'cancel' => '취소'
            ]
        ],
        'footer' => [
            'copyright' => '© {year} FlowBreath.io. All rights reserved.',
            'description' => '호흡 건강을 위한 최고의 자료 플랫폼'
        ]
    ],
    'en.json' => [
        'common' => [
            'site_name' => 'FlowBreath.io',
            'search' => 'Search',
            'view_all' => 'View All',
            'read_more' => 'Read More',
            'login' => 'Login',
            'logout' => 'Logout',
            'register' => 'Register',
            'profile' => 'My Profile',
            'anonymous' => 'Anonymous'
        ],
        'home' => [
            'hero' => [
                'title' => 'Best Resources for Breathing, FlowBreath.io',
                'subtitle' => 'Find breathing resources for health, exercise, meditation, and therapy easily and quickly.',
                'search_placeholder' => 'Search by resource, tag, or keyword...'
            ],
            'recent_resources' => [
                'title' => 'Recently Added Breathing Resources',
                'no_results' => 'No search results found.'
            ],
            'popular_tags' => [
                'title' => 'Popular Tags'
            ]
        ],
        'resources' => [
            'title' => 'Resources',
            'tags' => 'Tags',
            'author' => 'Author',
            'date' => 'Date',
            'content' => 'Content',
            'language' => 'Language',
            'status' => [
                'draft' => 'Draft',
                'published' => 'Published',
                'private' => 'Private'
            ],
            'form' => [
                'title' => 'Title',
                'content' => 'Content',
                'description' => 'Description',
                'language' => 'Language',
                'visibility' => 'Visibility',
                'status' => 'Status',
                'submit' => 'Save',
                'cancel' => 'Cancel'
            ]
        ],
        'footer' => [
            'copyright' => '© {year} FlowBreath.io. All rights reserved.',
            'description' => 'The Best Resource Platform for Breathing Health'
        ]
    ]
];

foreach ($langFiles as $filename => $content) {
    $filepath = $langDir . '/' . $filename;
    if (file_put_contents($filepath, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        echo "Created language file: $filepath\n";
        chmod($filepath, 0644);
    } else {
        echo "Failed to create language file: $filepath\n";
    }
}

echo "Language directory setup completed.\n"; 