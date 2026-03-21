<?php
/**
 * GSCC CMS — admin/parametres/test-email.php
 * Envoie un email de test via PHPMailer SMTP.
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

require_once ROOT_PATH . 'includes/mailer.php';

$admin = getCurrentAdmin();
$to    = $admin['email'];
$subj  = '[GSCC CMS] Test de configuration email';
$html  = "<!DOCTYPE html><html><body style='font-family:Arial;max-width:500px;margin:40px auto;'>
<div style='background:#003399;color:#fff;padding:20px;border-radius:10px 10px 0 0;text-align:center;'>
  <div style='font-size:28px;'>&#127385;</div>
  <strong>GSCC CMS — Test Email</strong>
</div>
<div style='background:#f9f9f9;padding:24px;border-radius:0 0 10px 10px;border:1px solid #eee;'>
  <h2 style='color:#003399;'>&#9989; Email de test envoy&eacute; avec succ&egrave;s !</h2>
  <p>Bonjour <strong>" . htmlspecialchars($admin['prenom']) . "</strong>,</p>
  <p>La configuration SMTP de votre CMS GSCC fonctionne correctement.</p>
  <p style='color:#888;font-size:12px;margin-top:24px;'>Envoy&eacute; le " . date('d/m/Y \à H:i') . " depuis " . SITE_URL . "</p>
</div></body></html>";

try {
    gsccMail($pdo, $to, $admin['prenom'] . ' ' . $admin['nom'], $subj, $html);
    adminFlash('success', "Email de test envoyé à $to via SMTP.");
} catch (\RuntimeException $e) {
    adminFlash('error', $e->getMessage());
}

header('Location: index.php?tab=smtp');
exit;
