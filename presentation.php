<?php
// presentation.php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title       = 'Présentation';
$page_description = 'Découvrez notre mission, notre vision, notre histoire et notre équipe dévouée.';

try {
    $stmt   = $pdo->query("SELECT * FROM equipe WHERE est_actif = 1 ORDER BY ordre ASC");
    $equipe = $stmt->fetchAll();

    $stmt    = $pdo->query("SELECT * FROM valeurs WHERE est_actif = 1 ORDER BY ordre ASC");
    $valeurs = $stmt->fetchAll();

    if (empty($valeurs)) {
        $valeurs = [
            ['titre' => 'Confiance',       'description' => 'Nous construisons des relations basées sur la transparence et l\'intégrité avec nos bénéficiaires, partenaires et donateurs.', 'icone' => 'fa-heart',              'couleur' => '#003399'],
            ['titre' => 'Espoir',          'description' => 'Nous apportons de l\'espoir à travers des programmes de soutien et d\'accompagnement personnalisés.',                          'icone' => 'fa-dove',               'couleur' => '#D94F7A'],
            ['titre' => 'Solidarité',      'description' => 'Ensemble, nous sommes plus forts. La solidarité est au cœur de notre action et de notre engagement.',                          'icone' => 'fa-hand-holding-heart', 'couleur' => '#4CAF50'],
            ['titre' => 'Vie et guérison', 'description' => 'Nous œuvrons sans relâche pour offrir une meilleure qualité de vie et favoriser la guérison.',                                 'icone' => 'fa-leaf',               'couleur' => '#C9933A'],
        ];
    }
} catch (PDOException $e) {
    logError("Erreur presentation.php: " . $e->getMessage());
    $equipe  = [];
    $valeurs = [];
}

require_once 'templates/header.php';
?>

<style>
    /* ── Offset ancres : compense header fixe + top-bar ─────────── */
    #propos,
    #mission,
    #vision,
    #historique,
    #valeurs,
    #equipe {
        scroll-margin-top: 180px;
    }

    /* ── Hero ────────────────────────────────────────────────────── */
    .page-hero {
        background: linear-gradient(135deg, #003399 0%, #001a66 60%, #1a1a2e 100%);
        color: white;
        padding: 80px 0 70px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .page-hero::before {
        content: '';
        position: absolute;
        top: -40%;
        right: -10%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(217,79,122,0.18), transparent 70%);
        pointer-events: none;
    }

    .page-hero-content {
        position: relative;
        z-index: 1;
    }

    .page-hero-tag {
        display: inline-block;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.25);
        color: #ffffff;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        padding: 5px 16px;
        border-radius: 30px;
        margin-bottom: 18px;
        font-family: 'DM Sans', 'Inter', sans-serif;
        text-decoration: none;
    }

    .page-hero h1 {
        font-family: 'Playfair Display', serif;
        font-size: clamp(2rem, 5vw, 3.2rem);
        color: #ffffff;
        margin-bottom: 16px;
    }

    .page-hero-desc {
        font-size: 1.05rem;
        color: rgba(255,255,255,0.88);
        max-width: 640px;
        margin: 0 auto 32px;
        line-height: 1.8;
    }

    /* ── Navigation ancres dans le hero ─────────────────────────── */
    .anchor-nav {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .anchor-nav a {
        background: rgba(255,255,255,0.12);
        color: #ffffff;
        padding: 7px 20px;
        border-radius: 30px;
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
        border: 1px solid rgba(255,255,255,0.2);
        transition: all 0.2s;
        font-family: 'DM Sans', 'Inter', sans-serif;
    }

    .anchor-nav a:hover {
        background: #D94F7A;
        border-color: #D94F7A;
        color: #ffffff;
        transform: translateY(-2px);
    }

    /* ── En-têtes de section ─────────────────────────────────────── */
    .pres-section-header {
        text-align: center;
        margin-bottom: 60px;
    }

    .pres-section-tag {
        display: inline-block;
        background: #EEF2FF;
        color: #003399;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        padding: 5px 14px;
        border-radius: 30px;
        margin-bottom: 12px;
        font-family: 'DM Sans', 'Inter', sans-serif;
    }

    .pres-section-header h2 {
        font-family: 'Playfair Display', serif;
        font-size: 2.2rem;
        color: #003399;
        margin-bottom: 14px;
    }

    .pres-divider {
        width: 60px;
        height: 4px;
        background: linear-gradient(90deg, #003399, #D94F7A);
        border-radius: 2px;
        margin: 0 auto 16px;
    }

    .pres-section-sub {
        color: #4B5563;
        font-size: 1.05rem;
        max-width: 500px;
        margin: 0 auto;
    }

    /* ── Mission & Vision ────────────────────────────────────────── */
    .mission-vision {
        background: white;
        padding: 80px 0;
    }

    .mv-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }

    .mv-card {
        padding: 40px;
        border-radius: 20px;
        background: #f8f9fa;
        transition: all 0.3s ease;
        border-bottom: 4px solid transparent;
    }

    .mv-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }

    .mv-card.mission { border-bottom-color: #003399; }
    .mv-card.vision  { border-bottom-color: #4CAF50; }

    .mv-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 25px;
    }

    .mv-card.mission .mv-icon { background: rgba(0,51,153,0.08);  color: #003399; }
    .mv-card.vision  .mv-icon { background: rgba(76,175,80,0.08); color: #4CAF50; }

    .mv-icon i { font-size: 38px; }

    .mv-card h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
        margin-bottom: 18px;
        color: #1E2A35;
    }

    .mv-card p {
        color: #374151;
        line-height: 1.8;
        font-size: 1rem;
        margin-bottom: 16px;
    }

    .mv-quote {
        font-style: italic;
        color: #003399;
        font-size: 1rem;
        border-left: 3px solid #003399;
        padding-left: 14px;
        margin: 16px 0 0;
    }

    /* ── Statistiques ────────────────────────────────────────────── */
    .stats-section {
        background: linear-gradient(135deg, #003399 0%, #001a66 100%);
        padding: 60px 0;
        color: white;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        text-align: center;
    }

    .stat-item {
        padding: 20px;
        border-right: 1px solid rgba(255,255,255,0.1);
    }

    .stat-item:last-child { border-right: none; }

    .stat-number {
        font-family: 'Playfair Display', serif;
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 8px;
        color: white;
    }

    .stat-label {
        font-size: 0.95rem;
        color: rgba(255,255,255,0.88);
    }

    /* ── Timeline ────────────────────────────────────────────────── */
    .historique {
        padding: 80px 0;
        background: #f8f9fa;
    }

    .timeline {
        position: relative;
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px 0;
    }

    /* Barre verticale centrale */
    .timeline::before {
        content: '';
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, #003399, #D94F7A, #4CAF50);
        border-radius: 2px;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 50px;
        width: 100%;
        display: flex;
        justify-content: flex-end;
    }

    .timeline-item:nth-child(even) {
        justify-content: flex-start;
    }

    /* Boites : width 38% + marges pour laisser espace autour de la barre */
    .timeline-content {
        width: 38%;
        padding: 30px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        position: relative;
        transition: box-shadow 0.3s;
    }

    .timeline-content:hover {
        box-shadow: 0 10px 32px rgba(0,0,0,0.13);
    }

    /* odd = droite : margin-right crée l'espace entre la boite et la barre */
    .timeline-item:nth-child(odd) .timeline-content {
        margin-right: 7%;
    }

    /* even = gauche : margin-left crée l'espace entre la barre et la boite */
    .timeline-item:nth-child(even) .timeline-content {
        margin-left: 7%;
    }

    .timeline-dot {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        width: 20px;
        height: 20px;
        background: white;
        border: 4px solid #003399;
        border-radius: 50%;
        top: 30px;
        z-index: 1;
    }

    .timeline-year {
        position: absolute;
        top: -14px;
        left: 24px;
        background: #003399;
        color: white;
        padding: 4px 18px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 13px;
        font-family: 'DM Sans', 'Inter', sans-serif;
    }

    .timeline-content h3 {
        font-family: 'Playfair Display', serif;
        margin-top: 18px;
        margin-bottom: 10px;
        color: #1E2A35;
        font-size: 1.15rem;
    }

    .timeline-content p {
        color: #374151;
        line-height: 1.7;
        font-size: 0.95rem;
        margin: 0;
    }

    /* ── Valeurs ─────────────────────────────────────────────────── */
    .valeurs {
        padding: 80px 0;
        background: white;
    }

    .valeurs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 28px;
    }

    .valeur-card {
        background: #f8f9fa;
        padding: 40px 28px;
        border-radius: 16px;
        text-align: center;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }

    .valeur-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 40px rgba(0,0,0,0.1);
        background: white;
        border-color: #E8ECF0;
    }

    .valeur-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 22px;
        font-size: 2rem;
    }

    .valeur-card h3 {
        font-family: 'Playfair Display', serif;
        color: #1E2A35;
        margin-bottom: 12px;
        font-size: 1.3rem;
    }

    .valeur-card p {
        color: #374151;
        line-height: 1.7;
        margin: 0;
        font-size: 0.95rem;
    }

    /* ── Équipe ──────────────────────────────────────────────────── */
    .equipe {
        padding: 80px 0;
        background: #f8f9fa;
    }

    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 28px;
    }

    .team-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }

    .team-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.13);
    }

    .team-image {
        height: 300px;
        overflow: hidden;
    }

    .team-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .team-card:hover .team-image img {
        transform: scale(1.07);
    }

    .team-info {
        padding: 24px;
        text-align: center;
    }

    .team-info h3 {
        font-family: 'Playfair Display', serif;
        color: #1E2A35;
        margin-bottom: 4px;
        font-size: 1.15rem;
    }

    .team-info .fonction {
        color: #003399;
        font-weight: 600;
        margin-bottom: 12px;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .team-info .bio {
        color: #374151;
        font-size: 0.92rem;
        line-height: 1.6;
        margin-bottom: 18px;
    }

    .team-social {
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .team-social a {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #EEF2FF;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #003399;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.25s;
    }

    .team-social a:hover {
        background: #003399;
        color: white;
        transform: translateY(-3px);
    }

    /* ── Responsive ──────────────────────────────────────────────── */
    @media (max-width: 900px) {
        .mv-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .stat-item {
            border-right: none;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .timeline::before {
            left: 28px;
        }

        .timeline-item,
        .timeline-item:nth-child(even) {
            justify-content: flex-end;
        }

        .timeline-content {
            width: 85%;
            margin-left: 55px !important;
            margin-right: 0 !important;
        }

        .timeline-dot {
            left: 28px;
        }

        .anchor-nav a {
            font-size: 0.82rem;
            padding: 6px 14px;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .page-hero {
            padding: 60px 0 50px;
        }
    }
</style>

<!-- ══ HERO — id="propos" ══════════════════════════════════════ -->
<div class="page-hero" id="propos">
    <div class="container">
        <div class="page-hero-content" data-aos="fade-up">
            <a href="#propos" class="page-hero-tag">À propos du GSCC</a>
            <h1>Qui sommes-nous&nbsp;?</h1>
            <p class="page-hero-desc">Le Groupe de Support Contre le Cancer (GSCC) est une organisation à but non lucratif fondée en 1999 par un groupe de dames, qui, ayant vécu le cancer personnellement ou à travers un proche, ont décidé de donner gracieusement de leur temps et de partager leurs expériences avec les personnes atteintes du cancer. Reconnu par le Ministère des Affaires Sociales et du Travail, le GSCC travaille depuis sa fondation à l'amélioration du sort des personnes atteintes de cancer, et à la sensibilisation de la population sur la maladie du cancer, sa prévention et son dépistage.</p>
            <nav class="anchor-nav" aria-label="Navigation rapide">
                <a href="#mission">Mission</a>
                <a href="#vision">Vision</a>
                <a href="#historique">Historique</a>
                <a href="#equipe">Équipe</a>
                <a href="#valeurs">Valeurs</a>
            </nav>
        </div>
    </div>
</div>

<!-- ══ MISSION — id="mission" ══════════════════════════════════ -->
<section id="mission" class="mission-vision">
    <div class="container">
        <div class="mv-grid">

            <div class="mv-card mission" data-aos="fade-right">
                <div class="mv-icon"><i class="fas fa-bullseye"></i></div>
                <h3>Notre Mission</h3>
                <p>Offrir un soutien global aux personnes atteintes de cancer et à leurs familles, en leur fournissant une assistance financière, un accompagnement psychologique et des informations fiables pour améliorer leur qualité de vie et leurs chances de guérison.</p>
                <blockquote class="mv-quote">"Vivre pour Aimer, Vivre pour Aider, Vivre pour Partager, Vivre Intensément"</blockquote>
            </div>

            <div id="vision" class="mv-card vision" data-aos="fade-left">
                <div class="mv-icon"><i class="fas fa-eye"></i></div>
                <h3>Notre Vision</h3>
                <p>Un Haïti où chaque personne atteinte de cancer a accès à des soins de qualité, à un soutien adapté et à l'espoir d'une guérison. Nous travaillons pour un avenir où le cancer ne sera plus un obstacle à la vie, mais un combat que nous pouvons gagner ensemble.</p>
            </div>

        </div>
    </div>
</section>

<!-- ══ STATISTIQUES ════════════════════════════════════════════ -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item" data-aos="zoom-in" data-aos-delay="0">
                <div class="stat-number">20+</div>
                <div class="stat-label">Années d'engagement</div>
            </div>
            <div class="stat-item" data-aos="zoom-in" data-aos-delay="100">
                <div class="stat-number">500+</div>
                <div class="stat-label">Membres actifs</div>
            </div>
            <div class="stat-item" data-aos="zoom-in" data-aos-delay="200">
                <div class="stat-number">25+</div>
                <div class="stat-label">Projets réalisés</div>
            </div>
            <div class="stat-item" data-aos="zoom-in" data-aos-delay="300">
                <div class="stat-number">1 500+</div>
                <div class="stat-label">Personnes aidées</div>
            </div>
        </div>
    </div>
</section>

<!-- ══ HISTORIQUE — id="historique" ═══════════════════════════ -->
<section id="historique" class="historique">
    <div class="container">
        <div class="pres-section-header" data-aos="fade-up">
            <span class="pres-section-tag">Notre parcours</span>
            <h2>Notre Histoire</h2>
            <div class="pres-divider"></div>
        </div>
        <div class="timeline">
            <div class="timeline-item" data-aos="fade-right">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="timeline-year">1999</span>
                    <h3>Fondation du GSCC</h3>
                    <p>Création du Groupe de Support Contre le Cancer par un groupe de dames bénévoles, animées par la volonté d'apporter du soutien aux personnes atteintes de cancer en Haïti.</p>
                </div>
            </div>
            <div class="timeline-item" data-aos="fade-left">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="timeline-year">2003</span>
                    <h3>Reconnaissance officielle</h3>
                    <p>Reconnaissance par le Ministère des Affaires Sociales et du Travail, marquant une étape clé dans la légitimité de l'organisation.</p>
                </div>
            </div>
            <div class="timeline-item" data-aos="fade-right">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="timeline-year">2010</span>
                    <h3>Premier programme d'aide financière</h3>
                    <p>Lancement du premier programme d'aide financière pour permettre aux patients à faibles revenus d'accéder aux traitements nécessaires.</p>
                </div>
            </div>
            <div class="timeline-item" data-aos="fade-left">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="timeline-year">2015</span>
                    <h3>Ouverture du centre d'écoute</h3>
                    <p>Inauguration du premier centre d'écoute et de soutien psychologique pour les patients et leurs familles à travers Haïti.</p>
                </div>
            </div>
            <div class="timeline-item" data-aos="fade-right">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="timeline-year">2020</span>
                    <h3>Campagnes de sensibilisation nationale</h3>
                    <p>Lancement de grandes campagnes de sensibilisation à travers tout le pays sur la prévention et le dépistage précoce du cancer.</p>
                </div>
            </div>
            <div class="timeline-item" data-aos="fade-left">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="timeline-year">2024</span>
                    <h3>Plus de 1 500 personnes aidées</h3>
                    <p>Aujourd'hui, le GSCC a accompagné plus de 1 500 personnes dans leur combat contre le cancer et continue d'étendre ses actions à travers tout le pays.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══ VALEURS — id="valeurs" ═════════════════════════════════ -->
<section id="valeurs" class="valeurs">
    <div class="container">
        <div class="pres-section-header" data-aos="fade-up">
            <span class="pres-section-tag">Ce qui nous guide</span>
            <h2>Nos Valeurs &amp; Engagements</h2>
            <div class="pres-divider"></div>
        </div>
        <div class="valeurs-grid">
            <?php
            $couleurs_defaut = ['#003399', '#D94F7A', '#4CAF50', '#C9933A'];
            foreach ($valeurs as $index => $valeur):
                $couleur = htmlspecialchars($valeur['couleur'] ?? $couleurs_defaut[$index % 4]);
                $icone   = htmlspecialchars($valeur['icone']   ?? 'fa-heart');
            ?>
                <div class="valeur-card" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                    <div class="valeur-icon" style="background:<?= $couleur ?>18; color:<?= $couleur ?>;">
                        <i class="fas <?= $icone ?>"></i>
                    </div>
                    <h3><?= e($valeur['titre']) ?></h3>
                    <p><?= e($valeur['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ ÉQUIPE — id="equipe" ═══════════════════════════════════ -->
<section id="equipe" class="equipe">
    <div class="container">
        <div class="pres-section-header" data-aos="fade-up">
            <span class="pres-section-tag">Les visages du GSCC</span>
            <h2>Notre Équipe</h2>
            <div class="pres-divider"></div>
            <p class="pres-section-sub">Des professionnels dévoués et passionnés à votre service</p>
        </div>
        <div class="team-grid">
            <?php if (empty($equipe)): ?>
                <?php
                $membres_defaut = [
                    ['nom' => 'Dr. Marie Jean-Baptiste',  'fonction' => 'Présidente Fondatrice',       'bio' => 'Oncologue avec plus de 20 ans d\'expérience, dédiée à la lutte contre le cancer en Haïti.', 'rand' => 10],
                    ['nom' => 'Pierre Richard Alexandre', 'fonction' => 'Coordinateur des Programmes',  'bio' => 'Expert en gestion de projets humanitaires, coordonne les actions sur le terrain.',          'rand' => 11],
                    ['nom' => 'Rose-Merline Charles',     'fonction' => 'Psychologue Clinicienne',      'bio' => 'Spécialiste en accompagnement psychologique des patients et familles.',                    'rand' => 12],
                    ['nom' => 'Jean-Claude Michel',       'fonction' => 'Responsable Administratif',    'bio' => 'Gère les aspects administratifs et financiers de l\'organisation.',                        'rand' => 13],
                ];
                foreach ($membres_defaut as $i => $m):
                ?>
                    <div class="team-card" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                        <div class="team-image">
                            <img src="https://picsum.photos/400/500?random=<?= $m['rand'] ?>"
                                 alt="<?= htmlspecialchars($m['nom']) ?>">
                        </div>
                        <div class="team-info">
                            <h3><?= htmlspecialchars($m['nom']) ?></h3>
                            <div class="fonction"><?= htmlspecialchars($m['fonction']) ?></div>
                            <p class="bio"><?= htmlspecialchars($m['bio']) ?></p>
                            <div class="team-social">
                                <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#" aria-label="Email"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($equipe as $i => $membre): ?>
                    <div class="team-card" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                        <div class="team-image">
                            <?php
                            if (!empty($membre['photo'])) {
                                $photo_src = rtrim(SITE_URL, '/') . '/' . ltrim($membre['photo'], '/');
                            } else {
                                $photo_src = 'https://ui-avatars.com/api/?name='
                                    . urlencode($membre['prenom'] . '+' . $membre['nom'])
                                    . '&size=400&background=D94F7A&color=fff&font-size=0.33';
                            }
                            $fallback = 'https://ui-avatars.com/api/?name='
                                . urlencode($membre['prenom'] . '+' . $membre['nom'])
                                . '&size=400&background=D94F7A&color=fff&font-size=0.33';
                            ?>
                            <img src="<?= htmlspecialchars($photo_src) ?>"
                                 alt="<?= htmlspecialchars($membre['prenom'] . ' ' . $membre['nom']) ?>"
                                 onerror="this.onerror=null;this.src='<?= htmlspecialchars($fallback) ?>';">
                        </div>
                        <div class="team-info">
                            <h3><?= e($membre['prenom'] . ' ' . $membre['nom']) ?></h3>
                            <div class="fonction"><?= e($membre['fonction']) ?></div>
                            <?php if (!empty($membre['bio'])): ?>
                                <p class="bio"><?= e($membre['bio']) ?></p>
                            <?php endif; ?>
                            <div class="team-social">
                                <?php if (!empty($membre['email'])): ?>
                                    <a href="mailto:<?= e($membre['email']) ?>" aria-label="Email">
                                        <i class="fas fa-envelope"></i>
                                    </a>
                                <?php endif; ?>
                                <?php
                                $reseaux = [];
                                if (!empty($membre['reseaux_sociaux'])) {
                                    $reseaux = is_array($membre['reseaux_sociaux'])
                                        ? $membre['reseaux_sociaux']
                                        : json_decode($membre['reseaux_sociaux'], true) ?? [];
                                }
                                if (!empty($reseaux['linkedin'])): ?>
                                    <a href="<?= e($reseaux['linkedin']) ?>" target="_blank" rel="noopener" aria-label="LinkedIn">
                                        <i class="fab fa-linkedin-in"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'templates/footer.php'; ?>