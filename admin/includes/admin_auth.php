<?php
// admin/includes/admin_auth.php
session_start();
require_once '../../includes/config.php';

// Vérifier si l'utilisateur est connecté ET est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

// Récupérer les informations de l'admin
$admin_id = $_SESSION['user_id'];
$admin_nom = $_SESSION['user_nom'];
$admin_email = $_SESSION['user_email'];

// Vérifier si la session n'a pas expiré
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
    session_unset();
    session_destroy();
    header('Location: login.php?expired=1');
    exit;
}

$_SESSION['last_activity'] = time();
