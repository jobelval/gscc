<?php
// campagne-detail.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Récupérer l'id
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: campagnes.php');
    exit;
}

// Charger la campagne
$campagne = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM campagnes_projets WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $campagne = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError("Erreur campagne-detail.php: " . $e->getMessage());
}

if (!$campagne) {
    header('Location: campagnes.php');
    exit;
}

// Campagnes similaires (même type, autres id)
$similaires = [];
try {
    $stmt = $pdo->prepare("SELECT id, titre, image_couverture, statut, type, description
                           FROM campagnes_projets
                           WHERE type = ? AND id != ?
                           ORDER BY date_debut DESC LIMIT 3");
    $stmt->execute([$campagne['type'], $id]);
    $similaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
}

$page_title = htmlspecialchars($campagne['titre']);
$statut_labels = ['en_cours' => 'En cours', 'termine' => 'Terminé', 'a_venir' => 'À venir'];
$sl = $campagne['statut'] ?? 'en_cours';
$prog = isset($campagne['progression']) ? (int)$campagne['progression'] : null;
$img = !empty($campagne['image_couverture']) ? $campagne['image_couverture'] : 'https://picsum.photos/1200/500?random=' . $id;
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - <?= defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'GSCC' ?></title>
    <meta name="description" content="<?= htmlspecialchars(mb_strimwidth(strip_tags($campagne['description'] ?? ''), 0, 160, '…')) ?>">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --blue: #003399;
            --blue-dark: #002270;
            --blue-soft: rgba(0, 51, 153, 0.07);
            --rose: #D94F7A;
            --green: #2E7D32;
            --orange: #D97706;
            --gray-bg: #F4F6FB;
            --gray-light: #EEF1F8;
            --gray-text: #4B5563;
            --border: #D1D5DB;
            --white: #FFFFFF;
            --dark: #0D1117;
            --radius: 14px;
            --shadow: 0 4px 24px rgba(0, 51, 153, 0.08);
        }

        body {
            background: var(--gray-bg);
            color: var(--dark);
            font-family: 'Inter', sans-serif;
        }

        /* ── Hero image ── */
        .detail-hero {
            position: relative;
            height: 420px;
            overflow: hidden;
            background: #0D1117;
        }

        .detail-hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.65;
        }

        .detail-hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.1) 0%, rgba(0, 51, 153, 0.6) 100%);
            display: flex;
            align-items: flex-end;
        }

        .detail-hero-content {
            padding: 40px 0;
            color: white;
            width: 100%;
        }

        .detail-hero-content .container {
            max-width: 900px;
        }

        .detail-breadcrumb {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.75);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-breadcrumb a {
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
        }

        .detail-breadcrumb a:hover {
            color: white;
        }

        .detail-breadcrumb i {
            font-size: 10px;
        }

        .detail-badges {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .badge {
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .badge-type-pill {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .badge-en_cours {
            background: rgba(46, 125, 50, 0.9);
            color: white;
        }

        .badge-termine {
            background: rgba(107, 114, 128, 0.85);
            color: white;
        }

        .badge-a_venir {
            background: rgba(217, 119, 6, 0.9);
            color: white;
        }

        .detail-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 700;
            color: white;
            line-height: 1.25;
            margin: 0;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        /* ── Layout principal ── */
        .detail-section {
            padding: 50px 0 80px;
        }

        .detail-layout {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 36px;
            align-items: start;
            max-width: 1100px;
            margin: 0 auto;
        }

        /* ── Contenu principal ── */
        .detail-main {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .detail-main-body {
            padding: 36px 40px;
        }

        .detail-main-body h2 {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--blue);
            margin: 28px 0 14px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--blue-soft);
        }

        .detail-main-body h2:first-child {
            margin-top: 0;
        }

        .detail-main-body p {
            color: var(--gray-text);
            line-height: 1.8;
            font-size: 15px;
            margin-bottom: 16px;
        }

        .detail-main-body ul {
            padding-left: 20px;
            margin-bottom: 16px;
        }

        .detail-main-body ul li {
            color: var(--gray-text);
            line-height: 1.8;
            font-size: 15px;
            margin-bottom: 6px;
        }

        /* Méta en haut du contenu */
        .detail-meta-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 18px 40px;
            background: var(--gray-bg);
            border-bottom: 1px solid var(--border);
            font-size: 13.5px;
            color: var(--gray-text);
        }

        .detail-meta-strip span {
            display: flex;
            align-items: center;
            gap: 7px;
            font-weight: 500;
        }

        .detail-meta-strip i {
            color: var(--blue);
        }

        /* ── Sidebar ── */
        .detail-sidebar {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .sidebar-card {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            padding: 24px;
        }

        .sidebar-card h3 {
            font-size: 14px;
            font-weight: 800;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--blue-soft);
        }

        /* Progression sidebar */
        .prog-number {
            font-size: 2.4rem;
            font-weight: 800;
            color: var(--blue);
            line-height: 1;
            margin-bottom: 4px;
        }

        .prog-sub {
            font-size: 13px;
            color: var(--gray-text);
            margin-bottom: 14px;
            font-weight: 500;
        }

        .progress-bar {
            height: 10px;
            background: var(--gray-light);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--blue), var(--rose));
            border-radius: 10px;
        }

        .prog-objectif {
            font-size: 13px;
            color: var(--gray-text);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .prog-objectif i {
            color: var(--blue);
        }

        /* Infos sidebar */
        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-list li {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px solid var(--gray-light);
            font-size: 13.5px;
            gap: 12px;
        }

        .info-list li:last-child {
            border-bottom: none;
        }

        .info-list .info-key {
            color: var(--gray-text);
            font-weight: 500;
            flex-shrink: 0;
        }

        .info-list .info-val {
            color: var(--dark);
            font-weight: 600;
            text-align: right;
        }

        /* Bouton don sidebar */
        .btn-don-sidebar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--blue);
            color: white;
            padding: 14px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            transition: background 0.2s, transform 0.2s;
            margin-top: 4px;
        }

        .btn-don-sidebar:hover {
            background: var(--blue-dark);
            transform: translateY(-2px);
            color: white;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--gray-text);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 24px;
            transition: color 0.2s;
        }

        .btn-back:hover {
            color: var(--blue);
        }

        /* ── Similaires ── */
        .similaires-section {
            padding: 0 0 70px;
        }

        .similaires-section h2 {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--blue);
            margin-bottom: 28px;
        }

        .similaires-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        .sim-card {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            transition: transform 0.25s, box-shadow 0.25s;
            display: block;
        }

        .sim-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 40px rgba(0, 51, 153, 0.13);
        }

        .sim-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            display: block;
        }

        .sim-card-body {
            padding: 16px 18px;
        }

        .sim-card-body h4 {
            font-size: 14px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 6px;
            line-height: 1.4;
        }

        .sim-card-body p {
            font-size: 13px;
            color: var(--gray-text);
            line-height: 1.6;
        }

        .sim-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 8px;
        }

        .sim-badge-en_cours {
            background: #D1FAE5;
            color: #065F46;
        }

        .sim-badge-termine {
            background: #F3F4F6;
            color: #4B5563;
        }

        .sim-badge-a_venir {
            background: #FEF3C7;
            color: #92400E;
        }

        @media (max-width: 900px) {
            .detail-layout {
                grid-template-columns: 1fr;
            }

            .similaires-grid {
                grid-template-columns: 1fr 1fr;
            }

            .detail-hero {
                height: 300px;
            }
        }

        @media (max-width: 600px) {
            .similaires-grid {
                grid-template-columns: 1fr;
            }

            .detail-main-body {
                padding: 24px 20px;
            }

            .detail-meta-strip {
                padding: 14px 20px;
            }
        }
    </style>
</head>

<body>
    <?php include 'templates/header.php'; ?>

    <!-- Hero -->
    <div class="detail-hero">
        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($campagne['titre']) ?>">
        <div class="detail-hero-overlay">
            <div class="detail-hero-content">
                <div class="container">
                    <div class="detail-breadcrumb">
                        <a href="index.php"><i class="fas fa-home"></i> Accueil</a>
                        <i class="fas fa-chevron-right"></i>
                        <a href="campagnes.php">Campagnes & Projets</a>
                        <i class="fas fa-chevron-right"></i>
                        <span><?= htmlspecialchars($campagne['titre']) ?></span>
                    </div>
                    <div class="detail-badges">
                        <span class="badge badge-type-pill">
                            <?= $campagne['type'] === 'campagne' ? '<i class="fas fa-megaphone"></i> Campagne' : '<i class="fas fa-tasks"></i> Projet' ?>
                        </span>
                        <span class="badge badge-<?= $sl ?>">
                            <?= $statut_labels[$sl] ?? $sl ?>
                        </span>
                    </div>
                    <h1><?= htmlspecialchars($campagne['titre']) ?></h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu -->
    <section class="detail-section">
        <div class="container" style="max-width:1100px;">

            <a href="campagnes.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour aux campagnes
            </a>

            <div class="detail-layout">

                <!-- Colonne principale -->
                <div class="detail-main" data-aos="fade-up">
                    <!-- Méta strip -->
                    <div class="detail-meta-strip">
                        <?php if (!empty($campagne['date_debut'])): ?>
                            <span>
                                <i class="far fa-calendar-alt"></i>
                                <?= date('d/m/Y', strtotime($campagne['date_debut'])) ?>
                                <?php if (!empty($campagne['date_fin'])): ?>
                                    — <?= date('d/m/Y', strtotime($campagne['date_fin'])) ?>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                        <span>
                            <i class="fas fa-tag"></i>
                            <?= $campagne['type'] === 'campagne' ? 'Campagne de sensibilisation' : 'Projet en cours' ?>
                        </span>
                        <span>
                            <i class="fas fa-circle" style="color:<?= $sl === 'en_cours' ? '#2E7D32' : ($sl === 'a_venir' ? '#D97706' : '#6B7280') ?>; font-size:8px;"></i>
                            <?= $statut_labels[$sl] ?? $sl ?>
                        </span>
                    </div>

                    <div class="detail-main-body">
                        <?php if (!empty($campagne['description'])): ?>
                            <h2><i class="fas fa-info-circle" style="margin-right:8px;font-size:0.9em;"></i>À propos</h2>
                            <p><?= nl2br(htmlspecialchars($campagne['description'])) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($campagne['contenu'])): ?>
                            <h2><i class="fas fa-align-left" style="margin-right:8px;font-size:0.9em;"></i>Détails</h2>
                            <div style="color:var(--gray-text);font-size:15px;line-height:1.8;">
                                <?= $campagne['contenu'] /* HTML autorisé depuis admin */ ?>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($campagne['description']) && empty($campagne['contenu'])): ?>
                            <p style="color:var(--gray-text);font-style:italic;">Aucun détail disponible pour cette campagne.</p>
                        <?php endif; ?>

                        <!-- CTA -->
                        <?php if ($sl !== 'termine'): ?>
                            <div style="margin-top:32px;padding-top:24px;border-top:1px solid var(--border);display:flex;gap:14px;flex-wrap:wrap;">
                                <a href="faire-un-don.php" class="btn-don-sidebar" style="flex:1;min-width:160px;">
                                    <i class="fas fa-heart"></i> Soutenir cette action
                                </a>
                                <a href="benevolat.php" style="flex:1;min-width:160px;display:flex;align-items:center;justify-content:center;gap:8px;background:var(--blue-soft);color:var(--blue);padding:14px;border-radius:10px;text-decoration:none;font-size:15px;font-weight:700;transition:background 0.2s;" onmouseover="this.style.background='#d9e6ff'" onmouseout="this.style.background='var(--blue-soft)'">
                                    <i class="fas fa-hand-holding-heart"></i> Devenir bénévole
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="detail-sidebar" data-aos="fade-up" data-aos-delay="100">

                    <!-- Progression -->
                    <?php if ($prog !== null): ?>
                        <div class="sidebar-card">
                            <h3><i class="fas fa-chart-line" style="margin-right:6px;color:var(--blue);"></i>Progression</h3>
                            <div class="prog-number"><?= min(100, $prog) ?>%</div>
                            <div class="prog-sub">de l'objectif atteint</div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width:<?= min(100, $prog) ?>%"></div>
                            </div>
                            <?php if (!empty($campagne['objectif_montant'])): ?>
                                <div class="prog-objectif">
                                    <i class="fas fa-bullseye"></i>
                                    Objectif : $<?= number_format((float)$campagne['objectif_montant'], 0, ',', ' ') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Informations -->
                    <div class="sidebar-card">
                        <h3><i class="fas fa-info-circle" style="margin-right:6px;color:var(--blue);"></i>Informations</h3>
                        <ul class="info-list">
                            <li>
                                <span class="info-key">Type</span>
                                <span class="info-val"><?= $campagne['type'] === 'campagne' ? 'Campagne' : 'Projet' ?></span>
                            </li>
                            <li>
                                <span class="info-key">Statut</span>
                                <span class="info-val"><?= $statut_labels[$sl] ?? $sl ?></span>
                            </li>
                            <?php if (!empty($campagne['date_debut'])): ?>
                                <li>
                                    <span class="info-key">Début</span>
                                    <span class="info-val"><?= date('d/m/Y', strtotime($campagne['date_debut'])) ?></span>
                                </li>
                            <?php endif; ?>
                            <?php if (!empty($campagne['date_fin'])): ?>
                                <li>
                                    <span class="info-key">Fin prévue</span>
                                    <span class="info-val"><?= date('d/m/Y', strtotime($campagne['date_fin'])) ?></span>
                                </li>
                            <?php endif; ?>
                            <?php if (!empty($campagne['objectif_montant'])): ?>
                                <li>
                                    <span class="info-key">Objectif</span>
                                    <span class="info-val">$<?= number_format((float)$campagne['objectif_montant'], 0, ',', ' ') ?></span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- Bouton don -->
                    <?php if ($sl !== 'termine'): ?>
                        <div class="sidebar-card" style="text-align:center;">
                            <p style="color:var(--gray-text);font-size:14px;font-weight:500;margin-bottom:14px;line-height:1.6;">
                                Votre soutien fait la différence. Chaque don compte pour cette action.
                            </p>
                            <a href="faire-un-don.php" class="btn-don-sidebar">
                                <i class="fas fa-heart"></i> Faire un don
                            </a>
                        </div>
                    <?php endif; ?>

                </div><!-- /sidebar -->
            </div><!-- /layout -->
        </div>
    </section>

    <!-- Campagnes similaires -->
    <?php if (!empty($similaires)): ?>
        <section class="similaires-section">
            <div class="container" style="max-width:1100px;">
                <h2 data-aos="fade-up">Autres <?= $campagne['type'] === 'campagne' ? 'campagnes' : 'projets' ?></h2>
                <div class="similaires-grid">
                    <?php foreach ($similaires as $j => $sim): ?>
                        <?php
                        $sim_img = !empty($sim['image_couverture']) ? $sim['image_couverture'] : 'https://picsum.photos/400/200?random=' . ($sim['id'] + 10);
                        $sim_sl  = $sim['statut'] ?? 'en_cours';
                        ?>
                        <a href="campagne-detail.php?id=<?= (int)$sim['id'] ?>" class="sim-card" data-aos="fade-up" data-aos-delay="<?= $j * 80 ?>">
                            <img src="<?= htmlspecialchars($sim_img) ?>" alt="<?= htmlspecialchars($sim['titre']) ?>">
                            <div class="sim-card-body">
                                <span class="sim-badge sim-badge-<?= $sim_sl ?>"><?= $statut_labels[$sim_sl] ?? $sim_sl ?></span>
                                <h4><?= htmlspecialchars($sim['titre']) ?></h4>
                                <p><?= htmlspecialchars(mb_strimwidth(strip_tags($sim['description'] ?? ''), 0, 90, '…')) ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php include 'templates/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        AOS.init({
            duration: 600,
            once: true,
            offset: 50
        });
    </script>
</body>

</html>