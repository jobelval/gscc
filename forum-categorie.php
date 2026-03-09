<?php
// forum-categorie.php
require_once 'includes/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: forum.php');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

try {
    // Récupérer la catégorie
    $stmt = $pdo->prepare("SELECT * FROM forum_categories WHERE id = ? AND est_actif = 1");
    $stmt->execute([$id]);
    $categorie = $stmt->fetch();

    if (!$categorie) {
        header('Location: forum.php');
        exit;
    }

    $page_title = $categorie['nom'];
    $page_description = $categorie['description'];

    // Compter le nombre total de sujets
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM forum_sujets WHERE categorie_id = ?");
    $stmt->execute([$id]);
    $total_sujets = $stmt->fetchColumn();
    $total_pages = ceil($total_sujets / $limit);

    // Récupérer les sujets avec pagination
    $sql = "SELECT s.*, 
            CONCAT(u.prenom, ' ', u.nom) as auteur_nom,
            u.email as auteur_email,
            COUNT(r.id) as nb_reponses,
            MAX(r.date_creation) as date_derniere_reponse
            FROM forum_sujets s
            LEFT JOIN utilisateurs u ON s.auteur_id = u.id
            LEFT JOIN forum_reponses r ON s.id = r.sujet_id
            WHERE s.categorie_id = ?
            GROUP BY s.id
            ORDER BY s.est_epingle DESC, s.date_creation DESC
            LIMIT ? OFFSET ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $limit, $offset]);
    $sujets = $stmt->fetchAll();

    // Statistiques supplémentaires
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM forum_reponses r 
                           JOIN forum_sujets s ON r.sujet_id = s.id 
                           WHERE s.categorie_id = ?");
    $stmt->execute([$id]);
    $total_reponses = $stmt->fetchColumn();
} catch (PDOException $e) {
    logError("Erreur forum-categorie.php: " . $e->getMessage());
    header('Location: forum.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> - Forum <?= SITE_NAME ?></title>
    <meta name="description" content="<?= e($page_description) ?>">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        /* =============================================
           VARIABLES
        ============================================= */
        :root {
            --blue: #003399;
            --blue-dark: #002270;
            --blue-soft: rgba(0, 51, 153, 0.07);
            --rose: #D94F7A;
            --orange: #F59E0B;
            --green: #2E7D32;
            --gray-bg: #F4F6FB;
            --gray-light: #EEF1F8;
            --gray-text: #6B7280;
            --border: #E5E9F2;
            --white: #FFFFFF;
            --dark: #1A2240;
            --radius: 12px;
            --shadow: 0 4px 24px rgba(0, 51, 153, 0.08);
            --shadow-h: 0 16px 48px rgba(0, 51, 153, 0.15);
        }

        /* =============================================
           FORUM HEADER
        ============================================= */
        .forum-header {
            background: linear-gradient(135deg, #003399 0%, #1a56cc 60%, #1a7abf 100%);
            color: white;
            padding: 52px 0 72px;
            position: relative;
            overflow: hidden;
        }

        .forum-header::before,
        .forum-header::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            opacity: 0.07;
            background: white;
            pointer-events: none;
        }

        .forum-header::before {
            width: 360px;
            height: 360px;
            top: -140px;
            right: -60px;
        }

        .forum-header::after {
            width: 200px;
            height: 200px;
            bottom: -80px;
            left: -40px;
        }

        .forum-header-wave {
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            line-height: 0;
        }

        .forum-header-wave svg {
            display: block;
        }

        .forum-header-inner {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 20px;
            flex-wrap: wrap;
        }

        .forum-header-eyebrow {
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
            padding: 5px 14px;
            border-radius: 20px;
            margin-bottom: 14px;
        }

        .forum-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.3px;
        }

        .forum-header-desc {
            color: rgba(255, 255, 255, 0.75);
            font-size: 1rem;
            line-height: 1.6;
            max-width: 520px;
        }

        .forum-header-stats {
            display: flex;
            gap: 28px;
            flex-shrink: 0;
        }

        .fh-stat {
            text-align: center;
        }

        .fh-stat-n {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            line-height: 1;
            display: block;
        }

        .fh-stat-l {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 4px;
        }

        /* =============================================
           BREADCRUMB
        ============================================= */
        .forum-navigation {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 0;
        }

        .breadcrumb {
            display: flex;
            gap: 6px;
            align-items: center;
            color: var(--gray-text);
            font-size: 13.5px;
            padding: 14px 0;
        }

        .breadcrumb a {
            color: var(--blue);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .breadcrumb a:hover {
            color: var(--rose);
        }

        .breadcrumb .sep {
            color: var(--border);
            font-size: 11px;
        }

        .breadcrumb .current {
            color: var(--dark);
            font-weight: 600;
        }

        /* =============================================
           SECTION PRINCIPALE
        ============================================= */
        .category-section {
            padding: 44px 0 80px;
            background: var(--gray-bg);
        }

        /* Barre d'actions */
        .topics-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 16px;
            flex-wrap: wrap;
        }

        .topics-count {
            font-size: 14px;
            color: var(--gray-text);
        }

        .topics-count strong {
            color: var(--dark);
        }

        .btn-new-topic {
            background: linear-gradient(135deg, var(--blue) 0%, #1a56cc 100%);
            color: white;
            padding: 11px 22px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.22s, box-shadow 0.22s;
            white-space: nowrap;
        }

        .btn-new-topic:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 51, 153, 0.28);
        }

        /* =============================================
           TABLE DES SUJETS
        ============================================= */
        .topics-table {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        /* En-tête du tableau */
        .topics-head {
            display: grid;
            grid-template-columns: 1fr 120px 130px;
            padding: 12px 20px;
            background: var(--gray-light);
            border-bottom: 1px solid var(--border);
            font-size: 12px;
            font-weight: 700;
            color: var(--gray-text);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .th-center {
            text-align: center;
        }

        /* Ligne de sujet */
        .topic-row {
            display: grid;
            grid-template-columns: 1fr 120px 130px;
            padding: 18px 20px;
            border-bottom: 1px solid var(--gray-light);
            transition: background 0.18s;
            align-items: center;
        }

        .topic-row:last-child {
            border-bottom: none;
        }

        .topic-row:hover {
            background: #FAFBFF;
        }

        /* Sujet épinglé */
        .topic-row.epingle {
            background: #FFFBF0;
            border-left: 3px solid var(--orange);
        }

        .topic-row.epingle:hover {
            background: #FFF8E6;
        }

        /* Icône statut */
        .topic-main {
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .topic-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
            background: var(--blue-soft);
            color: var(--blue);
        }

        .topic-icon.epingle {
            background: rgba(245, 158, 11, 0.12);
            color: var(--orange);
        }

        .topic-icon.resolu {
            background: rgba(46, 125, 50, 0.1);
            color: var(--green);
        }

        .topic-icon.ferme {
            background: var(--gray-light);
            color: var(--gray-text);
        }

        /* Contenu du sujet */
        .topic-content {
            min-width: 0;
        }

        .topic-title-row {
            display: flex;
            align-items: baseline;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 6px;
        }

        .topic-content h3 {
            font-size: 15px;
            font-weight: 600;
            line-height: 1.4;
            margin: 0;
        }

        .topic-content h3 a {
            color: var(--dark);
            text-decoration: none;
            transition: color 0.2s;
        }

        .topic-content h3 a:hover {
            color: var(--blue);
        }

        /* Badges */
        .topic-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .badge-epingle {
            background: rgba(245, 158, 11, 0.15);
            color: #B45309;
        }

        .badge-resolu {
            background: rgba(46, 125, 50, 0.12);
            color: var(--green);
        }

        .badge-ferme {
            background: var(--gray-light);
            color: var(--gray-text);
        }

        /* Méta du sujet */
        .topic-meta {
            display: flex;
            gap: 14px;
            font-size: 12px;
            color: var(--gray-text);
            flex-wrap: wrap;
        }

        .topic-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .topic-meta i {
            color: var(--blue);
            font-size: 10px;
        }

        .topic-meta a {
            color: var(--blue);
            text-decoration: none;
            font-weight: 500;
        }

        .topic-meta a:hover {
            color: var(--rose);
        }

        /* Colonne statistiques */
        .topic-stats {
            text-align: center;
        }

        .topic-stats .stat-n {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            line-height: 1;
            display: block;
        }

        .topic-stats .stat-l {
            font-size: 11px;
            color: var(--gray-text);
            display: block;
            margin-top: 2px;
        }

        .topic-stats .stat-views {
            font-size: 11px;
            color: var(--gray-text);
            margin-top: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
        }

        .topic-stats .stat-views i {
            font-size: 9px;
        }

        /* Colonne dernier message */
        .topic-lastpost {
            font-size: 12px;
            color: var(--gray-text);
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .topic-lastpost .lp-date {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
            color: var(--dark);
        }

        .topic-lastpost .lp-date i {
            color: var(--blue);
            font-size: 10px;
        }

        .topic-lastpost .lp-none {
            color: var(--gray-text);
            font-style: italic;
        }

        /* =============================================
           ÉTAT VIDE
        ============================================= */
        .empty-state {
            text-align: center;
            padding: 72px 24px;
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
        }

        .empty-state .empty-icon {
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
            margin-bottom: 20px;
        }

        .empty-state a {
            color: var(--blue);
            font-weight: 600;
            text-decoration: none;
        }

        .empty-state a:hover {
            color: var(--rose);
        }

        /* =============================================
           PAGINATION
        ============================================= */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            margin-top: 32px;
        }

        .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: var(--white);
            color: var(--dark);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid var(--border);
            transition: all 0.22s;
        }

        .page-link:hover,
        .page-link.active {
            background: var(--blue);
            color: white;
            border-color: var(--blue);
            box-shadow: 0 4px 12px rgba(0, 51, 153, 0.25);
        }

        /* =============================================
           RESPONSIVE
        ============================================= */
        @media (max-width: 768px) {
            .topics-head {
                display: none;
            }

            .topic-row {
                grid-template-columns: 1fr;
                gap: 12px;
                padding: 16px;
            }

            .topic-stats,
            .topic-lastpost {
                flex-direction: row;
                align-items: center;
                gap: 20px;
                padding: 10px 14px;
                background: var(--gray-light);
                border-radius: 8px;
            }

            .topic-stats {
                justify-content: flex-start;
            }

            .topic-stats .stat-n {
                font-size: 15px;
            }

            .forum-header-stats {
                display: none;
            }

            .forum-header {
                padding: 44px 0 64px;
            }
        }

        @media (max-width: 500px) {
            .topics-toolbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
    <?php include 'templates/header.php'; ?>

    <!-- Forum Header -->
    <div class="forum-header">
        <div class="container forum-header-inner">
            <div>
                <div class="forum-header-eyebrow">
                    <i class="fas fa-folder-open"></i> Catégorie du forum
                </div>
                <h1><?= e($categorie['nom']) ?></h1>
                <p class="forum-header-desc"><?= e($categorie['description']) ?></p>
            </div>
            <div class="forum-header-stats">
                <div class="fh-stat">
                    <span class="fh-stat-n"><?= number_format($total_sujets) ?></span>
                    <div class="fh-stat-l">Sujets</div>
                </div>
                <div class="fh-stat">
                    <span class="fh-stat-n"><?= number_format($total_reponses) ?></span>
                    <div class="fh-stat-l">Réponses</div>
                </div>
            </div>
        </div>
        <div class="forum-header-wave">
            <svg viewBox="0 0 1440 48" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path fill="#F4F6FB" d="M0,48 C360,0 1080,0 1440,48 L1440,48 L0,48 Z" />
            </svg>
        </div>
    </div>

    <!-- Breadcrumb -->
    <div class="forum-navigation">
        <div class="container">
            <nav class="breadcrumb" aria-label="Fil d'Ariane">
                <a href="index.php"><i class="fas fa-home"></i> Accueil</a>
                <i class="fas fa-chevron-right sep"></i>
                <a href="forum.php">Forum</a>
                <i class="fas fa-chevron-right sep"></i>
                <span class="current"><?= e($categorie['nom']) ?></span>
            </nav>
        </div>
    </div>

    <!-- Sujets -->
    <section class="category-section">
        <div class="container">

            <!-- Barre d'actions -->
            <div class="topics-toolbar">
                <p class="topics-count">
                    <strong><?= number_format($total_sujets) ?></strong> sujet<?= $total_sujets > 1 ? 's' : '' ?> ·
                    <strong><?= number_format($total_reponses) ?></strong> réponse<?= $total_reponses > 1 ? 's' : '' ?>
                </p>
                <?php if (isLoggedIn()): ?>
                    <a href="nouveau-sujet.php?categorie=<?= $id ?>" class="btn-new-topic">
                        <i class="fas fa-plus-circle"></i> Nouveau sujet
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($sujets)): ?>
                <div class="empty-state" data-aos="fade-up">
                    <div class="empty-icon"><i class="fas fa-comments"></i></div>
                    <h3>Aucun sujet dans cette catégorie</h3>
                    <p>Soyez le premier à lancer la discussion !</p>
                    <?php if (isLoggedIn()): ?>
                        <a href="nouveau-sujet.php?categorie=<?= $id ?>" class="btn-new-topic">
                            <i class="fas fa-plus-circle"></i> Créer le premier sujet
                        </a>
                    <?php else: ?>
                        <p><a href="connexion.php">Connectez-vous</a> pour créer un sujet</p>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <div class="topics-table" data-aos="fade-up">

                    <!-- En-tête -->
                    <div class="topics-head">
                        <div>Sujet</div>
                        <div class="th-center">Réponses</div>
                        <div class="th-center">Dernier message</div>
                    </div>

                    <!-- Liste des sujets -->
                    <?php foreach ($sujets as $sujet):
                        $row_class   = '';
                        $icone       = 'fa-comment';
                        $icone_class = '';

                        if ($sujet['est_epingle']) {
                            $row_class   = 'epingle';
                            $icone       = 'fa-thumbtack';
                            $icone_class = 'epingle';
                        }
                        if ($sujet['est_resolu']) {
                            $icone       = 'fa-check-circle';
                            $icone_class = 'resolu';
                        }
                        if ($sujet['est_ferme']) {
                            $icone       = 'fa-lock';
                            $icone_class = 'ferme';
                        }
                    ?>
                        <div class="topic-row <?= $row_class ?>">

                            <!-- Colonne principale -->
                            <div class="topic-main">
                                <div class="topic-icon <?= $icone_class ?>">
                                    <i class="fas <?= $icone ?>"></i>
                                </div>
                                <div class="topic-content">
                                    <div class="topic-title-row">
                                        <h3>
                                            <a href="forum-sujet.php?id=<?= $sujet['id'] ?>">
                                                <?= e($sujet['titre']) ?>
                                            </a>
                                        </h3>
                                        <?php if ($sujet['est_epingle']): ?>
                                            <span class="topic-badge badge-epingle"><i class="fas fa-thumbtack"></i> Épinglé</span>
                                        <?php endif; ?>
                                        <?php if ($sujet['est_resolu']): ?>
                                            <span class="topic-badge badge-resolu"><i class="fas fa-check"></i> Résolu</span>
                                        <?php endif; ?>
                                        <?php if ($sujet['est_ferme']): ?>
                                            <span class="topic-badge badge-ferme"><i class="fas fa-lock"></i> Fermé</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="topic-meta">
                                        <span>
                                            <i class="fas fa-user"></i>
                                            <?php if ($sujet['auteur_nom']): ?>
                                                <!-- <a href="profil.php?id=<?= $sujet['auteur_id'] ?>"> -->
                                                    <?= e($sujet['auteur_nom']) ?>
                                                </a>
                                            <?php else: ?>
                                                Ancien membre
                                            <?php endif; ?>
                                        </span>
                                        <span>
                                            <i class="far fa-clock"></i>
                                            <?= formatDateFr($sujet['date_creation']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Statistiques -->
                            <div class="topic-stats">
                                <span class="stat-n"><?= number_format($sujet['nb_reponses'] ?? 0) ?></span>
                                <span class="stat-l">réponse<?= ($sujet['nb_reponses'] ?? 0) > 1 ? 's' : '' ?></span>
                                <div class="stat-views">
                                    <i class="fas fa-eye"></i> <?= number_format($sujet['vue_compteur'] ?? 0) ?> vues
                                </div>
                            </div>

                            <!-- Dernier message -->
                            <div class="topic-lastpost">
                                <?php if ($sujet['date_derniere_reponse']): ?>
                                    <div class="lp-date">
                                        <i class="fas fa-reply"></i>
                                        <?= formatDateFr($sujet['date_derniere_reponse']) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="lp-none">Aucune réponse</span>
                                <?php endif; ?>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?id=<?= $id ?>&page=<?= $i ?>"
                                class="page-link <?= $page == $i ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 600,
            once: true,
            offset: 50
        });
    </script>
</body>

</html>