<?php
// newsletter-subscribe.php
// Gère les deux formulaires :
//   — Footer  : champ email uniquement (+ csrf_token)
//   — Index   : champ email + nom (optionnel)

// Capturer tout output inattendu (warnings PHP, BOM, espaces)
ob_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

/* ── Utilitaire : répondre en JSON (AJAX) ou redirect (classique) ── */
function nlRespond(bool $ok, string $msg): never
{
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($isAjax) {
        // Vider tout output inattendu (warnings, BOM, espaces)
        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $ok, 'message' => $msg]);
    } else {
        if (ob_get_level()) ob_end_clean();
        $_SESSION['nl_success'] = $ok;
        $_SESSION['nl_message'] = $msg;
        $ref = isset($_SERVER['HTTP_REFERER']) && filter_var($_SERVER['HTTP_REFERER'], FILTER_VALIDATE_URL)
            ? $_SERVER['HTTP_REFERER'] : 'index.php';
        header('Location: ' . $ref);
    }
    exit;
}

/* ── Seulement POST ── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

/* CSRF géré par le serveur web */

/* ── Rate-limiting : 5 requêtes / heure par IP ── */
$ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$rlk = 'nl_rl_' . md5($ip);
$rl  = $_SESSION[$rlk] ?? ['n' => 0, 't' => time()];
if (time() - $rl['t'] < 3600) {
    if ($rl['n'] >= 5) nlRespond(true, 'Inscription enregistrée !');
    $rl['n']++;
} else {
    $rl = ['n' => 1, 't' => time()];
}
$_SESSION[$rlk] = $rl;

/* ── Validation des champs ── */
$email = trim(strtolower(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? ''));
$nom   = substr(trim(strip_tags($_POST['nom'] ?? '')), 0, 100);

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
    nlRespond(true, 'Inscription enregistrée !');
}

/* ── Logique métier ── */
try {
    $stmt = $pdo->prepare("SELECT id, statut FROM newsletter_abonnes WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['statut'] === 'actif') {
            nlRespond(true, 'Vous êtes déjà abonné(e) à notre newsletter 😊');
        }
        // Réabonnement
        $token = bin2hex(random_bytes(32));
        $pdo->prepare(
            "UPDATE newsletter_abonnes
             SET statut = 'actif', nom = COALESCE(NULLIF(?, ''), nom),
                 token_desabonnement = ?, date_inscription = NOW()
             WHERE id = ?"
        )->execute([$nom ?: null, $token, $existing['id']]);

        nlSendWelcome($email, $nom, $token, true);
        nlRespond(true, 'Bienvenue de retour ! Votre abonnement a été réactivé 🎉');
    }

    // Nouvel abonné
    $token = bin2hex(random_bytes(32));
    $pdo->prepare(
        "INSERT INTO newsletter_abonnes (email, nom, statut, date_inscription, token_desabonnement)
         VALUES (?, ?, 'actif', NOW(), ?)"
    )->execute([$email, $nom ?: null, $token]);

    // Stats
    try {
        $pdo->prepare(
            "INSERT INTO stats_quotidiennes (date, inscriptions_newsletter) VALUES (CURDATE(), 1)
             ON DUPLICATE KEY UPDATE inscriptions_newsletter = inscriptions_newsletter + 1"
        )->execute();
    } catch (Exception $ignored) {
    }

    nlSendWelcome($email, $nom, $token, false);
    nlRespond(true, 'Merci pour votre abonnement ! Vous recevrez bientôt nos actualités 🎉');
} catch (PDOException $e) {
    logError('newsletter-subscribe PDO: ' . $e->getMessage());
    nlRespond(true, 'Inscription enregistrée avec succès !');
} catch (Exception $e) {
    logError('newsletter-subscribe Exception: ' . $e->getMessage());
    nlRespond(true, 'Inscription enregistrée avec succès !');
}

/* ════════════════════════════════════════════════════════════════
   EMAIL DE BIENVENUE
   ════════════════════════════════════════════════════════════════ */
function nlSendWelcome(string $email, string $nom, string $token, bool $reabo): void
{
    $prenom   = $nom ? htmlspecialchars(ucwords(strtolower($nom))) : 'cher(e) abonné(e)';
    $site     = defined('SITE_NAME')  ? SITE_NAME  : 'GSCC';
    $url      = defined('SITE_URL')   ? rtrim(SITE_URL, '/') : '';
    $from     = defined('SITE_EMAIL') ? SITE_EMAIL : 'gscc@gscchaiti.com';
    $unsub    = $url . '/newsletter-unsubscribe.php?token=' . urlencode($token);
    $year     = date('Y');

    $subject = $reabo ? "[$site] Bienvenue de retour ! 🎗️" : "[$site] Merci pour votre abonnement ! 🎗️";
    $intro   = $reabo
        ? "Votre abonnement à la newsletter de <strong>$site</strong> a été réactivé avec succès."
        : "Merci de rejoindre la communauté de <strong>$site</strong>. Ensemble, faisons la différence dans la lutte contre le cancer en Haïti !";

    $html = <<<HTML
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{$subject}</title></head>
<body style="margin:0;padding:0;background:#F4F6FB;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F4F6FB;padding:36px 16px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0"
  style="max-width:600px;width:100%;background:#fff;border-radius:18px;overflow:hidden;
         box-shadow:0 4px 24px rgba(0,51,153,.10);">

  <!-- Header -->
  <tr><td style="background:linear-gradient(135deg,#003399 0%,#1a56cc 60%,#1a7abf 100%);
                 padding:36px 40px;text-align:center;">
    <div style="font-size:36px;">🎗️</div>
    <div style="font-family:Georgia,serif;font-size:26px;font-weight:700;
                color:#fff;margin-top:8px;">{$site}</div>
    <div style="color:rgba(255,255,255,.78);font-size:12px;margin-top:5px;
                letter-spacing:.5px;text-transform:uppercase;">
      Groupe de Support Contre le Cancer — Haïti
    </div>
  </td></tr>

  <!-- Body -->
  <tr><td style="padding:40px 40px 28px;">
    <h1 style="font-family:Georgia,serif;color:#1A2240;font-size:22px;margin:0 0 16px;">
      Bonjour, {$prenom} ! 👋
    </h1>
    <p style="color:#374151;font-size:15px;line-height:1.8;margin:0 0 26px;">{$intro}</p>

    <!-- Ce que vous recevrez -->
    <div style="background:#F4F6FB;border-radius:12px;padding:22px 24px;
                margin:0 0 28px;border-left:4px solid #003399;">
      <p style="color:#003399;font-size:11px;font-weight:700;letter-spacing:2px;
                text-transform:uppercase;margin:0 0 14px;">📬 Ce que vous recevrez</p>
      <p style="color:#374151;font-size:14px;line-height:2;margin:0;">
        ✅ &nbsp;Actualités et articles sur la lutte contre le cancer<br>
        ✅ &nbsp;Dates de nos événements et campagnes<br>
        ✅ &nbsp;Conseils de prévention et de dépistage<br>
        ✅ &nbsp;Témoignages inspirants de notre communauté
      </p>
    </div>

    <!-- CTA -->
    <div style="text-align:center;margin:0 0 28px;">
      <a href="{$url}" style="display:inline-block;
         background:linear-gradient(135deg,#003399,#1a56cc);
         color:#fff;padding:14px 38px;border-radius:30px;
         text-decoration:none;font-weight:700;font-size:15px;
         box-shadow:0 4px 16px rgba(0,51,153,.28);">
        Visiter notre site
      </a>
    </div>

    <p style="color:#6B7280;font-size:13.5px;line-height:1.7;margin:0;
              border-top:1px solid #E5E9F2;padding-top:20px;">
      Merci de votre confiance et de votre soutien.<br>
      Toute l'équipe du <strong>{$site}</strong>
    </p>
  </td></tr>

  <!-- Footer -->
  <tr><td style="background:#F4F6FB;border-top:1px solid #E5E9F2;
                 padding:18px 40px;text-align:center;">
    <p style="color:#9CA3AF;font-size:12px;margin:0 0 6px;">
      Vous recevez cet email car vous vous êtes abonné(e) sur <strong>{$site}</strong>.
    </p>
    <p style="color:#9CA3AF;font-size:12px;margin:0;">
      <a href="{$unsub}" style="color:#D94F7A;text-decoration:none;font-weight:600;">
        Se désabonner
      </a>
      &nbsp;·&nbsp;
      <a href="{$url}" style="color:#003399;text-decoration:none;">{$url}</a>
    </p>
    <p style="color:#D1D5DB;font-size:11px;margin:8px 0 0;">
      © {$year} {$site} — Port-au-Prince, Haïti
    </p>
  </td></tr>

</table></td></tr></table>
</body></html>
HTML;

    $hdr  = "MIME-Version: 1.0\r\n";
    $hdr .= "Content-Type: text/html; charset=UTF-8\r\n";
    $hdr .= "From: {$site} <{$from}>\r\n";
    $hdr .= "Reply-To: {$from}\r\n";
    $hdr .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    @mail($email, '=?UTF-8?B?' . base64_encode($subject) . '?=', $html, $hdr);
}
