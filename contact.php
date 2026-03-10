<?php
// contact.php
require_once 'includes/config.php';
require_once 'includes/smtp_config.php'; // Config SMTP Gmail

$page_title = 'Contactez-nous';
$page_description = 'Prenez contact avec l\'équipe du GSCC pour toute question, demande d\'aide ou proposition de partenariat.';

$success = '';
$error = '';

// Email destinataire pour les notifications
define('ADMIN_NOTIFY_EMAIL', 'belvaljohnsley@gmail.com');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $data = [
            'nom'       => trim(strip_tags($_POST['nom']       ?? '')),
            'email'     => trim(strip_tags($_POST['email']     ?? '')),
            'telephone' => trim(strip_tags($_POST['telephone'] ?? '')),
            'sujet'     => trim(strip_tags($_POST['sujet']     ?? '')),
            'message'   => trim(strip_tags($_POST['message']   ?? ''))
        ];

        // ── Validation ──────────────────────────────────────────────
        if (empty($data['nom'])) {
            $error = 'Veuillez entrer votre nom.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'Veuillez entrer un email valide.';
        } elseif (!empty($data['telephone']) && !preg_match('/^[0-9+\s\-().]{7,20}$/', $data['telephone'])) {
            $error = 'Le numéro de téléphone est invalide. Utilisez uniquement des chiffres, +, espaces ou tirets.';
        } elseif (empty($data['message'])) {
            $error = 'Veuillez entrer votre message.';
        } else {
            $result = addContactMessage($data);
            if ($result['success']) {
                $success = 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.';

                // ── Notification email à l'administrateur ──────────────
                $sujet_email   = '[GSCC Contact] ' . (!empty($data['sujet']) ? $data['sujet'] : 'Nouveau message');
                $tel_affiche   = !empty($data['telephone']) ? htmlspecialchars($data['telephone']) : 'Non renseigné';
                $date_envoi    = date('d/m/Y à H:i');

                $corps_html = '
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f3f4f6;margin:0;padding:20px;">
  <div style="max-width:600px;margin:0 auto;background:white;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.1);">
    <div style="background:linear-gradient(135deg,#003399,#D94F7A);padding:28px 32px;text-align:center;">
      <h2 style="color:white;margin:0;font-size:1.4rem;">📬 Nouveau message de contact</h2>
      <p style="color:rgba(255,255,255,.85);margin:8px 0 0;font-size:0.9rem;">Reçu le ' . $date_envoi . '</p>
    </div>
    <div style="padding:32px;">
      <table style="width:100%;border-collapse:collapse;">
        <tr>
          <td style="padding:10px 0;border-bottom:1px solid #E5E7EB;width:140px;">
            <strong style="color:#003399;">👤 Nom</strong>
          </td>
          <td style="padding:10px 0;border-bottom:1px solid #E5E7EB;color:#1F2937;">
            ' . htmlspecialchars($data['nom']) . '
          </td>
        </tr>
        <tr>
          <td style="padding:10px 0;border-bottom:1px solid #E5E7EB;">
            <strong style="color:#003399;">✉️ Email</strong>
          </td>
          <td style="padding:10px 0;border-bottom:1px solid #E5E7EB;color:#1F2937;">
            <a href="mailto:' . htmlspecialchars($data['email']) . '" style="color:#D94F7A;">' . htmlspecialchars($data['email']) . '</a>
          </td>
        </tr>
        <tr>
          <td style="padding:10px 0;border-bottom:1px solid #E5E7EB;">
            <strong style="color:#003399;">📞 Téléphone</strong>
          </td>
          <td style="padding:10px 0;border-bottom:1px solid #E5E7EB;color:#1F2937;">' . $tel_affiche . '</td>
        </tr>
        <tr>
          <td style="padding:10px 0;border-bottom:1px solid #E5E7EB;">
            <strong style="color:#003399;">📌 Sujet</strong>
          </td>
          <td style="padding:10px 0;border-bottom:1px solid #E5E7EB;color:#1F2937;">
            ' . (!empty($data['sujet']) ? htmlspecialchars($data['sujet']) : '<em style="color:#9CA3AF;">Non renseigné</em>') . '
          </td>
        </tr>
      </table>

      <div style="margin-top:24px;">
        <p style="color:#003399;font-weight:700;margin-bottom:10px;">💬 Message</p>
        <div style="background:#F9FAFB;border-left:4px solid #D94F7A;padding:16px 20px;border-radius:0 8px 8px 0;color:#374151;line-height:1.7;">
          ' . nl2br(htmlspecialchars($data['message'])) . '
        </div>
      </div>

      <div style="margin-top:28px;text-align:center;">
        <a href="mailto:' . htmlspecialchars($data['email']) . '?subject=Re: ' . rawurlencode(!empty($data['sujet']) ? $data['sujet'] : 'Votre message') . '"
           style="display:inline-block;background:#003399;color:white;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.95rem;">
          ↩ Répondre à ' . htmlspecialchars($data['nom']) . '
        </a>
      </div>
    </div>
    <div style="background:#F3F4F6;padding:16px 32px;text-align:center;font-size:12px;color:#6B7280;">
      © ' . date('Y') . ' GSCC — Groupe de Support Contre le Cancer
    </div>
  </div>
</body>
</html>';

                // -- Envoi via PHPMailer + Gmail SMTP -------------------
                try {
                    require_once __DIR__ . '/vendor/PHPMailer/PHPMailer.php';
                    require_once __DIR__ . '/vendor/PHPMailer/SMTP.php';
                    require_once __DIR__ . '/vendor/PHPMailer/Exception.php';

                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'belvaljohnsley@gmail.com';
                    $mail->Password   = GMAIL_APP_PASSWORD;
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom('belvaljohnsley@gmail.com', 'GSCC Site Web');
                    $mail->addAddress(ADMIN_NOTIFY_EMAIL, 'GSCC Admin');
                    $mail->addReplyTo($data['email'], $data['nom']);

                    $mail->isHTML(true);
                    $mail->Subject = $sujet_email;
                    $mail->Body    = $corps_html;

                    $mail->send();
                } catch (Exception $e) {
                    logError('PHPMailer erreur: ' . $e->getMessage());
                }

                // Vider le formulaire
                $_POST = [];
            } else {
                $error = $result['error'];
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> - <?= SITE_NAME ?></title>
    <meta name="description" content="<?= e($page_description) ?>">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --rose: #D94F7A;
            --teal: #1a7abf;
            --blue: #003399;
        }

        .page-header {
            background: linear-gradient(135deg, var(--rose), var(--teal));
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .page-header h1 {
            font-size: 2.4rem;
            font-weight: 800;
            color: #FFFFFF;
            margin-bottom: 12px;
            text-shadow: 0 1px 3px rgba(0, 0, 0, .3);
        }

        .page-header p {
            font-size: 1.1rem;
            color: #F0F4FF;
            font-weight: 400;
        }

        .contact-section {
            padding: 80px 0;
            background: #F3F4F6;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }

        .contact-info {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .07);
            border: 1px solid #D1D5DB;
        }

        .contact-info h3 {
            color: #003399;
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
        }

        .contact-info h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: #003399;
            border-radius: 2px;
        }

        .info-item {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: #F3F4F6;
            border-radius: 10px;
            border: 1px solid #D1D5DB;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            transform: translateX(10px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .info-icon {
            width: 50px;
            height: 50px;
            background: #003399;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            flex-shrink: 0;
        }

        .info-content h4 {
            color: #0D1117;
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 16px;
        }

        .info-content p {
            color: #1F2937;
            font-weight: 400;
            line-height: 1.7;
        }

        .info-content a {
            color: #003399;
            text-decoration: none;
            font-weight: 500;
        }

        .info-content a:hover {
            text-decoration: underline;
        }

        .social-contact {
            margin-top: 40px;
        }

        .social-contact h4 {
            color: #0D1117;
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .social-links-contact {
            display: flex;
            gap: 15px;
        }

        .social-links-contact a {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #E5E7EB;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1F2937;
            font-size: 20px;
            transition: all 0.3s ease;
        }

        .social-links-contact a:hover {
            background: #003399;
            color: white;
            transform: translateY(-3px);
        }

        .contact-form {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .07);
            border: 1px solid #D1D5DB;
        }

        .contact-form h3 {
            color: #003399;
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
        }

        .contact-form h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: #003399;
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #D1D5DB;
            border-radius: 10px;
            font-size: 15px;
            color: #0D1117;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #003399;
            box-shadow: 0 0 0 3px rgba(0, 51, 153, 0.1);
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--rose), var(--teal));
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 51, 153, 0.3);
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #D1FAE5;
            color: #065F46;
            border: 1.5px solid #6EE7B7;
            font-weight: 500;
        }

        .alert-error {
            background: #FEE2E2;
            color: #991B1B;
            border: 1.5px solid #FCA5A5;
            font-weight: 500;
        }

        .map-section {
            padding: 0 0 80px 0;
        }

        .map-container {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include 'templates/header.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 data-aos="fade-up">Contactez-nous</h1>
            <p data-aos="fade-up" data-aos-delay="100">
                Nous sommes à votre écoute pour répondre à toutes vos questions
            </p>
        </div>
    </div>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="contact-grid">
                <!-- Informations de contact -->
                <div class="contact-info" data-aos="fade-right">
                    <h3>Nos coordonnées</h3>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <h4>Adresse</h4>
                            <p>Port-au-Prince, Haïti<br>Bureau principal</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="info-content">
                            <h4>Téléphone</h4>
                            <p><a href="tel:+50929474722">+509 2947 4722</a></p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h4>Email</h4>
                            <p><a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a></p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <h4>Heures d'ouverture</h4>
                            <p>Lundi - Vendredi: 9h00 - 18h00</p>
                            <p>Samedi: 9h00 - 14h00</p>
                            <p>Dimanche: Fermé</p>
                        </div>
                    </div>

                    <div class="social-contact">
                        <h4>Suivez-nous</h4>
                        <div class="social-links-contact">
                            <a href="https://web.facebook.com/GSCCHAITI" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://x.com/gscchaiti_" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="https://www.instagram.com/gscchaiti" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="https://www.linkedin.com/company/98641192/admin/dashboard/" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                            <a href="https://www.youtube.com/@gscchaiti" target="_blank" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Formulaire de contact -->
                <div class="contact-form" data-aos="fade-left">
                    <h3>Envoyez-nous un message</h3>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?= e($success) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= e($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <input type="text" class="form-control" name="nom"
                                    placeholder="Votre nom *"
                                    value="<?= e($_POST['nom'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <input type="email" class="form-control" name="email"
                                    placeholder="Votre email *"
                                    value="<?= e($_POST['email'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <input type="tel" class="form-control" name="telephone"
                                    id="input-telephone"
                                    placeholder="Votre téléphone (ex: +509 2947 4722)"
                                    inputmode="tel"
                                    pattern="[0-9+\s\-().]{7,20}"
                                    maxlength="20"
                                    autocomplete="tel"
                                    value="<?= e($_POST['telephone'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <input type="text" class="form-control" name="sujet"
                                    placeholder="Sujet"
                                    value="<?= e($_POST['sujet'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <textarea class="form-control" name="message"
                                placeholder="Votre message *" required><?= e($_POST['message'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-paper-plane"></i>
                                Envoyer le message
                            </button>
                        </div>

                        <p style="color: #4B5563; font-size: 13px; font-weight: 500; margin-top: 10px;">
                            * Champs obligatoires
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Carte -->
    <section class="map-section">
        <div class="container">
            <div class="map-container" data-aos="zoom-in">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d123456!2d-72.338!3d18.594!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTjCsDM1JzM4LjQiTiA3MsKwMjAnMTYuOCJX!5e0!3m2!1sfr!2sht!4v1234567890"
                    width="100%"
                    height="450"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        // ── Restriction champ téléphone ──────────────────────────────
        // Accepte uniquement : chiffres 0-9, +, espace, tiret, parenthèses, point
        (function() {
            var tel = document.getElementById('input-telephone');
            if (!tel) return;

            // Bloquer à la saisie toute touche qui n'est pas autorisée
            tel.addEventListener('keypress', function(e) {
                var allowed = /[0-9+\s\-().]/;
                if (!allowed.test(e.key) && e.key.length === 1) {
                    e.preventDefault();
                }
            });

            // Nettoyer si l'utilisateur colle du texte (Ctrl+V)
            tel.addEventListener('input', function() {
                var cleaned = this.value.replace(/[^0-9+\s\-().]/g, '');
                if (this.value !== cleaned) {
                    this.value = cleaned;
                }
            });

            // Feedback visuel si valeur invalide au blur
            tel.addEventListener('blur', function() {
                var val = this.value.trim();
                if (val.length > 0 && (val.length < 7 || !/^[0-9+\s\-().]+$/.test(val))) {
                    this.style.borderColor = '#DC2626';
                    this.title = 'Numéro invalide — chiffres, +, espaces et tirets uniquement';
                } else {
                    this.style.borderColor = '';
                    this.title = '';
                }
            });
        })();
    </script>
</body>

</html>