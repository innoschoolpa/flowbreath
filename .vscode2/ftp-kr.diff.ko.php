<?php
return [
    // 공통
    'common' => [
        'save' => '저장',
        'cancel' => '취소',
        'edit' => '수정',
        'delete' => '삭제',
        'back' => '뒤로',
    ],
    
    // 리소스 관련
    'resource' => [
        'edit_title' => '리소스 수정',
        'basic_info' => '기본 정보',
        'title' => '제목',
        'url' => 'URL',
        'source_type' => '자료 유형',
        'tags' => '태그',
        'tags_placeholder' => '태그를 쉼표로 구분하여 입력하세요',
        'tags_help' => '태그를 쉼표로 구분하여 입력하세요. 예: 교육, 기술, 혁신',
        
        'publication_info' => '출판 정보',
        'author_creator' => '저자/제작자',
        
        'summary_analysis' => '요약 및 분석',
        'summary' => '요약',
        'initial_impression' => '초기 인상',
        'personal_connection' => '개인적 연관성',
        
        'reliability' => '신뢰성 평가',
        'reliability_level' => '신뢰성',
        'reliability_rationale' => '신뢰성 근거',
        
        'source_types' => [
            'article' => '기사',
            'book' => '책',
            'video' => '비디오',
            'podcast' => '팟캐스트',
            'website' => '웹사이트',
            'other' => '기타',
        ],
        
        'reliability_levels' => [
            'high' => '높음',
            'medium' => '중간',
            'low' => '낮음',
        ],
    ],
    
    // 유효성 검사 메시지
    'validation' => [
        'required' => ':field을(를) 입력해주세요.',
        'select_required' => ':field을(를) 선택해주세요.',
    ],
]; 