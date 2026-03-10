<?php
// temoignages.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$page_title       = 'Témoignages';
$page_description = 'Découvrez les témoignages de patients, familles et bénévoles du GSCC.';

try {
    $stmt        = $pdo->query("SELECT * FROM temoignages WHERE statut = 'approuve' ORDER BY date_creation DESC");
    $temoignages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError("Erreur temoignages.php: " . $e->getMessage());
    $temoignages = [];
}

if (empty($temoignages)) {
    $temoignages = [
        ['id' => 1, 'nom' => 'Marie C.', 'fonction' => 'Patiente accompagnée', 'photo' => 'https://randomuser.me/api/portraits/women/1.jpg', 'temoignage' => 'Grâce au soutien du GSCC, j\'ai pu suivre mon traitement dans de bonnes conditions. Leur accompagnement a été essentiel dans mon combat contre le cancer. Les bénévoles sont à l\'écoute et vraiment présents à chaque étape.', 'note' => 5, 'date_creation' => '2024-09-15'],
        ['id' => 2, 'nom' => 'Jean-Paul D.', 'fonction' => 'Bénévole', 'photo' => 'https://randomuser.me/api/portraits/men/2.jpg', 'temoignage' => 'Je suis bénévole au GSCC depuis 2 ans. Voir l\'impact positif de nos actions sur les patients et leurs familles est une source de motivation incroyable. Chaque sourire, chaque remerciement vaut tout l\'or du monde.', 'note' => 5, 'date_creation' => '2024-10-20'],
        ['id' => 3, 'nom' => 'Sophie L.', 'fonction' => 'Proche de patient', 'photo' => 'https://randomuser.me/api/portraits/women/3.jpg', 'temoignage' => 'Le GSCC m\'a apporté un soutien psychologique précieux quand mon mari a été diagnostiqué. Leur équipe est à l\'écoute et vraiment dévouée. Les groupes de parole m\'ont énormément aidée à traverser cette épreuve.', 'note' => 5, 'date_creation' => '2024-08-05'],
        ['id' => 4, 'nom' => 'Dr. Pierre Antoine', 'fonction' => 'Médecin partenaire', 'photo' => 'https://randomuser.me/api/portraits/men/4.jpg', 'temoignage' => 'En tant que médecin, je travaille régulièrement avec le GSCC. Leur professionnalisme et leur dévouement pour les patients sont remarquables. Ils comblent un vide dans notre système de santé.', 'note' => 5, 'date_creation' => '2024-09-30'],
        ['id' => 5, 'nom' => 'Marchena D.', 'fonction' => 'Patiente', 'photo' => 'https://randomuser.me/api/portraits/women/5.jpg', 'temoignage' => 'Le GSCC m\'a aidée à comprendre ma maladie et à ne pas perdre espoir. Leurs conseils et leur présence m\'ont donné la force de continuer le combat. Je recommande cette association à tous ceux qui traversent cette épreuve.', 'note' => 5, 'date_creation' => '2024-10-10'],
        ['id' => 6, 'nom' => 'Robert F.', 'fonction' => 'Donateur', 'photo' => 'https://randomuser.me/api/portraits/men/6.jpg', 'temoignage' => 'Je soutiens le GSCC depuis plusieurs années. Voir la transparence avec laquelle ils utilisent les dons et l\'impact concret de leurs actions me conforte dans mon engagement.', 'note' => 5, 'date_creation' => '2024-07-22'],
    ];
}

// Témoignage à la une — le premier (le plus récent)
$featured = $temoignages[0];
// Les autres
$rest = array_slice($temoignages, 1);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — <?= defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'GSCC' ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        /* =====================================================
   GSCC · Témoignages — Design sobre & professionnel
   2 couleurs principales : Bleu nuit + Rose grenat
   Fond blanc/ivoire, pas de dégradés criards
===================================================== */
        :root {
            --navy: #003399;
            --navy-2: #1a56cc;
            --rose: #1a56cc;
            --cream: #FAF8F5;
            --white: #FFFFFF;
            --text: #141425;
            --text-2: #3A3A5C;
            --text-3: #5C5C7A;
            --border: #E8E4F0;
            --gold: #C4933F;
            --r: 16px;
            --sh: 0 2px 16px rgba(13, 27, 53, .07);
            --sh2: 0 8px 32px rgba(13, 27, 53, .11);
            --ease: cubic-bezier(.4, 0, .2, 1);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--cream);
            color: var(--text);
            line-height: 1.6;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 28px;
        }

        /* ── EN-TÊTE PAGE ─────────────────────────────────── */
        .page-hero {
            background: linear-gradient(135deg, #003399 0%, #1a56cc 60%, #1a7abf 100%);
            padding: 90px 0 72px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* ligne rose en bas */
        .page-hero::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--rose);
        }

        .page-hero .container {
            position: relative;
            z-index: 1;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(255, 255, 255, .18);
            color: rgba(255, 255, 255, .92);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 6px 18px;
            border-radius: 99px;
            margin-bottom: 24px;
        }

        .page-hero h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(2.6rem, 5vw, 4.2rem);
            font-weight: 700;
            color: var(--white);
            line-height: 1.1;
            margin-bottom: 16px;
        }

        .page-hero p {
            font-size: 1rem;
            font-weight: 400;
            color: rgba(255, 255, 255, .88);
            max-width: 480px;
            margin: 0 auto;
        }

        /* ── STATS ────────────────────────────────────────── */
        .stats-row {
            background: var(--white);
            border-bottom: 1px solid var(--border);
        }

        .stats-row .container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
        }

        .stat-cell {
            padding: 34px 20px;
            text-align: center;
            border-right: 1px solid var(--border);
        }

        .stat-cell:last-child {
            border-right: none;
        }

        .stat-n {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.4rem;
            font-weight: 700;
            color: var(--navy);
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-l {
            font-size: 12.5px;
            color: var(--text-3);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 500;
        }

        /* ── SECTION PRINCIPALE ───────────────────────────── */
        .main-section {
            padding: 80px 0 100px;
        }

        /* ── TÉMOIGNAGE À LA UNE ──────────────────────────── */
        .featured {
            display: grid;
            grid-template-columns: 1fr 3px 1fr;
            gap: 48px;
            align-items: center;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 52px 48px;
            margin-bottom: 72px;
            box-shadow: var(--sh);
            position: relative;
            overflow: hidden;
        }

        /* accent rose sur le côté gauche */
        .featured::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--rose);
        }

        .featured-divider {
            background: var(--border);
        }

        .featured-quote {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(1.25rem, 2vw, 1.55rem);
            font-style: italic;
            font-weight: 600;
            color: var(--text);
            line-height: 1.65;
            position: relative;
            padding-top: 32px;
        }

        /* grand guillemet décoratif */
        .featured-quote::before {
            content: '\201C';
            position: absolute;
            top: -8px;
            left: 0;
            font-size: 5rem;
            line-height: 1;
            color: var(--rose);
            opacity: .25;
            font-style: normal;
        }

        .featured-author {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 28px;
        }

        .featured-avatar {
            width: 62px;
            height: 62px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid var(--border);
        }

        .featured-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .featured-name {
            font-weight: 600;
            font-size: 15px;
            color: var(--text);
            margin-bottom: 3px;
        }

        .featured-role {
            font-size: 13px;
            color: var(--text-3);
        }

        .featured-aside {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .featured-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--rose);
            margin-bottom: 6px;
        }

        .featured-stat-item {
            padding: 18px 20px;
            background: var(--cream);
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .featured-stat-item strong {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--navy);
            display: block;
            line-height: 1;
            margin-bottom: 3px;
        }

        .featured-stat-item span {
            font-size: 12.5px;
            color: var(--text-3);
        }

        /* ── GRILLE TÉMOIGNAGES ───────────────────────────── */
        .section-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 8px;
        }

        .section-rule {
            width: 44px;
            height: 3px;
            background: var(--rose);
            border-radius: 99px;
            margin-bottom: 40px;
        }

        .temoignages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 22px;
            margin-bottom: 72px;
        }

        /* ── CARTE ────────────────────────────────────────── */
        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 28px 26px 24px;
            box-shadow: var(--sh);
            transition: transform .3s var(--ease), box-shadow .3s var(--ease), border-color .3s var(--ease);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--rose);
            border-radius: var(--r) var(--r) 0 0;
            opacity: 0;
            transition: opacity .3s var(--ease);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--sh2);
            border-color: transparent;
        }

        .card:hover::after {
            opacity: 1;
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
        }

        .card-avatar {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid var(--border);
        }

        .card-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-name {
            font-size: 15px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 2px;
        }

        .card-role {
            font-size: 12.5px;
            color: var(--text-3);
        }

        .card-stars {
            display: flex;
            gap: 3px;
            margin-bottom: 14px;
        }

        .card-stars i {
            font-size: 13px;
            color: var(--gold);
        }

        .card-stars i.empty {
            color: var(--border);
        }

        .card-text {
            font-size: 14px;
            color: var(--text-2);
            line-height: 1.78;
            flex: 1;
            margin-bottom: 18px;
            /* guillemet typographique avant le texte */
            position: relative;
            padding-left: 16px;
        }

        .card-text::before {
            content: '';
            position: absolute;
            left: 0;
            top: 4px;
            bottom: 4px;
            width: 3px;
            background: var(--border);
            border-radius: 99px;
        }

        .card-date {
            font-size: 11.5px;
            color: var(--text-3);
            text-align: right;
            padding-top: 14px;
            border-top: 1px solid var(--border);
        }

        /* ── CTA TÉMOIGNER ────────────────────────────────── */
        .cta-box {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 52px 40px;
            text-align: center;
            box-shadow: var(--sh);
            position: relative;
            overflow: hidden;
        }

        .cta-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--rose);
        }

        .cta-box h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 12px;
        }

        .cta-box p {
            font-size: 15px;
            color: var(--text-2);
            max-width: 520px;
            margin: 0 auto 32px;
            line-height: 1.75;
        }

        .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            background: var(--navy);
            color: var(--white);
            padding: 14px 36px;
            border-radius: 99px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: all .3s var(--ease);
            box-shadow: 0 6px 20px rgba(13, 27, 53, .20);
        }

        .btn-cta:hover {
            background: var(--rose);
            color: var(--white);
            box-shadow: 0 10px 28px rgba(172, 47, 88, .30);
            transform: translateY(-2px);
        }

        /* ── RESPONSIVE ───────────────────────────────────── */
        @media(max-width:860px) {
            .featured {
                grid-template-columns: 1fr;
                gap: 32px;
                padding: 36px 28px;
            }

            .featured-divider {
                display: none;
            }

            .featured-aside {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .featured-stat-item {
                flex: 1;
                min-width: 140px;
            }

            .stats-row .container {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media(max-width:580px) {
            .page-hero {
                padding: 64px 0 56px;
            }

            .temoignages-grid {
                grid-template-columns: 1fr;
            }

            .stats-row .container {
                grid-template-columns: 1fr;
            }

            .stat-cell {
                border-right: none;
                border-bottom: 1px solid var(--border);
            }

            .stat-cell:last-child {
                border-bottom: none;
            }

            .cta-box {
                padding: 36px 22px;
            }

            .featured {
                padding: 28px 20px;
            }
        }
    </style>
</head>

<body>
    <?php require_once 'templates/header.php'; ?>

    <!-- ══ EN-TÊTE ══ -->
    <header class="page-hero">
        <div class="container">
            <div class="hero-eyebrow" data-aos="fade-down">
                <i class="fas fa-quote-left"></i>
                Paroles de notre communauté
            </div>
            <h1 data-aos="fade-up" data-aos-delay="80">Témoignages</h1>
            <p data-aos="fade-up" data-aos-delay="150">
                Patients, familles, bénévoles — ils partagent leur expérience avec le GSCC.
            </p>
        </div>
    </header>

    <!-- ══ STATS ══ -->
    <div class="stats-row">
        <div class="container">
            <div class="stat-cell" data-aos="fade-up">
                <div class="stat-n">150+</div>
                <div class="stat-l">Patients accompagnés</div>
            </div>
            <div class="stat-cell" data-aos="fade-up" data-aos-delay="80">
                <div class="stat-n">25</div>
                <div class="stat-l">Bénévoles actifs</div>
            </div>
            <div class="stat-cell" data-aos="fade-up" data-aos-delay="160">
                <div class="stat-n">100%</div>
                <div class="stat-l">Satisfaction</div>
            </div>
        </div>
    </div>

    <!-- ══ CONTENU PRINCIPAL ══ -->
    <section class="main-section">
        <div class="container">

            <!-- Témoignage à la une -->
            <?php if (!empty($featured)): ?>
                <div class="featured" data-aos="fade-up">
                    <div>
                        <p class="featured-label">Témoignage du moment</p>
                        <blockquote class="featured-quote">
                            <?= htmlspecialchars($featured['temoignage']) ?>
                        </blockquote>
                        <div class="featured-author">
                            <div class="featured-avatar">
                                <img src="<?= htmlspecialchars($featured['photo'] ?? '') ?>"
                                    alt="<?= htmlspecialchars($featured['nom']) ?>"
                                    loading="lazy"
                                    onerror="this.style.display='none'">
                            </div>
                            <div>
                                <div class="featured-name"><?= htmlspecialchars($featured['nom']) ?></div>
                                <div class="featured-role"><?= htmlspecialchars($featured['fonction'] ?? '') ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="featured-divider"></div>

                    <div class="featured-aside">
                        <p class="featured-label">En chiffres</p>
                        <div class="featured-stat-item">
                            <strong>150+</strong>
                            <span>Personnes accompagnées depuis 2014</span>
                        </div>
                        <div class="featured-stat-item">
                            <strong><?= count($temoignages) ?></strong>
                            <span>Témoignages publiés sur cette page</span>
                        </div>
                        <div class="featured-stat-item">
                            <strong>5 / 5</strong>
                            <span>Note de satisfaction moyenne</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tous les témoignages -->
            <h2 class="section-title" data-aos="fade-up">Toutes les voix</h2>
            <div class="section-rule" data-aos="fade-up" data-aos-delay="60"></div>

            <div class="temoignages-grid">
                <?php foreach ($temoignages as $idx => $t): ?>
                    <div class="card" data-aos="fade-up" data-aos-delay="<?= ($idx % 3) * 70 ?>">

                        <div class="card-header">
                            <div class="card-avatar">
                                <img src="<?= htmlspecialchars($t['photo'] ?? '') ?>"
                                    alt="<?= htmlspecialchars($t['nom']) ?>"
                                    loading="lazy"
                                    onerror="this.style.display='none'">
                            </div>
                            <div>
                                <div class="card-name"><?= htmlspecialchars($t['nom']) ?></div>
                                <div class="card-role"><?= htmlspecialchars($t['fonction'] ?? '') ?></div>
                            </div>
                        </div>

                        <?php if (!empty($t['note'])): ?>
                            <div class="card-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i > $t['note'] ? 'empty' : '' ?>"></i>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>

                        <p class="card-text"><?= htmlspecialchars($t['temoignage']) ?></p>

                        <div class="card-date">
                            <?= htmlspecialchars(function_exists('formatDateFr')
                                ? formatDateFr($t['date_creation'] ?? date('Y-m-d'))
                                : date('d/m/Y', strtotime($t['date_creation'] ?? 'now'))) ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

            <!-- CTA -->
            <div class="cta-box" data-aos="fade-up">
                <h3>Partagez votre expérience</h3>
                <p>
                    Vous avez été accompagné par le GSCC, vous êtes bénévole ou donateur&nbsp;?
                    Votre témoignage peut aider et inspirer d'autres personnes.
                </p>
                <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                    <a href="ajouter-temoignage.php" class="btn-cta">
                        <i class="fas fa-pen"></i> Ajouter mon témoignage
                    </a>
                <?php else: ?>
                    <a href="connexion.php" class="btn-cta">
                        <i class="fas fa-arrow-right-to-bracket"></i> Connectez-vous pour témoigner
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <?php require_once 'templates/footer.php'; ?>

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