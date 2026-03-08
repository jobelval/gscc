<?php
// formations.php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title       = 'Formations';
$page_description = 'Programmes de formation pour les professionnels de santé et le grand public – GSCC Haïti.';

// ── Filtre actif (sécurisé) ──────────────────────────────────────
$types_valides = ['tous', 'professionnels', 'public', 'beneficiaires'];
$type_actif    = (isset($_GET['type']) && in_array($_GET['type'], $types_valides))
                 ? $_GET['type'] : 'tous';

// ── Données formations (toutes gratuites) ───────────────────────
$formations = [
    [
        'id'          => 1,
        'titre'       => 'Prise en charge des patients atteints de cancer',
        'type'        => 'professionnels',
        'badge'       => 'Professionnels',
        'date'        => '15 – 17 Nov 2024',
        'lieu'        => 'Port-au-Prince',
        'en_ligne'    => false,
        'duree'       => '3 jours',
        'places'      => 20,
        'places_rest' => 7,
        'description' => 'Formation intensive pour les médecins et infirmiers sur les dernières avancées dans la prise en charge des patients atteints de cancer.',
        'image'       => 'images/formations/formation-1.jpg',
        'certificat'  => true,
    ],
    [
        'id'          => 2,
        'titre'       => 'Atelier nutrition et prévention du cancer',
        'type'        => 'public',
        'badge'       => 'Grand public',
        'date'        => '5 Déc 2024',
        'lieu'        => 'En ligne',
        'en_ligne'    => true,
        'duree'       => '1 journée',
        'places'      => 50,
        'places_rest' => 32,
        'description' => 'Apprenez les bases d\'une alimentation saine pour réduire les risques de cancer. Atelier pratique animé par une nutritionniste certifiée.',
        'image'       => 'images/formations/formation-2.jpg',
        'certificat'  => false,
    ],
    [
        'id'          => 3,
        'titre'       => 'Gestion du stress et soutien psychologique',
        'type'        => 'beneficiaires',
        'badge'       => 'Bénéficiaires',
        'date'        => 'Tous les mercredis',
        'lieu'        => 'Centre GSCC',
        'en_ligne'    => false,
        'duree'       => '2 heures',
        'places'      => 15,
        'places_rest' => 4,
        'description' => 'Ateliers hebdomadaires pour les patients et leurs proches sur la gestion du stress et le soutien psychologique.',
        'image'       => 'images/formations/formation-3.jpg',
        'certificat'  => false,
    ],
    [
        'id'          => 4,
        'titre'       => 'Soins palliatifs et accompagnement en fin de vie',
        'type'        => 'professionnels',
        'badge'       => 'Professionnels',
        'date'        => '10 – 12 Jan 2025',
        'lieu'        => 'Cap-Haïtien',
        'en_ligne'    => false,
        'duree'       => '3 jours',
        'places'      => 18,
        'places_rest' => 10,
        'description' => 'Formation spécialisée sur les soins palliatifs et l\'accompagnement humain des patients en fin de vie.',
        'image'       => 'images/formations/formation-4.jpg',
        'certificat'  => true,
    ],
    [
        'id'          => 5,
        'titre'       => 'Auto-palpation : les bons gestes',
        'type'        => 'public',
        'badge'       => 'Grand public',
        'date'        => '20 Oct 2024',
        'lieu'        => 'Pétion-Ville',
        'en_ligne'    => false,
        'duree'       => '3 heures',
        'places'      => 30,
        'places_rest' => 18,
        'description' => 'Apprenez les techniques d\'auto-palpation pour détecter précocement les anomalies. Formation pratique avec mannequins pédagogiques.',
        'image'       => 'images/formations/formation-5.jpg',
        'certificat'  => false,
    ],
    [
        'id'          => 6,
        'titre'       => 'Groupe de parole pour proches de patients',
        'type'        => 'beneficiaires',
        'badge'       => 'Bénéficiaires',
        'date'        => '1er et 3e jeudi du mois',
        'lieu'        => 'En ligne',
        'en_ligne'    => true,
        'duree'       => '1h30',
        'places'      => 12,
        'places_rest' => 5,
        'description' => 'Espace d\'échange et de soutien pour les proches de patients, animé par une psychologue clinicienne agréée.',
        'image'       => 'images/formations/formation-6.jpg',
        'certificat'  => false,
    ],
    [
        'id'          => 7,
        'titre'       => 'Communication thérapeutique avec les patients',
        'type'        => 'professionnels',
        'badge'       => 'Professionnels',
        'date'        => '22 – 23 Fév 2025',
        'lieu'        => 'Port-au-Prince',
        'en_ligne'    => false,
        'duree'       => '2 jours',
        'places'      => 25,
        'places_rest' => 14,
        'description' => 'Développez vos compétences relationnelles pour mieux accompagner les patients et leurs familles tout au long du parcours de soins.',
        'image'       => 'images/formations/formation-7.jpg',
        'certificat'  => true,
    ],
    [
        'id'          => 8,
        'titre'       => 'Dépistage précoce : sensibilisation communautaire',
        'type'        => 'public',
        'badge'       => 'Grand public',
        'date'        => '8 Mar 2025',
        'lieu'        => 'En ligne',
        'en_ligne'    => true,
        'duree'       => '2 heures',
        'places'      => 100,
        'places_rest' => 67,
        'description' => 'Webinaire pour comprendre l\'importance du dépistage précoce et apprendre à mobiliser votre communauté.',
        'image'       => 'images/formations/formation-8.jpg',
        'certificat'  => false,
    ],
];

// Filtrage
$formations_affichees = ($type_actif === 'tous')
    ? $formations
    : array_values(array_filter($formations, fn($f) => $f['type'] === $type_actif));

// Compteurs pour les onglets
$comptes = [
    'tous'           => count($formations),
    'professionnels' => count(array_filter($formations, fn($f) => $f['type'] === 'professionnels')),
    'public'         => count(array_filter($formations, fn($f) => $f['type'] === 'public')),
    'beneficiaires'  => count(array_filter($formations, fn($f) => $f['type'] === 'beneficiaires')),
];

// Stats hero
$total_certificat = count(array_filter($formations, fn($f) => $f['certificat']));

require_once 'templates/header.php';
?>

<style>
/* ─────────────────────────────────────────────────────────────
   PAGE FORMATIONS — styles spécifiques
   ───────────────────────────────────────────────────────────── */

/* Offset pour header sticky */
#formations-anchor { scroll-margin-top: 110px; }

/* ── Hero ──────────────────────────────────────────────────── */
.page-hero {
    background: linear-gradient(135deg, #003399 0%, #001a66 60%, #1a1a2e 100%);
    color: white; padding: 80px 0 70px;
    text-align: center; position: relative; overflow: hidden;
}
.page-hero::before {
    content: ''; position: absolute;
    top: -40%; right: -5%;
    width: 520px; height: 520px;
    background: radial-gradient(circle, rgba(217,79,122,0.18), transparent 70%);
    pointer-events: none;
}
.page-hero::after {
    content: ''; position: absolute;
    bottom: -30%; left: -5%;
    width: 380px; height: 380px;
    background: radial-gradient(circle, rgba(76,175,80,0.1), transparent 70%);
    pointer-events: none;
}
.page-hero-inner { position: relative; z-index: 1; }
.page-hero-tag {
    display: inline-block;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    color: white; font-size: 0.75rem; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    padding: 5px 18px; border-radius: 30px; margin-bottom: 18px;
    font-family: 'DM Sans', 'Inter', sans-serif;
}
.page-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 5vw, 3.2rem);
    color: white; margin-bottom: 14px;
}
.page-hero-desc {
    font-size: 1.1rem; color: rgba(255,255,255,0.82);
    max-width: 580px; margin: 0 auto 40px; line-height: 1.75;
}
.hero-stats {
    display: inline-flex; gap: 0;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.14);
    border-radius: 16px; overflow: hidden;
}
.hero-stat {
    padding: 18px 36px; text-align: center;
    border-right: 1px solid rgba(255,255,255,0.12);
}
.hero-stat:last-child { border-right: none; }
.hero-stat-number {
    font-family: 'Playfair Display', serif;
    font-size: 2rem; font-weight: 700; color: white; line-height: 1;
}
.hero-stat-label {
    font-size: 0.78rem; color: rgba(255,255,255,0.6);
    margin-top: 4px; white-space: nowrap;
}

/* ── Section principale ─────────────────────────────────────── */
.formations-section { padding: 70px 0; background: #F5F7FA; }

/* ── Tabs filtre ────────────────────────────────────────────── */
.formations-tabs {
    display: flex; justify-content: center;
    gap: 10px; margin-bottom: 12px; flex-wrap: wrap;
}
.tab-btn {
    padding: 10px 22px; border-radius: 30px;
    background: white; color: #1E2A35;
    text-decoration: none; font-weight: 600; font-size: 0.9rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    transition: all 0.25s; border: 2px solid transparent;
    display: inline-flex; align-items: center; gap: 7px;
    font-family: 'DM Sans', 'Inter', sans-serif;
}
.tab-btn:hover   { border-color: #003399; color: #003399; transform: translateY(-2px); }
.tab-btn.active  { background: #003399; color: white; border-color: #003399;
                   box-shadow: 0 4px 16px rgba(0,51,153,0.3); }
.tab-count {
    font-size: 0.75rem; font-weight: 700;
    padding: 1px 8px; border-radius: 10px;
    background: #EEF2FF; color: #003399;
}
.tab-btn.active .tab-count { background: rgba(255,255,255,0.25); color: white; }

/* Info résultats */
.results-info {
    text-align: center; color: #6B7A8D;
    font-size: 0.9rem; margin-bottom: 36px;
}
.results-info strong { color: #003399; font-weight: 700; }

/* ── Grille ─────────────────────────────────────────────────── */
.formations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 28px;
}
.no-results {
    grid-column: 1 / -1; text-align: center; padding: 60px;
    background: white; border-radius: 16px;
    color: #6B7A8D;
}
.no-results i { font-size: 2.5rem; color: #E0E7EF; display: block; margin-bottom: 14px; }

/* ── Carte formation ────────────────────────────────────────── */
.formation-card {
    background: white; border-radius: 18px; overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.07);
    transition: all 0.3s ease;
    display: flex; flex-direction: column;
    border: 1px solid transparent;
}
.formation-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 18px 44px rgba(0,0,0,0.13);
    border-color: #E0E7EF;
}

/* Image */
.formation-image { height: 200px; position: relative; overflow: hidden; flex-shrink: 0; }
.formation-image img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform 0.5s ease;
}
.formation-card:hover .formation-image img { transform: scale(1.07); }

/* Badges sur l'image */
.badge-type {
    position: absolute; top: 13px; left: 13px;
    color: white; padding: 4px 12px;
    border-radius: 20px; font-size: 11px; font-weight: 700;
    letter-spacing: 0.5px; text-transform: uppercase;
    font-family: 'DM Sans', 'Inter', sans-serif;
}
.badge-pro   { background: #003399; }
.badge-pub   { background: #D94F7A; }
.badge-benef { background: #4CAF50; }

.badge-cert {
    position: absolute; top: 13px; right: 13px;
    background: #C9933A; color: white;
    padding: 4px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 700;
    display: flex; align-items: center; gap: 4px;
}
.badge-online {
    position: absolute; bottom: 12px; left: 13px;
    background: rgba(0,0,0,0.55); backdrop-filter: blur(4px);
    color: white; padding: 3px 10px; border-radius: 10px;
    font-size: 11px; font-weight: 600;
    display: flex; align-items: center; gap: 5px;
}
.online-dot {
    width: 7px; height: 7px;
    background: #4CAF50; border-radius: 50%; flex-shrink: 0;
}

/* Contenu */
.formation-content { padding: 24px; flex: 1; display: flex; flex-direction: column; }
.formation-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.08rem; color: #1E2A35;
    margin-bottom: 12px; line-height: 1.45;
}
.formation-meta {
    display: flex; flex-wrap: wrap; gap: 10px;
    margin-bottom: 14px; font-size: 0.82rem; color: #6B7A8D;
}
.formation-meta span { display: inline-flex; align-items: center; gap: 5px; }
.formation-meta i { color: #003399; font-size: 11px; }
.formation-description {
    color: #6B7A8D; font-size: 0.9rem;
    line-height: 1.65; margin-bottom: 16px; flex: 1;
}

/* Barre de places */
.places-row {
    display: flex; align-items: center; gap: 8px;
    font-size: 0.8rem; color: #6B7A8D; margin-bottom: 16px;
}
.places-bar-wrap {
    flex: 1; height: 5px; background: #E8ECF0;
    border-radius: 3px; overflow: hidden;
}
.places-bar-fill { height: 100%; border-radius: 3px; background: #003399; }
.places-bar-fill.urgent { background: #D94F7A; }
.places-urgency { color: #D94F7A; font-weight: 700; font-size: 0.78rem; }

/* Pied de carte */
.formation-footer {
    display: flex; justify-content: space-between; align-items: center;
    padding-top: 16px; border-top: 1px solid #F0F2F5; margin-top: auto;
}
.formation-price {
    font-family: 'Playfair Display', serif;
    font-size: 1.35rem; font-weight: 700; color: #003399;
}
.formation-price.free {
    font-family: 'DM Sans', 'Inter', sans-serif;
    font-size: 1rem; color: #4CAF50; font-weight: 700;
    display: flex; align-items: center; gap: 6px;
}
.formation-price .curr { font-size: 0.8rem; font-weight: 500; opacity: 0.65; }

.btn-formation {
    background: #003399; color: white;
    padding: 9px 20px; border-radius: 25px;
    text-decoration: none; font-size: 0.87rem; font-weight: 600;
    transition: all 0.25s; display: inline-flex; align-items: center; gap: 6px;
    font-family: 'DM Sans', 'Inter', sans-serif;
}
.btn-formation:hover {
    background: #D94F7A; color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(217,79,122,0.35);
}

/* ── Calendrier ─────────────────────────────────────────────── */
.calendar-section {
    margin-top: 70px; background: white;
    border-radius: 20px; padding: 40px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
.calendar-header {
    display: flex; justify-content: space-between;
    align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;
}
.calendar-title {
    font-family: 'Playfair Display', serif;
    color: #003399; font-size: 1.4rem; margin: 0;
}
.cal-nav-wrap { display: flex; gap: 8px; }
.btn-nav {
    width: 38px; height: 38px; background: #EEF2FF;
    border: none; border-radius: 9px; color: #003399;
    cursor: pointer; font-size: 0.88rem; transition: all 0.2s;
    display: flex; align-items: center; justify-content: center;
}
.btn-nav:hover { background: #003399; color: white; }

.cal-weekdays {
    display: grid; grid-template-columns: repeat(7,1fr);
    gap: 6px; margin-bottom: 6px;
}
.cal-wday {
    text-align: center; font-size: 0.72rem; font-weight: 700;
    color: #6B7A8D; text-transform: uppercase;
    letter-spacing: 0.5px; padding: 6px 0;
}
.cal-grid { display: grid; grid-template-columns: repeat(7,1fr); gap: 6px; }
.cal-day {
    aspect-ratio: 1; background: #F5F7FA; border-radius: 10px;
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; position: relative;
    border: 2px solid transparent; font-size: 0.88rem;
    font-weight: 600; color: #1E2A35;
}
.cal-day.empty { background: transparent; border: none; }
.cal-day.has-event {
    background: #EEF2FF; border-color: #003399;
    cursor: pointer; transition: all 0.2s;
}
.cal-day.has-event:hover { background: #003399; color: white; }
.cal-day.has-event:hover .cal-dot { background: white; }
.cal-dot {
    position: absolute; bottom: 5px; right: 5px;
    width: 6px; height: 6px; background: #D94F7A; border-radius: 50%;
}
.cal-day.today { background: #FDE8EF; border-color: #D94F7A; color: #D94F7A; }

.cal-legend {
    display: flex; gap: 22px; margin-top: 18px; flex-wrap: wrap;
}
.cal-legend-item {
    display: flex; align-items: center; gap: 7px;
    font-size: 0.83rem; color: #6B7A8D;
}
.cal-legend-swatch {
    width: 14px; height: 14px; border-radius: 4px; flex-shrink: 0;
}

/* ── CTA bas de page ────────────────────────────────────────── */
.formations-cta {
    margin-top: 70px;
    background: linear-gradient(135deg, #003399 0%, #001a66 100%);
    border-radius: 20px; padding: 52px 40px; text-align: center; color: white;
}
.formations-cta h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem; color: white; margin-bottom: 14px;
}
.formations-cta p {
    color: rgba(255,255,255,0.8); margin-bottom: 30px; font-size: 1.05rem;
    max-width: 540px; margin-left: auto; margin-right: auto; line-height: 1.7;
}
.cta-btn-row { display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; }
.btn-cta-rose {
    background: #D94F7A; color: white;
    padding: 13px 30px; border-radius: 30px;
    font-weight: 700; font-size: 0.97rem; text-decoration: none;
    display: inline-flex; align-items: center; gap: 8px;
    transition: all 0.25s;
}
.btn-cta-rose:hover {
    background: #C0306A; color: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(217,79,122,0.4);
}
.btn-cta-ghost {
    background: transparent; color: white;
    padding: 13px 30px; border-radius: 30px;
    font-weight: 600; font-size: 0.97rem; text-decoration: none;
    border: 2px solid rgba(255,255,255,0.35);
    display: inline-flex; align-items: center; gap: 8px;
    transition: all 0.25s;
}
.btn-cta-ghost:hover {
    border-color: white; background: rgba(255,255,255,0.08); color: white;
}

/* ── Responsive ─────────────────────────────────────────────── */
@media (max-width: 900px) {
    .hero-stats { display: flex; flex-wrap: wrap; border-radius: 12px; }
    .hero-stat  { padding: 14px 22px; border-right: none; border-bottom: 1px solid rgba(255,255,255,0.12); }
    .hero-stat:last-child { border-bottom: none; }
    .formations-grid { grid-template-columns: repeat(auto-fill, minmax(280px,1fr)); }
}
@media (max-width: 768px) {
    .formations-tabs { gap: 8px; }
    .tab-btn { padding: 8px 16px; font-size: 0.84rem; }
    .calendar-section { padding: 22px 14px; }
    .cal-grid, .cal-weekdays { gap: 4px; }
    .cal-day { font-size: 0.78rem; }
    .formations-cta { padding: 36px 20px; }
}
@media (max-width: 480px) {
    .formations-grid { grid-template-columns: 1fr; }
    .hero-stats { width: 100%; }
    .hero-stat { flex: 1 1 50%; }
}
</style>

<!-- ════════════════════════════════════════════════════════════
     HERO
     ════════════════════════════════════════════════════════════ -->
<div class="page-hero">
    <div class="container">
        <div class="page-hero-inner" data-aos="fade-up">
            <span class="page-hero-tag">GSCC — Renforcement des capacités</span>
            <h1>Formations</h1>
            <p class="page-hero-desc">
                Renforcez vos compétences avec nos programmes de formation en oncologie,
                prévention du cancer et soutien psychologique.
            </p>
            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="hero-stat-number"><?= count($formations) ?></div>
                    <div class="hero-stat-label">Formations disponibles</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-number">100%</div>
                    <div class="hero-stat-label">Gratuites</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-number"><?= $total_certificat ?></div>
                    <div class="hero-stat-label">Avec certificat</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════
     SECTION PRINCIPALE
     ════════════════════════════════════════════════════════════ -->
<section id="formations-anchor" class="formations-section">
    <div class="container">

        <!-- TABS FILTRE avec compteurs fonctionnels -->
        <?php
        $tabs = [
            'tous'           => ['label' => 'Toutes les formations', 'icon' => 'fa-th-large'],
            'professionnels' => ['label' => 'Professionnels de santé','icon' => 'fa-user-md'],
            'public'         => ['label' => 'Grand public',           'icon' => 'fa-users'],
            'beneficiaires'  => ['label' => 'Bénéficiaires',          'icon' => 'fa-hand-holding-heart'],
        ];
        ?>
        <div class="formations-tabs" data-aos="fade-up">
            <?php foreach ($tabs as $key => $tab): ?>
            <a href="?type=<?= $key ?>"
               class="tab-btn <?= $type_actif === $key ? 'active' : '' ?>">
                <i class="fas <?= $tab['icon'] ?>"></i>
                <?= $tab['label'] ?>
                <span class="tab-count"><?= $comptes[$key] ?></span>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Résultats -->
        <p class="results-info">
            <strong><?= count($formations_affichees) ?></strong>
            formation<?= count($formations_affichees) > 1 ? 's' : '' ?>
            <?= $type_actif !== 'tous' ? 'dans cette catégorie' : 'disponibles' ?>
        </p>

        <!-- GRILLE FORMATIONS -->
        <div class="formations-grid">
            <?php if (empty($formations_affichees)): ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <p>Aucune formation disponible dans cette catégorie pour le moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($formations_affichees as $i => $f):
                    // Classe badge type
                    $badge_css = match($f['type']) {
                        'professionnels' => 'badge-pro',
                        'public'         => 'badge-pub',
                        'beneficiaires'  => 'badge-benef',
                        default          => 'badge-pub',
                    };
                    // Calcul taux d'occupation
                    $places_prises = $f['places'] - $f['places_rest'];
                    $taux   = ($f['places'] > 0) ? round(($places_prises / $f['places']) * 100) : 0;
                    $urgent = ($f['places_rest'] <= 5);
                ?>
                <div class="formation-card" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 100 ?>">

                    <!-- Image + badges -->
                    <div class="formation-image">
                        <img src="<?= htmlspecialchars($f['image']) ?>"
                             onerror="this.src='https://picsum.photos/400/250?random=<?= $f['id'] ?>'"
                             alt="<?= htmlspecialchars($f['titre']) ?>" loading="lazy">

                        <span class="badge-type <?= $badge_css ?>">
                            <?= htmlspecialchars($f['badge']) ?>
                        </span>

                        <?php if ($f['certificat']): ?>
                        <span class="badge-cert">
                            <i class="fas fa-certificate"></i> Certificat
                        </span>
                        <?php endif; ?>

                        <?php if ($f['en_ligne']): ?>
                        <span class="badge-online">
                            <span class="online-dot"></span> En ligne
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Contenu -->
                    <div class="formation-content">
                        <h3 class="formation-title"><?= htmlspecialchars($f['titre']) ?></h3>

                        <div class="formation-meta">
                            <span><i class="far fa-calendar-alt"></i> <?= htmlspecialchars($f['date']) ?></span>
                            <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($f['lieu']) ?></span>
                            <span><i class="far fa-clock"></i> <?= htmlspecialchars($f['duree']) ?></span>
                        </div>

                        <p class="formation-description"><?= htmlspecialchars($f['description']) ?></p>

                        <!-- Barre de places disponibles -->
                        <div class="places-row">
                            <i class="fas fa-user-friends" style="color:#003399;font-size:11px;"></i>
                            <span><?= (int)$f['places_rest'] ?> place<?= $f['places_rest'] > 1 ? 's' : '' ?> restante<?= $f['places_rest'] > 1 ? 's' : '' ?></span>
                            <div class="places-bar-wrap">
                                <div class="places-bar-fill <?= $urgent ? 'urgent' : '' ?>"
                                     style="width:<?= $taux ?>%;"></div>
                            </div>
                            <?php if ($urgent): ?>
                                <span class="places-urgency">Quasi complet !</span>
                            <?php endif; ?>
                        </div>

                        <!-- Badge Gratuit -->
                        <div class="formation-footer">
                            <span class="formation-price free">
                                <i class="fas fa-check-circle"></i> Gratuit
                            </span>
                            <a href="contact.php?formation=<?= (int)$f['id'] ?>"
                               class="btn-formation">
                                <i class="fas fa-envelope"></i> Nous contacter
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ════ CTA BAS DE PAGE ═════════════════════════════════ -->
        <div class="formations-cta" data-aos="fade-up">
            <h3>Intéressé(e) par une formation ?</h3>
            <p>Toutes nos formations sont gratuites. Contactez-nous pour vous inscrire ou obtenir plus d'informations sur le programme qui vous intéresse.</p>
            <div class="cta-btn-row">
                <a href="contact.php" class="btn-cta-rose">
                    <i class="fas fa-envelope"></i> Nous contacter
                </a>
            </div>
        </div>

    </div>
</section>

<?php require_once 'templates/footer.php'; ?>

<!-- Scripts -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script src="assets/js/main.js"></script>