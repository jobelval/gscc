<?php
// demande-aide.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$page_title       = "Demande d'aide";
$page_description = 'Formulaire de demande de soutien financier, médical ou psychologique.';

$est_connecte = function_exists('isLoggedIn') ? isLoggedIn() : isset($_SESSION['user_id']);
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $est_connecte) {

    $type_aide   = trim(strip_tags($_POST['type_aide']   ?? ''));
    $description = trim(strip_tags($_POST['description'] ?? ''));
    $documents   = [];

    if (empty($type_aide)) {
        $error = "Veuillez sélectionner un type d'aide.";
    } elseif (empty($description)) {
        $error = 'Veuillez décrire votre demande.';
    } elseif (strlen($description) < 50) {
        $error = 'Veuillez fournir une description plus détaillée (minimum 50 caractères).';
    } else {

        /* ── Upload fichiers ── */
        $upload_dir = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\')
            . DIRECTORY_SEPARATOR . 'uploads'
            . DIRECTORY_SEPARATOR . 'demandes'
            . DIRECTORY_SEPARATOR;
        if (!file_exists($upload_dir)) @mkdir($upload_dir, 0755, true);

        if (!empty($_FILES['documents']['name'][0])) {
            foreach ($_FILES['documents']['tmp_name'] as $k => $tmp) {
                if ($_FILES['documents']['error'][$k] !== UPLOAD_ERR_OK) continue;
                if ($_FILES['documents']['size'][$k] > 5 * 1024 * 1024) continue;
                $ext = strtolower(pathinfo($_FILES['documents']['name'][$k], PATHINFO_EXTENSION));
                if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) continue;
                $fname = uniqid('doc_', true) . '.' . $ext;
                if (@move_uploaded_file($tmp, $upload_dir . $fname)) {
                    $documents[] = $fname;
                }
            }
        }

        /* ── INSERT en base ── */
        try {
            // Récupérer user_id depuis la session et vérifier qu'il existe en base
            $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
            if ($uid) {
                $chk = $pdo->prepare("SELECT id FROM utilisateurs WHERE id = ? LIMIT 1");
                $chk->execute([$uid]);
                if (!$chk->fetch()) $uid = null;
            }

            $docs_json = !empty($documents) ? json_encode($documents) : null;

            $stmt = $pdo->prepare(
                "INSERT INTO demandes_aide
                    (utilisateur_id, type_aide, description_demande,
                     documents_justificatifs, statut, date_soumission)
                 VALUES (?, ?, ?, ?, 'soumis', NOW())"
            );
            $stmt->execute([$uid, $type_aide, $description, $docs_json]);

            $success = "Votre demande a été soumise avec succès. Nous vous contacterons dans les plus brefs délais.";
            $_POST   = [];

            // Emails non bloquants
            try {
                $unom   = trim(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? ''));
                $uemail = $_SESSION['user_email'] ?? '';
                $sname  = defined('SITE_NAME')  ? SITE_NAME  : 'GSCC';
                $semail = defined('SITE_EMAIL') ? SITE_EMAIL : '';
                if ($uemail && function_exists('sendEmail'))
                    sendEmail(
                        $uemail,
                        "Confirmation demande d'aide — $sname",
                        "Bonjour $unom,\n\nVotre demande ($type_aide) a bien été reçue.\nNous vous contacterons très prochainement.\n\nL'équipe $sname"
                    );
                if ($semail && function_exists('sendEmail'))
                    sendEmail(
                        $semail,
                        "Nouvelle demande d'aide — $sname",
                        "Demande de $unom ($uemail) — Type : $type_aide"
                    );
            } catch (Exception $ignored) {
            }
        } catch (Exception $e) {
            $error = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }
}

$csrf_token = function_exists('generateCSRFToken') ? generateCSRFToken() : bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — <?= defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'GSCC' ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        /* =====================================================
   GSCC · Demande d'aide
   Couleurs originales : Bleu #003399 · Vert #4CAF50
   Design sobre & professionnel
===================================================== */
        :root {
            --blue: #003399;
            --blue-2: #002277;
            --blue-lite: #EBF0FF;
            --green: #4CAF50;
            --green-2: #388E3C;
            --green-lite: #E8F5E9;
            --orange: #E67E22;
            --orange-lite: #FEF3E2;
            --cream: #F7F8FC;
            --white: #FFFFFF;
            --text: #1C1C2E;
            --text-2: #44445A;
            --text-3: #8A8AAA;
            --border: #E4E6F0;
            --r: 14px;
            --r2: 10px;
            --sh: 0 2px 16px rgba(0, 51, 153, .07);
            --sh2: 0 8px 32px rgba(0, 51, 153, .11);
            --ease: cubic-bezier(.4, 0, .2, 1);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--cream);
            color: var(--text);
            line-height: 1.6;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 28px;
        }

        /* ── HERO ─────────────────────────────────────────── */
        .page-hero {
            background: var(--blue);
            padding: 90px 0 72px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }



        /* ligne verte en bas — couleur du site */
        .page-hero::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--green);
        }

        .page-hero .container {
            position: relative;
            z-index: 1;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(255, 255, 255, .18);
            color: rgba(255, 255, 255, .70);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 6px 18px;
            border-radius: 99px;
            margin-bottom: 24px;
        }

        .page-hero h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(2.6rem, 5vw, 4.2rem);
            font-weight: 700;
            color: var(--white);
            line-height: 1.1;
            margin-bottom: 16px;
        }

        .page-hero p {
            font-size: 1rem;
            font-weight: 300;
            color: rgba(255, 255, 255, .60);
            max-width: 480px;
            margin: 0 auto;
        }

        /* ── SECTION ──────────────────────────────────────── */
        .main-section {
            padding: 72px 0 100px;
        }

        /* ── LAYOUT 2 COLONNES ────────────────────────────── */
        .aide-layout {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 32px;
            align-items: start;
        }

        /* ── CARTE GÉNÉRIQUE ──────────────────────────────── */
        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r);
            box-shadow: var(--sh);
            overflow: hidden;
        }

        /* ── FORMULAIRE ───────────────────────────────────── */
        .form-card {
            padding: 40px 44px;
        }

        .card-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.65rem;
            font-weight: 700;
            color: var(--blue);
            margin-bottom: 6px;
        }

        .card-rule {
            width: 44px;
            height: 3px;
            border-radius: 99px;
            background: var(--green);
            margin-bottom: 32px;
        }

        /* alertes */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 15px 18px;
            border-radius: var(--r2);
            margin-bottom: 26px;
            font-size: 14px;
            line-height: 1.6;
        }

        .alert i {
            font-size: 17px;
            margin-top: 1px;
            flex-shrink: 0;
        }

        .alert-success {
            background: #E8F5E9;
            color: #1B5E20;
            border: 1px solid #A5D6A7;
        }

        .alert-success i {
            color: var(--green-2);
        }

        .alert-error {
            background: #FEECEC;
            color: #7A1010;
            border: 1px solid #FFCDD2;
        }

        .alert-error i {
            color: #D32F2F;
        }

        /* séparateurs sections */
        .form-sep {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text-3);
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 28px 0 20px;
        }

        .form-sep::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* champs */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 7px;
            font-size: 13.5px;
            font-weight: 500;
            color: var(--text-2);
        }

        .form-label .req {
            color: var(--blue);
            margin-left: 2px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1.5px solid var(--border);
            border-radius: var(--r2);
            font-family: 'Outfit', sans-serif;
            font-size: 14.5px;
            color: var(--text);
            background: var(--cream);
            transition: all .3s var(--ease);
            outline: none;
        }

        .form-control:focus {
            border-color: var(--blue);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(0, 51, 153, .08);
        }

        textarea.form-control {
            min-height: 148px;
            resize: vertical;
        }

        .form-hint {
            font-size: 12px;
            color: var(--text-3);
            margin-top: 5px;
        }

        /* compteur description */
        .desc-counter {
            text-align: right;
            font-size: 12px;
            color: var(--text-3);
            margin-top: 4px;
            transition: color .2s;
        }

        .desc-counter.ok {
            color: var(--green-2);
            font-weight: 600;
        }

        .desc-counter.warn {
            color: #D32F2F;
        }

        /* niveau d'urgence — cards cliquables */
        .urgence-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .urgence-card {
            flex: 1;
            min-width: 110px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px;
            border: 1.5px solid var(--border);
            border-radius: var(--r2);
            cursor: pointer;
            transition: all .3s var(--ease);
            background: var(--cream);
        }

        .urgence-card input[type="radio"] {
            display: none;
        }

        .urgence-card:hover {
            border-color: var(--blue);
            background: var(--white);
        }

        .urgence-card.selected-normale {
            border-color: var(--green);
            background: var(--green-lite);
        }

        .urgence-card.selected-urgent {
            border-color: var(--orange);
            background: var(--orange-lite);
        }

        .urgence-card.selected-tres_urgent {
            border-color: #D32F2F;
            background: #FEECEC;
        }

        .urgence-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .urgence-card[data-val="normale"] .urgence-dot {
            background: var(--green);
        }

        .urgence-card[data-val="urgent"] .urgence-dot {
            background: var(--orange);
        }

        .urgence-card[data-val="tres_urgent"] .urgence-dot {
            background: #D32F2F;
        }

        .urgence-label {
            font-size: 13.5px;
            font-weight: 500;
            color: var(--text-2);
        }

        /* zone upload */
        .file-drop {
            border: 2px dashed var(--border);
            border-radius: var(--r2);
            padding: 32px 24px;
            text-align: center;
            cursor: pointer;
            transition: all .3s var(--ease);
            background: var(--cream);
        }

        .file-drop:hover {
            border-color: var(--blue);
            background: var(--blue-lite);
        }

        .file-drop i {
            font-size: 36px;
            color: var(--blue);
            margin-bottom: 10px;
            display: block;
        }

        .file-drop p {
            font-size: 14px;
            color: var(--text-2);
            margin-bottom: 4px;
        }

        .file-drop small {
            font-size: 12px;
            color: var(--text-3);
        }

        .file-list {
            margin-top: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--blue-lite);
            border: 1px solid rgba(0, 51, 153, .12);
            border-radius: 8px;
            padding: 9px 14px;
            font-size: 13.5px;
            color: var(--blue);
        }

        .file-item button {
            background: none;
            border: none;
            color: #D32F2F;
            cursor: pointer;
            font-size: 14px;
            padding: 0 2px;
            transition: transform .2s;
        }

        .file-item button:hover {
            transform: scale(1.2);
        }

        /* bouton submit */
        .btn-submit {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 15px 32px;
            margin-top: 28px;
            background: var(--blue);
            color: var(--white);
            border: none;
            border-radius: 99px;
            font-family: 'Outfit', sans-serif;
            font-size: 15.5px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(0, 51, 153, .25);
            transition: all .3s var(--ease);
        }

        .btn-submit:hover {
            background: var(--blue-2);
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(0, 51, 153, .32);
        }

        /* ── SIDEBAR ──────────────────────────────────────── */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
            position: sticky;
            top: 100px;
        }

        .sidebar-card {
            padding: 28px 26px;
        }

        .sidebar-card+.sidebar-card {
            margin-top: 0;
        }

        .sidebar-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--blue);
            margin-bottom: 6px;
        }

        .sidebar-rule {
            width: 36px;
            height: 3px;
            border-radius: 99px;
            background: var(--green);
            margin-bottom: 22px;
        }

        /* types d'aide */
        .aide-type {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 0;
            border-bottom: 1px solid var(--border);
        }

        .aide-type:last-of-type {
            border-bottom: none;
            padding-bottom: 0;
        }

        .aide-type-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: var(--blue-lite);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--blue);
            font-size: 17px;
            flex-shrink: 0;
        }

        .aide-type-text h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 3px;
        }

        .aide-type-text p {
            font-size: 12.5px;
            color: var(--text-3);
            line-height: 1.5;
        }

        /* contact urgence */
        .urgent-card {
            background: var(--orange-lite);
            border: 1px solid rgba(230, 126, 34, .25);
            border-radius: var(--r);
            padding: 24px 26px;
            position: relative;
            overflow: hidden;
        }

        .urgent-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--orange);
            border-radius: var(--r) 0 0 var(--r);
        }

        .urgent-card-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 700;
            color: var(--orange);
            margin-bottom: 14px;
        }

        .urgent-contact-row {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13.5px;
            color: var(--text-2);
            margin-bottom: 8px;
        }

        .urgent-contact-row i {
            color: var(--orange);
            width: 16px;
            text-align: center;
        }

        .urgent-contact-row strong {
            color: var(--text);
        }

        .urgent-note {
            font-size: 12px;
            color: var(--text-3);
            margin-top: 10px;
            line-height: 1.55;
        }

        /* engagement */
        .engagement-card {
            background: var(--blue-lite);
            border-color: rgba(0, 51, 153, .12);
        }

        .engagement-card .sidebar-title {
            color: var(--blue);
        }

        .engagement-card p {
            font-size: 13.5px;
            color: var(--text-2);
            line-height: 1.75;
        }

        .engagement-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: var(--text-2);
            margin-top: 12px;
        }

        .engagement-item i {
            color: var(--green);
            font-size: 14px;
        }

        /* ── CONNEXION REQUISE ────────────────────────────── */
        .login-prompt {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 72px 40px;
            text-align: center;
            box-shadow: var(--sh);
            max-width: 620px;
            margin: 0 auto;
        }

        .login-prompt-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--blue-lite);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: var(--blue);
            margin: 0 auto 24px;
        }

        .login-prompt h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--blue);
            margin-bottom: 12px;
        }

        .login-prompt p {
            font-size: 15px;
            color: var(--text-2);
            line-height: 1.75;
            margin-bottom: 32px;
            max-width: 440px;
            margin-left: auto;
            margin-right: auto;
        }

        .login-btns {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--blue);
            color: var(--white);
            padding: 13px 30px;
            border-radius: 99px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14.5px;
            transition: all .3s var(--ease);
            box-shadow: 0 6px 18px rgba(0, 51, 153, .22);
        }

        .btn-login:hover {
            background: var(--blue-2);
            transform: translateY(-2px);
        }

        .btn-register {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--green);
            color: var(--white);
            padding: 13px 30px;
            border-radius: 99px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14.5px;
            transition: all .3s var(--ease);
            box-shadow: 0 6px 18px rgba(76, 175, 80, .22);
        }

        .btn-register:hover {
            background: var(--green-2);
            transform: translateY(-2px);
        }

        /* ── RESPONSIVE ───────────────────────────────────── */
        @media(max-width:860px) {
            .aide-layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
            }

            .urgence-options {
                flex-direction: row;
            }
        }

        @media(max-width:580px) {
            .page-hero {
                padding: 64px 0 52px;
            }

            .form-card {
                padding: 28px 22px;
            }

            .urgence-options {
                flex-direction: column;
            }

            .login-prompt {
                padding: 48px 24px;
            }
        }
    </style>
</head>

<body>
    <?php require_once 'templates/header.php'; ?>

    <!-- ══ HERO ══ -->
    <header class="page-hero">
        <div class="container">
            <div class="hero-eyebrow" data-aos="fade-down">
                <i class="fas fa-hands-holding-heart"></i>
                GSCC — Soutien aux patients
            </div>
            <h1 data-aos="fade-up" data-aos-delay="80">Demande d'aide</h1>
            <p data-aos="fade-up" data-aos-delay="150">
                Nous sommes là pour vous accompagner à chaque étape de votre parcours.
            </p>
        </div>
    </header>

    <!-- ══ CONTENU ══ -->
    <section class="main-section">
        <div class="container">

            <?php if (!$est_connecte): ?>
                <!-- ── CONNEXION REQUISE ── -->
                <div class="login-prompt" data-aos="fade-up">
                    <div class="login-prompt-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3>Connexion requise</h3>
                    <p>
                        Pour soumettre une demande d'aide, vous devez être connecté à votre compte.
                        Si vous n'en avez pas encore, créez-en un gratuitement en quelques minutes.
                    </p>
                    <div class="login-btns">
                        <a href="connexion.php" class="btn-login">
                            <i class="fas fa-arrow-right-to-bracket"></i> Se connecter
                        </a>
                        <a href="inscription.php" class="btn-register">
                            <i class="fas fa-user-plus"></i> Créer un compte
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <!-- ── FORMULAIRE + SIDEBAR ── -->
                <div class="aide-layout">

                    <!-- Formulaire -->
                    <div class="card form-card" data-aos="fade-right">
                        <h2 class="card-title">Formulaire de demande</h2>
                        <div class="card-rule"></div>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-circle-check"></i>
                                <span><?= htmlspecialchars($success) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-triangle-exclamation"></i>
                                <span><?= htmlspecialchars($error) ?></span>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" id="aideForm" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                            <!-- Type d'aide -->
                            <p class="form-sep">Type de demande</p>
                            <div class="form-group">
                                <label class="form-label" for="type_aide">Type d'aide <span class="req">*</span></label>
                                <select name="type_aide" id="type_aide" class="form-control" required>
                                    <option value="">Sélectionnez un type d'aide…</option>
                                    <option value="financiere" <?= ($_POST['type_aide'] ?? '') === 'financiere'     ? 'selected' : '' ?>>Aide financière</option>
                                    <option value="medicale" <?= ($_POST['type_aide'] ?? '') === 'medicale'       ? 'selected' : '' ?>>Aide médicale</option>
                                    <option value="psychologique" <?= ($_POST['type_aide'] ?? '') === 'psychologique'  ? 'selected' : '' ?>>Soutien psychologique</option>
                                    <option value="accompagnement" <?= ($_POST['type_aide'] ?? '') === 'accompagnement' ? 'selected' : '' ?>>Accompagnement</option>
                                </select>
                            </div>

                            <!-- Niveau d'urgence -->
                            <p class="form-sep">Niveau d'urgence</p>
                            <div class="urgence-options" id="urgence-options">
                                <?php
                                $urgences = [
                                    'normale'     => ['label' => 'Normale',    'icon' => 'fa-circle-check'],
                                    'urgent'      => ['label' => 'Urgent',     'icon' => 'fa-circle-exclamation'],
                                    'tres_urgent' => ['label' => 'Très urgent', 'icon' => 'fa-triangle-exclamation'],
                                ];
                                $cur_urg = $_POST['urgence'] ?? 'normale';
                                foreach ($urgences as $val => $u):
                                    $sel = $cur_urg === $val ? "selected-$val" : '';
                                ?>
                                    <label class="urgence-card <?= $sel ?>" data-val="<?= $val ?>">
                                        <input type="radio" name="urgence" value="<?= $val ?>" <?= $cur_urg === $val ? 'checked' : '' ?>>
                                        <div class="urgence-dot"></div>
                                        <span class="urgence-label">
                                            <i class="fas <?= $u['icon'] ?>" style="margin-right:5px"></i><?= $u['label'] ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <!-- Description -->
                            <p class="form-sep">Description</p>
                            <div class="form-group">
                                <label class="form-label" for="description">
                                    Description détaillée <span class="req">*</span>
                                </label>
                                <textarea class="form-control" name="description" id="description" required
                                    placeholder="Décrivez votre situation, vos besoins, ce que vous attendez du GSCC…"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                <div class="desc-counter" id="desc-counter">0 / 50 caractères minimum</div>
                            </div>

                            <!-- Documents -->
                            <p class="form-sep">Documents (optionnel)</p>
                            <div class="form-group">
                                <div class="file-drop" id="file-drop">
                                    <i class="fas fa-cloud-arrow-up"></i>
                                    <p>Cliquez ou glissez vos fichiers ici</p>
                                    <small>PDF, JPG, PNG — Max 5 Mo par fichier</small>
                                </div>
                                <input type="file" name="documents[]" id="files" multiple
                                    style="display:none" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="file-list" id="fileList"></div>
                            </div>

                            <button type="submit" class="btn-submit">
                                <i class="fas fa-paper-plane"></i>
                                Soumettre ma demande
                            </button>
                        </form>
                    </div>

                    <!-- Sidebar -->
                    <aside class="sidebar" data-aos="fade-left">

                        <!-- Types d'aide -->
                        <div class="card sidebar-card">
                            <h3 class="sidebar-title">Types d'aide disponibles</h3>
                            <div class="sidebar-rule"></div>
                            <div class="aide-type">
                                <div class="aide-type-icon"><i class="fas fa-hand-holding-dollar"></i></div>
                                <div class="aide-type-text">
                                    <h4>Aide financière</h4>
                                    <p>Prise en charge des frais médicaux, médicaments et transports</p>
                                </div>
                            </div>
                            <div class="aide-type">
                                <div class="aide-type-icon"><i class="fas fa-stethoscope"></i></div>
                                <div class="aide-type-text">
                                    <h4>Aide médicale</h4>
                                    <p>Consultations spécialisées, examens et suivi médical</p>
                                </div>
                            </div>
                            <div class="aide-type">
                                <div class="aide-type-icon"><i class="fas fa-heart"></i></div>
                                <div class="aide-type-text">
                                    <h4>Soutien psychologique</h4>
                                    <p>Séances avec psychologue, groupes de parole, soutien aux proches</p>
                                </div>
                            </div>
                            <div class="aide-type">
                                <div class="aide-type-icon"><i class="fas fa-users"></i></div>
                                <div class="aide-type-text">
                                    <h4>Accompagnement</h4>
                                    <p>Aide administrative, orientation et accompagnement physique</p>
                                </div>
                            </div>
                        </div>

                        <!-- Urgence -->
                        <div class="urgent-card">
                            <div class="urgent-card-title">
                                <i class="fas fa-triangle-exclamation"></i>
                                Situation d'urgence ?
                            </div>
                            <div class="urgent-contact-row">
                                <i class="fas fa-phone"></i>
                                <span>Téléphone : <strong>+(509) 29 47 47 22</strong></span>
                            </div>
                            <div class="urgent-contact-row">
                                <i class="fas fa-envelope"></i>
                                <span>Email : <strong>urgence@gscc.org</strong></span>
                            </div>
                            <p class="urgent-note">
                                Pour les situations critiques, contactez directement notre permanence disponible 7j/7.
                            </p>
                        </div>

                        <!-- Engagement -->
                        <div class="card sidebar-card engagement-card">
                            <h3 class="sidebar-title">Notre engagement</h3>
                            <div class="sidebar-rule"></div>
                            <p>Chaque demande est traitée avec la plus grande attention et en toute confidentialité.</p>
                            <div class="engagement-item">
                                <i class="fas fa-circle-check"></i>
                                Réponse sous 48h ouvrées
                            </div>
                            <div class="engagement-item">
                                <i class="fas fa-circle-check"></i>
                                24h pour les urgences
                            </div>
                            <div class="engagement-item">
                                <i class="fas fa-circle-check"></i>
                                Traitement confidentiel garanti
                            </div>
                        </div>

                    </aside>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <?php require_once 'templates/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        AOS.init({
            duration: 600,
            once: true,
            offset: 50
        });

        /* ── Urgence cards ── */
        document.querySelectorAll('.urgence-card').forEach(card => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.urgence-card').forEach(c => {
                    c.className = 'urgence-card';
                });
                const val = card.dataset.val;
                card.classList.add('selected-' + val);
                card.querySelector('input[type="radio"]').checked = true;
            });
        });

        /* ── Compteur description ── */
        const descEl = document.getElementById('description');
        const counterEl = document.getElementById('desc-counter');
        if (descEl && counterEl) {
            function updateCounter() {
                const len = descEl.value.length;
                if (len < 50) {
                    counterEl.textContent = `${len} / 50 caractères minimum`;
                    counterEl.className = 'desc-counter warn';
                } else {
                    counterEl.textContent = `${len} caractères ✓`;
                    counterEl.className = 'desc-counter ok';
                }
            }
            descEl.addEventListener('input', updateCounter);
            updateCounter();
        }

        /* ── Upload fichiers ── */
        var selectedFiles = [];
        var dropZone = document.getElementById('file-drop');
        var fileInput = document.getElementById('files');
        var fileList = document.getElementById('fileList');

        if (dropZone) {
            dropZone.addEventListener('click', function() {
                if (fileInput) fileInput.click();
            });
            ['dragenter', 'dragover'].forEach(function(evt) {
                dropZone.addEventListener(evt, function(e) {
                    e.preventDefault();
                    dropZone.style.borderColor = 'var(--blue)';
                    dropZone.style.background = 'var(--blue-lite)';
                });
            });
            ['dragleave', 'drop'].forEach(function(evt) {
                dropZone.addEventListener(evt, function(e) {
                    e.preventDefault();
                    dropZone.style.borderColor = '';
                    dropZone.style.background = '';
                    if (evt === 'drop') addFiles(e.dataTransfer.files);
                });
            });
        }
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                addFiles(fileInput.files);
            });
        }

        function addFiles(newFiles) {
            var allowed = ['pdf', 'jpg', 'jpeg', 'png'];
            Array.from(newFiles).forEach(function(f) {
                var ext = f.name.split('.').pop().toLowerCase();
                if (allowed.indexOf(ext) === -1) return;
                if (f.size > 5 * 1024 * 1024) return;
                var exists = selectedFiles.some(function(sf) {
                    return sf.name === f.name && sf.size === f.size;
                });
                if (!exists) selectedFiles.push(f);
            });
            renderFileList();
            syncInput();
        }

        window.removeFile = function(idx) {
            selectedFiles.splice(idx, 1);
            renderFileList();
            syncInput();
        };

        function renderFileList() {
            if (!fileList) return;
            fileList.innerHTML = '';
            selectedFiles.forEach(function(file, i) {
                var item = document.createElement('div');
                item.className = 'file-item';
                item.innerHTML =
                    '<span><i class="fas fa-file" style="margin-right:8px"></i>' + file.name +
                    ' <span style="color:var(--text-3);font-size:12px">(' + (file.size / 1024).toFixed(1) + ' Ko)</span></span>' +
                    '<button type="button" onclick="removeFile(' + i + ')"><i class="fas fa-xmark"></i></button>';
                fileList.appendChild(item);
            });
        }

        function syncInput() {
            if (!fileInput) return;
            try {
                var dt = new DataTransfer();
                selectedFiles.forEach(function(f) {
                    dt.items.add(f);
                });
                fileInput.files = dt.files;
            } catch (e) {}
        }

        /* ── Validation avant soumission ── */
        var form = document.getElementById('aideForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                var desc = document.querySelector('textarea[name="description"]');
                if (desc && desc.value.trim().length < 50) {
                    e.preventDefault();
                    desc.focus();
                    if (counterEl) {
                        counterEl.className = 'desc-counter warn';
                        counterEl.textContent = desc.value.length + ' / 50 — Description trop courte';
                    }
                }
            });
        }
    </script>
</body>

</html>