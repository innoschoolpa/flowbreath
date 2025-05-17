<?php
return [
    // 공통
    'app_name' => 'FlowBreath',
    'confirm' => '확인',
    'cancel' => '취소',
    'save' => '저장',
    'edit' => '수정',
    'delete' => '삭제',
    'search' => '검색',
    'back' => '뒤로',
    'yes' => '예',
    'no' => '아니오',

    // 리소스
    'resource' => [
        'list' => '리소스 목록',
        'create' => '리소스 등록',
        'edit' => '리소스 수정',
        'delete' => '리소스 삭제',
        'title' => '제목',
        'summary' => '요약',
        'content' => '내용',
        'url' => '원본 URL',
        'author' => '작성자',
        'created_at' => '작성일',
        'updated_at' => '수정일',
        'tags' => '태그',
        'visibility' => [
            'all' => '전체',
            'public' => '공개',
            'private' => '비공개'
        ],
        'is_pinned' => '상단 고정',
        'initial_impression' => '초기 인상',
        'personal_connection' => '개인적 연관성',
        'reflection_insights' => '반영 및 통찰',
        'application_ideas' => '적용 아이디어',
        'search_placeholder' => '제목, 내용, 요약에서 검색',
        'select_tags' => '태그를 선택하세요',
        'sort' => [
            'latest' => '최신순',
            'oldest' => '오래된순',
            'title' => '제목순'
        ],
        'filter' => [
            'all' => '전체',
            'public' => '공개만',
            'private' => '비공개만'
        ],
        'messages' => [
            'create_success' => '리소스가 성공적으로 등록되었습니다.',
            'update_success' => '리소스가 성공적으로 수정되었습니다.',
            'delete_success' => '리소스가 성공적으로 삭제되었습니다.',
            'delete_confirm' => '정말로 이 리소스를 삭제하시겠습니까?',
            'delete_warning' => '이 작업은 되돌릴 수 없습니다.',
            'not_found' => '리소스를 찾을 수 없습니다.',
            'no_results' => '검색 결과가 없습니다. 다른 검색어나 필터를 시도해보세요.'
        ]
    ],

    // 태그
    'tag' => [
        'management' => '태그 관리',
        'add' => '태그 추가',
        'remove' => '태그 제거',
        'name' => '태그 이름',
        'count' => '사용 횟수',
        'messages' => [
            'add_success' => '태그가 추가되었습니다.',
            'remove_success' => '태그가 제거되었습니다.',
            'exists' => '이미 존재하는 태그입니다.'
        ]
    ],

    // 페이지네이션
    'pagination' => [
        'previous' => '이전',
        'next' => '다음',
        'showing' => ':total개 중 :from-:to',
    ],

    // 오류
    'error' => [
        'title' => '오류가 발생했습니다',
        'back_to_home' => '홈으로 돌아가기',
        'unauthorized' => '권한이 없습니다.',
        'invalid_request' => '잘못된 요청입니다.',
        'required_field' => ':field 필드는 필수입니다.',
        'server_error' => '서버 오류가 발생했습니다.'
    ],

    // 인증
    'auth' => [
        'login' => '로그인',
        'logout' => '로그아웃',
        'login_with_google' => 'Google 계정으로 로그인',
        'login_help' => '로그인에 문제가 있으신가요?',
        'contact_support' => '고객 지원에 문의하기',
        'registration_success' => '회원 가입이 완료되었습니다.',
        'google_login_failed' => 'Google 로그인 중 오류가 발생했습니다.',
        'unauthorized' => '권한이 없습니다.',
        'login_required' => '로그인이 필요한 서비스입니다.',
        'already_logged_in' => '이미 로그인되어 있습니다.',
        'invalid_credentials' => '잘못된 로그인 정보입니다.',
        'account_not_found' => '계정을 찾을 수 없습니다.',
        'account_disabled' => '계정이 비활성화되었습니다.',
        'verify_email' => '이메일 인증이 필요합니다.',
        'logout_success' => '로그아웃되었습니다.'
    ],

    // 프로필
    'profile' => [
        'edit_title' => '프로필 수정',
        'profile_image' => '프로필 이미지',
        'name' => '이름',
        'name_required' => '이름을 입력해주세요.',
        'email' => '이메일',
        'google_connected' => 'Google 계정으로 연동됨',
        'current_password' => '현재 비밀번호',
        'new_password' => '새 비밀번호',
        'confirm_password' => '비밀번호 확인',
        'password_hint' => '비밀번호는 8자 이상이어야 합니다.',
        'password_mismatch' => '비밀번호가 일치하지 않습니다.',
        'notifications' => '알림 설정',
        'notify_comments' => '댓글 알림 받기',
        'notify_updates' => '업데이트 알림 받기',
        'delete_account' => '계정 삭제',
        'delete_warning' => '계정을 삭제하면 모든 데이터가 영구적으로 삭제됩니다.',
        'delete_confirm' => '정말로 계정을 삭제하시겠습니까?',
        'delete_permanent' => '이 작업은 되돌릴 수 없습니다.',
        'update_success' => '프로필이 성공적으로 업데이트되었습니다.',
        'update_error' => '프로필 업데이트 중 오류가 발생했습니다.',
        'wrong_password' => '현재 비밀번호가 올바르지 않습니다.',
        'image_upload_error' => '이미지 업로드 중 오류가 발생했습니다.',
        'image_type_error' => '지원하지 않는 이미지 형식입니다.',
        'image_size_error' => '이미지 크기는 2MB를 초과할 수 없습니다.'
    ],

    // 관리자
    'admin' => [
        'dashboard' => '관리자 대시보드',
        'users' => '사용자 관리',
        'resources' => '리소스 관리',
        'tags' => '태그 관리',
        'settings' => '시스템 설정',
        'total_users' => '전체 사용자',
        'total_resources' => '전체 리소스',
        'total_tags' => '전체 태그',
        'storage_used' => '저장소 사용량',
        'new_today' => '오늘 {count}개 추가',
        'most_used' => '가장 많이 사용: {tag}',
        'total_files' => '전체 {count}개 파일',
        'recent_users' => '최근 가입한 사용자',
        'recent_resources' => '최근 등록된 리소스',
        'view_all' => '전체 보기',
        'name' => '이름',
        'email' => '이메일',
        'joined_at' => '가입일',
        'actions' => '작업',
        'title' => '제목',
        'author' => '작성자',
        'created_at' => '작성일',
        'system_status' => '시스템 상태',
        'php_version' => 'PHP 버전',
        'server_info' => '서버 정보',
        'database_size' => '데이터베이스 크기',
        'user_not_found' => '사용자를 찾을 수 없습니다.',
        'name_required' => '이름을 입력해주세요.',
        'password_too_short' => '비밀번호는 8자 이상이어야 합니다.',
        'user_updated' => '사용자 정보가 업데이트되었습니다.',
        'tags_merged' => '태그가 병합되었습니다.',
        'tag_deleted' => '태그가 삭제되었습니다.',
        'tag_renamed' => '태그 이름이 변경되었습니다.',
        'settings_updated' => '설정이 업데이트되었습니다.',
        'dashboard_error' => '대시보드를 불러오는 중 오류가 발생했습니다.',
        'users_error' => '사용자 목록을 불러오는 중 오류가 발생했습니다.',
        'resources_error' => '리소스 목록을 불러오는 중 오류가 발생했습니다.',
        'tags_error' => '태그 목록을 불러오는 중 오류가 발생했습니다.',
        'settings_error' => '설정을 불러오는 중 오류가 발생했습니다.'
    ],

    // 댓글
    'comment' => [
        'content_required' => '댓글 내용을 입력해주세요.',
        'create_success' => '댓글이 등록되었습니다.',
        'update_success' => '댓글이 수정되었습니다.',
        'delete_success' => '댓글이 삭제되었습니다.',
        'delete_confirm' => '정말로 이 댓글을 삭제하시겠습니까?',
        'delete_warning' => '이 작업은 되돌릴 수 없습니다.',
        'write_comment' => '댓글 작성',
        'edit_comment' => '댓글 수정',
        'delete_comment' => '댓글 삭제',
        'is_public' => '공개',
        'is_private' => '비공개',
        'no_comments' => '아직 댓글이 없습니다.',
        'load_more' => '더 보기',
        'reply' => '답글',
        'submit' => '등록',
        'update' => '수정'
    ],

    // 네비게이션
    'nav' => [
        'breathing' => '호흡 운동',
        'resources' => '자료',
        'tags' => '태그',
        'api_docs' => 'API 안내',
        'my_info' => '내 정보',
        'login' => '로그인',
        'register' => '회원가입',
        'logout' => '로그아웃'
    ],

    // 호흡 운동
    'breathing' => [
        'title' => '호흡 운동',
        'patterns' => [
            'title' => '호흡 패턴',
            'danjeon' => '단전 호흡',
            '478' => '4-7-8 호흡법',
            'box' => '박스 호흡법'
        ],
        'settings' => [
            'title' => '설정',
            'sound' => '소리',
            'vibration' => '진동'
        ],
        'timer' => [
            'title' => '호흡 시간 (초)',
            'inhale' => '들숨',
            'exhale' => '날숨',
            'duration' => '운동 시간 (초)'
        ],
        'controls' => [
            'ready' => '준비',
            'start' => '시작',
            'stop' => '정지'
        ]
    ]
]; 