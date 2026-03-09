<?php
// specialistes.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$page_title       = 'Nos Spécialistes';
$page_description = 'Annuaire des professionnels de santé partenaires du GSCC pour la prise en charge des patients.';

$tous_specialistes = [
    ['nom' => 'Dr. Marie Jean-Baptiste',     'specialite' => 'Oncologue',            'hopital' => 'Hôpital Universitaire de Port-au-Prince', 'ville' => 'Port-au-Prince', 'telephone' => '+(509) 37 45 67 89', 'email' => 'marie.jb@hopital.ht',      'photo' => '#', 'disponible' => true],
    ['nom' => 'Dr. Pierre Richard Alexandre', 'specialite' => 'Radiothérapeute',      'hopital' => 'Centre Hospitalier du Cap',              'ville' => 'Cap-Haïtien',   'telephone' => '+(509) 38 12 34 56', 'email' => 'pr.alexandre@ch-cap.ht',    'photo' => '#',  'disponible' => true],
    ['nom' => 'Dr. Rose-Merline Charles',    'specialite' => 'Psycho-oncologue',     'hopital' => 'Clinique de la Concorde',                'ville' => 'Pétion-Ville',  'telephone' => '+(509) 36 98 76 54', 'email' => 'rm.charles@clinique.ht',    'photo' => '#', 'disponible' => true],
    ['nom' => 'Dr. Jean-Claude Michel',      'specialite' => 'Chirurgien oncologue', 'hopital' => 'Hôpital Saint-François',                'ville' => 'Port-au-Prince', 'telephone' => '+(509) 33 45 67 89', 'email' => 'jc.michel@hsf.ht',          'photo' => '#',  'disponible' => false],
    ['nom' => 'Dr. Marie Carmelle Augustin', 'specialite' => 'Gynécologue oncologue', 'hopital' => 'Maternité Isaie Jeanty',                 'ville' => 'Port-au-Prince', 'telephone' => '+(509) 37 12 34 56', 'email' => 'mc.augustin@maternite.ht',  'photo' => '#', 'disponible' => true],
    ['nom' => 'Dr. Fritz Dorval',            'specialite' => 'Hématologue',          'hopital' => 'Hôpital de la Paix',                    'ville' => 'Port-au-Prince', 'telephone' => '+(509) 34 56 78 90', 'email' => 'f.dorval@hopitalpaix.ht',   'photo' => '#',  'disponible' => true],
    ['nom' => 'Dr. Guiteau Jean-Pierre',     'specialite' => 'Urologue',             'hopital' => 'Hôpital Universitaire de Mirebalais',   'ville' => 'Mirebalais',    'telephone' => '+(509) 32 45 67 89', 'email' => 'g.jeanpierre@hum.ht',       'photo' => '#',  'disponible' => true],
    ['nom' => 'Dr. Nadine François',         'specialite' => 'Nutritionniste',       'hopital' => 'Cabinet privé',                          'ville' => 'Pétion-Ville',  'telephone' => '+(509) 38 98 76 54', 'email' => 'n.francois@nutrition.ht',   'photo' => '#', 'disponible' => false],
];

$specialite_filter = isset($_GET['specialite']) ? sanitize($_GET['specialite']) : '';
$ville_filter      = isset($_GET['ville'])      ? sanitize($_GET['ville'])      : '';

$specialistes = array_filter($tous_specialistes, function ($s) use ($specialite_filter, $ville_filter) {
    $ok_spec  = !$specialite_filter || stripos($s['specialite'], $specialite_filter) !== false;
    $ok_ville = !$ville_filter      || stripos($s['ville'],      $ville_filter)      !== false;
    return $ok_spec && $ok_ville;
});

$specialites_list = array_unique(array_column($tous_specialistes, 'specialite'));
$villes_list      = array_unique(array_column($tous_specialistes, 'ville'));
sort($specialites_list);
sort($villes_list);

function getInitials(string $nom): string
{
    $words = array_filter(explode(' ', $nom));
    $init  = '';
    $c = 0;
    foreach ($words as $w) {
        if (mb_strtolower($w) !== 'dr.' && $c < 2) {
            $init .= mb_strtoupper(mb_substr($w, 0, 1));
            $c++;
        }
    }
    return $init;
}
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        /* =====================================================
   GSCC · Spécialistes — Design professionnel
   Couleurs : Navy #0D1B35 · Rose #AC2F58
   Pas de grille en background
===================================================== */
        :root {
            --navy: #0D1B35;
            --navy-2: #1A2D50;
            --rose: #AC2F58;
            --rose-lite: #FAE8EE;
            --bg: #F4F6FB;
            --white: #FFFFFF;
            --text: #1A1A2E;
            --text-2: #4A4A6A;
            --text-3: #9A9AB0;
            --border: #E2E6F0;
            --r: 12px;
            --sh: 0 1px 8px rgba(13, 27, 53, .06), 0 4px 16px rgba(13, 27, 53, .04);
            --sh-hover: 0 8px 32px rgba(13, 27, 53, .13);
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
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 28px;
        }

        /* ══ PAGE HERO ═════════════════════════════════════ */
        .page-hero {
            background: var(--navy);
            padding: 84px 0 68px;
            text-align: center;
            /* Pas de ::before avec grille */
            position: relative;
        }

        /* fine ligne rose en bas uniquement */
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
            color: rgba(255, 255, 255, .65);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 6px 18px;
            border-radius: 99px;
            margin-bottom: 22px;
        }

        .page-hero h1 {
            font-size: clamp(2.2rem, 5vw, 3.6rem);
            font-weight: 700;
            color: #fff;
            line-height: 1.1;
            margin-bottom: 14px;
            letter-spacing: -.5px;
        }

        .page-hero p {
            font-size: 1rem;
            color: rgba(255, 255, 255, .55);
            max-width: 460px;
            margin: 0 auto;
        }

        /* ══ STATS ═════════════════════════════════════════ */
        .stats-row {
            background: var(--white);
            border-bottom: 1px solid var(--border);
        }

        .stats-row .inner {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 28px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
        }

        .stat-cell {
            padding: 28px 20px;
            text-align: center;
            border-right: 1px solid var(--border);
            transition: background .2s var(--ease);
        }

        .stat-cell:last-child {
            border-right: none;
        }

        .stat-cell:hover {
            background: #F0F4FF;
        }

        .stat-n {
            font-size: 2rem;
            font-weight: 700;
            color: var(--navy);
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-l {
            font-size: 11px;
            color: var(--text-3);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 600;
        }

        /* ══ SECTION PRINCIPALE ════════════════════════════ */
        .main-section {
            padding: 64px 0 96px;
        }

        /* ══ FILTRES ═══════════════════════════════════════ */
        .filters-wrap {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 22px 26px;
            margin-bottom: 40px;
            box-shadow: var(--sh);
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 180px;
        }

        .filter-label {
            display: block;
            margin-bottom: 6px;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-3);
            text-transform: uppercase;
            letter-spacing: .8px;
        }

        .filter-select {
            width: 100%;
            padding: 10px 36px 10px 13px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: var(--text);
            background-color: var(--bg);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%239A9AB0' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            appearance: none;
            cursor: pointer;
            outline: none;
            transition: border-color .2s var(--ease);
        }

        .filter-select:focus {
            border-color: var(--navy-2);
            background-color: var(--white);
        }

        .btn-filter {
            padding: 11px 26px;
            background: var(--navy);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: background .25s var(--ease);
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .btn-filter:hover {
            background: var(--rose);
        }

        .btn-reset {
            padding: 10px 18px;
            background: transparent;
            color: var(--text-3);
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 13.5px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .25s var(--ease);
        }

        .btn-reset:hover {
            border-color: var(--navy);
            color: var(--navy);
        }

        /* compteur */
        .results-count {
            font-size: 13px;
            color: var(--text-3);
            margin-bottom: 24px;
        }

        .results-count strong {
            color: var(--navy);
        }

        /* ══ GRILLE SPÉCIALISTES ═══════════════════════════ */
        .spec-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
            gap: 20px;
        }

        /* ══ CARTE ═════════════════════════════════════════ */
        .spec-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: var(--sh);
            transition: transform .3s var(--ease), box-shadow .3s var(--ease);
            position: relative;
        }

        /* ligne rose en haut au hover */
        .spec-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--rose);
            opacity: 0;
            transition: opacity .3s var(--ease);
            z-index: 1;
        }

        .spec-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--sh-hover);
        }

        .spec-card:hover::before {
            opacity: 1;
        }

        /* en-tête navy */
        .card-head {
            background: var(--navy);
            padding: 28px 20px 22px;
            text-align: center;
            position: relative;
        }

        /* badge disponibilité */
        .dispo-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 3px 11px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .3px;
        }

        .dispo-badge.available {
            background: rgba(76, 175, 80, .2);
            color: #81C784;
            border: 1px solid rgba(76, 175, 80, .3);
        }

        .dispo-badge.unavailable {
            background: rgba(0, 0, 0, .25);
            color: rgba(255, 255, 255, .38);
            border: 1px solid rgba(255, 255, 255, .1);
        }

        /* avatar */
        .card-avatar {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, .2);
            overflow: hidden;
            margin: 0 auto 13px;
            background: rgba(255, 255, 255, .08);
        }

        .card-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-init {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--white);
        }

        .card-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 5px;
            line-height: 1.25;
        }

        .card-spec {
            font-size: 12px;
            color: rgba(255, 255, 255, .52);
            font-weight: 500;
            letter-spacing: .3px;
        }

        /* corps */
        .card-body {
            padding: 20px 22px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .info-row {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            font-size: 13px;
            color: var(--text-2);
        }

        .info-row i {
            width: 16px;
            text-align: center;
            color: var(--text-3);
            font-size: 12.5px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .info-row a {
            color: var(--text-2);
            text-decoration: none;
            transition: color .2s;
        }

        .info-row a:hover {
            color: var(--rose);
        }

        /* pied */
        .card-foot {
            padding: 14px 20px;
            border-top: 1px solid var(--border);
        }

        .btn-contact {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 10px;
            background: var(--navy);
            color: #FFFFFF !important;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all .25s var(--ease);
        }

        .btn-contact:hover {
            background: var(--rose);
            color: #FFFFFF;
        }

        .btn-contact:active,
        .btn-contact:focus {
            background: #8B1A3A;
            color: #FFFFFF;
            outline: none;
        }

        /* état vide */
        .empty-state {
            grid-column: 1 / -1;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 72px 32px;
            text-align: center;
            box-shadow: var(--sh);
        }

        .empty-state i {
            font-size: 44px;
            color: var(--border);
            margin-bottom: 16px;
            display: block;
        }

        .empty-state h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 14px;
            color: var(--text-3);
        }

        /* ══ BLOC REJOINDRE ════════════════════════════════ */
        .join-block {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 44px 40px;
            margin-top: 48px;
            box-shadow: var(--sh);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 32px;
        }

        /* accent rose gauche */
        .join-block::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--rose);
        }

        .join-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            flex-shrink: 0;
            background: var(--rose-lite);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--rose);
            font-size: 22px;
        }

        .join-text {
            flex: 1;
        }

        .join-text h2 {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .join-text p {
            font-size: 14px;
            color: var(--text-2);
            line-height: 1.75;
            max-width: 620px;
        }

        .btn-rejoindre {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--navy);
            color: #FFFFFF !important;
            padding: 12px 26px;
            border-radius: 99px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13.5px;
            white-space: nowrap;
            transition: all .3s var(--ease);
            box-shadow: 0 4px 16px rgba(13, 27, 53, .18);
            flex-shrink: 0;
        }

        .btn-rejoindre:hover {
            background: var(--rose);
            color: #FFFFFF;
            transform: translateY(-2px);
        }

        .btn-rejoindre:active,
        .btn-rejoindre:focus {
            background: #8B1A3A;
            color: #FFFFFF;
            outline: none;
            transform: translateY(0);
        }

        /* ══ RESPONSIVE ════════════════════════════════════ */
        @media (max-width: 860px) {
            .spec-grid {
                grid-template-columns: 1fr 1fr;
            }

            .join-block {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 580px) {
            .page-hero {
                padding: 60px 0 52px;
            }

            .stats-row .inner {
                grid-template-columns: 1fr 1fr;
            }

            .stat-cell:nth-child(3) {
                border-top: 1px solid var(--border);
                grid-column: span 2;
                border-right: none;
            }

            .spec-grid {
                grid-template-columns: 1fr;
            }

            .filters-wrap {
                flex-direction: column;
            }

            .filter-group {
                width: 100%;
            }

            .join-block {
                padding: 28px 22px;
            }
        }
    </style>
</head>

<body>
    <?php require_once 'templates/header.php'; ?>

    <!-- ══ HERO ══ -->
    <header class="page-hero">
        <div class="container">
            <div class="hero-eyebrow" data-aos="fade-down">
                <i class="fas fa-stethoscope"></i>
                Réseau médical GSCC
            </div>
            <h1 data-aos="fade-up" data-aos-delay="80">Nos Spécialistes</h1>
            <p data-aos="fade-up" data-aos-delay="150">
                Des professionnels de santé dévoués à l'accompagnement des patients atteints de cancer.
            </p>
        </div>
    </header>

    <!-- ══ STATS ══ -->
    <div class="stats-row">
        <div class="inner">
            <div class="stat-cell" data-aos="fade-up">
                <div class="stat-n"><?= count($tous_specialistes) ?></div>
                <div class="stat-l">Spécialistes partenaires</div>
            </div>
            <div class="stat-cell" data-aos="fade-up" data-aos-delay="80">
                <div class="stat-n"><?= count(array_unique(array_column($tous_specialistes, 'specialite'))) ?></div>
                <div class="stat-l">Spécialités couvertes</div>
            </div>
            <div class="stat-cell" data-aos="fade-up" data-aos-delay="160">
                <div class="stat-n"><?= count(array_filter($tous_specialistes, fn($s) => $s['disponible'])) ?></div>
                <div class="stat-l">Disponibles maintenant</div>
            </div>
        </div>
    </div>

    <!-- ══ CONTENU ══ -->
    <section class="main-section">
        <div class="container">

            <!-- Filtres -->
            <div class="filters-wrap" data-aos="fade-up">
                <form method="GET" style="display:contents">
                    <div class="filter-group">
                        <label class="filter-label" for="f-spec">Spécialité</label>
                        <select name="specialite" id="f-spec" class="filter-select">
                            <option value="">Toutes les spécialités</option>
                            <?php foreach ($specialites_list as $sp): ?>
                                <option value="<?= htmlspecialchars($sp) ?>"
                                    <?= $specialite_filter === $sp ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sp) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label" for="f-ville">Ville</label>
                        <select name="ville" id="f-ville" class="filter-select">
                            <option value="">Toutes les villes</option>
                            <?php foreach ($villes_list as $vl): ?>
                                <option value="<?= htmlspecialchars($vl) ?>"
                                    <?= $ville_filter === $vl ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($vl) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-magnifying-glass"></i> Filtrer
                    </button>
                    <?php if ($specialite_filter || $ville_filter): ?>
                        <a href="specialistes.php" class="btn-reset">
                            <i class="fas fa-xmark"></i> Réinitialiser
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Compteur -->
            <?php $count = count($specialistes); ?>
            <p class="results-count" data-aos="fade-up">
                <strong><?= $count ?></strong>
                spécialiste<?= $count > 1 ? 's' : '' ?> trouvé<?= $count > 1 ? 's' : '' ?>
                <?= ($specialite_filter || $ville_filter) ? ' pour votre recherche' : '' ?>
            </p>

            <!-- Grille -->
            <div class="spec-grid">
                <?php if (empty($specialistes)): ?>
                    <div class="empty-state" data-aos="fade-up">
                        <i class="fas fa-user-doctor"></i>
                        <h3>Aucun spécialiste trouvé</h3>
                        <p>Modifiez vos critères ou réinitialisez les filtres.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($specialistes as $idx => $s):
                        $init = htmlspecialchars(getInitials($s['nom']));
                    ?>
                        <div class="spec-card" data-aos="fade-up" data-aos-delay="<?= ($idx % 3) * 70 ?>">

                            <div class="card-head">
                                <span class="dispo-badge <?= $s['disponible'] ? 'available' : 'unavailable' ?>">
                                    <?= $s['disponible'] ? 'Disponible' : 'Indisponible' ?>
                                </span>
                                <div class="card-avatar">
                                    <?php if (!empty($s['photo'])): ?>
                                        <img src="<?= htmlspecialchars($s['photo']) ?>"
                                            alt="<?= htmlspecialchars($s['nom']) ?>"
                                            loading="lazy"
                                            onerror="this.parentElement.innerHTML='<div class=\'avatar-init\'><?= $init ?></div>'">
                                    <?php else: ?>
                                        <div class="avatar-init"><?= $init ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-name"><?= htmlspecialchars($s['nom']) ?></div>
                                <div class="card-spec"><?= htmlspecialchars($s['specialite']) ?></div>
                            </div>

                            <div class="card-body">
                                <div class="info-row">
                                    <i class="fas fa-hospital"></i>
                                    <span><?= htmlspecialchars($s['hopital']) ?></span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-location-dot"></i>
                                    <span><?= htmlspecialchars($s['ville']) ?></span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-phone"></i>
                                    <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $s['telephone'])) ?>">
                                        <?= htmlspecialchars($s['telephone']) ?>
                                    </a>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-envelope"></i>
                                    <a href="mailto:<?= htmlspecialchars($s['email']) ?>">
                                        <?= htmlspecialchars($s['email']) ?>
                                    </a>
                                </div>
                            </div>

                            <div class="card-foot">
                                <a href="mailto:<?= htmlspecialchars($s['email']) ?>" class="btn-contact">
                                    <i class="fas fa-envelope"></i> Contacter
                                </a>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Rejoindre -->
            <div class="join-block" data-aos="fade-up">
                <div class="join-icon"><i class="fas fa-user-plus"></i></div>
                <div class="join-text">
                    <h2>Vous êtes spécialiste et souhaitez rejoindre notre réseau&nbsp;?</h2>
                    <p>
                        Le GSCC est constamment à la recherche de professionnels de santé passionnés
                        pour élargir son réseau. Si vous souhaitez mettre vos compétences au service
                        des patients atteints de cancer, contactez-nous.
                    </p>
                </div>
                <a href="contact.php?objet=devenir-specialiste" class="btn-rejoindre">
                    <i class="fas fa-handshake"></i> Nous rejoindre
                </a>
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