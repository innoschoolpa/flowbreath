<?php

/**
 * 현재 언어 설정
 */
function set_language($lang) {
    $_SESSION['lang'] = $lang;
}

/**
 * 현재 언어 가져오기
 */
function get_language() {
    return $_SESSION['lang'] ?? 'ko';
}

/**
 * 언어 파일 로드
 */
function load_language_file($lang) {
    $file = __DIR__ . "/../lang/{$lang}/messages.php";
    if (file_exists($file)) {
        return require $file;
    }
    // 기본 언어(한국어) 파일 로드
    return require __DIR__ . "/../lang/ko/messages.php";
}

/**
 * 번역된 메시지 가져오기
 * 예: __('resource.title') => '제목'
 * 예: __('pagination.showing', ['from' => 1, 'to' => 10, 'total' => 100]) => '100개 중 1-10'
 */
function __($key, $replacements = []) {
    static $messages = null;
    
    if ($messages === null) {
        $messages = load_language_file(get_language());
    }

    $keys = explode('.', $key);
    $message = $messages;
    
    foreach ($keys as $k) {
        if (!isset($message[$k])) {
            return $key;
        }
        $message = $message[$k];
    }

    if (!empty($replacements) && is_string($message)) {
        foreach ($replacements as $key => $value) {
            $message = str_replace(":{$key}", $value, $message);
        }
    }

    return $message;
}

/**
 * 언어 선택기 HTML 생성
 */
function language_selector() {
    $current_lang = get_language();
    $languages = [
        'ko' => '한국어',
        'en' => 'English'
    ];

    $html = '<div class="dropdown">';
    $html .= '<button class="btn btn-outline-secondary dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown">';
    $html .= $languages[$current_lang];
    $html .= '</button>';
    $html .= '<ul class="dropdown-menu" aria-labelledby="languageDropdown">';
    
    foreach ($languages as $code => $name) {
        $active = $code === $current_lang ? ' active' : '';
        $html .= sprintf(
            '<li><a class="dropdown-item%s" href="?lang=%s">%s</a></li>',
            $active,
            $code,
            $name
        );
    }
    
    $html .= '</ul></div>';
    
    return $html;
} 