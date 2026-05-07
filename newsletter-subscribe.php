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

/* ── Vérification CSRF ── */
$csrfToken = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
$sessionToken = $_SESSION[CSRF_TOKEN_NAME] ?? '';
if ($sessionToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    nlRespond(false, 'Requête invalide. Veuillez recharger la page et réessayer.');
}

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
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{$subject}</title>
<!--[if mso]>
<xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch><o:AllowPNG/></o:OfficeDocumentSettings></xml>
<![endif]-->
<style>
  body,table,td{margin:0;padding:0;}
  img{display:block;border:0;}
  table{border-collapse:collapse;}
  @media only screen and (max-width:620px){
    .wrap{width:100%!important;}
    .body-pad{padding:28px 20px!important;}
    .head-pad{padding:32px 20px 24px!important;}
  }
</style>
</head>
<body style="margin:0;padding:0;background:#FFF0F7;font-family:Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#FFF0F7" style="background:#FFF0F7;">
<tr><td align="center" style="padding:36px 16px;">

  <!-- Carte principale -->
  <table class="wrap" width="600" cellpadding="0" cellspacing="0" border="0"
    style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(217,79,122,.12);">

    <!-- HEADER : bgcolor pour Outlook, gradient pour les autres -->
    <tr>
      <td class="head-pad" align="center" bgcolor="#D94F7A"
        style="background:linear-gradient(135deg,#D94F7A 0%,#FF69B4 100%);padding:40px 40px 32px;">
        <p style="font-size:36px;line-height:1;margin:0;padding:0;">🎗️</p>
        <h1 style="font-family:Georgia,'Times New Roman',serif;font-size:22px;font-weight:700;
                   color:#ffffff;margin:10px 0 6px;padding:0;">
          {$site} — Groupe de Support Contre le Cancer
        </h1>
        <p style="color:rgba(255,255,255,.85);font-size:12px;margin:0;padding:0;
                  letter-spacing:.8px;text-transform:uppercase;">Haïti</p>
      </td>
    </tr>

    <!-- BODY -->
    <tr>
      <td class="body-pad" style="padding:40px 40px 32px;">
        <h2 style="font-family:Georgia,'Times New Roman',serif;color:#1A1A2E;font-size:20px;
                   font-weight:700;margin:0 0 16px;padding:0;">
          Bonjour, {$prenom} ! 👋
        </h2>
        <p style="color:#374151;font-size:15px;line-height:1.8;margin:0 0 26px;padding:0;">
          {$intro}
        </p>

        <!-- Encadré "Ce que vous recevrez" -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td bgcolor="#FFF0F7"
              style="background:#FFF0F7;border-radius:10px;padding:20px 22px;border-left:4px solid #D94F7A;">
              <p style="color:#D94F7A;font-size:11px;font-weight:700;letter-spacing:2px;
                        text-transform:uppercase;margin:0 0 12px;padding:0;">📬 Ce que vous recevrez</p>
              <p style="color:#374151;font-size:14px;line-height:2;margin:0;padding:0;">
                ✅ &nbsp;Actualités et articles sur la lutte contre le cancer<br>
                ✅ &nbsp;Dates de nos événements et campagnes<br>
                ✅ &nbsp;Conseils de prévention et de dépistage<br>
                ✅ &nbsp;Témoignages inspirants de notre communauté
              </p>
            </td>
          </tr>
        </table>

        <!-- Bouton CTA : VML pour Outlook, <a> pour les autres -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td align="center" style="padding:28px 0 0;">
              <!--[if mso]>
              <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="{$url}"
                style="height:46px;v-text-anchor:middle;width:200px;"
                arcsize="50%" stroke="f" fillcolor="#D94F7A">
                <w:anchorlock/>
                <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:15px;font-weight:700;">
                  Visiter notre site
                </center>
              </v:roundrect>
              <![endif]-->
              <!--[if !mso]><!-->
              <a href="{$url}"
                style="display:inline-block;background:#D94F7A;color:#ffffff;padding:14px 38px;
                       border-radius:30px;text-decoration:none;font-weight:700;font-size:15px;
                       font-family:Arial,Helvetica,sans-serif;">
                Visiter notre site
              </a>
              <!--<![endif]-->
            </td>
          </tr>
        </table>

        <!-- Signature -->
        <p style="color:#6B7280;font-size:13.5px;line-height:1.7;margin:28px 0 0;padding-top:20px;border-top:1px solid #F3E5EB;">
          Merci de votre confiance et de votre soutien.<br>
          Toute l'équipe du <strong>{$site}</strong>
        </p>
      </td>
    </tr>

    <!-- FOOTER -->
    <tr>
      <td bgcolor="#FFF0F7" align="center"
        style="background:#FFF0F7;border-top:1px solid #F3E5EB;padding:18px 40px;">
        <p style="color:#9CA3AF;font-size:12px;margin:0 0 6px;padding:0;">
          Vous recevez cet email car vous vous êtes abonné(e) sur <strong>{$site}</strong>.
        </p>
        <p style="color:#9CA3AF;font-size:12px;margin:0;padding:0;">
          <a href="{$unsub}" style="color:#D94F7A;text-decoration:none;font-weight:600;">Se désabonner</a>
          &nbsp;·&nbsp; © {$year} {$site} — Port-au-Prince, Haïti
        </p>
      </td>
    </tr>

  </table>

</td></tr></table>
</body></html>
HTML;

    $hdr  = "MIME-Version: 1.0\r\n";
    $hdr .= "Content-Type: text/html; charset=UTF-8\r\n";
    $hdr .= "From: {$site} <{$from}>\r\n";
    $hdr .= "Reply-To: {$from}\r\n";
    $hdr .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    @mail($email, '=?UTF-8?B?' . base64_encode($subject) . '?=', $html, $hdr);
}
