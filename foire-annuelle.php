<?php
// foire-annuelle.php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title       = 'Grande Foire Annuelle';
$page_description = 'La plus grande foire de collecte de fonds et de sensibilisation organisée par le GSCC.';

// Date de la prochaine foire — si déjà passée cette année, afficher l'année suivante
$annee           = (int)date('Y');
$prochaine_foire = mktime(0, 0, 0, 11, 15, $annee);
if ($prochaine_foire < time()) {
    $annee++;
    $prochaine_foire = mktime(0, 0, 0, 11, 15, $annee);
}
$jours_restants = max(0, (int)ceil(($prochaine_foire - time()) / 86400));

require_once 'templates/header.php';
?>

<style>
/* ───────────────────────────────────────────────────────────────
   FOIRE ANNUELLE — styles spécifiques
   ─────────────────────────────────────────────────────────────── */

/* Offset ancres header sticky */
#programme { scroll-margin-top: 110px; }

/* ══ HERO ══════════════════════════════════════════════════════ */
.foire-hero {
    background: linear-gradient(135deg, #003399 0%, #001a66 55%, #1a1a2e 100%);
    color: white;
    padding: 90px 0 80px;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.foire-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path fill="white" opacity=".05" d="M50 15 L61 35 L85 40 L67 55 L70 80 L50 70 L30 80 L33 55 L15 40 L39 35 Z"/></svg>') center / 110px repeat;
    animation: herorot 50s linear infinite;
    pointer-events: none;
}
@keyframes herorot { to { transform: rotate(360deg) scale(1.15); } }
.foire-hero::after {
    content: '';
    position: absolute;
    bottom: -30%; right: -5%;
    width: 480px; height: 480px;
    background: radial-gradient(circle, rgba(217,79,122,0.2), transparent 70%);
    pointer-events: none;
}
.foire-hero-inner { position: relative; z-index: 1; }

/* Tag au-dessus du titre */
.foire-tag {
    display: inline-block;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.22);
    color: white;
    font-size: 0.75rem; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    padding: 5px 18px; border-radius: 30px;
    margin-bottom: 22px;
}
.foire-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.2rem, 6vw, 4rem);
    color: white;
    margin-bottom: 18px; line-height: 1.15;
}
.foire-hero h1 em { color: #F2A8C0; font-style: normal; }

/* Description sous le titre — blanc suffisamment opaque = lisible */
.foire-hero-sub {
    font-size: 1.15rem;
    color: rgba(255,255,255,1.0);    /* blanc plein */
    max-width: 580px; margin: 0 auto 36px; line-height: 1.75;
}

/* Pastilles date / lieu / horaire */
.foire-meta-row {
    display: flex; justify-content: center;
    gap: 12px; flex-wrap: wrap; margin-bottom: 38px;
}
.foire-meta-pill {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    padding: 8px 18px; border-radius: 30px;
    font-size: 0.93rem; color: white; font-weight: 500;
}
.foire-meta-pill i { color: #F2A8C0; }

/* Boutons hero */
.hero-cta-row { display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; }
.btn-rose {
    background: #D94F7A; color: white;
    padding: 14px 32px; border-radius: 30px;
    font-weight: 700; font-size: 1rem;
    text-decoration: none;
    display: inline-flex; align-items: center; gap: 9px;
    box-shadow: 0 4px 18px rgba(217,79,122,0.45);
    transition: all 0.25s;
}
.btn-rose:hover {
    background: #C0306A; color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 26px rgba(217,79,122,0.55);
}
.btn-ghost-white {
    background: transparent; color: white;
    padding: 14px 30px; border-radius: 30px;
    font-weight: 600; font-size: 1rem;
    text-decoration: none;
    display: inline-flex; align-items: center; gap: 9px;
    border: 2px solid rgba(255,255,255,0.4);
    transition: all 0.25s;
}
.btn-ghost-white:hover {
    border-color: white;
    background: rgba(255,255,255,0.08);
    color: white;
}



/* ══ PRÉSENTATION ══════════════════════════════════════════════ */
.presentation-section { padding: 80px 0; background: white; }
.presentation-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 60px; align-items: center;
}
.presentation-image {
    border-radius: 20px; overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,0,0,0.12);
}
.presentation-image img { width: 100%; height: auto; display: block; }

.pres-eyebrow {
    display: inline-block; background: #EEF2FF; color: #003399;
    font-size: 0.72rem; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; padding: 5px 14px; border-radius: 30px;
    margin-bottom: 14px;
}
.presentation-text h2 {
    font-family: 'Playfair Display', serif;
    color: #1E2A35; font-size: 2rem;
    margin-bottom: 16px; line-height: 1.3;
}
.pres-line {
    width: 52px; height: 4px;
    background: linear-gradient(90deg, #003399, #D94F7A);
    border-radius: 2px; margin-bottom: 22px;
}
/* LISIBLE : texte corps sur fond blanc */
.presentation-text p { color: #374151; line-height: 1.8; margin-bottom: 16px; }

.info-list { list-style: none; padding: 0; margin: 22px 0 0; }
.info-list li {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 10px 0; border-bottom: 1px solid #F0F2F5;
    font-size: 0.96rem; color: #1E2A35;
}
.info-list li:last-child { border-bottom: none; }
.info-list i { color: #003399; margin-top: 3px; width: 16px; flex-shrink: 0; }

/* ══ EN-TÊTE DE SECTION (helper) ═══════════════════════════════ */
.sec-head { text-align: center; margin-bottom: 58px; }
.sec-eyebrow {
    display: inline-block; background: #EEF2FF; color: #003399;
    font-size: 0.72rem; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; padding: 5px 14px; border-radius: 30px;
    margin-bottom: 12px;
}
.sec-head h2 {
    font-family: 'Playfair Display', serif;
    color: #003399; font-size: 2.1rem; margin-bottom: 14px;
}
.sec-rule {
    width: 58px; height: 4px;
    background: linear-gradient(90deg, #003399, #D94F7A);
    border-radius: 2px; margin: 0 auto;
}

/* ══ POURQUOI PARTICIPER ═══════════════════════════════════════ */
.features-section { padding: 80px 0; background: #F5F7FA; }
.features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }
.feature-card {
    background: white; border-radius: 18px; padding: 40px 28px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.07);
    transition: all 0.3s; border: 1px solid transparent;
}
.feature-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 18px 44px rgba(0,0,0,0.12);
    border-color: #E0E7EF;
}
.feature-icon {
    width: 80px; height: 80px; background: #EEF2FF;
    border-radius: 50%; display: flex; align-items: center;
    justify-content: center; margin: 0 auto 22px;
    color: #003399; font-size: 2rem; transition: all 0.3s;
}
.feature-card:hover .feature-icon { background: #003399; color: white; }
/* LISIBLE : titres et textes sur fond blanc */
.feature-card h3 { font-family: 'Playfair Display', serif; color: #1E2A35; margin-bottom: 12px; font-size: 1.2rem; }
.feature-card p  { color: #3D4A5C; line-height: 1.7; font-size: 0.95rem; margin: 0; }

/* ══ PROGRAMME ═════════════════════════════════════════════════ */
.programme-section { padding: 80px 0; background: white; }
.programme-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 26px; }
.programme-card {
    background: #F5F7FA; border-radius: 18px; padding: 32px;
    border-left: 4px solid #003399; transition: all 0.3s;
}
.programme-card:hover { background: white; box-shadow: 0 8px 32px rgba(0,0,0,0.09); }
.programme-time {
    display: inline-block; background: #003399; color: white;
    padding: 5px 16px; border-radius: 20px;
    font-size: 0.82rem; font-weight: 700; margin-bottom: 14px;
}
/* LISIBLE : titre de journée sur fond clair */
.programme-card h3 { font-family: 'Playfair Display', serif; color: #1E2A35; margin-bottom: 16px; font-size: 1.1rem; }
.prog-list { list-style: none; padding: 0; margin: 0; }
.prog-list li {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 0;
    color: #1E2A35;         /* LISIBLE : texte foncé sur fond gris clair */
    font-size: 0.93rem;
    border-bottom: 1px solid #E8ECF0;
}
.prog-list li:last-child { border-bottom: none; }
.prog-list i { color: #003399; font-size: 0.82rem; width: 14px; flex-shrink: 0; }

/* ══ EXPOSANTS ═════════════════════════════════════════════════ */
.exposants-section { padding: 80px 0; background: #F5F7FA; }
.exposants-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px; }
.exposant-card {
    background: white; border-radius: 16px; padding: 28px 18px;
    text-align: center;
    box-shadow: 0 4px 16px rgba(0,0,0,0.07);
    transition: all 0.3s; border: 1px solid transparent;
}
.exposant-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 14px 34px rgba(0,0,0,0.12);
    border-color: #003399;
}
.exposant-logo {
    width: 70px; height: 70px; background: #EEF2FF;
    border-radius: 50%; margin: 0 auto 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.7rem; color: #003399; transition: all 0.3s;
}
.exposant-card:hover .exposant-logo { background: #003399; color: white; }
/* LISIBLE : textes sur fond blanc */
.exposant-card h4 { color: #1E2A35; margin-bottom: 4px; font-size: 0.93rem; font-weight: 700; }
.exposant-card p  { color: #3D4A5C; font-size: 0.8rem; margin: 0; }

/* CTA devenir exposant */
.exposant-cta {
    text-align: center; margin-top: 44px;
    background: white; border-radius: 18px;
    padding: 36px 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.07);
}
.exposant-cta h3 {
    font-family: 'Playfair Display', serif;
    color: #1E2A35; font-size: 1.3rem; margin-bottom: 10px;
}
.exposant-cta p { color: #374151; margin-bottom: 20px; font-size: 0.95rem; }
.btn-blue {
    background: #003399; color: white;
    padding: 13px 30px; border-radius: 30px;
    font-size: 0.97rem; font-weight: 700;
    text-decoration: none;
    display: inline-flex; align-items: center; gap: 9px;
    box-shadow: 0 4px 16px rgba(0,51,153,0.25);
    transition: all 0.25s;
}
.btn-blue:hover {
    background: #002277; color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,51,153,0.35);
}

/* ══ RESPONSIVE ════════════════════════════════════════════════ */
@media (max-width: 1100px) {
    .exposants-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 900px) {
    .presentation-grid { grid-template-columns: 1fr; }
    .features-grid     { grid-template-columns: 1fr; }
    .pass-card.featured { transform: none; }
    .pass-card.featured:hover { transform: translateY(-4px); }
}
@media (max-width: 768px) {
    .programme-grid { grid-template-columns: 1fr; }
    .exposants-grid { grid-template-columns: repeat(2, 1fr); }
    .foire-meta-row { gap: 10px; }
    .hero-cta-row   { flex-direction: column; align-items: center; }
    }
@media (max-width: 480px) {
    .exposants-grid { grid-template-columns: 1fr 1fr; }
}
</style>

<!-- ════════════════════════════════════════════════════════════
     HERO
     ════════════════════════════════════════════════════════════ -->
<div class="foire-hero">
    <div class="container">
        <div class="foire-hero-inner" data-aos="fade-up">
            <span class="foire-tag">GSCC — Événement annuel phare</span>
            <h1>Grande Foire <em>Annuelle <?= $annee ?></em></h1>
            <p class="foire-hero-sub">
                Le plus grand rassemblement solidaire de l'année — trois jours de festivités,
                de partage et d'action pour la lutte contre le cancer en Haïti.
            </p>
            <div class="foire-meta-row">
                <span class="foire-meta-pill"><i class="far fa-calendar-alt"></i> 15 – 17 novembre <?= $annee ?></span>
                <span class="foire-meta-pill"><i class="fas fa-map-marker-alt"></i> Place Saint-Pierre, Port-au-Prince</span>
                <span class="foire-meta-pill"><i class="far fa-clock"></i> 10h – 20h (nocturne samedi)</span>
            </div>
            <div class="hero-cta-row">
                <!-- Acheter un billet → contact.php -->
                <a href="contact.php?sujet=billet-foire" class="btn-rose">
                    <i class="fas fa-ticket-alt"></i> Obtenir un billet
                </a>
                <a href="#programme" class="btn-ghost-white">
                    <i class="fas fa-list-alt"></i> Voir le programme
                </a>
            </div>
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════════════════════════
     PRÉSENTATION
     ════════════════════════════════════════════════════════════ -->
<section class="presentation-section">
    <div class="container">
        <div class="presentation-grid">
            <div class="presentation-image" data-aos="fade-right">
                <img src="uploads/galerie/IMG_3945.jpg" alt="Grande Foire Annuelle GSCC" loading="lazy">
            </div>
            <div class="presentation-text" data-aos="fade-left">
                <span class="pres-eyebrow">À propos de l'événement</span>
                <h2>Un événement unique en Haïti</h2>
                <div class="pres-line"></div>
                <p>
                    La Grande Foire Annuelle du GSCC est devenue au fil des années le rendez-vous
                    incontournable de la solidarité en Haïti. Chaque année, des milliers de visiteurs
                    se rassemblent pour soutenir notre cause et célébrer la vie.
                </p>
                <p>
                    Au programme : stands d'artisanat local, animations culturelles, conférences
                    santé, espace restauration, jeux pour enfants, et bien plus encore. Tous les
                    bénéfices sont reversés à nos programmes d'aide aux patients.
                </p>
                <ul class="info-list">
                    <li><i class="far fa-calendar-alt"></i> <span><strong>Date :</strong> 15 – 17 novembre <?= $annee ?></span></li>
                    <li><i class="fas fa-map-marker-alt"></i> <span><strong>Lieu :</strong> Place Saint-Pierre, Port-au-Prince</span></li>
                    <li><i class="far fa-clock"></i> <span><strong>Horaires :</strong> 10h – 20h (samedi nocturne jusqu'à 23h)</span></li>
                    <li><i class="fas fa-ticket-alt"></i> <span><strong>Entrée :</strong> Sur billet — contactez le GSCC pour réserver</span></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════
     POURQUOI PARTICIPER
     ════════════════════════════════════════════════════════════ -->
<section class="features-section">
    <div class="container">
        <div class="sec-head" data-aos="fade-up">
            <span class="sec-eyebrow">Raisons de nous rejoindre</span>
            <h2>Pourquoi participer ?</h2>
            <div class="sec-rule"></div>
        </div>
        <div class="features-grid">
            <div class="feature-card" data-aos="fade-up">
                <div class="feature-icon"><i class="fas fa-hand-holding-heart"></i></div>
                <h3>Agir pour la cause</h3>
                <p>Chaque achat, chaque participation contribue directement à financer nos actions de soutien aux patients atteints de cancer.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-icon"><i class="fas fa-gift"></i></div>
                <h3>Découvertes & bons plans</h3>
                <p>Plus de 50 exposants proposent artisanat, produits locaux, gastronomie haïtienne et créations uniques introuvables ailleurs.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-icon"><i class="fas fa-users"></i></div>
                <h3>Moment de partage</h3>
                <p>Une occasion unique de rencontrer notre communauté, d'échanger avec des personnes engagées et de célébrer ensemble.</p>
            </div>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════
     PROGRAMME
     ════════════════════════════════════════════════════════════ -->
<section id="programme" class="programme-section">
    <div class="container">
        <div class="sec-head" data-aos="fade-up">
            <span class="sec-eyebrow">Les 3 jours</span>
            <h2>Programme <?= $annee ?></h2>
            <div class="sec-rule"></div>
        </div>
        <div class="programme-grid">
            <div class="programme-card" data-aos="fade-right">
                <span class="programme-time">Vendredi 15 novembre</span>
                <h3>Journée d'ouverture</h3>
                <ul class="prog-list">
                    <li><i class="fas fa-clock"></i> 10h00 — Cérémonie d'ouverture officielle</li>
                    <li><i class="fas fa-clock"></i> 14h00 — Conférence "Prévention du cancer"</li>
                    <li><i class="fas fa-clock"></i> 18h00 — Concert d'ouverture</li>
                </ul>
            </div>
            <div class="programme-card" data-aos="fade-left">
                <span class="programme-time">Samedi 16 novembre</span>
                <h3>Journée familiale</h3>
                <ul class="prog-list">
                    <li><i class="fas fa-clock"></i> 11h00 — Ateliers pour enfants</li>
                    <li><i class="fas fa-clock"></i> 15h00 — Défilé de mode solidaire</li>
                    <li><i class="fas fa-clock"></i> 20h00 — Soirée de gala</li>
                </ul>
            </div>
            <div class="programme-card" data-aos="fade-right">
                <span class="programme-time">Dimanche 17 novembre</span>
                <h3>Journée de clôture</h3>
                <ul class="prog-list">
                    <li><i class="fas fa-clock"></i> 10h00 — Messe d'action de grâce</li>
                    <li><i class="fas fa-clock"></i> 14h00 — Tombola géante</li>
                    <li><i class="fas fa-clock"></i> 18h00 — Cérémonie de clôture</li>
                </ul>
            </div>
            <div class="programme-card" data-aos="fade-left">
                <span class="programme-time" style="background:#D94F7A;">Tout le week-end</span>
                <h3>Animations continues</h3>
                <ul class="prog-list">
                    <li><i class="fas fa-check"></i> Village des exposants (50+ stands)</li>
                    <li><i class="fas fa-check"></i> Espace restauration haïtienne</li>
                    <li><i class="fas fa-check"></i> Jeux et animations pour tous</li>
                    <li><i class="fas fa-check"></i> Consultations santé gratuites</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════
     EXPOSANTS
     ════════════════════════════════════════════════════════════ -->
<section class="exposants-section">
    <div class="container">
        <div class="sec-head" data-aos="fade-up">
            <span class="sec-eyebrow">Ils seront présents</span>
            <h2>Nos exposants <?= $annee ?></h2>
            <div class="sec-rule"></div>
        </div>
        <div class="exposants-grid">
            <?php
            $exposants = [
                ['icon' => 'fa-store',     'nom' => 'Artisanat d\'Haïti',     'cat' => 'Produits artisanaux'],
                ['icon' => 'fa-utensils',  'nom' => 'Saveurs Créoles',        'cat' => 'Gastronomie locale'],
                ['icon' => 'fa-tshirt',    'nom' => 'Mode Éthique',           'cat' => 'Couture & accessoires'],
                ['icon' => 'fa-book',      'nom' => 'Librairie des Lumières', 'cat' => 'Livres & éducation'],
                ['icon' => 'fa-leaf',      'nom' => 'Bio Haïti',              'cat' => 'Produits naturels'],
                ['icon' => 'fa-heartbeat', 'nom' => 'Santé Plus',             'cat' => 'Matériel médical'],
                ['icon' => 'fa-palette',   'nom' => 'Artistes en Herbe',      'cat' => 'Œuvres d\'art'],
                ['icon' => 'fa-gem',       'nom' => 'Créations Précieuses',   'cat' => 'Bijoux artisanaux'],
            ];
            foreach ($exposants as $i => $ex):
            ?>
            <div class="exposant-card" data-aos="zoom-in" data-aos-delay="<?= ($i % 4) * 60 ?>">
                <div class="exposant-logo"><i class="fas <?= $ex['icon'] ?>"></i></div>
                <h4><?= htmlspecialchars($ex['nom']) ?></h4>
                <p><?= htmlspecialchars($ex['cat']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- CTA devenir exposant → contact.php -->
        <div class="exposant-cta" data-aos="fade-up">
            <h3>Vous souhaitez exposer à la foire ?</h3>
            <p>Rejoignez nos exposants, faites connaître votre activité et soutenez la cause. Contactez-nous pour réserver votre stand.</p>
            <a href="contact.php?sujet=devenir-exposant" class="btn-blue">
                <i class="fas fa-store-alt"></i> Devenir exposant
            </a>
        </div>
    </div>
</section>


<?php require_once 'templates/footer.php'; ?>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script src="assets/js/main.js"></script>