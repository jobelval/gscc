<?php
// football-rose.php — Le Football Rose
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title       = 'Football Rose';
$page_description = 'Le Football Rose — initiative phare du GSCC alliant sport, solidarité et sensibilisation contre le cancer du sein.';

require_once 'templates/header.php';
?>

<!-- ══════════════════════════════════════════════════════════
     STYLES SPÉCIFIQUES — FOOTBALL ROSE
     ══════════════════════════════════════════════════════════ -->
<style>
    :root {
        --fr-rose:       #D94F7A;
        --fr-rose-dark:  #B03060;
        --fr-rose-deep:  #8B1A40;
        --fr-rose-pale:  #FDE8EF;
        --fr-rose-mid:   #F5B8CC;
        --fr-gold:       #C9933A;
        --fr-dark:       #1A0A12;
        --fr-charcoal:   #1E2A35;
        --fr-white:      #FFFFFF;
        --fr-gray-bg:    #F9F0F4;
    }

    /* ── HERO ─────────────────────────────────────────────────── */
    .fr-hero {
        position: relative;
        min-height: 560px;
        display: flex;
        align-items: center;
        background:
            linear-gradient(135deg, rgba(26, 10, 18, 0.90) 0%, rgba(176, 48, 96, 0.82) 55%, rgba(217, 79, 122, 0.72) 100%),
            url('images/Football-rose/photo1.jpg') center / cover no-repeat;
        overflow: hidden;
        padding: 100px 0 90px;
    }

    .fr-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 60% 60% at 80% 40%, rgba(217, 79, 122, 0.28), transparent),
            radial-gradient(ellipse 35% 35% at 10% 75%, rgba(255, 255, 255, 0.05), transparent);
        pointer-events: none;
    }

    /* Cercles décoratifs */
    .fr-hero-orbs span {
        position: absolute;
        border-radius: 50%;
        opacity: .06;
        background: white;
    }
    .fr-hero-orbs span:nth-child(1) { width: 380px; height: 380px; top: -100px; right: -80px; }
    .fr-hero-orbs span:nth-child(2) { width: 200px; height: 200px; bottom: 20px; left: 6%; }
    .fr-hero-orbs span:nth-child(3) { width: 100px; height: 100px; top: 50px;   left: 38%; }

    .fr-hero-content {
        position: relative;
        z-index: 2;
        max-width: 760px;
    }

    .fr-hero-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.13);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.22);
        color: #fff;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        padding: 7px 18px;
        border-radius: 30px;
        margin-bottom: 22px;
        text-decoration: none;
    }
    .fr-hero-tag i { color: var(--fr-rose-mid); }

    .fr-hero h1 {
        font-family: 'Playfair Display', serif;
        font-size: clamp(2.4rem, 6vw, 4rem);
        color: #fff;
        font-weight: 700;
        line-height: 1.12;
        margin-bottom: 12px;
        text-shadow: 0 2px 20px rgba(0,0,0,0.3);
    }

    .fr-hero h1 span {
        color: var(--fr-rose-mid);
    }

    .fr-hero-sub {
        font-size: 1.1rem;
        color: rgba(255,255,255,0.88);
        max-width: 600px;
        line-height: 1.75;
        margin-bottom: 36px;
    }

    .fr-hero-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .fr-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: rgba(255,255,255,0.13);
        border: 1px solid rgba(255,255,255,0.22);
        color: #fff;
        font-size: 0.85rem;
        font-weight: 500;
        padding: 8px 18px;
        border-radius: 30px;
        backdrop-filter: blur(4px);
    }
    .fr-badge i { color: var(--fr-rose-mid); font-size: .8rem; }

    /* ── SECTION TAG générique ─────────────────────────────────── */
    .fr-section-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--fr-rose-pale);
        color: var(--fr-rose-dark);
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        padding: 5px 14px;
        border-radius: 30px;
        margin-bottom: 12px;
    }

    .fr-divider {
        width: 56px;
        height: 4px;
        background: linear-gradient(90deg, var(--fr-rose-dark), var(--fr-rose-mid));
        border-radius: 2px;
        margin: 12px auto 18px;
    }

    /* ── CONCEPT ───────────────────────────────────────────────── */
    .fr-concept {
        padding: 90px 0;
        background: var(--fr-white);
    }

    .fr-concept-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 64px;
        align-items: center;
    }

    .fr-concept-text h2 {
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.8rem, 3vw, 2.4rem);
        color: var(--fr-charcoal);
        margin-bottom: 20px;
        line-height: 1.25;
    }
    .fr-concept-text h2 em {
        font-style: italic;
        color: var(--fr-rose-dark);
    }
    .fr-concept-text p {
        color: #374151;
        line-height: 1.82;
        font-size: 1rem;
        margin-bottom: 18px;
    }

    .fr-objectives {
        list-style: none;
        padding: 0;
        margin: 28px 0 0;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .fr-objectives li {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        background: var(--fr-gray-bg);
        padding: 16px 20px;
        border-radius: 14px;
        border-left: 4px solid var(--fr-rose);
        transition: transform .22s, box-shadow .22s;
    }
    .fr-objectives li:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 20px rgba(217,79,122,.12);
    }
    .fr-obj-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: var(--fr-rose-pale);
        color: var(--fr-rose-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .fr-objectives li p {
        margin: 0;
        font-size: .95rem;
        color: #374151;
        line-height: 1.6;
    }
    .fr-objectives li strong {
        display: block;
        color: var(--fr-charcoal);
        font-size: .9rem;
        font-weight: 700;
        margin-bottom: 2px;
    }

    /* Image concept */
    .fr-concept-visual {
        position: relative;
    }
    .fr-concept-img-wrap {
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 24px 64px rgba(176,48,96,.2);
        position: relative;
    }
    .fr-concept-img-wrap img {
        width: 100%;
        height: 480px;
        object-fit: cover;
        display: block;
        transition: transform .5s ease;
    }
    .fr-concept-img-wrap:hover img { transform: scale(1.04); }

    .fr-concept-badge {
        position: absolute;
        bottom: -20px;
        left: -20px;
        background: var(--fr-rose-dark);
        color: #fff;
        padding: 18px 24px;
        border-radius: 18px;
        box-shadow: 0 8px 30px rgba(176,48,96,.4);
        text-align: center;
        font-family: 'DM Sans', 'Inter', sans-serif;
    }
    .fr-concept-badge .big { font-family: 'Playfair Display', serif; font-size: 2.2rem; font-weight: 700; line-height: 1; }
    .fr-concept-badge .small { font-size: .78rem; font-weight: 600; opacity: .9; letter-spacing: .5px; text-transform: uppercase; }

    /* ── STATS ─────────────────────────────────────────────────── */
    .fr-stats {
        background:
            linear-gradient(135deg, rgba(26,10,18,.88) 0%, rgba(139,26,64,.82) 55%, rgba(176,48,96,.76) 100%),
            url('images/Football-rose/photo2.jpg') center / cover no-repeat;
        padding: 70px 0;
        color: #fff;
    }
    .fr-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0;
        text-align: center;
    }
    .fr-stat-item {
        padding: 28px 20px;
        border-right: 1px solid rgba(255,255,255,.12);
    }
    .fr-stat-item:last-child { border-right: none; }
    .fr-stat-number {
        font-family: 'Playfair Display', serif;
        font-size: 3rem;
        font-weight: 700;
        color: var(--fr-rose-mid);
        line-height: 1;
        margin-bottom: 8px;
    }
    .fr-stat-label {
        font-size: .9rem;
        color: rgba(255,255,255,.85);
        line-height: 1.4;
    }

    /* ── ÉDITION 2025 ──────────────────────────────────────────── */
    .fr-edition {
        padding: 90px 0;
        background: var(--fr-gray-bg);
    }

    .fr-edition-header {
        text-align: center;
        margin-bottom: 64px;
    }
    .fr-edition-header h2 {
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.9rem, 3.5vw, 2.6rem);
        color: var(--fr-charcoal);
        margin-bottom: 10px;
    }
    .fr-edition-header .kreol {
        font-size: 1rem;
        color: var(--fr-rose-dark);
        font-weight: 700;
        letter-spacing: .5px;
        font-style: italic;
    }
    .fr-edition-header p {
        max-width: 600px;
        margin: 14px auto 0;
        color: #4B5563;
        font-size: 1rem;
        line-height: 1.75;
    }

    /* Grille match */
    .fr-match-grid {
        display: grid;
        grid-template-columns: 1.15fr 1fr;
        gap: 40px;
        margin-bottom: 56px;
        align-items: start;
    }

    .fr-match-card {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(176,48,96,.1);
    }
    .fr-match-card-img {
        height: 300px;
        overflow: hidden;
        position: relative;
    }
    .fr-match-card-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .5s ease;
        display: block;
    }
    .fr-match-card:hover .fr-match-card-img img { transform: scale(1.05); }
    .fr-match-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 32px 24px 20px;
        background: linear-gradient(to top, rgba(26,10,18,.9), transparent);
    }
    .fr-teams {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
    }
    .fr-team {
        font-family: 'Playfair Display', serif;
        font-size: 1.05rem;
        color: #fff;
        font-weight: 700;
        text-align: center;
        line-height: 1.3;
    }
    .fr-vs {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--fr-rose);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: .8rem;
        color: #fff;
        flex-shrink: 0;
    }

    .fr-match-body { padding: 28px; }
    .fr-match-body h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.35rem;
        color: var(--fr-charcoal);
        margin-bottom: 12px;
    }
    .fr-match-body p {
        color: #4B5563;
        font-size: .95rem;
        line-height: 1.75;
        margin: 0;
    }

    /* Cartes impact */
    .fr-impact-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }
    .fr-impact-card {
        background: #fff;
        border-radius: 16px;
        padding: 28px 24px;
        box-shadow: 0 4px 20px rgba(176,48,96,.08);
        border-top: 4px solid var(--fr-rose);
        transition: transform .25s, box-shadow .25s;
    }
    .fr-impact-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 14px 40px rgba(176,48,96,.16);
    }
    .fr-impact-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: var(--fr-rose-pale);
        color: var(--fr-rose-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        margin-bottom: 18px;
    }
    .fr-impact-card h4 {
        font-family: 'Playfair Display', serif;
        font-size: 1.1rem;
        color: var(--fr-charcoal);
        margin-bottom: 8px;
    }
    .fr-impact-card p {
        color: #4B5563;
        font-size: .9rem;
        line-height: 1.65;
        margin: 0;
    }
    .fr-impact-card .fr-meta {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: var(--fr-rose-pale);
        color: var(--fr-rose-dark);
        font-size: .76rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        margin-bottom: 12px;
    }

    /* ── GALERIE ───────────────────────────────────────────────── */
    .fr-gallery {
        padding: 90px 0;
        background: #fff;
    }
    .fr-gallery-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        grid-template-rows: auto auto;
        gap: 16px;
    }
    .fr-gallery-item {
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        display: block;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(0,0,0,.1);
    }
    .fr-gallery-item.large {
        grid-row: span 2;
    }
    .fr-gallery-item img {
        width: 100%;
        height: 100%;
        min-height: 220px;
        object-fit: cover;
        display: block;
        transition: transform .5s ease;
    }
    .fr-gallery-item.large img { min-height: 100%; }
    .fr-gallery-item:hover img { transform: scale(1.07); }
    .fr-gallery-caption {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        padding: 28px 18px 16px;
        background: linear-gradient(to top, rgba(26,10,18,.88), transparent);
        color: #fff;
        font-size: .88rem;
        font-weight: 600;
        opacity: 0;
        transition: opacity .28s;
    }
    .fr-gallery-item:hover .fr-gallery-caption { opacity: 1; }

    /* ── CITATION ──────────────────────────────────────────────── */
    .fr-quote-section {
        background:
            linear-gradient(135deg, rgba(139,26,64,.92) 0%, rgba(176,48,96,.88) 50%, rgba(217,79,122,.84) 100%),
            url('images/Football-rose/photo3.jpg') center / cover no-repeat;
        padding: 90px 0;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .fr-quote-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse 70% 70% at 50% 50%, rgba(255,255,255,.06), transparent);
    }
    .fr-quote-inner { position: relative; z-index: 1; max-width: 780px; margin: 0 auto; }
    .fr-quote-icon {
        font-size: 3.5rem;
        color: rgba(255,255,255,.25);
        line-height: 1;
        margin-bottom: 10px;
    }
    .fr-quote-text {
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.6rem, 3.5vw, 2.4rem);
        color: #fff;
        font-weight: 700;
        font-style: italic;
        line-height: 1.4;
        margin-bottom: 14px;
        text-shadow: 0 2px 12px rgba(0,0,0,.2);
    }
    .fr-quote-kreol {
        font-size: 1.1rem;
        color: var(--fr-rose-mid);
        font-weight: 700;
        letter-spacing: .5px;
    }
    .fr-quote-attr {
        margin-top: 24px;
        color: rgba(255,255,255,.7);
        font-size: .88rem;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    /* ── CTA ───────────────────────────────────────────────────── */
    .fr-cta {
        padding: 80px 0;
        background: var(--fr-gray-bg);
        text-align: center;
    }
    .fr-cta h2 {
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.7rem, 3vw, 2.2rem);
        color: var(--fr-charcoal);
        margin-bottom: 14px;
    }
    .fr-cta p {
        color: #4B5563;
        font-size: 1rem;
        max-width: 540px;
        margin: 0 auto 36px;
        line-height: 1.75;
    }
    .fr-cta-btns { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
    .fr-btn-primary {
        display: inline-flex; align-items: center; gap: 9px;
        background: linear-gradient(135deg, var(--fr-rose-dark), var(--fr-rose));
        color: #fff; padding: 15px 36px; border-radius: 50px;
        font-size: 1rem; font-weight: 700; text-decoration: none;
        box-shadow: 0 6px 22px rgba(176,48,96,.32);
        transition: all .22s;
    }
    .fr-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(176,48,96,.44); color: #fff;
    }
    .fr-btn-outline {
        display: inline-flex; align-items: center; gap: 9px;
        background: transparent; color: var(--fr-rose-dark);
        border: 2px solid var(--fr-rose-dark);
        padding: 13px 32px; border-radius: 50px;
        font-size: 1rem; font-weight: 700; text-decoration: none;
        transition: all .22s;
    }
    .fr-btn-outline:hover {
        background: var(--fr-rose-dark); color: #fff;
        transform: translateY(-3px);
    }

    /* ── RESPONSIVE ────────────────────────────────────────────── */
    @media (max-width: 960px) {
        .fr-concept-grid { grid-template-columns: 1fr; gap: 40px; }
        .fr-concept-visual { order: -1; }
        .fr-concept-img-wrap img { height: 340px; }
        .fr-match-grid { grid-template-columns: 1fr; }
        .fr-impact-grid { grid-template-columns: 1fr 1fr; }
        .fr-stats-grid { grid-template-columns: repeat(2, 1fr); }
        .fr-stat-item { border-right: none; border-bottom: 1px solid rgba(255,255,255,.12); }
        .fr-stat-item:last-child { border-bottom: none; }
    }
    @media (max-width: 768px) {
        .fr-hero { padding: 80px 0 70px; min-height: auto; }
        .fr-gallery-grid { grid-template-columns: 1fr 1fr; }
        .fr-gallery-item.large { grid-row: span 1; }
        .fr-impact-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 480px) {
        .fr-gallery-grid { grid-template-columns: 1fr; }
        .fr-stats-grid { grid-template-columns: 1fr 1fr; }
    }
</style>

<!-- ══ HERO ═════════════════════════════════════════════════════ -->
<section class="fr-hero">
    <div class="fr-hero-orbs">
        <span></span><span></span><span></span>
    </div>
    <div class="container">
        <div class="fr-hero-content" data-aos="fade-up">
            <div class="fr-hero-tag">
                <i class="fas fa-futbol"></i>
                GSCC — Initiative sportive &amp; solidaire
            </div>
            <h1>Football <span>Rose</span></h1>
            <p class="fr-hero-sub">
                Là où le sport rencontre la solidarité. Ensemble, nous portons le message
                que le cancer nous concerne tous — et qu'ensemble, nous sommes plus forts.
            </p>
            <div class="fr-hero-badges" data-aos="fade-up" data-aos-delay="150">
                <span class="fr-badge"><i class="fas fa-ribbon"></i> Octobre Rose</span>
                <span class="fr-badge"><i class="fas fa-heart"></i> Solidarité & espoir</span>
                <span class="fr-badge"><i class="fas fa-map-marker-alt"></i> Haïti</span>
                <span class="fr-badge"><i class="fas fa-futbol"></i> Sport &amp; sensibilisation</span>
            </div>
        </div>
    </div>
</section>

<!-- ══ CONCEPT ══════════════════════════════════════════════════ -->
<section class="fr-concept">
    <div class="container">
        <div class="fr-concept-grid">

            <!-- Texte -->
            <div class="fr-concept-text" data-aos="fade-right">
                <div class="fr-section-tag">
                    <i class="fas fa-lightbulb"></i> Le concept
                </div>
                <h2>Le sport au service <em>de la vie</em></h2>
                <p>
                    Le <strong>Football Rose</strong> est une initiative phare du Groupe de Support Contre le Cancer (GSCC)
                    qui allie sport, solidarité et sensibilisation. À travers des matchs de football symboliques
                    et engagés, cette activité vise à mobiliser la communauté autour de la lutte contre le cancer,
                    en particulier le cancer du sein.
                </p>
                <p>
                    C'est un espace unique où athlètes, survivantes, familles et supporters se réunissent pour
                    porter un message d'espoir : le cancer nous concerne tous, et <strong>ensemble, nous sommes
                    plus forts que la maladie</strong>.
                </p>

                <ul class="fr-objectives" data-aos="fade-up" data-aos-delay="100">
                    <li>
                        <div class="fr-obj-icon"><i class="fas fa-bullhorn"></i></div>
                        <div>
                            <strong>Sensibiliser</strong>
                            <p>Sensibiliser le grand public à l'importance du dépistage précoce du cancer.</p>
                        </div>
                    </li>
                    <li>
                        <div class="fr-obj-icon"><i class="fas fa-hand-holding-heart"></i></div>
                        <div>
                            <strong>Mobiliser</strong>
                            <p>Mobiliser des fonds pour soutenir les patients et leurs familles dans leurs parcours.</p>
                        </div>
                    </li>
                    <li>
                        <div class="fr-obj-icon"><i class="fas fa-users"></i></div>
                        <div>
                            <strong>Rassembler</strong>
                            <p>Rassembler la communauté haïtienne autour d'une cause de santé publique majeure.</p>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Image -->
            <div class="fr-concept-visual" data-aos="fade-left">
                <div class="fr-concept-img-wrap">
                    <img src="images/Football-rose/photo4.jpg" alt="Équipe Football Rose GSCC en t-shirts roses">
                </div>
                <div class="fr-concept-badge">
                    <div class="big">25<sup style="font-size:.6em;">+</sup></div>
                    <div class="small">Ans d'engagement</div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ══ STATISTIQUES ════════════════════════════════════════════ -->
<section class="fr-stats">
    <div class="container">
        <div class="fr-stats-grid">
            <div class="fr-stat-item" data-aos="zoom-in" data-aos-delay="0">
                <div class="fr-stat-number">1<span style="font-size:1.8rem;">+</span></div>
                <div class="fr-stat-label">Édition organisée</div>
            </div>
            <div class="fr-stat-item" data-aos="zoom-in" data-aos-delay="80">
                <div class="fr-stat-number">100<span style="font-size:1.8rem;">%</span></div>
                <div class="fr-stat-label">Des revenus reversés aux familles</div>
            </div>
            <div class="fr-stat-item" data-aos="zoom-in" data-aos-delay="160">
                <div class="fr-stat-number">2<span style="font-size:1.8rem;"> équipes</span></div>
                <div class="fr-stat-label">En compétition solidaire</div>
            </div>
            <div class="fr-stat-item" data-aos="zoom-in" data-aos-delay="240">
                <div class="fr-stat-number">1<span style="font-size:1.8rem;"> message</span></div>
                <div class="fr-stat-label">« Ensemble contre le cancer »</div>
            </div>
        </div>
    </div>
</section>

<!-- ══ ÉDITION 2025 ════════════════════════════════════════════ -->
<section class="fr-edition">
    <div class="container">

        <div class="fr-edition-header" data-aos="fade-up">
            <div class="fr-section-tag">
                <i class="fas fa-calendar-alt"></i> Archive
            </div>
            <h2>Football Rose 2025</h2>
            <p class="kreol">« Kansè Pa Ka Tann : Ann Aji Kounya »</p>
            <p>
                L'édition 2025, inscrite dans le cadre de la campagne <strong>Octobre Rose</strong>,
                sous le cri de ralliement « Le cancer n'attend pas : agissons maintenant ».
                Un événement qui a marqué les esprits par son intensité et son message fort de prévention.
            </p>
        </div>

        <!-- Match de la solidarité -->
        <div class="fr-match-grid">
            <div class="fr-match-card" data-aos="fade-right">
                <div class="fr-match-card-img">
                    <img src="images/Football-rose/photo5.jpg" alt="Match Football Rose 2025 — Parc Sainte-Thérèse">
                    <div class="fr-match-overlay">
                        <div class="fr-teams">
                            <div class="fr-team">Dumornay<br>FC</div>
                            <div class="fr-vs">VS</div>
                            <div class="fr-team">FC Girl<br>Squad</div>
                        </div>
                    </div>
                </div>
                <div class="fr-match-body">
                    <h3>Le Match de la Solidarité</h3>
                    <p>
                        Le clou de cette édition a été la rencontre opposant le <strong>Dumornay FC</strong>
                        au <strong>FC Girl Squad</strong> au <strong>Parc Sainte-Thérèse de Pétion-Ville</strong>.
                        Plus qu'une simple compétition, ce match caritatif a servi de plateforme pour amplifier
                        la voix de milliers de patients luttant chaque jour contre le cancer du sein en Haïti.
                    </p>
                </div>
            </div>

            <!-- Impact -->
            <div data-aos="fade-left">
                <div style="margin-bottom:20px;">
                    <div class="fr-section-tag" style="margin-bottom:10px;">
                        <i class="fas fa-chart-bar"></i> Impact &amp; Engagement
                    </div>
                    <div class="fr-divider" style="margin:0 0 0 0;"></div>
                </div>

                <div class="fr-impact-grid" style="grid-template-columns:1fr;">

                    <div class="fr-impact-card" data-aos="fade-up" data-aos-delay="0">
                        <div class="fr-meta"><i class="fas fa-calendar"></i> Date &amp; Lieu</div>
                        <h4>Samedi 8 novembre — Parc Sainte-Thérèse</h4>
                        <p>L'événement s'est tenu au cœur de Pétion-Ville, un lieu symbolique accessible à toute la communauté haïtienne.</p>
                    </div>

                    <div class="fr-impact-card" data-aos="fade-up" data-aos-delay="80">
                        <div class="fr-meta"><i class="fas fa-star"></i> Mission</div>
                        <h4>Financer le dépistage &amp; la sensibilisation</h4>
                        <p>Financer les actions de soutien, de dépistage et de sensibilisation à travers tout le pays.</p>
                    </div>

                    <div class="fr-impact-card" data-aos="fade-up" data-aos-delay="160">
                        <div class="fr-meta"><i class="fas fa-check-circle"></i> Résultat social</div>
                        <h4>100% des revenus pour les familles</h4>
                        <p>L'intégralité des revenus générés a été allouée pour offrir <strong>espoir et dignité</strong> aux familles touchées par la maladie.</p>
                    </div>

                </div>
            </div>
        </div>

    </div>
</section>

<!-- ══ GALERIE ══════════════════════════════════════════════════ -->
<section class="fr-gallery">
    <div class="container">
        <div style="text-align:center;margin-bottom:52px;" data-aos="fade-up">
            <div class="fr-section-tag" style="margin:0 auto 12px;">
                <i class="fas fa-images"></i> Galerie photos
            </div>
            <h2 style="font-family:'Playfair Display',serif;font-size:clamp(1.7rem,3vw,2.3rem);color:var(--fr-charcoal);margin-bottom:12px;">
                Les moments forts
            </h2>
            <div class="fr-divider"></div>
            <p style="color:#4B5563;max-width:500px;margin:0 auto;font-size:.97rem;line-height:1.7;">
                Revivez les instants inoubliables du Football Rose à travers ces images qui témoignent
                de l'engagement et de la joie de notre communauté.
            </p>
        </div>

        <div class="fr-gallery-grid">

            <!-- Grande image -->
            <div class="fr-gallery-item large" data-aos="fade-right" data-aos-delay="0">
                <img src="images/Football-rose/photo6.jpg" alt="Football Rose — 25e anniversaire GSCC, équipe en t-shirts roses">
                <div class="fr-gallery-caption">
                    <i class="fas fa-camera" style="margin-right:6px;"></i>
                    25e anniversaire — Équipe GSCC en action
                </div>
            </div>

            <!-- Colonne droite -->
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div class="fr-gallery-item" data-aos="fade-left" data-aos-delay="80">
                    <img src="images/Football-rose/photo7.jpg" alt="Football Rose — Grande photo de groupe GSCC">
                    <div class="fr-gallery-caption">
                        <i class="fas fa-camera" style="margin-right:6px;"></i>
                        La grande famille GSCC
                    </div>
                </div>
                <div class="fr-gallery-item" data-aos="fade-left" data-aos-delay="150">
                    <img src="images/Football-rose/photo8.jpg" alt="Football Rose — Bénévoles à l'inscription">
                    <div class="fr-gallery-caption">
                        <i class="fas fa-camera" style="margin-right:6px;"></i>
                        Bénévoles engagés sur le terrain
                    </div>
                </div>
            </div>

            <!-- Ligne du bas -->
            <div class="fr-gallery-item" data-aos="fade-up" data-aos-delay="100">
                <img src="images/Football-rose/photo9.jpg" alt="Football Rose — Membres avec bannière GSCC">
                <div class="fr-gallery-caption">
                    <i class="fas fa-camera" style="margin-right:6px;"></i>
                    Membres et supporters solidaires
                </div>
            </div>
            <div class="fr-gallery-item" data-aos="fade-up" data-aos-delay="170">
                <img src="images/Football-rose/photo10.jpg" alt="Football Rose — Porte-parole GSCC">
                <div class="fr-gallery-caption">
                    <i class="fas fa-camera" style="margin-right:6px;"></i>
                    Porte-parole de la cause
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ══ CITATION ═════════════════════════════════════════════════ -->
<section class="fr-quote-section">
    <div class="container">
        <div class="fr-quote-inner" data-aos="zoom-in">
            <div class="fr-quote-icon">"</div>
            <div class="fr-quote-text">
                Ensemble, nous sommes plus forts que le cancer.
            </div>
            <div class="fr-quote-kreol">« Ansanm nou pi fò pase kansè. »</div>
            <div class="fr-quote-attr">— GSCC, Football Rose 2025</div>
        </div>
    </div>
</section>

<!-- ══ CTA ══════════════════════════════════════════════════════ -->
<section class="fr-cta">
    <div class="container">
        <div data-aos="fade-up">
            <div class="fr-section-tag" style="margin:0 auto 16px;">
                <i class="fas fa-hands-helping"></i> Rejoignez-nous
            </div>
            <h2>Participez à la prochaine édition</h2>
            <p>
                Vous voulez soutenir le Football Rose et contribuer à la lutte contre le cancer en Haïti ?
                Faites un don, devenez bénévole ou partenaire de cet événement unique.
            </p>
            <div class="fr-cta-btns">
                <a href="faire-un-don.php" class="fr-btn-primary">
                    <i class="fas fa-heart"></i> Faire un don
                </a>
                <a href="benevolat.php" class="fr-btn-outline">
                    <i class="fas fa-hands-helping"></i> Devenir bénévole
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'templates/footer.php'; ?>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({ duration: 650, once: true, offset: 60 });</script>
