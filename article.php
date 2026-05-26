<?php
// article.php
require_once 'includes/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if ($id <= 0 && empty($slug)) {
    header('Location: blog.php');
    exit;
}

try {
    // Récupérer l'article
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT a.*, c.nom as categorie_nom, c.slug as categorie_slug,
                               CONCAT(u.prenom, ' ', u.nom) as auteur_nom,
                               u.email as auteur_email
                               FROM articles a
                               LEFT JOIN categories c ON a.categorie_id = c.id
                               LEFT JOIN utilisateurs u ON a.auteur_id = u.id
                               WHERE a.id = ? AND a.statut = 'publie'");
        $stmt->execute([$id]);
    } else {
        $stmt = $pdo->prepare("SELECT a.*, c.nom as categorie_nom, c.slug as categorie_slug,
                               CONCAT(u.prenom, ' ', u.nom) as auteur_nom,
                               u.email as auteur_email
                               FROM articles a
                               LEFT JOIN categories c ON a.categorie_id = c.id
                               LEFT JOIN utilisateurs u ON a.auteur_id = u.id
                               WHERE a.slug = ? AND a.statut = 'publie'");
        $stmt->execute([$slug]);
    }
    
    $article = $stmt->fetch();
    
    if (!$article) {
        header('Location: blog.php');
        exit;
    }
    
    // Incrémenter le compteur de vues
    $stmt = $pdo->prepare("UPDATE articles SET vue_compteur = vue_compteur + 1 WHERE id = ?");
    $stmt->execute([$article['id']]);
    
    // Récupérer les articles similaires (même catégorie)
    if ($article['categorie_id']) {
        $stmt = $pdo->prepare("SELECT id, titre, slug, image_couverture, date_publication 
                               FROM articles 
                               WHERE categorie_id = ? AND id != ? AND statut = 'publie'
                               ORDER BY date_publication DESC 
                               LIMIT 3");
        $stmt->execute([$article['categorie_id'], $article['id']]);
        $articles_similaires = $stmt->fetchAll();
    } else {
        $articles_similaires = [];
    }
    
    $page_title = $article['titre'];
    $page_description = $article['meta_description'] ?? truncate(strip_tags($article['resume'] ?? $article['contenu']), 160);

    // Images de galerie
    $gallery_images = [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM article_images WHERE article_id=? ORDER BY ordre ASC");
        $stmt->execute([$article['id']]);
        $gallery_images = $stmt->fetchAll();
    } catch (PDOException $e) { $gallery_images = []; }

} catch (PDOException $e) {
    logError("Erreur article.php: " . $e->getMessage());
    header('Location: blog.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> - <?= SITE_NAME ?></title>
    <meta name="description" content="<?= e($page_description) ?>">
    
    <!-- Open Graph pour le partage -->
    <meta property="og:title" content="<?= e($article['titre']) ?>">
    <meta property="og:description" content="<?= e($page_description) ?>">
    <?php
        $og_img = $article['image_couverture'] ?? '';
        $og_img_url = !empty($og_img)
            ? ((strpos($og_img, 'http') === 0) ? $og_img : rtrim(SITE_URL, '/') . '/' . ltrim($og_img, '/'))
            : rtrim(SITE_URL, '/') . '/assets/images/og-image.jpg';
    ?>
    <meta property="og:image" content="<?= $og_img_url ?>">
    <meta property="og:url" content="<?= SITE_URL ?>/article.php?id=<?= $article['id'] ?>">
    <meta property="og:type" content="article">
    <meta property="article:published_time" content="<?= $article['date_publication'] ?>">
    <meta property="article:author" content="<?= e($article['auteur_nom'] ?? 'GSCC') ?>">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    <style>
        .article-header {
            background: linear-gradient(135deg, #7B1535 0%, #C8375F 55%, #D94F7A 100%);
            color: white !important;
            padding: 60px 0;
            text-align: center;
        }

        .article-header h1 {
            font-size: 2.5rem;
            max-width: 800px;
            margin: 0 auto 20px;
            color: white !important;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
        }

        .article-meta {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            font-size: 14px;
            opacity: 0.9;
            color: white !important;
        }
        
        .article-meta i {
            margin-right: 5px;
        }
        
        .article-section {
            padding: 60px 0;
            background: #f8f9fa;
        }
        
        .article-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .article-image {
            margin: -40px -40px 30px -40px;
            border-radius: 20px 20px 0 0;
            overflow: hidden;
            max-height: 400px;
        }
        
        .article-image img {
            width: 100%;
            height: auto;
            object-fit: cover;
        }
        
        .article-content {
            line-height: 1.8;
            color: #333;
        }
        
        .article-content h2 {
            color: #003399;
            margin: 30px 0 20px;
            font-size: 24px;
        }
        
        .article-content h3 {
            color: #333;
            margin: 25px 0 15px;
            font-size: 20px;
        }
        
        .article-content p {
            margin-bottom: 20px;
        }
        
        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .article-content blockquote {
            border-left: 4px solid #003399;
            padding: 20px;
            background: #f8f9fa;
            font-style: italic;
            margin: 20px 0;
            border-radius: 0 10px 10px 0;
        }
        
        .article-content ul, .article-content ol {
            margin: 20px 0;
            padding-left: 20px;
        }
        
        .article-content li {
            margin-bottom: 10px;
        }
        
        .article-tags {
            margin: 30px 0;
            padding: 20px 0;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .article-tags .tag {
            display: inline-block;
            background: #f0f0f0;
            color: #666;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            margin: 0 5px 5px 0;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .article-tags .tag:hover {
            background: #003399;
            color: white;
        }
        
        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 30px 0;
        }
        
        .share-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .share-btn.facebook  { background: #1877F2; }
        .share-btn.twitter   { background: #000000; }
        .share-btn.instagram { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); }
        .share-btn.linkedin  { background: #0077b5; }
        .share-btn.youtube   { background: #FF0000; }
        .share-btn.tiktok    { background: #000000; }
        .share-btn.whatsapp  { background: #25d366; }
        
        .share-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .author-box {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin: 40px 0;
            display: flex;
            gap: 25px;
            align-items: center;
        }
        
        .author-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #003399;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 40px;
            flex-shrink: 0;
        }
        
        .author-info h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .author-info p {
            color: #666;
            line-height: 1.6;
        }
        
        .similar-articles {
            margin-top: 60px;
        }
        
        .similar-articles h3 {
            text-align: center;
            color: #003399;
            margin-bottom: 30px;
            font-size: 24px;
        }
        
        .similar-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }
        
        .similar-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .similar-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .similar-image {
            height: 150px;
            overflow: hidden;
        }
        
        .similar-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .similar-card:hover .similar-image img {
            transform: scale(1.1);
        }
        
        .similar-content {
            padding: 20px;
        }
        
        .similar-content h4 {
            font-size: 16px;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .similar-content h4 a {
            color: #333;
            text-decoration: none;
        }
        
        .similar-content h4 a:hover {
            color: #003399;
        }
        
        .similar-date {
            font-size: 12px;
            color: #999;
        }
        
        .similar-date i {
            margin-right: 5px;
            color: #003399;
        }
        
        /* ── Galerie photos ── */
        .article-gallery-section {
            margin: 36px 0 30px;
            padding-top: 28px;
            border-top: 1px solid #f0f0f0;
        }
        .article-gallery-section h3 {
            color: #003399;
            font-size: 18px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .article-gallery-section h3 i { color: #D94F7A; }
        .article-swiper { border-radius: 12px; overflow: hidden; }
        .article-swiper .swiper-slide { position: relative; }
        .article-swiper .swiper-slide img {
            width: 100%;
            max-height: 520px;
            height: auto;
            object-fit: contain;
            cursor: zoom-in;
            display: block;
        }
        .gallery-slide-caption {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,.65));
            color: white;
            padding: 24px 18px 14px;
            font-size: 13.5px;
            font-style: italic;
        }
        .article-swiper .swiper-button-next,
        .article-swiper .swiper-button-prev { color: white; }
        .article-swiper .swiper-button-next::after,
        .article-swiper .swiper-button-prev::after { font-size: 20px; }
        .article-swiper .swiper-pagination-bullet-active { background: white; }
        /* Lightbox */
        #artLightbox {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.93);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            cursor: zoom-out;
        }
        #artLightbox.open { display: flex; }
        #artLightboxImg {
            max-width: 92vw;
            max-height: 88vh;
            border-radius: 8px;
            object-fit: contain;
            box-shadow: 0 8px 40px rgba(0,0,0,.6);
        }
        #artLightboxClose {
            position: absolute;
            top: 18px; right: 22px;
            background: rgba(255,255,255,.15);
            border: none; color: white;
            font-size: 22px; width: 42px; height: 42px;
            border-radius: 50%; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }
        #artLightboxClose:hover { background: rgba(255,255,255,.28); }

        @media (max-width: 768px) {
            .article-swiper .swiper-slide img { max-height: 300px; }
        }

        @media (max-width: 768px) {
            .article-header h1 {
                font-size: 2rem;
            }
            
            .article-meta {
                flex-direction: column;
                gap: 10px;
            }
            
            .article-container {
                padding: 20px;
            }
            
            .article-image {
                margin: -20px -20px 20px -20px;
            }
            
            .author-box {
                flex-direction: column;
                text-align: center;
            }
            
            .similar-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <!-- Article Header -->
    <div class="article-header">
        <div class="container">
            <h1 data-aos="fade-up"><?= e($article['titre']) ?></h1>
            <div class="article-meta" data-aos="fade-up" data-aos-delay="100">
                <span><i class="far fa-calendar-alt"></i> <?= formatDateFr($article['date_publication'] ?? $article['date_creation']) ?></span>
                <span><i class="far fa-user"></i> <?= e($article['auteur_nom'] ?? 'GSCC') ?></span>
                <?php if (!empty($article['categorie_nom'])): ?>
                    <span><i class="far fa-folder"></i> <?= e($article['categorie_nom']) ?></span>
                <?php endif; ?>
                <span><i class="far fa-eye"></i> <?= number_format($article['vue_compteur'] ?? 0) ?> vues</span>
            </div>
        </div>
    </div>

    <!-- Article Content -->
    <section class="article-section">
        <div class="container">
            <div class="article-container" data-aos="fade-up">
                <?php if (!empty($article['image_couverture'])): ?>
                    <?php
                        $art_img = $article['image_couverture'];
                        $art_img_url = (strpos($art_img, 'http') === 0)
                            ? $art_img
                            : rtrim(SITE_URL, '/') . '/' . ltrim($art_img, '/');
                    ?>
                    <div class="article-image">
                        <img src="<?= $art_img_url ?>" alt="<?= e($article['titre']) ?>">
                    </div>
                <?php endif; ?>
                
                <div class="article-content">
                    <?= $article['contenu'] ?>
                </div>

                <?php if (!empty($gallery_images)): ?>
                <div class="article-gallery-section">
                    <h3><i class="fas fa-images"></i> Galerie photos</h3>
                    <div class="swiper article-swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($gallery_images as $gi):
                                $gi_url = rtrim(SITE_URL,'/') . '/' . ltrim($gi['fichier'],'/');
                            ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($gi_url) ?>"
                                     alt="<?= htmlspecialchars($gi['legende'] ?? '') ?>"
                                     onclick="artLightboxOpen(this.src)">
                                <?php if (!empty($gi['legende'])): ?>
                                <div class="gallery-slide-caption"><?= htmlspecialchars($gi['legende']) ?></div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($gallery_images) > 1): ?>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-pagination"></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($article['tags'])): ?>
                    <div class="article-tags">
                        <strong><i class="fas fa-tags"></i> Tags :</strong>
                        <?php 
                        $tags = explode(',', $article['tags']);
                        foreach ($tags as $tag): 
                            $tag = trim($tag);
                            if (!empty($tag)):
                        ?>
                            <a href="blog.php?tag=<?= urlencode($tag) ?>" class="tag">#<?= e($tag) ?></a>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                <?php endif; ?>
                
                <!-- Réseaux sociaux GSCC -->
                <div style="text-align:center;margin:30px 0;">
                    <p style="font-size:13px;color:#888;margin-bottom:14px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;">Suivez-nous</p>
                    <div class="share-buttons">
                        <a href="https://web.facebook.com/GSCCHAITI" target="_blank" rel="noopener noreferrer"
                           class="share-btn facebook" title="Facebook GSCC">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://x.com/gscchaiti_" target="_blank" rel="noopener noreferrer"
                           class="share-btn twitter" title="X (Twitter) GSCC">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.instagram.com/gscchaiti" target="_blank" rel="noopener noreferrer"
                           class="share-btn instagram" title="Instagram GSCC">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://www.linkedin.com/company/98641192/" target="_blank" rel="noopener noreferrer"
                           class="share-btn linkedin" title="LinkedIn GSCC">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="https://www.youtube.com/@gscchaiti" target="_blank" rel="noopener noreferrer"
                           class="share-btn youtube" title="YouTube GSCC">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="https://www.tiktok.com/@gscchaiti" target="_blank" rel="noopener noreferrer"
                           class="share-btn tiktok" title="TikTok GSCC">
                            <i class="fab fa-tiktok"></i>
                        </a>
                        <a href="https://wa.me/50929474722" target="_blank" rel="noopener noreferrer"
                           class="share-btn whatsapp" title="WhatsApp GSCC">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Boîte auteur -->
                <?php if (!empty($article['auteur_nom'])): ?>
                <div class="author-box">
                    <div class="author-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="author-info">
                        <h3><?= e($article['auteur_nom']) ?></h3>
                        <p>
                            <?= e($article['auteur_bio'] ?? 'Membre de l\'équipe GSCC, engagé dans la lutte contre le cancer en Haïti.') ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Articles similaires -->
            <?php if (!empty($articles_similaires)): ?>
                <div class="similar-articles">
                    <h3 data-aos="fade-up">Articles similaires</h3>
                    <div class="similar-grid">
                        <?php foreach ($articles_similaires as $similaire): ?>
                            <div class="similar-card" data-aos="fade-up" data-aos-delay="100">
                                <div class="similar-image">
                                    <?php
                                        $sim_img = $similaire['image_couverture'] ?? '';
                                        $sim_img_url = !empty($sim_img)
                                            ? ((strpos($sim_img, 'http') === 0)
                                                ? $sim_img
                                                : rtrim(SITE_URL, '/') . '/' . ltrim($sim_img, '/'))
                                            : rtrim(SITE_URL, '/') . '/assets/images/blog-placeholder.jpg';
                                    ?>
                                    <img src="<?= $sim_img_url ?>" 
                                         alt="<?= e($similaire['titre']) ?>">
                                </div>
                                <div class="similar-content">
                                    <h4><a href="article.php?id=<?= $similaire['id'] ?>"><?= e($similaire['titre']) ?></a></h4>
                                    <div class="similar-date">
                                        <i class="far fa-calendar-alt"></i> <?= formatDateFr($similaire['date_publication']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>

    <!-- Lightbox -->
    <div id="artLightbox" onclick="artLightboxClose()">
        <button id="artLightboxClose" onclick="artLightboxClose()" aria-label="Fermer">×</button>
        <img id="artLightboxImg" src="" alt="">
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
    <?php if (!empty($gallery_images)): ?>
    new Swiper('.article-swiper', {
        loop: <?= count($gallery_images) > 1 ? 'true' : 'false' ?>,
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        pagination: { el: '.swiper-pagination', clickable: true },
        slidesPerView: 1,
        spaceBetween: 0,
        autoHeight: true,
    });
    <?php endif; ?>
    function artLightboxOpen(src) {
        document.getElementById('artLightboxImg').src = src;
        document.getElementById('artLightbox').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function artLightboxClose() {
        document.getElementById('artLightbox').classList.remove('open');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') artLightboxClose(); });
    </script>
</body>
</html>