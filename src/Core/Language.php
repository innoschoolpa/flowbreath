<?php

namespace App\Core;

class Language
{
    private static $instance = null;
    private $translations = [];
    private $currentLang = 'ko';
    private $fallbackLang = 'en';
    private $langPath;
    private $currentLanguage = 'ko';
    private $defaultLanguage = 'ko';
    private $availableLanguages = ['ko', 'en'];

    private function __construct()
    {
        $this->langPath = dirname(__DIR__, 2) . '/src/Lang';
        $this->loadLanguage();
        if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], $this->availableLanguages)) {
            $this->currentLanguage = $_SESSION['lang'];
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadLanguage()
    {
        // 브라우저 언어 설정 확인
        $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'ko', 0, 2);
        $this->currentLang = in_array($browserLang, ['ko', 'en']) ? $browserLang : 'ko';

        // 세션에 저장된 언어 설정이 있으면 사용
        if (isset($_SESSION['lang'])) {
            $this->currentLang = $_SESSION['lang'];
        }

        // 현재 언어 파일 로드
        $this->loadTranslationFile($this->currentLang);

        // 폴백 언어가 다르면 추가로 로드
        if ($this->currentLang !== $this->fallbackLang) {
            $this->loadTranslationFile($this->fallbackLang);
        }
    }

    private function loadTranslationFile($lang)
    {
        $file = "{$this->langPath}/{$lang}.json";
        if (file_exists($file)) {
            $translations = json_decode(file_get_contents($file), true);
            if ($translations) {
                $this->translations[$lang] = $translations;
            }
        }
    }

    public function setLanguage(string $lang): void
    {
        if (in_array($lang, $this->availableLanguages)) {
            $this->currentLang = $lang;
            $_SESSION['lang'] = $lang;
            $this->loadTranslationFile($lang);
        }
    }

    public function get($key, $params = [])
    {
        $keys = explode('.', $key);
        $translation = $this->getNestedTranslation($keys, $this->currentLang);

        // 현재 언어에서 번역을 찾지 못하면 폴백 언어 사용
        if ($translation === null && $this->currentLang !== $this->fallbackLang) {
            $translation = $this->getNestedTranslation($keys, $this->fallbackLang);
        }

        if ($translation === null) {
            return $key; // 번역을 찾지 못하면 키 반환
        }

        // 파라미터 치환
        if (!empty($params)) {
            foreach ($params as $param => $value) {
                $translation = str_replace("{{$param}}", $value, $translation);
            }
        }

        return $translation;
    }

    private function getNestedTranslation($keys, $lang)
    {
        $current = $this->translations[$lang] ?? [];
        
        foreach ($keys as $key) {
            if (!isset($current[$key])) {
                return null;
            }
            $current = $current[$key];
        }

        return is_string($current) ? $current : null;
    }

    public function getCurrentLang()
    {
        return $this->currentLang;
    }

    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }

    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }

    public function getAvailableLanguages(): array
    {
        return $this->availableLanguages;
    }
} 