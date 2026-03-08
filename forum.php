<?php
// forum.php
require_once 'includes/config.php';

$page_title = 'Forum';
$page_description = 'Échangez avec la communauté GSCC : partagez vos expériences, posez vos questions, soutenez-vous mutuellement.';

$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$categorie_id = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;
$sujet_id = isset($_GET['sujet']) ? (int)$_GET['sujet'] : 0;

try {
    // Récupérer les catégories
    $stmt = $pdo->query("SELECT * FROM forum_categories WHERE est_actif = 1 ORDER BY ordre ASC");
    $categories = $stmt->fetchAll();

    // Statistiques du forum
    $stats = [];
    $stmt = $pdo->query("SELECT COUNT(*) FROM forum_sujets");
    $stats['sujets'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM forum_reponses");
    $stats['reponses'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(DISTINCT auteur_id) FROM forum_sujets");
    $stats['membres_actifs'] = $stmt->fetchColumn();

    // Dernier sujet
    $stmt = $pdo->query("SELECT s.*, c.nom as categorie_nom, 
                         CONCAT(u.prenom, ' ', u.nom) as auteur_nom
                         FROM forum_sujets s
                         LEFT JOIN forum_categories c ON s.categorie_id = c.id
                         LEFT JOIN utilisateurs u ON s.auteur_id = u.id
                         ORDER BY s.date_creation DESC LIMIT 1");
    $dernier_sujet = $stmt->fetch();
} catch (PDOException $e) {
    logError("Erreur forum.php: " . $e->getMessage());
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> - <?= SITE_NAME ?></title>
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
            --blue-soft: rgba(0, 51, 153, 0.08);
            --rose: #D94F7A;
            --rose-light: #F2A8C0;
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
           PAGE HEADER
        ============================================= */
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
            margin-bottom: 14px;
            letter-spacing: -0.5px;
        }

        .page-header p {
            font-size: 1.05rem;
            color: rgba(255, 255, 255, 0.82);
            max-width: 460px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* =============================================
           FORUM SECTION
        ============================================= */
        .forum-section {
            padding: 60px 0 90px;
            background: var(--gray-bg);
        }

        /* =============================================
           LAYOUT
        ============================================= */
        .forum-layout {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 36px;
            align-items: start;
        }

        /* Titre de section */
        .section-heading {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-heading i {
            color: var(--rose);
            font-size: 16px;
        }

        /* =============================================
           CARTES DE CATÉGORIES
        ============================================= */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .category-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 24px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            text-decoration: none;
            color: inherit;
            display: block;
            transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
            position: relative;
            overflow: hidden;
        }

        /* Barre colorée gauche au hover */
        .category-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--blue);
            transform: scaleY(0);
            transform-origin: bottom;
            transition: transform 0.28s ease;
            border-radius: 3px 0 0 3px;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-h);
            border-color: var(--border);
        }

        .category-card:hover::before {
            transform: scaleY(1);
        }

        .category-icon {
            width: 48px;
            height: 48px;
            background: var(--blue-soft);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--blue);
            font-size: 18px;
            margin-bottom: 14px;
        }

        .category-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .category-description {
            color: var(--gray-text);
            font-size: 13.5px;
            line-height: 1.6;
            margin-bottom: 14px;
        }

        .category-meta {
            display: flex;
            gap: 16px;
            font-size: 12.5px;
            color: var(--gray-text);
            padding-top: 12px;
            border-top: 1px solid var(--gray-light);
        }

        .category-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .category-meta i {
            color: var(--blue);
            font-size: 11px;
        }

        /* État vide */
        .forum-empty {
            text-align: center;
            padding: 70px 20px;
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
        }

        .forum-empty i {
            font-size: 52px;
            color: var(--border);
            display: block;
            margin-bottom: 18px;
        }

        .forum-empty h3 {
            color: var(--dark);
            margin-bottom: 8px;
        }

        .forum-empty p {
            color: var(--gray-text);
            font-size: 15px;
        }

        /* =============================================
           SIDEBAR
        ============================================= */
        .forum-sidebar {
            position: sticky;
            top: 100px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .sidebar-widget {
            background: var(--white);
            border-radius: var(--radius);
            padding: 22px 24px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .widget-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--gray-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .widget-title i {
            color: var(--rose);
            font-size: 14px;
        }

        /* Bouton nouveau sujet */
        .btn-new-topic {
            background: linear-gradient(135deg, var(--blue) 0%, #1a56cc 100%);
            color: white;
            padding: 14px 24px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.22s, box-shadow 0.22s;
            width: 100%;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-new-topic:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 51, 153, 0.28);
        }

        /* Login prompt */
        .login-prompt {
            text-align: center;
            padding: 24px 16px;
            background: var(--gray-bg);
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .login-prompt .lock-icon {
            width: 48px;
            height: 48px;
            background: var(--blue-soft);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            color: var(--blue);
            font-size: 18px;
        }

        .login-prompt p {
            color: var(--gray-text);
            font-size: 13.5px;
            margin-bottom: 14px;
            line-height: 1.5;
        }

        .login-prompt .login-links {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .login-prompt a {
            color: var(--white);
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            background: var(--blue);
            padding: 7px 16px;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .login-prompt a:hover {
            background: var(--blue-dark);
        }

        .login-prompt a.btn-secondary {
            background: var(--gray-light);
            color: var(--dark);
        }

        .login-prompt a.btn-secondary:hover {
            background: var(--border);
        }

        /* Recherche */
        .search-box {
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 10px 42px 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: var(--gray-bg);
            color: var(--dark);
            transition: border-color 0.2s, background 0.2s;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--blue);
            background: var(--white);
        }

        .search-box input::placeholder {
            color: var(--gray-text);
        }

        .search-box button {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-text);
            cursor: pointer;
            padding: 8px 10px;
            transition: color 0.2s;
        }

        .search-box button:hover {
            color: var(--blue);
        }

        /* Liste de sujets */
        .topic-list {
            list-style: none;
        }

        .topic-item {
            padding: 11px 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .topic-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .topic-item:first-child {
            padding-top: 0;
        }

        .topic-link {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            font-size: 13.5px;
            line-height: 1.4;
            display: block;
            margin-bottom: 5px;
            transition: color 0.2s;
        }

        .topic-link:hover {
            color: var(--blue);
        }

        .topic-meta {
            font-size: 12px;
            color: var(--gray-text);
            display: flex;
            gap: 12px;
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

        /* Règles */
        .rules-list {
            list-style: none;
        }

        .rules-list li {
            padding: 7px 0;
            display: flex;
            align-items: flex-start;
            gap: 9px;
            color: var(--gray-text);
            font-size: 13.5px;
            border-bottom: 1px solid var(--gray-light);
        }

        .rules-list li:last-child {
            border-bottom: none;
        }

        .rules-list li i {
            color: var(--green);
            font-size: 12px;
            margin-top: 3px;
            flex-shrink: 0;
        }

        .rules-note {
            font-size: 12px;
            color: var(--gray-text);
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid var(--gray-light);
            display: flex;
            align-items: flex-start;
            gap: 7px;
            line-height: 1.5;
        }

        .rules-note i {
            color: var(--blue);
            font-size: 11px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        /* =============================================
           RESPONSIVE
        ============================================= */
        @media (max-width: 900px) {
            .forum-layout {
                grid-template-columns: 1fr;
            }

            .forum-sidebar {
                position: static;
            }
        }

        @media (max-width: 640px) {

            .page-header {
                padding: 52px 0 72px;
            }
        }

        @media (max-width: 400px) {
        }
    </style>
</head>

<body>
    <?php include 'templates/header.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container" style="position:relative;z-index:1;">
            <div class="header-eyebrow" data-aos="fade-down">
                <i class="fas fa-comments"></i> Communauté GSCC
            </div>
            <h1 data-aos="fade-up">Forum communautaire</h1>
            <p data-aos="fade-up" data-aos-delay="100">
                Partagez vos expériences, posez vos questions, soutenez-vous mutuellement
            </p>
        </div>
        <div class="page-header-wave">
            <svg viewBox="0 0 1440 48" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path fill="#F4F6FB" d="M0,48 C360,0 1080,0 1440,48 L1440,48 L0,48 Z" />
            </svg>
        </div>
    </div>

    <!-- Forum Section -->
    <section class="forum-section">
        <div class="container">
            <!-- Layout principal -->
            <div class="forum-layout">

                <!-- ── Catégories ── -->
                <div class="forum-main">
                    <h2 class="section-heading">
                        <i class="fas fa-folder-open"></i> Catégories du forum
                    </h2>

                    <?php if (empty($categories)): ?>
                        <div class="forum-empty" data-aos="fade-up">
                            <i class="fas fa-comments"></i>
                            <h3>Aucune catégorie pour le moment</h3>
                            <p>Le forum sera bientôt disponible.</p>
                        </div>
                    <?php else: ?>
                        <div class="categories-grid">
                            <?php foreach ($categories as $i => $cat):
                                // Compter les sujets par catégorie
                                $stmt = $pdo->prepare("SELECT COUNT(*) FROM forum_sujets WHERE categorie_id = ?");
                                $stmt->execute([$cat['id']]);
                                $nb_sujets = $stmt->fetchColumn();

                                // Dernier sujet de la catégorie
                                $stmt = $pdo->prepare("SELECT titre, date_creation FROM forum_sujets 
                                                       WHERE categorie_id = ? 
                                                       ORDER BY date_creation DESC LIMIT 1");
                                $stmt->execute([$cat['id']]);
                                $dernier = $stmt->fetch();
                            ?>
                                <a href="forum-categorie.php?id=<?= $cat['id'] ?>"
                                    class="category-card"
                                    data-aos="fade-up"
                                    data-aos-delay="<?= ($i % 3) * 80 ?>">
                                    <div class="category-icon">
                                        <i class="fas fa-folder"></i>
                                    </div>
                                    <h3 class="category-title"><?= e($cat['nom']) ?></h3>
                                    <p class="category-description"><?= e($cat['description']) ?></p>
                                    <div class="category-meta">
                                        <span><i class="fas fa-comment"></i> <?= $nb_sujets ?> sujet<?= $nb_sujets > 1 ? 's' : '' ?></span>
                                        <?php if ($dernier): ?>
                                            <span><i class="far fa-clock"></i> <?= formatDate($dernier['date_creation']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ── Sidebar ── -->
                <div class="forum-sidebar">

                    <!-- Nouveau sujet / Login -->
                    <div class="sidebar-widget" data-aos="fade-left">
                        <?php if (isLoggedIn()): ?>
                            <a href="nouveau-sujet.php" class="btn-new-topic">
                                <i class="fas fa-plus-circle"></i> Nouveau sujet
                            </a>
                        <?php else: ?>
                            <div class="login-prompt">
                                <div class="lock-icon"><i class="fas fa-lock"></i></div>
                                <p>Connectez-vous pour participer au forum et créer des sujets.</p>
                                <div class="login-links">
                                    <a href="connexion.php">Se connecter</a>
                                    <a href="inscription.php" class="btn-secondary">S'inscrire</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Recherche -->
                    <div class="sidebar-widget" data-aos="fade-left" data-aos-delay="80">
                        <h3 class="widget-title">
                            <span>Rechercher</span>
                            <i class="fas fa-search"></i>
                        </h3>
                        <form class="search-box" action="recherche-forum.php" method="GET">
                            <input type="text" name="q" placeholder="Mots-clés...">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>

                    <!-- Derniers sujets -->
                    <div class="sidebar-widget" data-aos="fade-left" data-aos-delay="160">
                        <h3 class="widget-title">
                            <span>Derniers sujets</span>
                            <i class="fas fa-clock"></i>
                        </h3>
                        <ul class="topic-list">
                            <?php
                            $stmt = $pdo->query("SELECT s.*, c.nom as categorie_nom,
                                                CONCAT(u.prenom, ' ', u.nom) as auteur_nom
                                                FROM forum_sujets s
                                                LEFT JOIN forum_categories c ON s.categorie_id = c.id
                                                LEFT JOIN utilisateurs u ON s.auteur_id = u.id
                                                ORDER BY s.date_creation DESC LIMIT 5");
                            $derniers_sujets = $stmt->fetchAll();

                            foreach ($derniers_sujets as $sujet):
                            ?>
                                <li class="topic-item">
                                    <a href="forum-sujet.php?id=<?= $sujet['id'] ?>" class="topic-link">
                                        <?= truncate(e($sujet['titre']), 40) ?>
                                    </a>
                                    <div class="topic-meta">
                                        <span><i class="fas fa-user"></i> <?= e($sujet['auteur_nom'] ?? 'Anonyme') ?></span>
                                        <span><i class="far fa-clock"></i> <?= formatDate($sujet['date_creation']) ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Sujets populaires -->
                    <div class="sidebar-widget" data-aos="fade-left" data-aos-delay="240">
                        <h3 class="widget-title">
                            <span>Sujets populaires</span>
                            <i class="fas fa-fire"></i>
                        </h3>
                        <ul class="topic-list">
                            <?php
                            $stmt = $pdo->query("SELECT s.*, c.nom as categorie_nom,
                                                COUNT(r.id) as nb_reponses
                                                FROM forum_sujets s
                                                LEFT JOIN forum_reponses r ON s.id = r.sujet_id
                                                LEFT JOIN forum_categories c ON s.categorie_id = c.id
                                                GROUP BY s.id
                                                ORDER BY nb_reponses DESC, s.vue_compteur DESC 
                                                LIMIT 5");
                            $sujets_populaires = $stmt->fetchAll();

                            foreach ($sujets_populaires as $sujet):
                            ?>
                                <li class="topic-item">
                                    <a href="forum-sujet.php?id=<?= $sujet['id'] ?>" class="topic-link">
                                        <?= truncate(e($sujet['titre']), 40) ?>
                                    </a>
                                    <div class="topic-meta">
                                        <span><i class="fas fa-eye"></i> <?= number_format($sujet['vue_compteur'] ?? 0) ?></span>
                                        <span><i class="fas fa-reply"></i> <?= $sujet['nb_reponses'] ?? 0 ?> réponse<?= ($sujet['nb_reponses'] ?? 0) > 1 ? 's' : '' ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Règles du forum -->
                    <div class="sidebar-widget" data-aos="fade-left" data-aos-delay="320">
                        <h3 class="widget-title">
                            <span>Règles du forum</span>
                            <i class="fas fa-gavel"></i>
                        </h3>
                        <ul class="rules-list">
                            <li><i class="fas fa-check-circle"></i> Respectez les autres membres</li>
                            <li><i class="fas fa-check-circle"></i> Pas de propos discriminatoires</li>
                            <li><i class="fas fa-check-circle"></i> Pas de spam ou publicité</li>
                            <li><i class="fas fa-check-circle"></i> Restez dans le sujet</li>
                            <li><i class="fas fa-check-circle"></i> Signalez les abus aux modérateurs</li>
                        </ul>
                        <div class="rules-note">
                            <i class="fas fa-info-circle"></i>
                            Les modérateurs se réservent le droit de supprimer tout message inapproprié.
                        </div>
                    </div>

                </div>
            </div>
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