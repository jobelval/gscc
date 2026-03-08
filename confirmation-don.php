<?php
// confirmation-don.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$page_title = 'Merci pour votre don';

// Récupérer les infos depuis l'URL
$don_id  = isset($_GET['don_id'])  ? (int)$_GET['don_id']          : 0;
$mode    = isset($_GET['mode'])    ? sanitize($_GET['mode'])        : 'virement';
$montant = isset($_GET['montant']) ? floatval($_GET['montant'])     : 0;
$nom     = isset($_GET['nom'])     ? sanitize($_GET['nom'])         : 'Donateur';

// Récupérer les détails du don depuis la base si possible
$don = null;
if ($don_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM dons WHERE id = ?");
        $stmt->execute([$don_id]);
        $don = $stmt->fetch();
    } catch (PDOException $e) {
        logError("confirmation-don: " . $e->getMessage());
    }
}

// Utiliser les données de la base ou celles de l'URL
if ($don) {
    $montant = floatval($don['montant']);
    $mode    = $don['mode_paiement'];
    $nom     = $don['nom_donateur'] ?? $nom;
}

// Labels des modes de paiement
$mode_labels = [
    'paypal'   => 'PayPal',
    'stripe'   => 'Carte bancaire',
    'virement' => 'Virement bancaire',
];
$mode_label = $mode_labels[$mode] ?? ucfirst($mode);

// Icônes
$mode_icons = [
    'paypal'   => 'fab fa-paypal',
    'stripe'   => 'fas fa-credit-card',
    'virement' => 'fas fa-building-columns',
];
$mode_icon = $mode_icons[$mode] ?? 'fas fa-circle-check';

// Nettoyer la session don
unset($_SESSION['don_en_cours']);

// Référence unique
$reference = 'DON-' . date('Ymd') . '-' . str_pad($don_id, 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> — <?= SITE_NAME ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --blue: #003399;
            --blue-dark: #002270;
            --blue-soft: rgba(0, 51, 153, .08);
            --rose: #D94F7A;
            --green: #2E7D32;
            --green-soft: rgba(46, 125, 50, .08);
            --gray-bg: #F4F6FB;
            --gray-light: #EEF1F8;
            --gray-text: #4B5563;
            --border: #E5E9F2;
            --white: #FFFFFF;
            --dark: #1A2240;
            --radius: 16px;
            --shadow: 0 4px 24px rgba(0, 51, 153, .08);
            --shadow-h: 0 16px 48px rgba(0, 51, 153, .15);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--gray-bg);
            color: var(--dark);
            line-height: 1.6;
        }

        /* ── PAGE HEADER ── */
        .page-header {
            background: linear-gradient(135deg, #003399 0%, #1a56cc 60%, #1a7abf 100%);
            color: white;
            padding: 60px 0 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before,
        .page-header::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            opacity: .07;
            background: white;
            pointer-events: none;
        }

        .page-header::before {
            width: 420px;
            height: 420px;
            top: -160px;
            right: -80px;
        }

        .page-header::after {
            width: 260px;
            height: 260px;
            bottom: -100px;
            left: -60px;
        }

        .page-header-wave {
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            line-height: 0;
        }

        .page-header-wave svg {
            display: block;
        }

        /* ── SECTION ── */
        .confirmation-section {
            padding: 50px 0 80px;
            background: var(--gray-bg);
        }

        /* ── CARD PRINCIPALE ── */
        .confirm-card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow-h);
            overflow: hidden;
            max-width: 680px;
            margin: 0 auto;
        }

        /* Bandeau vert succès */
        .confirm-header {
            background: linear-gradient(135deg, #2E7D32, #43A047);
            padding: 40px 40px 32px;
            text-align: center;
            color: white;
        }

        .confirm-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, .2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 36px;
            border: 3px solid rgba(255, 255, 255, .4);
        }

        .confirm-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.9rem;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -.3px;
        }

        .confirm-header p {
            font-size: 1rem;
            color: #E8F0FE;
            opacity: 1;
            line-height: 1.6;
        }

        /* Corps */
        .confirm-body {
            padding: 36px 40px;
        }

        /* Récapitulatif */
        .recap-box {
            background: var(--gray-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px 28px;
            margin-bottom: 28px;
        }

        .recap-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--blue);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .recap-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }

        .recap-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .recap-row .label {
            color: var(--gray-text);
        }

        .recap-row .value {
            font-weight: 600;
            color: var(--dark);
        }

        .recap-row .value.montant-val {
            font-size: 22px;
            font-weight: 700;
            color: var(--green);
        }

        .recap-row .mode-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--blue-soft);
            color: var(--blue);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .recap-row .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fff8e1;
            color: #b45309;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .recap-row .ref {
            font-family: monospace;
            font-size: 13px;
            color: var(--gray-text);
            letter-spacing: .5px;
        }

        /* Infos virement (si mode = virement) */
        .virement-box {
            background: #f0f4ff;
            border: 1px solid rgba(0, 51, 153, .15);
            border-left: 4px solid var(--blue);
            border-radius: 10px;
            padding: 22px 24px;
            margin-bottom: 28px;
        }

        .virement-box h4 {
            color: var(--blue);
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 14px;
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

        .bank-row .bl {
            color: var(--gray-text);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .bank-row .bv {
            font-family: monospace;
            font-weight: 600;
            color: var(--dark);
            font-size: 13px;
        }

        .bank-row .copy-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--blue);
            font-size: 13px;
            padding: 2px 7px;
            border-radius: 4px;
            transition: background .2s;
        }

        .bank-row .copy-btn:hover {
            background: rgba(0, 51, 153, .1);
        }

        .bank-note {
            margin-top: 12px;
            font-size: 13px;
            color: var(--gray-text);
            font-style: italic;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        /* Prochaines étapes */
        .steps-box {
            margin-bottom: 28px;
        }

        .steps-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 16px;
        }

        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .step-item:last-child {
            border-bottom: none;
        }

        .step-num {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--blue-soft);
            color: var(--blue);
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .step-text strong {
            display: block;
            font-size: 14px;
            color: var(--dark);
            margin-bottom: 2px;
        }

        .step-text span {
            font-size: 13px;
            color: var(--gray-text);
        }

        /* Boutons d'action */
        .confirm-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            padding-top: 20px;
            border-top: 1px solid var(--gray-light);
        }

        .btn-primary {
            flex: 1;
            background: linear-gradient(135deg, var(--blue), #1a56cc);
            color: white;
            padding: 13px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all .2s;
            text-align: center;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 51, 153, .28);
            color: white;
        }

        .btn-secondary {
            flex: 1;
            background: var(--gray-bg);
            color: var(--dark);
            padding: 13px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1.5px solid var(--border);
            transition: all .2s;
            text-align: center;
        }

        .btn-secondary:hover {
            background: var(--gray-light);
            border-color: #D1D5DB;
        }



        /* ── RESPONSIVE ── */
        @media (max-width: 680px) {
            .confirm-body {
                padding: 24px 20px;
            }

            .confirm-header {
                padding: 30px 20px 24px;
            }

            .confirm-actions {
                flex-direction: column;
            }

            .recap-box {
                padding: 18px;
            }
        }
    </style>
</head>

<body>
    <?php include 'templates/header.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container" style="position:relative;z-index:1;"></div>
        <div class="page-header-wave">
            <svg viewBox="0 0 1440 48" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path fill="#F4F6FB" d="M0,48 C360,0 1080,0 1440,48 L1440,48 L0,48 Z" />
            </svg>
        </div>
    </div>

    <!-- Section confirmation -->
    <section class="confirmation-section">
        <div class="container">

            <div class="confirm-card" data-aos="fade-up">

                <!-- Bandeau vert -->
                <div class="confirm-header">
                    <div class="confirm-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h1>Merci, <?= e(explode(' ', trim($nom))[0]) ?> !</h1>
                    <p>Votre générosité fait une vraie différence dans la vie<br>des patients accompagnés par le GSCC.</p>
                </div>

                <!-- Corps -->
                <div class="confirm-body">

                    <!-- Récapitulatif -->
                    <div class="recap-box">
                        <div class="recap-title">
                            <i class="fas fa-receipt"></i> Récapitulatif de votre don
                        </div>

                        <div class="recap-row">
                            <span class="label">Montant</span>
                            <span class="value montant-val">$<?= number_format($montant, 2) ?></span>
                        </div>
                        <div class="recap-row">
                            <span class="label">Mode de paiement</span>
                            <span class="value">
                                <span class="mode-badge">
                                    <i class="<?= $mode_icon ?>"></i>
                                    <?= e($mode_label) ?>
                                </span>
                            </span>
                        </div>
                        <div class="recap-row">
                            <span class="label">Statut</span>
                            <span class="value">
                                <span class="status-badge">
                                    <i class="fas fa-clock"></i> En attente
                                </span>
                            </span>
                        </div>
                        <div class="recap-row">
                            <span class="label">Référence</span>
                            <span class="value ref" id="ref-val"><?= e($reference) ?></span>
                        </div>
                        <div class="recap-row">
                            <span class="label">Date</span>
                            <span class="value"><?= date('d/m/Y à H:i') ?></span>
                        </div>
                    </div>

                    <?php if ($mode === 'virement'): ?>
                        <!-- Coordonnées bancaires pour virement -->
                        <div class="virement-box">
                            <h4><i class="fas fa-university"></i> Coordonnées bancaires — Virement en USD</h4>
                            <div class="bank-row">
                                <span class="bl">Banque</span>
                                <span class="bv">Banque Nationale de Crédit (BNC)</span>
                            </div>
                            <div class="bank-row">
                                <span class="bl">Titulaire</span>
                                <span class="bv">GSCC — Groupe de Support Contre le Cancer</span>
                            </div>
                            <div class="bank-row">
                                <span class="bl">N° Compte USD</span>
                                <span class="bv" id="compte-val"><!-- Votre numéro de compte --></span>
                                <button class="copy-btn" onclick="copyText('compte-val')"><i class="fas fa-copy"></i></button>
                            </div>
                            <div class="bank-row">
                                <span class="bl">SWIFT / BIC</span>
                                <span class="bv" id="swift-val"><!-- Votre code SWIFT --></span>
                                <button class="copy-btn" onclick="copyText('swift-val')"><i class="fas fa-copy"></i></button>
                            </div>
                            <div class="bank-row">
                                <span class="bl">Référence à indiquer</span>
                                <span class="bv" id="vir-ref"><?= e($reference) ?></span>
                                <button class="copy-btn" onclick="copyText('vir-ref')"><i class="fas fa-copy"></i></button>
                            </div>
                            <p class="bank-note">
                                <i class="fas fa-circle-info"></i>
                                Après votre virement, envoyez la preuve de paiement à
                                <strong>dons@gscc.org</strong>. Votre don sera confirmé sous 48h ouvrées.
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Prochaines étapes -->
                    <div class="steps-box">
                        <p class="steps-title">Prochaines étapes</p>

                        <?php if ($mode === 'virement'): ?>
                            <div class="step-item">
                                <div class="step-num">1</div>
                                <div class="step-text">
                                    <strong>Effectuez votre virement</strong>
                                    <span>Utilisez les coordonnées bancaires ci-dessus en indiquant votre référence.</span>
                                </div>
                            </div>
                            <div class="step-item">
                                <div class="step-num">2</div>
                                <div class="step-text">
                                    <strong>Envoyez votre preuve de paiement</strong>
                                    <span>Transmettez le reçu de virement à <strong>dons@gscc.org</strong>.</span>
                                </div>
                            </div>
                            <div class="step-item">
                                <div class="step-num">3</div>
                                <div class="step-text">
                                    <strong>Confirmation sous 48h</strong>
                                    <span>Le GSCC vous envoie un email de confirmation avec votre reçu fiscal.</span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="step-item">
                                <div class="step-num">1</div>
                                <div class="step-text">
                                    <strong>Votre don est enregistré</strong>
                                    <span>Référence : <strong><?= e($reference) ?></strong></span>
                                </div>
                            </div>
                            <div class="step-item">
                                <div class="step-num">2</div>
                                <div class="step-text">
                                    <strong>Email de confirmation</strong>
                                    <span>Vous recevrez un email de confirmation avec votre reçu fiscal.</span>
                                </div>
                            </div>
                            <div class="step-item">
                                <div class="step-num">3</div>
                                <div class="step-text">
                                    <strong>Votre don est mis au travail</strong>
                                    <span>100% de votre don est reversé aux programmes d'accompagnement du GSCC.</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="confirm-actions">
                        <a href="index.php" class="btn-primary">
                            <i class="fas fa-home"></i> Retour à l'accueil
                        </a>
                        <a href="faire-un-don.php" class="btn-secondary">
                            <i class="fas fa-heart"></i> Faire un autre don
                        </a>
                    </div>



                </div><!-- /.confirm-body -->
            </div><!-- /.confirm-card -->

        </div>
    </section>

    <?php include 'templates/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 600,
            once: true,
            offset: 40
        });

        function copyText(id) {
            const txt = document.getElementById(id)?.textContent?.trim();
            if (!txt) return;
            navigator.clipboard.writeText(txt).then(() => {
                const btn = document.querySelector('.bank-row:has(#' + id + ') .copy-btn');
                if (btn) {
                    const orig = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-check" style="color:#2E7D32"></i>';
                    setTimeout(() => btn.innerHTML = orig, 1500);
                }
            });
        }
    </script>
</body>

</html>