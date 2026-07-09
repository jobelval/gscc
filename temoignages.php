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
        ['id'=>1,'nom'=>'Marie C.','fonction'=>'Patiente accompagnée','photo'=>'https://randomuser.me/api/portraits/women/1.jpg','temoignage'=>'Grâce au soutien du GSCC, j\'ai pu suivre mon traitement dans de bonnes conditions. Leur accompagnement a été essentiel dans mon combat contre le cancer. Les bénévoles sont à l\'écoute et vraiment présents à chaque étape.','note'=>5,'date_creation'=>'2024-09-15'],
        ['id'=>2,'nom'=>'Jean-Paul D.','fonction'=>'Bénévole','photo'=>'https://randomuser.me/api/portraits/men/2.jpg','temoignage'=>'Je suis bénévole au GSCC depuis 2 ans. Voir l\'impact positif de nos actions sur les patients et leurs familles est une source de motivation incroyable. Chaque sourire, chaque remerciement vaut tout l\'or du monde.','note'=>5,'date_creation'=>'2024-10-20'],
        ['id'=>3,'nom'=>'Sophie L.','fonction'=>'Proche de patient','photo'=>'https://randomuser.me/api/portraits/women/3.jpg','temoignage'=>'Le GSCC m\'a apporté un soutien psychologique précieux quand mon mari a été diagnostiqué. Leur équipe est à l\'écoute et vraiment dévouée. Les groupes de parole m\'ont énormément aidée à traverser cette épreuve.','note'=>5,'date_creation'=>'2024-08-05'],
        ['id'=>4,'nom'=>'Dr. Pierre Antoine','fonction'=>'Médecin partenaire','photo'=>'https://randomuser.me/api/portraits/men/4.jpg','temoignage'=>'En tant que médecin, je travaille régulièrement avec le GSCC. Leur professionnalisme et leur dévouement pour les patients sont remarquables. Ils comblent un vide dans notre système de santé.','note'=>5,'date_creation'=>'2024-09-30'],
        ['id'=>5,'nom'=>'Marchena D.','fonction'=>'Patiente','photo'=>'https://randomuser.me/api/portraits/women/5.jpg','temoignage'=>'Le GSCC m\'a aidée à comprendre ma maladie et à ne pas perdre espoir. Leurs conseils et leur présence m\'ont donné la force de continuer le combat. Je recommande cette association à tous ceux qui traversent cette épreuve.','note'=>5,'date_creation'=>'2024-10-10'],
        ['id'=>6,'nom'=>'Robert F.','fonction'=>'Donateur','photo'=>'https://randomuser.me/api/portraits/men/6.jpg','temoignage'=>'Je soutiens le GSCC depuis plusieurs années. Voir la transparence avec laquelle ils utilisent les dons et l\'impact concret de leurs actions me conforte dans mon engagement.','note'=>5,'date_creation'=>'2024-07-22'],
    ];
}

$featured = $temoignages[0];
$rest     = array_slice($temoignages, 1);

function avatarSrc(array $t): string {
    $p = $t['photo'] ?? '';
    if (empty($p)) return '';
    return (str_starts_with($p,'http')) ? $p : (defined('SITE_URL') ? SITE_URL.'/uploads/temoignages/'.$p : $p);
}

function roleColor(string $role): string {
    $map = [
        'patient'   => '#C8375A',
        'bénévole'  => '#003399',
        'proche'    => '#2E7D32',
        'médecin'   => '#1565C0',
        'donateur'  => '#E8A020',
    ];
    foreach ($map as $k => $c) {
        if (str_contains(mb_strtolower($role), $k)) return $c;
    }
    return '#003399';
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
            --rose-lite: #FDF0F3;
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
        .tm-hero {
            background: linear-gradient(135deg, #7B1535 0%, #C8375F 55%, #D94F7A 100%);
            padding: 90px 0 130px;
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        .tm-hero::before, .tm-hero::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
            pointer-events: none;
        }
        .tm-hero::before { width: 500px; height: 500px; top: -130px; right: -100px; }
        .tm-hero::after  { width: 300px; height: 300px; bottom: 20px; left: -70px; }
        .tm-hero .container { position: relative; z-index: 2; }

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
        .tm-hero h1 {
            font-size: clamp(2.4rem, 5vw, 3.8rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
            margin-bottom: 18px;
        }
        .tm-hero h1 span { color: #F9A8C4; }
        .tm-hero > .container > p {
            font-size: 1.05rem;
            color: rgba(255,255,255,0.88);
            max-width: 500px;
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
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 28px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
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
            letter-spacing: .4px;
        }

        /* ── MAIN ── */
        .main-section { padding: 70px 0 80px; background: var(--bg); }

        /* ── FEATURED ── */
        .featured-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 24px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
            margin-bottom: 72px;
            box-shadow: 0 4px 24px rgba(0,0,0,.07);
        }
        .featured-left {
            padding: 52px 48px;
            position: relative;
            border-right: 1px solid var(--border);
        }
        .featured-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--rose);
            background: var(--rose-lite);
            border-radius: 100px;
            padding: 4px 14px;
            margin-bottom: 28px;
        }
        .big-quote {
            font-size: 120px;
            line-height: .8;
            color: var(--blue-lite);
            font-family: Georgia, serif;
            font-weight: 900;
            margin-bottom: -16px;
            user-select: none;
        }
        .featured-text {
            font-size: 1.05rem;
            color: var(--text-2);
            line-height: 1.85;
            font-style: italic;
            margin-bottom: 32px;
        }
        .featured-author {
            display: flex;
            align-items: center;
            gap: 16px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
        }
        .featured-avatar {
            width: 58px; height: 58px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            border: 3px solid var(--blue-lite);
        }
        .featured-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .avatar-initials {
            width: 58px; height: 58px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 800; color: #fff;
            flex-shrink: 0;
        }
        .featured-author strong { display: block; font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 2px; }
        .featured-author span { font-size: 13px; color: var(--muted); }

        .featured-right {
            padding: 52px 44px;
            background: var(--bg);
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 18px;
        }
        .featured-right-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 6px;
        }
        .fstat-item {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px 24px;
            transition: box-shadow .2s, transform .2s;
        }
        .fstat-item:hover { box-shadow: 0 6px 20px rgba(0,0,0,.07); transform: translateY(-2px); }
        .fstat-item strong {
            display: block;
            font-size: 2rem;
            font-weight: 800;
            color: var(--blue);
            line-height: 1;
            margin-bottom: 4px;
        }
        .fstat-item span { font-size: 13px; color: var(--muted); }

        /* ── SECTION LABEL ── */
        .section-label {
            margin-bottom: 40px;
        }
        .section-label .tag {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: var(--rose-lite);
            color: var(--rose);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 100px;
            margin-bottom: 10px;
        }
        .section-label h2 {
            font-size: 1.7rem;
            font-weight: 800;
            color: var(--text);
        }
        .divider-line {
            width: 48px; height: 4px;
            background: linear-gradient(90deg, var(--rose), var(--blue-mid));
            border-radius: 4px;
            margin: 10px 0 0;
        }

        /* ── TESTIMONIAL GRID ── */
        .temoignages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 22px;
            margin-bottom: 72px;
        }

        /* ── TESTIMONIAL CARD ── */
        .tm-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 28px 26px 24px;
            display: flex;
            flex-direction: column;
            transition: transform .3s, box-shadow .3s;
            position: relative;
            overflow: hidden;
        }
        .tm-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            border-radius: 20px 20px 0 0;
            opacity: 0;
            transition: opacity .3s;
        }
        .tm-card:hover { transform: translateY(-6px); box-shadow: 0 14px 40px rgba(0,0,0,.09); }
        .tm-card:hover::before { opacity: 1; }

        .card-quote-mark {
            font-size: 60px;
            line-height: .9;
            font-family: Georgia, serif;
            font-weight: 900;
            margin-bottom: 2px;
            user-select: none;
            opacity: .18;
        }
        .card-stars {
            display: flex;
            gap: 3px;
            margin-bottom: 14px;
        }
        .card-stars i { font-size: 13px; color: var(--gold); }
        .card-stars i.empty { color: var(--border); }

        .card-text {
            font-size: 14px;
            color: var(--text-2);
            line-height: 1.8;
            flex: 1;
            margin-bottom: 22px;
            font-style: italic;
        }

        .card-footer {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }
        .card-avatar {
            width: 44px; height: 44px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid var(--border);
        }
        .card-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .card-initials {
            width: 44px; height: 44px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .card-meta { flex: 1; }
        .card-name { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 2px; }
        .card-role { font-size: 12px; color: var(--muted); }
        .card-date { font-size: 11px; color: var(--muted); margin-left: auto; white-space: nowrap; }

        /* ── CTA ── */
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
        .cta-inner::before { width: 380px; height: 380px; top: -130px; right: -80px; }
        .cta-inner::after  { width: 250px; height: 250px; bottom: -80px; left: -60px; }
        .cta-inner .rel { position: relative; z-index: 2; }
        .cta-icon {
            width: 62px; height: 62px;
            border-radius: 50%;
            background: rgba(255,255,255,.14);
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; color: #F9A8C4;
            margin: 0 auto 22px;
        }
        .cta-inner h3 {
            font-size: clamp(1.5rem, 2.8vw, 2.1rem);
            font-weight: 800;
            color: #fff;
            margin-bottom: 14px;
        }
        .cta-inner p {
            font-size: 1rem;
            color: rgba(255,255,255,.82);
            max-width: 500px;
            margin: 0 auto 36px;
            line-height: 1.8;
        }
        .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--rose);
            color: #fff;
            padding: 16px 40px;
            border-radius: 100px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            box-shadow: 0 6px 22px rgba(200,55,90,.45);
            transition: transform .25s, box-shadow .25s;
        }
        .btn-cta:hover { transform: translateY(-3px); box-shadow: 0 14px 36px rgba(200,55,90,.6); color: #fff; }

        /* ── RESPONSIVE ── */
        @media (max-width: 860px) {
            .featured-card { grid-template-columns: 1fr; }
            .featured-right { border-top: 1px solid var(--border); flex-direction: row; flex-wrap: wrap; gap: 14px; }
            .fstat-item { flex: 1; min-width: 130px; }
            .featured-left { border-right: none; padding: 36px 28px; }
        }
        @media (max-width: 580px) {
            .stats-band .inner { grid-template-columns: 1fr; }
            .stat-cell { border-right: none; border-bottom: 1px solid var(--border); }
            .stat-cell:last-child { border-bottom: none; }
            .temoignages-grid { grid-template-columns: 1fr; }
            .cta-inner { padding: 52px 24px; }
        }
    </style>
</head>

<body>
    <?php require_once 'templates/header.php'; ?>

    <!-- Hero -->
    <section class="tm-hero">
        <div class="container">
            <div class="hero-badge" data-aos="fade-down">
                <i class="fas fa-quote-left"></i> Paroles de notre communauté
            </div>
            <h1 data-aos="fade-up" data-aos-delay="60">
                Leurs <span>témoignages</span>
            </h1>
            <p data-aos="fade-up" data-aos-delay="130">
                Patients, familles, bénévoles — ils partagent leur expérience avec le GSCC.
            </p>
        </div>
        <div class="hero-wave">
            <svg viewBox="0 0 1440 64" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path d="M0,32 C360,64 1080,0 1440,32 L1440,64 L0,64 Z" fill="#FFFFFF"/>
            </svg>
        </div>
    </section>


    <!-- Main -->
    <section class="main-section">
        <div class="container">

            <!-- Témoignage à la une -->
            <?php if (!empty($featured)):
                $fc = roleColor($featured['fonction'] ?? '');
                $fs = avatarSrc($featured);
                $fi = mb_strtoupper(mb_substr($featured['nom'] ?? '?', 0, 1));
            ?>
            <div class="featured-card" data-aos="fade-up">
                <div class="featured-left">
                    <div class="featured-label">
                        <i class="fas fa-star"></i> Témoignage du moment
                    </div>
                    <div class="big-quote">"</div>
                    <p class="featured-text"><?= htmlspecialchars($featured['temoignage']) ?></p>
                    <div class="featured-author">
                        <?php if ($fs): ?>
                            <div class="featured-avatar">
                                <img src="<?= htmlspecialchars($fs) ?>" alt="<?= htmlspecialchars($featured['nom']) ?>" loading="lazy" onerror="this.parentElement.style.display='none';this.parentElement.nextElementSibling.style.display='flex';">
                            </div>
                            <div class="avatar-initials" style="background:<?= $fc ?>;display:none"><?= $fi ?></div>
                        <?php else: ?>
                            <div class="avatar-initials" style="background:<?= $fc ?>"><?= $fi ?></div>
                        <?php endif; ?>
                        <div>
                            <strong><?= htmlspecialchars($featured['nom']) ?></strong>
                            <span><?= htmlspecialchars($featured['fonction'] ?? '') ?></span>
                        </div>
                    </div>
                </div>

                <div class="featured-right">
                    <div class="featured-right-label">En chiffres</div>
                    <div class="fstat-item">
                        <strong>300+</strong>
                        <span>Personnes accompagnées depuis 25 ans</span>
                    </div>
                    <div class="fstat-item">
                        <strong><?= count($temoignages) ?></strong>
                        <span>Témoignages publiés sur cette page</span>
                    </div>
                    <div class="fstat-item">
                        <strong>5 / 5</strong>
                        <span>Note de satisfaction moyenne</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Grille -->
            <div class="section-label" data-aos="fade-up">
                <div class="tag"><i class="fas fa-comments"></i> Toutes les voix</div>
                <h2>Ce qu'ils disent de nous</h2>
                <div class="divider-line"></div>
            </div>

            <div class="temoignages-grid">
                <?php foreach ($temoignages as $idx => $t):
                    $c  = roleColor($t['fonction'] ?? '');
                    $ts = avatarSrc($t);
                    $ti = mb_strtoupper(mb_substr($t['nom'] ?? '?', 0, 1));
                    $dateStr = function_exists('formatDateFr')
                        ? formatDateFr($t['date_creation'] ?? date('Y-m-d'))
                        : date('d/m/Y', strtotime($t['date_creation'] ?? 'now'));
                ?>
                <div class="tm-card" data-aos="fade-up" data-aos-delay="<?= ($idx % 3) * 70 ?>"
                     style="--card-color:<?= $c ?>">
                    <style>.tm-card:nth-child(<?= $idx+1 ?>)::before{ background:<?= $c ?>; }</style>

                    <div class="card-quote-mark" style="color:<?= $c ?>">"</div>

                    <?php if (!empty($t['note'])): ?>
                    <div class="card-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i > $t['note'] ? 'empty' : '' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>

                    <p class="card-text"><?= htmlspecialchars($t['temoignage']) ?></p>

                    <div class="card-footer">
                        <?php if ($ts): ?>
                            <div class="card-avatar">
                                <img src="<?= htmlspecialchars($ts) ?>" alt="<?= htmlspecialchars($t['nom']) ?>" loading="lazy"
                                     onerror="this.parentElement.style.display='none';this.parentElement.nextElementSibling.style.display='flex';">
                            </div>
                            <div class="card-initials" style="background:<?= $c ?>;display:none"><?= $ti ?></div>
                        <?php else: ?>
                            <div class="card-initials" style="background:<?= $c ?>"><?= $ti ?></div>
                        <?php endif; ?>
                        <div class="card-meta">
                            <div class="card-name"><?= htmlspecialchars($t['nom']) ?></div>
                            <div class="card-role"><?= htmlspecialchars($t['fonction'] ?? '') ?></div>
                        </div>
                        <div class="card-date"><?= htmlspecialchars($dateStr) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- CTA -->
            <div class="cta-inner" data-aos="fade-up">
                <div class="rel">
                    <div class="cta-icon"><i class="fas fa-pen"></i></div>
                    <h3>Partagez votre expérience</h3>
                    <p>
                        Vous avez été accompagné par le GSCC, vous êtes bénévole ou donateur ?
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

        </div>
    </section>

    <?php require_once 'templates/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>
    <script>AOS.init({ duration: 600, once: true, offset: 50 });</script>
</body>

</html>
