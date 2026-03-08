<?php
// campagnes.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$page_title = 'Campagnes et projets';
$page_description = 'Découvrez nos campagnes de sensibilisation et nos projets en cours pour lutter contre le cancer en Haïti.';

// Filtres
$type   = isset($_GET['type'])   ? trim(strip_tags($_GET['type']))   : 'tout';
$statut = isset($_GET['statut']) ? trim(strip_tags($_GET['statut'])) : 'tout';

// Valeurs autorisées
if (!in_array($type,   ['tout', 'campagne', 'projet']))              $type   = 'tout';
if (!in_array($statut, ['tout', 'en_cours', 'termine', 'a_venir']))  $statut = 'tout';

$campagnes = [];

try {
    $where  = [];
    $params = [];

    if ($type !== 'tout') {
        $where[]  = "type = ?";
        $params[] = $type;
    }
    if ($statut !== 'tout') {
        $where[]  = "statut = ?";
        $params[] = $statut;
    }

    $where_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

    $sql = "SELECT * FROM campagnes_projets
            $where_sql
            ORDER BY
                CASE statut
                    WHEN 'en_cours' THEN 1
                    WHEN 'a_venir'  THEN 2
                    WHEN 'termine'  THEN 3
                    ELSE 4
                END,
                date_debut DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $campagnes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError("Erreur campagnes.php: " . $e->getMessage());
    $campagnes = [];
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - <?= defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'GSCC' ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --blue: #003399;
            --blue-dark: #002270;
            --blue-soft: rgba(0, 51, 153, 0.08);
            --rose: #D94F7A;
            --green: #2E7D32;
            --orange: #D97706;
            --gray-bg: #F4F6FB;
            --gray-light: #EEF1F8;
            --gray-text: #4B5563;
            --border: #D1D5DB;
            --white: #FFFFFF;
            --dark: #0D1117;
            --radius: 12px;
            --shadow: 0 4px 24px rgba(0, 51, 153, 0.08);
            --shadow-h: 0 16px 48px rgba(0, 51, 153, 0.15);
        }

        .page-header {
            background: linear-gradient(135deg, #003399 0%, #1a56cc 60%, #1a7abf 100%);
            color: white;
            padding: 72px 0 90px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before,
        .page-header::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            opacity: 0.07;
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

        .header-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: white;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 6px 16px;
            border-radius: 20px;
            margin-bottom: 20px;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.2rem, 5vw, 3.4rem);
            font-weight: 700;
            color: #FFFFFF;
            margin-bottom: 14px;
            letter-spacing: -0.5px;
        }

        .page-header p {
            font-size: 1.05rem;
            color: #E8F0FE;
            max-width: 480px;
            margin: 0 auto;
            line-height: 1.7;
        }

        .campagnes-section {
            padding: 60px 0 90px;
            background: var(--gray-bg);
            min-height: 500px;
        }

        .filters-bar {
            background: var(--white);
            border-radius: var(--radius);
            padding: 20px 24px;
            margin-bottom: 40px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            justify-content: center;
        }

        .filter-group {
            display: flex;
            gap: 6px;
            align-items: center;
            background: var(--gray-light);
            padding: 5px;
            border-radius: 10px;
        }

        .filter-sep {
            width: 1px;
            height: 28px;
            background: var(--border);
            flex-shrink: 0;
        }

        .filter-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 7px;
            background: transparent;
            color: var(--dark);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            transition: all 0.22s;
            white-space: nowrap;
        }

        .filter-btn i {
            font-size: 12px;
            color: var(--gray-text);
            transition: color 0.22s;
        }

        .filter-btn:hover {
            background: var(--white);
            color: var(--blue);
        }

        .filter-btn:hover i {
            color: var(--blue);
        }

        .filter-btn.active {
            background: var(--blue);
            color: var(--white);
            box-shadow: 0 3px 10px rgba(0, 51, 153, 0.25);
        }

        .filter-btn.active i {
            color: rgba(255, 255, 255, 0.85);
        }

        .campagnes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
            gap: 28px;
        }

        .campagne-card {
            background: var(--white);
            border-radius: var(--radius);
            overflow: hidden;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            transition: transform 0.28s ease, box-shadow 0.28s ease;
            display: flex;
            flex-direction: column;
        }

        .campagne-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-h);
        }

        .campagne-image {
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .campagne-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
            display: block;
        }

        .campagne-card:hover .campagne-image img {
            transform: scale(1.06);
        }

        .badge-statut {
            position: absolute;
            top: 14px;
            right: 14px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .badge-en_cours {
            background: rgba(46, 125, 50, 0.9);
            color: #fff;
        }

        .badge-termine {
            background: rgba(107, 114, 128, 0.9);
            color: #fff;
        }

        .badge-a_venir {
            background: rgba(217, 119, 6, 0.9);
            color: #fff;
        }

        .badge-type {
            position: absolute;
            top: 14px;
            left: 14px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            background: rgba(0, 51, 153, 0.88);
            color: #fff;
        }

        .campagne-content {
            padding: 22px 24px 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .campagne-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.4;
            margin-bottom: 10px;
        }

        .campagne-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s;
        }

        .campagne-title a:hover {
            color: var(--blue);
        }

        .campagne-meta {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 12px;
            font-size: 12.5px;
            color: var(--gray-text);
        }

        .campagne-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .campagne-meta i {
            color: var(--blue);
            font-size: 11px;
        }

        .campagne-description {
            color: var(--gray-text);
            font-size: 14px;
            line-height: 1.65;
            margin-bottom: 18px;
            flex: 1;
        }

        .progress-wrap {
            margin-bottom: 18px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 6px;
        }

        .progress-bar {
            height: 7px;
            background: var(--gray-light);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--blue), var(--rose));
            border-radius: 10px;
            transition: width 0.6s ease;
        }

        .campagne-footer {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding-top: 14px;
            border-top: 1px solid var(--gray-light);
            margin-top: auto;
        }

        .btn-detail {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--blue);
            color: var(--white);
            padding: 9px 18px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.22s, transform 0.22s;
            white-space: nowrap;
        }

        .btn-detail:hover {
            background: var(--rose);
            color: var(--white);
            transform: translateX(2px);
        }

        .btn-detail i {
            font-size: 11px;
        }

        .empty-state {
            text-align: center;
            padding: 70px 24px;
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            grid-column: 1 / -1;
        }

        .empty-icon {
            width: 72px;
            height: 72px;
            background: var(--blue-soft);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--blue);
            font-size: 28px;
        }

        .empty-state h3 {
            color: var(--dark);
            font-size: 18px;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: var(--gray-text);
            font-size: 14px;
        }

        @media (max-width: 900px) {
            .campagnes-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .campagnes-grid {
                grid-template-columns: 1fr;
            }

            .filters-bar {
                flex-direction: column;
            }

            .filter-sep {
                width: 80%;
                height: 1px;
            }

            .page-header {
                padding: 52px 0 72px;
            }
        }
    </style>
</head>

<body>
    <?php include 'templates/header.php'; ?>

    <div class="page-header">
        <div class="container" style="position:relative;z-index:1;">
            <div class="header-eyebrow" data-aos="fade-down">
                <i class="fas fa-megaphone"></i> GSCC — Nos actions
            </div>
            <h1 data-aos="fade-up">Campagnes & Projets</h1>
            <p data-aos="fade-up" data-aos-delay="100">
                Découvrez nos actions pour lutter contre le cancer en Haïti
            </p>
        </div>
        <div class="page-header-wave">
            <svg viewBox="0 0 1440 48" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path fill="#F4F6FB" d="M0,48 C360,0 1080,0 1440,48 L1440,48 L0,48 Z" />
            </svg>
        </div>
    </div>

    <section class="campagnes-section">
        <div class="container">

            <!-- Filtres -->
            <div class="filters-bar" data-aos="fade-up">
                <div class="filter-group">
                    <a href="?type=tout&statut=<?= $statut ?>" class="filter-btn <?= $type === 'tout'     ? 'active' : '' ?>"><i class="fas fa-layer-group"></i> Tous</a>
                    <a href="?type=campagne&statut=<?= $statut ?>" class="filter-btn <?= $type === 'campagne' ? 'active' : '' ?>"><i class="fas fa-megaphone"></i> Campagnes</a>
                    <a href="?type=projet&statut=<?= $statut ?>" class="filter-btn <?= $type === 'projet'   ? 'active' : '' ?>"><i class="fas fa-tasks"></i> Projets</a>
                </div>
                <div class="filter-sep"></div>
                <div class="filter-group">
                    <a href="?type=<?= $type ?>&statut=tout" class="filter-btn <?= $statut === 'tout'     ? 'active' : '' ?>">Tous</a>
                    <a href="?type=<?= $type ?>&statut=en_cours" class="filter-btn <?= $statut === 'en_cours' ? 'active' : '' ?>">En cours</a>
                    <a href="?type=<?= $type ?>&statut=termine" class="filter-btn <?= $statut === 'termine'  ? 'active' : '' ?>">Terminés</a>
                    <a href="?type=<?= $type ?>&statut=a_venir" class="filter-btn <?= $statut === 'a_venir'  ? 'active' : '' ?>">À venir</a>
                </div>
            </div>

            <!-- Grille -->
            <div class="campagnes-grid">
                <?php if (empty($campagnes)): ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-calendar-times"></i></div>
                        <h3>Aucune campagne ou projet trouvé</h3>
                        <p>Revenez plus tard pour découvrir nos nouvelles actions.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($campagnes as $i => $c): ?>
                        <?php
                        $sl    = $c['statut'] ?? 'en_cours';
                        $prog  = isset($c['progression']) ? (int)$c['progression'] : null;
                        // Image de fallback si vide
                        $img   = !empty($c['image_couverture']) ? $c['image_couverture'] : 'https://picsum.photos/800/400?random=' . $c['id'];
                        $desc  = mb_strimwidth(strip_tags($c['description'] ?? $c['contenu'] ?? ''), 0, 150, '…');
                        $statut_labels = ['en_cours' => 'En cours', 'termine' => 'Terminé', 'a_venir' => 'À venir'];
                        ?>
                        <div class="campagne-card" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 80 ?>">
                            <div class="campagne-image">
                                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($c['titre']) ?>" loading="lazy">
                                <span class="badge-type"><?= $c['type'] === 'campagne' ? 'Campagne' : 'Projet' ?></span>
                                <span class="badge-statut badge-<?= $sl ?>"><?= $statut_labels[$sl] ?? $sl ?></span>
                            </div>
                            <div class="campagne-content">
                                <h3 class="campagne-title">
                                    <a href="campagne-detail.php?id=<?= (int)$c['id'] ?>">
                                        <?= htmlspecialchars($c['titre']) ?>
                                    </a>
                                </h3>
                                <div class="campagne-meta">
                                    <?php if (!empty($c['date_debut'])): ?>
                                        <span>
                                            <i class="far fa-calendar-alt"></i>
                                            <?= date('d/m/Y', strtotime($c['date_debut'])) ?>
                                            <?php if (!empty($c['date_fin'])): ?>
                                                — <?= date('d/m/Y', strtotime($c['date_fin'])) ?>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($c['objectif_montant'])): ?>
                                        <span>
                                            <i class="fas fa-bullseye"></i>
                                            Objectif : $<?= number_format((float)$c['objectif_montant'], 0, ',', ' ') ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="campagne-description"><?= htmlspecialchars($desc) ?></p>

                                <?php if ($prog !== null): ?>
                                    <div class="progress-wrap">
                                        <div class="progress-label">
                                            <span>Progression</span>
                                            <span><?= min(100, $prog) ?>%</span>
                                        </div>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width:<?= min(100, $prog) ?>%"></div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="campagne-footer">
                                    <a href="campagne-detail.php?id=<?= (int)$c['id'] ?>" class="btn-detail">
                                        En savoir plus <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </section>

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