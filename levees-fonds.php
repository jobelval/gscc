<?php
// levees-fonds.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$page_title = 'Levées de fonds';
$page_description = 'Soutenez nos actions en participant à nos collectes de fonds.';

// Récupérer les campagnes de collecte
try {
    $stmt = $pdo->prepare("SELECT * FROM campagnes_projets 
                           WHERE type = 'campagne' AND statut = 'en_cours'
                           AND (objectif_montant IS NOT NULL OR progression > 0)
                           ORDER BY date_fin ASC");
    $stmt->execute();
    $collectes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError("Erreur levees-fonds: " . $e->getMessage());
    $collectes = [];
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - <?= defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'GSCC' ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --blue: #003399;
            --blue-dark: #002277;
            --text: #0D1117;
            --text-2: #1F2937;
            --muted: #4B5563;
            --border: #D1D5DB;
            --bg: #F3F4F6;
            --white: #FFFFFF;
        }

        /* ── Header ── */
        .page-header {
            background: linear-gradient(135deg, #003399 0%, #1a56cc 60%, #1a7abf 100%);
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
            color: #E8F0FE;
        }

        /* ── Section ── */
        .fundraising-section {
            padding: 60px 0;
            background: var(--bg);
        }

        /* ── Titre section ── */
        .section-title {
            font-size: 1.6rem !important;
            font-weight: 800 !important;
            color: var(--blue) !important;
            text-align: center;
            margin-bottom: 40px;
        }

        /* ── Grille campagnes ── */
        .campagnes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 28px;
        }

        .campagne-card {
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--border);
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            transition: all .25s ease;
        }

        .campagne-card:hover {
            transform: translateY(-8px);
            border-color: var(--blue);
            box-shadow: 0 12px 32px rgba(0, 51, 153, .12);
        }

        .campagne-image {
            height: 200px;
            position: relative;
            overflow: hidden;
        }

        .campagne-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .5s ease;
        }

        .campagne-card:hover .campagne-image img {
            transform: scale(1.08);
        }

        .campagne-badge {
            position: absolute;
            top: 14px;
            right: 14px;
            background: #DC2626;
            color: white;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .3px;
        }

        .campagne-content {
            padding: 24px;
        }

        .campagne-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 10px;
        }

        .campagne-description {
            color: var(--muted);
            font-size: 14px;
            font-weight: 400;
            line-height: 1.7;
            margin-bottom: 18px;
        }

        /* ── Progress ── */
        .progress-section {
            margin: 16px 0;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-2);
            margin-bottom: 6px;
        }

        .progress-bar {
            height: 10px;
            background: var(--border);
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--blue);
            border-radius: 5px;
            transition: width .3s ease;
        }

        /* ── Stats carte ── */
        .campagne-stats {
            display: flex;
            justify-content: space-between;
            margin: 18px 0;
            padding: 14px 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .stat {
            text-align: center;
        }

        .stat-value {
            font-size: 18px;
            font-weight: 800;
            color: var(--text);
        }

        .stat-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        /* ── Bouton don ── */
        .btn-donate {
            display: block;
            background: var(--blue);
            color: white;
            text-align: center;
            padding: 13px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            transition: all .25s ease;
        }

        .btn-donate:hover {
            background: var(--blue-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 51, 153, .3);
        }

        .btn-donate i {
            margin-right: 8px;
        }

        /* ── Comment ça marche ── */
        .how-it-works {
            margin-top: 60px;
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            border: 1px solid var(--border);
        }

        .how-it-works h2 {
            text-align: center;
            color: var(--blue) !important;
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .how-it-works>p {
            text-align: center;
            color: var(--muted);
            font-size: 15px;
            font-weight: 400;
            max-width: 700px;
            margin: 0 auto 40px;
            line-height: 1.8;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 28px;
            margin-top: 0;
        }

        .step {
            text-align: center;
        }

        .step-number {
            width: 42px;
            height: 42px;
            background: var(--blue);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-weight: 800;
            font-size: 16px;
        }

        .step h4 {
            color: var(--text);
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .step p {
            color: var(--muted);
            font-size: 13.5px;
            font-weight: 400;
            line-height: 1.7;
        }

        @media (max-width: 768px) {
            .steps-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .campagnes-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php require_once 'templates/header.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 data-aos="fade-up">Levées de fonds</h1>
            <p data-aos="fade-up" data-aos-delay="100">
                Chaque don compte pour sauver des vies
            </p>
        </div>
    </div>

    <!-- Fundraising Section -->
    <section class="fundraising-section">
        <div class="container">
            <!-- Campagnes en cours -->
            <h2 class="section-title" style="text-align: center; color: #003399; margin-bottom: 40px;" data-aos="fade-up">
                Collectes en cours
            </h2>

            <div class="campagnes-grid">
                <!-- Campagne 1 -->
                <div class="campagne-card" data-aos="fade-up">
                    <div class="campagne-image">
                        <img src="images/image6.jpg" alt="Collecte">
                        <div class="campagne-badge">Urgent</div>
                    </div>
                    <div class="campagne-content">
                        <h3 class="campagne-title">Aide aux patients démunis</h3>
                        <p class="campagne-description">
                            Aidez-nous à prendre en charge les frais médicaux des patients qui n'ont pas les moyens de se soigner.
                        </p>

                        <div class="progress-section">
                            <div class="progress-header">
                                <span>Collecté: $3,600</span>
                                <span>Objectif: $5,000</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 72%;"></div>
                            </div>
                        </div>


                        <a href="faire-un-don.php?campagne=aide-patients" class="btn-donate">
                            <i class="fas fa-heart"></i> Faire un don
                        </a>
                    </div>
                </div>

                <!-- Campagne 2 -->
                <div class="campagne-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="campagne-image">
                        <img src="images/image5.jpg" alt="Collecte">
                    </div>
                    <div class="campagne-content">
                        <h3 class="campagne-title">Centre d'accueil - Phase 2</h3>
                        <p class="campagne-description">
                            Construction de 5 nouvelles chambres pour accueillir plus de patients venant de provinces.
                        </p>

                        <div class="progress-section">
                            <div class="progress-header">
                                <span>Collecté: $12,500</span>
                                <span>Objectif: $25,000</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 50%;"></div>
                            </div>
                        </div>


                        <a href="faire-un-don.php?campagne=centre-accueil" class="btn-donate">
                            <i class="fas fa-heart"></i> Faire un don
                        </a>
                    </div>
                </div>

                <!-- Campagne 3 -->
                <div class="campagne-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="campagne-image">
                        <img src="images/image1.jpg" alt="Collecte">
                    </div>
                    <div class="campagne-content">
                        <h3 class="campagne-title">Unité mobile de dépistage</h3>
                        <p class="campagne-description">
                            Financez les tournées de notre unité mobile dans les zones rurales pour dépister gratuitement.
                        </p>

                        <div class="progress-section">
                            <div class="progress-header">
                                <span>Collecté: $8,000</span>
                                <span>Objectif: $10,000</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 80%;"></div>
                            </div>
                        </div>


                        <a href="faire-un-don.php?campagne=unite-mobile" class="btn-donate">
                            <i class="fas fa-heart"></i> Faire un don
                        </a>
                    </div>
                </div>
            </div>

            <!-- Comment ça marche -->
            <div class="how-it-works" data-aos="fade-up">
                <h2>Comment ça marche ?</h2>
                <p>
                    Votre don, quel que soit son montant, est utilisé à 100% pour nos actions.
                    Voici comment nous procédons :
                </p>

                <div class="steps-grid">
                    <div class="step">
                        <div class="step-number">1</div>
                        <h4>Vous faites un don</h4>
                        <p>En ligne, par virement ou en espèces, choisissez le montant en $ et la campagne.</p>
                    </div>

                    <div class="step">
                        <div class="step-number">2</div>
                        <h4>Nous collectons</h4>
                        <p>Tous les dons sont centralisés et tracés pour chaque campagne.</p>
                    </div>

                    <div class="step">
                        <div class="step-number">3</div>
                        <h4>Nous agissons</h4>
                        <p>Les fonds sont utilisés directement pour les actions prévues.</p>
                    </div>

                    <div class="step">
                        <div class="step-number">4</div>
                        <h4>Vous êtes informé</h4>
                        <p>Recevez des nouvelles de l'avancement des campagnes.</p>
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
    </script>
</body>

</html>