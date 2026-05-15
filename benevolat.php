<?php
// devenir-benevole.php
require_once 'includes/config.php';
require_once 'includes/smtp_config.php';

$page_title = 'Devenir bénévole';
$page_description = 'Rejoignez notre équipe de bénévoles et donnez de votre temps pour soutenir notre mission.';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom           = trim(strip_tags($_POST['nom']            ?? ''));
    $prenom        = trim(strip_tags($_POST['prenom']         ?? ''));
    $email         = trim(strip_tags($_POST['email']          ?? ''));
    $telephone     = trim(strip_tags($_POST['telephone']      ?? ''));
    $date_naissance = trim(strip_tags($_POST['date_naissance'] ?? ''));
    $profession    = trim(strip_tags($_POST['profession']     ?? ''));
    $disponibilites = trim(strip_tags($_POST['disponibilites'] ?? ''));
    $motivations   = trim(strip_tags($_POST['motivations']    ?? ''));
    $competences   = isset($_POST['competences']) && is_array($_POST['competences'])
        ? array_map('strip_tags', $_POST['competences']) : [];
    $engagement    = isset($_POST['engagement']);

    if (empty($nom) || empty($prenom) || empty($email) || empty($telephone)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!preg_match("/^[\p{L}\s'\-]{2,60}$/u", $nom)) {
        $error = 'Le nom ne doit contenir que des lettres.';
    } elseif (!preg_match("/^[\p{L}\s'\-]{2,60}$/u", $prenom)) {
        $error = 'Le prénom ne doit contenir que des lettres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide.';
    } elseif (!preg_match('/^[0-9+\s\-().]{7,20}$/', $telephone)) {
        $error = 'Numéro de téléphone invalide. Chiffres uniquement.';
    } elseif (!empty($date_naissance)) {
        $dob    = new DateTime($date_naissance);
        $today  = new DateTime();
        $age    = $today->diff($dob)->y;
        if ($age < 16) {
            $error = 'Vous devez avoir au moins 16 ans pour devenir bénévole.';
        } elseif ($age > 100) {
            $error = 'Date de naissance invalide.';
        }
    }
    if (empty($error)) {
        if (empty($motivations) || strlen($motivations) < 50) {
            $error = 'Veuillez décrire vos motivations (minimum 50 caractères).';
        } elseif (!$engagement) {
            $error = 'Vous devez accepter la charte du bénévole.';
        } else {
            try {
                $competences_json = !empty($competences) ? json_encode($competences, JSON_UNESCAPED_UNICODE) : null;
                $dob = !empty($date_naissance) ? $date_naissance : null;

                $stmt = $pdo->prepare(
                    "INSERT INTO candidatures_benevoles
                    (nom, prenom, email, telephone, date_naissance, profession,
                     disponibilites, competences, motivations, statut, date_candidature)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW())"
                );
                $stmt->execute([
                    $nom, $prenom, $email, $telephone, $dob,
                    $profession, $disponibilites, $competences_json, $motivations
                ]);

                try {
                    $sname = defined('SITE_NAME') ? SITE_NAME : 'GSCC';
                    $subject_c = "Candidature de bénévolat — $sname";
                    $msg_c = "Bonjour $prenom $nom,\n\n"
                        . "Nous avons bien reçu votre candidature pour devenir bénévole au $sname.\n"
                        . "Notre équipe va l'étudier et vous recontactera dans les plus brefs délais.\n\n"
                        . "Compétences mentionnées : " . (!empty($competences) ? implode(', ', $competences) : 'Non précisées') . "\n\n"
                        . "Merci pour votre engagement !\n\nCordialement,\nL'équipe $sname";
                    if (function_exists('sendEmail')) sendEmail($email, $subject_c, $msg_c);
                } catch (Exception $ignored) {}

                try {
                    require_once __DIR__ . '/vendor/PHPMailer/PHPMailer.php';
                    require_once __DIR__ . '/vendor/PHPMailer/SMTP.php';
                    require_once __DIR__ . '/vendor/PHPMailer/Exception.php';

                    $sname      = defined('SITE_NAME') ? SITE_NAME : 'GSCC';
                    $date_envoi = date('d/m/Y \xc3\xa0 H:i');
                    $tel_aff    = !empty($telephone)      ? htmlspecialchars($telephone)                    : 'Non renseigné';
                    $prof_aff   = !empty($profession)     ? htmlspecialchars($profession)                   : 'Non renseignée';
                    $dispo_aff  = !empty($disponibilites) ? htmlspecialchars($disponibilites)               : 'Non renseignées';
                    $comp_aff   = !empty($competences)    ? htmlspecialchars(implode(', ', $competences))   : 'Non précisées';
                    $dob_aff    = !empty($date_naissance) ? date('d/m/Y', strtotime($date_naissance))       : 'Non renseignée';

                    $corps_html = '
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f3f4f6;margin:0;padding:20px;">
  <div style="max-width:620px;margin:0 auto;background:white;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.1);">
    <div style="background:linear-gradient(135deg,#003399,#D94F7A);padding:28px 32px;text-align:center;">
      <h2 style="color:white;margin:0;font-size:1.4rem;">🙋 Nouvelle candidature bénévole</h2>
      <p style="color:rgba(255,255,255,.85);margin:8px 0 0;font-size:0.9rem;">Reçue le ' . $date_envoi . ' — En attente de traitement</p>
    </div>
    <div style="padding:32px;">
      <table style="width:100%;border-collapse:collapse;">
        <tr><td style="padding:10px 0;border-bottom:1px solid #E5E7EB;width:180px;"><strong style="color:#003399;">👤 Nom complet</strong></td>
            <td style="padding:10px 0;border-bottom:1px solid #E5E7EB;color:#1F2937;">' . htmlspecialchars($prenom . ' ' . $nom) . '</td></tr>
        <tr><td style="padding:10px 0;border-bottom:1px solid #E5E7EB;"><strong style="color:#003399;">✉️ Email</strong></td>
            <td style="padding:10px 0;border-bottom:1px solid #E5E7EB;"><a href="mailto:' . htmlspecialchars($email) . '" style="color:#D94F7A;">' . htmlspecialchars($email) . '</a></td></tr>
        <tr><td style="padding:10px 0;border-bottom:1px solid #E5E7EB;"><strong style="color:#003399;">📞 Téléphone</strong></td>
            <td style="padding:10px 0;border-bottom:1px solid #E5E7EB;color:#1F2937;">' . $tel_aff . '</td></tr>
        <tr><td style="padding:10px 0;border-bottom:1px solid #E5E7EB;"><strong style="color:#003399;">🎂 Date de naissance</strong></td>
            <td style="padding:10px 0;border-bottom:1px solid #E5E7EB;color:#1F2937;">' . $dob_aff . '</td></tr>
        <tr><td style="padding:10px 0;border-bottom:1px solid #E5E7EB;"><strong style="color:#003399;">💼 Profession</strong></td>
            <td style="padding:10px 0;border-bottom:1px solid #E5E7EB;color:#1F2937;">' . $prof_aff . '</td></tr>
        <tr><td style="padding:10px 0;border-bottom:1px solid #E5E7EB;"><strong style="color:#003399;">🕐 Disponibilités</strong></td>
            <td style="padding:10px 0;border-bottom:1px solid #E5E7EB;color:#1F2937;">' . $dispo_aff . '</td></tr>
        <tr><td style="padding:10px 0;border-bottom:1px solid #E5E7EB;"><strong style="color:#003399;">🛠️ Compétences</strong></td>
            <td style="padding:10px 0;border-bottom:1px solid #E5E7EB;color:#1F2937;">' . $comp_aff . '</td></tr>
      </table>
      <div style="margin-top:24px;">
        <p style="color:#003399;font-weight:700;margin-bottom:10px;">💬 Motivations</p>
        <div style="background:#F9FAFB;border-left:4px solid #D94F7A;padding:16px 20px;border-radius:0 8px 8px 0;color:#374151;line-height:1.7;">'
        . nl2br(htmlspecialchars($motivations)) . '</div>
      </div>
      <div style="margin-top:28px;text-align:center;">
        <a href="mailto:' . htmlspecialchars($email) . '?subject=Re: Candidature bénévole GSCC"
           style="display:inline-block;background:#003399;color:white;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.95rem;">
          ↩ Répondre à ' . htmlspecialchars($prenom) . '</a>
      </div>
    </div>
    <div style="background:#F3F4F6;padding:16px;text-align:center;font-size:12px;color:#6B7280;">
      © ' . date('Y') . ' GSCC — Groupe de Support Contre le Cancer</div>
  </div>
</body></html>';

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
                    $mail->addReplyTo($email, $prenom . ' ' . $nom);
                    $mail->isHTML(true);
                    $mail->Subject = '[GSCC Bénévolat] Nouvelle candidature — ' . $prenom . ' ' . $nom;
                    $mail->Body    = $corps_html;
                    $mail->send();
                } catch (Exception $e) {
                    logError('PHPMailer bénévolat: ' . $e->getMessage());
                }

                $success = 'Votre candidature a été envoyée avec succès. Nous vous contacterons rapidement.';
                $_POST   = [];
            } catch (Exception $e) {
                $error = "Erreur lors de l'enregistrement : " . $e->getMessage();
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

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --blue:      #003399;
            --blue-dark: #001f6b;
            --blue-mid:  #1a56cc;
            --rose:      #C8375A;
            --rose-lite: #FDF0F3;
            --gold:      #E8A020;
            --sage:      #2E7D32;
            --text:      #0D1117;
            --text-2:    #1F2937;
            --muted:     #6B7280;
            --border:    #E5E7EB;
            --bg:        #F8FAFF;
            --white:     #FFFFFF;
        }

        /* ── HERO ── */
        .bv-hero {
            background: linear-gradient(135deg, #7B1535 0%, #C8375F 55%, #D94F7A 100%);
            padding: 90px 0 130px;
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        .bv-hero::before, .bv-hero::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
            pointer-events: none;
        }
        .bv-hero::before { width: 480px; height: 480px; top: -130px; right: -90px; }
        .bv-hero::after  { width: 300px; height: 300px; bottom: 30px; left: -70px; }
        .bv-hero .container { position: relative; z-index: 2; }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.25);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            padding: 7px 20px;
            border-radius: 100px;
            margin-bottom: 24px;
        }
        .bv-hero h1 {
            font-size: clamp(2.2rem, 5vw, 3.6rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
            margin-bottom: 18px;
        }
        .bv-hero h1 span { color: #F9A8C4; }
        .bv-hero > .container > p {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.88);
            max-width: 540px;
            margin: 0 auto;
            line-height: 1.8;
        }
        .hero-wave {
            position: absolute;
            bottom: -1px; left: 0;
            width: 100%; line-height: 0; z-index: 1;
        }

        /* ── MAIN SECTION ── */
        .bv-section {
            background: var(--bg);
            padding: 60px 0 80px;
        }
        .bv-layout {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 32px;
            align-items: start;
        }

        /* ── FORM CARD ── */
        .form-card {
            background: var(--white);
            border-radius: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 24px rgba(0,0,0,.07);
            overflow: hidden;
        }
        .form-card-header {
            background: linear-gradient(135deg, var(--blue-dark), var(--blue-mid));
            padding: 28px 36px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .form-card-header .fh-icon {
            width: 52px; height: 52px;
            background: rgba(255,255,255,.12);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; color: #F9A8C4;
            flex-shrink: 0;
        }
        .form-card-header h2 {
            font-size: 1.25rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 3px;
        }
        .form-card-header p {
            font-size: 13px;
            color: rgba(255,255,255,.75);
        }
        .form-card-body { padding: 36px; }

        /* Alerts */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 28px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .alert i { font-size: 16px; flex-shrink: 0; margin-top: 1px; }
        .alert-success { background: #D1FAE5; color: #065F46; border: 1.5px solid #6EE7B7; }
        .alert-error   { background: #FEE2E2; color: #991B1B; border: 1.5px solid #FCA5A5; }

        /* Section dividers in form */
        .form-section-title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--blue);
            padding: 0 0 12px;
            border-bottom: 2px solid var(--bg);
            margin-bottom: 20px;
            margin-top: 8px;
        }
        .form-section-title:not(:first-child) { margin-top: 32px; }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
            font-size: 13.5px;
            color: var(--text-2);
        }
        .form-group label .req { color: var(--rose); margin-left: 2px; }

        .form-control {
            width: 100%;
            padding: 11px 14px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 14.5px;
            font-family: inherit;
            color: var(--text);
            background: var(--white);
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control::placeholder { color: #9CA3AF; }
        .form-control:focus {
            outline: none;
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(0,51,153,.1);
        }
        textarea.form-control { min-height: 130px; resize: vertical; }

        /* Checkboxes */
        .competences-label {
            display: block;
            margin-bottom: 12px;
            font-weight: 600;
            font-size: 13.5px;
            color: var(--text-2);
        }
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .cb-item {
            display: flex;
            align-items: center;
            gap: 9px;
            background: var(--bg);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 10px 12px;
            cursor: pointer;
            transition: border-color .2s, background .2s;
        }
        .cb-item:has(input:checked) {
            border-color: var(--blue);
            background: #EBF0FF;
        }
        .cb-item input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: var(--blue);
            flex-shrink: 0; cursor: pointer;
        }
        .cb-item label {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-2);
            cursor: pointer;
            margin: 0;
        }

        /* Charte */
        .charte-box {
            background: var(--bg);
            border: 1.5px solid var(--border);
            border-left: 4px solid var(--blue);
            border-radius: 12px;
            padding: 18px 20px;
            margin: 8px 0 18px;
            max-height: 190px;
            overflow-y: auto;
        }
        .charte-box h4 {
            color: var(--blue);
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .charte-box ul { padding-left: 0; list-style: none; }
        .charte-box li {
            color: var(--text-2);
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 8px;
            line-height: 1.55;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .charte-box li::before {
            content: '\f058';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: var(--blue);
            font-size: 12px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        /* Engagement */
        .engagement-row {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: var(--rose-lite);
            border: 1.5px solid #F9C8D4;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 24px;
        }
        .engagement-row input[type="checkbox"] {
            width: 18px; height: 18px;
            margin-top: 1px; flex-shrink: 0;
            accent-color: var(--rose); cursor: pointer;
        }
        .engagement-row label {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text-2);
            cursor: pointer;
            line-height: 1.6;
        }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: 16px 28px;
            background: linear-gradient(135deg, var(--rose), #E0456B);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 6px 20px rgba(200,55,90,.35);
            transition: transform .25s, box-shadow .25s;
        }
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(200,55,90,.50);
        }

        /* ── SIDEBAR ── */
        .sidebar { display: flex; flex-direction: column; gap: 20px; }

        .sb-card {
            background: var(--white);
            border-radius: 20px;
            border: 1px solid var(--border);
            box-shadow: 0 2px 12px rgba(0,0,0,.05);
            overflow: hidden;
        }
        .sb-card-header {
            padding: 18px 24px 0;
        }
        .sb-card-header h3 {
            font-size: 14px;
            font-weight: 800;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 14px;
            border-bottom: 2px solid var(--bg);
        }
        .sb-card-header h3 i { color: var(--blue); }
        .sb-card-body { padding: 8px 24px 20px; }

        .why-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 0;
            border-bottom: 1px solid var(--border);
        }
        .why-item:last-child { border-bottom: none; }
        .why-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: #fff;
            flex-shrink: 0;
        }
        .why-item h4 {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 3px;
        }
        .why-item p {
            font-size: 12.5px;
            color: var(--muted);
            line-height: 1.6;
        }

        /* Testimonial */
        .testimonial-block {
            padding: 20px 24px;
        }
        .quote-mark {
            font-size: 42px;
            line-height: 1;
            color: #DBEAFE;
            font-family: Georgia, serif;
            font-weight: 900;
            margin-bottom: -8px;
        }
        .testimonial-text {
            font-size: 14px;
            color: var(--text-2);
            line-height: 1.75;
            font-style: italic;
        }
        .testimonial-author {
            margin-top: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .author-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--rose), #E0456B);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .author-info strong {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
        }
        .author-info span {
            font-size: 12px;
            color: var(--muted);
        }

        /* Contact card */
        .contact-line {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }
        .contact-line:last-child { border-bottom: none; }
        .contact-ico {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; color: #fff;
            flex-shrink: 0;
        }
        .contact-line strong {
            display: block;
            font-size: 13.5px;
            font-weight: 700;
            color: var(--text);
        }
        .contact-line span {
            font-size: 12px;
            color: var(--muted);
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 1024px) {
            .bv-layout { grid-template-columns: 1fr; }
            .sidebar { flex-direction: row; flex-wrap: wrap; }
            .sb-card { flex: 1 1 280px; }
        }
        @media (max-width: 680px) {
            .form-row { grid-template-columns: 1fr; }
            .checkbox-grid { grid-template-columns: 1fr 1fr; }
            .form-card-body { padding: 24px; }
            .sidebar { flex-direction: column; }
        }
        @media (max-width: 420px) {
            .checkbox-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>
    <?php include 'templates/header.php'; ?>

    <!-- Hero -->
    <section class="bv-hero">
        <div class="container">
            <div class="hero-badge" data-aos="fade-down">
                <i class="fas fa-hands-helping"></i> Rejoindre notre équipe
            </div>
            <h1 data-aos="fade-up" data-aos-delay="60">
                Devenez <span>bénévole</span><br>au GSCC
            </h1>
            <p data-aos="fade-up" data-aos-delay="130">
                Donnez de votre temps et changez des vies. Chaque heure offerte est une lumière pour ceux qui luttent contre le cancer.
            </p>
        </div>
        <div class="hero-wave">
            <svg viewBox="0 0 1440 64" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path d="M0,32 C360,64 1080,0 1440,32 L1440,64 L0,64 Z" fill="#F8FAFF"/>
            </svg>
        </div>
    </section>

    <!-- Main -->
    <section class="bv-section">
        <div class="container">
            <div class="bv-layout">

                <!-- Formulaire -->
                <div class="form-card" data-aos="fade-right">
                    <div class="form-card-header">
                        <div class="fh-icon"><i class="fas fa-file-signature"></i></div>
                        <div>
                            <h2>Formulaire de candidature</h2>
                            <p>Remplissez le formulaire — nous vous répondrons sous 48h</p>
                        </div>
                    </div>
                    <div class="form-card-body">

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

                        <form method="POST" id="benevoleForm">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                            <div class="form-section-title">Informations personnelles</div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Nom <span class="req">*</span></label>
                                    <input type="text" name="nom" class="form-control" placeholder="Votre nom" value="<?= e($_POST['nom'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Prénom <span class="req">*</span></label>
                                    <input type="text" name="prenom" class="form-control" placeholder="Votre prénom" value="<?= e($_POST['prenom'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Email <span class="req">*</span></label>
                                    <input type="email" name="email" class="form-control" placeholder="votre@email.com" value="<?= e($_POST['email'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Téléphone <span class="req">*</span></label>
                                    <input type="tel" name="telephone" class="form-control" placeholder="+509 37 00 00 00" value="<?= e($_POST['telephone'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Date de naissance</label>
                                    <input type="date" name="date_naissance" class="form-control" value="<?= e($_POST['date_naissance'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Profession</label>
                                    <input type="text" name="profession" class="form-control" placeholder="Votre métier" value="<?= e($_POST['profession'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="form-section-title">Engagement &amp; disponibilités</div>

                            <div class="form-group">
                                <label>Disponibilités</label>
                                <input type="text" name="disponibilites" class="form-control"
                                    placeholder="Ex : week-ends, soirées, quelques heures par semaine…"
                                    value="<?= e($_POST['disponibilites'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <div class="competences-label">Compétences / Centres d'intérêt</div>
                                <div class="checkbox-grid">
                                    <?php
                                    $comps = ['Accompagnement','Administratif','Communication','Événementiel','Informatique','Santé'];
                                    foreach ($comps as $i => $c):
                                        $id  = 'comp' . ($i + 1);
                                        $chk = in_array($c, $_POST['competences'] ?? []) ? 'checked' : '';
                                    ?>
                                    <div class="cb-item">
                                        <input type="checkbox" name="competences[]" value="<?= $c ?>" id="<?= $id ?>" <?= $chk ?>>
                                        <label for="<?= $id ?>"><?= $c ?></label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Vos motivations <span class="req">*</span></label>
                                <textarea name="motivations" class="form-control" required
                                    placeholder="Décrivez pourquoi vous souhaitez devenir bénévole… (minimum 50 caractères)"><?= e($_POST['motivations'] ?? '') ?></textarea>
                            </div>

                            <div class="form-section-title">Charte du bénévole</div>

                            <div class="charte-box">
                                <h4><i class="fas fa-scroll"></i> En devenant bénévole au GSCC, je m'engage à :</h4>
                                <ul>
                                    <li>Respecter la confidentialité des informations</li>
                                    <li>Être ponctuel et assidu dans mes engagements</li>
                                    <li>Adopter une attitude bienveillante envers les patients</li>
                                    <li>Travailler en équipe et respecter les consignes</li>
                                    <li>Signaler toute difficulté à mon référent</li>
                                </ul>
                            </div>

                            <div class="engagement-row">
                                <input type="checkbox" name="engagement" id="engagement" required>
                                <label for="engagement">J'ai lu et j'accepte la charte du bénévole du GSCC <span class="req">*</span></label>
                            </div>

                            <button type="submit" class="btn-submit">
                                <i class="fas fa-paper-plane"></i>
                                Envoyer ma candidature
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="sidebar" data-aos="fade-left" data-aos-delay="80">

                    <!-- Pourquoi bénévole -->
                    <div class="sb-card">
                        <div class="sb-card-header">
                            <h3><i class="fas fa-star"></i> Pourquoi devenir bénévole ?</h3>
                        </div>
                        <div class="sb-card-body">
                            <div class="why-item">
                                <div class="why-icon" style="background:var(--rose);"><i class="fas fa-heart"></i></div>
                                <div>
                                    <h4>Donner du sens</h4>
                                    <p>Apportez votre pierre à l'édifice et aidez ceux qui en ont besoin.</p>
                                </div>
                            </div>
                            <div class="why-item">
                                <div class="why-icon" style="background:var(--blue);"><i class="fas fa-handshake"></i></div>
                                <div>
                                    <h4>Rencontrer</h4>
                                    <p>Faites partie d'une équipe passionnée et rencontrez des personnes inspirantes.</p>
                                </div>
                            </div>
                            <div class="why-item">
                                <div class="why-icon" style="background:var(--sage);"><i class="fas fa-graduation-cap"></i></div>
                                <div>
                                    <h4>Apprendre</h4>
                                    <p>Développez de nouvelles compétences et enrichissez votre expérience.</p>
                                </div>
                            </div>
                            <div class="why-item">
                                <div class="why-icon" style="background:var(--gold);"><i class="fas fa-clock"></i></div>
                                <div>
                                    <h4>Flexibilité</h4>
                                    <p>Choisissez vos missions selon vos disponibilités et envies.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Témoignage -->
                    <div class="sb-card">
                        <div class="testimonial-block">
                            <div class="quote-mark">"</div>
                            <p class="testimonial-text">
                                Être bénévole au GSCC m'a permis de donner un sens à mon temps libre.
                                Accompagner les patients et voir leur sourire, c'est une richesse inestimable.
                            </p>
                            <div class="testimonial-author">
                                <div class="author-avatar">M</div>
                                <div class="author-info">
                                    <strong>Marie</strong>
                                    <span>Bénévole depuis 2 ans</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact -->
                    <div class="sb-card">
                        <div class="sb-card-header">
                            <h3><i class="fas fa-headset"></i> Besoin d'info ?</h3>
                        </div>
                        <div class="sb-card-body">
                            <p style="font-size:13.5px;color:var(--muted);line-height:1.7;padding:8px 0 12px;">
                                Notre responsable bénévolat est à votre disposition pour répondre à toutes vos questions.
                            </p>
                            <div class="contact-line">
                                <div class="contact-ico" style="background:var(--blue);"><i class="fas fa-phone"></i></div>
                                <div>
                                    <strong>+(509) 29 47 47 22</strong>
                                    <span>Lun – Ven, 8h – 16h</span>
                                </div>
                            </div>
                            <div class="contact-line">
                                <div class="contact-ico" style="background:var(--rose);"><i class="fas fa-envelope"></i></div>
                                <div>
                                    <strong>benevolat@gscc.org</strong>
                                    <span>Réponse sous 48h</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 650, once: true, offset: 50 });</script>

    <script>
        document.getElementById('benevoleForm').addEventListener('submit', function(e) {
            const motivations = document.querySelector('textarea[name="motivations"]').value;
            if (motivations.length < 50) {
                e.preventDefault();
                alert('Veuillez détailler un peu plus vos motivations (minimum 50 caractères).');
            }
        });
    </script>

    <script>
        (function() {
            function addError(input, msg) {
                removeError(input);
                input.style.borderColor = '#DC2626';
                var el = document.createElement('span');
                el.className = 'field-error-msg';
                el.style.cssText = 'color:#DC2626;font-size:12px;margin-top:4px;display:block;';
                el.textContent = msg;
                input.parentNode.insertBefore(el, input.nextSibling);
            }
            function removeError(input) {
                input.style.borderColor = '';
                var next = input.nextSibling;
                while (next) {
                    if (next.classList && next.classList.contains('field-error-msg')) { next.parentNode.removeChild(next); break; }
                    next = next.nextSibling;
                }
            }
            function addOk(input) { removeError(input); input.style.borderColor = '#16A34A'; }

            document.querySelectorAll('input[name="nom"], input[name="prenom"]').forEach(function(inp) {
                inp.setAttribute('autocomplete', inp.name === 'nom' ? 'family-name' : 'given-name');
                inp.setAttribute('maxlength', '60');
                inp.addEventListener('keypress', function(e) { if (!/[\p{L}\s\-']/u.test(e.key) && e.key.length === 1) e.preventDefault(); });
                inp.addEventListener('input', function() { this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-']/g, ''); });
                inp.addEventListener('blur', function() { var v = this.value.trim(); if (v.length < 2) addError(this, 'Minimum 2 caractères.'); else addOk(this); });
            });

            document.querySelectorAll('input[type="tel"], input[name="telephone"]').forEach(function(inp) {
                inp.setAttribute('inputmode', 'tel');
                inp.setAttribute('maxlength', '20');
                inp.addEventListener('keypress', function(e) { if (!/[0-9+\s\-().]/.test(e.key) && e.key.length === 1) e.preventDefault(); });
                inp.addEventListener('input', function() { var clean = this.value.replace(/[^0-9+\s\-(). ]/g, ''); if (this.value !== clean) this.value = clean; });
                inp.addEventListener('blur', function() {
                    var v = this.value.trim();
                    if (v.length === 0 && !this.required) { removeError(this); return; }
                    if (v.length < 7 || !/^[0-9+\s\-().]+$/.test(v)) addError(this, 'Numéro invalide. Utilisez uniquement des chiffres.');
                    else addOk(this);
                });
            });

            document.querySelectorAll('input[type="email"]').forEach(function(inp) {
                inp.addEventListener('blur', function() {
                    var v = this.value.trim();
                    if (!v) { removeError(this); return; }
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) addError(this, 'Adresse email invalide.');
                    else addOk(this);
                });
                inp.addEventListener('input', function() { removeError(this); this.style.borderColor = ''; });
            });

            var dob = document.querySelector('input[name="date_naissance"]');
            if (dob) {
                var today = new Date();
                var maxDate = new Date(today.getFullYear() - 16, today.getMonth(), today.getDate());
                var minDate = new Date(today.getFullYear() - 100, today.getMonth(), today.getDate());
                dob.setAttribute('max', maxDate.toISOString().split('T')[0]);
                dob.setAttribute('min', minDate.toISOString().split('T')[0]);
                dob.addEventListener('blur', function() {
                    var v = this.value;
                    if (!v) { removeError(this); return; }
                    var d = new Date(v);
                    if (d > maxDate) addError(this, 'Vous devez avoir au moins 16 ans.');
                    else if (d < minDate) addError(this, 'Date invalide.');
                    else addOk(this);
                });
            }

            var motiv = document.querySelector('textarea[name="motivations"]');
            if (motiv) {
                var counter = document.createElement('span');
                counter.style.cssText = 'font-size:12px;color:#6B7280;display:block;margin-top:4px;text-align:right;';
                counter.textContent = '0 / 50 caractères minimum';
                motiv.parentNode.appendChild(counter);
                motiv.addEventListener('input', function() {
                    var len = this.value.length;
                    counter.textContent = len + ' / 50 caractères minimum';
                    counter.style.color = len >= 50 ? '#16A34A' : '#6B7280';
                    if (len >= 50) removeError(this);
                });
                motiv.addEventListener('blur', function() {
                    if (this.value.length > 0 && this.value.length < 50) addError(this, 'Décrivez vos motivations (minimum 50 caractères).');
                    else if (this.value.length >= 50) addOk(this);
                });
            }

            var dispo = document.querySelector('input[name="disponibilites"]');
            if (dispo) dispo.setAttribute('maxlength', '200');

            document.querySelectorAll('input[name="profession"]').forEach(function(inp) {
                inp.setAttribute('maxlength', '80');
                inp.addEventListener('input', function() { this.value = this.value.replace(/[^a-zA-ZÀ-ÿ0-9\s\-',.()]/g, ''); });
            });
        })();
    </script>
</body>

</html>
