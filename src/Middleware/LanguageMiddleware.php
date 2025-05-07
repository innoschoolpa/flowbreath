<?php
namespace Middleware;

class LanguageMiddleware {
    public function handle() {
        // 언어 변경 요청 처리
        if (isset($_GET['lang'])) {
            $lang = $_GET['lang'];
            if (in_array($lang, ['ko', 'en'])) {
                set_language($lang);
                
                // 현재 URL에서 lang 파라미터 제거
                $url = parse_url($_SERVER['REQUEST_URI']);
                $query = [];
                if (isset($url['query'])) {
                    parse_str($url['query'], $query);
                    unset($query['lang']);
                }
                
                // 리다이렉트 URL 생성
                $redirect = $url['path'];
                if (!empty($query)) {
                    $redirect .= '?' . http_build_query($query);
                }
                
                header('Location: ' . $redirect);
                exit;
            }
        }

        // 언어가 설정되어 있지 않으면 브라우저 언어 설정 확인
        if (!isset($_SESSION['lang'])) {
            $browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'ko', 0, 2);
            set_language(in_array($browser_lang, ['ko', 'en']) ? $browser_lang : 'ko');
        }
    }
} 