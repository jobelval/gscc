<?php
/**
 * GSCC CMS — admin/includes/auth.php
 * Middleware d'authentification admin.
 *
 * IMPORTANT : Toutes les fonctions sont protégées par if (!function_exists(...))
 * pour éviter les Fatal Error "Cannot redeclare" avec includes/functions.php.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ── Authentification ── */

if (!function_exists('requireAdmin')) {
    function requireAdmin(): void {
        if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if ($uri && strpos($uri, 'login') === false) {
                $_SESSION['redirect_after_login'] = $uri;
            }
            header('Location: ' . getAdminBase() . '/login.php');
            exit;
        }
        if (isset($_SESSION['admin_ip']) && $_SESSION['admin_ip'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
            adminLogout();
        }
        if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity']) > 7200) {
            adminLogout('Votre session a expiré. Veuillez vous reconnecter.');
        }
        $_SESSION['admin_last_activity'] = time();
    }
}

if (!function_exists('requireModerator')) {
    function requireModerator(): void {
        if (!isset($_SESSION['admin_id']) || !in_array($_SESSION['admin_role'] ?? '', ['admin','moderateur'])) {
            header('Location: ' . getAdminBase() . '/login.php');
            exit;
        }
    }
}

if (!function_exists('adminLogout')) {
    function adminLogout(string $message = ''): never {
        $flash = $message ?: null;
        session_unset();
        session_destroy();
        session_start();
        if ($flash) $_SESSION['login_error'] = $flash;
        header('Location: ' . getAdminBase() . '/login.php');
        exit;
    }
}

if (!function_exists('getAdminBase')) {
    function getAdminBase(): string {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $parts  = explode('/', trim($script, '/'));
        $idx    = array_search('admin', $parts);
        if ($idx !== false) {
            return '/' . implode('/', array_slice($parts, 0, $idx + 1));
        }
        return '/admin';
    }
}

if (!function_exists('getCurrentAdmin')) {
    function getCurrentAdmin(): array {
        return [
            'id'     => $_SESSION['admin_id']    ?? 0,
            'nom'    => $_SESSION['admin_nom']    ?? 'Admin',
            'prenom' => $_SESSION['admin_prenom'] ?? '',
            'email'  => $_SESSION['admin_email']  ?? '',
            'role'   => $_SESSION['admin_role']   ?? 'admin',
            'photo'  => $_SESSION['admin_photo']  ?? null,
        ];
    }
}

/* ── CSRF ── */

if (!function_exists('adminCsrfToken')) {
    function adminCsrfToken(): string {
        if (empty($_SESSION['admin_csrf'])) {
            $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['admin_csrf'];
    }
}

if (!function_exists('adminCheckCsrf')) {
    function adminCheckCsrf(): bool {
        $token = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        return hash_equals($_SESSION['admin_csrf'] ?? '', $token);
    }
}

/* ── Flash messages ── */

if (!function_exists('adminFlash')) {
    function adminFlash(string $type, string $msg): void {
        $_SESSION['admin_flash'] = ['type' => $type, 'msg' => $msg];
    }
}

if (!function_exists('adminGetFlash')) {
    function adminGetFlash(): ?array {
        if (isset($_SESSION['admin_flash'])) {
            $f = $_SESSION['admin_flash'];
            unset($_SESSION['admin_flash']);
            return $f;
        }
        return null;
    }
}

/* ── Utilitaires (déclarés ici seulement s'ils ne viennent pas de functions.php) ── */

if (!function_exists('fmt')) {
    function fmt(float $n, int $dec = 0): string {
        return number_format($n, $dec, ',', ' ');
    }
}

if (!function_exists('slugify')) {
    function slugify(string $str): string {
        $str = mb_strtolower(trim($str));
        $map = ['à'=>'a','â'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
                'î'=>'i','ï'=>'i','ô'=>'o','ö'=>'o','ù'=>'u','û'=>'u','ü'=>'u','ç'=>'c','ñ'=>'n'];
        $str = strtr($str, $map);
        $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
        $str = preg_replace('/[\s-]+/', '-', $str);
        return trim($str, '-');
    }
}

if (!function_exists('truncate')) {
    function truncate(string $text, int $len = 80): string {
        $text = strip_tags($text);
        if (mb_strlen($text) <= $len) return $text;
        return mb_substr($text, 0, $len) . '…';
    }
}

if (!function_exists('dateFr')) {
    function dateFr(?string $date, string $format = 'd/m/Y'): string {
        if (!$date) return '—';
        return date($format, strtotime($date));
    }
}

if (!function_exists('statusBadge')) {
    function statusBadge(string $status): string {
        $map = [
            'publie'     => ['Publié',     'success'],
            'brouillon'  => ['Brouillon',  'warning'],
            'archive'    => ['Archivé',    'secondary'],
            'actif'      => ['Actif',      'success'],
            'inactif'    => ['Inactif',    'secondary'],
            'en_attente' => ['En attente', 'warning'],
            'contacte'   => ['Contacté',   'info'],
            'accepte'    => ['Accepté',    'success'],
            'refuse'     => ['Refusé',     'danger'],
            'complete'   => ['Complété',   'success'],
            'echoue'     => ['Échoué',     'danger'],
            'rembourse'  => ['Remboursé',  'info'],
            'en_cours'   => ['En cours',   'info'],
            'approuve'   => ['Approuvé',   'success'],
            'soumis'     => ['Soumis',     'warning'],
            'a_venir'    => ['À venir',    'info'],
            'termine'    => ['Terminé',    'secondary'],
            'annule'     => ['Annulé',     'danger'],
            'desabonne'  => ['Désabonné',  'secondary'],
            'admin'      => ['Admin',      'danger'],
            'moderateur' => ['Modérateur', 'warning'],
            'membre'     => ['Membre',     'info'],
        ];
        [$label, $type] = $map[$status] ?? [ucfirst($status), 'secondary'];
        return "<span class=\"badge badge-{$type}\">{$label}</span>";
    }
}

if (!function_exists('uploadFile')) {
    function uploadFile(array $file, string $destination, array $allowedTypes = ['jpg','jpeg','png','gif','pdf']): array {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Erreur upload (code ' . $file['error'] . ')'];
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedTypes)) {
            return ['success' => false, 'error' => 'Type non autorisé : .' . $ext];
        }
        $maxSize = defined('MAX_FILE_SIZE') ? MAX_FILE_SIZE : 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'Fichier trop volumineux (max ' . round($maxSize/1024/1024) . ' Mo)'];
        }
        if (!is_dir($destination)) mkdir($destination, 0755, true);
        $filename = uniqid('', true) . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], rtrim($destination,'/') . '/' . $filename)) {
            return ['success' => true, 'filename' => $filename];
        }
        return ['success' => false, 'error' => 'Erreur de sauvegarde.'];
    }
}

if (!function_exists('logError')) {
    function logError(string $message): void {
        error_log('[GSCC CMS] ' . $message);
    }
}
