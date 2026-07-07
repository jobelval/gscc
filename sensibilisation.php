<?php
// sensibilisation.php
require_once 'includes/config.php';

$page_title = 'Sensibilisation';
$page_description = 'Découvrez nos actions de sensibilisation pour informer et prévenir contre le cancer.';

// Activités de sensibilisation depuis la base de données (gérées via admin CMS)
try {
    $stmt = $pdo->query("SELECT * FROM sensibilisation_activites WHERE est_actif=1 ORDER BY ordre ASC, id ASC");
    $activites = $stmt->fetchAll();
} catch (PDOException $e) {
    $activites = [];
}

// Campagnes liées à la sensibilisation
try {
    $stmt = $pdo->prepare("SELECT * FROM campagnes_projets
                           WHERE type = 'campagne' AND statut IN ('en_cours', 'termine')
                           AND (titre LIKE '%sensibilisation%' OR description LIKE '%sensibilisation%')
                           ORDER BY date_debut DESC");
    $stmt->execute();
    $campagnes_sensibilisation = $stmt->fetchAll();
} catch (PDOException $e) {
    $campagnes_sensibilisation = [];
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> - <?= SITE_NAME ?></title>
    <meta name="description" content="<?= e($page_description) ?>">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --blue:      #003399;
            --blue-dark: #001f6b;
            --blue-mid:  #1a56cc;
            --text:      #0D1117;
            --text-2:    #1F2937;
            --muted:     #6B7280;
            --border:    #E5E7EB;
            --bg:        #F8FAFF;
            --white:     #FFFFFF;
        }

        /* ── HERO ── */
        .page-hero {
            background: linear-gradient(135deg, #7B1535 0%, #C8375F 55%, #D94F7A 100%);
            padding: 90px 0 130px;
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        .page-hero::before,
        .page-hero::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
        }
        .page-hero::before { width:460px;height:460px;top:-120px;right:-100px; }
        .page-hero::after  { width:300px;height:300px;bottom:0;left:-80px; }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.25);
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .6px;
            text-transform: uppercase;
            padding: 6px 18px;
            border-radius: 100px;
            margin-bottom: 22px;
        }
        .page-hero h1 {
            font-size: 3rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 16px;
            text-shadow: 0 2px 6px rgba(0,0,0,.25);
            line-height: 1.15;
        }
        .page-hero p {
            font-size: 1.15rem;
            color: #FFD0DF;
            max-width: 560px;
            margin: 0 auto;
            line-height: 1.7;
        }
        .hero-wave {
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            line-height: 0;
        }

        /* ── INTRO ── */
        .intro-block {
            background: var(--white);
            padding: 60px 0 50px;
        }
        .intro-inner {
            max-width: 760px;
            margin: 0 auto;
            text-align: center;
        }
        .intro-inner p {
            font-size: 1.05rem;
            color: var(--text-2);
            line-height: 1.9;
        }
        .intro-divider {
            width: 60px;
            height: 4px;
            background: var(--blue);
            border-radius: 4px;
            margin: 24px auto 0;
        }

        /* ── SECTION LABEL ── */
        .section-label {
            text-align: center;
            margin-bottom: 48px;
        }
        .section-label span {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--blue);
            margin-bottom: 8px;
        }
        .section-label h2 {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text);
        }

        /* ── THEMES SECTION ── */
        .themes-section {
            background: var(--bg);
            padding: 70px 0;
        }
        .themes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
        }

        .theme-card {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid var(--border);
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .theme-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 40px rgba(0,0,0,.1);
        }
        .theme-card-stripe {
            height: 5px;
        }
        .theme-card-body {
            padding: 28px 26px 30px;
            text-align: center;
        }
        .theme-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: #fff;
            margin: 0 auto 18px;
        }
        .theme-card h3 {
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 10px;
            line-height: 1.35;
        }
        .theme-card p {
            font-size: 14px;
            color: var(--muted);
            line-height: 1.75;
        }

        /* ── STATS ── */
        .stats-section {
            padding: 70px 0;
            background: var(--white);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }
        .stat-card {
            background: var(--bg);
            border-radius: 20px;
            padding: 36px 24px;
            text-align: center;
            border: 1px solid var(--border);
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 32px rgba(0,51,153,.1);
        }
        .stat-number {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--text);
            line-height: 1;
            margin-bottom: 8px;
        }
        .stat-label {
            font-size: 14px;
            font-weight: 500;
            color: var(--muted);
        }

        /* ── RESOURCES ── */
        .resources-section {
            background: var(--bg);
            padding: 70px 0;
        }
        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }
        .resource-card {
            background: var(--white);
            border-radius: 16px;
            padding: 24px 22px;
            display: flex;
            align-items: center;
            gap: 18px;
            text-decoration: none;
            color: var(--text);
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .resource-card:hover {
            transform: translateY(-4px);
            border-color: var(--blue);
            box-shadow: 0 8px 24px rgba(0,51,153,.12);
        }
        .resource-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #fff;
            flex-shrink: 0;
        }
        .resource-content h4 {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 4px;
            line-height: 1.35;
        }
        .resource-content p {
            font-size: 13px;
            color: var(--muted);
            font-weight: 500;
        }
        .resource-arrow {
            margin-left: auto;
            color: var(--border);
            font-size: 16px;
            transition: color .2s, transform .2s;
            flex-shrink: 0;
        }
        .resource-card:hover .resource-arrow {
            color: var(--blue);
            transform: translateX(4px);
        }

        @media (max-width: 900px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 600px) {
            .page-hero h1 { font-size: 2.1rem; }
            .stats-grid   { grid-template-columns: 1fr 1fr; }
            .themes-grid  { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>
    <?php include 'templates/header.php'; ?>

    <!-- Hero -->
    <section class="page-hero">
        <div class="container" style="position:relative;z-index:2;">
            <div class="hero-badge" data-aos="fade-down">
                <i class="fas fa-bullhorn"></i> Prévention &amp; Information
            </div>
            <h1 data-aos="fade-up" data-aos-delay="50">Sensibilisation</h1>
            <p data-aos="fade-up" data-aos-delay="120">
                Informer pour mieux prévenir,<br>sensibiliser pour sauver des vies.
            </p>
        </div>
        <div class="hero-wave">
            <svg viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path d="M0,30 C360,60 1080,0 1440,30 L1440,60 L0,60 Z" fill="#FFFFFF"/>
            </svg>
        </div>
    </section>

    <!-- Intro -->
    <div class="intro-block">
        <div class="container">
            <div class="intro-inner" data-aos="fade-up">
                <p>
                    La sensibilisation est au cœur de notre mission. Nous croyons que l'information
                    et la prévention sont les armes les plus efficaces dans la lutte contre le cancer.
                    À travers nos campagnes, nous touchons des milliers de personnes chaque année.
                </p>
                <div class="intro-divider"></div>
            </div>
        </div>
    </div>

    <!-- Thèmes (chargés depuis la base de données) -->
    <section class="themes-section">
        <div class="container">
            <div class="section-label" data-aos="fade-up">
                <span>Nos campagnes</span>
                <h2>Thèmes de sensibilisation</h2>
            </div>

            <div class="themes-grid">
                <?php foreach ($activites as $i => $act): ?>
                <div class="theme-card" data-aos="fade-up" data-aos-delay="<?= $i * 60 ?>">
                    <div class="theme-card-stripe" style="background:<?= htmlspecialchars($act['couleur']) ?>;"></div>
                    <div class="theme-card-body">
                        <div class="theme-icon" style="background:<?= htmlspecialchars($act['couleur']) ?>;">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h3><?= htmlspecialchars($act['titre']) ?></h3>
                        <p><?= htmlspecialchars($act['description']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($activites)): ?>
                <p style="color:var(--muted);text-align:center;padding:40px 0;">
                    Aucune activité de sensibilisation pour le moment.
                </p>
                <?php endif; ?>
            </div>
        </div>
    </section>


    <!-- Ressources pédagogiques -->
    <section class="resources-section">
        <div class="container">
            <div class="section-label" data-aos="fade-up">
                <span>Documentation</span>
                <h2>Ressources pédagogiques</h2>
            </div>
            <div class="resources-grid">

                <a href="https://drive.google.com/file/d/1eWWp_GsP7wba9TKV4bx_saMtOAZkyrZw/view?usp=sharing" target="_blank" class="resource-card" data-aos="fade-up">
                    <div class="resource-icon" style="background:#C62828;">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div class="resource-content">
                        <h4>GSCC 25 ans de Lutte</h4>
                        <p>PDF · 2.5 MB</p>
                    </div>
                    <i class="fas fa-arrow-right resource-arrow"></i>
                </a>

                <a href="#" class="resource-card" data-aos="fade-up" data-aos-delay="60">
                    <div class="resource-icon" style="background:#C62828;">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div class="resource-content">
                        <h4>Brochure "Cancer de la prostate"</h4>
                        <p>PDF · 1.8 MB</p>
                    </div>
                    <i class="fas fa-arrow-right resource-arrow"></i>
                </a>

                <a href="https://youtu.be/peX2zTu3z8w?si=nJzcEB1_KMiTA6bh" target="_blank" class="resource-card" data-aos="fade-up" data-aos-delay="120">
                    <div class="resource-icon" style="background:#CC0000;">
                        <i class="fab fa-youtube"></i>
                    </div>
                    <div class="resource-content">
                        <h4>Témoignage : François L. Péan, Membre Fondateur</h4>
                        <p>YouTube · 8 min</p>
                    </div>
                    <i class="fas fa-arrow-right resource-arrow"></i>
                </a>

                <a href="https://drive.google.com/file/d/1ilvcWUTnH7_CfECGTSDdyEBk1wCRUA7t/view?usp=sharing" target="_blank" class="resource-card" data-aos="fade-up" data-aos-delay="180">
                    <div class="resource-icon" style="background:#C62828;">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div class="resource-content">
                        <h4>Devenez Partenaire Contre le cancer</h4>
                        <p>PDF · 2.5 MB</p>
                    </div>
                    <i class="fas fa-arrow-right resource-arrow"></i>
                </a>

            </div>
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ once: true, duration: 600 });</script>
</body>

</html>
