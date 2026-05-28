<?php
// Language helper
// Loads translation array based on selected language and provides a function to retrieve translated strings.
// Usage: include_once __DIR__.'/lang.php'; then call loadLanguage('en'); and t('key');

if (!function_exists('loadLanguage')) {
    function loadLanguage(string $lang = 'en') {
        // Define path to language files directory
        $langDir = __DIR__ . '/lang';
        $file = "$langDir/{$lang}.php";
        if (file_exists($file)) {
            $translations = include $file;
            if (is_array($translations)) {
                $_SESSION['translations'] = $translations;
            } else {
                $_SESSION['translations'] = [];
            }
        } else {
            $_SESSION['translations'] = [];
        }
    }
}

if (!function_exists('t')) {
    function t(string $key, $default = null) {
        return $_SESSION['translations'][$key] ?? $default ?? $key;
    }
}
?>
