<?php
// partenaires.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$page_title       = 'Nos Partenaires';
$page_description = 'Découvrez les entreprises et organisations qui soutiennent le GSCC dans sa mission.';

try {
    $stmt        = $pdo->query("SELECT * FROM partenaires WHERE est_actif = 1 ORDER BY type, ordre ASC");
    $partenaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError("Erreur partenaires.php: " . $e->getMessage());
    $partenaires = [];
}

if (empty($partenaires)) {
    $partenaires = [
        ['id'=>1,'nom'=>'Plasbag SA','description'=>'Entreprise leader dans la fabrication de produits plastiques, Plasbag SA met son expertise industrielle au service de la communauté. Soucieuse de son impact social, l\'entreprise soutient activement les initiatives du Groupe de Support Contre le Cancer (GSCC) depuis 2022. Ce partenariat solide témoigne de leur volonté d\'accompagner durablement la lutte contre le cancer et de contribuer au bien-être de la population haïtienne.','logo'=>'images/partenaires/plasbag.png','site_web'=>'https://www.facebook.com/plasbaght/?locale=fr_FR','type'=>'principal','ordre'=>1],
        ['id'=>2,'nom'=>'Carimpex','description'=>'Fort de plus de 20 ans d\'expertise dans le secteur du transport de fret en Haïti, Carimpex est un acteur clé de la logistique facilitant les échanges entre le pays et l\'international. Partenaire engagé du Groupe de Support Contre le Cancer (GSCC) depuis 2020, l\'entreprise met son sérieux et son professionnalisme au profit de notre cause.','logo'=>'images/partenaires/carimpex.png','site_web'=>'#','type'=>'principal','ordre'=>2],
        ['id'=>3,'nom'=>'Fondation Sogebank','description'=>'Institution à but non lucratif reconnue d\'utilité publique, la Fondation Sogebank incarne l\'engagement social du Groupe Sogebank depuis sa création en 1993. En rejoignant les partenaires du Groupe de Support Contre le Cancer (GSCC) en 2025, la Fondation renforce son action en faveur de projets d\'intérêt collectif, contribuant ainsi de manière significative à la santé publique en Haïti.','logo'=>'images/partenaires/sogebank.png','site_web'=>'#','type'=>'principal','ordre'=>3],
        ['id'=>4,'nom'=>'Groupe Sogebank','description'=>'Filiale du Groupe Sogebank fondée en 1988, la Sogebel est une institution financière de référence dédiée aux besoins immobiliers des particuliers et des institutions. Engagée dans le développement socio-économique du pays, elle soutient fidèlement le Groupe de Support Contre le Cancer (GSCC) depuis 2020.','logo'=>'images/partenaires/sogebel.png','site_web'=>'#','type'=>'principal','ordre'=>4],
        ['id'=>5,'nom'=>'Fondation Haïtienne de la Santé et de l\'Éducation (FHASE)','description'=>'Organisation dédiée au renforcement des piliers fondamentaux de la société, la FHASE œuvre quotidiennement pour l\'amélioration de l\'accès aux soins de santé et à une éducation de qualité en Haïti. Partenaire du GSCC depuis 2023, la fondation apporte une dimension essentielle à nos actions grâce à sa vision intégrée du développement humain.','logo'=>'images/partenaires/fhase.png','site_web'=>'#','type'=>'principal','ordre'=>5],
        ['id'=>6,'nom'=>'Église de Jésus-Christ des Saints des Derniers Jours','description'=>'Composée d\'une communauté mondiale unie par une foi commune, l\'Église de Jésus-Christ des Saints des Derniers Jours se donne pour mission de vivre l\'Évangile à travers des actions concrètes de solidarité. Engagée aux côtés du Groupe de Support Contre le Cancer (GSCC) dès 2025, ce partenariat témoigne de leur dévouement envers le bien-être d\'autrui.','logo'=>'images/partenaires/jesus-christ.png','site_web'=>'#','type'=>'principal','ordre'=>6],
        ['id'=>7,'nom'=>'Deka Group','description'=>'Fondée il y a environ 35 ans, Deka Group est une entreprise familiale de premier plan et l\'un des plus grands importateurs de produits de grande consommation en Haïti. Avec ses sept divisions spécialisées et son réseau de plus de 100 fournisseurs à travers le monde, Deka Group mobilise sa force pour soutenir durablement les actions de santé publique portées par le GSCC depuis 2020.','logo'=>'images/partenaires/deka_group.png','site_web'=>'#','type'=>'principal','ordre'=>7],
    ];
}

$types = [
    'principal'      => ['label' => 'Partenaires Principaux',     'icon' => 'fa-star'],
    'institutionnel' => ['label' => 'Partenaires Institutionnels', 'icon' => 'fa-landmark'],
    'technique'      => ['label' => 'Partenaires Techniques',      'icon' => 'fa-cogs'],
    'media'          => ['label' => 'Partenaires Médias',          'icon' => 'fa-satellite-dish'],
];

$par_type = [];
foreach ($partenaires as $p) $par_type[$p['type']][] = $p;

function initials(string $n): string {
    $w = array_filter(explode(' ', $n));
    return mb_strtoupper(mb_substr(array_shift($w) ?? '', 0, 1) . mb_substr(array_shift($w) ?? '', 0, 1));
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — <?= defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'GSCC' ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --blue:      #003399;
            --blue-dark: #001f6b;
            --blue-mid:  #1a56cc;
            --blue-lite: #EBF0FF;
            --rose:      #C8375A;
            --gold:      #E8A020;
            --sage:      #2E7D32;
            --text:      #0D1117;
            --text-2:    #1F2937;
            --muted:     #6B7280;
            --border:    #E5E7EB;
            --bg:        #F8FAFF;
            --white:     #FFFFFF;
        }

        /* ── HERO ── */
        .pt-hero {
            background: linear-gradient(140deg, var(--blue-dark) 0%, var(--blue) 55%, #1565C0 100%);
            padding: 90px 0 130px;
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        .pt-hero::before, .pt-hero::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
            pointer-events: none;
        }
        .pt-hero::before { width: 500px; height: 500px; top: -130px; right: -90px; }
        .pt-hero::after  { width: 300px; height: 300px; bottom: 20px; left: -70px; }
        .pt-hero .container { position: relative; z-index: 2; }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.25);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            padding: 7px 20px;
            border-radius: 100px;
            margin-bottom: 24px;
        }
        .pt-hero h1 {
            font-size: clamp(2.2rem, 5vw, 3.6rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
            margin-bottom: 18px;
        }
        .pt-hero h1 span { color: #F9A8C4; }
        .pt-hero > .container > p {
            font-size: 1.05rem;
            color: #C7D9FF;
            max-width: 520px;
            margin: 0 auto;
            line-height: 1.8;
        }
        .hero-wave {
            position: absolute;
            bottom: -1px; left: 0;
            width: 100%; line-height: 0; z-index: 1;
        }

        /* ── STATS BAND ── */
        .stats-band {
            background: var(--white);
            border-bottom: 1px solid var(--border);
        }
        .stats-band .inner {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 28px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
        }
        .stat-cell {
            padding: 32px 20px;
            text-align: center;
            border-right: 1px solid var(--border);
            transition: background .2s;
        }
        .stat-cell:last-child { border-right: none; }
        .stat-cell:hover { background: var(--bg); }
        .stat-n {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--blue);
            line-height: 1;
            margin-bottom: 6px;
        }
        .stat-l {
            font-size: 12px;
            color: var(--muted);
            font-weight: 500;
            letter-spacing: .5px;
        }

        /* ── MAIN ── */
        .main-section { padding: 70px 0 80px; background: var(--bg); }

        .intro-block {
            max-width: 740px;
            margin: 0 auto 64px;
            text-align: center;
        }
        .intro-block p {
            font-size: 1rem;
            color: var(--text-2);
            line-height: 1.9;
        }
        .intro-divider {
            width: 52px; height: 4px;
            background: linear-gradient(90deg, var(--rose), var(--blue-mid));
            border-radius: 4px;
            margin: 20px auto 0;
        }

        /* ── CATEGORY HEADER ── */
        .category-block { margin-bottom: 72px; }
        .category-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 32px;
        }
        .cat-icon {
            width: 42px; height: 42px;
            border-radius: 12px;
            background: var(--blue-lite);
            display: flex; align-items: center; justify-content: center;
            color: var(--blue);
            font-size: 16px;
            flex-shrink: 0;
        }
        .cat-title {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--text);
            flex: 1;
        }
        .cat-pill {
            font-size: 12px;
            font-weight: 600;
            color: var(--blue);
            background: var(--blue-lite);
            border: 1px solid #C7D9FF;
            border-radius: 100px;
            padding: 4px 14px;
        }

        /* ── PARTNER CARDS GRID ── */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 22px;
        }

        /* ── PARTNER CARD ── */
        .p-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform .3s, box-shadow .3s;
            position: relative;
        }
        .p-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--blue), var(--blue-mid));
            opacity: 0;
            transition: opacity .3s;
        }
        .p-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 16px 48px rgba(0,0,0,.1);
        }
        .p-card:hover::before { opacity: 1; }

        /* Logo zone */
        .p-logo {
            height: 140px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px 36px;
            border-bottom: 1px solid var(--border);
            transition: background .3s;
        }
        .p-card:hover .p-logo { background: var(--bg); }
        .p-logo img {
            max-width: 100%;
            max-height: 80px;
            object-fit: contain;
            transition: transform .35s, filter .35s;
            filter: drop-shadow(0 1px 3px rgba(0,0,0,.08));
        }
        .p-card:hover .p-logo img {
            transform: scale(1.07);
            filter: drop-shadow(0 3px 8px rgba(0,51,153,.15));
        }
        .p-initials {
            width: 70px; height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--blue-dark), var(--blue-mid));
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: .5px;
            transition: transform .35s;
        }
        .p-card:hover .p-initials { transform: scale(1.07); }

        /* Card body */
        .p-body {
            padding: 22px 24px 18px;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .p-name {
            font-size: 15.5px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 6px;
            line-height: 1.3;
        }
        .p-since {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 700;
            color: var(--blue);
            background: var(--blue-lite);
            border-radius: 100px;
            padding: 3px 12px;
            margin-bottom: 14px;
        }
        .p-since i { font-size: 9px; }
        .p-desc {
            font-size: 13.5px;
            color: var(--muted);
            line-height: 1.75;
            flex: 1;
            margin-bottom: 18px;
        }
        .p-footer {
            padding-top: 14px;
            border-top: 1px solid var(--border);
            width: 100%;
            text-align: center;
        }
        .btn-site {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            font-weight: 600;
            color: var(--blue);
            border: 1.5px solid var(--blue);
            border-radius: 100px;
            padding: 7px 20px;
            text-decoration: none;
            transition: background .25s, color .25s;
        }
        .btn-site:hover { background: var(--blue); color: #fff; }
        .btn-site i { font-size: 10px; }
        .verified-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            font-weight: 500;
            color: var(--muted);
        }
        .verified-tag i { color: var(--sage); }

        /* ── CTA BLOCK ── */
        .cta-wrap { margin-top: 16px; }
        .cta-inner {
            background: linear-gradient(140deg, var(--blue-dark) 0%, var(--blue) 55%, #1565C0 100%);
            border-radius: 24px;
            padding: 72px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .cta-inner::before, .cta-inner::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,.05);
            pointer-events: none;
        }
        .cta-inner::before { width: 400px; height: 400px; top: -140px; right: -80px; }
        .cta-inner::after  { width: 260px; height: 260px; bottom: -80px; left: -60px; }
        .cta-inner .container { position: relative; z-index: 2; }

        .cta-icon-wrap {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: rgba(255,255,255,.15);
            display: flex; align-items: center; justify-content: center;
            font-size: 26px; color: #F9A8C4;
            margin: 0 auto 22px;
            transition: transform .4s;
        }
        .cta-inner:hover .cta-icon-wrap { transform: rotate(8deg) scale(1.1); }
        .cta-inner h2 {
            font-size: clamp(1.5rem, 2.8vw, 2.2rem);
            font-weight: 800;
            color: #fff;
            margin-bottom: 14px;
        }
        .cta-inner p {
            font-size: 1rem;
            color: rgba(255,255,255,.82);
            max-width: 540px;
            margin: 0 auto 36px;
            line-height: 1.8;
        }
        .cta-btns {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-cta-primary {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            background: #fff;
            color: var(--blue);
            padding: 14px 32px;
            border-radius: 100px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14.5px;
            box-shadow: 0 4px 18px rgba(0,0,0,.14);
            transition: transform .25s, box-shadow .25s;
        }
        .btn-cta-primary:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,0,0,.2); color: var(--blue); }
        .btn-cta-secondary {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            background: transparent;
            color: rgba(255,255,255,.85);
            padding: 14px 28px;
            border-radius: 100px;
            border: 1.5px solid rgba(255,255,255,.3);
            text-decoration: none;
            font-weight: 500;
            font-size: 14.5px;
            transition: border-color .25s, color .25s;
        }
        .btn-cta-secondary:hover { border-color: #fff; color: #fff; }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            .stats-band .inner { grid-template-columns: repeat(2, 1fr); }
            .stat-cell:nth-child(2) { border-right: none; }
            .stat-cell:nth-child(3) { border-top: 1px solid var(--border); }
        }
        @media (max-width: 600px) {
            .cards-grid { grid-template-columns: 1fr; }
            .cta-inner  { padding: 52px 24px; }
            .cat-pill   { display: none; }
        }
    </style>
</head>

<body>
    <?php require_once 'templates/header.php'; ?>

    <!-- Hero -->
    <section class="pt-hero">
        <div class="container">
            <div class="hero-badge" data-aos="fade-down">
                <i class="fas fa-handshake"></i> Ensemble contre le cancer
            </div>
            <h1 data-aos="fade-up" data-aos-delay="60">
                Nos <span>Partenaires</span>
            </h1>
            <p data-aos="fade-up" data-aos-delay="130">
                Ils nous soutiennent dans notre mission et nous permettent d'étendre notre impact pour aider toujours plus de familles haïtiennes.
            </p>
        </div>
        <div class="hero-wave">
            <svg viewBox="0 0 1440 64" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path d="M0,32 C360,64 1080,0 1440,32 L1440,64 L0,64 Z" fill="#FFFFFF"/>
            </svg>
        </div>
    </section>

    <!-- Stats -->
    <div class="stats-band">
        <div class="inner">
            <div class="stat-cell" data-aos="fade-up">
                <div class="stat-n"><?= count($partenaires) ?></div>
                <div class="stat-l">Partenaires actifs</div>
            </div>
            <div class="stat-cell" data-aos="fade-up" data-aos-delay="80">
                <div class="stat-n">25+</div>
                <div class="stat-l">Années de soutien</div>
            </div>
            <div class="stat-cell" data-aos="fade-up" data-aos-delay="160">
                <div class="stat-n">200+</div>
                <div class="stat-l">Membres actifs</div>
            </div>
            <div class="stat-cell" data-aos="fade-up" data-aos-delay="240">
                <div class="stat-n">50+</div>
                <div class="stat-l">Projets soutenus</div>
            </div>
        </div>
    </div>

    <!-- Contenu -->
    <section class="main-section">
        <div class="container">

            <div class="intro-block" data-aos="fade-up">
                <p>
                    Le GSCC ne pourrait pas mener à bien ses actions sans le soutien précieux de ses partenaires.
                    Entreprises, institutions et personnalités locales — leur engagement à nos côtés nous permet
                    d'aider toujours plus de personnes touchées par le cancer. Ensemble, nous semons la vie.
                </p>
                <div class="intro-divider"></div>
            </div>

            <?php foreach ($types as $key => $info):
                if (empty($par_type[$key])) continue;
                $cnt = count($par_type[$key]);
            ?>
            <div class="category-block" data-aos="fade-up">
                <div class="category-header">
                    <div class="cat-icon"><i class="fas <?= htmlspecialchars($info['icon']) ?>"></i></div>
                    <h2 class="cat-title"><?= htmlspecialchars($info['label']) ?></h2>
                    <span class="cat-pill"><?= $cnt ?> partenaire<?= $cnt > 1 ? 's' : '' ?></span>
                </div>

                <div class="cards-grid">
                    <?php foreach ($par_type[$key] as $i => $p): ?>
                    <div class="p-card" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 70 ?>">

                        <div class="p-logo">
                            <?php if (!empty($p['logo'])): ?>
                                <img src="<?= htmlspecialchars($p['logo']) ?>"
                                     alt="Logo <?= htmlspecialchars($p['nom']) ?>"
                                     loading="lazy"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                <div class="p-initials" style="display:none"><?= htmlspecialchars(initials($p['nom'])) ?></div>
                            <?php else: ?>
                                <div class="p-initials"><?= htmlspecialchars(initials($p['nom'])) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="p-body">
                            <h3 class="p-name"><?= htmlspecialchars($p['nom']) ?></h3>
                            <span class="p-since">
                                <i class="fas fa-circle-check"></i>
                                <?= htmlspecialchars($info['label']) ?>
                            </span>
                            <p class="p-desc"><?= htmlspecialchars($p['description'] ?? '') ?></p>

                            <div class="p-footer">
                                <?php if (!empty($p['site_web']) && $p['site_web'] !== '#'): ?>
                                    <a href="<?= htmlspecialchars($p['site_web']) ?>" target="_blank" rel="noopener noreferrer" class="btn-site">
                                        Visiter le site <i class="fas fa-arrow-up-right-from-square"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="verified-tag">
                                        <i class="fas fa-shield-check"></i> Partenaire certifié
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- CTA -->
            <div class="cta-wrap" data-aos="fade-up">
                <div class="cta-inner">
                    <div class="container">
                        <div class="cta-icon-wrap"><i class="fas fa-handshake"></i></div>
                        <h2>Vous souhaitez devenir partenaire ?</h2>
                        <p>
                            Rejoignez notre réseau et contribuez à faire la différence dans la lutte contre le cancer en Haïti.
                            Ensemble, construisons un avenir sans cancer.
                        </p>
                        <div class="cta-btns">
                            <a href="https://h23xktcc.forms.app/gsccadhesion?objet=partenariat" target="_blank" class="btn-cta-primary">
                                <i class="fas fa-envelope"></i> Nous contacter
                            </a>
                            <a href="presentation.php#mission" class="btn-cta-secondary">
                                <i class="fas fa-arrow-right"></i> Notre mission
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <?php require_once 'templates/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>
    <script>AOS.init({ duration: 600, once: true, offset: 50 });</script>
</body>

</html>
