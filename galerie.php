<?php
// galerie.php
require_once 'includes/config.php';

$page_title       = 'Galerie';
$page_description = 'Découvrez en images nos actions, événements et moments forts.';

$type = isset($_GET['type']) ? sanitize($_GET['type']) : 'photos';
if ($type === 'albums') $type = 'photos';

try {
    $stmt = $pdo->prepare(
        "SELECT * FROM galerie
         WHERE type = ? AND est_public = 1
         ORDER BY date_upload DESC
         LIMIT 60"
    );
    $stmt->execute([$type === 'photos' ? 'photo' : 'video']);
    $medias = $stmt->fetchAll();

    $stmt = $pdo->prepare(
        "SELECT * FROM galerie
         WHERE type = 'video' AND est_public = 1
         ORDER BY date_upload DESC
         LIMIT 12"
    );
    $stmt->execute();
    $videos = $stmt->fetchAll();

} catch (PDOException $e) {
    logError("Erreur galerie.php: " . $e->getMessage());
    $medias = [];
    $videos = [];
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet">

    <style>
        :root {
            --blue:      #003399;
            --blue-dark: #002270;
            --blue-soft: rgba(0,51,153,0.08);
            --rose:      #D94F7A;
            --gray-bg:   #F4F6FB;
            --gray-light:#EEF1F8;
            --gray-text: #6B7280;
            --border:    #E5E9F2;
            --white:     #FFFFFF;
            --dark:      #1A2240;
            --radius:    12px;
            --shadow:    0 4px 24px rgba(0,51,153,0.08);
            --shadow-h:  0 16px 48px rgba(0,51,153,0.15);
        }

        /* ── PAGE HEADER ── */
        .page-header {
            background: linear-gradient(135deg, #003399 0%, #1a56cc 60%, #1a7abf 100%);
            color: white; padding: 72px 0 90px;
            text-align: center; position: relative; overflow: hidden;
        }
        .page-header::before, .page-header::after {
            content: ''; position: absolute; border-radius: 50%;
            opacity: 0.07; background: white; pointer-events: none;
        }
        .page-header::before { width: 420px; height: 420px; top: -160px; right: -80px; }
        .page-header::after  { width: 260px; height: 260px; bottom: -100px; left: -60px; }
        .page-header-wave    { position: absolute; bottom: -1px; left: 0; width: 100%; line-height: 0; }
        .page-header-wave svg { display: block; }
        .header-eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25);
            color: white; font-size: 11px; font-weight: 600; letter-spacing: 2px;
            text-transform: uppercase; padding: 6px 16px; border-radius: 20px; margin-bottom: 20px;
        }
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.2rem, 5vw, 3.4rem);
            font-weight: 700; margin-bottom: 14px; letter-spacing: -0.5px;
        }
        .page-header p {
            font-size: 1.05rem; color: rgba(255,255,255,0.82);
            max-width: 460px; margin: 0 auto; line-height: 1.7;
        }

        /* ── SECTION GALERIE ── */
        .gallery-section { padding: 60px 0 90px; background: var(--gray-bg); }

        /* ── TABS ── */
        .gallery-tabs {
            display: flex; justify-content: center;
            gap: 14px; margin-bottom: 44px; flex-wrap: wrap;
        }
        .tab-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 11px 28px; border-radius: 50px;
            background: var(--white); color: var(--dark);
            text-decoration: none; font-size: 14px; font-weight: 600;
            border: 1.5px solid var(--border); box-shadow: var(--shadow);
            transition: all 0.22s;
        }
        .tab-btn:hover, .tab-btn.active {
            background: var(--blue); color: white; border-color: var(--blue);
            transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,51,153,0.25);
        }
        .tab-count {
            background: rgba(255,255,255,0.25);
            padding: 1px 8px; border-radius: 10px; font-size: 12px;
        }

        /* ── GRILLE PHOTOS ── */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .gallery-item {
            position: relative; border-radius: var(--radius); overflow: hidden;
            box-shadow: var(--shadow); cursor: pointer;
            transition: transform 0.28s ease, box-shadow 0.28s ease;
            display: block; text-decoration: none;
        }
        .gallery-item:hover { transform: translateY(-6px); box-shadow: var(--shadow-h); }
        .gallery-image      { height: 240px; position: relative; overflow: hidden; }
        .gallery-image img  {
            width: 100%; height: 100%; object-fit: cover;
            transition: transform 0.5s ease; display: block;
        }
        .gallery-item:hover .gallery-image img { transform: scale(1.07); }
        .gallery-overlay {
            position: absolute; bottom: 0; left: 0; width: 100%;
            padding: 36px 18px 18px;
            background: linear-gradient(to top, rgba(10,20,50,0.88) 0%, rgba(10,20,50,0.4) 70%, transparent 100%);
            color: white; opacity: 0; transition: opacity 0.28s ease;
        }
        .gallery-item:hover .gallery-overlay { opacity: 1; }
        .gallery-title {
            font-size: 15px; font-weight: 700; margin-bottom: 5px;
            color: #fff; line-height: 1.35; text-shadow: 0 1px 4px rgba(0,0,0,0.5);
        }
        .gallery-meta { font-size: 12px; color: rgba(255,255,255,0.85); display: flex; align-items: center; gap: 6px; }
        .gallery-meta i { font-size: 10px; }

        /* ── VIDÉOS ── */
        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 24px;
        }
        .video-card {
            background: var(--white); border-radius: var(--radius);
            overflow: hidden; border: 1px solid var(--border); box-shadow: var(--shadow);
            transition: transform 0.28s ease, box-shadow 0.28s ease;
        }
        .video-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-h); }
        .video-container   { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; }
        .video-container iframe {
            position: absolute; top: 0; left: 0;
            width: 100%; height: 100%; border: none;
        }
        .video-info   { padding: 18px 20px; }
        .video-info h4 {
            font-size: 15px; font-weight: 700; color: var(--dark);
            margin-bottom: 6px; line-height: 1.4;
        }
        .video-info p  { color: var(--gray-text); font-size: 13px; margin: 0; line-height: 1.55; }

        /* ── ÉTAT VIDE ── */
        .empty-gallery {
            text-align: center; padding: 64px 30px;
            background: var(--white); border-radius: var(--radius);
            border: 2px dashed var(--border);
        }
        .empty-icon {
            width: 80px; height: 80px;
            background: var(--blue-soft); border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px; font-size: 32px; color: var(--blue);
        }
        .empty-gallery h3 {
            font-size: 20px; font-weight: 700; color: var(--dark); margin-bottom: 10px;
        }
        .empty-gallery > p {
            color: var(--gray-text); font-size: 14.5px;
            line-height: 1.75; max-width: 480px; margin: 0 auto 24px;
        }

        /* Boîte d'instructions */
        .how-to-box {
            background: var(--gray-bg); border: 1px solid var(--border);
            border-radius: 10px; padding: 20px 24px;
            max-width: 540px; margin: 0 auto;
            text-align: left;
        }
        .how-to-box .how-title {
            font-size: 12px; font-weight: 700; color: var(--blue);
            letter-spacing: 1px; text-transform: uppercase;
            margin-bottom: 12px; display: flex; align-items: center; gap: 7px;
        }
        .how-to-box ol {
            padding-left: 18px; margin: 0;
            font-size: 14px; color: var(--gray-text); line-height: 2;
        }
        .how-to-box ol li strong { color: var(--dark); font-weight: 600; }
        .how-to-box code {
            background: rgba(0,51,153,0.08); color: var(--blue);
            padding: 1px 7px; border-radius: 4px;
            font-family: monospace; font-size: 13px;
        }

        .btn-import {
            display: inline-flex; align-items: center; gap: 9px;
            background: linear-gradient(135deg, var(--blue), #1a56cc);
            color: white; padding: 13px 28px; border-radius: 30px;
            font-size: 15px; font-weight: 600; text-decoration: none;
            box-shadow: 0 4px 16px rgba(0,51,153,0.25);
            transition: all 0.2s; margin-top: 22px;
        }
        .btn-import:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,51,153,0.32); color: white;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .gallery-grid  { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .videos-grid   { grid-template-columns: 1fr; }
            .gallery-image { height: 180px; }
            .page-header   { padding: 52px 0 72px; }
        }
        @media (max-width: 480px) {
            .gallery-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container" style="position:relative;z-index:1;">
            <div class="header-eyebrow" data-aos="fade-down">
                <i class="fas fa-images"></i> GSCC — Médiathèque
            </div>
            <h1 data-aos="fade-up">Notre Galerie</h1>
            <p data-aos="fade-up" data-aos-delay="100">
                Découvrez nos actions, événements et moments forts en images
            </p>
        </div>
        <div class="page-header-wave">
            <svg viewBox="0 0 1440 48" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path fill="#F4F6FB" d="M0,48 C360,0 1080,0 1440,48 L1440,48 L0,48 Z"/>
            </svg>
        </div>
    </div>

    <!-- Gallery Section -->
    <section class="gallery-section">
        <div class="container">

            <!-- Tabs : Photos & Vidéos -->
            <div class="gallery-tabs" data-aos="fade-up">
                <a href="?type=photos" class="tab-btn <?= $type === 'photos' ? 'active' : '' ?>">
                    <i class="fas fa-images"></i> Photos
                    <?php if (!empty($medias) && $type === 'photos'): ?>
                        <span class="tab-count"><?= count($medias) ?></span>
                    <?php endif; ?>
                </a>
                <a href="?type=videos" class="tab-btn <?= $type === 'videos' ? 'active' : '' ?>">
                    <i class="fas fa-video"></i> Vidéos
                    <?php if (!empty($videos) && $type === 'videos'): ?>
                        <span class="tab-count"><?= count($videos) ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <?php if ($type === 'videos'): ?>

                <!-- ══════════ VIDÉOS ══════════ -->
                <?php if (empty($videos)): ?>

                    <div class="empty-gallery" data-aos="fade-up">
                        <div class="empty-icon"><i class="fas fa-video"></i></div>
                        <h3>Aucune vidéo pour le moment</h3>
                        <p>Ajoutez vos liens YouTube dans la base de données pour les voir apparaître ici.</p>

                        <div class="how-to-box">
                            <div class="how-title">
                                <i class="fas fa-info-circle"></i> Comment ajouter une vidéo YouTube
                            </div>
                            <ol>
                                <li>Ouvrez votre vidéo sur <strong>YouTube</strong> et copiez son URL</li>
                                <li>Repérez l'<strong>identifiant</strong> dans l'URL :<br>
                                    <code>youtube.com/watch?v=<strong>IDENTIFIANT_ICI</strong></code>
                                </li>
                                <li>Dans <strong>phpMyAdmin</strong>, ouvrez la table <code>galerie</code> et cliquez <strong>Insérer</strong></li>
                                <li>Remplissez les champs :
                                    <ul style="margin:4px 0 0 4px;line-height:1.9;">
                                        <li><code>type</code> = <strong>video</strong></li>
                                        <li><code>titre</code> = Nom de votre vidéo</li>
                                        <li><code>description</code> = Courte description (facultatif)</li>
                                        <li><code>url_fichier</code> = <strong>l'identifiant YouTube</strong> uniquement<br>
                                            <span style="font-size:12px;color:var(--gray-text);">Exemple : <code>dQw4w9WgXcQ</code></span>
                                        </li>
                                        <li><code>est_public</code> = <strong>1</strong></li>
                                    </ul>
                                </li>
                                <li>Sauvegardez — la vidéo s'affiche immédiatement ✅</li>
                            </ol>
                        </div>
                    </div>

                <?php else: ?>

                    <div class="videos-grid">
                        <?php foreach ($videos as $i => $video):
                            // url_fichier = identifiant YouTube (ex: dQw4w9WgXcQ)
                            $vid_id    = htmlspecialchars(trim($video['url_fichier'] ?? ''));
                            $vid_titre = htmlspecialchars($video['titre'] ?? 'Vidéo GSCC');
                            $vid_desc  = htmlspecialchars($video['description'] ?? '');
                            $vid_date  = isset($video['date_upload'])
                                            ? date('d/m/Y', strtotime($video['date_upload'])) : '';
                            if (!$vid_id) continue;
                        ?>
                        <div class="video-card" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 80 ?>">
                            <div class="video-container">
                                <iframe
                                    src="https://www.youtube.com/embed/<?= $vid_id ?>"
                                    title="<?= $vid_titre ?>"
                                    allowfullscreen
                                    loading="lazy">
                                </iframe>
                            </div>
                            <div class="video-info">
                                <h4><?= $vid_titre ?></h4>
                                <?php if ($vid_desc): ?><p><?= $vid_desc ?></p><?php endif; ?>
                                <?php if ($vid_date): ?>
                                <p style="margin-top:8px;font-size:12px;color:var(--gray-text);">
                                    <i class="far fa-calendar-alt"></i> <?= $vid_date ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>

            <?php else: ?>

                <!-- ══════════ PHOTOS ══════════ -->
                <?php if (empty($medias)): ?>

                    <div class="empty-gallery" data-aos="fade-up">
                        <div class="empty-icon"><i class="fas fa-images"></i></div>
                        <h3>Aucune photo pour le moment</h3>
                        <p>Importez vos photos depuis votre ordinateur pour les afficher dans cette galerie.</p>

                        <div class="how-to-box">
                            <div class="how-title">
                                <i class="fas fa-info-circle"></i> Comment ajouter vos photos
                            </div>
                            <ol>
                                <li>
                                    Placez vos images dans le dossier :<br>
                                    <code>uploads/galerie/</code>
                                    <span style="font-size:12px;color:var(--gray-text);"> (créez-le s'il n'existe pas)</span>
                                </li>
                                <li>
                                    Ouvrez dans votre navigateur :<br>
                                    <code>http://localhost/gscc/import-galerie-local.php</code>
                                </li>
                                <li>Cliquez <strong>Lancer l'import</strong> — toutes vos images sont détectées et enregistrées automatiquement</li>
                                <li>Rechargez cette page, vos photos apparaissent ici ✅</li>
                            </ol>
                        </div>

                        <?php if (isAdmin() || isModerator()): ?>
                        <a href="import-galerie-local.php" class="btn-import">
                            <i class="fas fa-upload"></i> Importer mes photos maintenant
                        </a>
                        <?php endif; ?>
                    </div>

                <?php else: ?>

                    <div class="gallery-grid">
                        <?php foreach ($medias as $i => $media):
                            $url_full = (strpos($media['url_fichier'] ?? '', 'http') === 0)
                                ? $media['url_fichier']
                                : rtrim(SITE_URL, '/') . '/' . ltrim($media['url_fichier'] ?? '', '/');

                            $url_thumb = !empty($media['url_thumbnail'])
                                ? ((strpos($media['url_thumbnail'], 'http') === 0)
                                    ? $media['url_thumbnail']
                                    : rtrim(SITE_URL, '/') . '/' . ltrim($media['url_thumbnail'], '/'))
                                : $url_full;

                            $titre = htmlspecialchars($media['titre'] ?? 'Photo GSCC');
                            $date  = isset($media['date_upload'])
                                        ? date('d/m/Y', strtotime($media['date_upload'])) : '';
                        ?>
                        <a href="<?= $url_full ?>"
                           class="gallery-item"
                           data-lightbox="gallery"
                           data-title="<?= $titre ?>"
                           data-aos="fade-up"
                           data-aos-delay="<?= ($i % 3) * 70 ?>">
                            <div class="gallery-image">
                                <img src="<?= $url_thumb ?>" alt="<?= $titre ?>" loading="lazy">
                            </div>
                            <div class="gallery-overlay">
                                <div class="gallery-title"><?= $titre ?></div>
                                <?php if ($date): ?>
                                <div class="gallery-meta">
                                    <i class="far fa-calendar-alt"></i> <?= $date ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>

            <?php endif; ?>

        </div>
    </section>

    <?php include 'templates/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
    <script>
        AOS.init({ duration: 600, once: true, offset: 50 });
        lightbox.option({
            resizeDuration:    200,
            wrapAround:        true,
            albumLabel:        'Image %1 sur %2',
            fadeDuration:      300,
            imageFadeDuration: 300
        });
    </script>
</body>
</html>