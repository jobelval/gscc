<?php
// newsletter-unsubscribe.php
// Désabonnement via le lien présent dans chaque email.
// URL : newsletter-unsubscribe.php?token=XXXX

require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$token  = trim($_GET['token'] ?? '');
$status = 'invalid'; // invalid | already | success
$email  = '';

if ($token !== '') {
    try {
        $stmt = $pdo->prepare(
            "SELECT id, email, statut FROM newsletter_abonnes
             WHERE token_desabonnement = ? LIMIT 1"
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch();

        if ($row) {
            $email = $row['email'];
            if ($row['statut'] === 'desabonne') {
                $status = 'already';
            } else {
                $pdo->prepare(
                    "UPDATE newsletter_abonnes SET statut = 'desabonne' WHERE id = ?"
                )->execute([$row['id']]);
                $status = 'success';

                // Email de confirmation de désabonnement
                nlSendGoodbye(
                    $email,
                    defined('SITE_NAME') ? SITE_NAME : 'GSCC',
                    defined('SITE_URL')  ? rtrim(SITE_URL, '/') : '',
                    defined('SITE_EMAIL') ? SITE_EMAIL : 'gscc@gscchaiti.com'
                );
            }
        }
    } catch (PDOException $e) {
        logError('newsletter-unsubscribe PDO: ' . $e->getMessage());
        $status = 'error';
    }
}

/* ── Traitement du formulaire de réabonnement ── */
$reabo_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($status === 'success' || $status === 'already')) {
    // L'utilisateur regrette et veut se réabonner
    try {
        $pdo->prepare(
            "UPDATE newsletter_abonnes SET statut = 'actif', date_inscription = NOW()
             WHERE token_desabonnement = ?"
        )->execute([$token]);
        $reabo_msg = 'ok';
    } catch (Exception $e) {
        $reabo_msg = 'error';
    }
}

$site = defined('SITE_NAME') ? SITE_NAME : 'GSCC';
$url  = defined('SITE_URL')  ? rtrim(SITE_URL, '/') : '';

/* ════════════════════════════════════════════════════════════════
   EMAIL DE CONFIRMATION DE DÉSABONNEMENT
   ════════════════════════════════════════════════════════════════ */
function nlSendGoodbye(string $email, string $site, string $url, string $from): void
{
    $subject = "[$site] Vous êtes désabonné(e)";
    $year    = date('Y');

    $html = <<<HTML
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#F4F6FB;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F4F6FB;padding:36px 16px;">
<tr><td align="center">
<table width="560" cellpadding="0" cellspacing="0"
  style="max-width:560px;width:100%;background:#fff;border-radius:18px;overflow:hidden;
         box-shadow:0 4px 20px rgba(0,0,0,.08);">
  <tr><td style="background:linear-gradient(135deg,#003399,#1a56cc);
                 padding:30px 36px;text-align:center;">
    <div style="font-size:32px;">🎗️</div>
    <div style="font-family:Georgia,serif;font-size:22px;font-weight:700;
                color:#fff;margin-top:8px;">{$site}</div>
  </td></tr>
  <tr><td style="padding:36px 36px 28px;">
    <h2 style="font-family:Georgia,serif;color:#1A2240;font-size:20px;margin:0 0 14px;">
      Désabonnement confirmé
    </h2>
    <p style="color:#374151;font-size:14.5px;line-height:1.8;margin:0 0 20px;">
      Votre adresse email a bien été retirée de notre liste d'envoi.<br>
      Vous ne recevrez plus nos newsletters.
    </p>
    <p style="color:#6B7280;font-size:13.5px;line-height:1.7;margin:0 0 24px;">
      Si vous avez effectué cette démarche par erreur, vous pouvez vous réabonner
      à tout moment depuis notre site.
    </p>
    <div style="text-align:center;">
      <a href="{$url}" style="display:inline-block;background:#003399;
         color:#fff;padding:12px 30px;border-radius:30px;
         text-decoration:none;font-weight:700;font-size:14px;">
        Retour au site
      </a>
    </div>
  </td></tr>
  <tr><td style="background:#F4F6FB;border-top:1px solid #E5E9F2;
                 padding:16px 36px;text-align:center;">
    <p style="color:#D1D5DB;font-size:11px;margin:0;">
      © {$year} {$site} — Port-au-Prince, Haïti
    </p>
  </td></tr>
</table></td></tr></table>
</body></html>
HTML;

    $hdr  = "MIME-Version: 1.0\r\n";
    $hdr .= "Content-Type: text/html; charset=UTF-8\r\n";
    $hdr .= "From: {$site} <{$from}>\r\n";
    @mail($email, '=?UTF-8?B?' . base64_encode($subject) . '?=', $html, $hdr);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Désabonnement — <?= htmlspecialchars($site) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --blue: #003399;
            --rose: #D94F7A;
            --dark: #1A2240;
            --gray: #6B7280;
            --bg: #F4F6FB;
            --border: #E5E9F2;
        }

        body {
            background: var(--bg);
        }

        .unsub-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }

        .unsub-card {
            background: #fff;
            border-radius: 20px;
            padding: 52px 44px;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 8px 40px rgba(0, 51, 153, .10);
            border: 1px solid var(--border);
        }

        .unsub-icon {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 34px;
        }

        .icon-success {
            background: rgba(46, 125, 50, .10);
        }

        .icon-already {
            background: rgba(245, 158, 11, .10);
        }

        .icon-invalid {
            background: rgba(220, 38, 38, .08);
        }

        .icon-error {
            background: rgba(107, 114, 128, .10);
        }

        .unsub-card h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.7rem;
            color: var(--dark);
            margin-bottom: 14px;
        }

        .unsub-card p {
            color: var(--gray);
            font-size: 15px;
            line-height: 1.75;
            margin-bottom: 16px;
        }

        .unsub-card .email-badge {
            display: inline-block;
            background: var(--bg);
            border: 1px solid var(--border);
            padding: 7px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 24px;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--blue), #1a56cc);
            color: #fff;
            padding: 13px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(0, 51, 153, .24);
            transition: transform .2s, box-shadow .2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 51, 153, .30);
        }

        .btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            color: var(--gray);
            padding: 11px 24px;
            border-radius: 30px;
            border: 1.5px solid var(--border);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all .2s;
            margin-left: 10px;
        }

        .btn-ghost:hover {
            background: var(--bg);
            color: var(--dark);
        }

        .reabo-success {
            background: #F0FDF4;
            border: 1px solid #BBF7D0;
            color: #166534;
            border-radius: 10px;
            padding: 14px 18px;
            font-size: 14px;
            margin-top: 20px;
            display: flex;
            align-items: center;
            gap: 9px;
            justify-content: center;
        }

        .divider {
            height: 1px;
            background: var(--border);
            margin: 28px 0;
        }

        .site-link {
            color: var(--blue);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .site-link:hover {
            color: var(--rose);
        }
    </style>
</head>

<body>
    <?php include 'templates/header.php'; ?>

    <div class="unsub-wrap">
        <div class="unsub-card">

            <?php if ($reabo_msg === 'ok'): ?>
                <!-- Réabonnement réussi -->
                <div class="unsub-icon icon-success">✅</div>
                <h1>Vous êtes de retour !</h1>
                <p>Vous êtes de nouveau abonné(e) à notre newsletter. Merci de rester avec nous !</p>
                <a href="<?= htmlspecialchars($url) ?>" class="btn-primary">
                    <i class="fas fa-home"></i> Retour à l'accueil
                </a>

            <?php elseif ($status === 'success'): ?>
                <!-- Désabonnement réussi -->
                <div class="unsub-icon icon-success">👋</div>
                <h1>Désabonnement confirmé</h1>
                <?php if ($email): ?>
                    <div class="email-badge"><i class="far fa-envelope"></i> <?= htmlspecialchars($email) ?></div>
                <?php endif; ?>
                <p>Vous avez bien été retiré(e) de notre liste de diffusion. Vous ne recevrez plus nos emails.</p>
                <p>Nous espérons vous revoir bientôt parmi nous !</p>

                <div class="divider"></div>
                <p style="font-size:13.5px;">Désabonné(e) par erreur ?</p>
                <form method="POST">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-redo"></i> Me réabonner
                    </button>
                    <a href="<?= htmlspecialchars($url) ?>" class="btn-ghost">
                        <i class="fas fa-home"></i> Accueil
                    </a>
                </form>

            <?php elseif ($status === 'already'): ?>
                <!-- Déjà désabonné -->
                <div class="unsub-icon icon-already">ℹ️</div>
                <h1>Déjà désabonné(e)</h1>
                <p>Cette adresse email est déjà retirée de notre liste de diffusion.</p>
                <p style="font-size:13.5px;">Vous souhaitez vous réabonner ?</p>
                <form method="POST">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-envelope"></i> Me réabonner
                    </button>
                    <a href="<?= htmlspecialchars($url) ?>" class="btn-ghost">Retour</a>
                </form>

            <?php elseif ($status === 'error'): ?>
                <!-- Erreur serveur -->
                <div class="unsub-icon icon-error">⚠️</div>
                <h1>Erreur</h1>
                <p>Une erreur est survenue. Veuillez réessayer dans quelques instants ou contacter notre équipe.</p>
                <a href="contact.php" class="btn-primary"><i class="fas fa-envelope"></i> Nous contacter</a>

            <?php else: ?>
                <!-- Lien invalide -->
                <div class="unsub-icon icon-invalid">❌</div>
                <h1>Lien invalide</h1>
                <p>Ce lien de désabonnement est invalide ou a expiré. Il est possible que vous ayez déjà utilisé ce lien.</p>
                <a href="<?= htmlspecialchars($url) ?>" class="btn-primary">
                    <i class="fas fa-home"></i> Retour à l'accueil
                </a>
            <?php endif; ?>

            <div class="divider"></div>
            <a href="<?= htmlspecialchars($url) ?>" class="site-link">
                <i class="fas fa-ribbon"></i> <?= htmlspecialchars($site) ?>
            </a>

        </div>
    </div>

    <?php include 'templates/footer.php'; ?>
</body>

</html>