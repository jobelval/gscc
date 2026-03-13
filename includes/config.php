<?php
// includes/config.php - Version finale avec gestion des sessions

// =============================================
// 1. DÉSACTIVER TEMPORAIREMENT LES SESSIONS AUTOMATIQUES
// =============================================

// Vérifier si une session est déjà active
$session_was_active = false;
if (session_status() === PHP_SESSION_ACTIVE) {
    $session_was_active = true;
    session_write_close(); // Fermer la session pour pouvoir modifier les paramètres
}

// =============================================
// 2. CONFIGURATION DES SESSIONS (MAINTENANT POSSIBLE)
// =============================================
define('GOOGLE_CLIENT_ID',     'XXXXXXX.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'XXXXXXX');
define('GOOGLE_REDIRECT_URI',  'https://tonsite.com/auth/google-callback.php');


// Supprimer les warnings en vérifiant si les paramètres peuvent être modifiés
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Mettre à 1 en production avec HTTPS
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.gc_maxlifetime', 7200); // 2 heures
    ini_set('session.cookie_lifetime', 0); // Jusqu'à la fermeture du navigateur
    ini_set('session.use_strict_mode', 1);
}

// =============================================
// 3. REDÉMARRER LA SESSION
// =============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
} elseif ($session_was_active) {
    // Si on avait fermé une session, on la rouvre
    session_start();
}

// =============================================
// 4. CONSTANTES DE CONFIGURATION
// =============================================

// Base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'gscc');
define('DB_USER', 'root'); // À modifier en production
define('DB_PASS', ''); // À modifier en production

// Site
define('SITE_NAME', 'GSCC - Groupe de Support Contre le Cancer');
define('SITE_URL', 'http://localhost/gscc'); // À modifier en production
define('SITE_EMAIL', 'gscc@gscchaiti.com');

// Chemins absolus
define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');
define('UPLOADS_PATH', ROOT_PATH . 'assets/uploads/');

// URLs
define('ASSETS_URL', SITE_URL . '/assets/');
define('UPLOADS_URL', SITE_URL . '/assets/uploads/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMAGES_URL', ASSETS_URL . 'images/');

// Sécurité
define('SALT', 'votre_chaine_aleatoire_unique_ici_' . md5(__DIR__));
define('CSRF_TOKEN_NAME', 'csrf_token_gscc');
define('SESSION_LIFETIME', 7200); // 2 heures en secondes
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// Uploads
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', serialize(['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']));

// Pagination
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 25);

// Cache
define('CACHE_ENABLED', false);
define('CACHE_LIFETIME', 3600); // 1 heure

// Timezone
date_default_timezone_set('America/Port-au-Prince');

// =============================================
// 5. CONNEXION À LA BASE DE DONNÉES
// =============================================

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );
} catch (PDOException $e) {
    // Log l'erreur
    error_log("Erreur de connexion BDD : " . $e->getMessage());

    // Message d'erreur générique
    die("Désolé, une erreur technique est survenue. Veuillez réessayer plus tard.");
}

// =============================================
// 6. SÉCURITÉ DE LA SESSION
// =============================================

// Vérification anti-hijacking
if (isset($_SESSION['ip_address']) && isset($_SESSION['user_agent'])) {
    if (
        $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR'] ||
        $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']
    ) {
        // Session potentiellement volée - on détruit tout
        session_unset();
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }
}

// Initialiser les variables de session si nouvelle session
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['created_at'] = time();
}

// =============================================
// 7. CHARGEMENT DES FONCTIONS
// =============================================

// Charger les fichiers de fonctions
if (file_exists(INCLUDES_PATH . 'functions.php')) {
    require_once INCLUDES_PATH . 'functions.php';
}

if (file_exists(INCLUDES_PATH . 'db_functions.php')) {
    require_once INCLUDES_PATH . 'db_functions.php';
}

// Initialisation terminée
$_SESSION['last_activity'] = time();

// Supprimer les warnings PHP (optionnel)
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

require_once ROOT_PATH . 'lang/config.php';
require_once INCLUDES_PATH . 'i18n.php';