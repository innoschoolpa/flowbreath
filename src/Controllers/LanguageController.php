<?php

namespace App\Controllers;

use App\Core\Language;

class LanguageController
{
    private $language;

    public function __construct()
    {
        $this->language = Language::getInstance();
    }

    public function switch($lang)
    {
        if (in_array($lang, ['ko', 'en'])) {
            $_SESSION['lang'] = $lang;
            $this->language->setLanguage($lang);
        }
        // 이전 페이지로 리다이렉트 (없으면 홈)
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: $redirectUrl");
        exit;
    }
} 