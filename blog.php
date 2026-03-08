<?php
// blog.php
require_once 'includes/config.php';

$page_title = 'Blog';
$page_description = 'Actualités, conseils et informations sur la lutte contre le cancer en Haïti.';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6; // Articles par page
$offset = ($page - 1) * $limit;

// Filtre par catégorie
$categorie_id = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

try {
    // Récupérer les catégories pour le filtre
    $stmt = $pdo->query("SELECT * FROM categories WHERE type = 'blog' ORDER BY nom");
    $categories = $stmt->fetchAll();

    // Construction de la requête
    $where = ["a.statut = 'publie'"];
    $params = [];

    if ($categorie_id > 0) {
        $where[] = "a.categorie_id = ?";
        $params[] = $categorie_id;
    }

    if (!empty($search)) {
        $where[] = "(a.titre LIKE ? OR a.contenu LIKE ? OR a.resume LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $where_clause = "WHERE " . implode(" AND ", $where);

    // Compter le nombre total d'articles
    $sql_count = "SELECT COUNT(*) FROM articles a $where_clause";
    $stmt = $pdo->prepare($sql_count);
    $stmt->execute($params);
    $total_articles = $stmt->fetchColumn();
    $total_pages = ceil($total_articles / $limit);

    // Récupérer les articles
    $sql = "SELECT a.*, c.nom as categorie_nom, c.slug as categorie_slug,
            CONCAT(u.prenom, ' ', u.nom) as auteur_nom
            FROM articles a
            LEFT JOIN categories c ON a.categorie_id = c.id
            LEFT JOIN utilisateurs u ON a.auteur_id = u.id
            $where_clause
            ORDER BY a.date_publication DESC
            LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $articles = $stmt->fetchAll();

    // Récupérer les articles populaires
    $stmt = $pdo->query("SELECT a.*, c.nom as categorie_nom 
                         FROM articles a 
                         LEFT JOIN categories c ON a.categorie_id = c.id 
                         WHERE a.statut = 'publie' 
                         ORDER BY a.vue_compteur DESC 
                         LIMIT 5");
    $articles_populaires = $stmt->fetchAll();
} catch (PDOException $e) {
    logError("Erreur blog.php: " . $e->getMessage());
    $articles = [];
    $categories = [];
    $articles_populaires = [];
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
            --rose: #D94F7A;
            --rose-light: #F2A8C0;
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
            max-width: 480px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* =============================================
           BLOG SECTION
        ============================================= */
        .blog-section {
            padding: 70px 0 90px;
            background: var(--gray-bg);
        }

        .blog-layout {
            display: grid;
            grid-template-columns: 1fr 330px;
            gap: 36px;
            align-items: start;
        }

        /* =============================================
           CARTES D'ARTICLES
        ============================================= */
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
            gap: 26px;
        }

        .blog-card {
            background: var(--white);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            transition: transform 0.28s ease, box-shadow 0.28s ease;
            display: flex;
            flex-direction: column;
        }

        .blog-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-h);
        }

        .blog-image {
            position: relative;
            height: 210px;
            overflow: hidden;
        }

        .blog-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .blog-card:hover .blog-image img {
            transform: scale(1.07);
        }

        .blog-category {
            position: absolute;
            top: 14px;
            left: 14px;
            background: var(--blue);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            z-index: 1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .blog-content {
            padding: 22px 24px 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .blog-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 12.5px;
            color: var(--gray-text);
            margin-bottom: 12px;
        }

        .blog-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .blog-meta i {
            color: var(--blue);
            font-size: 11px;
        }

        .blog-title {
            font-size: 17px;
            font-weight: 700;
            line-height: 1.45;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .blog-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.22s;
        }

        .blog-title a:hover {
            color: var(--blue);
        }

        .blog-excerpt {
            color: var(--gray-text);
            font-size: 14px;
            line-height: 1.65;
            margin-bottom: 18px;
            flex: 1;
        }

        .blog-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 14px;
            border-top: 1px solid var(--gray-light);
            margin-top: auto;
        }

        .read-more {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--blue);
            font-weight: 600;
            font-size: 13.5px;
            text-decoration: none;
            transition: gap 0.22s, color 0.22s;
        }

        .read-more:hover {
            color: var(--rose);
            gap: 9px;
        }

        .blog-stats {
            display: flex;
            gap: 10px;
            font-size: 12.5px;
            color: var(--gray-text);
        }

        .blog-stats span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .blog-stats i {
            font-size: 11px;
        }

        /* État vide */
        .blog-empty {
            text-align: center;
            padding: 70px 20px;
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
        }

        .blog-empty i {
            font-size: 52px;
            color: var(--border);
            display: block;
            margin-bottom: 18px;
        }

        .blog-empty h3 {
            color: var(--dark);
            margin-bottom: 8px;
        }

        .blog-empty p {
            color: var(--gray-text);
            font-size: 15px;
        }

        /* =============================================
           PAGINATION
        ============================================= */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            margin-top: 48px;
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
           SIDEBAR
        ============================================= */
        .blog-sidebar {
            position: sticky;
            top: 100px;
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .sidebar-widget {
            background: var(--white);
            border-radius: var(--radius);
            padding: 24px 26px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .widget-title {
            font-size: 15.5px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 18px;
            padding-bottom: 13px;
            border-bottom: 2px solid var(--gray-light);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .widget-title i {
            color: var(--rose);
            font-size: 14px;
        }

        /* Recherche */
        .search-form {
            display: flex;
            gap: 8px;
        }

        .search-input {
            flex: 1;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: var(--gray-bg);
            color: var(--dark);
            transition: border-color 0.2s, background 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--blue);
            background: white;
        }

        .search-btn {
            background: var(--blue);
            color: white;
            border: none;
            padding: 0 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.22s;
            flex-shrink: 0;
        }

        .search-btn:hover {
            background: var(--rose);
        }

        /* Catégories */
        .categories-list {
            list-style: none;
        }

        .category-item {
            border-bottom: 1px solid var(--gray-light);
        }

        .category-item:last-child {
            border-bottom: none;
        }

        .category-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--gray-text);
            text-decoration: none;
            padding: 10px 4px;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s, padding-left 0.2s;
        }

        .category-link:hover,
        .category-link.active {
            color: var(--blue);
            padding-left: 8px;
        }

        .category-link.active {
            font-weight: 700;
        }

        .category-count {
            background: var(--gray-light);
            color: var(--gray-text);
            padding: 2px 9px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
        }

        .category-link.active .category-count {
            background: #E8EEFF;
            color: var(--blue);
        }

        /* Articles populaires */
        .popular-item {
            display: flex;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .popular-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .popular-item:first-child {
            padding-top: 0;
        }

        .popular-image {
            width: 72px;
            height: 72px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .popular-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .popular-content {
            flex: 1;
            min-width: 0;
        }

        .popular-content h4 {
            font-size: 13.5px;
            font-weight: 600;
            line-height: 1.4;
            margin-bottom: 6px;
            color: var(--dark);
        }

        .popular-content h4 a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s;
        }

        .popular-content h4 a:hover {
            color: var(--blue);
        }

        .popular-date {
            font-size: 12px;
            color: var(--gray-text);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .popular-date i {
            color: var(--blue);
            font-size: 11px;
        }

        /* Tags */
        .tags-cloud {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .tag {
            background: var(--gray-light);
            color: var(--gray-text);
            padding: 5px 13px;
            border-radius: 20px;
            font-size: 12.5px;
            font-weight: 500;
            text-decoration: none;
            border: 1px solid var(--border);
            transition: all 0.22s;
        }

        .tag:hover {
            background: var(--blue);
            color: white;
            border-color: var(--blue);
        }

        /* Newsletter */
        .widget-newsletter {
            background: linear-gradient(135deg, #003399 0%, #1a56cc 100%);
            border-color: transparent;
        }

        .widget-newsletter .widget-title {
            color: white;
            border-bottom-color: rgba(255, 255, 255, 0.15);
        }

        .widget-newsletter .widget-title i {
            color: var(--rose-light);
        }

        .widget-newsletter p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.82);
            margin-bottom: 16px;
            line-height: 1.65;
        }

        .widget-newsletter .search-input {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .widget-newsletter .search-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .widget-newsletter .search-input:focus {
            border-color: rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.18);
        }

        .widget-newsletter .search-btn {
            background: white;
            color: var(--blue);
        }

        .widget-newsletter .search-btn:hover {
            background: var(--rose-light);
            color: white;
        }

        /* =============================================
           RESPONSIVE
        ============================================= */
        @media (max-width: 900px) {
            .blog-layout {
                grid-template-columns: 1fr;
            }

            .blog-sidebar {
                position: static;
            }
        }

        @media (max-width: 600px) {
            .blog-grid {
                grid-template-columns: 1fr;
            }

            .page-header {
                padding: 52px 0 72px;
            }
        }
    </style>
</head>

<body>
    <?php include 'templates/header.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container" style="position:relative;z-index:1;">
            <div class="header-eyebrow" data-aos="fade-down">
                <i class="fas fa-pen-nib"></i> GSCC — Actualités
            </div>
            <h1 data-aos="fade-up">Notre Blog</h1>
            <p data-aos="fade-up" data-aos-delay="100">
                Actualités, conseils et informations sur la lutte contre le cancer en Haïti
            </p>
        </div>
        <!-- Vague de transition -->
        <div class="page-header-wave">
            <svg viewBox="0 0 1440 48" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path fill="#F4F6FB" d="M0,48 C360,0 1080,0 1440,48 L1440,48 L0,48 Z" />
            </svg>
        </div>
    </div>

    <!-- Blog Section -->
    <section class="blog-section">
        <div class="container">
            <div class="blog-layout">

                <!-- ── Articles principaux ── -->
                <div class="blog-main">
                    <?php if (empty($articles)): ?>
                        <div class="blog-empty" data-aos="fade-up">
                            <i class="fas fa-newspaper"></i>
                            <h3>Aucun article trouvé</h3>
                            <p>Revenez plus tard pour découvrir nos actualités.</p>
                        </div>
                    <?php else: ?>
                        <div class="blog-grid">
                            <?php foreach ($articles as $i => $article): ?>
                                <article class="blog-card" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 80 ?>">
                                    <div class="blog-image">
                                        <?php
                                            $img = $article['image_couverture'] ?? '';
                                            if (!empty($img)) {
                                                $img_url = (strpos($img, 'http') === 0)
                                                    ? $img
                                                    : rtrim(SITE_URL, '/') . '/' . ltrim($img, '/');
                                            } else {
                                                $img_url = rtrim(SITE_URL, '/') . '/assets/images/blog-placeholder.jpg';
                                            }
                                        ?>
                                        <img src="<?= $img_url ?>"
                                            alt="<?= e($article['titre']) ?>">
                                        <?php if (!empty($article['categorie_nom'])): ?>
                                            <div class="blog-category"><?= e($article['categorie_nom']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="blog-content">
                                        <div class="blog-meta">
                                            <span><i class="far fa-calendar-alt"></i> <?= formatDateFr($article['date_publication'] ?? $article['date_creation']) ?></span>
                                            <span><i class="far fa-user"></i> <?= e($article['auteur_nom'] ?? 'GSCC') ?></span>
                                        </div>
                                        <h3 class="blog-title">
                                            <a href="article.php?id=<?= $article['id'] ?>&slug=<?= e($article['slug']) ?>">
                                                <?= e($article['titre']) ?>
                                            </a>
                                        </h3>
                                        <p class="blog-excerpt">
                                            <?= truncate(strip_tags($article['resume'] ?? $article['contenu']), 150) ?>
                                        </p>
                                        <div class="blog-footer">
                                            <a href="article.php?id=<?= $article['id'] ?>" class="read-more">
                                                Lire la suite <i class="fas fa-arrow-right"></i>
                                            </a>
                                            <div class="blog-stats">
                                                <span><i class="far fa-eye"></i> <?= number_format($article['vue_compteur'] ?? 0) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?page=<?= $i ?><?= $categorie_id ? '&categorie=' . $categorie_id : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                                        class="page-link <?= $page == $i ? 'active' : '' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- ── Sidebar ── -->
                <div class="blog-sidebar">

                    <!-- Widget Recherche -->
                    <div class="sidebar-widget" data-aos="fade-left">
                        <h3 class="widget-title">
                            <i class="fas fa-search"></i> Rechercher
                        </h3>
                        <form class="search-form" action="blog.php" method="GET">
                            <input type="text" name="search" class="search-input"
                                placeholder="Rechercher un article..." value="<?= e($search) ?>">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Widget Catégories -->
                    <?php if (!empty($categories)): ?>
                        <div class="sidebar-widget" data-aos="fade-left" data-aos-delay="80">
                            <h3 class="widget-title">
                                <i class="fas fa-folder-open"></i> Catégories
                            </h3>
                            <ul class="categories-list">
                                <li class="category-item">
                                    <a href="blog.php" class="category-link <?= !$categorie_id ? 'active' : '' ?>">
                                        <span>Tous les articles</span>
                                        <span class="category-count"><?= $total_articles ?></span>
                                    </a>
                                </li>
                                <?php foreach ($categories as $cat):
                                    // Compter les articles par catégorie
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE categorie_id = ? AND statut = 'publie'");
                                    $stmt->execute([$cat['id']]);
                                    $count = $stmt->fetchColumn();
                                ?>
                                    <?php if ($count > 0): ?>
                                        <li class="category-item">
                                            <a href="?categorie=<?= $cat['id'] ?>" class="category-link <?= $categorie_id == $cat['id'] ? 'active' : '' ?>">
                                                <span><?= e($cat['nom']) ?></span>
                                                <span class="category-count"><?= $count ?></span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Widget Articles populaires -->
                    <?php if (!empty($articles_populaires)): ?>
                        <div class="sidebar-widget" data-aos="fade-left" data-aos-delay="160">
                            <h3 class="widget-title">
                                <i class="fas fa-fire"></i> Articles populaires
                            </h3>
                            <?php foreach ($articles_populaires as $pop): ?>
                                <div class="popular-item">
                                    <div class="popular-image">
                                        <?php
                                            $pop_img = $pop['image_couverture'] ?? '';
                                            if (!empty($pop_img)) {
                                                $pop_img_url = (strpos($pop_img, 'http') === 0)
                                                    ? $pop_img
                                                    : rtrim(SITE_URL, '/') . '/' . ltrim($pop_img, '/');
                                            } else {
                                                $pop_img_url = rtrim(SITE_URL, '/') . '/assets/images/blog-placeholder.jpg';
                                            }
                                        ?>
                                        <img src="<?= $pop_img_url ?>"
                                            alt="<?= e($pop['titre']) ?>">
                                    </div>
                                    <div class="popular-content">
                                        <h4><a href="article.php?id=<?= $pop['id'] ?>"><?= e($pop['titre']) ?></a></h4>
                                        <div class="popular-date">
                                            <i class="far fa-calendar-alt"></i> <?= formatDateFr($pop['date_publication']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Widget Tags -->
                    <div class="sidebar-widget" data-aos="fade-left" data-aos-delay="240">
                        <h3 class="widget-title">
                            <i class="fas fa-tags"></i> Tags populaires
                        </h3>
                        <div class="tags-cloud">
                            <a href="?tag=cancer" class="tag">cancer</a>
                            <a href="?tag=prévention" class="tag">prévention</a>
                            <a href="?tag=dépistage" class="tag">dépistage</a>
                            <a href="?tag=soutien" class="tag">soutien</a>
                            <a href="?tag=haïti" class="tag">haïti</a>
                            <a href="?tag=traitement" class="tag">traitement</a>
                            <a href="?tag=espoir" class="tag">espoir</a>
                            <a href="?tag=témoignage" class="tag">témoignage</a>
                        </div>
                    </div>

                    <!-- Widget Newsletter -->
                    <div class="sidebar-widget widget-newsletter" data-aos="fade-left" data-aos-delay="320">
                        <h3 class="widget-title">
                            <i class="fas fa-envelope"></i> Newsletter
                        </h3>
                        <p>Recevez nos derniers articles directement dans votre boîte mail.</p>
                        <form class="search-form" action="newsletter-subscribe.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <input type="email" name="email" class="search-input"
                                placeholder="Votre email..." required>
                            <button type="submit" class="search-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
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