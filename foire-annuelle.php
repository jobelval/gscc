<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$page_title       = 'Grande Foire Annuelle';
$page_description = 'La plus grande foire de collecte de fonds et de sensibilisation organisée par le GSCC.';

$annee        = (int)date('Y');
$date_foire   = mktime(0, 0, 0, 11, 15, $annee);
if ($date_foire < time()) {
    $annee++;
    $date_foire = mktime(0, 0, 0, 11, 15, $annee);
}
$ts_foire      = $date_foire * 1000;
$jours_restants = max(0, (int)ceil(($date_foire - time()) / 86400));

/* ── Données des éditions (base de données) ── */
try {
    $stmt = $pdo->query("SELECT * FROM foire_editions ORDER BY ordre ASC, annee DESC");
    $editions = $stmt->fetchAll();
} catch (PDOException $e) {
    $editions = [];
}
$a_venir   = array_values(array_filter($editions, fn($e) => $e['statut'] === 'a_venir'));
$terminees = array_values(array_filter($editions, fn($e) => $e['statut'] === 'termine'));

/* Formater plage de dates MySQL → texte français */
function faFmtDates(?string $debut, ?string $fin): string {
    if (!$debut) return '';
    static $m = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    $ts1 = strtotime($debut);
    $mois = $m[(int)date('n', $ts1)];
    $annee = date('Y', $ts1);
    $d1 = (int)date('j', $ts1);
    if ($fin) {
        $d2 = (int)date('j', strtotime($fin));
        return "$d1 – $d2 $mois $annee";
    }
    return "$d1 $mois $annee";
}

require_once 'templates/header.php';
?>

<style>
/* ══════════════════════════════════════════════════════
   GRANDE FOIRE ANNUELLE — Variables
══════════════════════════════════════════════════════ */
:root {
    --fa-blue:      #003399;
    --fa-blue-dk:   #001f7a;
    --fa-rose:      #D94F7A;
    --fa-rose-dk:   #B03060;
    --fa-rose-deep: #8B1A40;
    --fa-rose-pale: #FDE8EF;
    --fa-gold:      #C9933A;
    --fa-gold-pale: #FEF3E2;
    --fa-dark:      #0D1117;
    --fa-charcoal:  #1E2A35;
    --fa-gray:      #374151;
    --fa-gray-lt:   #6B7280;
    --fa-border:    #E5E9F2;
    --fa-white:     #FFFFFF;
    --fa-bg:        #F8F9FC;
    --fa-blue-bg:   #EEF3FF;
    --fa-shadow:    0 4px 20px rgba(0,51,153,.08);
    --fa-shadow-h:  0 16px 48px rgba(0,51,153,.16);
    --fa-r:         16px;
}

/* ══ HERO ══════════════════════════════════════════════ */
.fa-hero {
    background: linear-gradient(135deg, #003399 0%, #001a66 55%, #1a1a2e 100%);
    padding: 110px 0 90px;
    position: relative; overflow: hidden; text-align: center;
    color: white;
}
/* Étoiles animées */
.fa-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path fill="white" opacity=".06" d="M50 15 L61 35 L85 40 L67 55 L70 80 L50 70 L30 80 L33 55 L15 40 L39 35 Z"/></svg>') center / 100px repeat;
    animation: faStars 55s linear infinite;
    pointer-events: none;
}
@keyframes faStars { to { transform: rotate(360deg) scale(1.12); } }
/* Orbe rose en bas à droite */
.fa-hero::after {
    content: '';
    position: absolute; bottom: -20%; right: -5%;
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(217,79,122,.22), transparent 70%);
    pointer-events: none;
}
/* Vague bas */
.fa-hero-wave {
    position: absolute; bottom: -1px; left: 0; width: 100%; line-height: 0;
}
.fa-hero-wave svg { display: block; }
.fa-hero-inner { position: relative; z-index: 2; }

.fa-hero-tag {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.22);
    color: #fff; font-size: .72rem; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    padding: 6px 18px; border-radius: 30px; margin-bottom: 24px;
    backdrop-filter: blur(8px);
}
.fa-hero-tag i { color: #F2A8C0; }
.fa-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.6rem, 5.5vw, 4rem);
    color: #fff; font-weight: 700; line-height: 1.1; margin-bottom: 18px;
}
.fa-hero h1 em { color: #F2A8C0; font-style: normal; }
.fa-hero-sub {
    font-size: 1.05rem; color: #fff;
    max-width: 560px; margin: 0 auto 32px; line-height: 1.8;
}
.fa-hero-pills {
    display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;
    margin-bottom: 36px;
}
.fa-hero-pill {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.3);
    padding: 8px 18px; border-radius: 30px;
    font-size: .9rem; color: #fff; font-weight: 600;
}
.fa-hero-pill i { color: #F2A8C0; }
.fa-hero-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.fa-btn-rose {
    display: inline-flex; align-items: center; gap: 9px;
    background: var(--fa-rose); color: white;
    padding: 14px 30px; border-radius: 30px;
    font-weight: 700; font-size: .95rem; text-decoration: none;
    box-shadow: 0 4px 18px rgba(217,79,122,.45); transition: all .25s;
}
.fa-btn-rose:hover { background: var(--fa-rose-dk); color: white; transform: translateY(-2px); box-shadow: 0 8px 26px rgba(217,79,122,.55); }
.fa-btn-ghost {
    display: inline-flex; align-items: center; gap: 9px;
    background: transparent; color: rgba(255,255,255,.88);
    border: 1.5px solid rgba(255,255,255,.32);
    padding: 14px 26px; border-radius: 30px;
    font-weight: 500; font-size: .95rem; text-decoration: none; transition: all .25s;
}
.fa-btn-ghost:hover { border-color: #fff; color: #fff; background: rgba(255,255,255,.08); }

/* ══ STATS ══════════════════════════════════════════════ */
.fa-stats { background: var(--fa-white); border-bottom: 1px solid var(--fa-border); box-shadow: 0 2px 12px rgba(0,51,153,.05); }
.fa-stats-inner { display: grid; grid-template-columns: repeat(4,1fr); max-width: 1140px; margin: 0 auto; padding: 0 28px; }
.fa-st { padding: 26px 16px; text-align: center; border-right: 1px solid var(--fa-border); transition: background .2s; }
.fa-st:last-child { border-right: none; }
.fa-st:hover { background: var(--fa-rose-pale); }
.fa-st-ico { font-size: 16px; color: var(--fa-rose); margin-bottom: 7px; }
.fa-st-n { font-family: 'Playfair Display', serif; font-size: 1.85rem; font-weight: 700; color: var(--fa-blue); line-height: 1; margin-bottom: 4px; }
.fa-st-l { font-size: .7rem; color: var(--fa-charcoal); text-transform: uppercase; letter-spacing: 1.2px; font-weight: 600; }

/* ══ LAYOUT HELPERS ══════════════════════════════════════ */
.fa-section { padding: 80px 0; background: var(--fa-bg); }
.fa-section.alt { background: var(--fa-white); }
.fa-label {
    display: inline-flex; align-items: center; gap: 7px;
    font-size: .7rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
    color: var(--fa-rose-dk); background: var(--fa-rose-pale);
    border: 1px solid #F5B8CC; padding: 5px 14px; border-radius: 30px; margin-bottom: 12px;
}
.fa-title { font-family: 'Playfair Display', serif; font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 700; color: var(--fa-blue); margin-bottom: 10px; line-height: 1.2; }
.fa-sub { font-size: .97rem; color: var(--fa-gray); max-width: 560px; line-height: 1.8; }
.fa-head { margin-bottom: 50px; }
.fa-head.center { text-align: center; }
.fa-head.center .fa-sub { margin: 0 auto; }

/* ══ PRÉSENTATION ════════════════════════════════════════ */
.fa-pres-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
.fa-pres-img { border-radius: 20px; overflow: hidden; box-shadow: var(--fa-shadow-h); }
.fa-pres-img img { width: 100%; height: 420px; object-fit: cover; display: block; }
.fa-pres-line { width: 52px; height: 4px; background: linear-gradient(90deg, var(--fa-blue), var(--fa-rose)); border-radius: 2px; margin-bottom: 22px; }
.fa-pres-text p { color: var(--fa-gray); line-height: 1.8; margin-bottom: 14px; font-size: .95rem; }
.fa-info-list { list-style: none; padding: 0; margin: 20px 0 0; }
.fa-info-list li { display: flex; align-items: flex-start; gap: 12px; padding: 10px 0; border-bottom: 1px solid #F0F2F5; font-size: .92rem; color: var(--fa-charcoal); }
.fa-info-list li:last-child { border-bottom: none; }
.fa-info-list i { color: var(--fa-rose); margin-top: 3px; width: 16px; flex-shrink: 0; }

/* ══ POURQUOI PARTICIPER ════════════════════════════════ */
.fa-features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.fa-feat-card {
    background: var(--fa-white); border-radius: var(--fa-r); padding: 38px 26px;
    text-align: center; box-shadow: var(--fa-shadow);
    border: 1px solid transparent; transition: all .3s;
}
.fa-feat-card:hover { transform: translateY(-7px); box-shadow: var(--fa-shadow-h); border-color: var(--fa-rose-pale); }
.fa-feat-icon {
    width: 78px; height: 78px; border-radius: 50%;
    background: var(--fa-rose-pale); display: flex; align-items: center;
    justify-content: center; margin: 0 auto 20px;
    color: var(--fa-rose); font-size: 2rem; transition: all .3s;
}
.fa-feat-card:hover .fa-feat-icon { background: var(--fa-rose); color: white; }
.fa-feat-card h3 { font-family: 'Playfair Display', serif; color: var(--fa-charcoal); margin-bottom: 10px; font-size: 1.1rem; }
.fa-feat-card p { color: var(--fa-gray-lt); line-height: 1.7; font-size: .9rem; margin: 0; }

/* ══ PROGRAMME ═══════════════════════════════════════════ */
.fa-prog-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
.fa-prog-card {
    background: var(--fa-bg); border-radius: var(--fa-r); padding: 30px;
    border-left: 4px solid var(--fa-rose); transition: all .3s;
}
.fa-prog-card:hover { background: white; box-shadow: var(--fa-shadow-h); }
.fa-prog-badge {
    display: inline-block; background: var(--fa-blue); color: white;
    padding: 4px 16px; border-radius: 20px;
    font-size: .78rem; font-weight: 700; margin-bottom: 12px;
}
.fa-prog-badge.rose { background: var(--fa-rose); }
.fa-prog-card h3 { font-family: 'Playfair Display', serif; color: var(--fa-charcoal); margin-bottom: 14px; font-size: 1.05rem; }
.fa-prog-list { list-style: none; padding: 0; margin: 0; }
.fa-prog-list li { display: flex; align-items: center; gap: 10px; padding: 8px 0; color: var(--fa-charcoal); font-size: .9rem; border-bottom: 1px solid var(--fa-border); }
.fa-prog-list li:last-child { border-bottom: none; }
.fa-prog-list i { color: var(--fa-rose); font-size: .78rem; width: 14px; flex-shrink: 0; }

/* ══ ÉDITIONS ════════════════════════════════════════════ */
.fa-editions { padding: 80px 0; background: var(--fa-bg); }

.fa-ed-tabs {
    display: flex; gap: 4px;
    background: var(--fa-white); border: 1px solid var(--fa-border);
    padding: 4px; border-radius: 14px; width: fit-content;
    margin-bottom: 48px; box-shadow: var(--fa-shadow);
}
.fa-ed-tab {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 22px; border-radius: 10px;
    font-size: .87rem; font-weight: 600; cursor: pointer;
    background: transparent; color: var(--fa-charcoal);
    border: none; transition: all .22s;
}
.fa-ed-tab.active { background: var(--fa-rose); color: #fff; box-shadow: 0 3px 12px rgba(176,48,96,.28); }
.fa-ed-tab i { font-size: .75rem; }

.fa-ed-panel { display: none; }
.fa-ed-panel.active { display: block; }

/* À venir — carte premium */
.fa-ed-upcoming {
    background: var(--fa-white); border-radius: 24px;
    border: 1px solid var(--fa-border); box-shadow: var(--fa-shadow-h); overflow: hidden;
}
.fa-ed-up-top {
    background: linear-gradient(135deg, #003399 0%, #001a66 50%, #1a1a2e 100%);
    position: relative; overflow: hidden; padding: 52px 48px;
    display: grid; grid-template-columns: 1fr auto; gap: 40px; align-items: center;
}
.fa-ed-up-top::before {
    content: '';
    position: absolute; inset: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path fill="white" opacity=".05" d="M50 15 L61 35 L85 40 L67 55 L70 80 L50 70 L30 80 L33 55 L15 40 L39 35 Z"/></svg>') center / 80px repeat;
    pointer-events: none;
}
.fa-ed-up-top::after {
    content: '';
    position: absolute; bottom: -30%; right: 0;
    width: 380px; height: 380px;
    background: radial-gradient(circle, rgba(217,79,122,.25), transparent 70%);
    pointer-events: none;
}
.fa-ed-up-left { position: relative; z-index: 2; }
.fa-ed-up-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(217,79,122,.25); border: 1px solid rgba(217,79,122,.4);
    color: #F2A8C0; font-size: .7rem; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; padding: 5px 14px; border-radius: 20px; margin-bottom: 14px;
}
.fa-ed-up-top h3 { font-family: 'Playfair Display', serif; font-size: clamp(1.5rem, 2.5vw, 2.1rem); color: #fff; font-weight: 700; margin-bottom: 10px; line-height: 1.2; }
.fa-ed-up-top p { color: rgba(255,255,255,.95); font-size: .95rem; line-height: 1.75; max-width: 500px; }

.fa-ed-up-right { position: relative; z-index: 2; flex-shrink: 0; }
.fa-ed-up-cd {
    background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18);
    border-radius: 16px; padding: 22px 26px; text-align: center;
    backdrop-filter: blur(10px);
}
.fa-ed-up-cd .cd-lbl { font-size: .7rem; color: rgba(255,255,255,.55); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 12px; }
.fa-cd-row { display: flex; gap: 4px; align-items: center; }
.fa-cd-unit { text-align: center; padding: 6px 10px; }
.fa-cd-unit .n { font-family: 'Playfair Display', serif; font-size: 1.8rem; font-weight: 700; color: #fff; line-height: 1; }
.fa-cd-unit .l { font-size: .62rem; color: rgba(255,255,255,.88); letter-spacing: 1.2px; text-transform: uppercase; margin-top: 3px; }
.fa-cd-sep { font-size: 1.4rem; color: rgba(255,255,255,.25); padding-bottom: 8px; }

.fa-ed-up-body { padding: 30px 48px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; border-top: 1px solid var(--fa-border); }
.fa-ed-up-info { display: flex; gap: 28px; flex-wrap: wrap; }
.fa-ed-up-info-item { display: flex; align-items: center; gap: 8px; font-size: .9rem; color: var(--fa-charcoal); font-weight: 500; }
.fa-ed-up-info-item i { color: var(--fa-rose); font-size: .85rem; }

/* Grille terminées */
.fa-ed-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 22px; }
.fa-ed-card {
    background: var(--fa-white); border-radius: var(--fa-r);
    border: 1px solid var(--fa-border); overflow: hidden;
    box-shadow: var(--fa-shadow); transition: transform .28s, box-shadow .28s;
}
.fa-ed-card:hover { transform: translateY(-6px); box-shadow: var(--fa-shadow-h); }
.fa-ed-card-top {
    background: linear-gradient(135deg, var(--fa-rose-deep) 0%, var(--fa-rose-dk) 50%, var(--fa-rose) 100%);
    padding: 22px 24px; position: relative; overflow: hidden;
}
.fa-ed-card-top::before {
    content: '';
    position: absolute; inset: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path fill="white" opacity=".06" d="M50 15 L61 35 L85 40 L67 55 L70 80 L50 70 L30 80 L33 55 L15 40 L39 35 Z"/></svg>') center / 60px repeat;
    pointer-events: none;
}
.fa-ed-card-year { font-family: 'Playfair Display', serif; font-size: 2.2rem; font-weight: 700; color: #fff; line-height: 1; position: relative; z-index: 1; }
.fa-ed-card-theme { font-size: .82rem; color: #fff; margin-top: 4px; position: relative; z-index: 1; font-weight: 500; }
.fa-ed-card-status {
    position: absolute; top: 14px; right: 14px; z-index: 2;
    background: rgba(255,255,255,.2); border: 1px solid rgba(255,255,255,.3);
    color: #fff; font-size: .68rem; font-weight: 700; letter-spacing: .5px; text-transform: uppercase;
    padding: 4px 11px; border-radius: 20px; display: flex; align-items: center; gap: 5px;
    backdrop-filter: blur(6px);
}
.fa-ed-card-body { padding: 20px 24px; }
.fa-ed-card-dates { display: flex; align-items: center; gap: 6px; font-size: .82rem; color: var(--fa-charcoal); margin-bottom: 12px; font-weight: 500; }
.fa-ed-card-dates i { color: var(--fa-rose); font-size: .75rem; }
.fa-ed-card-stats { display: grid; grid-template-columns: repeat(2,1fr); gap: 8px; margin-bottom: 14px; }
.fa-ed-stat { text-align: center; background: var(--fa-rose-pale); border-radius: 10px; padding: 10px 6px; }
.fa-ed-stat .n { font-weight: 700; font-size: .9rem; color: var(--fa-rose-dk); display: block; }
.fa-ed-stat .l { font-size: .68rem; color: var(--fa-charcoal); margin-top: 2px; font-weight: 500; }
.fa-ed-card-desc { font-size: .88rem; color: var(--fa-charcoal); line-height: 1.7; }

/* ══ EXPOSANTS ═══════════════════════════════════════════ */
.fa-exp-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 20px; }
.fa-exp-card {
    background: var(--fa-white); border-radius: var(--fa-r); padding: 26px 16px;
    text-align: center; box-shadow: var(--fa-shadow);
    border: 1px solid transparent; transition: all .3s;
}
.fa-exp-card:hover { transform: translateY(-5px); box-shadow: var(--fa-shadow-h); border-color: var(--fa-rose); }
.fa-exp-logo {
    width: 68px; height: 68px; border-radius: 50%;
    background: var(--fa-rose-pale); margin: 0 auto 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; color: var(--fa-rose); transition: all .3s;
}
.fa-exp-card:hover .fa-exp-logo { background: var(--fa-rose); color: white; }
.fa-exp-card h4 { color: var(--fa-charcoal); margin-bottom: 4px; font-size: .9rem; font-weight: 700; }
.fa-exp-card p { color: var(--fa-gray-lt); font-size: .78rem; margin: 0; }
.fa-exp-cta { text-align: center; margin-top: 42px; background: var(--fa-white); border-radius: var(--fa-r); padding: 34px 28px; box-shadow: var(--fa-shadow); }
.fa-exp-cta h3 { font-family: 'Playfair Display', serif; color: var(--fa-charcoal); font-size: 1.25rem; margin-bottom: 8px; }
.fa-exp-cta p { color: var(--fa-gray); margin-bottom: 18px; font-size: .92rem; }
.fa-btn-blue {
    display: inline-flex; align-items: center; gap: 9px;
    background: var(--fa-blue); color: white;
    padding: 13px 28px; border-radius: 30px;
    font-size: .92rem; font-weight: 700; text-decoration: none;
    box-shadow: 0 4px 16px rgba(0,51,153,.25); transition: all .25s;
}
.fa-btn-blue:hover { background: var(--fa-blue-dk); color: white; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,51,153,.35); }

/* ══ CTA FINAL ═══════════════════════════════════════════ */
.fa-cta {
    background: linear-gradient(135deg, var(--fa-rose-deep) 0%, var(--fa-rose-dk) 45%, var(--fa-rose) 100%);
    padding: 90px 0; text-align: center; position: relative; overflow: hidden;
}
.fa-cta::before {
    content: '';
    position: absolute; inset: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path fill="white" opacity=".04" d="M50 15 L61 35 L85 40 L67 55 L70 80 L50 70 L30 80 L33 55 L15 40 L39 35 Z"/></svg>') center / 90px repeat;
    animation: faStars 60s linear infinite;
    pointer-events: none;
}
.fa-cta-inner { position: relative; z-index: 2; }
.fa-cta-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
    color: rgba(255,255,255,.92); font-size: .7rem; font-weight: 700;
    letter-spacing: 1.5px; text-transform: uppercase;
    padding: 5px 14px; border-radius: 30px; margin-bottom: 18px;
}
.fa-cta h2 { font-family: 'Playfair Display', serif; font-size: clamp(1.8rem, 3vw, 2.6rem); font-weight: 700; color: #fff; margin-bottom: 12px; }
.fa-cta > div > p { font-size: .97rem; color: #fff !important; line-height: 1.8; max-width: 520px; margin: 0 auto 34px; }

/* Force blanc sur tous les textes des sections foncées */
.fa-hero h1, .fa-hero p, .fa-hero span, .fa-hero em,
.fa-cta h2, .fa-cta p, .fa-cta span, .fa-cta strong,
.fa-ed-up-top h3, .fa-ed-up-top p, .fa-ed-up-top span,
.fa-ed-up-cd *, .fa-cd-unit *, .fa-cd-row *,
.fa-hero-tag, .fa-hero-sub, .fa-hero-pill,
.fa-cta-badge { color: #fff !important; }
.fa-cta-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.fa-btn-white {
    display: inline-flex; align-items: center; gap: 9px;
    background: #fff; color: var(--fa-rose-dk);
    padding: 14px 30px; border-radius: 30px;
    text-decoration: none; font-weight: 700; font-size: .95rem;
    box-shadow: 0 4px 20px rgba(0,0,0,.18); transition: all .3s;
}
.fa-btn-white:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,.26); }
.fa-btn-ghost-white {
    display: inline-flex; align-items: center; gap: 9px;
    background: transparent; color: rgba(255,255,255,.9);
    border: 1.5px solid rgba(255,255,255,.35);
    padding: 14px 26px; border-radius: 30px;
    text-decoration: none; font-weight: 500; font-size: .95rem; transition: all .3s;
}
.fa-btn-ghost-white:hover { border-color: #fff; color: #fff; background: rgba(255,255,255,.1); }

/* ══ RESPONSIVE ══════════════════════════════════════════ */
@media (max-width: 960px) {
    .fa-stats-inner { grid-template-columns: repeat(2,1fr); }
    .fa-st:nth-child(2) { border-right: none; }
    .fa-st:nth-child(3) { border-top: 1px solid var(--fa-border); }
    .fa-exp-grid { grid-template-columns: repeat(2,1fr); }
    .fa-ed-up-top { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .fa-pres-grid, .fa-features-grid, .fa-prog-grid { grid-template-columns: 1fr; }
    .fa-ed-grid { grid-template-columns: 1fr; }
    .fa-ed-up-top { padding: 36px 26px; }
    .fa-ed-up-body { padding: 24px 26px; flex-direction: column; align-items: flex-start; }
}
@media (max-width: 540px) {
    .fa-exp-grid { grid-template-columns: repeat(2,1fr); }
    .fa-cd-row { gap: 2px; }
}

/* ══ CARD IMAGE ══════════════════════════════════════════ */
.fa-ed-card-img { width: 100%; height: 175px; overflow: hidden; flex-shrink: 0; }
.fa-ed-card-img img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .4s; }
.fa-ed-card:hover .fa-ed-card-img img { transform: scale(1.05); }

/* ══ MODAL ÉDITION ═══════════════════════════════════════ */
.fa-modal-overlay {
    display: none; position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,.65); backdrop-filter: blur(4px);
    align-items: flex-start; justify-content: center;
    padding: 28px 20px; overflow-y: auto;
}
.fa-modal-overlay.open { display: flex; }
.fa-modal-box {
    background: #fff; border-radius: 20px; max-width: 680px; width: 100%;
    display: flex; flex-direction: column; overflow: hidden;
    box-shadow: 0 24px 80px rgba(0,0,0,.38); position: relative;
    animation: faModalIn .25s ease; margin: auto;
}
@keyframes faModalIn { from { opacity:0; transform: scale(.94) translateY(16px); } to { opacity:1; transform: none; } }
.fa-modal-close {
    position: absolute; top: 14px; right: 16px; z-index: 10;
    background: rgba(0,0,0,.42); border: none; color: #fff;
    width: 34px; height: 34px; border-radius: 50%;
    font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: background .2s;
}
.fa-modal-close:hover { background: rgba(0,0,0,.68); }
.fa-modal-img-wrap { flex-shrink: 0; height: 230px; overflow: hidden; }
.fa-modal-img-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }
.fa-modal-img-placeholder {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, var(--fa-rose-deep) 0%, var(--fa-rose-dk) 50%, var(--fa-rose) 100%);
    display: flex; align-items: center; justify-content: center;
}
.fa-modal-img-placeholder i { font-size: 3rem; color: rgba(255,255,255,.22); }
.fa-modal-body-content { padding: 26px 30px 32px; }
.fa-modal-badge-done {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(176,48,96,.1); color: var(--fa-rose-dk);
    border: 1px solid rgba(176,48,96,.25);
    font-size: .7rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;
    padding: 4px 12px; border-radius: 20px; margin-bottom: 12px;
}
.fa-modal-title {
    font-family: 'Playfair Display', serif; font-size: 1.7rem; font-weight: 700;
    color: var(--fa-blue); margin-bottom: 4px; line-height: 1.15;
}
.fa-modal-theme-txt { font-size: .92rem; color: var(--fa-gray); margin-bottom: 18px; font-style: italic; }
.fa-modal-stats-row { display: grid; grid-template-columns: repeat(2,1fr); gap: 10px; margin-bottom: 18px; }
.fa-modal-stat-box { background: var(--fa-rose-pale); border-radius: 10px; padding: 13px 10px; text-align: center; }
.fa-modal-stat-box .n { font-weight: 700; font-size: 1.05rem; color: var(--fa-rose-dk); display: block; }
.fa-modal-stat-box .l { font-size: .72rem; color: var(--fa-charcoal); font-weight: 500; margin-top: 2px; }
.fa-modal-date-row { display: flex; align-items: center; gap: 8px; font-size: .87rem; color: var(--fa-charcoal); margin-bottom: 18px; }
.fa-modal-date-row i { color: var(--fa-rose); }
.fa-modal-desc-text { font-size: .93rem; color: var(--fa-gray); line-height: 1.88; white-space: pre-line; }
.fa-modal-gallery-wrap { margin-top: 24px; }
.fa-modal-gallery-lbl { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--fa-gray-lt); margin-bottom: 10px; }
.fa-modal-gallery-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 8px; }
.fa-modal-gallery-grid img { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 8px; cursor: zoom-in; transition: opacity .2s; }
.fa-modal-gallery-grid img:hover { opacity: .82; }
@media (max-width: 540px) {
    .fa-modal-box { border-radius: 16px; }
    .fa-modal-img-wrap { height: 180px; }
    .fa-modal-body-content { padding: 20px 20px 26px; }
    .fa-modal-gallery-grid { grid-template-columns: repeat(2,1fr); }
}
</style>

<!-- ══ HERO ══════════════════════════════════════════════ -->
<div class="fa-hero">
    <div class="container">
        <div class="fa-hero-inner" data-aos="fade-up">
            <div class="fa-hero-tag"><i class="fas fa-star"></i> GSCC — Événement annuel phare</div>
            <h1>Grande Foire <em>Annuelle</em></h1>
            <p class="fa-hero-sub">
                Le plus grand rassemblement solidaire de l'année — trois jours de festivités,
                de partage et d'action pour la lutte contre le cancer en Haïti.
            </p>
            <div class="fa-hero-pills">
                <span class="fa-hero-pill"><i class="far fa-calendar-alt"></i> 13 – 15 novembre <?= $annee ?></span>
                <span class="fa-hero-pill"><i class="fas fa-map-marker-alt"></i> Place Saint-Pierre, Port-au-Prince</span>
                <span class="fa-hero-pill"><i class="far fa-clock"></i> 10h – 20h (nocturne samedi)</span>
            </div>
            <div class="fa-hero-btns">
                <a href="contact.php?sujet=billet-foire" class="fa-btn-rose"><i class="fas fa-ticket-alt"></i> Obtenir un billet</a>
                <a href="#editions" class="fa-btn-ghost"><i class="fas fa-history"></i> Voir les éditions</a>
            </div>
        </div>
    </div>
    <div class="fa-hero-wave">
        <svg viewBox="0 0 1440 52" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path fill="#F8F9FC" d="M0,52 C480,0 960,0 1440,52 L1440,52 L0,52 Z"/>
        </svg>
    </div>
</div>

<!-- ══ STATS ══════════════════════════════════════════════ -->
<div class="fa-stats">
    <div class="fa-stats-inner">
        <div class="fa-st" data-aos="fade-up">
            <div class="fa-st-ico"><i class="fas fa-users"></i></div>
            <div class="fa-st-n">5 000+</div>
            <div class="fa-st-l">Visiteurs / édition</div>
        </div>
        <div class="fa-st" data-aos="fade-up" data-aos-delay="60">
            <div class="fa-st-ico"><i class="fas fa-store"></i></div>
            <div class="fa-st-n">50+</div>
            <div class="fa-st-l">Exposants</div>
        </div>
        <div class="fa-st" data-aos="fade-up" data-aos-delay="120">
            <div class="fa-st-ico"><i class="fas fa-calendar-alt"></i></div>
            <div class="fa-st-n">3 jours</div>
            <div class="fa-st-l">De festivités</div>
        </div>
        <div class="fa-st" data-aos="fade-up" data-aos-delay="180">
            <div class="fa-st-ico"><i class="fas fa-hand-holding-heart"></i></div>
            <div class="fa-st-n">100%</div>
            <div class="fa-st-l">Bénéfices reversés</div>
        </div>
    </div>
</div>

<!-- ══ PRÉSENTATION ═══════════════════════════════════════ -->
<section class="fa-section alt">
    <div class="container">
        <div class="fa-pres-grid">
            <div class="fa-pres-img" data-aos="fade-right">
                <img src="uploads/galerie/IMG_3945.jpg" alt="Grande Foire Annuelle GSCC" loading="lazy"
                     onerror="this.src='images/site/image3.jpg'">
            </div>
            <div data-aos="fade-left">
                <div class="fa-label"><i class="fas fa-info-circle"></i> À propos de l'événement</div>
                <h2 class="fa-title">Un événement unique en Haïti</h2>
                <div class="fa-pres-line"></div>
                <div class="fa-pres-text">
                    <p>La Grande Foire Annuelle du GSCC est devenue au fil des années le rendez-vous incontournable de la solidarité en Haïti. Chaque année, des milliers de visiteurs se rassemblent pour soutenir notre cause et célébrer la vie.</p>
                    <p>Au programme : stands d'artisanat local, animations culturelles, conférences santé, espace restauration, jeux pour enfants, et bien plus encore. Tous les bénéfices sont reversés à nos programmes d'aide aux patients.</p>
                </div>
                <ul class="fa-info-list">
                    <li><i class="far fa-calendar-alt"></i><span><strong>Date :</strong> 13 – 15 novembre <?= $annee ?></span></li>
                    <li><i class="fas fa-map-marker-alt"></i><span><strong>Lieu :</strong> Place Saint-Pierre, Port-au-Prince</span></li>
                    <li><i class="far fa-clock"></i><span><strong>Horaires :</strong> 10h – 20h (samedi nocturne jusqu'à 23h)</span></li>
                    <li><i class="fas fa-ticket-alt"></i><span><strong>Entrée :</strong> Sur billet — contactez le GSCC pour réserver</span></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ══ POURQUOI PARTICIPER ════════════════════════════════ -->
<section class="fa-section">
    <div class="container">
        <div class="fa-head center" data-aos="fade-up">
            <div class="fa-label"><i class="fas fa-heart"></i> Raisons de nous rejoindre</div>
            <h2 class="fa-title">Pourquoi participer ?</h2>
            <p class="fa-sub">Trois bonnes raisons de rejoindre le plus grand événement solidaire de l'année.</p>
        </div>
        <div class="fa-features-grid">
            <div class="fa-feat-card" data-aos="fade-up">
                <div class="fa-feat-icon"><i class="fas fa-hand-holding-heart"></i></div>
                <h3>Agir pour la cause</h3>
                <p>Chaque achat, chaque participation contribue directement à financer nos actions de soutien aux patients atteints de cancer.</p>
            </div>
            <div class="fa-feat-card" data-aos="fade-up" data-aos-delay="100">
                <div class="fa-feat-icon"><i class="fas fa-gift"></i></div>
                <h3>Découvertes &amp; bons plans</h3>
                <p>Plus de 50 exposants proposent artisanat, produits locaux, gastronomie haïtienne et créations uniques introuvables ailleurs.</p>
            </div>
            <div class="fa-feat-card" data-aos="fade-up" data-aos-delay="200">
                <div class="fa-feat-icon"><i class="fas fa-users"></i></div>
                <h3>Moment de partage</h3>
                <p>Une occasion unique de rencontrer notre communauté, d'échanger avec des personnes engagées et de célébrer ensemble.</p>
            </div>
        </div>
    </div>
</section>

<!-- ══ ÉDITIONS ═══════════════════════════════════════════ -->
<section class="fa-editions" id="editions">
    <div class="container">
        <div class="fa-head" data-aos="fade-up">
            <div class="fa-label"><i class="fas fa-calendar-check"></i> Historique</div>
            <h2 class="fa-title">Les éditions de la Foire</h2>
            <p class="fa-sub">Revivez les grandes éditions passées et découvrez les événements à venir.</p>
        </div>

        <div class="fa-ed-tabs" data-aos="fade-up">
            <button class="fa-ed-tab active" onclick="faShowTab('a_venir',this)">
                <i class="fas fa-calendar-plus"></i> À venir
            </button>
            <button class="fa-ed-tab" onclick="faShowTab('termine',this)">
                <i class="fas fa-check-circle"></i> Terminées
            </button>
        </div>

        <!-- Panneau : À venir -->
        <div class="fa-ed-panel active" id="fa-panel-a_venir">
            <?php foreach ($a_venir as $ed): ?>
            <div class="fa-ed-upcoming" data-aos="fade-up">
                <div class="fa-ed-up-top">
                    <div class="fa-ed-up-left">
                        <div class="fa-ed-up-badge"><i class="fas fa-star"></i> Édition <?= $ed['annee'] ?> — À venir</div>
                        <h3>Grande Foire Annuelle <?= $ed['annee'] ?></h3>
                        <p style="margin-top:10px;"><?= htmlspecialchars($ed['description'] ?? '') ?></p>
                    </div>
                    <div class="fa-ed-up-right">
                        <div class="fa-ed-up-cd">
                            <div class="cd-lbl">Compte à rebours</div>
                            <div class="fa-cd-row">
                                <div class="fa-cd-unit"><div class="n" id="fa-j"><?= $jours_restants ?></div><div class="l">Jours</div></div>
                                <span class="fa-cd-sep">:</span>
                                <div class="fa-cd-unit"><div class="n" id="fa-h">00</div><div class="l">H</div></div>
                                <span class="fa-cd-sep">:</span>
                                <div class="fa-cd-unit"><div class="n" id="fa-m">00</div><div class="l">Min</div></div>
                                <span class="fa-cd-sep">:</span>
                                <div class="fa-cd-unit"><div class="n" id="fa-s">00</div><div class="l">Sec</div></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="fa-ed-up-body">
                    <div class="fa-ed-up-info">
                        <div class="fa-ed-up-info-item"><i class="far fa-calendar-alt"></i><span><?= htmlspecialchars(faFmtDates($ed['date_debut'] ?? null, $ed['date_fin'] ?? null)) ?></span></div>
                        <div class="fa-ed-up-info-item"><i class="fas fa-map-marker-alt"></i><span>Place Saint-Pierre, Port-au-Prince</span></div>
                        <div class="fa-ed-up-info-item"><i class="fas fa-store"></i><span><?= htmlspecialchars($ed['stands'] ?? '') ?> exposants attendus</span></div>
                        <div class="fa-ed-up-info-item"><i class="fas fa-ticket-alt"></i><span><?= htmlspecialchars($ed['billet'] ?? '') ?></span></div>
                    </div>
                    <a href="contact.php?sujet=billet-foire" class="fa-btn-rose"><i class="fas fa-ticket-alt"></i> Réserver un billet</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Panneau : Terminées -->
        <div class="fa-ed-panel" id="fa-panel-termine">
            <div class="fa-ed-grid">
                <?php foreach ($terminees as $i => $ed): ?>
                <div class="fa-ed-card" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 70 ?>">
                    <?php if (!empty($ed['image'])): ?>
                    <div class="fa-ed-card-img">
                        <img src="<?= htmlspecialchars($ed['image']) ?>" alt="Édition <?= $ed['annee'] ?>" loading="lazy">
                    </div>
                    <?php endif; ?>
                    <div class="fa-ed-card-top">
                        <div class="fa-ed-card-status"><i class="fas fa-check"></i> Terminée</div>
                        <div class="fa-ed-card-year"><?= $ed['annee'] ?></div>
                        <div class="fa-ed-card-theme"><?= htmlspecialchars($ed['theme'] ?? '') ?></div>
                    </div>
                    <?php
                    $desc  = $ed['description'] ?? '';
                    $short = mb_strlen($desc) > 130 ? mb_substr($desc, 0, 130) . '…' : $desc;
                    ?>
                    <div class="fa-ed-card-body">
                        <div class="fa-ed-card-dates"><i class="far fa-calendar-alt"></i><?= htmlspecialchars(faFmtDates($ed['date_debut'] ?? null, $ed['date_fin'] ?? null)) ?></div>
                        <div class="fa-ed-card-stats">
                            <div class="fa-ed-stat"><span class="n"><?= htmlspecialchars($ed['visiteurs'] ?? '—') ?></span><div class="l">Visiteurs</div></div>
                            <div class="fa-ed-stat"><span class="n"><?= htmlspecialchars($ed['stands'] ?? '—') ?></span><div class="l">Exposants</div></div>
                        </div>
                        <p class="fa-ed-card-desc"><?= htmlspecialchars($short) ?></p>
                        <?php if (mb_strlen($desc) > 130): ?>
                        <button class="mc-ed-read-more" style="color:var(--fa-rose-dk);" onclick="faOpenModal(<?= (int)$ed['id'] ?>)">
                            Lire la suite <i class="fas fa-arrow-right" style="font-size:.7rem;"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ══ EXPOSANTS ══════════════════════════════════════════ -->
<section class="fa-section alt">
    <div class="container">
        <div class="fa-head center" data-aos="fade-up">
            <div class="fa-label"><i class="fas fa-store"></i> Ils seront présents</div>
            <h2 class="fa-title">Nos exposants <?= $annee ?></h2>
            <p class="fa-sub">Plus de 50 exposants réunis pour 3 jours de découvertes et de solidarité.</p>
        </div>
        <div class="fa-exp-grid">
            <?php
            $exposants = [
                ['icon'=>'fa-store',     'nom'=>'Artisanat d\'Haïti',     'cat'=>'Produits artisanaux'],
                ['icon'=>'fa-utensils',  'nom'=>'Saveurs Créoles',        'cat'=>'Gastronomie locale'],
                ['icon'=>'fa-tshirt',    'nom'=>'Mode Éthique',           'cat'=>'Couture & accessoires'],
                ['icon'=>'fa-book',      'nom'=>'Librairie des Lumières', 'cat'=>'Livres & éducation'],
                ['icon'=>'fa-leaf',      'nom'=>'Bio Haïti',              'cat'=>'Produits naturels'],
                ['icon'=>'fa-heartbeat', 'nom'=>'Santé Plus',             'cat'=>'Matériel médical'],
                ['icon'=>'fa-palette',   'nom'=>'Artistes en Herbe',      'cat'=>'Œuvres d\'art'],
                ['icon'=>'fa-gem',       'nom'=>'Créations Précieuses',   'cat'=>'Bijoux artisanaux'],
            ];
            foreach ($exposants as $i => $ex):
            ?>
            <div class="fa-exp-card" data-aos="zoom-in" data-aos-delay="<?= ($i % 4) * 60 ?>">
                <div class="fa-exp-logo"><i class="fas <?= $ex['icon'] ?>"></i></div>
                <h4><?= htmlspecialchars($ex['nom']) ?></h4>
                <p><?= htmlspecialchars($ex['cat']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="fa-exp-cta" data-aos="fade-up">
            <h3>Vous souhaitez exposer à la foire ?</h3>
            <p>Rejoignez nos exposants, faites connaître votre activité et soutenez la cause. Contactez-nous pour réserver votre stand.</p>
            <a href="contact.php?sujet=devenir-exposant" class="fa-btn-blue"><i class="fas fa-store-alt"></i> Devenir exposant</a>
        </div>
    </div>
</section>

<!-- ══ CTA FINAL ══════════════════════════════════════════ -->
<section class="fa-cta">
    <div class="container fa-cta-inner">
        <div class="fa-cta-badge" data-aos="fade-down"><i class="fas fa-star"></i> <?= $jours_restants ?> jours avant la foire</div>
        <h2 data-aos="fade-up">Grande Foire Annuelle <?= $annee ?></h2>
        <div>
            <p data-aos="fade-up" data-aos-delay="80">Trois jours de festivités, de solidarité et d'espoir. Rejoignez des milliers de personnes pour la plus belle fête solidaire d'Haïti.</p>
            <div class="fa-cta-btns" data-aos="fade-up" data-aos-delay="160">
                <a href="contact.php?sujet=billet-foire" class="fa-btn-white"><i class="fas fa-ticket-alt"></i> Obtenir un billet</a>
                <a href="faire-un-don.php" class="fa-btn-ghost-white"><i class="fas fa-heart"></i> Faire un don</a>
            </div>
        </div>
    </div>
</section>

<!-- ══ MODAL ÉDITION ═════════════════════════════════════ -->
<div id="fa-modal-overlay" class="fa-modal-overlay" onclick="faCloseModal(event)">
    <div class="fa-modal-box" onclick="event.stopPropagation()">
        <button class="fa-modal-close" onclick="faCloseModal()" aria-label="Fermer">&times;</button>
        <div class="fa-modal-img-wrap" id="fa-modal-img-wrap">
            <div class="fa-modal-img-placeholder"><i class="fas fa-star"></i></div>
        </div>
        <div class="fa-modal-body-content">
            <div class="fa-modal-badge-done"><i class="fas fa-check"></i> Édition Terminée</div>
            <div class="fa-modal-title" id="fa-modal-title"></div>
            <div class="fa-modal-theme-txt" id="fa-modal-theme"></div>
            <div class="fa-modal-stats-row" id="fa-modal-stats"></div>
            <div class="fa-modal-date-row" id="fa-modal-date"></div>
            <div class="fa-modal-desc-text" id="fa-modal-desc"></div>
            <div class="fa-modal-gallery-wrap" id="fa-modal-gallery" style="display:none;">
                <div class="fa-modal-gallery-lbl"><i class="fas fa-images" style="margin-right:6px;"></i>Galerie photos</div>
                <div class="fa-modal-gallery-grid" id="fa-modal-gallery-grid"></div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>

<?php
$terminees_js = [];
foreach ($terminees as $ed) {
    $terminees_js[(int)$ed['id']] = [
        'id'                   => (int)$ed['id'],
        'annee'                => (int)$ed['annee'],
        'theme'                => $ed['theme'] ?? '',
        'dates'                => faFmtDates($ed['date_debut'] ?? null, $ed['date_fin'] ?? null),
        'visiteurs'            => $ed['visiteurs'] ?? '',
        'stands'               => $ed['stands'] ?? '',
        'description_complete' => $ed['description'] ?? '',
        'image_hero'           => $ed['image'] ?? '',
        'galerie'              => [],
    ];
}
?>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init({ duration: 700, once: true, offset: 55 });

var FA_EDITIONS = <?= json_encode($terminees_js, JSON_HEX_TAG | JSON_HEX_AMP) ?>;

/* Compte à rebours foire */
(function() {
    var target = <?= $ts_foire ?>;
    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function tick() {
        var diff = target - Date.now();
        if (diff <= 0) { ['fa-j','fa-h','fa-m','fa-s'].forEach(function(id){ var el=document.getElementById(id); if(el) el.textContent='00'; }); return; }
        var d = Math.floor(diff/86400000), h = Math.floor((diff%86400000)/3600000), m = Math.floor((diff%3600000)/60000), s = Math.floor((diff%60000)/1000);
        var el;
        if((el=document.getElementById('fa-j'))) el.textContent = d;
        if((el=document.getElementById('fa-h'))) el.textContent = pad(h);
        if((el=document.getElementById('fa-m'))) el.textContent = pad(m);
        if((el=document.getElementById('fa-s'))) el.textContent = pad(s);
    }
    tick(); setInterval(tick, 1000);
})();

/* Onglets éditions */
function faShowTab(id, btn) {
    document.querySelectorAll('.fa-ed-panel').forEach(function(p){ p.classList.remove('active'); });
    document.querySelectorAll('.fa-ed-tab').forEach(function(b){ b.classList.remove('active'); });
    document.getElementById('fa-panel-' + id).classList.add('active');
    btn.classList.add('active');
}

/* Modal Lire la suite */
function faOpenModal(id) {
    var ed = FA_EDITIONS[id];
    if (!ed) return;

    /* Image */
    var imgWrap = document.getElementById('fa-modal-img-wrap');
    if (ed.image_hero) {
        imgWrap.innerHTML = '<img src="' + ed.image_hero + '" alt="Édition ' + ed.annee + '">';
    } else {
        imgWrap.innerHTML = '<div class="fa-modal-img-placeholder"><i class="fas fa-star"></i></div>';
    }

    /* Texte */
    document.getElementById('fa-modal-title').textContent = 'Grande Foire ' + ed.annee;
    document.getElementById('fa-modal-theme').textContent = ed.theme;

    /* Stats */
    var stats = '';
    if (ed.visiteurs) stats += '<div class="fa-modal-stat-box"><span class="n">' + ed.visiteurs + '</span><div class="l">Visiteurs</div></div>';
    if (ed.stands)    stats += '<div class="fa-modal-stat-box"><span class="n">' + ed.stands + '</span><div class="l">Exposants</div></div>';
    document.getElementById('fa-modal-stats').innerHTML = stats;

    /* Dates */
    document.getElementById('fa-modal-date').innerHTML = ed.dates
        ? '<i class="far fa-calendar-alt"></i> ' + ed.dates : '';

    /* Description complète */
    document.getElementById('fa-modal-desc').textContent = ed.description_complete;

    /* Galerie */
    var galWrap = document.getElementById('fa-modal-gallery');
    var galGrid  = document.getElementById('fa-modal-gallery-grid');
    if (ed.galerie && ed.galerie.length) {
        galGrid.innerHTML = ed.galerie.map(function(src) {
            return '<img src="' + src + '" alt="" loading="lazy">';
        }).join('');
        galWrap.style.display = '';
    } else {
        galWrap.style.display = 'none';
    }

    document.getElementById('fa-modal-overlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function faCloseModal(e) {
    if (e && e.target !== document.getElementById('fa-modal-overlay')) return;
    document.getElementById('fa-modal-overlay').classList.remove('open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { document.getElementById('fa-modal-overlay').classList.remove('open'); document.body.style.overflow = ''; }
});
</script>
