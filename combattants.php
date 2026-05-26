<?php
// combattants.php — Nos combattants : personnes vivant avec le cancer
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$combattants = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM combattants WHERE statut = 'publie' ORDER BY annees_combat DESC");
    $stmt->execute();
    $combattants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$total      = count($combattants);
$max_annees = $total ? max(array_column($combattants, 'annees_combat')) : 0;
$avg_annees = $total ? round(array_sum(array_column($combattants, 'annees_combat')) / $total, 1) : 0;

$page_title       = 'Nos Combattants — GSCC';
$page_description = 'Des femmes et des hommes qui vivent avec le cancer en Haïti. Leurs histoires, leur force, leur courage.';
require_once 'templates/header.php';
?>
<style>
/* ═══════════════════════════════════════════════════════
   VARIABLES — même palette rose que survivants.php
   ═══════════════════════════════════════════════════════ */
:root {
    --cb-rose:       #C8375F;
    --cb-rose-dark:  #9B1D40;
    --cb-rose-pale:  #FCE8EF;
    --cb-rose-mid:   #F5C6D5;
    --cb-gold:       #C9933A;
    --cb-gold-light: #F0D090;
    --cb-charcoal:   #1E2A35;
    --cb-deep:       #2D1020;
}

/* ═══════════════════════════════════════════════════════
   HERO
   ═══════════════════════════════════════════════════════ */
.cb-hero {
    position: relative;
    min-height: 560px;
    display: flex;
    align-items: center;
    background:
        linear-gradient(140deg, rgba(30,10,24,0.96) 0%, rgba(155,29,64,0.90) 55%, rgba(200,55,95,0.82) 100%),
        url('images/site/image3.jpg') center/cover no-repeat;
    overflow: hidden;
    padding: 110px 0 100px;
}
.cb-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 65% 65% at 78% 48%, rgba(200,55,95,0.24), transparent),
        radial-gradient(ellipse 42% 42% at 8%  82%, rgba(201,147,58,0.10), transparent);
}
/* Orbs décoratifs */
.cb-orbs span {
    position: absolute; border-radius: 50%;
    opacity: .07; background: white;
}
.cb-orbs span:nth-child(1) { width:360px; height:360px; top:-90px;  right:-70px;  }
.cb-orbs span:nth-child(2) { width:200px; height:200px; bottom:30px; left:5%;     }
.cb-orbs span:nth-child(3) { width:110px; height:110px; top:70px;   left:37%;     }
.cb-orbs span:nth-child(4) { width: 60px; height: 60px; top:200px;  right:18%;    }

/* Heartbeat line — SVG décoratif */
.cb-hero-heartbeat {
    position: absolute;
    right: 0; top: 50%;
    transform: translateY(-50%);
    width: 48%; height: 140px;
    opacity: .08;
    pointer-events: none;
}

.cb-hero-content {
    position: relative; z-index: 2;
    max-width: 700px;
}
.cb-hero-tag {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,0.11);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.22);
    color: #FFD6E5;
    font-size: .74rem; font-weight: 700;
    letter-spacing: 2.5px; text-transform: uppercase;
    padding: 7px 18px; border-radius: 999px;
    margin-bottom: 22px;
}
.cb-hero-tag i { color: #FFB3CD; font-size: .9rem; }

.cb-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.3rem, 5vw, 4rem);
    font-weight: 700; color: white;
    line-height: 1.13; margin-bottom: 22px;
    text-shadow: 0 2px 24px rgba(0,0,0,.35);
}
.cb-hero h1 em { font-style: italic; color: #FFB3CD; display: block; }

.cb-hero-sub {
    font-size: 1.08rem; color: rgba(255,230,240,.85);
    line-height: 1.80; margin-bottom: 38px; max-width: 570px;
}

/* Stats */
.cb-hero-stats { display:flex; gap:28px; flex-wrap:wrap; }
.cb-hstat {
    text-align: center;
    background: rgba(255,255,255,.10);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,.16);
    border-radius: 18px; padding: 18px 28px; min-width: 112px;
}
.cb-hstat-num {
    display: block;
    font-family: 'Playfair Display', serif;
    font-size: 2.1rem; font-weight: 700; color: #FFD6E5; line-height: 1;
}
.cb-hstat-label {
    display: block; font-size: .72rem;
    color: rgba(255,210,225,.70);
    letter-spacing: 1.2px; text-transform: uppercase; margin-top: 6px;
}

/* Ruban elliptique bas */
.cb-hero-ribbon {
    position: absolute; bottom: 0; left: 0; right: 0;
    height: 52px; background: white;
    clip-path: ellipse(55% 100% at 50% 100%);
}

/* ═══════════════════════════════════════════════════════
   INTRO
   ═══════════════════════════════════════════════════════ */
.cb-intro {
    background: white;
    padding: 68px 0 28px;
    text-align: center;
}
.cb-intro h2 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.65rem, 3vw, 2.55rem);
    color: var(--cb-charcoal); margin-bottom: 14px;
}
.cb-intro h2 span { color: var(--cb-rose); }
.cb-intro p {
    max-width: 650px; margin: 0 auto;
    color: #4A5568; font-size: 1.06rem; line-height: 1.85;
}
.cb-divider {
    display: flex; align-items: center; justify-content: center;
    gap: 12px; margin: 28px auto;
}
.cb-divider-line { flex:1; max-width:80px; height:1px; background:var(--cb-rose-mid); }
.cb-divider-icon { color: var(--cb-rose); font-size: 1.1rem; }

/* Pills thématiques */
.cb-pills {
    display:flex; gap:10px; flex-wrap:wrap;
    justify-content:center; padding-bottom: 14px;
}
.cb-pill {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--cb-rose-pale); color: var(--cb-rose-dark);
    font-size: .78rem; font-weight: 700;
    padding: 7px 16px; border-radius: 999px;
    border: 1px solid var(--cb-rose-mid);
}
.cb-pill i { color: var(--cb-rose); font-size: .78rem; }

/* ═══════════════════════════════════════════════════════
   BANDE STATISTIQUES
   ═══════════════════════════════════════════════════════ */
.cb-stats-strip {
    background: linear-gradient(135deg, var(--cb-rose-pale) 0%, white 50%, var(--cb-rose-pale) 100%);
    padding: 52px 0;
    border-top: 1px solid var(--cb-rose-mid);
    border-bottom: 1px solid var(--cb-rose-mid);
}
.cb-stats-row {
    display: flex; align-items: center; justify-content: center;
}
.cb-stat-block {
    text-align: center; padding: 0 56px;
    position: relative;
}
.cb-stat-block + .cb-stat-block::before {
    content: '';
    position: absolute; left: 0; top: 50%; transform: translateY(-50%);
    width: 1px; height: 60px;
    background: linear-gradient(to bottom, transparent, var(--cb-rose-mid), transparent);
}
.cb-stat-block-num {
    display: block;
    font-family: 'Playfair Display', serif;
    font-size: 3.6rem; font-weight: 700;
    color: var(--cb-rose); line-height: 1;
    letter-spacing: -1px;
}
.cb-stat-block-label {
    display: block;
    font-size: .76rem; color: var(--cb-charcoal);
    text-transform: uppercase; letter-spacing: 1.6px;
    margin-top: 9px; font-weight: 600;
}
.cb-stat-block-icon {
    display: block; font-size: 1rem;
    color: var(--cb-rose-mid); margin-bottom: 12px;
}

/* ═══════════════════════════════════════════════════════
   SECTION GRILLE
   ═══════════════════════════════════════════════════════ */
.cb-section {
    background: linear-gradient(180deg, white 0%, #FFF5F8 40%, var(--cb-rose-pale) 100%);
    padding: 28px 0 90px;
}
.cb-section-head {
    text-align: center; margin-bottom: 10px;
}
.cb-section-head h3 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.4rem, 2.5vw, 2rem);
    color: var(--cb-charcoal); margin-bottom: 8px;
}
.cb-section-head p { font-size: .95rem; color: #6B7A8D; }

.cb-grid {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 32px; margin-top: 44px;
}

/* ═══════════════════════════════════════════════════════
   CARTE COMBATTANT
   ═══════════════════════════════════════════════════════ */
.cb-card {
    background: white; border-radius: 26px;
    overflow: hidden;
    box-shadow: 0 4px 28px rgba(200,55,95,.09);
    border: 1.5px solid var(--cb-rose-mid);
    transition: all .32s cubic-bezier(.34,1.2,.64,1);
    display: flex; flex-direction: column;
    position: relative;
}
.cb-card:hover {
    transform: translateY(-11px);
    box-shadow: 0 24px 56px rgba(200,55,95,.22);
    border-color: var(--cb-rose);
}

/* ── Bandeau photo ─────────────────────────────────── */
.cb-card-photo {
    position: relative; height: 260px; overflow: hidden;
    background:
        radial-gradient(ellipse 80% 80% at 50% 110%, rgba(255,255,255,.08) 0%, transparent 70%),
        linear-gradient(150deg, #2D1020 0%, var(--cb-rose-dark) 45%, var(--cb-rose) 100%);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.cb-card-photo::before {
    content: ''; position: absolute; inset: 0;
    background:
        radial-gradient(circle 130px at 18% 22%, rgba(255,255,255,.07), transparent),
        radial-gradient(circle 80px  at 82% 78%, rgba(255,255,255,.05), transparent);
    pointer-events: none; z-index: 0;
}
.cb-card-photo.has-bg::before {
    background-image: var(--cb-bg-img);
    background-size: cover; background-position: center top;
    opacity: .22;
}

/* Badge "COMBATTANT" — pill doré en haut à gauche */
.cb-ribbon {
    position: absolute; top: 14px; left: 14px;
    background: linear-gradient(135deg, var(--cb-gold), #DBA040);
    color: white; font-size: .55rem; font-weight: 900;
    letter-spacing: 1.6px; text-transform: uppercase;
    padding: 5px 13px 5px 10px;
    border-radius: 999px;
    box-shadow: 0 3px 14px rgba(0,0,0,.28);
    z-index: 5; white-space: nowrap;
    display: flex; align-items: center; gap: 5px;
}

/* Cercle photo — double halo */
.cb-circle {
    position: relative; z-index: 2;
    width: 150px; height: 150px; border-radius: 50%;
    border: 4px solid rgba(255,255,255,.52);
    box-shadow:
        0 0 0  8px rgba(255,255,255,.13),
        0 0 0 16px rgba(255,255,255,.05),
        0 14px 36px rgba(0,0,0,.32);
    overflow: hidden;
    background: linear-gradient(135deg, var(--cb-rose-dark), var(--cb-rose));
    display: flex; align-items: center; justify-content: center;
    transition: transform .45s cubic-bezier(.34,1.2,.64,1), box-shadow .45s ease;
    flex-shrink: 0;
}
.cb-card:hover .cb-circle {
    transform: scale(1.06) translateY(-5px);
    box-shadow:
        0 0 0  8px rgba(255,255,255,.22),
        0 0 0 18px rgba(255,255,255,.09),
        0 0 0 28px rgba(255,255,255,.03),
        0 22px 44px rgba(0,0,0,.40);
}
.cb-circle img {
    width:100%; height:100%;
    object-fit: cover; object-position: top center;
    transition: transform .45s ease;
}
.cb-card:hover .cb-circle img { transform: scale(1.07); }

.cb-avatar {
    width:100%; height:100%; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-family:'Playfair Display',serif;
    font-size: 2.7rem; font-weight: 700; color: white;
    text-shadow: 0 2px 10px rgba(0,0,0,.25);
}

/* Badge années */
.cb-years-badge {
    position: absolute; top: 14px; right: 14px;
    background: white; border-radius: 999px;
    padding: 6px 14px;
    display: flex; align-items: center; gap: 6px;
    box-shadow: 0 4px 16px rgba(0,0,0,.18);
    z-index: 4;
}
.cb-years-badge .num {
    font-family:'Playfair Display',serif;
    font-size: 1.25rem; font-weight: 700;
    color: var(--cb-rose); line-height: 1;
}
.cb-years-badge .unit {
    font-size: .64rem; color: var(--cb-rose-dark);
    font-weight: 700; letter-spacing: .5px;
    line-height: 1.2; text-transform: uppercase;
}

/* Bandelette cancer */
.cb-cancer-strip {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: linear-gradient(transparent, rgba(45,16,32,.78));
    padding: 24px 16px 12px; z-index: 3;
}
.cb-cancer-type {
    display: inline-block;
    background: rgba(200,55,95,.88);
    color: white; font-size: .72rem; font-weight: 700;
    letter-spacing: 1px; text-transform: uppercase;
    padding: 4px 12px; border-radius: 999px;
}

/* ── Corps carte ────────────────────────────────────── */
.cb-card-body {
    padding: 22px 22px 0; flex:1;
    display: flex; flex-direction: column;
    border-top: 3px solid var(--cb-rose-pale);
    transition: border-top-color .32s;
}
.cb-card:hover .cb-card-body {
    border-top-color: var(--cb-rose-mid);
}
/* Trait décoratif sous le nom */
.cb-card-name-line {
    width: 36px; height: 3px;
    background: linear-gradient(90deg, var(--cb-rose), var(--cb-rose-mid));
    border-radius: 999px;
    margin: 6px 0 12px;
}
.cb-card-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.28rem; font-weight: 700;
    color: var(--cb-charcoal); margin-bottom: 5px; line-height: 1.2;
}
.cb-card-meta {
    display: flex; align-items: center; gap: 8px;
    font-size: .80rem; color: #7A5060; margin-bottom: 14px;
}
.cb-card-meta i { color: var(--cb-rose); font-size: .74rem; }
.cb-card-meta-sep { color: var(--cb-rose-mid); }

.cb-card-excerpt {
    font-size: .93rem; color: #4A5568; line-height: 1.78;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 4; -webkit-box-orient: vertical;
    line-clamp: 4;
    overflow: hidden; margin-bottom: 16px;
}

/* Citation / message de force */
.cb-card-quote {
    background: var(--cb-rose-pale);
    border-left: 3px solid var(--cb-rose);
    border-radius: 0 10px 10px 0;
    padding: 10px 14px; margin-bottom: 18px;
    font-style: italic; font-size: .85rem;
    color: #6B2040; line-height: 1.6;
    position: relative;
}
.cb-card-quote::before {
    content: '\201C';
    position: absolute; top: -5px; left: 8px;
    font-size: 2rem; color: var(--cb-rose);
    opacity: .28; font-family: serif; line-height: 1;
}

/* Footer carte */
.cb-card-footer {
    padding: 0 22px 22px;
    display: flex; align-items: center;
    justify-content: space-between; gap: 12px;
}
.cb-card-location {
    display: flex; align-items: center; gap: 5px;
    font-size: .80rem; color: #7A5060;
}
.cb-card-location i { color: var(--cb-rose); }

.cb-btn-read {
    display: inline-flex; align-items: center; gap: 7px;
    background: var(--cb-rose); color: white;
    font-size: .82rem; font-weight: 700;
    padding: 9px 18px; border-radius: 999px;
    text-decoration: none; transition: all .22s;
    white-space: nowrap; letter-spacing: .3px;
}
.cb-btn-read:hover {
    background: var(--cb-rose-dark); color: white;
    transform: translateX(3px);
}
.cb-btn-read i { font-size: .74rem; transition: transform .2s; }
.cb-btn-read:hover i { transform: translateX(3px); }

/* ═══════════════════════════════════════════════════════
   CITATION SPOTLIGHT
   ═══════════════════════════════════════════════════════ */
.cb-spotlight {
    background: linear-gradient(135deg, var(--cb-rose-dark) 0%, var(--cb-rose) 60%, #E8849F 100%);
    padding: 80px 0;
    position: relative; overflow: hidden;
    text-align: center;
}
.cb-spotlight::before {
    content: '\201C';
    position: absolute; top: -20px; left: 50%; transform: translateX(-50%);
    font-family: 'Playfair Display', serif;
    font-size: 14rem; color: rgba(255,255,255,.06);
    line-height: 1; pointer-events: none; user-select: none;
}
.cb-spotlight::after {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse 80% 80% at 50% 0%, rgba(255,255,255,.07), transparent);
}
.cb-spotlight-inner {
    position: relative; z-index: 1;
    max-width: 760px; margin: 0 auto;
}
.cb-spotlight-tag {
    display: inline-flex; align-items: center; gap: 7px;
    background: rgba(255,255,255,.14); color: #FFD6E5;
    font-size: .70rem; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; padding: 6px 16px;
    border-radius: 999px; border: 1px solid rgba(255,255,255,.20);
    margin-bottom: 24px;
}
.cb-spotlight blockquote {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.25rem, 2.8vw, 1.85rem);
    font-style: italic; color: white; line-height: 1.65;
    margin: 0 0 22px; font-weight: 400;
    text-shadow: 0 2px 20px rgba(0,0,0,.2);
}
.cb-spotlight-attr {
    font-size: .82rem; color: rgba(255,210,225,.75);
    font-weight: 600; letter-spacing: 1px;
}
/* Étoiles déco */
.cb-spotlight-stars {
    display: flex; justify-content: center; gap: 8px;
    margin-bottom: 20px;
}
.cb-spotlight-stars i { color: rgba(255,255,255,.30); font-size: .75rem; }
.cb-spotlight-stars i:nth-child(3) { color: rgba(255,255,255,.65); font-size: .9rem; }

/* ═══════════════════════════════════════════════════════
   MODAL HISTOIRE COMPLÈTE
   ═══════════════════════════════════════════════════════ */
.cb-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(45,16,32,.80);
    backdrop-filter: blur(6px);
    z-index: 9000;
    align-items: flex-start; justify-content: center;
    padding: 40px 16px; overflow-y: auto;
}
.cb-modal-overlay.open { display: flex; }

.cb-modal {
    background: white; border-radius: 28px;
    width: 100%; max-width: 780px;
    margin: auto; overflow: hidden;
    box-shadow: 0 32px 80px rgba(45,16,32,.42);
    animation: cbIn .38s cubic-bezier(.34,1.3,.64,1) both;
}
@keyframes cbIn {
    from { opacity:0; transform: translateY(40px) scale(.96); }
    to   { opacity:1; transform: translateY(0)    scale(1);   }
}

.cb-modal-header {
    position: relative; height: 290px;
    background: linear-gradient(135deg, #2D1020, var(--cb-rose-dark), var(--cb-rose));
    display: flex; align-items: flex-end;
    padding: 28px; overflow: hidden;
}
.cb-modal-hbg { position: absolute; inset: 0; overflow: hidden; }
.cb-modal-hbg img {
    width:100%; height:100%; object-fit:cover; object-position:top center;
    opacity:.32; mix-blend-mode:luminosity;
}
.cb-modal-hoverlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(45,16,32,.88) 0%, rgba(45,16,32,.20) 100%);
}
.cb-modal-hcontent {
    position: relative; z-index: 2;
    display: flex; align-items: flex-end; gap: 20px; width: 100%;
}
.cb-modal-avatar {
    width: 84px; height: 84px; border-radius: 50%;
    border: 3px solid rgba(255,255,255,.52);
    background: rgba(255,255,255,.14);
    display: flex; align-items: center; justify-content: center;
    font-family:'Playfair Display',serif;
    font-size: 1.9rem; font-weight: 700; color: white;
    flex-shrink: 0; overflow: hidden;
}
.cb-modal-avatar img { width:100%; height:100%; object-fit:cover; object-position:top; }
.cb-modal-identity { flex: 1; }
.cb-modal-identity h3 {
    font-family:'Playfair Display',serif;
    font-size: 1.75rem; color: white;
    margin: 0 0 7px; line-height: 1.2;
}
.cb-modal-pills { display:flex; gap:8px; flex-wrap:wrap; }
.cb-modal-pill {
    background: rgba(255,255,255,.16); color: #FFD6E5;
    font-size: .72rem; font-weight: 700;
    letter-spacing: 1px; padding: 4px 12px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.20);
    text-transform: uppercase;
}
.cb-modal-close {
    position: absolute; top: 16px; right: 16px; z-index: 10;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.25);
    color: white; width: 42px; height: 42px;
    border-radius: 50%; display: flex;
    align-items: center; justify-content: center;
    cursor: pointer; font-size: 1rem;
    transition: background .2s; backdrop-filter: blur(4px);
}
.cb-modal-close:hover { background: rgba(200,55,95,.55); }

.cb-modal-body {
    padding: 38px; max-height: 60vh; overflow-y: auto;
}
.cb-modal-body::-webkit-scrollbar { width: 5px; }
.cb-modal-body::-webkit-scrollbar-track { background: var(--cb-rose-pale); }
.cb-modal-body::-webkit-scrollbar-thumb { background: var(--cb-rose-mid); border-radius: 3px; }

.cb-modal-story {
    font-size: 1rem; color: #3D2030; line-height: 2.05; margin-bottom: 28px;
}
.cb-modal-story p { margin-bottom: 18px; }

.cb-modal-force {
    background: linear-gradient(135deg, var(--cb-rose-pale), #FFF0F8);
    border-radius: 16px; padding: 24px 28px;
    border-left: 4px solid var(--cb-rose);
    position: relative; overflow: hidden;
}
.cb-modal-force::before {
    content: '\201C';
    position: absolute; top: -10px; left: 14px;
    font-size: 5rem; color: var(--cb-rose);
    opacity: .10; font-family:'Playfair Display',serif; line-height: 1;
}
.cb-modal-force-label {
    font-size: .70rem; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    color: var(--cb-rose); margin-bottom: 10px;
    display: flex; align-items: center; gap: 8px;
}
.cb-modal-force-label::after {
    content: ''; flex:1; height: 1px; background: var(--cb-rose-mid);
}
.cb-modal-force p {
    font-family:'Playfair Display',serif;
    font-style: italic; font-size: 1.15rem;
    color: var(--cb-rose-dark); line-height: 1.7; margin: 0;
}

/* ═══════════════════════════════════════════════════════
   CTA FINAL
   ═══════════════════════════════════════════════════════ */
.cb-cta {
    background: linear-gradient(135deg, #2D1020 0%, var(--cb-rose-dark) 50%, var(--cb-rose) 100%);
    padding: 88px 0; text-align: center;
    position: relative; overflow: hidden;
}
.cb-cta::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse 70% 70% at 50% 0%, rgba(255,255,255,.07), transparent);
}
.cb-cta h2 {
    font-family:'Playfair Display',serif;
    font-size: clamp(1.7rem, 3vw, 2.6rem);
    color: white; margin-bottom: 14px; position: relative;
}
.cb-cta p {
    color: rgba(255,220,235,.88); font-size: 1.05rem;
    max-width: 580px; margin: 0 auto 34px; position: relative;
}
.cb-cta-btns {
    display:flex; gap:16px; justify-content:center;
    flex-wrap:wrap; position: relative;
}
.cb-btn-w {
    display: inline-flex; align-items: center; gap: 9px;
    background: white; color: var(--cb-rose-dark);
    font-weight: 700; font-size: .96rem;
    padding: 14px 34px; border-radius: 999px;
    text-decoration: none; transition: all .25s;
    box-shadow: 0 8px 24px rgba(0,0,0,.18);
}
.cb-btn-w:hover {
    background: var(--cb-rose-pale); color: var(--cb-rose-dark);
    transform: translateY(-3px);
}
.cb-btn-ow {
    display: inline-flex; align-items: center; gap: 9px;
    background: rgba(255,255,255,.12); color: white;
    font-weight: 700; font-size: .96rem;
    padding: 14px 34px; border-radius: 999px;
    text-decoration: none;
    border: 2px solid rgba(255,255,255,.34);
    transition: all .25s; backdrop-filter: blur(4px);
}
.cb-btn-ow:hover {
    background: rgba(255,255,255,.22); color: white;
    transform: translateY(-3px);
}

/* ═══════════════════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════════════════ */
@media (max-width: 1024px) {
    .cb-grid { grid-template-columns: repeat(2,1fr); gap: 24px; }
    .cb-stat-block { padding: 0 36px; }
}
@media (max-width: 720px) {
    .cb-stats-row { flex-direction: column; gap: 28px; }
    .cb-stat-block + .cb-stat-block::before { display: none; }
    .cb-stat-block-num { font-size: 2.8rem; }
}
@media (max-width: 640px) {
    .cb-grid { grid-template-columns: 1fr; gap: 20px; }
    .cb-hero-stats { gap: 14px; }
    .cb-hstat { padding: 12px 16px; min-width: 85px; }
    .cb-hstat-num { font-size: 1.6rem; }
    .cb-hero { min-height: 460px; padding: 80px 0 80px; }
    .cb-modal-body { padding: 22px; }
    .cb-modal-header { height: 230px; }
    .cb-modal-identity h3 { font-size: 1.3rem; }
    .cb-spotlight blockquote { font-size: 1.12rem; }
}
</style>

<!-- ════════════════════════════════════════════════════
     HERO
     ════════════════════════════════════════════════════ -->
<section class="cb-hero">
    <div class="cb-orbs" aria-hidden="true">
        <span></span><span></span><span></span><span></span>
    </div>

    <!-- Ligne heartbeat décorative -->
    <svg class="cb-hero-heartbeat" viewBox="0 0 600 140" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <polyline points="0,70 80,70 110,20 140,120 170,40 200,90 230,70 340,70 370,10 400,130 430,50 460,80 490,70 600,70"
                  stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>

    <div class="container">
        <div class="cb-hero-content" data-aos="fade-up">
            <div class="cb-hero-tag">
                <i class="fas fa-shield-heart"></i>
                Courage &amp; Résilience
            </div>
            <h1>
                Ils vivent avec le cancer.
                <em>Chaque jour est une victoire.</em>
            </h1>
            <p class="cb-hero-sub">
                Ces femmes et ces hommes font face au cancer depuis des années — non pas
                en silence, mais avec une force qui inspire toute la communauté GSCC.
                Leurs histoires méritent d'être entendues.
            </p>
            <div class="cb-hero-stats">
                <div class="cb-hstat">
                    <span class="cb-hstat-num"><?= $total ?></span>
                    <span class="cb-hstat-label">Combattants</span>
                </div>
                <div class="cb-hstat">
                    <span class="cb-hstat-num"><?= $max_annees ?>+</span>
                    <span class="cb-hstat-label">Ans de combat</span>
                </div>
                <div class="cb-hstat">
                    <span class="cb-hstat-num"><?= $avg_annees ?></span>
                    <span class="cb-hstat-label">Ans en moy.</span>
                </div>
            </div>
        </div>
    </div>
    <div class="cb-hero-ribbon" aria-hidden="true"></div>
</section>

<!-- ════════════════════════════════════════════════════
     INTRO
     ════════════════════════════════════════════════════ -->
<section class="cb-intro">
    <div class="container" data-aos="fade-up">
        <h2>Chaque jour vécu est <span>un acte de bravoure</span></h2>
        <p>
            Vivre avec le cancer n'est pas seulement un combat médical — c'est un acte de
            résistance quotidien. Ces combattants ont choisi de partager leur parcours pour
            que personne ne se sente seul dans cette lutte. Lisez leurs histoires.
            Laissez-vous inspirer.
        </p>
        <div class="cb-divider">
            <span class="cb-divider-line"></span>
            <i class="fas fa-heart cb-divider-icon"></i>
            <span class="cb-divider-line"></span>
        </div>
        <div class="cb-pills">
            <span class="cb-pill"><i class="fas fa-heart-pulse"></i> Résilience</span>
            <span class="cb-pill"><i class="fas fa-shield-heart"></i> Courage</span>
            <span class="cb-pill"><i class="fas fa-hands-holding-heart"></i> Solidarité</span>
            <span class="cb-pill"><i class="fas fa-sun"></i> Espoir</span>
            <span class="cb-pill"><i class="fas fa-ribbon"></i> Sensibilisation</span>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════
     BANDE STATS
     ════════════════════════════════════════════════════ -->
<section class="cb-stats-strip">
    <div class="container">
        <div class="cb-stats-row">
            <div class="cb-stat-block" data-aos="zoom-in" data-aos-delay="0">
                <i class="fas fa-users cb-stat-block-icon"></i>
                <span class="cb-stat-block-num"><?= $total ?></span>
                <span class="cb-stat-block-label">Combattants soutenus</span>
            </div>
            <div class="cb-stat-block" data-aos="zoom-in" data-aos-delay="100">
                <i class="fas fa-trophy cb-stat-block-icon"></i>
                <span class="cb-stat-block-num"><?= $max_annees ?>+</span>
                <span class="cb-stat-block-label">Années — record absolu</span>
            </div>
            <div class="cb-stat-block" data-aos="zoom-in" data-aos-delay="200">
                <i class="fas fa-chart-line cb-stat-block-icon"></i>
                <span class="cb-stat-block-num"><?= $avg_annees ?></span>
                <span class="cb-stat-block-label">Ans de combat en moyenne</span>
            </div>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════
     GRILLE DES COMBATTANTS
     ════════════════════════════════════════════════════ -->
<section class="cb-section">
    <div class="container">
        <div class="cb-section-head" data-aos="fade-up">
            <h3>Portraits de combat</h3>
            <p>Chaque visage porte une histoire. Chaque histoire porte un espoir.</p>
        </div>

        <?php if (empty($combattants)): ?>
            <div style="text-align:center;padding:70px 20px;color:#7A5060;">
                <i class="fas fa-shield-heart" style="font-size:3.2rem;opacity:.28;margin-bottom:18px;display:block;color:var(--cb-rose-mid);"></i>
                <p style="font-size:1rem;">Aucun combattant publié pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="cb-grid">
                <?php foreach ($combattants as $i => $c):
                    $initiales = strtoupper(substr($c['prenom'],0,1) . substr($c['nom'],0,1));
                    $has_photo = !empty($c['photo']);
                    $photo_url = $has_photo ? rtrim(SITE_URL,'/').'/uploads/'.$c['photo'] : null;
                    $has_long  = !empty(trim($c['histoire_longue'] ?? ''));
                ?>
                <div class="cb-card" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 100 ?>">

                    <!-- Header visuel -->
                    <div class="cb-card-photo<?= $has_photo ? ' has-bg' : '' ?>"
                         <?= $has_photo ? 'style="--cb-bg-img:url(\''.e($photo_url).'\')"' : '' ?>>

                        <!-- Badge doré "COMBATTANT" -->
                        <div class="cb-ribbon" aria-hidden="true">
                            <i class="fas fa-shield-halved"></i> Combattant
                        </div>

                        <!-- Cercle photo double halo -->
                        <div class="cb-circle">
                            <?php if ($photo_url): ?>
                                <img src="<?= e($photo_url) ?>"
                                     alt="Photo de <?= e($c['prenom'].' '.$c['nom']) ?>"
                                     onerror="this.style.display='none';">
                            <?php else: ?>
                                <div class="cb-avatar"><?= $initiales ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Badge années -->
                        <div class="cb-years-badge">
                            <span class="num"><?= (int)$c['annees_combat'] ?></span>
                            <span class="unit">ans<br>combat</span>
                        </div>

                        <!-- Type cancer -->
                        <div class="cb-cancer-strip">
                            <span class="cb-cancer-type">
                                <i class="fas fa-ribbon"></i> <?= e($c['cancer_type']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Corps -->
                    <div class="cb-card-body">
                        <div class="cb-card-name"><?= e($c['prenom'].' '.$c['nom']) ?></div>
                        <div class="cb-card-name-line" aria-hidden="true"></div>
                        <div class="cb-card-meta">
                            <?php if ($c['age_diagnostic']): ?>
                                <i class="fas fa-user"></i>
                                Diagnostiqué(e) à <?= (int)$c['age_diagnostic'] ?> ans
                                <span class="cb-card-meta-sep">·</span>
                            <?php endif; ?>
                            <i class="fas fa-calendar-check"></i>
                            En combat depuis <?= (int)$c['annees_combat'] ?> an<?= $c['annees_combat']>1?'s':'' ?>
                        </div>
                        <p class="cb-card-excerpt"><?= e($c['histoire_courte']) ?></p>

                        <?php if (!empty($c['message_force'])): ?>
                            <div class="cb-card-quote"><?= e($c['message_force']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Footer carte -->
                    <div class="cb-card-footer">
                        <div class="cb-card-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= e($c['ville']) ?>
                        </div>
                        <?php if ($has_long): ?>
                            <a href="#" class="cb-btn-read"
                               onclick="cbOpen(<?= (int)$c['id'] ?>);return false;">
                                Lire la suite <i class="fas fa-arrow-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ════════════════════════════════════════════════════
     CITATION SPOTLIGHT
     ════════════════════════════════════════════════════ -->
<section class="cb-spotlight" data-aos="fade-up">
    <div class="container">
        <div class="cb-spotlight-inner">
            <div class="cb-spotlight-tag">
                <i class="fas fa-quote-left"></i> Parole de combattant
            </div>
            <div class="cb-spotlight-stars">
                <i class="fas fa-star"></i><i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i><i class="fas fa-star"></i>
            </div>
            <blockquote>
                « Chaque matin que j'ouvre les yeux est une victoire que le cancer
                ne m'a pas prise. Je me lève. Je me bats. Je vis. »
            </blockquote>
            <p class="cb-spotlight-attr">— Un combattant GSCC, Haïti</p>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════
     MODALS
     ════════════════════════════════════════════════════ -->
<?php foreach ($combattants as $c):
    if (empty(trim($c['histoire_longue'] ?? ''))) continue;
    $initiales = strtoupper(substr($c['prenom'],0,1).substr($c['nom'],0,1));
    $has_photo = !empty($c['photo']);
    $photo_url = $has_photo ? rtrim(SITE_URL,'/').'/uploads/'.$c['photo'] : null;
    $paragraphes = array_filter(explode("\n", trim($c['histoire_longue'])));
?>
<div class="cb-modal-overlay" id="cb-modal-<?= (int)$c['id'] ?>"
     onclick="if(event.target===this)cbClose(<?= (int)$c['id'] ?>)">
    <div class="cb-modal" role="dialog" aria-modal="true"
         aria-label="Histoire de <?= e($c['prenom'].' '.$c['nom']) ?>">

        <div class="cb-modal-header">
            <div class="cb-modal-hbg">
                <?php if ($photo_url): ?>
                    <img src="<?= e($photo_url) ?>" alt="" aria-hidden="true">
                <?php endif; ?>
            </div>
            <div class="cb-modal-hoverlay"></div>
            <button class="cb-modal-close" onclick="cbClose(<?= (int)$c['id'] ?>)" aria-label="Fermer">
                <i class="fas fa-times"></i>
            </button>
            <div class="cb-modal-hcontent">
                <div class="cb-modal-avatar">
                    <?php if ($photo_url): ?>
                        <img src="<?= e($photo_url) ?>" alt="<?= e($c['prenom']) ?>">
                    <?php else: ?>
                        <?= $initiales ?>
                    <?php endif; ?>
                </div>
                <div class="cb-modal-identity">
                    <h3><?= e($c['prenom'].' '.$c['nom']) ?></h3>
                    <div class="cb-modal-pills">
                        <span class="cb-modal-pill">
                            <i class="fas fa-ribbon"></i> <?= e($c['cancer_type']) ?>
                        </span>
                        <span class="cb-modal-pill">
                            <?= (int)$c['annees_combat'] ?> an<?= $c['annees_combat']>1?'s':'' ?> de combat
                        </span>
                        <span class="cb-modal-pill">
                            <i class="fas fa-map-marker-alt"></i> <?= e($c['ville']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="cb-modal-body">
            <div class="cb-modal-story">
                <?php foreach ($paragraphes as $p): ?>
                    <p><?= e(trim($p)) ?></p>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($c['message_force'])): ?>
                <div class="cb-modal-force">
                    <div class="cb-modal-force-label">
                        <i class="fas fa-heart"></i> Message de force
                    </div>
                    <p><?= e($c['message_force']) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- ════════════════════════════════════════════════════
     CTA FINAL
     ════════════════════════════════════════════════════ -->
<section class="cb-cta">
    <div class="container" data-aos="fade-up">
        <h2>Vous aussi, vous vous battez ?</h2>
        <p>
            Votre histoire peut allumer une étincelle d'espoir pour quelqu'un qui lutte
            en ce moment. Rejoignez la communauté GSCC — nous marchons avec vous.
        </p>
        <div class="cb-cta-btns">
            <a href="contact.php" class="cb-btn-w">
                <i class="fas fa-pen-nib"></i> Partager mon histoire
            </a>
            <a href="faire-un-don.php" class="cb-btn-ow">
                <i class="fas fa-heart"></i> Soutenir nos combattants
            </a>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════
     JS MODAL
     ════════════════════════════════════════════════════ -->
<script>
function cbOpen(id) {
    var el = document.getElementById('cb-modal-' + id);
    if (!el) return;
    el.classList.add('open');
    document.body.style.overflow = 'hidden';
    setTimeout(function() {
        var btn = el.querySelector('.cb-modal-close');
        if (btn) btn.focus();
    }, 100);
}
function cbClose(id) {
    var el = document.getElementById('cb-modal-' + id);
    if (!el) return;
    el.classList.remove('open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.cb-modal-overlay.open').forEach(function(el) {
            el.classList.remove('open');
            document.body.style.overflow = '';
        });
    }
});
</script>

<?php require_once 'templates/footer.php'; ?>
