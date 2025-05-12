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
        file_put_contents(__DIR__.'/../../debug.log', "switch: $lang\n", FILE_APPEND);
        if (in_array($lang, ['ko', 'en'])) {
            $this->language->setLanguage($lang);
            file_put_contents(__DIR__.'/../../debug.log', "setLanguage: $lang\n", FILE_APPEND);
            file_put_contents(__DIR__.'/../../debug.log', print_r($_SESSION, true), FILE_APPEND);
        }
        // 이전 페이지로 리다이렉트 (없으면 홈)
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: $redirectUrl");
        exit;
    }
} 