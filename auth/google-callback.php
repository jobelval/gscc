<?php
// auth/google-callback.php
// ============================================================
//  Reçoit le code d'autorisation de Google et connecte
//  ou crée automatiquement l'utilisateur.
//
//  URL à déclarer dans Google Cloud Console :
//  https://gscchaiti.org/auth/google-callback.php
// ============================================================

require_once '../includes/config.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// ── 1. Vérification du state CSRF ───────────────────────────
$state = $_GET['state'] ?? '';
if (empty($state) || $state !== ($_SESSION['oauth_state'] ?? '')) {
    die('Erreur de sécurité : state invalide.');
}
unset($_SESSION['oauth_state']);

// ── 2. Vérification du code retourné par Google ─────────────
$code = $_GET['code'] ?? '';
if (empty($code)) {
    redirect('../connexion.php?error=google_denied');
    exit;
}

// ── 3. Échange du code contre un access token ───────────────
$tokenResponse = _googleHttpPost('https://oauth2.googleapis.com/token', [
    'code'          => $code,
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code',
]);

if (!$tokenResponse || empty($tokenResponse['access_token'])) {
    redirect('../connexion.php?error=google_token');
    exit;
}

// ── 4. Récupération du profil Google de l'utilisateur ───────
$profile = _googleHttpGet(
    'https://www.googleapis.com/oauth2/v2/userinfo',
    $tokenResponse['access_token']
);

if (!$profile || empty($profile['email'])) {
    redirect('../connexion.php?error=google_profile');
    exit;
}

// ── 5. Connexion ou création du compte ──────────────────────
$result = loginOrCreateGoogleUser(
    $profile['email'],
    $profile['given_name']  ?? '',
    $profile['family_name'] ?? '',
    $profile['id']          ?? '',
    $profile['picture']     ?? ''
);

if ($result['success']) {
    redirect('../index.php');
} else {
    redirect('../connexion.php?error=google_account');
}
exit;


// ════════════════════════════════════════════════════════════
//  Fonctions cURL internes (privées à ce fichier)
// ════════════════════════════════════════════════════════════

function _googleHttpPost(string $url, array $data): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($data),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response ? json_decode($response, true) : null;
}

function _googleHttpGet(string $url, string $accessToken): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ["Authorization: Bearer $accessToken"],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response ? json_decode($response, true) : null;
}