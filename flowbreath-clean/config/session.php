<?php

return [
    // 세션 쿠키 수명 (초)
    'lifetime' => 7200, // 2시간

    // 쿠키 경로
    'path' => '/',

    // 쿠키 도메인
    'domain' => '',

    // HTTPS 연결에서만 쿠키 전송
    'secure' => true,

    // JavaScript에서 쿠키 접근 방지
    'httponly' => true,

    // SameSite 쿠키 정책
    'samesite' => 'Lax',

    // 세션 이름
    'name' => 'FLOWBREATH_SESSION',

    // 세션 저장소 경로
    'save_path' => sys_get_temp_dir(),

    // 세션 가비지 컬렉션 확률 (퍼센트)
    'gc_probability' => 1,
    'gc_divisor' => 100,

    // 세션 가비지 컬렉션 최대 수명 (초)
    'gc_maxlifetime' => 7200,

    // 세션 ID 재생성 간격 (초)
    'regenerate_interval' => 300, // 5분
]; 