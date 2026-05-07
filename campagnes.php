<?php
// campagnes.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$page_title       = 'Campagnes & Projets';
$page_description = 'Découvrez nos campagnes de sensibilisation et nos projets en cours pour lutter contre le cancer en Haïti.';

$type   = isset($_GET['type'])   ? trim(strip_tags($_GET['type']))   : 'tout';
$statut = isset($_GET['statut']) ? trim(strip_tags($_GET['statut'])) : 'tout';

if (!in_array($type,   ['tout','campagne','projet']))             $type   = 'tout';
if (!in_array($statut, ['tout','en_cours','termine','a_venir']))  $statut = 'tout';

$campagnes = [];
try {
    $where = []; $params = [];
    if ($type   !== 'tout') { $where[] = "type = ?";   $params[] = $type; }
    if ($statut !== 'tout') { $where[] = "statut = ?"; $params[] = $statut; }
    $ws  = count($where) ? "WHERE " . implode(" AND ", $where) : "";
    $sql = "SELECT * FROM campagnes_projets $ws
            ORDER BY CASE statut WHEN 'en_cours' THEN 1 WHEN 'a_venir' THEN 2 WHEN 'termine' THEN 3 ELSE 4 END, date_debut DESC";
    $stmt = $pdo->prepare($sql); $stmt->execute($params);
    $campagnes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError("Erreur campagnes.php: " . $e->getMessage());
}

require_once 'templates/header.php';
?>

<style>
/* ═══════════════════════════════════════════════════════
   VARIABLES
═══════════════════════════════════════════════════════ */
:root {
    --cp-blue:      #003399;
    --cp-blue-dk:   #001f7a;
    --cp-rose:      #D94F7A;
    --cp-rose-dk:   #B03060;
    --cp-rose-deep: #8B1A40;
    --cp-rose-pale: #FDE8EF;
    --cp-rose-mid:  #F5B8CC;
    --cp-gold:      #C9933A;
    --cp-dark:      #0D1117;
    --cp-charcoal:  #1E2A35;
    --cp-gray-bg:   #F4F6FB;
    --cp-gray-lt:   #EEF1F8;
    --cp-gray-txt:  #4B5563;
    --cp-border:    #D1D5DB;
    --cp-white:     #FFFFFF;
    --cp-radius:    14px;
    --cp-shadow:    0 4px 24px rgba(0,51,153,.08);
    --cp-shadow-h:  0 20px 56px rgba(0,51,153,.16);
}

/* ═══════════════════════════════════════════════════════
   HERO
═══════════════════════════════════════════════════════ */
.cp-hero {
    position: relative;
    min-height: 500px;
    display: flex;
    align-items: center;
    background:
        linear-gradient(135deg, rgba(0,10,40,.90) 0%, rgba(0,31,122,.84) 45%, rgba(139,26,64,.70) 100%),
        url('images/site/image3.jpg') center / cover no-repeat;
    overflow: hidden;
    padding: 100px 0 80px;
}
.cp-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 60% 55% at 80% 35%, rgba(217,79,122,.22), transparent),
        radial-gradient(ellipse 35% 40% at 8%  70%, rgba(255,255,255,.04), transparent);
    pointer-events: none;
}
.cp-hero-orbs span {
    position: absolute; border-radius: 50%;
    opacity: .055; background: white;
}
.cp-hero-orbs span:nth-child(1) { width:420px; height:420px; top:-130px; right:-90px; }
.cp-hero-orbs span:nth-child(2) { width:220px; height:220px; bottom: 10px; left:5%; }
.cp-hero-orbs span:nth-child(3) { width:100px; height:100px; top:60px; left:42%; }

.cp-hero-inner { position:relative; z-index:2; }

.cp-hero-tag {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.13); border: 1px solid rgba(255,255,255,.22);
    color: #fff; font-size: .72rem; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    padding: 6px 16px; border-radius: 30px; margin-bottom: 22px;
    backdrop-filter: blur(6px);
}
.cp-hero-tag i { color: var(--cp-rose-mid); }

.cp-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.4rem, 5.5vw, 3.8rem);
    color: #fff; font-weight: 700; line-height: 1.12;
    margin-bottom: 16px;
    text-shadow: 0 2px 20px rgba(0,0,0,.25);
}
.cp-hero h1 span { color: var(--cp-rose-mid); }

.cp-hero-desc {
    font-size: 1.05rem; color: rgba(255,255,255,.85);
    max-width: 560px; line-height: 1.8; margin-bottom: 44px;
}

/* Hero stats */
.cp-hero-stats {
    display: flex; gap: 0;
    background: rgba(255,255,255,.10);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 16px; overflow: hidden;
    width: fit-content;
}
.cp-hero-stat {
    padding: 18px 32px;
    border-right: 1px solid rgba(255,255,255,.12);
    text-align: center;
}
.cp-hero-stat:last-child { border-right: none; }
.cp-hero-stat .num {
    font-family: 'Playfair Display', serif;
    font-size: 2rem; font-weight: 700;
    color: var(--cp-rose-mid); line-height: 1;
    margin-bottom: 4px;
}
.cp-hero-stat .lbl {
    font-size: .75rem; color: rgba(255,255,255,.78);
    font-weight: 500; white-space: nowrap;
}

/* Wave */
.cp-hero-wave {
    position: absolute; bottom: -1px; left: 0; width: 100%; line-height: 0;
}
.cp-hero-wave svg { display: block; }

/* ═══════════════════════════════════════════════════════
   SECTION WRAPPER
═══════════════════════════════════════════════════════ */
.cp-main {
    padding: 70px 0 100px;
    background: var(--cp-gray-bg);
}

/* ═══════════════════════════════════════════════════════
   FEATURED — OCTOBRE ROSE 2025
═══════════════════════════════════════════════════════ */
.cp-featured {
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 12px 52px rgba(139,26,64,.18);
    background: white;
}

.cp-feat-top {
    display: grid;
    grid-template-columns: 1fr 1.05fr;
    min-height: 380px;
}

/* Côté image */
.cp-feat-img {
    position: relative;
    overflow: hidden;
}
.cp-feat-img img {
    width: 100%; height: 100%;
    object-fit: cover; display: block;
    transition: transform .6s ease;
}
.cp-featured:hover .cp-feat-img img { transform: scale(1.04); }

.cp-feat-img-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(139,26,64,.82) 0%, rgba(176,48,96,.60) 55%, rgba(217,79,122,.35) 100%);
}
.cp-feat-img-content {
    position: absolute; inset: 0; z-index: 2;
    display: flex; flex-direction: column;
    justify-content: flex-end; padding: 36px;
}
.cp-feat-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
    color: #fff; font-size: .7rem; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    padding: 5px 13px; border-radius: 20px;
    margin-bottom: 14px; width: fit-content;
    backdrop-filter: blur(6px);
}
.cp-feat-img-content h2 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.5rem, 2.5vw, 2.1rem);
    color: #fff; font-weight: 700; line-height: 1.2;
    margin-bottom: 8px;
    text-shadow: 0 2px 12px rgba(0,0,0,.25);
}
.cp-feat-img-content .kreol {
    font-size: .92rem; color: rgba(255,255,255,.82);
    font-style: italic;
}
.cp-feat-status {
    position: absolute; top: 22px; right: 22px;
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.28);
    color: #fff; font-size: .72rem; font-weight: 700;
    letter-spacing: .5px; text-transform: uppercase;
    padding: 6px 14px; border-radius: 20px;
    backdrop-filter: blur(6px);
    display: flex; align-items: center; gap: 6px;
}

/* Côté contenu */
.cp-feat-body {
    padding: 40px 44px;
    display: flex; flex-direction: column; justify-content: center;
}
.cp-feat-section-tag {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--cp-rose-pale); color: var(--cp-rose-dk);
    font-size: .72rem; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    padding: 5px 13px; border-radius: 20px;
    margin-bottom: 14px; width: fit-content;
}
.cp-feat-body > p {
    color: #374151; font-size: .96rem; line-height: 1.8;
    margin-bottom: 26px;
}
.cp-feat-body > p strong { color: var(--cp-charcoal); }

.cp-feat-points {
    list-style: none; padding: 0; margin: 0 0 28px;
    display: flex; flex-direction: column; gap: 11px;
}
.cp-feat-points li {
    display: flex; align-items: flex-start; gap: 12px;
    background: var(--cp-gray-bg); padding: 14px 16px;
    border-radius: 12px; border-left: 3px solid var(--cp-rose);
    transition: transform .2s, box-shadow .2s;
}
.cp-feat-points li:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 16px rgba(176,48,96,.1);
}
.cp-fp-icon {
    width: 34px; height: 34px; border-radius: 9px;
    background: var(--cp-rose-pale); color: var(--cp-rose-dk);
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; flex-shrink: 0;
}
.cp-feat-points li div > strong {
    display: block; color: var(--cp-charcoal);
    font-size: .87rem; font-weight: 700; margin-bottom: 1px;
}
.cp-feat-points li div p {
    margin: 0; color: var(--cp-gray-txt);
    font-size: .83rem; line-height: 1.55;
}

/* Rapport banner */
.cp-feat-rapport {
    background: linear-gradient(135deg, #1A0A12 0%, var(--cp-rose-deep) 50%, var(--cp-rose-dk) 100%);
    border-top: none;
    display: flex; align-items: center;
    justify-content: space-between; flex-wrap: wrap;
    gap: 16px; padding: 22px 36px;
}
.cp-rapport-text strong {
    color: #fff; font-size: .95rem; font-weight: 700;
    display: block; margin-bottom: 3px;
}
.cp-rapport-text span {
    color: rgba(255,255,255,.72); font-size: .82rem;
}
.cp-rapport-btns { display: flex; gap: 10px; flex-wrap: wrap; }
.cp-btn-dl {
    display: inline-flex; align-items: center; gap: 7px;
    background: #fff; color: var(--cp-rose-dk);
    padding: 10px 22px; border-radius: 30px;
    font-size: .85rem; font-weight: 700;
    text-decoration: none; white-space: nowrap;
    transition: all .22s;
}
.cp-btn-dl:hover {
    background: var(--cp-rose-pale); transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,.2);
}
.cp-btn-ghost {
    display: inline-flex; align-items: center; gap: 7px;
    background: rgba(255,255,255,.12); border: 1.5px solid rgba(255,255,255,.3);
    color: #fff; padding: 10px 22px; border-radius: 30px;
    font-size: .85rem; font-weight: 700;
    text-decoration: none; white-space: nowrap;
    transition: all .22s;
}
.cp-btn-ghost:hover {
    background: rgba(255,255,255,.22); transform: translateY(-2px); color: #fff;
}

/* ═══════════════════════════════════════════════════════
   TITRE DE SECTION
═══════════════════════════════════════════════════════ */
.cp-grid-header {
    display: flex; align-items: flex-end;
    justify-content: space-between; flex-wrap: wrap;
    gap: 16px; margin-bottom: 32px;
}
.cp-grid-header-left h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.55rem; color: var(--cp-charcoal);
    margin-bottom: 4px;
}
.cp-grid-header-left p {
    color: var(--cp-gray-txt); font-size: .9rem; margin: 0;
}

/* ═══════════════════════════════════════════════════════
   FILTRES
═══════════════════════════════════════════════════════ */
.cp-filters {
    display: flex; flex-wrap: wrap; gap: 10px;
    align-items: center;
}
.cp-filter-group {
    display: flex; gap: 4px; align-items: center;
    background: var(--cp-white);
    border: 1px solid var(--cp-border);
    padding: 4px; border-radius: 12px;
    box-shadow: var(--cp-shadow);
}
.cp-filter-sep {
    font-size: .75rem; color: var(--cp-border);
    padding: 0 4px;
}
.cp-filter-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 8px 16px; border-radius: 9px;
    background: transparent; color: var(--cp-charcoal);
    text-decoration: none; font-size: .84rem; font-weight: 600;
    transition: all .2s; white-space: nowrap;
}
.cp-filter-btn:hover { background: var(--cp-gray-lt); color: var(--cp-blue); }
.cp-filter-btn.active {
    background: var(--cp-blue); color: #fff;
    box-shadow: 0 3px 12px rgba(0,51,153,.28);
}
.cp-filter-btn.active i { color: rgba(255,255,255,.8); }
.cp-filter-btn i { font-size: .75rem; color: var(--cp-gray-txt); }

/* ═══════════════════════════════════════════════════════
   GRILLE CAMPAGNES
═══════════════════════════════════════════════════════ */
.cp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 28px;
}

.cp-card {
    background: var(--cp-white);
    border-radius: var(--cp-radius);
    overflow: hidden;
    border: 1px solid var(--cp-border);
    box-shadow: var(--cp-shadow);
    display: flex; flex-direction: column;
    transition: transform .28s ease, box-shadow .28s ease;
}
.cp-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--cp-shadow-h);
}

/* Image */
.cp-card-img {
    height: 220px; position: relative; overflow: hidden;
}
.cp-card-img img {
    width: 100%; height: 100%; object-fit: cover; display: block;
    transition: transform .5s ease;
}
.cp-card:hover .cp-card-img img { transform: scale(1.07); }

.cp-card-img-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(13,17,23,.5) 0%, transparent 55%);
    transition: opacity .3s;
}

/* Badges */
.cp-badges {
    position: absolute; top: 14px;
    display: flex; flex-direction: column; gap: 6px;
}
.cp-badges.left  { left: 14px; }
.cp-badges.right { right: 14px; align-items: flex-end; }

.cp-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 11px; border-radius: 20px;
    font-size: .7rem; font-weight: 700;
    letter-spacing: .3px; text-transform: uppercase;
    backdrop-filter: blur(6px);
}
.cp-badge-type     { background: rgba(0,51,153,.88);    color: #fff; }
.cp-badge-en_cours { background: rgba(34,139,34,.88);   color: #fff; }
.cp-badge-termine  { background: rgba(100,116,139,.88); color: #fff; }
.cp-badge-a_venir  { background: rgba(202,138,4,.92);   color: #fff; }

/* Icône zoom au hover */
.cp-card-zoom {
    position: absolute; bottom: 14px; right: 14px;
    width: 36px; height: 36px; border-radius: 50%;
    background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.3);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: .85rem;
    opacity: 0; transition: opacity .28s, transform .28s;
    backdrop-filter: blur(4px);
}
.cp-card:hover .cp-card-zoom { opacity: 1; transform: scale(1.1); }

/* Corps */
.cp-card-body {
    padding: 24px 26px 22px;
    flex: 1; display: flex; flex-direction: column;
}
.cp-card-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem; font-weight: 700;
    color: var(--cp-dark); line-height: 1.4;
    margin-bottom: 10px;
}
.cp-card-title a { color: inherit; text-decoration: none; transition: color .2s; }
.cp-card-title a:hover { color: var(--cp-blue); }

.cp-card-meta {
    display: flex; gap: 12px; flex-wrap: wrap;
    margin-bottom: 12px;
    font-size: .8rem; color: var(--cp-gray-txt);
}
.cp-card-meta span {
    display: flex; align-items: center; gap: 4px;
}
.cp-card-meta i { color: var(--cp-blue); font-size: .72rem; }

.cp-card-desc {
    color: var(--cp-gray-txt); font-size: .9rem;
    line-height: 1.65; margin-bottom: 18px; flex: 1;
}

/* Progress */
.cp-progress {
    margin-bottom: 18px;
}
.cp-progress-head {
    display: flex; justify-content: space-between;
    font-size: .78rem; font-weight: 700;
    color: var(--cp-charcoal); margin-bottom: 7px;
}
.cp-progress-track {
    height: 7px; background: var(--cp-gray-lt);
    border-radius: 10px; overflow: hidden;
}
.cp-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--cp-blue), var(--cp-rose));
    border-radius: 10px; transition: width .8s ease;
}

/* Footer */
.cp-card-footer {
    display: flex; align-items: center;
    justify-content: space-between; flex-wrap: wrap; gap: 10px;
    padding-top: 16px;
    border-top: 1px solid var(--cp-gray-lt);
    margin-top: auto;
}
.cp-card-type-label {
    font-size: .76rem; font-weight: 600;
    color: var(--cp-gray-txt);
    display: flex; align-items: center; gap: 5px;
}
.cp-card-type-label i { color: var(--cp-blue); font-size: .7rem; }

.cp-btn-more {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--cp-blue); color: #fff;
    padding: 9px 20px; border-radius: 30px;
    text-decoration: none; font-size: .82rem; font-weight: 700;
    transition: all .22s; white-space: nowrap;
}
.cp-btn-more:hover {
    background: var(--cp-rose); color: #fff;
    transform: translateX(3px);
}
.cp-btn-more i { font-size: .72rem; }

/* ═══════════════════════════════════════════════════════
   ÉTAT VIDE
═══════════════════════════════════════════════════════ */
.cp-empty {
    grid-column: 1 / -1;
    text-align: center; padding: 80px 24px;
    background: var(--cp-white); border-radius: var(--cp-radius);
    border: 2px dashed var(--cp-border);
}
.cp-empty-icon {
    width: 76px; height: 76px; border-radius: 20px;
    background: rgba(0,51,153,.07); color: var(--cp-blue);
    font-size: 2rem; display: flex; align-items: center;
    justify-content: center; margin: 0 auto 20px;
}
.cp-empty h3 { color: var(--cp-dark); font-size: 1.2rem; margin-bottom: 8px; }
.cp-empty p  { color: var(--cp-gray-txt); font-size: .9rem; margin: 0; }

/* ═══════════════════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════════════════ */
@media (max-width: 960px) {
    .cp-feat-top { grid-template-columns: 1fr; }
    .cp-feat-img { min-height: 280px; }
    .cp-feat-body { padding: 32px 28px; }
    .cp-hero-stats { flex-wrap: wrap; }
}
@media (max-width: 768px) {
    .cp-hero { padding: 80px 0 70px; min-height: auto; }
    .cp-grid { grid-template-columns: 1fr; }
    .cp-grid-header { flex-direction: column; align-items: flex-start; }
    .cp-feat-rapport { flex-direction: column; align-items: flex-start; padding: 22px 24px; }
    .cp-hero-stat { padding: 14px 20px; }
}
@media (max-width: 540px) {
    .cp-hero-stats { display: grid; grid-template-columns: 1fr 1fr; }
    .cp-hero-stat { border-right: none; border-bottom: 1px solid rgba(255,255,255,.12); }
}
</style>

<!-- ══ HERO ═══════════════════════════════════════════════════════ -->
<section class="cp-hero">
    <div class="cp-hero-orbs"><span></span><span></span><span></span></div>
    <div class="container">
        <div class="cp-hero-inner">
            <div class="cp-hero-tag" data-aos="fade-down">
                <i class="fas fa-megaphone"></i> GSCC — Nos actions
            </div>
            <h1 data-aos="fade-up">Campagnes &amp; <span>Projets</span></h1>
            <p class="cp-hero-desc" data-aos="fade-up" data-aos-delay="80">
                Chaque campagne, chaque projet est un acte de résistance contre le cancer.
                Découvrez comment le GSCC mobilise la communauté haïtienne pour sauver des vies.
            </p>
            <div class="cp-hero-stats" data-aos="fade-up" data-aos-delay="160">
                <div class="cp-hero-stat">
                    <div class="num">25<sup style="font-size:1rem;">+</sup></div>
                    <div class="lbl">Années d'action</div>
                </div>
                <div class="cp-hero-stat">
                    <div class="num">2<sup style="font-size:1rem;">+</sup></div>
                    <div class="lbl">Grandes campagnes</div>
                </div>
                <div class="cp-hero-stat">
                    <div class="num">100<sup style="font-size:1rem;">%</sup></div>
                    <div class="lbl">Fonds reversés</div>
                </div>
                <div class="cp-hero-stat">
                    <div class="num">1</div>
                    <div class="lbl">Mission : sauver des vies</div>
                </div>
            </div>
        </div>
    </div>
    <div class="cp-hero-wave">
        <svg viewBox="0 0 1440 52" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path fill="#F4F6FB" d="M0,52 C480,0 960,0 1440,52 L1440,52 L0,52 Z"/>
        </svg>
    </div>
</section>

<!-- ══ SECTION PRINCIPALE ════════════════════════════════════════ -->
<section class="cp-main">
    <div class="container">

        <!-- ── EN-TÊTE GRILLE + FILTRES ──────────────────────────── -->
        <div class="cp-grid-header" data-aos="fade-up">
            <div class="cp-grid-header-left">
                <h3>Toutes nos campagnes &amp; projets</h3>
                <p><?= count($campagnes) ?> initiative<?= count($campagnes) > 1 ? 's' : '' ?> trouvée<?= count($campagnes) > 1 ? 's' : '' ?></p>
            </div>
            <div class="cp-filters">
                <div class="cp-filter-group">
                    <a href="?type=tout&statut=<?= $statut ?>"     class="cp-filter-btn <?= $type==='tout'     ?'active':'' ?>"><i class="fas fa-layer-group"></i> Tous</a>
                    <a href="?type=campagne&statut=<?= $statut ?>"  class="cp-filter-btn <?= $type==='campagne' ?'active':'' ?>"><i class="fas fa-megaphone"></i> Campagnes</a>
                    <a href="?type=projet&statut=<?= $statut ?>"    class="cp-filter-btn <?= $type==='projet'   ?'active':'' ?>"><i class="fas fa-tasks"></i> Projets</a>
                </div>
                <span class="cp-filter-sep">|</span>
                <div class="cp-filter-group">
                    <a href="?type=<?= $type ?>&statut=tout"       class="cp-filter-btn <?= $statut==='tout'     ?'active':'' ?>">Tous</a>
                    <a href="?type=<?= $type ?>&statut=en_cours"   class="cp-filter-btn <?= $statut==='en_cours' ?'active':'' ?>">En cours</a>
                    <a href="?type=<?= $type ?>&statut=termine"    class="cp-filter-btn <?= $statut==='termine'  ?'active':'' ?>">Terminés</a>
                    <a href="?type=<?= $type ?>&statut=a_venir"    class="cp-filter-btn <?= $statut==='a_venir'  ?'active':'' ?>">À venir</a>
                </div>
            </div>
        </div>

        <!-- ── GRILLE ─────────────────────────────────────────────── -->
        <div class="cp-grid">
            <?php if (empty($campagnes)): ?>
                <div class="cp-empty" data-aos="fade-up">
                    <div class="cp-empty-icon"><i class="fas fa-calendar-times"></i></div>
                    <h3>Aucune campagne ou projet trouvé</h3>
                    <p>Modifiez vos filtres ou revenez plus tard pour découvrir nos nouvelles actions.</p>
                </div>
            <?php else: ?>
                <?php
                $statut_labels = ['en_cours'=>'En cours','termine'=>'Terminé','a_venir'=>'À venir'];
                $statut_icons  = ['en_cours'=>'fa-circle-notch','termine'=>'fa-check-circle','a_venir'=>'fa-clock'];
                foreach ($campagnes as $i => $c):
                    $sl   = $c['statut'] ?? 'en_cours';
                    $prog = isset($c['progression']) ? (int)$c['progression'] : null;
                    $img  = !empty($c['image_couverture']) ? $c['image_couverture'] : 'https://picsum.photos/700/400?random='.$c['id'];
                    $desc = mb_strimwidth(strip_tags($c['description'] ?? $c['contenu'] ?? ''), 0, 145, '…');
                ?>
                <div class="cp-card" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 70 ?>">
                    <div class="cp-card-img">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($c['titre']) ?>" loading="lazy">
                        <div class="cp-card-img-overlay"></div>
                        <div class="cp-badges left">
                            <span class="cp-badge cp-badge-type">
                                <i class="fas <?= $c['type']==='campagne' ? 'fa-megaphone':'fa-tasks' ?>"></i>
                                <?= $c['type']==='campagne' ? 'Campagne':'Projet' ?>
                            </span>
                        </div>
                        <div class="cp-badges right">
                            <span class="cp-badge cp-badge-<?= $sl ?>">
                                <i class="fas <?= $statut_icons[$sl] ?? 'fa-circle' ?>"></i>
                                <?= $statut_labels[$sl] ?? $sl ?>
                            </span>
                        </div>
                        <div class="cp-card-zoom"><i class="fas fa-arrow-right"></i></div>
                    </div>

                    <div class="cp-card-body">
                        <h3 class="cp-card-title">
                            <a href="campagne-detail.php?id=<?= (int)$c['id'] ?>">
                                <?= htmlspecialchars($c['titre']) ?>
                            </a>
                        </h3>

                        <div class="cp-card-meta">
                            <?php if (!empty($c['date_debut'])): ?>
                            <span>
                                <i class="far fa-calendar-alt"></i>
                                <?= date('d/m/Y', strtotime($c['date_debut'])) ?>
                                <?php if (!empty($c['date_fin'])): ?>
                                    — <?= date('d/m/Y', strtotime($c['date_fin'])) ?>
                                <?php endif; ?>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($c['objectif_montant'])): ?>
                            <span>
                                <i class="fas fa-bullseye"></i>
                                Objectif : $<?= number_format((float)$c['objectif_montant'], 0, ',', ' ') ?>
                            </span>
                            <?php endif; ?>
                        </div>

                        <p class="cp-card-desc"><?= htmlspecialchars($desc) ?></p>

                        <?php if ($prog !== null): ?>
                        <div class="cp-progress">
                            <div class="cp-progress-head">
                                <span>Progression</span>
                                <span><?= min(100, $prog) ?>%</span>
                            </div>
                            <div class="cp-progress-track">
                                <div class="cp-progress-fill" style="width:<?= min(100,$prog) ?>%"></div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="cp-card-footer">
                            <span class="cp-card-type-label">
                                <i class="fas fa-tag"></i>
                                <?= $c['type']==='campagne' ? 'Campagne de sensibilisation':'Projet' ?>
                            </span>
                            <a href="campagne-detail.php?id=<?= (int)$c['id'] ?>" class="cp-btn-more">
                                En savoir plus <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ── FEATURED : OCTOBRE ROSE 2025 ─────────────────────── -->
        <div class="cp-featured" data-aos="fade-up" style="margin-top:64px;">

            <div class="cp-feat-top">

                <!-- Visuel gauche -->
                <div class="cp-feat-img">
                    <img src="images/site/image3.jpg" alt="Campagne Octobre Rose 2025 — Football Rose GSCC">
                    <div class="cp-feat-img-overlay"></div>
                    <div class="cp-feat-status">
                        <i class="fas fa-check-circle"></i> Campagne terminée
                    </div>
                    <div class="cp-feat-img-content">
                        <div class="cp-feat-badge">
                            <i class="fas fa-ribbon"></i> Octobre Rose — 2025
                        </div>
                        <h2>« Kansè Pa Ka Tann »<br>Ann Aji Kounya</h2>
                        <p class="kreol">Le cancer n'attend pas : agissons maintenant</p>
                    </div>
                </div>

                <!-- Contenu droite -->
                <div class="cp-feat-body">
                    <div class="cp-feat-section-tag">
                        <i class="fas fa-star"></i> Campagne vedette
                    </div>
                    <p>
                        L'édition 2025 de la campagne Octobre Rose a marqué un <strong>tournant décisif</strong>
                        dans l'engagement du GSCC en Haïti. Cette initiative a mobilisé des milliers de personnes,
                        des partenaires institutionnels et les médias pour briser le silence autour du cancer du sein
                        et du col de l'utérus.
                    </p>

                    <ul class="cp-feat-points">
                        <li>
                            <div class="cp-fp-icon"><i class="fas fa-broadcast-tower"></i></div>
                            <div>
                                <strong>Sensibilisation massive</strong>
                                <p>Présence accrue dans les médias et réseaux sociaux pour informer sur les signes précurseurs et l'importance du dépistage précoce.</p>
                            </div>
                        </li>
                        <li>
                            <div class="cp-fp-icon"><i class="fas fa-futbol"></i></div>
                            <div>
                                <strong>Événements de mobilisation</strong>
                                <p>Le <strong>Football Rose 2025</strong>, alliant sport et solidarité pour collecter des fonds essentiels à la cause.</p>
                            </div>
                        </li>
                        <li>
                            <div class="cp-fp-icon"><i class="fas fa-hand-holding-heart"></i></div>
                            <div>
                                <strong>100% des fonds reversés</strong>
                                <p>Intégralement réinvestis dans des programmes de dépistage, sensibilisation et soins de dignité pour les familles.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Rapport -->
            <div class="cp-feat-rapport">
                <div class="cp-rapport-text">
                    <strong><i class="fas fa-file-pdf" style="margin-right:6px;"></i>Rapport d'impact officiel — Octobre Rose 2025</strong>
                    <span>Résultats complets, témoignages et utilisation détaillée des fonds collectés.</span>
                </div>
                <div class="cp-rapport-btns">
                    <a href="https://drive.google.com/file/d/1OtqVCUE9AwxpaDvp0mknw0-vpDRFjALZ/view?usp=sharing" target="_blank" class="cp-btn-dl">
                        <i class="fas fa-download"></i> Télécharger le PDF
                    </a>
                    <a href="football-rose.php" class="cp-btn-ghost">
                        <i class="fas fa-futbol"></i> Football Rose 2025
                    </a>
                </div>
            </div>
        </div>

    </div>
</section>

<?php include 'templates/footer.php'; ?>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({ duration: 620, once: true, offset: 55 });</script>
