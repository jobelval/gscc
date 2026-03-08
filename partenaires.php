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
        ['id'=>1,'nom'=>'Ministère de la Santé Publique',  'description'=>'Partenariat institutionnel pour la mise en place des campagnes de dépistage national.',            'logo'=>'','site_web'=>'#','type'=>'institutionnel','ordre'=>1],
        ['id'=>2,'nom'=>'Croix Rouge Haïtienne',           'description'=>'Soutien logistique pour nos actions sur le terrain et formations aux premiers secours.',            'logo'=>'','site_web'=>'#','type'=>'principal',     'ordre'=>2],
        ['id'=>3,'nom'=>'Digicel Haïti',                   'description'=>'Soutien financier et mise à disposition de moyens de communication pour nos campagnes.',            'logo'=>'','site_web'=>'#','type'=>'principal',     'ordre'=>3],
        ['id'=>4,'nom'=>'Médecins Sans Frontières',        'description'=>"Collaboration médicale et formation de nos équipes aux soins d'urgence.",                          'logo'=>'','site_web'=>'#','type'=>'technique',    'ordre'=>4],
        ['id'=>5,'nom'=>'Radio Télévision Caraïbes',       'description'=>'Diffusion de nos messages de sensibilisation et couverture médiatique.',                           'logo'=>'','site_web'=>'#','type'=>'media',        'ordre'=>5],
        ['id'=>6,'nom'=>'Banque Nationale de Crédit',      'description'=>'Soutien financier et mise à disposition de comptes pour la collecte de dons.',                    'logo'=>'','site_web'=>'#','type'=>'principal',     'ordre'=>6],
        ['id'=>7,'nom'=>'Fondation Sogebank',               'description'=>'Financement de projets spécifiques et programme de micro-crédits pour les patients.',             'logo'=>'','site_web'=>'#','type'=>'principal',     'ordre'=>7],
        ['id'=>8,'nom'=>'Le Nouvelliste',                   'description'=>"Couverture médiatique de nos événements et publication d'articles de sensibilisation.",            'logo'=>'','site_web'=>'#','type'=>'media',        'ordre'=>8],
    ];
}

$types = [
    'principal'      => ['label'=>'Partenaires Principaux',      'icon'=>'fa-star'],
    'institutionnel' => ['label'=>'Partenaires Institutionnels',  'icon'=>'fa-landmark'],
    'technique'      => ['label'=>'Partenaires Techniques',       'icon'=>'fa-cogs'],
    'media'          => ['label'=>'Partenaires Médias',           'icon'=>'fa-satellite-dish'],
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<style>
/* =====================================================
   GSCC · Partenaires — Design professionnel
   Couleurs : #003399 · #4CAF50 · #FF69B4
   Pas de grille en background
===================================================== */
:root {
    --blue:       #003399;
    --blue-2:     #002277;
    --blue-lite:  #EBF0FF;
    --green:      #4CAF50;
    --green-lite: #E8F5E9;
    --pink:       #FF69B4;
    --pink-lite:  #FFF0F7;
    --bg:         #F4F6FB;
    --white:      #FFFFFF;
    --text:       #1A1A2E;
    --text-2:     #4A4A6A;
    --text-3:     #9A9AB0;
    --border:     #E2E6F0;
    --r:          12px;
    --sh:         0 1px 8px rgba(0,51,153,.06), 0 4px 16px rgba(0,51,153,.04);
    --sh-hover:   0 8px 32px rgba(0,51,153,.13);
    --ease:       cubic-bezier(.4,0,.2,1);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); line-height: 1.6; }
.container { max-width: 1140px; margin: 0 auto; padding: 0 28px; }
img { display: block; }

/* ══ PAGE HEADER ═══════════════════════════════════ */
.page-header {
    background: linear-gradient(135deg, #003399 0%, #4CAF50 100%);
    padding: 80px 0 64px;
    text-align: center;
    /* Pas de ::before avec grille */
}
.page-header h1 {
    font-size: clamp(2rem, 4vw, 3.2rem);
    font-weight: 700;
    color: #fff;
    margin-bottom: 12px;
    letter-spacing: -.5px;
}
.page-header p {
    font-size: 1.05rem;
    color: rgba(255,255,255,.78);
    max-width: 460px;
    margin: 0 auto;
}

/* ══ BANDE STATISTIQUES ════════════════════════════ */
.stats-band {
    background: var(--white);
    border-bottom: 1px solid var(--border);
    box-shadow: 0 1px 0 var(--border);
}
.stats-band .inner {
    max-width: 1140px;
    margin: 0 auto;
    padding: 0 28px;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
}
.stat-cell {
    padding: 30px 20px;
    text-align: center;
    border-right: 1px solid var(--border);
    transition: background .2s var(--ease);
}
.stat-cell:last-child { border-right: none; }
.stat-cell:hover      { background: var(--blue-lite); }
.stat-ico  { font-size: 17px; color: var(--blue); margin-bottom: 8px; }
.stat-n    { font-size: 2rem; font-weight: 700; color: var(--blue); line-height: 1; margin-bottom: 4px; }
.stat-l    { font-size: 11px; color: var(--text-3); text-transform: uppercase; letter-spacing: 1.2px; font-weight: 600; }

/* ══ SECTION PRINCIPALE ════════════════════════════ */
.main-section { padding: 64px 0 96px; }

/* intro */
.intro-block {
    max-width: 720px;
    margin: 0 auto 60px;
    text-align: center;
}
.intro-block p {
    font-size: 15.5px;
    color: var(--text-2);
    line-height: 1.85;
}

/* ══ CATÉGORIE ═════════════════════════════════════ */
.category-block { margin-bottom: 64px; }

.category-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 6px;
    padding-bottom: 18px;
    border-bottom: 1px solid var(--border);
}
.cat-icon {
    width: 38px; height: 38px;
    border-radius: 9px;
    background: var(--blue-lite);
    display: flex; align-items: center; justify-content: center;
    color: var(--blue); font-size: 15px; flex-shrink: 0;
}
.cat-title {
    font-size: 20px; font-weight: 700;
    color: var(--blue); flex: 1;
}
.cat-pill {
    font-size: 11.5px; font-weight: 600;
    color: var(--text-3);
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 99px;
    padding: 3px 13px;
}
.cat-accent {
    display: block;
    width: 48px; height: 3px;
    border-radius: 99px;
    background: linear-gradient(90deg, #003399, #FF69B4);
    margin-bottom: 28px;
}

/* ══ GRILLE CARTES ═════════════════════════════════ */
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

/* ══ CARTE PARTENAIRE ══════════════════════════════ */
.p-card {
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
/* fine barre bleue-rose en haut au hover */
.p-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, #003399, #FF69B4);
    opacity: 0;
    transition: opacity .3s var(--ease);
}
.p-card:hover { transform: translateY(-5px); box-shadow: var(--sh-hover); }
.p-card:hover::before { opacity: 1; }

/* zone logo */
.p-logo {
    height: 130px;
    background: var(--bg);
    display: flex; align-items: center; justify-content: center;
    padding: 24px;
    border-bottom: 1px solid var(--border);
    transition: background .3s var(--ease);
}
.p-card:hover .p-logo { background: var(--blue-lite); }
.p-logo img {
    max-width: 100%; max-height: 72px; object-fit: contain;
    transition: transform .35s var(--ease);
}
.p-card:hover .p-logo img { transform: scale(1.05); }

/* avatar initiales */
.p-initials {
    width: 68px; height: 68px; border-radius: 50%;
    background: linear-gradient(135deg, #003399 0%, #4CAF50 100%);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.3rem; font-weight: 700; letter-spacing: .5px;
    transition: transform .35s var(--ease);
}
.p-card:hover .p-initials { transform: scale(1.06); }

/* contenu */
.p-content { padding: 20px 22px 16px; flex: 1; display: flex; flex-direction: column; }
.p-name {
    font-size: 15.5px; font-weight: 700;
    color: var(--text); margin-bottom: 7px; line-height: 1.3;
}
.p-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 11px; border-radius: 99px;
    font-size: 10px; font-weight: 700;
    letter-spacing: .6px; text-transform: uppercase;
    margin-bottom: 11px; width: fit-content;
}
.badge-principal      { background: var(--blue-lite);  color: var(--blue);   }
.badge-institutionnel { background: #EEF0FF;            color: #2233AA;       }
.badge-technique      { background: var(--green-lite);  color: #2E7D32;       }
.badge-media          { background: var(--pink-lite);   color: #CC1177;       }

.p-desc {
    font-size: 13px; color: var(--text-2);
    line-height: 1.7; flex: 1; margin-bottom: 16px;
}
.p-footer {
    padding-top: 13px;
    border-top: 1px solid var(--border);
}
.btn-site {
    display: inline-flex; align-items: center; gap: 7px;
    font-size: 12.5px; font-weight: 600; color: var(--blue);
    border: 1.5px solid var(--blue); border-radius: 99px;
    padding: 6px 16px; text-decoration: none;
    transition: all .25s var(--ease);
}
.btn-site:hover { background: var(--blue); color: #fff; }
.btn-site i { font-size: 10px; transition: transform .25s; }
.btn-site:hover i { transform: translate(3px, -3px); }
.verified-tag {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 12px; color: var(--text-3);
}
.verified-tag i { color: var(--green); }

/* ══ BLOC CTA ══════════════════════════════════════ */
.cta-block {
    background: linear-gradient(135deg, #003399 0%, #4CAF50 100%);
    border-radius: 16px;
    padding: 64px 40px;
    text-align: center;
    margin-top: 12px;
    /* Pas de grille ::before */
}
.cta-icon-wrap {
    width: 60px; height: 60px; border-radius: 50%;
    background: rgba(255,255,255,.18);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #fff;
    margin: 0 auto 20px;
    transition: transform .4s var(--ease);
}
.cta-block:hover .cta-icon-wrap { transform: rotate(8deg) scale(1.08); }
.cta-block h2 {
    font-size: clamp(1.4rem, 2.5vw, 2rem);
    font-weight: 700; color: #fff; margin-bottom: 12px;
}
.cta-block p {
    font-size: 15px; color: rgba(255,255,255,.78);
    line-height: 1.8; max-width: 560px;
    margin: 0 auto 32px;
}
.cta-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.btn-cta-primary {
    display: inline-flex; align-items: center; gap: 8px;
    background: #fff; color: var(--blue);
    padding: 13px 30px; border-radius: 99px;
    text-decoration: none; font-weight: 700; font-size: 14px;
    transition: all .3s var(--ease);
    box-shadow: 0 4px 18px rgba(0,0,0,.14);
}
.btn-cta-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 26px rgba(0,0,0,.2); }
.btn-cta-secondary {
    display: inline-flex; align-items: center; gap: 8px;
    background: transparent; color: rgba(255,255,255,.82);
    padding: 13px 26px; border-radius: 99px;
    border: 1.5px solid rgba(255,255,255,.32);
    text-decoration: none; font-weight: 500; font-size: 14px;
    transition: all .3s var(--ease);
}
.btn-cta-secondary:hover { border-color: #fff; color: #fff; }

/* ══ RESPONSIVE ════════════════════════════════════ */
@media (max-width: 900px) {
    .stats-band .inner { grid-template-columns: repeat(2, 1fr); }
    .stat-cell:nth-child(2) { border-right: none; }
    .stat-cell:nth-child(3) { border-top: 1px solid var(--border); }
}
@media (max-width: 580px) {
    .page-header       { padding: 60px 0 48px; }
    .cards-grid        { grid-template-columns: 1fr; }
    .cta-block         { padding: 48px 24px; }
    .cat-pill          { display: none; }
    .stats-band .inner { grid-template-columns: repeat(2, 1fr); }
}
</style>
</head>
<body>
<?php require_once 'templates/header.php'; ?>

<!-- ══ HEADER ══ -->
<div class="page-header">
    <div class="container">
        <h1 data-aos="fade-up">Nos Partenaires</h1>
        <p data-aos="fade-up" data-aos-delay="100">
            Ils nous soutiennent dans notre mission contre le cancer
        </p>
    </div>
</div>

<!-- ══ STATS ══ -->
<div class="stats-band">
    <div class="inner">
        <div class="stat-cell" data-aos="fade-up">
            <div class="stat-ico"><i class="fas fa-handshake"></i></div>
            <div class="stat-n">15+</div>
            <div class="stat-l">Partenaires actifs</div>
        </div>
        <div class="stat-cell" data-aos="fade-up" data-aos-delay="80">
            <div class="stat-ico"><i class="fas fa-globe"></i></div>
            <div class="stat-n">5</div>
            <div class="stat-l">Internationaux</div>
        </div>
        <div class="stat-cell" data-aos="fade-up" data-aos-delay="160">
            <div class="stat-ico"><i class="fas fa-coins"></i></div>
            <div class="stat-n">$2M</div>
            <div class="stat-l">Fonds collectés</div>
        </div>
        <div class="stat-cell" data-aos="fade-up" data-aos-delay="240">
            <div class="stat-ico"><i class="fas fa-seedling"></i></div>
            <div class="stat-n">10</div>
            <div class="stat-l">Projets soutenus</div>
        </div>
    </div>
</div>

<!-- ══ CONTENU ══ -->
<section class="main-section">
    <div class="container">

        <div class="intro-block" data-aos="fade-up">
            <p>
                Le GSCC ne pourrait pas mener à bien ses actions sans le soutien précieux de ses partenaires.
                Entreprises, institutions, organisations internationales&nbsp;: leur engagement à nos côtés
                nous permet d'étendre notre impact et d'aider toujours plus de personnes touchées par le cancer.
            </p>
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
            <span class="cat-accent"></span>

            <div class="cards-grid">
                <?php foreach ($par_type[$key] as $i => $p): ?>
                <div class="p-card" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 80 ?>">

                    <div class="p-logo">
                        <?php if (!empty($p['logo'])): ?>
                            <img src="<?= htmlspecialchars($p['logo']) ?>"
                                 alt="Logo <?= htmlspecialchars($p['nom']) ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="p-initials"><?= htmlspecialchars(initials($p['nom'])) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="p-content">
                        <h3 class="p-name"><?= htmlspecialchars($p['nom']) ?></h3>
                        <span class="p-badge badge-<?= htmlspecialchars($key) ?>">
                            <i class="fas <?= htmlspecialchars($info['icon']) ?>"></i>
                            <?= htmlspecialchars($info['label']) ?>
                        </span>
                        <p class="p-desc"><?= htmlspecialchars($p['description'] ?? '') ?></p>

                        <div class="p-footer">
                            <?php if (!empty($p['site_web']) && $p['site_web'] !== '#'): ?>
                                <a href="<?= htmlspecialchars($p['site_web']) ?>"
                                   target="_blank" rel="noopener noreferrer"
                                   class="btn-site">
                                    Visiter le site <i class="fas fa-arrow-up-right-from-square"></i>
                                </a>
                            <?php else: ?>
                                <span class="verified-tag">
                                    <i class="fas fa-circle-check"></i> Partenaire certifié
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
        <div class="cta-block" data-aos="fade-up">
            <div class="cta-icon-wrap"><i class="fas fa-handshake"></i></div>
            <h2>Vous souhaitez devenir partenaire&nbsp;?</h2>
            <p>
                Rejoignez notre réseau et contribuez à faire la différence dans la lutte contre le cancer en Haïti.
                Ensemble, construisons un avenir meilleur pour les patients et leurs familles.
            </p>
            <div class="cta-btns">
                <a href="contact.php?objet=partenariat" class="btn-cta-primary">
                    <i class="fas fa-envelope"></i> Nous contacter
                </a>
                <a href="presentation.php#mission" class="btn-cta-secondary">
                    <i class="fas fa-arrow-right"></i> Notre mission
                </a>
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