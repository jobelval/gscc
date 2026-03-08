<?php
// faire-un-don.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$page_title       = 'Faire un don';
$page_description = 'Soutenez notre mission en faisant un don en dollars. Chaque geste compte dans la lutte contre le cancer.';

$success = '';
$error   = '';

// ── Traitement du formulaire ──────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['don'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $montant      = floatval($_POST['montant']             ?? 0);
        $montant_perso = floatval($_POST['montant_personnalise'] ?? 0);
        $type_don     = sanitize($_POST['type_don']            ?? 'ponctuel');
        $mode         = sanitize($_POST['mode_paiement']       ?? 'paypal');
        $nom          = sanitize($_POST['nom']                 ?? '');
        $email        = sanitize($_POST['email']               ?? '');
        $telephone    = sanitize($_POST['telephone']           ?? '');
        $commentaire  = sanitize($_POST['commentaire']         ?? '');
        $newsletter   = isset($_POST['newsletter']);
        $conditions   = isset($_POST['conditions']);

        if ($montant === 0.0 && $montant_perso > 0) $montant = $montant_perso;

        // Validation
        if ($montant < 1) {
            $error = 'Le montant minimum est de $1.';
        } elseif (empty($nom)) {
            $error = 'Veuillez entrer votre nom.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Adresse email invalide.';
        } elseif (!$conditions) {
            $error = 'Vous devez accepter les conditions générales.';
        } else {
            try {
                $uid = (function_exists('isLoggedIn') && isLoggedIn()) ? ($_SESSION['user_id'] ?? null) : null;
                $stmt = $pdo->prepare("
                    INSERT INTO dons
                        (utilisateur_id, montant, type_don, mode_paiement,
                         nom_donateur, email_donateur, telephone,
                         commentaire, newsletter, statut, date_don)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW())
                ");
                $stmt->execute([
                    $uid,
                    $montant,
                    $type_don,
                    $mode,
                    $nom,
                    $email,
                    $telephone,
                    $commentaire,
                    $newsletter ? 1 : 0
                ]);
                $don_id = $pdo->lastInsertId();

                $_SESSION['don_en_cours'] = [
                    'id'      => $don_id,
                    'montant' => $montant,
                    'devise'  => 'USD',
                    'nom'     => $nom,
                    'email'   => $email,
                ];

                // Redirection vers la page de confirmation
                header('Location: confirmation-don.php?don_id=' . $don_id . '&mode=' . urlencode($mode) . '&montant=' . $montant . '&nom=' . urlencode($nom));
                exit;
            } catch (PDOException $e) {
                logError("Erreur don: " . $e->getMessage());
                $error = "Erreur lors de l'enregistrement. Veuillez réessayer.";
            }
        }
    }
}

$csrf_token = generateCSRFToken();

// Montants prédéfinis en USD
$montants = [10, 25, 50, 100, 250];

// Pré-remplir si connecté
$user_nom   = function_exists('isLoggedIn') && isLoggedIn() ? ($_SESSION['user_nom']   ?? '') : '';
$user_email = function_exists('isLoggedIn') && isLoggedIn() ? ($_SESSION['user_email'] ?? '') : '';

// Campagne pré-sélectionnée (depuis levees-fonds.php)
$campagne = htmlspecialchars($_GET['campagne'] ?? '');
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        /* ── Variables couleurs originales ─── */
        :root {
            --blue: #003399;
            --blue-lite: rgba(0, 51, 153, .07);
            --green: #4CAF50;
            --pink: #FF69B4;
            --bg: #F3F4F6;
            --white: #ffffff;
            --text: #0D1117;
            --muted: #4B5563;
            --border: #D1D5DB;
            --ease: cubic-bezier(.4, 0, .2, 1);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        .container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* ── Page header ──────────────────── */
        .page-header {
            background: linear-gradient(135deg, var(--rose), var(--teal));
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .page-header h1 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            color: #FFFFFF;
            text-shadow: 0 1px 3px rgba(0,0,0,.3);
            margin-bottom: 10px;
        }

        .page-header p {
            font-size: 1.05rem;
            color: #E8F0FE;
            opacity: 1;
        }

        /* ── Alertes ──────────────────────── */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 15px 18px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 14px;
            line-height: 1.6;
        }

        .alert i {
            font-size: 17px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .alert-success {
            background: #D1FAE5;
            color: #065F46;
            font-weight: 500;
            border: 1.5px solid #6EE7B7;
        }

        .alert-error {
            background: #FEE2E2;
            color: #991B1B;
            font-weight: 500;
            border: 1.5px solid #FCA5A5;
        }

        /* ── Section principale ───────────── */
        .don-section {
            padding: 60px 0;
            background: var(--bg);
        }

        .don-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
        }

        /* ── Formulaire ───────────────────── */
        .don-form-container {
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .1);
        }

        .don-form-container h2 {
            color: var(--blue);
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
            font-size: 24px;
        }

        .don-form-container h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #003399, #FF69B4);
        }

        .don-form-container h3 {
            color: var(--text);
            font-size: 16px;
            margin-bottom: 14px;
            font-weight: 600;
        }

        /* Montants */
        .montant-section {
            margin-bottom: 28px;
        }

        .montants-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-bottom: 14px;
        }

        .montant-btn {
            background: var(--bg);
            border: 2px solid var(--border);
            border-radius: 10px;
            padding: 14px 8px;
            text-align: center;
            cursor: pointer;
            transition: all .3s var(--ease);
            font-weight: 700;
            font-size: 15px;
            color: var(--text);
            user-select: none;
        }

        .montant-btn:hover {
            border-color: var(--blue);
            background: var(--blue-lite);
        }

        .montant-btn.selected {
            background: var(--blue);
            border-color: var(--blue);
            color: white;
        }

        .montant-sub {
            display: block;
            font-size: 10px;
            font-weight: 500;
            color: #E8F0FE;
            opacity: 1;
            margin-top: 2px;
        }

        .montant-personnalise {
            position: relative;
            margin-top: 12px;
        }

        .montant-personnalise .currency-prefix {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-weight: 700;
            color: var(--blue);
            font-size: 16px;
            pointer-events: none;
        }

        .montant-personnalise input {
            width: 100%;
            padding: 14px 14px 14px 30px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            transition: all .3s var(--ease);
            outline: none;
            background: var(--bg);
        }

        .montant-personnalise input:focus {
            border-color: var(--blue);
            background: var(--white);
        }

        .montant-personnalise input::placeholder {
            color: #9CA3AF;
        }

        /* Type de don */
        .type-don {
            display: flex;
            gap: 12px;
            margin: 20px 0;
            padding: 14px;
            background: var(--bg);
            border-radius: 10px;
        }

        .type-don label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            transition: all .25s var(--ease);
            border: 1.5px solid transparent;
            flex: 1;
            justify-content: center;
        }

        .type-don label:has(input:checked) {
            background: var(--white);
            border-color: var(--blue);
            color: var(--blue);
            box-shadow: 0 2px 8px rgba(0, 51, 153, .12);
        }

        .type-don input[type="radio"] {
            display: none;
        }

        /* Moyens de paiement */
        .payment-methods {
            margin: 28px 0;
        }

        .payment-option {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 18px;
            border: 2px solid var(--border);
            border-radius: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all .3s var(--ease);
            background: var(--bg);
        }

        .payment-option:hover {
            border-color: var(--blue);
            background: var(--white);
        }

        .payment-option.selected {
            border-color: var(--blue);
            background: var(--blue-lite);
            box-shadow: 0 0 0 3px rgba(0, 51, 153, .08);
        }

        /* radio custom */
        .pay-radio {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #D1D5DB;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .25s;
        }

        .payment-option.selected .pay-radio {
            border-color: var(--blue);
            background: var(--blue);
        }

        .payment-option.selected .pay-radio::after {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: white;
            display: block;
        }

        /* logos paiement */
        .pay-logo {
            font-size: 28px;
            flex-shrink: 0;
            width: 36px;
            text-align: center;
        }

        .pay-logo.paypal {
            color: #003087;
        }

        .pay-logo.stripe {
            color: #635bff;
        }

        .pay-logo.bank {
            color: var(--green);
        }

        .pay-info strong {
            display: block;
            font-size: 15px;
            color: var(--text);
            margin-bottom: 2px;
        }

        .pay-info small {
            font-size: 12.5px;
            color: var(--muted);
        }

        /* ── Coordonnées bancaires ─────────── */
        .bank-details {
            background: #f0f4ff;
            border: 1px solid rgba(0, 51, 153, .15);
            border-left: 4px solid var(--blue);
            border-radius: 10px;
            padding: 22px 24px;
            margin: 22px 0;
        }

        .bank-details h4 {
            color: var(--blue);
            margin-bottom: 14px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .bank-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(0, 51, 153, .08);
            font-size: 14px;
        }

        .bank-row:last-of-type {
            border-bottom: none;
        }

        .bank-row .label {
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .bank-row .value {
            font-family: monospace;
            font-weight: 600;
            color: var(--text);
            font-size: 14px;
        }

        .bank-row .copy-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--blue);
            font-size: 13px;
            padding: 2px 6px;
            border-radius: 4px;
            transition: background .2s;
        }

        .bank-row .copy-btn:hover {
            background: rgba(0, 51, 153, .1);
        }

        .bank-note {
            margin-top: 12px;
            font-size: 13px;
            color: var(--muted);
            font-style: italic;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        /* ── Champs formulaire ─────────────── */
        .form-sep {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #6B7280;
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text);
            font-size: 14px;
        }

        .form-group label .req {
            color: var(--blue);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: all .3s var(--ease);
            outline: none;
            background: var(--bg);
        }

        .form-control:focus {
            border-color: var(--blue);
            background: var(--white);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Checkboxes */
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 14px 0;
            font-size: 14px;
            color: var(--muted);
        }

        .checkbox-group input[type="checkbox"] {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
            margin-top: 2px;
            accent-color: var(--blue);
            cursor: pointer;
        }

        .checkbox-group a {
            color: var(--blue);
        }

        /* Badges sécurité */
        .security-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-top: 22px;
            padding: 16px;
            background: var(--bg);
            border-radius: 10px;
            flex-wrap: wrap;
        }

        .sec-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--muted);
            font-weight: 500;
        }

        .sec-badge i {
            color: var(--green);
            font-size: 15px;
        }

        /* Bouton don */
        .btn-don {
            width: 100%;
            background: linear-gradient(135deg, var(--rose), var(--teal));
            color: white;
            border: none;
            padding: 18px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 20px;
            transition: all .3s var(--ease);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 6px 20px rgba(0, 51, 153, .25);
            font-family: 'Inter', sans-serif;
        }

        .btn-don:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0, 51, 153, .35);
        }

        .btn-don:active {
            transform: translateY(0);
        }

        /* ── Sidebar impact ────────────────── */
        .don-info {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .1);
            position: sticky;
            top: 100px;
        }

        .don-info h3 {
            color: var(--blue);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border);
            font-size: 18px;
        }

        .impact-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 0;
            border-bottom: 1px solid var(--border);
        }

        .impact-item:last-of-type {
            border-bottom: none;
        }

        .impact-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--blue-lite);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--blue);
            font-size: 19px;
            flex-shrink: 0;
        }

        .impact-text h4 {
            color: var(--text);
            margin-bottom: 3px;
            font-size: 15px;
            font-weight: 700;
        }

        .impact-text p {
            color: var(--muted);
            font-size: 13px;
            margin: 0;
        }

        /* mini témoignage */
        .testimonial-mini {
            background: var(--bg);
            border-radius: 10px;
            padding: 18px;
            margin-top: 20px;
            font-style: italic;
            border-left: 3px solid var(--blue);
        }

        .testimonial-mini .fa-quote-left {
            color: var(--blue);
            opacity: .3;
            font-size: 18px;
            margin-bottom: 8px;
            display: block;
        }

        .testimonial-mini p {
            color: var(--muted);
            line-height: 1.6;
            font-size: 13.5px;
        }

        .testimonial-mini .author {
            margin-top: 10px;
            font-weight: 600;
            font-style: normal;
            color: var(--text);
            font-size: 13px;
        }

        .fiscal-note {
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            color: var(--muted);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .fiscal-note i {
            color: var(--green);
        }

        /* ── Responsive ────────────────────── */
        @media(max-width:860px) {
            .don-layout {
                grid-template-columns: 1fr;
            }

            .don-info {
                position: static;
            }

            .montants-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media(max-width:580px) {
            .page-header {
                padding: 48px 0;
            }

            .don-form-container {
                padding: 24px 18px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .montants-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .type-don {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>

<body>
    <?php require_once 'templates/header.php'; ?>

    <!-- ══ EN-TÊTE ══ -->
    <div class="page-header">
        <div class="container">
            <h1 data-aos="fade-up">Faire un don</h1>
            <p data-aos="fade-up" data-aos-delay="100">Votre générosité peut sauver des vies. Chaque dollar compte.</p>
        </div>
    </div>

    <!-- ══ SECTION DON ══ -->
    <section class="don-section">
        <div class="container">

            <?php if ($success): ?>
                <div class="alert alert-success" data-aos="fade-up">
                    <i class="fas fa-circle-check"></i>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success && isset($_POST['mode_paiement']) && $_POST['mode_paiement'] === 'virement'): ?>
                <!-- ── Coordonnées bancaires (virement) ── -->
                <div class="bank-details" data-aos="fade-up">
                    <h4><i class="fas fa-university"></i> Coordonnées bancaires pour le virement en USD</h4>

                    <div class="bank-row">
                        <span class="label">Banque</span>
                        <span class="value">Banque Nationale de Crédit (BNC)</span>
                    </div>
                    <div class="bank-row">
                        <span class="label">Titulaire</span>
                        <span class="value">GSCC — Groupe de Support Contre le Cancer</span>
                    </div>
                    <div class="bank-row">
                        <span class="label">Compte USD</span>
                        <span class="value" id="iban-val"><!-- REMPLACER : votre N° de compte USD --></span>
                        <button class="copy-btn" onclick="copyText('iban-val')"><i class="fas fa-copy"></i></button>
                    </div>
                    <div class="bank-row">
                        <span class="label">SWIFT / BIC</span>
                        <span class="value" id="swift-val"><!-- REMPLACER : votre code SWIFT --></span>
                        <button class="copy-btn" onclick="copyText('swift-val')"><i class="fas fa-copy"></i></button>
                    </div>
                    <div class="bank-row">
                        <span class="label">ABA / Routing (si USA)</span>
                        <span class="value" id="aba-val"><!-- REMPLACER : votre ABA routing number --></span>
                        <button class="copy-btn" onclick="copyText('aba-val')"><i class="fas fa-copy"></i></button>
                    </div>
                    <div class="bank-row">
                        <span class="label">Référence</span>
                        <span class="value" id="ref-val">DON-<?= date('Ymd') ?>-<?= htmlspecialchars(strtoupper(substr(sanitize($_POST['nom'] ?? 'DONATEUR'), 0, 10))) ?></span>
                        <button class="copy-btn" onclick="copyText('ref-val')"><i class="fas fa-copy"></i></button>
                    </div>

                    <p class="bank-note">
                        <i class="fas fa-circle-info"></i>
                        Après votre virement, envoyez une preuve de paiement à <strong>dons@gscc.org</strong>.
                        Votre don sera confirmé sous 48h ouvrées.
                    </p>
                </div>
            <?php endif; ?>

            <div class="don-layout">

                <!-- ── FORMULAIRE ── -->
                <div class="don-form-container" data-aos="fade-right">
                    <h2>Je fais un don</h2>

                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-triangle-exclamation"></i>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="donForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="don" value="1">
                        <input type="hidden" name="montant" id="montant" value="0">
                        <?php if ($campagne): ?>
                            <input type="hidden" name="campagne" value="<?= $campagne ?>">
                        <?php endif; ?>

                        <!-- Montant -->
                        <div class="montant-section">
                            <h3>Choisissez votre montant</h3>
                            <div class="montants-grid">
                                <?php
                                // Descriptions d'impact rapide
                                $impacts = [
                                    10  => 'Fournitures',
                                    25  => '½ consultation',
                                    50  => 'Médicaments',
                                    100 => 'Consultation',
                                    250 => '1 mois suivi',
                                ];
                                foreach ($montants as $m): ?>
                                    <div class="montant-btn" data-val="<?= $m ?>" onclick="selectMontant(<?= $m ?>, this)">
                                        $<?= number_format($m) ?>
                                        <span class="montant-sub"><?= $impacts[$m] ?? '' ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="montant-personnalise">
                                <span class="currency-prefix">$</span>
                                <input type="number" name="montant_personnalise" id="montant_personnalise"
                                    placeholder="Autre montant (min. $1)" min="1" step="1"
                                    oninput="clearPreset(this)">
                            </div>
                        </div>

                        <!-- Type de don -->
                        <h3>Fréquence du don</h3>
                        <div class="type-don">
                            <label>
                                <input type="radio" name="type_don" value="ponctuel" checked>
                                <i class="fas fa-hand-holding-heart"></i> Ponctuel
                            </label>
                            <label>
                                <input type="radio" name="type_don" value="mensuel">
                                <i class="fas fa-calendar-alt"></i> Mensuel
                            </label>
                            <label>
                                <input type="radio" name="type_don" value="annuel">
                                <i class="fas fa-calendar-check"></i> Annuel
                            </label>
                        </div>

                        <!-- Mode de paiement -->
                        <div class="payment-methods">
                            <h3>Mode de paiement</h3>

                            <!--
                        ╔══════════════════════════════════════════════════════╗
                        ║  CONFIGURATION DES PASSERELLES DE PAIEMENT          ║
                        ╠══════════════════════════════════════════════════════╣
                        ║  PAYPAL                                              ║
                        ║  1. Créez un compte PayPal Business sur paypal.com   ║
                        ║  2. Dans includes/config.php, ajoutez :              ║
                        ║     define('PAYPAL_CLIENT_ID', 'votre_client_id');   ║
                        ║     define('PAYPAL_SECRET',    'votre_secret');      ║
                        ║     define('PAYPAL_MODE', 'live'); // ou 'sandbox'   ║
                        ║  3. Dans paiement/paypal.php, utilisez le SDK PHP    ║
                        ║     PayPal ou l'API REST v2 pour créer l'ordre       ║
                        ║                                                      ║
                        ║  STRIPE                                              ║
                        ║  1. Créez un compte sur dashboard.stripe.com         ║
                        ║  2. Dans includes/config.php, ajoutez :              ║
                        ║     define('STRIPE_PUBLIC_KEY','pk_live_xxx');       ║
                        ║     define('STRIPE_SECRET_KEY','sk_live_xxx');       ║
                        ║  3. Dans paiement/stripe.php, utilisez               ║
                        ║     Stripe Checkout ou Payment Intents API           ║
                        ║     (composer require stripe/stripe-php)             ║
                        ║                                                      ║
                        ║  VIREMENT BANCAIRE                                   ║
                        ║  Remplissez vos coordonnées bancaires dans           ║
                        ║  la section "bank-details" ci-dessus                 ║
                        ╚══════════════════════════════════════════════════════╝
                        -->

                            <label class="payment-option selected" data-method="paypal">
                                <input type="radio" name="mode_paiement" value="paypal" checked style="display:none">
                                <div class="pay-radio"></div>
                                <i class="fab fa-paypal pay-logo paypal"></i>
                                <div class="pay-info">
                                    <strong>PayPal</strong>
                                    <small>Paiement sécurisé — compte PayPal ou carte</small>
                                </div>
                            </label>

                            <label class="payment-option" data-method="stripe">
                                <input type="radio" name="mode_paiement" value="stripe" style="display:none">
                                <div class="pay-radio"></div>
                                <i class="fas fa-credit-card pay-logo stripe"></i>
                                <div class="pay-info">
                                    <strong>Carte bancaire (Stripe)</strong>
                                    <small>Visa · Mastercard · American Express</small>
                                </div>
                            </label>

                            <label class="payment-option" data-method="virement">
                                <input type="radio" name="mode_paiement" value="virement" style="display:none">
                                <div class="pay-radio"></div>
                                <i class="fas fa-building-columns pay-logo bank"></i>
                                <div class="pay-info">
                                    <strong>Virement bancaire</strong>
                                    <small>Traitement sous 48h ouvrées</small>
                                </div>
                            </label>

                        </div>

                        <!-- Informations personnelles -->
                        <p class="form-sep">Vos informations</p>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nom">Nom complet <span class="req">*</span></label>
                                <input type="text" class="form-control" id="nom" name="nom"
                                    value="<?= htmlspecialchars($user_nom) ?>"
                                    autocomplete="name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email <span class="req">*</span></label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= htmlspecialchars($user_email) ?>"
                                    autocomplete="email" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone"
                                autocomplete="tel">
                        </div>

                        <div class="form-group">
                            <label for="commentaire">Message (optionnel)</label>
                            <textarea class="form-control" id="commentaire" name="commentaire"
                                rows="3" placeholder="Un mot pour accompagner votre don…"></textarea>
                        </div>

                        <!-- Options -->
                        <div class="checkbox-group">
                            <input type="checkbox" name="newsletter" id="newsletter" checked>
                            <label for="newsletter">Je souhaite recevoir la newsletter du GSCC</label>
                        </div>
                        <div class="checkbox-group">
                            <input type="checkbox" name="conditions" id="conditions" required>
                            <label for="conditions">
                                J'accepte les <a href="conditions-utilisation.php" target="_blank" rel="noopener">conditions générales</a> <span class="req">*</span>
                            </label>
                        </div>

                        <!-- Sécurité -->
                        <div class="security-row">
                            <div class="sec-badge"><i class="fas fa-lock"></i> Paiement sécurisé SSL</div>
                            <div class="sec-badge"><i class="fas fa-shield-halved"></i> Données protégées</div>
                            <div class="sec-badge"><i class="fas fa-circle-check"></i> 100% des dons reversés</div>
                        </div>

                        <!-- Bouton -->
                        <button type="submit" class="btn-don">
                            <i class="fas fa-heart"></i>
                            Donner <span id="montantDisplay">$0</span>
                        </button>
                    </form>
                </div>

                <!-- ── SIDEBAR ── -->
                <div class="don-info" data-aos="fade-left">
                    <h3>L'impact de votre don</h3>

                    <div class="impact-item">
                        <div class="impact-icon"><i class="fas fa-stethoscope"></i></div>
                        <div class="impact-text">
                            <h4>$10</h4>
                            <p>Fournitures médicales pour un patient</p>
                        </div>
                    </div>
                    <div class="impact-item">
                        <div class="impact-icon"><i class="fas fa-pills"></i></div>
                        <div class="impact-text">
                            <h4>$25</h4>
                            <p>Finance une demi-consultation spécialisée</p>
                        </div>
                    </div>
                    <div class="impact-item">
                        <div class="impact-icon"><i class="fas fa-heartbeat"></i></div>
                        <div class="impact-text">
                            <h4>$100</h4>
                            <p>Consultation médicale complète</p>
                        </div>
                    </div>
                    <div class="impact-item">
                        <div class="impact-icon"><i class="fas fa-ambulance"></i></div>
                        <div class="impact-text">
                            <h4>$250</h4>
                            <p>Un mois de suivi et traitement complet</p>
                        </div>
                    </div>

                    <div class="testimonial-mini">
                        <i class="fas fa-quote-left"></i>
                        <p>"Grâce aux dons, j'ai pu suivre mon traitement dans de bonnes conditions. Merci du fond du cœur."</p>
                        <div class="author">— Marie, patiente accompagnée</div>
                    </div>

                    <div class="fiscal-note">
                        <i class="fas fa-receipt"></i>
                        Reçu fiscal disponible sur demande
                    </div>
                </div>

            </div>
        </div>
    </section>

    <?php require_once 'templates/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        let montantSelectionne = 0;

        /* ── Sélection montant preset ── */
        function selectMontant(val, el) {
            montantSelectionne = val;
            document.getElementById('montant').value = val;
            document.getElementById('montant_personnalise').value = '';
            updateDisplay(val);

            document.querySelectorAll('.montant-btn').forEach(b => b.classList.remove('selected'));
            el.classList.add('selected');
        }

        /* ── Montant libre ── */
        function clearPreset(input) {
            montantSelectionne = 0;
            document.getElementById('montant').value = 0;
            document.querySelectorAll('.montant-btn').forEach(b => b.classList.remove('selected'));
            const v = parseFloat(input.value);
            updateDisplay(isNaN(v) ? 0 : v);
        }

        /* ── Affichage bouton ── */
        function updateDisplay(val) {
            const el = document.getElementById('montantDisplay');
            el.textContent = val > 0 ? '$' + val.toLocaleString('en-US') : '$0';
        }

        /* ── Modes de paiement ── */
        document.querySelectorAll('.payment-option').forEach(opt => {
            opt.addEventListener('click', () => {
                document.querySelectorAll('.payment-option').forEach(o => {
                    o.classList.remove('selected');
                    o.querySelector('.pay-radio').style.cssText = '';
                });
                opt.classList.add('selected');
                opt.querySelector('input[type="radio"]').checked = true;
            });
        });

        /* ── Type de don — style actif ── */
        document.querySelectorAll('.type-don input[type="radio"]').forEach(r => {
            r.addEventListener('change', () => {
                // le CSS :has() gère le style, mais on force un repaint pour anciens navigateurs
                document.querySelectorAll('.type-don label').forEach(l => l.style.cssText = '');
            });
        });

        /* ── Copier dans le presse-papier ── */
        function copyText(id) {
            const txt = document.getElementById(id)?.textContent?.trim();
            if (!txt) return;
            navigator.clipboard.writeText(txt).then(() => {
                const btn = document.querySelector(`#${id} ~ .copy-btn`) ||
                    document.querySelector(`.bank-row:has(#${id}) .copy-btn`);
                if (btn) {
                    const orig = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-check"></i>';
                    setTimeout(() => btn.innerHTML = orig, 1500);
                }
            });
        }

        /* ── Validation avant soumission ── */
        document.getElementById('donForm').addEventListener('submit', function(e) {
            const montant = parseFloat(document.getElementById('montant').value) ||
                parseFloat(document.getElementById('montant_personnalise').value) || 0;
            if (montant < 1) {
                e.preventDefault();
                alert('Veuillez sélectionner ou saisir un montant (minimum $1).');
                return;
            }
            // Synchroniser le champ caché avec le perso si besoin
            if (montant > 0 && document.getElementById('montant').value == 0) {
                document.getElementById('montant').value = montant;
            }
        });
    </script>
</body>

</html>