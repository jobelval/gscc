<?php
/**
 * GSCC CMS — includes/mailer.php
 * Helper d'envoi email via PHPMailer + Gmail SMTP (smtp_config.php).
 * Utilisation : gsccMail($pdo, $to, $toName, $subject, $htmlBody)
 */

require_once ROOT_PATH . 'includes/smtp_config.php';
require_once ROOT_PATH . 'vendor/PHPMailer/Exception.php';
require_once ROOT_PATH . 'vendor/PHPMailer/PHPMailer.php';
require_once ROOT_PATH . 'vendor/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

/**
 * Envoi groupé avec une seule connexion SMTP ouverte (SMTPKeepAlive).
 * Retourne ['sent' => int, 'failed' => int, 'errors' => array]
 *
 * @param PDO    $pdo
 * @param array  $recipients  [ ['email'=>'...', 'nom'=>'...'], ... ]
 * @param string $subject
 * @param callable $buildHtml  fn(string $prenom, string $unsub_url): string
 */
function gsccBulkSend(PDO $pdo, array $recipients, string $subject, callable $buildHtml): array
{
    require_once ROOT_PATH . 'includes/smtp_config.php';

    set_time_limit(0); // Pas de timeout PHP pour les gros envois

    $fromEmail = 'gscchaiti.contact@gmail.com';
    $fromName  = defined('SITE_NAME') ? SITE_NAME : 'GSCC';
    $sent = 0; $failed = 0; $errors = [];

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host         = 'smtp.gmail.com';
    $mail->SMTPAuth     = true;
    $mail->Username     = $fromEmail;
    $mail->Password     = GMAIL_APP_PASSWORD;
    $mail->SMTPSecure   = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port         = 587;
    $mail->CharSet      = 'UTF-8';
    $mail->SMTPKeepAlive = true; // ← connexion réutilisée pour tous les emails
    $mail->setFrom($fromEmail, $fromName);
    $mail->isHTML(true);
    $mail->Subject = $subject;

    $siteUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';

    foreach ($recipients as $rec) {
        $email  = $rec['email'] ?? '';
        $nom    = $rec['nom']   ?? '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $failed++; continue; }

        // Lien désabonnement
        try {
            $stmt = $pdo->prepare("SELECT token_desabonnement FROM newsletter_abonnes WHERE email=? LIMIT 1");
            $stmt->execute([$email]);
            $row  = $stmt->fetch();
            $token = $row['token_desabonnement'] ?? bin2hex(random_bytes(16));
        } catch (\Exception) { $token = bin2hex(random_bytes(16)); }
        $unsubUrl = $siteUrl . '/newsletter-unsubscribe.php?token=' . urlencode($token);

        $prenom = $nom ? htmlspecialchars(ucwords(strtolower($nom))) : 'cher(e) abonné(e)';
        $html   = $buildHtml($prenom, $unsubUrl);

        try {
            $mail->clearAddresses();
            $mail->addAddress($email, $nom);
            $mail->Body    = $html;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html));
            $mail->send();
            $sent++;
        } catch (MailerException) {
            $errors[] = $email . ' : ' . $mail->ErrorInfo;
            $failed++;
        }

        usleep(300000); // 300ms entre chaque email — respecte les limites Gmail
    }

    $mail->smtpClose(); // Ferme proprement la connexion SMTP
    return ['sent' => $sent, 'failed' => $failed, 'errors' => $errors];
}

/**
 * Envoie un email HTML via PHPMailer + Gmail SMTP.
 *
 * @param PDO    $pdo       Connexion BDD (conservé pour compatibilité)
 * @param string $to        Adresse destinataire
 * @param string $toName    Nom destinataire (peut être vide)
 * @param string $subject   Sujet de l'email
 * @param string $htmlBody  Corps HTML de l'email
 * @throws \RuntimeException si l'envoi échoue
 */
function gsccMail(PDO $pdo, string $to, string $toName, string $subject, string $htmlBody): void
{
    $fromEmail = 'gscchaiti.contact@gmail.com';
    $fromName  = defined('SITE_NAME') ? SITE_NAME : 'GSCC';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $fromEmail;
        $mail->Password   = GMAIL_APP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($fromEmail, $fromName);
        $mail->addReplyTo($fromEmail, $fromName);
        $mail->addAddress($to, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        $mail->send();

    } catch (MailerException) {
        throw new \RuntimeException('Erreur SMTP : ' . $mail->ErrorInfo);
    }
}
