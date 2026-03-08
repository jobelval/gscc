<?php
// includes/i18n.php - Fonctions d'internationalisation

/**
 * Affiche un texte traduit
 */
function _e($key)
{
    echo __($key);
}

/**
 * Génère un lien avec conservation de la langue
 */
function url_lang($path = '')
{
    return SITE_URL . '/' . ltrim($path, '/');
}

/**
 * Génère un sélecteur de langue
 */
function language_selector()
{
    global $langues_disponibles, $lang;

    $html = '<div class="language-selector">';
    foreach ($langues_disponibles as $code => $nom) {
        $active = ($code == $lang) ? 'active' : '';
        $html .= '<a href="?lang=' . $code . '" class="' . $active . '">' . strtoupper($code) . '</a>';
        if ($code !== array_key_last($langues_disponibles)) {
            $html .= '<span>/</span>';
        }
    }
    $html .= '</div>';

    return $html;
}
