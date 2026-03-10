<?php
// includes/functions.php - Fonctions utilitaires

/**
 * Affiche une variable de manière formatée (debug)
 */
function debug($var)
{
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

/**
 * Nettoie les entrées utilisateur
 */
function sanitize($input)
{
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirige vers une URL
 */
function redirect($url)
{
    header("Location: " . $url);
    exit;
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur est admin
 */
function isAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Vérifie si l'utilisateur est modérateur ou admin
 */
function isModerator()
{
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['moderateur', 'admin']);
}

/**
 * Génère une URL sécurisée
 */
function url($path = '')
{
    return SITE_URL . '/' . ltrim($path, '/');
}

/**
 * Génère un token CSRF
 */
function generateCSRFToken()
{
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Vérifie le token CSRF
 */
function verifyCSRFToken($token)
{
    if (empty($_SESSION[CSRF_TOKEN_NAME]) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Affiche un message flash
 */
function setFlashMessage($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
        'time' => time()
    ];
}

/**
 * Récupère et efface le message flash
 */
function getFlashMessage()
{
    if (isset($_SESSION['flash'])) {
        // Supprimer les flashs de plus de 5 minutes
        if (time() - $_SESSION['flash']['time'] > 300) {
            unset($_SESSION['flash']);
            return null;
        }
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Échappe les données pour le HTML
 */
function e($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Tronque un texte
 */
function truncate($text, $length = 100, $suffix = '...')
{
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Formate une date
 */
function formatDate($date, $format = 'd/m/Y')
{
    if (empty($date)) {
        return '';
    }
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Formate une date en français
 */
function formatDateFr($date)
{
    if (empty($date)) {
        return '';
    }

    $months = [
        1 => 'janvier',
        2 => 'février',
        3 => 'mars',
        4 => 'avril',
        5 => 'mai',
        6 => 'juin',
        7 => 'juillet',
        8 => 'août',
        9 => 'septembre',
        10 => 'octobre',
        11 => 'novembre',
        12 => 'décembre'
    ];

    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[date('n', $timestamp)];
    $year = date('Y', $timestamp);

    return "$day $month $year";
}

/**
 * Vérifie si une requête est de type POST
 */
function isPost()
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Vérifie si une requête est de type GET
 */
function isGet()
{
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Récupère une valeur POST ou GET
 */
function input($key, $default = null)
{
    if (isPost()) {
        return $_POST[$key] ?? $default;
    }
    return $_GET[$key] ?? $default;
}

/**
 * Génère un slug à partir d'une chaîne
 */
function createSlug($string)
{
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Vérifie si l'utilisateur a accès à une page
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Vous devez être connecté pour accéder à cette page.');
        redirect(url('connexion.php'));
    }
}

/**
 * Vérifie si l'utilisateur est admin
 */
function requireAdmin()
{
    requireLogin();
    if (!isAdmin()) {
        setFlashMessage('error', 'Accès non autorisé.');
        redirect(url('index.php'));
    }
}

/**
 * Vérifie si l'utilisateur est modérateur ou admin
 */
function requireModerator()
{
    requireLogin();
    if (!isModerator()) {
        setFlashMessage('error', 'Accès non autorisé.');
        redirect(url('index.php'));
    }
}

/**
 * Affiche une image avec fallback
 */
function imageUrl($path, $default = 'default.jpg')
{
    $fullPath = UPLOADS_PATH . $path;
    if (!empty($path) && file_exists($fullPath)) {
        return UPLOADS_URL . $path;
    }
    return IMAGES_URL . $default;
}

/**
 * Affiche une icône Font Awesome
 */
function icon($name, $class = '')
{
    return '<i class="fas fa-' . $name . ' ' . $class . '"></i>';
}

/**
 * Vérifie si la page courante est active
 */
function isActive($page)
{
    $currentPage = basename($_SERVER['PHP_SELF']);
    return $currentPage === $page ? 'active' : '';
}

/**
 * Fonction de log personnalisée
 */
function logError($message)
{
    $logFile = ROOT_PATH . 'logs/error.log';

    // Créer le dossier logs s'il n'existe pas
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

/**
 * Upload de fichier sécurisé
 */
function uploadFile($file, $destination, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'])
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Erreur lors de l\'upload'];
    }

    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension']);

    if (!in_array($extension, $allowedTypes)) {
        return ['success' => false, 'error' => 'Type de fichier non autorisé'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'Fichier trop volumineux (max 5MB)'];
    }

    $newFilename = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $uploadPath = $destination . $newFilename;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $newFilename];
    }

    return ['success' => false, 'error' => 'Erreur lors de la sauvegarde'];
}

/**
 * Envoi d'email
 */
function sendEmail($to, $subject, $message, $from = null)
{
    if (!$from) {
        $from = SITE_EMAIL;
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: ' . $from,
        'Reply-To: ' . $from,
        'X-Mailer: PHP/' . phpversion()
    ];

    // Template d'email
    $htmlMessage = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #003399; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>" . SITE_NAME . "</h2>
            </div>
            <div class='content'>
                " . nl2br($message) . "
            </div>
            <div class='footer'>
                <p>© " . date('Y') . " " . SITE_NAME . ". Tous droits réservés.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return mail($to, $subject, $htmlMessage, implode("\r\n", $headers));
}

/**
 * Récupère un paramètre de configuration
 */
function getParam($key, $default = '')
{
    global $pdo;
    try {
        static $params = null;
        if ($params === null) {
            $stmt = $pdo->query("SELECT * FROM parametres");
            $params = [];
            while ($row = $stmt->fetch()) {
                $params[$row['cle']] = $row['valeur'];
            }
        }
        return $params[$key] ?? $default;
    } catch (PDOException $e) {
        logError("Erreur getParam: " . $e->getMessage());
        return $default;
    }
}
