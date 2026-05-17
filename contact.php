<?php
// contact.php
require_once 'includes/config.php';
require_once 'includes/smtp_config.php';

$page_title = 'Contactez-nous';
$page_description = 'Prenez contact avec l\'équipe du GSCC pour toute question, demande d\'aide ou proposition de partenariat.';

$success = '';
$error = '';

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

        if (empty($data['nom'])) {
            $error = 'Veuillez entrer votre nom.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'Veuillez entrer un email valide.';
        } elseif (!empty($data['telephone']) && !preg_match('/^[0-9+\s\-().]{7,20}$/', $data['telephone'])) {
            $error = 'Le numéro de téléphone est invalide.';
        } elseif (empty($data['message'])) {
            $error = 'Veuillez entrer votre message.';
        } else {
            $result = addContactMessage($data);
            if ($result['success']) {
                $success = 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.';

                $sujet_email = '[GSCC Contact] ' . (!empty($data['sujet']) ? $data['sujet'] : 'Nouveau message');
                $tel_affiche = !empty($data['telephone']) ? htmlspecialchars($data['telephone']) : 'Non renseigné';
                $date_envoi  = date('d/m/Y à H:i');

                $corps_html = '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f3f4f6;margin:0;padding:20px;">
  <div style="max-width:600px;margin:0 auto;background:white;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.1);">
    <div style="background:linear-gradient(135deg,#1E2A35,#D94F7A);padding:28px 32px;text-align:center;">
      <h2 style="color:white;margin:0;font-size:1.4rem;">📬 Nouveau message de contact</h2>
      <p style="color:rgba(255,255,255,.85);margin:8px 0 0;font-size:.9rem;">Reçu le ' . $date_envoi . '</p>
    </div>
    <div style="padding:32px;">
      <table style="width:100%;border-collapse:collapse;">
        <tr><td style="padding:10px 0;border-bottom:1px solid #E5E7EB;width:140px;"><strong style="color:#D94F7A;">👤 Nom</strong></td><td style="padding:10px 0;border-bottom:1px solid #E5E7EB;color:#1F2937;">' . htmlspecialchars($data['nom']) . '</td></tr>
        <tr><td style="padding:10px 0;border-bottom:1px solid #E5E7EB;"><strong style="color:#D94F7A;">✉️ Email</strong></td><td style="padding:10px 0;border-bottom:1px solid #E5E7EB;"><a href="mailto:' . htmlspecialchars($data['email']) . '" style="color:#D94F7A;">' . htmlspecialchars($data['email']) . '</a></td></tr>
        <tr><td style="padding:10px 0;border-bottom:1px solid #E5E7EB;"><strong style="color:#D94F7A;">📞 Téléphone</strong></td><td style="padding:10px 0;border-bottom:1px solid #E5E7EB;color:#1F2937;">' . $tel_affiche . '</td></tr>
        <tr><td style="padding:10px 0;"><strong style="color:#D94F7A;">📌 Sujet</strong></td><td style="padding:10px 0;color:#1F2937;">' . (!empty($data['sujet']) ? htmlspecialchars($data['sujet']) : '<em style="color:#9CA3AF;">Non renseigné</em>') . '</td></tr>
      </table>
      <div style="margin-top:24px;"><p style="color:#D94F7A;font-weight:700;margin-bottom:10px;">💬 Message</p>
        <div style="background:#F9FAFB;border-left:4px solid #D94F7A;padding:16px 20px;border-radius:0 8px 8px 0;color:#374151;line-height:1.7;">' . nl2br(htmlspecialchars($data['message'])) . '</div>
      </div>
      <div style="margin-top:28px;text-align:center;">
        <a href="mailto:' . htmlspecialchars($data['email']) . '?subject=Re: ' . rawurlencode(!empty($data['sujet']) ? $data['sujet'] : 'Votre message') . '" style="display:inline-block;background:#D94F7A;color:white;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;">↩ Répondre à ' . htmlspecialchars($data['nom']) . '</a>
      </div>
    </div>
    <div style="background:#F3F4F6;padding:16px 32px;text-align:center;font-size:12px;color:#6B7280;">© ' . date('Y') . ' GSCC — Groupe de Support Contre le Cancer</div>
  </div>
</body></html>';

                try {
                    require_once __DIR__ . '/vendor/PHPMailer/PHPMailer.php';
                    require_once __DIR__ . '/vendor/PHPMailer/SMTP.php';
                    require_once __DIR__ . '/vendor/PHPMailer/Exception.php';
                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'gscchaiti.contact@gmail.com';
                    $mail->Password   = GMAIL_APP_PASSWORD;
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';
                    $mail->setFrom('gscchaiti.contact@gmail.com', 'GSCC Site Web');
                    $mail->addAddress(ADMIN_NOTIFY_EMAIL, 'GSCC Admin');
                    $mail->addReplyTo($data['email'], $data['nom']);
                    $mail->isHTML(true);
                    $mail->Subject = $sujet_email;
                    $mail->Body    = $corps_html;
                    $mail->send();
                } catch (Exception $e) {
                    logError('PHPMailer erreur: ' . $e->getMessage());
                }
                $_POST = [];
            } else {
                $error = $result['error'];
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<?php include 'templates/header.php'; ?>

<style>
    :root {
        --rose:       #D94F7A;
        --rose-dark:  #B83565;
        --teal:       #2A7F7F;
        --charcoal:   #1E2A35;
        --warm-white: #FDFAF8;
        --grey:       #6B7A8D;
        --grey-light: #F0F4F8;
    }

    /* ══════════════════════════════
       HERO
    ══════════════════════════════ */
    .contact-hero {
        position: relative;
        min-height: 460px;
        background: linear-gradient(135deg, #1E2A35 0%, #7B1535 45%, #D94F7A 100%);
        display: flex;
        align-items: center;
        overflow: hidden;
    }

    .hero-blob {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }
    .hero-blob-1 {
        width: 520px; height: 520px;
        top: -160px; right: -120px;
        background: rgba(255,255,255,0.045);
    }
    .hero-blob-2 {
        width: 340px; height: 340px;
        bottom: -130px; left: -80px;
        background: rgba(42,127,127,0.18);
    }
    .hero-blob-3 {
        width: 180px; height: 180px;
        top: 40px; left: 38%;
        background: rgba(255,255,255,0.03);
    }

    .hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        padding: 60px 0 100px;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        background: rgba(255,255,255,0.13);
        border: 1px solid rgba(255,255,255,0.22);
        color: rgba(255,255,255,0.92);
        padding: 9px 22px;
        border-radius: 40px;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.3px;
        margin-bottom: 28px;
        backdrop-filter: blur(8px);
    }

    .hero-title {
        font-size: clamp(2.4rem, 5vw, 3.8rem);
        font-weight: 800;
        color: #fff;
        line-height: 1.1;
        margin-bottom: 18px;
        text-shadow: 0 2px 24px rgba(0,0,0,0.25);
    }

    .hero-title span {
        color: #FBCFE8;
    }

    .hero-subtitle {
        font-size: 1.1rem;
        color: rgba(255,255,255,0.82);
        max-width: 580px;
        margin: 0 auto;
        line-height: 1.75;
    }

    /* ══════════════════════════════
       QUICK CARDS (overlap hero)
    ══════════════════════════════ */
    .quick-cards-wrap {
        margin-top: -70px;
        position: relative;
        z-index: 10;
        padding-bottom: 0;
    }

    .quick-cards-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }

    .quick-card {
        background: white;
        border-radius: 20px;
        padding: 28px 24px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.14);
        display: flex;
        align-items: center;
        gap: 18px;
        text-decoration: none;
        color: inherit;
        transition: transform 0.3s, box-shadow 0.3s;
        border: 1px solid rgba(0,0,0,0.04);
    }

    .quick-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 32px 80px rgba(0,0,0,0.18);
    }

    .qc-icon {
        width: 54px; height: 54px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 21px;
        flex-shrink: 0;
        color: white;
    }
    .qc-icon.rose    { background: linear-gradient(135deg, #D94F7A, #B83565); }
    .qc-icon.teal    { background: linear-gradient(135deg, #2A7F7F, #1a5555); }
    .qc-icon.charcoal{ background: linear-gradient(135deg, #1E2A35, #2d3f50); }

    .qc-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: var(--grey);
        margin-bottom: 5px;
    }

    .qc-value {
        font-size: 15px;
        font-weight: 700;
        color: var(--charcoal);
    }

    /* ══════════════════════════════
       MAIN SECTION
    ══════════════════════════════ */
    .contact-main {
        background: var(--grey-light);
        padding: 80px 0 100px;
    }

    .contact-main-grid {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 56px;
        align-items: start;
    }

    /* ── FORM CARD ── */
    .form-card {
        background: white;
        border-radius: 28px;
        padding: 52px 48px;
        box-shadow: 0 4px 40px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.05);
    }

    .form-card-eyebrow {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--rose);
        margin-bottom: 10px;
    }

    .form-card-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--charcoal);
        margin-bottom: 6px;
        line-height: 1.2;
    }

    .form-card-sub {
        color: var(--grey);
        font-size: 15px;
        margin-bottom: 36px;
        line-height: 1.6;
    }

    .field-label {
        display: block;
        font-size: 12.5px;
        font-weight: 700;
        color: var(--charcoal);
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .field-label .req {
        color: var(--rose);
        margin-left: 2px;
    }

    .form-control {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid #E4E8EE;
        border-radius: 12px;
        font-size: 15px;
        color: var(--charcoal);
        background: var(--warm-white);
        transition: border-color 0.22s, box-shadow 0.22s, background 0.22s;
        font-family: inherit;
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--rose);
        background: white;
        box-shadow: 0 0 0 4px rgba(217,79,122,0.09);
    }

    textarea.form-control {
        min-height: 148px;
        resize: vertical;
        line-height: 1.65;
    }

    .form-row-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-group {
        margin-bottom: 22px;
    }

    .btn-send {
        width: 100%;
        padding: 17px;
        background: linear-gradient(135deg, var(--rose) 0%, var(--rose-dark) 100%);
        color: white;
        border: none;
        border-radius: 14px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: transform 0.28s, box-shadow 0.28s;
        letter-spacing: 0.2px;
        margin-top: 6px;
        font-family: inherit;
    }

    .btn-send:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 44px rgba(217,79,122,0.38);
    }

    .required-note {
        font-size: 12.5px;
        color: var(--grey);
        margin-top: 14px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* Alerts */
    .alert {
        padding: 16px 20px;
        border-radius: 14px;
        margin-bottom: 28px;
        display: flex;
        align-items: flex-start;
        gap: 13px;
        font-size: 14.5px;
        font-weight: 500;
        line-height: 1.5;
    }
    .alert i { margin-top: 2px; flex-shrink: 0; font-size: 16px; }
    .alert-success { background:#ECFDF5; color:#065F46; border:1.5px solid #A7F3D0; }
    .alert-error   { background:#FEF2F2; color:#991B1B; border:1.5px solid #FECACA; }

    /* ── INFO SIDEBAR ── */
    .info-sidebar {
        position: sticky;
        top: 140px;
    }

    .sidebar-section-title {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--charcoal);
        margin-bottom: 20px;
    }

    .info-item {
        display: flex;
        gap: 16px;
        align-items: flex-start;
        background: white;
        border-radius: 18px;
        padding: 20px;
        margin-bottom: 14px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
        transition: transform 0.25s, box-shadow 0.25s;
    }

    .info-item:hover {
        transform: translateX(5px);
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    }

    .info-icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
    }
    .info-icon.rose    { background:rgba(217,79,122,0.1); color:var(--rose); }
    .info-icon.teal    { background:rgba(42,127,127,0.1); color:var(--teal); }
    .info-icon.charcoal{ background:rgba(30,42,53,0.08);  color:var(--charcoal); }

    .info-text-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: var(--grey);
        margin-bottom: 4px;
    }

    .info-text-value {
        font-size: 14.5px;
        font-weight: 600;
        color: var(--charcoal);
        line-height: 1.55;
    }

    .info-text-value a {
        color: var(--rose);
        text-decoration: none;
    }
    .info-text-value a:hover { text-decoration: underline; }

    /* Hours card */
    .hours-card {
        background: linear-gradient(135deg, var(--charcoal) 0%, #2d3f50 100%);
        border-radius: 22px;
        padding: 28px 26px;
        margin-top: 20px;
        color: white;
    }

    .hours-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: rgba(255,255,255,0.55);
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .hours-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        font-size: 13.5px;
        border-bottom: 1px solid rgba(255,255,255,0.07);
    }
    .hours-row:last-child { border-bottom: none; padding-bottom: 0; }

    .hours-day  { color: rgba(255,255,255,0.7); }
    .hours-time { font-weight: 700; color: white; }
    .hours-time.closed { color: #FDA4AF; }

    .patient-tag {
        display: inline-block;
        background: rgba(217,79,122,0.25);
        color: #FBCFE8;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        margin: 12px 0 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Social */
    .social-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: var(--grey);
        margin: 22px 0 14px;
    }

    .social-bar {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .social-btn {
        width: 44px; height: 44px;
        border-radius: 12px;
        background: white;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.06);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--charcoal);
        font-size: 17px;
        text-decoration: none;
        transition: all 0.25s;
    }
    .social-btn:hover {
        background: var(--rose);
        color: white;
        transform: translateY(-4px);
        box-shadow: 0 10px 24px rgba(217,79,122,0.35);
        border-color: transparent;
    }

    /* ══════════════════════════════
       MAP
    ══════════════════════════════ */
    .map-section {
        position: relative;
        overflow: hidden;
    }

    .map-header {
        background: white;
        padding: 40px 0 0;
        text-align: center;
    }

    .map-header h2 {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--charcoal);
        margin-bottom: 6px;
    }

    .map-header p {
        color: var(--grey);
        font-size: 14.5px;
        margin-bottom: 32px;
    }

    .map-frame {
        display: block;
        width: 100%;
        height: 480px;
        border: 0;
        filter: grayscale(15%) contrast(1.05);
    }

    /* ══════════════════════════════
       RESPONSIVE
    ══════════════════════════════ */
    @media (max-width: 1100px) {
        .contact-main-grid { grid-template-columns: 1fr; }
        .info-sidebar { position: static; }
    }

    @media (max-width: 768px) {
        .hero-content { padding: 50px 0 90px; }
        .hero-badge { font-size: 12px; padding: 7px 16px; }
        .quick-cards-wrap { margin-top: -50px; }
        .quick-cards-grid { grid-template-columns: 1fr; gap: 14px; }
        .form-card { padding: 32px 22px; }
        .form-row-2 { grid-template-columns: 1fr; }
        .contact-main { padding: 60px 0 70px; }
        .map-frame { height: 360px; }
    }

    @media (max-width: 480px) {
        .quick-cards-wrap { margin-top: -30px; }
        .form-card { border-radius: 20px; }
    }
</style>

<!-- ═══════════════════════════════════════════
     HERO
═══════════════════════════════════════════ -->
<section class="contact-hero">
    <div class="hero-blob hero-blob-1"></div>
    <div class="hero-blob hero-blob-2"></div>
    <div class="hero-blob hero-blob-3"></div>

    <div class="container" style="width:100%;">
        <div class="hero-content" data-aos="fade-up">
            <div class="hero-badge">
                <i class="fas fa-comments"></i>
                Nous sommes disponibles pour vous
            </div>
            <h1 class="hero-title">
                Parlons <span>ensemble</span>
            </h1>
            <p class="hero-subtitle">
                Une question, un projet de partenariat, un besoin d'aide ?<br>
                L'équipe du GSCC est là pour vous accompagner.
            </p>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════
     QUICK CARDS
═══════════════════════════════════════════ -->
<div class="quick-cards-wrap">
    <div class="container">
        <div class="quick-cards-grid">
            <a class="quick-card" href="tel:+50929474722" data-aos="fade-up" data-aos-delay="0">
                <div class="qc-icon rose"><i class="fas fa-phone-alt"></i></div>
                <div>
                    <div class="qc-label">Téléphone</div>
                    <div class="qc-value">+509 2947 4722</div>
                </div>
            </a>
            <a class="quick-card" href="mailto:<?= SITE_EMAIL ?>" data-aos="fade-up" data-aos-delay="80">
                <div class="qc-icon teal"><i class="fas fa-envelope"></i></div>
                <div>
                    <div class="qc-label">Email</div>
                    <div class="qc-value"><?= SITE_EMAIL ?></div>
                </div>
            </a>
            <div class="quick-card" data-aos="fade-up" data-aos-delay="160">
                <div class="qc-icon charcoal"><i class="fas fa-map-marker-alt"></i></div>
                <div>
                    <div class="qc-label">Adresse</div>
                    <div class="qc-value">Port-au-Prince, Haïti</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════
     MAIN SECTION
═══════════════════════════════════════════ -->
<section class="contact-main">
    <div class="container">
        <div class="contact-main-grid">

            <!-- ── FORMULAIRE ── -->
            <div class="form-card" data-aos="fade-right">
                <p class="form-card-eyebrow"><i class="fas fa-paper-plane"></i>&nbsp; Écrivez-nous</p>
                <h2 class="form-card-title">Envoyez-nous un message</h2>
                <p class="form-card-sub">Remplissez le formulaire et nous vous répondrons dans les meilleurs délais.</p>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?= e($success) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= e($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                    <div class="form-row-2">
                        <div class="form-group">
                            <label class="field-label" for="f-nom">Nom complet <span class="req">*</span></label>
                            <input id="f-nom" type="text" class="form-control" name="nom"
                                placeholder="Marie Dupont"
                                value="<?= e($_POST['nom'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="field-label" for="f-email">Email <span class="req">*</span></label>
                            <input id="f-email" type="email" class="form-control" name="email"
                                placeholder="exemple@email.com"
                                value="<?= e($_POST['email'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label class="field-label" for="f-tel">Téléphone</label>
                            <input id="f-tel" type="tel" class="form-control" name="telephone"
                                placeholder="+509 2947 4722"
                                inputmode="tel"
                                pattern="[0-9+\s\-().]{7,20}"
                                maxlength="20"
                                value="<?= e($_POST['telephone'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="field-label" for="f-sujet">Sujet</label>
                            <input id="f-sujet" type="text" class="form-control" name="sujet"
                                placeholder="Ex: Demande de partenariat"
                                value="<?= e($_POST['sujet'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="field-label" for="f-msg">Message <span class="req">*</span></label>
                        <textarea id="f-msg" class="form-control" name="message"
                            placeholder="Décrivez votre demande en détail..." required><?= e($_POST['message'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn-send">
                        <i class="fas fa-paper-plane"></i>
                        Envoyer le message
                    </button>

                    <p class="required-note"><span style="color:var(--rose);">*</span> Champs obligatoires</p>
                </form>
            </div>

            <!-- ── SIDEBAR INFO ── -->
            <aside class="info-sidebar" data-aos="fade-left">
                <h3 class="sidebar-section-title">Nos coordonnées</h3>

                <div class="info-item">
                    <div class="info-icon rose"><i class="fas fa-phone-alt"></i></div>
                    <div>
                        <div class="info-text-label">Téléphone</div>
                        <div class="info-text-value">
                            <a href="tel:+50929474722">+509 2947 4722</a>
                        </div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon teal"><i class="fas fa-envelope"></i></div>
                    <div>
                        <div class="info-text-label">Email</div>
                        <div class="info-text-value">
                            <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>
                        </div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon charcoal"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <div class="info-text-label">Adresse</div>
                        <div class="info-text-value">Port-au-Prince, Haïti<br>Bureau principal</div>
                    </div>
                </div>

                <!-- Heures -->
                <div class="hours-card">
                    <div class="hours-title">
                        <i class="fas fa-clock"></i> Heures d'ouverture
                    </div>
                    <div class="hours-row">
                        <span class="hours-day">Lundi – Vendredi</span>
                        <span class="hours-time">9h00 – 15h00</span>
                    </div>
                    <div class="hours-row">
                        <span class="hours-day">Samedi – Dimanche</span>
                        <span class="hours-time closed">Fermé</span>
                    </div>
                    <div class="patient-tag"><i class="fas fa-heartbeat"></i> Visite patient</div>
                    <div class="hours-row">
                        <span class="hours-day">Lundi – Jeudi</span>
                        <span class="hours-time">9h00 – 14h00</span>
                    </div>
                </div>

                <!-- Réseaux sociaux -->
                <p class="social-title"><i class="fas fa-share-alt"></i>&nbsp; Suivez-nous</p>
                <div class="social-bar">
                    <a class="social-btn" href="https://web.facebook.com/GSCCHAITI" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a class="social-btn" href="https://x.com/gscchaiti_" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a class="social-btn" href="https://www.instagram.com/gscchaiti" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a class="social-btn" href="https://www.linkedin.com/company/98641192/admin/dashboard/" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a class="social-btn" href="https://www.youtube.com/@gscchaiti" target="_blank" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </aside>

        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════
     MAP
═══════════════════════════════════════════ -->
<section class="map-section">
    <div class="map-header">
        <h2 data-aos="fade-up">Nous trouver</h2>
        <p data-aos="fade-up" data-aos-delay="80">Port-au-Prince, Haïti — Bureau principal du GSCC</p>
    </div>
    <iframe class="map-frame"
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d123456!2d-72.338!3d18.594!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTjCsDM1JzM4LjQiTiA3MsKwMjAnMTYuOCJX!5e0!3m2!1sfr!2sht!4v1234567890"
        allowfullscreen=""
        loading="lazy"
        title="Localisation GSCC Port-au-Prince">
    </iframe>
</section>

<?php include 'templates/footer.php'; ?>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="assets/js/main.js"></script>
<script>
    AOS.init({ duration: 900, once: true, offset: 60 });

    /* Validation téléphone */
    (function () {
        var tel = document.getElementById('f-tel');
        if (!tel) return;
        tel.addEventListener('keypress', function (e) {
            if (!/[0-9+\s\-().]/.test(e.key) && e.key.length === 1) e.preventDefault();
        });
        tel.addEventListener('input', function () {
            var c = this.value.replace(/[^0-9+\s\-().]/g, '');
            if (this.value !== c) this.value = c;
        });
        tel.addEventListener('blur', function () {
            var v = this.value.trim();
            this.style.borderColor = (v.length > 0 && (v.length < 7 || !/^[0-9+\s\-().]+$/.test(v))) ? '#DC2626' : '';
        });
    })();
</script>
