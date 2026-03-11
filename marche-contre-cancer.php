<?php
// marche-contre-cancer.php
require_once 'includes/config.php';

$page_title = 'Marche Contre le Cancer';
$page_description = 'Participez à la grande marche solidaire organisée chaque année par le GSCC.';

$annee = date('Y');
$prochaine_marche = mktime(9, 0, 0, 10, 20, $annee);
$jours_restants = ceil(($prochaine_marche - time()) / (60 * 60 * 24));
if ($jours_restants < 0) {
    $prochaine_marche = mktime(9, 0, 0, 10, 20, $annee + 1);
    $jours_restants = ceil(($prochaine_marche - time()) / (60 * 60 * 24));
}
$timestamp_marche = $prochaine_marche * 1000;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> - <?= SITE_NAME ?></title>
    <meta name="description" content="<?= e($page_description) ?>">

    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
    /* ══════════════════════════════════════════════════════
       GSCC · Marche Contre le Cancer — Design Premium
    ══════════════════════════════════════════════════════ */
    :root {
        --blue:      #003399;
        --blue-mid:  #1a56cc;
        --blue-lite: #1a7abf;
        --blue-bg:   #EEF3FF;
        --blue-card: #F5F7FF;
        --white:     #FFFFFF;
        --bg:        #F8F9FC;
        --text:      #0D1117;
        --text-2:    #374151;
        --text-3:    #6B7280;
        --border:    #E5E9F2;
        --shadow:    0 2px 16px rgba(0,51,153,.08), 0 1px 4px rgba(0,51,153,.04);
        --shadow-lg: 0 12px 40px rgba(0,51,153,.14);
        --ease:      cubic-bezier(.4,0,.2,1);
        --r:         14px;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); line-height: 1.6; }
    h1,h2,h3,h4 { font-family: 'Sora', sans-serif; }
    .container { max-width: 1140px; margin: 0 auto; padding: 0 28px; }

    /* ── HERO ──────────────────────────────────────────── */
    .hero {
        background: linear-gradient(135deg, #003399 0%, #1a56cc 60%, #1a7abf 100%);
        padding: 110px 0 100px;
        position: relative;
        overflow: hidden;
    }
    .hero::after {
        content: '';
        position: absolute;
        bottom: -1px; left: 0; right: 0;
        height: 64px;
        background: var(--bg);
        clip-path: ellipse(56% 100% at 50% 100%);
    }
    .hero-orb {
        position: absolute; border-radius: 50%;
        filter: blur(72px); opacity: .16; pointer-events: none;
    }
    .hero-orb-1 { width: 520px; height: 520px; background: #fff; top: -200px; right: -120px; }
    .hero-orb-2 { width: 320px; height: 320px; background: #93c5fd; bottom: -100px; left: -80px; }

    .hero-inner { position: relative; z-index: 1; text-align: center; }

    .hero-tag {
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(255,255,255,.14);
        border: 1px solid rgba(255,255,255,.25);
        color: rgba(255,255,255,.9);
        font-size: 11.5px; font-weight: 600;
        letter-spacing: 1.8px; text-transform: uppercase;
        padding: 6px 16px; border-radius: 99px;
        margin-bottom: 26px;
        backdrop-filter: blur(8px);
    }
    .hero-tag i { font-size: 10px; color: #93c5fd; }

    .hero h1 {
        font-size: clamp(2.6rem, 5vw, 4rem);
        font-weight: 800; color: #fff;
        letter-spacing: -.5px; line-height: 1.1;
        margin-bottom: 18px;
    }
    .hero-date {
        display: inline-flex; align-items: center; gap: 10px;
        font-size: 1rem; color: rgba(255,255,255,.75);
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.18);
        padding: 8px 20px; border-radius: 99px;
        margin-bottom: 16px;
        font-weight: 500;
    }
    .hero-date i { font-size: 13px; color: #93c5fd; }
    .hero-sub {
        font-size: 1.05rem; color: rgba(255,255,255,.78);
        max-width: 520px; margin: 0 auto 44px; line-height: 1.75;
    }

    /* Compte à rebours */
    .countdown {
        display: inline-flex; gap: 10px;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 18px; padding: 22px 32px;
        backdrop-filter: blur(14px);
        margin-bottom: 44px;
    }
    .cd-unit { text-align: center; min-width: 60px; }
    .cd-num {
        font-family: 'Sora', sans-serif;
        font-size: 2.2rem; font-weight: 800;
        color: #fff; line-height: 1; display: block;
    }
    .cd-label {
        font-size: 9.5px; color: rgba(255,255,255,.55);
        letter-spacing: 1.5px; text-transform: uppercase;
        margin-top: 5px; display: block;
    }
    .cd-sep {
        font-size: 1.8rem; color: rgba(255,255,255,.28);
        font-weight: 300; align-self: flex-start; padding-top: 4px;
    }

    .hero-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    .btn-hero-primary {
        display: inline-flex; align-items: center; gap: 9px;
        background: #fff; color: var(--blue);
        padding: 14px 32px; border-radius: 99px;
        text-decoration: none; font-weight: 700;
        font-size: 15px; font-family: 'Sora', sans-serif;
        box-shadow: 0 4px 20px rgba(0,0,0,.18);
        transition: all .3s var(--ease);
    }
    .btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,.26); }
    .btn-hero-secondary {
        display: inline-flex; align-items: center; gap: 9px;
        background: transparent; color: rgba(255,255,255,.88);
        border: 1.5px solid rgba(255,255,255,.32);
        padding: 14px 28px; border-radius: 99px;
        text-decoration: none; font-weight: 500; font-size: 15px;
        transition: all .3s var(--ease);
    }
    .btn-hero-secondary:hover { border-color: #fff; color: #fff; background: rgba(255,255,255,.08); }

    /* ── STATS BAND ────────────────────────────────────── */
    .stats-strip {
        background: var(--white);
        border-bottom: 1px solid var(--border);
        box-shadow: 0 2px 12px rgba(0,51,153,.05);
    }
    .stats-strip-inner {
        display: grid; grid-template-columns: repeat(4, 1fr);
        max-width: 1140px; margin: 0 auto; padding: 0 28px;
    }
    .st-cell {
        padding: 28px 16px; text-align: center;
        border-right: 1px solid var(--border);
        transition: background .2s var(--ease);
    }
    .st-cell:last-child { border-right: none; }
    .st-cell:hover { background: var(--blue-card); }
    .st-ico { font-size: 17px; color: var(--blue-mid); margin-bottom: 8px; }
    .st-n {
        font-family: 'Sora', sans-serif;
        font-size: 1.9rem; font-weight: 800;
        color: var(--blue); line-height: 1; margin-bottom: 4px;
    }
    .st-l { font-size: 11px; color: var(--text-3); text-transform: uppercase; letter-spacing: 1.2px; font-weight: 600; }

    /* ── LAYOUT HELPERS ────────────────────────────────── */
    section { padding: 80px 0; }
    section.alt { background: var(--white); }

    .section-label {
        display: inline-flex; align-items: center; gap: 7px;
        font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
        color: var(--blue-mid); background: var(--blue-bg);
        border: 1px solid #C7D7FF;
        padding: 5px 14px; border-radius: 99px; margin-bottom: 14px;
    }
    .section-title {
        font-size: clamp(1.7rem, 3vw, 2.3rem); font-weight: 800;
        color: var(--blue); letter-spacing: -.3px; margin-bottom: 12px; line-height: 1.2;
    }
    .section-sub {
        font-size: 15.5px; color: var(--text-2);
        max-width: 560px; line-height: 1.8;
    }
    .section-head { margin-bottom: 52px; }
    .section-head.center { text-align: center; }
    .section-head.center .section-sub { margin: 0 auto; }

    /* ── INFOS — 3 cartes ──────────────────────────────── */
    .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .info-card {
        background: var(--white); border: 1px solid var(--border);
        border-radius: var(--r); padding: 36px 28px;
        position: relative; overflow: hidden;
        box-shadow: var(--shadow);
        transition: transform .3s var(--ease), box-shadow .3s var(--ease);
    }
    .info-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
        background: linear-gradient(90deg, var(--blue), var(--blue-lite));
        transform: scaleX(0); transform-origin: left;
        transition: transform .35s var(--ease);
    }
    .info-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-lg); }
    .info-card:hover::before { transform: scaleX(1); }
    .info-card-icon {
        width: 54px; height: 54px; background: var(--blue-bg);
        border-radius: 13px;
        display: flex; align-items: center; justify-content: center;
        font-size: 21px; color: var(--blue-mid);
        margin-bottom: 20px;
        transition: background .3s var(--ease), color .3s var(--ease);
    }
    .info-card:hover .info-card-icon {
        background: linear-gradient(135deg, var(--blue), var(--blue-lite)); color: #fff;
    }
    .info-card h3 { font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 10px; }
    .info-card p { font-size: 14px; color: var(--text-2); line-height: 1.75; }
    .info-card strong { color: var(--blue); }

    /* ── PARCOURS ──────────────────────────────────────── */
    .parcours-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 56px; align-items: center; }
    .parcours-map-wrap {
        border-radius: 18px; overflow: hidden;
        box-shadow: var(--shadow-lg); border: 1px solid var(--border);
    }
    .parcours-map-wrap iframe { width: 100%; height: 420px; border: none; display: block; }
    .parcours-info > p { font-size: 15px; color: var(--text-2); line-height: 1.8; margin-bottom: 28px; }

    .steps-list { list-style: none; display: flex; flex-direction: column; gap: 14px; margin-bottom: 28px; }
    .step-item {
        display: flex; align-items: flex-start; gap: 14px;
        background: var(--white); border: 1px solid var(--border);
        border-radius: 12px; padding: 15px 17px;
        box-shadow: var(--shadow);
        transition: border-color .2s var(--ease), transform .2s var(--ease);
    }
    .step-item:hover { border-color: var(--blue-mid); transform: translateX(4px); }
    .step-dot {
        width: 34px; height: 34px; flex-shrink: 0; border-radius: 50%;
        background: linear-gradient(135deg, var(--blue), var(--blue-lite));
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 12px; font-weight: 700; font-family: 'Sora', sans-serif;
    }
    .step-item span { font-size: 14px; color: var(--text-2); line-height: 1.6; padding-top: 6px; }
    .step-item strong { color: var(--text); }

    .access-note {
        display: inline-flex; align-items: center; gap: 10px;
        font-size: 13px; color: var(--blue);
        background: var(--blue-bg); border: 1px solid #C7D7FF;
        padding: 10px 16px; border-radius: 10px; font-weight: 500;
    }

    /* ── PROGRAMME ─────────────────────────────────────── */
    .programme-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
    .prog-card {
        background: var(--white); border: 1px solid var(--border);
        border-radius: var(--r); padding: 28px 20px;
        box-shadow: var(--shadow); text-align: center;
        position: relative; overflow: hidden;
        transition: transform .3s var(--ease), box-shadow .3s var(--ease);
    }
    .prog-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
    .prog-time {
        font-family: 'Sora', sans-serif; font-size: 1.5rem; font-weight: 800;
        color: var(--blue); margin-bottom: 10px;
    }
    .prog-icon { font-size: 24px; color: var(--blue-mid); margin-bottom: 12px; }
    .prog-title { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
    .prog-desc { font-size: 12.5px; color: var(--text-3); line-height: 1.65; }
    .prog-accent {
        position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, var(--blue), var(--blue-lite));
    }

    /* ── TÉMOIGNAGES ───────────────────────────────────── */
    .temo-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .temo-card {
        background: var(--white); border: 1px solid var(--border);
        border-radius: var(--r); padding: 32px 26px;
        box-shadow: var(--shadow); position: relative;
        transition: transform .3s var(--ease), box-shadow .3s var(--ease);
    }
    .temo-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
    .temo-quote {
        font-size: 46px; font-family: Georgia, serif;
        color: var(--blue-mid); opacity: .18; line-height: .8; margin-bottom: 14px;
    }
    .temo-text { font-size: 14px; color: var(--text-2); line-height: 1.8; font-style: italic; margin-bottom: 22px; }
    .temo-author {
        display: flex; align-items: center; gap: 12px;
        padding-top: 16px; border-top: 1px solid var(--border);
    }
    .temo-avatar {
        width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg, var(--blue), var(--blue-lite));
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 13px; font-weight: 700; font-family: 'Sora', sans-serif;
    }
    .temo-name { font-size: 14px; font-weight: 700; color: var(--text); }
    .temo-role { font-size: 12px; color: var(--text-3); margin-top: 1px; }

    /* ── PARTENAIRES ───────────────────────────────────── */
    .partners-row {
        display: flex; justify-content: center;
        align-items: center; gap: 16px; flex-wrap: wrap;
    }
    .partner-pill {
        display: flex; align-items: center; gap: 10px;
        background: var(--white); border: 1px solid var(--border);
        border-radius: 12px; padding: 15px 24px;
        box-shadow: var(--shadow); font-size: 14px; font-weight: 600;
        color: var(--text); text-decoration: none;
        transition: all .25s var(--ease);
    }
    .partner-pill i { color: var(--blue-mid); font-size: 16px; }
    .partner-pill:hover { border-color: var(--blue-mid); box-shadow: var(--shadow-lg); transform: translateY(-2px); color: var(--blue); }

    /* ── FAQ ───────────────────────────────────────────── */
    .faq-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .faq-item {
        background: var(--white); border: 1px solid var(--border);
        border-radius: var(--r); padding: 26px 24px;
        box-shadow: var(--shadow);
        transition: border-color .2s var(--ease);
    }
    .faq-item:hover { border-color: var(--blue-mid); }
    .faq-q {
        display: flex; align-items: flex-start; gap: 12px;
        font-size: 15px; font-weight: 700; color: var(--blue);
        margin-bottom: 12px; line-height: 1.4;
    }
    .faq-q-icon {
        width: 28px; height: 28px; flex-shrink: 0; border-radius: 8px;
        background: var(--blue-bg);
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; color: var(--blue-mid); margin-top: 1px;
    }
    .faq-a { font-size: 14px; color: var(--text-2); line-height: 1.75; padding-left: 40px; }

    /* ── CTA FINAL ─────────────────────────────────────── */
    .cta-section {
        background: linear-gradient(135deg, #003399 0%, #1a56cc 60%, #1a7abf 100%);
        padding: 96px 0; text-align: center; position: relative; overflow: hidden;
    }
    .cta-orb { position: absolute; border-radius: 50%; filter: blur(62px); opacity: .12; pointer-events: none; }
    .cta-orb-1 { width: 420px; height: 420px; background: #fff; top: -160px; right: -90px; }
    .cta-orb-2 { width: 260px; height: 260px; background: #93c5fd; bottom: -80px; left: -50px; }
    .cta-inner { position: relative; z-index: 1; }
    .cta-badge {
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.24);
        color: rgba(255,255,255,.88); font-size: 11px; font-weight: 600;
        letter-spacing: 1.5px; text-transform: uppercase;
        padding: 5px 14px; border-radius: 99px; margin-bottom: 20px;
    }
    .cta-section h2 {
        font-size: clamp(1.8rem, 3vw, 2.7rem);
        font-weight: 800; color: #fff; letter-spacing: -.3px; margin-bottom: 14px;
    }
    .cta-section p {
        font-size: 16px; color: rgba(255,255,255,.78); line-height: 1.8;
        max-width: 540px; margin: 0 auto 36px;
    }
    .cta-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    .btn-cta-white {
        display: inline-flex; align-items: center; gap: 9px;
        background: #fff; color: var(--blue); padding: 14px 32px; border-radius: 99px;
        text-decoration: none; font-weight: 700; font-size: 15px; font-family: 'Sora', sans-serif;
        box-shadow: 0 4px 20px rgba(0,0,0,.18); transition: all .3s var(--ease);
    }
    .btn-cta-white:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,.26); }
    .btn-cta-ghost {
        display: inline-flex; align-items: center; gap: 9px;
        background: transparent; color: rgba(255,255,255,.88);
        border: 1.5px solid rgba(255,255,255,.32);
        padding: 14px 28px; border-radius: 99px;
        text-decoration: none; font-weight: 500; font-size: 15px;
        transition: all .3s var(--ease);
    }
    .btn-cta-ghost:hover { border-color: #fff; color: #fff; background: rgba(255,255,255,.08); }

    /* ── RESPONSIVE ────────────────────────────────────── */
    @media (max-width: 960px) {
        .programme-grid { grid-template-columns: repeat(2, 1fr); }
        .stats-strip-inner { grid-template-columns: repeat(2, 1fr); }
        .st-cell:nth-child(2) { border-right: none; }
        .st-cell:nth-child(3) { border-top: 1px solid var(--border); }
    }
    @media (max-width: 768px) {
        .info-grid, .parcours-grid, .temo-grid, .faq-grid { grid-template-columns: 1fr; }
        .countdown { padding: 18px 20px; gap: 8px; }
        .cd-num { font-size: 1.8rem; }
        .hero { padding: 80px 0 80px; }
    }
    @media (max-width: 480px) {
        .programme-grid { grid-template-columns: 1fr; }
        .stats-strip-inner { grid-template-columns: repeat(2, 1fr); }
    }
    </style>
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <!-- ══ HERO ══════════════════════════════════════════ -->
    <section class="hero">
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="container">
            <div class="hero-inner">
                <div class="hero-tag" data-aos="fade-down">
                    <i class="fas fa-ribbon"></i>
                    Octobre Rose · <?= $annee ?>
                </div>

                <h1 data-aos="fade-up" data-aos-delay="80">
                    Marche Contre le Cancer
                </h1>

                <div class="hero-date" data-aos="fade-up" data-aos-delay="130">
                    <i class="fas fa-calendar-alt"></i>
                    20 octobre <?= $annee ?> &nbsp;·&nbsp;
                    <i class="fas fa-map-marker-alt"></i>
                    Parc de Martissant → Champs de Mars &nbsp;·&nbsp; 5 km · Gratuit
                </div>

                <p class="hero-sub" data-aos="fade-up" data-aos-delay="160">
                    Rejoignez des milliers de marcheurs solidaires pour faire avancer la lutte contre le cancer en Haïti.
                </p>

                <div class="countdown" data-aos="fade-up" data-aos-delay="220">
                    <div class="cd-unit">
                        <span class="cd-num" id="cd-jours"><?= $jours_restants ?></span>
                        <span class="cd-label">Jours</span>
                    </div>
                    <span class="cd-sep">:</span>
                    <div class="cd-unit">
                        <span class="cd-num" id="cd-heures">00</span>
                        <span class="cd-label">Heures</span>
                    </div>
                    <span class="cd-sep">:</span>
                    <div class="cd-unit">
                        <span class="cd-num" id="cd-min">00</span>
                        <span class="cd-label">Min</span>
                    </div>
                    <span class="cd-sep">:</span>
                    <div class="cd-unit">
                        <span class="cd-num" id="cd-sec">00</span>
                        <span class="cd-label">Sec</span>
                    </div>
                </div>

                <div class="hero-btns" data-aos="fade-up" data-aos-delay="300">
                    <a href="contact.php" class="btn-hero-primary">
                        <i class="fas fa-walking"></i> Je participe
                    </a>
                    <a href="#parcours" class="btn-hero-secondary">
                        <i class="fas fa-map-marked-alt"></i> Voir le parcours
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ STATS ══════════════════════════════════════════ -->
    <div class="stats-strip">
        <div class="stats-strip-inner">
            <div class="st-cell" data-aos="fade-up">
                <div class="st-ico"><i class="fas fa-users"></i></div>
                <div class="st-n">3 000+</div>
                <div class="st-l">Participants</div>
            </div>
            <div class="st-cell" data-aos="fade-up" data-aos-delay="70">
                <div class="st-ico"><i class="fas fa-route"></i></div>
                <div class="st-n">5 km</div>
                <div class="st-l">Parcours</div>
            </div>
            <div class="st-cell" data-aos="fade-up" data-aos-delay="140">
                <div class="st-ico"><i class="fas fa-calendar-check"></i></div>
                <div class="st-n">6ᵉ</div>
                <div class="st-l">Édition</div>
            </div>
            <div class="st-cell" data-aos="fade-up" data-aos-delay="210">
                <div class="st-ico"><i class="fas fa-hand-holding-heart"></i></div>
                <div class="st-n">100%</div>
                <div class="st-l">Gratuit</div>
            </div>
        </div>
    </div>

    <!-- ══ INFOS PRATIQUES ════════════════════════════════ -->
    <section>
        <div class="container">
            <div class="section-head center" data-aos="fade-up">
                <div class="section-label"><i class="fas fa-info-circle"></i> Informations pratiques</div>
                <h2 class="section-title">Tout ce qu'il faut savoir</h2>
                <p class="section-sub">Préparez votre venue à la grande marche solidaire du GSCC.</p>
            </div>
            <div class="info-grid">
                <div class="info-card" data-aos="fade-up">
                    <div class="info-card-icon"><i class="fas fa-calendar-alt"></i></div>
                    <h3>Date &amp; Heure</h3>
                    <p><strong>20 octobre <?= $annee ?></strong><br>
                    Accueil à partir de <strong>8h00</strong><br>
                    Départ officiel à <strong>9h00</strong></p>
                </div>
                <div class="info-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="info-card-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <h3>Lieu de départ</h3>
                    <p><strong>Parc de Martissant</strong><br>
                    Port-au-Prince, Haïti<br>
                    Arrivée : <strong>Champs de Mars</strong></p>
                </div>
                <div class="info-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="info-card-icon"><i class="fas fa-gift"></i></div>
                    <h3>Kit du marcheur</h3>
                    <p>T-shirt officiel · Bouteille d'eau<br>
                    Bracelet solidaire · Programme<br>
                    Distribué <strong>gratuitement</strong> sur place</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ PARCOURS ═══════════════════════════════════════ -->
    <section class="alt" id="parcours">
        <div class="container">
            <div class="parcours-grid">
                <div class="parcours-map-wrap" data-aos="fade-right">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d123456!2d-72.338!3d18.594!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTjCsDM1JzM4LjQiTiA3MsKwMjAnMTYuOCJX!5e0!3m2!1sfr!2sht!4v1234567890"
                        allowfullscreen="" loading="lazy">
                    </iframe>
                </div>
                <div data-aos="fade-left">
                    <div class="section-label"><i class="fas fa-map-marked-alt"></i> Itinéraire</div>
                    <h2 class="section-title">Le parcours</h2>
                    <p class="parcours-info" style="font-size:15px;color:var(--text-2);line-height:1.8;margin-bottom:28px;">
                        Une marche de <strong>5 kilomètres</strong> à travers les quartiers emblématiques de Port-au-Prince, avec points de ravitaillement et animations musicales tout au long du parcours.
                    </p>
                    <ul class="steps-list">
                        <li class="step-item">
                            <div class="step-dot">A</div>
                            <span><strong>Parc de Martissant</strong> — Départ 9h00 · Accueil des participants</span>
                        </li>
                        <li class="step-item">
                            <div class="step-dot">B</div>
                            <span><strong>Avenue Jean-Paul II</strong> — Point de ravitaillement · Eau &amp; collation</span>
                        </li>
                        <li class="step-item">
                            <div class="step-dot">C</div>
                            <span><strong>Place Boyer</strong> — Animation musicale · Photos solidaires</span>
                        </li>
                        <li class="step-item">
                            <div class="step-dot">D</div>
                            <span><strong>Champs de Mars</strong> — Arrivée · Village solidaire · Clôture officielle</span>
                        </li>
                    </ul>
                    <div class="access-note">
                        <i class="fas fa-wheelchair"></i>
                        Parcours accessible aux personnes à mobilité réduite
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ PROGRAMME ══════════════════════════════════════ -->
    <section>
        <div class="container">
            <div class="section-head center" data-aos="fade-up">
                <div class="section-label"><i class="fas fa-clock"></i> Programme</div>
                <h2 class="section-title">La journée en détail</h2>
                <p class="section-sub">Une journée complète de solidarité, de sport et de convivialité.</p>
            </div>
            <div class="programme-grid">
                <div class="prog-card" data-aos="fade-up">
                    <div class="prog-time">8h00</div>
                    <div class="prog-icon"><i class="fas fa-door-open"></i></div>
                    <div class="prog-title">Accueil</div>
                    <div class="prog-desc">Distribution des kits marcheurs et enregistrement des équipes</div>
                    <div class="prog-accent"></div>
                </div>
                <div class="prog-card" data-aos="fade-up" data-aos-delay="80">
                    <div class="prog-time">9h00</div>
                    <div class="prog-icon"><i class="fas fa-flag"></i></div>
                    <div class="prog-title">Départ officiel</div>
                    <div class="prog-desc">Coup d'envoi de la marche depuis le Parc de Martissant</div>
                    <div class="prog-accent"></div>
                </div>
                <div class="prog-card" data-aos="fade-up" data-aos-delay="160">
                    <div class="prog-time">11h00</div>
                    <div class="prog-icon"><i class="fas fa-music"></i></div>
                    <div class="prog-title">Animation</div>
                    <div class="prog-desc">Concerts live, témoignages de survivants et prises de parole</div>
                    <div class="prog-accent"></div>
                </div>
                <div class="prog-card" data-aos="fade-up" data-aos-delay="240">
                    <div class="prog-time">13h00</div>
                    <div class="prog-icon"><i class="fas fa-hands-holding-heart"></i></div>
                    <div class="prog-title">Village solidaire</div>
                    <div class="prog-desc">Stands, dépistage gratuit, remise des prix et clôture</div>
                    <div class="prog-accent"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ TÉMOIGNAGES ════════════════════════════════════ -->
    <section class="alt">
        <div class="container">
            <div class="section-head center" data-aos="fade-up">
                <div class="section-label"><i class="fas fa-comments"></i> Témoignages</div>
                <h2 class="section-title">Ils ont marché avec nous</h2>
                <p class="section-sub">Des milliers de participants nous ont rejoints ces dernières années.</p>
            </div>
            <div class="temo-grid">
                <div class="temo-card" data-aos="fade-up">
                    <div class="temo-quote">"</div>
                    <p class="temo-text">Une expérience incroyable ! Voir autant de personnes réunies pour une même cause, ça donne de l'espoir. Je reviens chaque année avec toute ma famille.</p>
                    <div class="temo-author">
                        <div class="temo-avatar">MC</div>
                        <div>
                            <div class="temo-name">Marie C.</div>
                            <div class="temo-role">Participante depuis 2019</div>
                        </div>
                    </div>
                </div>
                <div class="temo-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="temo-quote">"</div>
                    <p class="temo-text">J'ai marché pour ma sœur qui se bat contre le cancer. C'était tellement émouvant de voir tout ce soutien. Merci au GSCC pour cette belle initiative.</p>
                    <div class="temo-author">
                        <div class="temo-avatar">JD</div>
                        <div>
                            <div class="temo-name">Jean-Paul D.</div>
                            <div class="temo-role">Première participation</div>
                        </div>
                    </div>
                </div>
                <div class="temo-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="temo-quote">"</div>
                    <p class="temo-text">Avec mon entreprise, nous avons formé une équipe de 25 personnes. C'est devenu un rendez-vous annuel de team building solidaire incontournable.</p>
                    <div class="temo-author">
                        <div class="temo-avatar">SL</div>
                        <div>
                            <div class="temo-name">Sophie L.</div>
                            <div class="temo-role">Capitaine d'équipe</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ PARTENAIRES ════════════════════════════════════ -->
    <section>
        <div class="container">
            <div class="section-head center" data-aos="fade-up">
                <div class="section-label"><i class="fas fa-handshake"></i> Partenaires</div>
                <h2 class="section-title">Ils soutiennent la marche</h2>
            </div>
            <div class="partners-row" data-aos="fade-up" data-aos-delay="80">
                <a href="#" class="partner-pill"><i class="fas fa-university"></i> Sogebank</a>
                <a href="#" class="partner-pill"><i class="fas fa-signal"></i> Digicel</a>
                <a href="#" class="partner-pill"><i class="fas fa-landmark"></i> BNC</a>
                <a href="#" class="partner-pill"><i class="fas fa-broadcast-tower"></i> RTC</a>
                <a href="#" class="partner-pill"><i class="fas fa-newspaper"></i> Le Nouvelliste</a>
            </div>
        </div>
    </section>

    <!-- ══ FAQ ════════════════════════════════════════════ -->
    <section class="alt">
        <div class="container">
            <div class="section-head center" data-aos="fade-up">
                <div class="section-label"><i class="fas fa-question-circle"></i> FAQ</div>
                <h2 class="section-title">Questions fréquentes</h2>
            </div>
            <div class="faq-grid">
                <div class="faq-item" data-aos="fade-up">
                    <div class="faq-q">
                        <div class="faq-q-icon"><i class="fas fa-users"></i></div>
                        Qui peut participer ?
                    </div>
                    <div class="faq-a">Tout le monde, quel que soit l'âge ou la condition physique. Les enfants sont bienvenus accompagnés d'un adulte. Le parcours est adapté aux PMR.</div>
                </div>
                <div class="faq-item" data-aos="fade-up" data-aos-delay="50">
                    <div class="faq-q">
                        <div class="faq-q-icon"><i class="fas fa-gift"></i></div>
                        Que contient le kit du marcheur ?
                    </div>
                    <div class="faq-a">Un T-shirt officiel, une bouteille d'eau, un en-cas, un bracelet solidaire et le programme de la journée — tout distribué gratuitement sur place.</div>
                </div>
                <div class="faq-item" data-aos="fade-up" data-aos-delay="100">
                    <div class="faq-q">
                        <div class="faq-q-icon"><i class="fas fa-clipboard-check"></i></div>
                        Faut-il s'inscrire à l'avance ?
                    </div>
                    <div class="faq-a">Ce n'est pas obligatoire, mais recommandé. Informez-nous via la page contact. Le jour J, arrivez avant 8h30 pour récupérer votre kit sans attente.</div>
                </div>
                <div class="faq-item" data-aos="fade-up" data-aos-delay="150">
                    <div class="faq-q">
                        <div class="faq-q-icon"><i class="fas fa-home"></i></div>
                        Puis-je venir en famille ?
                    </div>
                    <div class="faq-a">Absolument ! La marche est pensée comme un moment familial et convivial. Enfants, parents, grands-parents — toutes les générations sont les bienvenues.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ CTA FINAL ══════════════════════════════════════ -->
    <section class="cta-section">
        <div class="cta-orb cta-orb-1"></div>
        <div class="cta-orb cta-orb-2"></div>
        <div class="container cta-inner">
            <div class="cta-badge" data-aos="fade-down">
                <i class="fas fa-calendar-alt"></i> 20 octobre <?= $annee ?>
            </div>
            <h2 data-aos="fade-up">
                <?= $jours_restants ?> jours avant la marche
            </h2>
            <p data-aos="fade-up" data-aos-delay="80">
                La participation est <strong>100% gratuite</strong>. Venez nombreux avec famille et amis pour cette journée de solidarité et d'espoir contre le cancer.
            </p>
            <div class="cta-btns" data-aos="fade-up" data-aos-delay="160">
                <a href="contact.php" class="btn-cta-white">
                    <i class="fas fa-walking"></i> Je participe
                </a>
                <a href="faire-un-don.php" class="btn-cta-ghost">
                    <i class="fas fa-heart"></i> Faire un don
</a>
            </div>
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        AOS.init({ duration: 700, once: true, offset: 60 });

        // Compte à rebours en temps réel
        (function() {
            var target = <?= $timestamp_marche ?>;
            function pad(n) { return n < 10 ? '0' + n : n; }
            function tick() {
                var diff = target - Date.now();
                if (diff <= 0) {
                    ['cd-jours','cd-heures','cd-min','cd-sec'].forEach(function(id) {
                        document.getElementById(id).textContent = '00';
                    });
                    return;
                }
                document.getElementById('cd-jours').textContent  = Math.floor(diff / 86400000);
                document.getElementById('cd-heures').textContent = pad(Math.floor((diff % 86400000) / 3600000));
                document.getElementById('cd-min').textContent    = pad(Math.floor((diff % 3600000) / 60000));
                document.getElementById('cd-sec').textContent    = pad(Math.floor((diff % 60000) / 1000));
            }
            tick();
            setInterval(tick, 1000);
        })();
    </script>
</body>
</html>