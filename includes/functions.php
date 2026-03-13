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
        'type'    => $type,
        'message' => $message,
        'time'    => time()
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
        1  => 'janvier',   2  => 'février',  3  => 'mars',
        4  => 'avril',     5  => 'mai',       6  => 'juin',
        7  => 'juillet',   8  => 'août',      9  => 'septembre',
        10 => 'octobre',   11 => 'novembre',  12 => 'décembre'
    ];

    $timestamp = strtotime($date);
    $day   = date('j', $timestamp);
    $month = $months[date('n', $timestamp)];
    $year  = date('Y', $timestamp);

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

    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }

    $timestamp  = date('Y-m-d H:i:s');
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

    $fileInfo  = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension']);

    if (!in_array($extension, $allowedTypes)) {
        return ['success' => false, 'error' => 'Type de fichier non autorisé'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'Fichier trop volumineux (max 5MB)'];
    }

    $newFilename = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $uploadPath  = $destination . $newFilename;

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
        'From: '     . $from,
        'Reply-To: ' . $from,
        'X-Mailer: PHP/' . phpversion()
    ];

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
                <p>&copy; " . date('Y') . " " . SITE_NAME . ". Tous droits réservés.</p>
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
            $stmt   = $pdo->query("SELECT * FROM parametres");
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

/**
 * Connexion PDO singleton
 */
function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    return $pdo;
}


// ════════════════════════════════════════════════════════════
//  AUTHENTIFICATION
// ════════════════════════════════════════════════════════════

/**
 * Inscription — crée un nouveau compte utilisateur.
 *
 * @param string $telephone  Optionnel
 * @return array ['success' => bool, 'error' => string|null]
 */
function registerUser(
    string $nom,
    string $prenom,
    string $email,
    string $password,
    string $telephone = ''
): array {
    global $pdo;

    try {
        // Email déjà utilisé ?
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Cette adresse email est déjà utilisée.'];
        }

        // Hash bcrypt sécurisé
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt = $pdo->prepare("
            INSERT INTO utilisateurs
                (nom, prenom, email, mot_de_passe, telephone,
                 role, type_membre, statut, date_inscription)
            VALUES
                (:nom, :prenom, :email, :mdp, :tel,
                 'membre', 'actif', 'actif', NOW())
        ");
        $stmt->execute([
            ':nom'    => $nom,
            ':prenom' => $prenom,
            ':email'  => $email,
            ':mdp'    => $hash,
            ':tel'    => $telephone ?: null,   // NULL si champ vide
        ]);

        $userId = $pdo->lastInsertId();

        session_regenerate_id(true);
        $_SESSION['user_id']     = $userId;
        $_SESSION['user_email']  = $email;
        $_SESSION['user_nom']    = $nom;
        $_SESSION['user_prenom'] = $prenom;
        $_SESSION['user_role']   = 'membre';

        return ['success' => true];

    } catch (PDOException $e) {
        error_log('registerUser error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Une erreur est survenue. Veuillez réessayer.'];
    }
}

/**
 * Connexion — vérifie les identifiants et ouvre la session.
 *
 * @return array ['success' => bool, 'error' => string|null]
 */
function loginUser(string $email, string $password): array
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT id, nom, prenom, email, mot_de_passe, role, statut
            FROM utilisateurs
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'error' => 'Email ou mot de passe incorrect.'];
        }

        if ($user['statut'] !== 'actif') {
            return ['success' => false, 'error' => 'Ce compte a été désactivé. Contactez l\'administrateur.'];
        }

        if (!password_verify($password, $user['mot_de_passe'])) {
            return ['success' => false, 'error' => 'Email ou mot de passe incorrect.'];
        }

        // Mise à jour de la dernière connexion
        $pdo->prepare("UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?")
            ->execute([$user['id']]);

        session_regenerate_id(true);
        $_SESSION['user_id']     = $user['id'];
        $_SESSION['user_email']  = $user['email'];
        $_SESSION['user_nom']    = $user['nom'];
        $_SESSION['user_prenom'] = $user['prenom'];
        $_SESSION['user_role']   = $user['role'];

        return ['success' => true];

    } catch (PDOException $e) {
        error_log('loginUser error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Une erreur est survenue. Veuillez réessayer.'];
    }
}

/**
 * Connexion Google — connecte ou crée automatiquement un compte.
 *
 * @return array ['success' => bool, 'error' => string|null]
 */
function loginOrCreateGoogleUser(
    string $email,
    string $prenom,
    string $nom,
    string $googleId,
    string $photo = ''
): array {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Compte existant : met à jour google_id et photo_url si vides
            $pdo->prepare("
                UPDATE utilisateurs
                SET
                    google_id          = COALESCE(google_id, :gid),
                    photo_url          = COALESCE(NULLIF(photo_url, ''), :photo),
                    derniere_connexion = NOW()
                WHERE id = :id
            ")->execute([
                ':gid'   => $googleId,
                ':photo' => $photo,
                ':id'    => $user['id'],
            ]);

            if ($user['statut'] !== 'actif') {
                return ['success' => false, 'error' => 'Ce compte est désactivé. Contactez l\'administrateur.'];
            }

        } else {
            // Nouveau compte créé via Google (email déjà vérifié par Google)
            $stmt = $pdo->prepare("
                INSERT INTO utilisateurs
                    (nom, prenom, email, mot_de_passe, google_id, photo_url,
                     role, type_membre, statut, email_verifie, date_inscription)
                VALUES
                    (:nom, :prenom, :email, '', :google_id, :photo,
                     'membre', 'actif', 'actif', 1, NOW())
            ");
            $stmt->execute([
                ':nom'       => $nom    ?: explode('@', $email)[0],
                ':prenom'    => $prenom ?: '',
                ':email'     => $email,
                ':google_id' => $googleId,
                ':photo'     => $photo,
            ]);

            $user = [
                'id'     => $pdo->lastInsertId(),
                'nom'    => $nom    ?: explode('@', $email)[0],
                'prenom' => $prenom ?: '',
                'email'  => $email,
                'role'   => 'membre',
            ];
        }

        session_regenerate_id(true);
        $_SESSION['user_id']     = $user['id'];
        $_SESSION['user_email']  = $user['email'];
        $_SESSION['user_nom']    = $user['nom'];
        $_SESSION['user_prenom'] = $user['prenom'];
        $_SESSION['user_role']   = $user['role'];

        return ['success' => true];

    } catch (PDOException $e) {
        error_log('loginOrCreateGoogleUser error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la connexion Google.'];
    }
}