<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
$admin = getCurrentAdmin();
$to    = $admin['email'];
$subj  = '[GSCC CMS] Test de configuration email';
$html  = "<!DOCTYPE html><html><body style='font-family:Arial;max-width:500px;margin:40px auto;'>
<div style='background:#003399;color:#fff;padding:20px;border-radius:10px 10px 0 0;text-align:center;'>
  <div style='font-size:28px;'>🎗️</div>
  <strong>GSCC CMS — Test Email</strong>
</div>
<div style='background:#f9f9f9;padding:24px;border-radius:0 0 10px 10px;border:1px solid #eee;'>
  <h2 style='color:#003399;'>✅ Email de test envoyé avec succès !</h2>
  <p>Bonjour <strong>" . htmlspecialchars($admin['prenom']) . "</strong>,</p>
  <p>La configuration email de votre CMS GSCC fonctionne correctement.</p>
  <p style='color:#888;font-size:12px;margin-top:24px;'>Envoyé le " . date('d/m/Y à H:i') . " depuis " . SITE_URL . "</p>
</div></body></html>";
$headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nFrom: GSCC CMS <" . SITE_EMAIL . ">\r\n";
$sent = @mail($to, $subj, $html, $headers);
adminFlash($sent ? 'success' : 'error', $sent ? "Email de test envoyé à $to" : "Échec de l'envoi. Vérifiez la configuration SMTP.");
header('Location: index.php?tab=smtp'); exit;