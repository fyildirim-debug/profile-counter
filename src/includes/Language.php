<?php
class Language {
    private static $translations = [];
    private static $currentLang = 'tr';
    private static $initialized = false;

    public static function init() {
        if (!self::$initialized) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            self::$currentLang = $_SESSION['lang'] ?? 'tr';

            // Dil dosyasını yükle
            $langFile = __DIR__ . '/../languages/' . self::$currentLang . '.php';
            if (file_exists($langFile)) {
                self::$translations = require $langFile;
            }
            self::$initialized = true;
        }
    }

    public static function get($key) {
        return self::$translations[$key] ?? $key;
    }

    public static function setLang($lang) {
        if (in_array($lang, ['tr', 'en'])) {
            $_SESSION['lang'] = $lang;
            self::$currentLang = $lang;

            // Dil dosyasını yeniden yükle
            $langFile = __DIR__ . '/../languages/' . self::$currentLang . '.php';
            if (file_exists($langFile)) {
                self::$translations = require $langFile;
            }
        }
    }

    public static function getCurrentLang() {
        return self::$currentLang;
    }
}