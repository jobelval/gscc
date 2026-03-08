<?php
// lang/config.php

// Langues disponibles
$langues_disponibles = [
    'fr' => 'Français',
    'en' => 'English'
];

// Langue par défaut
define('LANGUE_DEFAUT', 'fr');

// Détection de la langue
function getCurrentLanguage()
{
    global $langues_disponibles;

    // 1. Vérifier la session
    if (isset($_SESSION['lang']) && array_key_exists($_SESSION['lang'], $langues_disponibles)) {
        return $_SESSION['lang'];
    }

    // 2. Vérifier le cookie
    if (isset($_COOKIE['lang']) && array_key_exists($_COOKIE['lang'], $langues_disponibles)) {
        $_SESSION['lang'] = $_COOKIE['lang'];
        return $_COOKIE['lang'];
    }

    // 3. Détection automatique par le navigateur
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if (array_key_exists($browser_lang, $langues_disponibles)) {
            $_SESSION['lang'] = $browser_lang;
            return $browser_lang;
        }
    }

    // 4. Langue par défaut
    return LANGUE_DEFAUT;
}

// Charger les traductions
function loadTranslations($lang)
{
    $file = __DIR__ . '/' . $lang . '.php';
    if (file_exists($file)) {
        return require $file;
    }
    return require __DIR__ . '/' . LANGUE_DEFAUT . '.php';
}

// Initialiser la langue
$lang = getCurrentLanguage();
$translations = loadTranslations($lang);

// Fonction de traduction
function __($key)
{
    global $translations;
    return $translations[$key] ?? $key;
}

// Fonction pour changer de langue
function switchLanguage($new_lang)
{
    global $langues_disponibles;

    if (array_key_exists($new_lang, $langues_disponibles)) {
        $_SESSION['lang'] = $new_lang;
        setcookie('lang', $new_lang, time() + (86400 * 30), '/'); // 30 jours
        return true;
    }
    return false;
}

// Traitement du changement de langue
if (isset($_GET['lang']) && !empty($_GET['lang'])) {
    switchLanguage($_GET['lang']);
    // Rediriger vers la même page sans le paramètre lang
    $redirect = strtok($_SERVER['REQUEST_URI'], '?');
    header('Location: ' . $redirect);
    exit;
}
