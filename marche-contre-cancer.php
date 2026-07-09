<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$page_title       = 'Marche Contre le Cancer';
$page_description = 'Rejoignez la grande marche solidaire organisée chaque année par le GSCC pour lutter contre le cancer en Haïti.';

$annee       = (int)date('Y');
$date_marche = mktime(9, 0, 0, 10, 20, $annee);
if ($date_marche < time()) {
    $annee++;
    $date_marche = mktime(9, 0, 0, 10, 20, $annee);
}
$ts_marche     = $date_marche * 1000;
$jours_restants = max(0, (int)ceil(($date_marche - time()) / 86400));

/* ── Données des éditions (base de données) ── */
try {
    $stmt = $pdo->query("SELECT * FROM marche_editions ORDER BY ordre ASC, annee DESC");
    $editions = $stmt->fetchAll();
} catch (PDOException $e) {
    $editions = [];
}
$a_venir   = array_values(array_filter($editions, fn($e) => $e['statut'] === 'a_venir'));
$terminees = array_values(array_filter($editions, fn($e) => $e['statut'] === 'termine'));

/* Formater DATE MySQL → texte français */
function mcFmtDate(?string $d): string {
    if (!$d) return '';
    static $m = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    $ts = strtotime($d);
    return date('j', $ts) . ' ' . $m[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

require_once 'templates/header.php';
?>

<style>
/* ══════════════════════════════════════════════════════
   MARCHE CONTRE LE CANCER — Variables
══════════════════════════════════════════════════════ */
:root {
    --mc-blue:     #003399;
    --mc-blue-mid: #1a56cc;
    --mc-blue-lt:  #1a7abf;
    --mc-blue-bg:  #EEF3FF;
    --mc-blue-card:#F5F7FF;
    --mc-teal:     #0891B2;
    --mc-dark:     #0D1117;
    --mc-charcoal: #1E2A35;
    --mc-gray:     #374151;
    --mc-gray-lt:  #6B7280;
    --mc-border:   #E5E9F2;
    --mc-white:    #FFFFFF;
    --mc-bg:       #F8F9FC;
    --mc-shadow:   0 4px 20px rgba(0,51,153,.08);
    --mc-shadow-h: 0 16px 48px rgba(0,51,153,.16);
    --mc-r:        16px;
}

/* ══ HERO ══════════════════════════════════════════════ */
.mc-hero {
    background: linear-gradient(135deg, #7B1535 0%, #C8375F 55%, #D94F7A 100%);
    padding: 110px 0 90px;
    position: relative;
    overflow: hidden;
    text-align: center;
}
/* Motif de points en background */
.mc-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='20' cy='20' r='2.5' fill='white' fill-opacity='0.09'/%3E%3C/svg%3E") center / 40px repeat;
    pointer-events: none;
}
.mc-hero::after {
    content: '';
    position: absolute; bottom: -1px; left: 0; right: 0;
    height: 60px;
    background: var(--mc-bg);
    clip-path: ellipse(54% 100% at 50% 100%);
}
.mc-hero-inner { position: relative; z-index: 2; }

.mc-hero-tag {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.22);
    color: rgba(255,255,255,.9); font-size: .72rem; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    padding: 6px 18px; border-radius: 30px; margin-bottom: 24px;
    backdrop-filter: blur(8px);
}
.mc-hero-tag { color: #fff; }
.mc-hero-tag i { color: #FFB3CC; }
.mc-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.6rem, 5.5vw, 4rem);
    color: #fff; font-weight: 700; line-height: 1.1;
    letter-spacing: -.3px; margin-bottom: 18px;
}
.mc-hero-date {
    display: inline-flex; align-items: center; gap: 10px; flex-wrap: wrap;
    justify-content: center;
    font-size: .97rem; color: #fff;
    background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18);
    padding: 9px 22px; border-radius: 30px; margin-bottom: 18px; font-weight: 500;
}
.mc-hero-date i { color: #FFB3CC; font-size: .85rem; }
.mc-hero-sub {
    font-size: 1.05rem; color: rgba(255,255,255,.95);
    max-width: 520px; margin: 0 auto 40px; line-height: 1.8;
}

/* Countdown */
.mc-countdown {
    display: inline-flex; gap: 8px;
    background: rgba(255,255,255,.11); border: 1px solid rgba(255,255,255,.18);
    border-radius: 18px; padding: 20px 28px;
    backdrop-filter: blur(12px); margin-bottom: 40px;
}
.mc-cd-unit { text-align: center; min-width: 58px; }
.mc-cd-num {
    font-family: 'Playfair Display', serif;
    font-size: 2.1rem; font-weight: 700;
    color: #fff; line-height: 1; display: block;
}
.mc-cd-lbl {
    font-size: .7rem; color: rgba(255,255,255,.85);
    letter-spacing: 1.5px; text-transform: uppercase;
    margin-top: 4px; display: block;
}
.mc-cd-sep { font-size: 1.6rem; color: rgba(255,255,255,.25); align-self: flex-start; padding-top: 6px; }

.mc-hero-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.mc-btn-primary {
    display: inline-flex; align-items: center; gap: 9px;
    background: #fff; color: var(--mc-blue);
    padding: 14px 30px; border-radius: 30px;
    text-decoration: none; font-weight: 700; font-size: .95rem;
    box-shadow: 0 4px 20px rgba(0,0,0,.18); transition: all .3s;
}
.mc-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,.26); }
.mc-btn-ghost {
    display: inline-flex; align-items: center; gap: 9px;
    background: transparent; color: rgba(255,255,255,.88);
    border: 1.5px solid rgba(255,255,255,.32);
    padding: 14px 26px; border-radius: 30px;
    text-decoration: none; font-weight: 500; font-size: .95rem; transition: all .3s;
}
.mc-btn-ghost:hover { border-color: #fff; color: #fff; background: rgba(255,255,255,.08); }

/* ══ STATS ══════════════════════════════════════════════ */
.mc-stats {
    background: var(--mc-white);
    border-bottom: 1px solid var(--mc-border);
    box-shadow: 0 2px 12px rgba(0,51,153,.05);
}
.mc-stats-inner {
    display: grid; grid-template-columns: repeat(4, 1fr);
    max-width: 1140px; margin: 0 auto; padding: 0 28px;
}
.mc-st {
    padding: 26px 16px; text-align: center;
    border-right: 1px solid var(--mc-border);
    transition: background .2s;
}
.mc-st:last-child { border-right: none; }
.mc-st:hover { background: var(--mc-blue-card); }
.mc-st-ico { font-size: 16px; color: var(--mc-blue-mid); margin-bottom: 7px; }
.mc-st-n {
    font-family: 'Playfair Display', serif;
    font-size: 1.85rem; font-weight: 700;
    color: var(--mc-blue); line-height: 1; margin-bottom: 4px;
}
.mc-st-l { font-size: .7rem; color: var(--mc-charcoal); text-transform: uppercase; letter-spacing: 1.2px; font-weight: 600; }

/* ══ LAYOUT HELPERS ══════════════════════════════════════ */
.mc-section { padding: 80px 0; background: var(--mc-bg); }
.mc-section.alt { background: var(--mc-white); }
.mc-section-label {
    display: inline-flex; align-items: center; gap: 7px;
    font-size: .7rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
    color: var(--mc-blue-mid); background: var(--mc-blue-bg);
    border: 1px solid #C7D7FF; padding: 5px 14px; border-radius: 30px; margin-bottom: 12px;
}
.mc-section-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 700;
    color: var(--mc-blue); margin-bottom: 10px; line-height: 1.2;
}
.mc-section-sub { font-size: .97rem; color: var(--mc-gray); max-width: 560px; line-height: 1.8; }
.mc-head { margin-bottom: 50px; }
.mc-head.center { text-align: center; }
.mc-head.center .mc-section-sub { margin: 0 auto; }

/* ══ INFO CARDS ══════════════════════════════════════════ */
.mc-info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; }
.mc-info-card {
    background: var(--mc-white); border: 1px solid var(--mc-border);
    border-radius: var(--mc-r); padding: 34px 26px;
    position: relative; overflow: hidden;
    box-shadow: var(--mc-shadow);
    transition: transform .3s, box-shadow .3s;
}
.mc-info-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
    background: linear-gradient(90deg, var(--mc-blue), var(--mc-blue-lt));
    transform: scaleX(0); transform-origin: left; transition: transform .35s;
}
.mc-info-card:hover { transform: translateY(-6px); box-shadow: var(--mc-shadow-h); }
.mc-info-card:hover::before { transform: scaleX(1); }
.mc-info-icon {
    width: 52px; height: 52px; background: var(--mc-blue-bg);
    border-radius: 13px; display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: var(--mc-blue-mid); margin-bottom: 18px; transition: all .3s;
}
.mc-info-card:hover .mc-info-icon { background: linear-gradient(135deg, var(--mc-blue), var(--mc-blue-lt)); color: #fff; }
.mc-info-card h3 { font-size: .97rem; font-weight: 700; color: var(--mc-dark); margin-bottom: 8px; }
.mc-info-card p { font-size: .88rem; color: var(--mc-gray); line-height: 1.75; }
.mc-info-card strong { color: var(--mc-blue); }

/* ══ PARCOURS ════════════════════════════════════════════ */
.mc-parcours-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 52px; align-items: center; }
.mc-map-wrap { border-radius: 18px; overflow: hidden; box-shadow: var(--mc-shadow-h); border: 1px solid var(--mc-border); }
.mc-map-wrap iframe { width: 100%; height: 400px; border: none; display: block; }
.mc-steps { list-style: none; display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px; }
.mc-step {
    display: flex; align-items: flex-start; gap: 14px;
    background: var(--mc-white); border: 1px solid var(--mc-border);
    border-radius: 12px; padding: 14px 16px; box-shadow: var(--mc-shadow);
    transition: border-color .2s, transform .2s;
}
.mc-step:hover { border-color: var(--mc-blue-mid); transform: translateX(4px); }
.mc-step-dot {
    width: 32px; height: 32px; flex-shrink: 0; border-radius: 50%;
    background: linear-gradient(135deg, var(--mc-blue), var(--mc-blue-lt));
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: .75rem; font-weight: 700;
}
.mc-step span { font-size: .88rem; color: var(--mc-gray); line-height: 1.6; padding-top: 5px; }
.mc-step strong { color: var(--mc-dark); }
.mc-access {
    display: inline-flex; align-items: center; gap: 10px;
    font-size: .82rem; color: var(--mc-blue);
    background: var(--mc-blue-bg); border: 1px solid #C7D7FF;
    padding: 9px 16px; border-radius: 10px; font-weight: 500;
}

/* ══ PROGRAMME ═══════════════════════════════════════════ */
.mc-prog-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; }
.mc-prog-card {
    background: var(--mc-white); border: 1px solid var(--mc-border);
    border-radius: var(--mc-r); padding: 26px 18px;
    box-shadow: var(--mc-shadow); text-align: center; position: relative; overflow: hidden;
    transition: transform .3s, box-shadow .3s;
}
.mc-prog-card:hover { transform: translateY(-5px); box-shadow: var(--mc-shadow-h); }
.mc-prog-time {
    font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 700;
    color: var(--mc-blue); margin-bottom: 8px;
}
.mc-prog-icon { font-size: 22px; color: var(--mc-blue-mid); margin-bottom: 10px; }
.mc-prog-title { font-size: .88rem; font-weight: 700; color: var(--mc-dark); margin-bottom: 5px; }
.mc-prog-desc { font-size: .78rem; color: var(--mc-gray-lt); line-height: 1.6; }
.mc-prog-bar {
    position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--mc-blue), var(--mc-blue-lt));
}

/* ══ ÉDITIONS ════════════════════════════════════════════ */
.mc-editions { padding: 80px 0; background: var(--mc-bg); }

.mc-ed-tabs {
    display: flex; gap: 4px; align-items: center;
    background: var(--mc-white); border: 1px solid var(--mc-border);
    padding: 4px; border-radius: 14px; width: fit-content;
    margin-bottom: 48px; box-shadow: var(--mc-shadow);
}
.mc-ed-tab {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 22px; border-radius: 10px;
    font-size: .87rem; font-weight: 600; cursor: pointer;
    background: transparent; color: var(--mc-charcoal);
    border: none; transition: all .22s;
}
.mc-ed-tab.active { background: var(--mc-blue); color: #fff; box-shadow: 0 3px 12px rgba(0,51,153,.28); }
.mc-ed-tab i { font-size: .75rem; }

.mc-ed-panel { display: none; }
.mc-ed-panel.active { display: block; }

/* Carte "À venir" — grande featured */
.mc-ed-upcoming {
    background: var(--mc-white); border-radius: 24px;
    border: 1px solid var(--mc-border);
    box-shadow: var(--mc-shadow-h); overflow: hidden;
}
.mc-ed-up-top {
    background: linear-gradient(135deg, #001a66 0%, #003399 55%, #0e4fa3 100%);
    position: relative; overflow: hidden;
    padding: 56px 48px;
}
.mc-ed-up-top::before {
    content: '';
    position: absolute; inset: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path fill="white" opacity=".05" d="M50 15 L61 35 L85 40 L67 55 L70 80 L50 70 L30 80 L33 55 L15 40 L39 35 Z"/></svg>') center / 80px repeat;
    pointer-events: none;
}
.mc-ed-up-inner { position: relative; z-index: 2; display: grid; grid-template-columns: 1fr auto; gap: 40px; align-items: center; }
.mc-ed-up-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
    color: #fff; font-size: .7rem; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; padding: 5px 14px; border-radius: 20px;
    margin-bottom: 14px; width: fit-content;
}
.mc-ed-up-top h3 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.5rem, 2.5vw, 2rem); color: #fff; font-weight: 700;
    margin-bottom: 8px; line-height: 1.2;
}
.mc-ed-up-top p { color: rgba(255,255,255,.78); font-size: .95rem; line-height: 1.7; max-width: 480px; }

.mc-ed-up-cd {
    background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
    border-radius: 16px; padding: 24px 28px; text-align: center;
    backdrop-filter: blur(10px); flex-shrink: 0;
}
.mc-ed-up-cd-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 2px; }
.mc-up-unit { padding: 8px 12px; text-align: center; }
.mc-up-unit .n { font-family: 'Playfair Display', serif; font-size: 1.9rem; font-weight: 700; color: #fff; line-height: 1; }
.mc-up-unit .l { font-size: .65rem; color: rgba(255,255,255,.85); letter-spacing: 1.5px; text-transform: uppercase; margin-top: 3px; }
.mc-up-sep { font-size: 1.4rem; color: rgba(255,255,255,.25); align-self: center; padding-bottom: 10px; }

.mc-ed-up-body { padding: 32px 48px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; }
.mc-ed-up-info { display: flex; gap: 32px; flex-wrap: wrap; }
.mc-ed-up-info-item { display: flex; align-items: center; gap: 9px; }
.mc-ed-up-info-item i { font-size: .9rem; color: var(--mc-blue); }
.mc-ed-up-info-item span { font-size: .9rem; color: var(--mc-charcoal); font-weight: 500; }

/* Grille éditions terminées */
.mc-ed-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 22px; }
.mc-ed-card {
    background: var(--mc-white); border-radius: var(--mc-r);
    border: 1px solid var(--mc-border); overflow: hidden;
    box-shadow: var(--mc-shadow);
    transition: transform .28s, box-shadow .28s;
}
.mc-ed-card:hover { transform: translateY(-6px); box-shadow: var(--mc-shadow-h); }
.mc-ed-card-top {
    background: linear-gradient(135deg, var(--mc-blue) 0%, var(--mc-blue-mid) 100%);
    padding: 22px 24px; position: relative; overflow: hidden;
}
.mc-ed-card-top::before {
    content: '';
    position: absolute; inset: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path fill="white" opacity=".05" d="M50 15 L61 35 L85 40 L67 55 L70 80 L50 70 L30 80 L33 55 L15 40 L39 35 Z"/></svg>') center / 60px repeat;
    pointer-events: none;
}
.mc-ed-card-year {
    font-family: 'Playfair Display', serif; font-size: 2.2rem; font-weight: 700;
    color: #fff; line-height: 1; position: relative; z-index: 1;
}
.mc-ed-card-theme { font-size: .82rem; color: rgba(255,255,255,.95); margin-top: 4px; position: relative; z-index: 1; font-weight: 500; }
.mc-ed-card-status {
    position: absolute; top: 14px; right: 14px; z-index: 2;
    background: rgba(34,197,94,.9); color: #fff;
    font-size: .68rem; font-weight: 700; letter-spacing: .5px; text-transform: uppercase;
    padding: 4px 11px; border-radius: 20px;
    display: flex; align-items: center; gap: 5px;
}
.mc-ed-card-body { padding: 20px 24px; }
.mc-ed-card-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 14px; }
.mc-ed-stat { text-align: center; background: var(--mc-blue-bg); border-radius: 10px; padding: 10px 6px; }
.mc-ed-stat .n { font-weight: 700; font-size: .95rem; color: var(--mc-blue); display: block; }
.mc-ed-stat .l { font-size: .72rem; color: var(--mc-charcoal); margin-top: 2px; font-weight: 500; }
.mc-ed-card-desc { font-size: .88rem; color: var(--mc-charcoal); line-height: 1.7; }
.mc-ed-card-desc-short { display: block; }
.mc-ed-card-desc-full  { display: none; }
.mc-ed-card-desc.expanded .mc-ed-card-desc-short { display: none; }
.mc-ed-card-desc.expanded .mc-ed-card-desc-full  { display: block; }
.mc-ed-read-more {
    display: inline-flex; align-items: center; gap: 5px;
    margin-top: 8px; font-size: .82rem; font-weight: 700;
    color: var(--mc-blue); background: none; border: none; cursor: pointer;
    padding: 0; text-decoration: underline; text-underline-offset: 2px;
}
.mc-ed-card-date { display: flex; align-items: center; gap: 6px; font-size: .82rem; color: var(--mc-charcoal); margin-bottom: 10px; font-weight: 500; }
.mc-ed-card-date i { color: var(--mc-blue); font-size: .75rem; }

/* ══ CTA ═════════════════════════════════════════════════ */
.mc-cta {
    background: linear-gradient(135deg, #001a66 0%, #003399 55%, #0e4fa3 100%);
    padding: 90px 0; text-align: center; position: relative; overflow: hidden;
}
.mc-cta::before {
    content: '';
    position: absolute; inset: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path fill="white" opacity=".04" d="M50 15 L61 35 L85 40 L67 55 L70 80 L50 70 L30 80 L33 55 L15 40 L39 35 Z"/></svg>') center / 90px repeat;
    animation: starspin 60s linear infinite;
    pointer-events: none;
}
.mc-cta-inner { position: relative; z-index: 2; }
.mc-cta-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.13); border: 1px solid rgba(255,255,255,.22);
    color: rgba(255,255,255,.9); font-size: .7rem; font-weight: 700;
    letter-spacing: 1.5px; text-transform: uppercase;
    padding: 5px 14px; border-radius: 30px; margin-bottom: 18px;
}
.mc-cta h2 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.8rem, 3vw, 2.6rem);
    font-weight: 700; color: #fff; margin-bottom: 12px;
}
.mc-cta > div > p { font-size: .97rem; color: #fff !important; line-height: 1.8; max-width: 520px; margin: 0 auto 34px; }

/* Force blanc sur tous les textes des sections foncées */
.mc-hero h1, .mc-hero p, .mc-hero span,
.mc-cta h2, .mc-cta p, .mc-cta span, .mc-cta strong,
.mc-ed-up-top h3, .mc-ed-up-top p, .mc-ed-up-top span,
.mc-countdown *, .mc-cd-unit *, .mc-up-unit *,
.mc-hero-tag, .mc-hero-date, .mc-hero-sub,
.mc-cta-badge { color: #fff !important; }
.mc-cta-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }

/* ══ RESPONSIVE ══════════════════════════════════════════ */
@media (max-width: 960px) {
    .mc-prog-grid { grid-template-columns: repeat(2, 1fr); }
    .mc-stats-inner { grid-template-columns: repeat(2, 1fr); }
    .mc-st:nth-child(2) { border-right: none; }
    .mc-st:nth-child(3) { border-top: 1px solid var(--mc-border); }
    .mc-ed-up-inner { grid-template-columns: 1fr; }
    .mc-ed-up-cd { width: fit-content; }
}
@media (max-width: 768px) {
    .mc-info-grid, .mc-parcours-grid { grid-template-columns: 1fr; }
    .mc-ed-grid { grid-template-columns: 1fr; }
    .mc-ed-up-body { flex-direction: column; align-items: flex-start; }
    .mc-ed-up-top { padding: 36px 28px; }
    .mc-ed-up-body { padding: 24px 28px; }
}
@media (max-width: 540px) {
    .mc-prog-grid { grid-template-columns: 1fr; }
    .mc-ed-up-cd-grid { grid-template-columns: repeat(2, 1fr); }
}

/* ══ CARD IMAGE ══════════════════════════════════════════ */
.mc-ed-card-img { width: 100%; height: 175px; overflow: hidden; flex-shrink: 0; }
.mc-ed-card-img img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .4s; }
.mc-ed-card:hover .mc-ed-card-img img { transform: scale(1.05); }

/* ══ MODAL ÉDITION ═══════════════════════════════════════ */
.mc-modal-overlay {
    display: none; position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,.65); backdrop-filter: blur(4px);
    align-items: flex-start; justify-content: center;
    padding: 28px 20px; overflow-y: auto;
}
.mc-modal-overlay.open { display: flex; }
.mc-modal-box {
    background: #fff; border-radius: 20px; max-width: 680px; width: 100%;
    display: flex; flex-direction: column; overflow: hidden;
    box-shadow: 0 24px 80px rgba(0,0,0,.38); position: relative;
    animation: mcModalIn .25s ease; margin: auto;
}
@keyframes mcModalIn { from { opacity:0; transform: scale(.94) translateY(16px); } to { opacity:1; transform: none; } }
.mc-modal-close {
    position: absolute; top: 14px; right: 16px; z-index: 10;
    background: rgba(0,0,0,.42); border: none; color: #fff;
    width: 34px; height: 34px; border-radius: 50%;
    font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: background .2s;
}
.mc-modal-close:hover { background: rgba(0,0,0,.68); }
.mc-modal-img-wrap { flex-shrink: 0; height: 230px; overflow: hidden; }
.mc-modal-img-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }
.mc-modal-img-placeholder {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, #001a66 0%, #003399 55%, #0e4fa3 100%);
    display: flex; align-items: center; justify-content: center;
}
.mc-modal-img-placeholder i { font-size: 3rem; color: rgba(255,255,255,.22); }
.mc-modal-body-content { padding: 26px 30px 32px; }
.mc-modal-badge-done {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(34,197,94,.12); color: #16a34a;
    border: 1px solid rgba(34,197,94,.3);
    font-size: .7rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;
    padding: 4px 12px; border-radius: 20px; margin-bottom: 12px;
}
.mc-modal-title {
    font-family: 'Playfair Display', serif; font-size: 1.7rem; font-weight: 700;
    color: var(--mc-blue); margin-bottom: 4px; line-height: 1.15;
}
.mc-modal-theme-txt { font-size: .92rem; color: var(--mc-gray); margin-bottom: 18px; font-style: italic; }
.mc-modal-stats-row { display: grid; grid-template-columns: repeat(2,1fr); gap: 10px; margin-bottom: 18px; }
.mc-modal-stat-box { background: var(--mc-blue-bg); border-radius: 10px; padding: 13px 10px; text-align: center; }
.mc-modal-stat-box .n { font-weight: 700; font-size: 1.05rem; color: var(--mc-blue); display: block; }
.mc-modal-stat-box .l { font-size: .72rem; color: var(--mc-charcoal); font-weight: 500; margin-top: 2px; }
.mc-modal-date-row { display: flex; align-items: center; gap: 8px; font-size: .87rem; color: var(--mc-charcoal); margin-bottom: 18px; }
.mc-modal-date-row i { color: var(--mc-blue); }
.mc-modal-desc-text { font-size: .93rem; color: var(--mc-gray); line-height: 1.88; white-space: pre-line; }
.mc-modal-gallery-wrap { margin-top: 24px; }
.mc-modal-gallery-lbl { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--mc-gray-lt); margin-bottom: 10px; }
.mc-modal-gallery-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 8px; }
.mc-modal-gallery-grid img { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 8px; cursor: zoom-in; transition: opacity .2s; }
.mc-modal-gallery-grid img:hover { opacity: .82; }
@media (max-width: 540px) {
    .mc-modal-box { border-radius: 16px; }
    .mc-modal-img-wrap { height: 180px; }
    .mc-modal-body-content { padding: 20px 20px 26px; }
    .mc-modal-gallery-grid { grid-template-columns: repeat(2,1fr); }
}
</style>

<!-- ══ HERO ══════════════════════════════════════════════ -->
<section class="mc-hero">
    <div class="container">
        <div class="mc-hero-inner">
            <div class="mc-hero-tag" data-aos="fade-down">
                <i class="fas fa-walking"></i> GSCC — Événement annuel · Octobre Rose
            </div>
            <h1 data-aos="fade-up" data-aos-delay="60">Marche Contre le Cancer</h1>
            <div class="mc-hero-date" data-aos="fade-up" data-aos-delay="110">
                <i class="fas fa-calendar-alt"></i> 20 octobre <?= $annee ?>
                <span style="color:rgba(255,255,255,.3)">·</span>
                <i class="fas fa-map-marker-alt"></i> Nord — Cap-Haïtien
                <span style="color:rgba(255,255,255,.3)">·</span>
                5 km · Gratuit
            </div>
            <p class="mc-hero-sub" data-aos="fade-up" data-aos-delay="150">
                Rejoignez des milliers de marcheurs solidaires pour faire avancer la lutte contre le cancer en Haïti.
            </p>
            <div class="mc-countdown" data-aos="fade-up" data-aos-delay="200">
                <div class="mc-cd-unit"><span class="mc-cd-num" id="cd-j"><?= $jours_restants ?></span><span class="mc-cd-lbl">Jours</span></div>
                <span class="mc-cd-sep">:</span>
                <div class="mc-cd-unit"><span class="mc-cd-num" id="cd-h">00</span><span class="mc-cd-lbl">Heures</span></div>
                <span class="mc-cd-sep">:</span>
                <div class="mc-cd-unit"><span class="mc-cd-num" id="cd-m">00</span><span class="mc-cd-lbl">Min</span></div>
                <span class="mc-cd-sep">:</span>
                <div class="mc-cd-unit"><span class="mc-cd-num" id="cd-s">00</span><span class="mc-cd-lbl">Sec</span></div>
            </div>
            <div class="mc-hero-btns" data-aos="fade-up" data-aos-delay="260">
                <a href="contact.php" class="mc-btn-primary"><i class="fas fa-walking"></i> Je participe</a>
                <a href="#editions" class="mc-btn-ghost"><i class="fas fa-history"></i> Voir les éditions</a>
            </div>
        </div>
    </div>
</section>

<!-- ══ STATS ══════════════════════════════════════════════ -->
<div class="mc-stats">
    <div class="mc-stats-inner">
        <div class="mc-st" data-aos="fade-up">
            <div class="mc-st-ico"><i class="fas fa-users"></i></div>
            <div class="mc-st-n">3 200+</div>
            <div class="mc-st-l">Participants</div>
        </div>
        <div class="mc-st" data-aos="fade-up" data-aos-delay="60">
            <div class="mc-st-ico"><i class="fas fa-route"></i></div>
            <div class="mc-st-n">5 km</div>
            <div class="mc-st-l">Parcours</div>
        </div>
        <div class="mc-st" data-aos="fade-up" data-aos-delay="120">
            <div class="mc-st-ico"><i class="fas fa-calendar-check"></i></div>
            <div class="mc-st-n">6+</div>
            <div class="mc-st-l">Éditions</div>
        </div>
        <div class="mc-st" data-aos="fade-up" data-aos-delay="180">
            <div class="mc-st-ico"><i class="fas fa-heart"></i></div>
            <div class="mc-st-n">100%</div>
            <div class="mc-st-l">Gratuit</div>
        </div>
    </div>
</div>

<!-- ══ INFOS PRATIQUES ════════════════════════════════════ -->
<section class="mc-section alt">
    <div class="container">
        <div class="mc-head center" data-aos="fade-up">
            <div class="mc-section-label"><i class="fas fa-info-circle"></i> Informations pratiques</div>
            <h2 class="mc-section-title">Tout ce qu'il faut savoir</h2>
            <p class="mc-section-sub">Préparez votre venue à la grande marche solidaire du GSCC.</p>
        </div>
        <div class="mc-info-grid">
            <div class="mc-info-card" data-aos="fade-up">
                <h3>Date &amp; Heure</h3>
                <p><strong>20 octobre <?= $annee ?></strong><br>
                Accueil à partir de <strong>8h00</strong><br>
                Départ officiel à <strong>9h00</strong></p>
            </div>
            <div class="mc-info-card" data-aos="fade-up" data-aos-delay="90">
                <h3>Lieu de départ</h3>
                <p><strong>Nord — Cap-Haïtien</strong><br>
                Cap-Haïtien, Haïti</p>
            </div>
            <div class="mc-info-card" data-aos="fade-up" data-aos-delay="180">
                <h3>Kit du marcheur</h3>
                <p>T-shirt officiel · Bouteille d'eau<br>
                Bracelet solidaire · Programme<br>
                Distribué <strong>gratuitement</strong> sur place</p>
            </div>
        </div>
    </div>
</section>

<!-- ══ PROGRAMME ══════════════════════════════════════════ -->
<section class="mc-section alt">
    <div class="container">
        <div class="mc-head center" data-aos="fade-up">
            <div class="mc-section-label"><i class="fas fa-clock"></i> Programme</div>
            <h2 class="mc-section-title">La journée en détail</h2>
            <p class="mc-section-sub">Une journée complète de solidarité, de sport et de convivialité.</p>
        </div>
        <div class="mc-prog-grid">
            <div class="mc-prog-card" data-aos="fade-up">
                <div class="mc-prog-time">8h00</div>
                <div class="mc-prog-icon"><i class="fas fa-door-open"></i></div>
                <div class="mc-prog-title">Accueil</div>
                <div class="mc-prog-desc">Distribution des kits marcheurs et enregistrement des équipes</div>
                <div class="mc-prog-bar"></div>
            </div>
            <div class="mc-prog-card" data-aos="fade-up" data-aos-delay="70">
                <div class="mc-prog-time">9h00</div>
                <div class="mc-prog-icon"><i class="fas fa-flag"></i></div>
                <div class="mc-prog-title">Départ officiel</div>
                <div class="mc-prog-desc">Coup d'envoi depuis Cap-Haïtien</div>
                <div class="mc-prog-bar"></div>
            </div>
            <div class="mc-prog-card" data-aos="fade-up" data-aos-delay="140">
                <div class="mc-prog-time">11h00</div>
                <div class="mc-prog-icon"><i class="fas fa-music"></i></div>
                <div class="mc-prog-title">Animation</div>
                <div class="mc-prog-desc">Concerts live, témoignages de survivants et prises de parole</div>
                <div class="mc-prog-bar"></div>
            </div>
            <div class="mc-prog-card" data-aos="fade-up" data-aos-delay="210">
                <div class="mc-prog-time">13h00</div>
                <div class="mc-prog-icon"><i class="fas fa-store"></i></div>
                <div class="mc-prog-title">Village solidaire</div>
                <div class="mc-prog-desc">Stands, dépistage gratuit, remise des prix et clôture</div>
                <div class="mc-prog-bar"></div>
            </div>
        </div>
    </div>
</section>

<!-- ══ ÉDITIONS ═══════════════════════════════════════════ -->
<section class="mc-editions" id="editions">
    <div class="container">
        <div class="mc-head" data-aos="fade-up">
            <div class="mc-section-label"><i class="fas fa-calendar-check"></i> Historique</div>
            <h2 class="mc-section-title">Les éditions de la Marche</h2>
            <p class="mc-section-sub">Retrouvez toutes les éditions passées et à venir de la grande marche solidaire du GSCC.</p>
        </div>

        <!-- Onglets -->
        <div class="mc-ed-tabs" data-aos="fade-up">
            <button class="mc-ed-tab active" onclick="mcShowTab('a_venir',this)">
                <i class="fas fa-calendar-plus"></i> À venir
            </button>
            <button class="mc-ed-tab" onclick="mcShowTab('termine',this)">
                <i class="fas fa-check-circle"></i> Terminées
            </button>
        </div>

        <!-- Panneau : À venir -->
        <div class="mc-ed-panel active" id="mc-panel-a_venir">
            <?php foreach ($a_venir as $ed): ?>
            <div class="mc-ed-upcoming" data-aos="fade-up">
                <div class="mc-ed-up-top">
                    <div class="mc-ed-up-inner">
                        <div>
                            <div class="mc-ed-up-badge"><i class="fas fa-calendar-plus"></i> Édition <?= $ed['annee'] ?> — À venir</div>
                            <h3>Marche Contre le Cancer <?= $ed['annee'] ?></h3>
                            <p style="color:rgba(255,255,255,.82);font-size:.93rem;line-height:1.75;margin-top:10px;"><?= htmlspecialchars($ed['description'] ?? '') ?></p>
                        </div>
                        <div class="mc-ed-up-cd">
                            <div style="font-size:.72rem;color:rgba(255,255,255,.6);letter-spacing:1.5px;text-transform:uppercase;margin-bottom:12px;">Compte à rebours</div>
                            <div class="mc-ed-up-cd-grid">
                                <div class="mc-up-unit"><div class="n" id="up-j"><?= $jours_restants ?></div><div class="l">Jours</div></div>
                                <div class="mc-up-unit"><div class="n" id="up-h">00</div><div class="l">H</div></div>
                                <div class="mc-up-unit"><div class="n" id="up-m">00</div><div class="l">Min</div></div>
                                <div class="mc-up-unit"><div class="n" id="up-s">00</div><div class="l">Sec</div></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mc-ed-up-body">
                    <div class="mc-ed-up-info">
                        <div class="mc-ed-up-info-item"><i class="fas fa-calendar-alt"></i><span><?= htmlspecialchars(mcFmtDate($ed['date_evenement'] ?? null)) ?></span></div>
                        <div class="mc-ed-up-info-item"><i class="fas fa-map-marker-alt"></i><span>Nord — Cap-Haïtien</span></div>
                        <div class="mc-ed-up-info-item"><i class="fas fa-route"></i><span><?= htmlspecialchars($ed['km'] ?? '') ?></span></div>
                        <div class="mc-ed-up-info-item"><i class="fas fa-heart"></i><span>Participation gratuite</span></div>
                    </div>
                    <a href="contact.php" class="mc-btn-primary" style="white-space:nowrap;"><i class="fas fa-walking"></i> Je m'inscris</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Panneau : Terminées -->
        <div class="mc-ed-panel" id="mc-panel-termine">
            <div class="mc-ed-grid">
                <?php foreach ($terminees as $i => $ed): ?>
                <div class="mc-ed-card" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 70 ?>">
                    <?php if (!empty($ed['image'])): ?>
                    <div class="mc-ed-card-img">
                        <img src="<?= htmlspecialchars($ed['image']) ?>" alt="Édition <?= $ed['annee'] ?>" loading="lazy">
                    </div>
                    <?php endif; ?>
                    <div class="mc-ed-card-top">
                        <div class="mc-ed-card-status"><i class="fas fa-check"></i> Terminée</div>
                        <div class="mc-ed-card-year"><?= $ed['annee'] ?></div>
                        <div class="mc-ed-card-theme"><?= htmlspecialchars($ed['theme'] ?? '') ?></div>
                    </div>
                    <?php
                    $desc  = $ed['description'] ?? '';
                    $short = mb_strlen($desc) > 130 ? mb_substr($desc, 0, 130) . '…' : $desc;
                    ?>
                    <div class="mc-ed-card-body">
                        <div class="mc-ed-card-date"><i class="far fa-calendar-alt"></i><?= htmlspecialchars(mcFmtDate($ed['date_evenement'] ?? null)) ?></div>
                        <div class="mc-ed-card-stats">
                            <div class="mc-ed-stat"><span class="n"><?= htmlspecialchars($ed['participants'] ?? '—') ?></span><div class="l">Participants</div></div>
                            <div class="mc-ed-stat"><span class="n"><?= htmlspecialchars($ed['km'] ?? '—') ?></span><div class="l">Parcours</div></div>
                        </div>
                        <p class="mc-ed-card-desc"><?= htmlspecialchars($short) ?></p>
                        <?php if (mb_strlen($desc) > 130): ?>
                        <button class="mc-ed-read-more" onclick="mcOpenModal(<?= (int)$ed['id'] ?>)">
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

<!-- ══ CTA ════════════════════════════════════════════════ -->
<section class="mc-cta">
    <div class="container mc-cta-inner">
        <div class="mc-cta-badge" data-aos="fade-down"><i class="fas fa-calendar-alt"></i> 20 octobre <?= $annee ?></div>
        <h2 data-aos="fade-up"><?= $jours_restants ?> jours avant la prochaine marche</h2>
        <div>
            <p data-aos="fade-up" data-aos-delay="80">La participation est <strong>100% gratuite</strong>. Venez nombreux avec famille et amis pour cette journée de solidarité et d'espoir contre le cancer.</p>
            <div class="mc-cta-btns" data-aos="fade-up" data-aos-delay="160">
                <a href="contact.php" class="mc-btn-primary"><i class="fas fa-walking"></i> Je participe</a>
                <a href="faire-un-don.php" class="mc-btn-ghost"><i class="fas fa-heart"></i> Faire un don</a>
            </div>
        </div>
    </div>
</section>

<!-- ══ MODAL ÉDITION ═════════════════════════════════════ -->
<div id="mc-modal-overlay" class="mc-modal-overlay" onclick="mcCloseModal(event)">
    <div class="mc-modal-box" onclick="event.stopPropagation()">
        <button class="mc-modal-close" onclick="mcCloseModal()" aria-label="Fermer">&times;</button>
        <div class="mc-modal-img-wrap" id="mc-modal-img-wrap">
            <div class="mc-modal-img-placeholder"><i class="fas fa-walking"></i></div>
        </div>
        <div class="mc-modal-body-content">
            <div class="mc-modal-badge-done"><i class="fas fa-check"></i> Édition Terminée</div>
            <div class="mc-modal-title" id="mc-modal-title"></div>
            <div class="mc-modal-theme-txt" id="mc-modal-theme"></div>
            <div class="mc-modal-stats-row" id="mc-modal-stats"></div>
            <div class="mc-modal-date-row" id="mc-modal-date"></div>
            <div class="mc-modal-desc-text" id="mc-modal-desc"></div>
            <div class="mc-modal-gallery-wrap" id="mc-modal-gallery" style="display:none;">
                <div class="mc-modal-gallery-lbl"><i class="fas fa-images" style="margin-right:6px;"></i>Galerie photos</div>
                <div class="mc-modal-gallery-grid" id="mc-modal-gallery-grid"></div>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>

<?php
$terminees_js = [];
foreach ($terminees as $ed) {
    $terminees_js[(int)$ed['id']] = [
        'id'                   => (int)$ed['id'],
        'annee'                => (int)$ed['annee'],
        'theme'                => $ed['theme'] ?? '',
        'date_marche'          => mcFmtDate($ed['date_evenement'] ?? null),
        'participants'         => $ed['participants'] ?? '',
        'km'                   => $ed['km'] ?? '',
        'description_complete' => $ed['description'] ?? '',
        'image_hero'           => $ed['image'] ?? '',
        'galerie'              => [],
    ];
}
?>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init({ duration: 700, once: true, offset: 55 });

var MC_EDITIONS = <?= json_encode($terminees_js, JSON_HEX_TAG | JSON_HEX_AMP) ?>;

/* Compte à rebours */
(function() {
    var target = <?= $ts_marche ?>;
    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function tick() {
        var diff = target - Date.now();
        if (diff <= 0) { ['cd-j','cd-h','cd-m','cd-s','up-j','up-h','up-m','up-s'].forEach(function(id){ var el=document.getElementById(id); if(el) el.textContent='00'; }); return; }
        var d = Math.floor(diff/86400000), h = Math.floor((diff%86400000)/3600000), m = Math.floor((diff%3600000)/60000), s = Math.floor((diff%60000)/1000);
        ['cd-j','up-j'].forEach(function(id){var el=document.getElementById(id);if(el)el.textContent=d;});
        ['cd-h','up-h'].forEach(function(id){var el=document.getElementById(id);if(el)el.textContent=pad(h);});
        ['cd-m','up-m'].forEach(function(id){var el=document.getElementById(id);if(el)el.textContent=pad(m);});
        ['cd-s','up-s'].forEach(function(id){var el=document.getElementById(id);if(el)el.textContent=pad(s);});
    }
    tick(); setInterval(tick, 1000);
})();

/* Onglets éditions */
function mcShowTab(id, btn) {
    document.querySelectorAll('.mc-ed-panel').forEach(function(p){ p.classList.remove('active'); });
    document.querySelectorAll('.mc-ed-tab').forEach(function(b){ b.classList.remove('active'); });
    document.getElementById('mc-panel-' + id).classList.add('active');
    btn.classList.add('active');
}

/* Modal Lire la suite */
function mcOpenModal(id) {
    var ed = MC_EDITIONS[id];
    if (!ed) return;

    /* Image */
    var imgWrap = document.getElementById('mc-modal-img-wrap');
    if (ed.image_hero) {
        imgWrap.innerHTML = '<img src="' + ed.image_hero + '" alt="Édition ' + ed.annee + '">';
    } else {
        imgWrap.innerHTML = '<div class="mc-modal-img-placeholder"><i class="fas fa-walking"></i></div>';
    }

    /* Texte */
    document.getElementById('mc-modal-title').textContent = 'Marche ' + ed.annee;
    document.getElementById('mc-modal-theme').textContent = ed.theme;

    /* Stats */
    var stats = '';
    if (ed.participants) stats += '<div class="mc-modal-stat-box"><span class="n">' + ed.participants + '</span><div class="l">Participants</div></div>';
    if (ed.km)           stats += '<div class="mc-modal-stat-box"><span class="n">' + ed.km + '</span><div class="l">Parcours</div></div>';
    document.getElementById('mc-modal-stats').innerHTML = stats;

    /* Date */
    document.getElementById('mc-modal-date').innerHTML = ed.date_marche
        ? '<i class="far fa-calendar-alt"></i> ' + ed.date_marche : '';

    /* Description complète */
    document.getElementById('mc-modal-desc').textContent = ed.description_complete;

    /* Galerie */
    var galWrap = document.getElementById('mc-modal-gallery');
    var galGrid  = document.getElementById('mc-modal-gallery-grid');
    if (ed.galerie && ed.galerie.length) {
        galGrid.innerHTML = ed.galerie.map(function(src) {
            return '<img src="' + src + '" alt="" loading="lazy">';
        }).join('');
        galWrap.style.display = '';
    } else {
        galWrap.style.display = 'none';
    }

    document.getElementById('mc-modal-overlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function mcCloseModal(e) {
    if (e && e.target !== document.getElementById('mc-modal-overlay')) return;
    document.getElementById('mc-modal-overlay').classList.remove('open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { document.getElementById('mc-modal-overlay').classList.remove('open'); document.body.style.overflow = ''; }
});
</script>
