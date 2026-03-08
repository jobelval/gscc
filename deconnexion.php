<?php
// deconnexion.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Vider toutes les variables de session
$_SESSION = [];

// Supprimer le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Supprimer le cookie "Se souvenir de moi"
setcookie('remember_token', '', time() - 3600, '/', '', true, true);

// Détruire la session
session_destroy();

// Rediriger vers l'accueil
redirect('index.php');
