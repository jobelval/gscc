<?php
// survivants.php — Histoires de survivants du cancer
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// ═══════════════════════════════════════════════════════════════
// RÉCUPÉRATION DES DONNÉES
// ═══════════════════════════════════════════════════════════════
$survivants = [];
try {
    $stmt = $pdo->prepare("
        SELECT * FROM survivants
        WHERE statut = 'publie'
        ORDER BY annees_survie DESC
    ");
    $stmt->execute();
    $survivants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Survivant sélectionné (pour le modal / lecture complète)
$selected_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$selected = null;
if ($selected_id) {
    foreach ($survivants as $s) {
        if ($s['id'] == $selected_id) { $selected = $s; break; }
    }
}

// Stats globales
$total       = count($survivants);
$max_annees  = $total ? max(array_column($survivants, 'annees_survie')) : 0;
$avg_annees  = $total ? round(array_sum(array_column($survivants, 'annees_survie')) / $total, 1) : 0;

$page_title       = 'Histoires de Survivants — GSCC';
$page_description = 'Découvrez les témoignages inspirants de survivants du cancer en Haïti, suivis par le GSCC.';
require_once 'templates/header.php';
?>

<!-- ══════════════════════════════════════════════════════════
     STYLES SPÉCIFIQUES À CETTE PAGE
     ══════════════════════════════════════════════════════════ -->
<style>
/* ── Variables locales ───────────────────────────────────── */
:root {
    --surv-rose:       #C8375F;
    --surv-rose-dark:  #9B1D40;
    --surv-rose-pale:  #FCE8EF;
    --surv-rose-mid:   #F5C6D5;
    --surv-gold:       #C9933A;
    --surv-charcoal:   #1E2A35;
}

/* ── HERO ─────────────────────────────────────────────────── */
.surv-hero {
    position: relative;
    min-height: 520px;
    display: flex;
    align-items: center;
    background:
        linear-gradient(135deg, rgba(45,20,32,0.92) 0%, rgba(155,29,64,0.85) 60%, rgba(200,55,95,0.78) 100%),
        url('images/image1.jpg') center/cover no-repeat;
    overflow: hidden;
    padding: 100px 0 80px;
}
.surv-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 60% 60% at 80% 50%, rgba(200,55,95,0.22), transparent),
        radial-gradient(ellipse 40% 40% at 10% 80%, rgba(255,255,255,0.04), transparent);
}
/* Petits cercles décoratifs */
.surv-hero-orbs span {
    position: absolute;
    border-radius: 50%;
    opacity: .07;
    background: white;
}
.surv-hero-orbs span:nth-child(1) { width:320px;height:320px; top:-80px; right:-60px; }
.surv-hero-orbs span:nth-child(2) { width:180px;height:180px; bottom:40px; left:8%; }
.surv-hero-orbs span:nth-child(3) { width:90px;height:90px;   top:60px;   left:40%; }

.surv-hero-content {
    position: relative;
    z-index: 2;
    max-width: 700px;
}
.surv-hero-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.22);
    color: #FFD6E5;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    padding: 7px 16px;
    border-radius: 999px;
    margin-bottom: 22px;
}
.surv-hero-tag i { color: #FFB3CD; font-size: 0.9rem; }
.surv-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.2rem, 5vw, 3.8rem);
    font-weight: 700;
    color: white;
    line-height: 1.15;
    margin-bottom: 22px;
    text-shadow: 0 2px 20px rgba(0,0,0,0.3);
}
.surv-hero h1 em {
    font-style: italic;
    color: #FFB3CD;
    display: block;
}
.surv-hero-sub {
    font-size: 1.1rem;
    color: rgba(255,230,240,0.85);
    line-height: 1.75;
    margin-bottom: 36px;
    max-width: 560px;
}
.surv-hero-stats {
    display: flex;
    gap: 32px;
    flex-wrap: wrap;
}
.surv-hstat {
    text-align: center;
    background: rgba(255,255,255,0.10);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 16px;
    padding: 16px 24px;
    min-width: 100px;
}
.surv-hstat-num {
    display: block;
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    font-weight: 700;
    color: #FFD6E5;
    line-height: 1;
}
.surv-hstat-label {
    display: block;
    font-size: 0.75rem;
    color: rgba(255,210,225,0.72);
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-top: 5px;
}

/* Ruban décoratif bas du hero */
.surv-hero-ribbon {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 48px;
    background: white;
    clip-path: ellipse(55% 100% at 50% 100%);
}

/* ── INTRO STRIP ─────────────────────────────────────────── */
.surv-intro {
    background: white;
    padding: 60px 0 20px;
    text-align: center;
}
.surv-intro h2 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.6rem, 3vw, 2.4rem);
    color: var(--surv-charcoal);
    margin-bottom: 14px;
}
.surv-intro h2 span { color: var(--surv-rose); }
.surv-intro p {
    max-width: 640px;
    margin: 0 auto;
    color: #4A5568;
    font-size: 1.05rem;
    line-height: 1.8;
}
.surv-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin: 28px auto;
}
.surv-divider-line { flex: 1; max-width: 80px; height: 1px; background: var(--surv-rose-mid); }
.surv-divider-heart { color: var(--surv-rose); font-size: 1.1rem; }

/* ── GRILLE SURVIVANTS ───────────────────────────────────── */
.surv-section {
    background: linear-gradient(180deg, white 0%, #FFF5F8 40%, #FCE8EF 100%);
    padding: 20px 0 80px;
}
.surv-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
    margin-top: 40px;
}

/* ── CARTE SURVIVANT ─────────────────────────────────────── */
.surv-card {
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(200,55,95,0.08);
    border: 1.5px solid var(--surv-rose-mid);
    transition: all 0.32s cubic-bezier(.34,1.2,.64,1);
    display: flex;
    flex-direction: column;
    position: relative;
}
.surv-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 48px rgba(200,55,95,0.18);
    border-color: var(--surv-rose);
}

/* Photo / Avatar */
.surv-card-photo {
    position: relative;
    height: 220px;
    overflow: hidden;
    background: linear-gradient(135deg, var(--surv-rose-dark), var(--surv-rose));
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.surv-card-photo img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: top center;
    transition: transform 0.5s ease;
    display: block;
}
.surv-card:hover .surv-card-photo img { transform: scale(1.06); }

/* Avatar fallback initiales */
.surv-avatar-fallback {
    width: 90px; height: 90px;
    border-radius: 50%;
    background: rgba(255,255,255,0.18);
    border: 3px solid rgba(255,255,255,0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Playfair Display', serif;
    font-size: 2.2rem;
    font-weight: 700;
    color: white;
    text-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

/* Badge années de survie */
.surv-years-badge {
    position: absolute;
    top: 14px; right: 14px;
    background: white;
    border-radius: 999px;
    padding: 6px 14px;
    display: flex;
    align-items: center;
    gap: 5px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}
.surv-years-badge .num {
    font-family: 'Playfair Display', serif;
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--surv-rose);
    line-height: 1;
}
.surv-years-badge .unit {
    font-size: 0.68rem;
    color: #6B3048;
    font-weight: 600;
    letter-spacing: 0.5px;
    line-height: 1.2;
    text-transform: uppercase;
}

/* Bandelette type cancer */
.surv-cancer-strip {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    background: linear-gradient(transparent, rgba(45,20,32,0.75));
    padding: 20px 16px 10px;
}
.surv-cancer-type {
    display: inline-block;
    background: rgba(200,55,95,0.85);
    color: white;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 999px;
}

/* Corps de la carte */
.surv-card-body {
    padding: 22px 22px 0;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.surv-card-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--surv-charcoal);
    margin-bottom: 4px;
    line-height: 1.2;
}
.surv-card-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.8rem;
    color: #7A5060;
    margin-bottom: 14px;
}
.surv-card-meta i { color: var(--surv-rose); font-size: 0.75rem; }
.surv-card-meta-sep { color: var(--surv-rose-mid); }

.surv-card-excerpt {
    font-size: 0.92rem;
    color: #4A5568;
    line-height: 1.75;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 16px;
}

/* Citation courte */
.surv-card-quote {
    background: var(--surv-rose-pale);
    border-left: 3px solid var(--surv-rose);
    border-radius: 0 10px 10px 0;
    padding: 10px 14px;
    margin-bottom: 20px;
    font-style: italic;
    font-size: 0.85rem;
    color: #6B2040;
    line-height: 1.6;
    position: relative;
}
.surv-card-quote::before {
    content: '\201C';
    position: absolute;
    top: -5px; left: 8px;
    font-size: 2rem;
    color: var(--surv-rose);
    opacity: 0.3;
    font-family: serif;
    line-height: 1;
}

/* Footer carte */
.surv-card-footer {
    padding: 0 22px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.surv-card-location {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.8rem;
    color: #7A5060;
}
.surv-card-location i { color: var(--surv-rose); }
.surv-btn-read {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: var(--surv-rose);
    color: white;
    font-size: 0.82rem;
    font-weight: 700;
    padding: 9px 18px;
    border-radius: 999px;
    text-decoration: none;
    transition: all 0.22s;
    white-space: nowrap;
    letter-spacing: 0.3px;
}
.surv-btn-read:hover {
    background: var(--surv-rose-dark);
    color: white;
    transform: translateX(3px);
}
.surv-btn-read i { font-size: 0.75rem; transition: transform 0.2s; }
.surv-btn-read:hover i { transform: translateX(3px); }

/* ── MODAL HISTOIRE COMPLÈTE ─────────────────────────────── */
.surv-modal-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(45,20,32,0.75);
    backdrop-filter: blur(6px);
    z-index: 9000;
    align-items: flex-start;
    justify-content: center;
    padding: 40px 16px;
    overflow-y: auto;
}
.surv-modal-overlay.open { display: flex; }

.surv-modal {
    background: white;
    border-radius: 28px;
    width: 100%;
    max-width: 760px;
    margin: auto;
    overflow: hidden;
    box-shadow: 0 32px 80px rgba(45,20,32,0.35);
    animation: modalIn 0.38s cubic-bezier(.34,1.3,.64,1) both;
}
@keyframes modalIn {
    from { opacity:0; transform: translateY(40px) scale(0.96); }
    to   { opacity:1; transform: translateY(0) scale(1); }
}

/* En-tête modal */
.surv-modal-header {
    position: relative;
    height: 280px;
    background: linear-gradient(135deg, var(--surv-rose-dark), var(--surv-rose));
    display: flex;
    align-items: flex-end;
    padding: 28px;
    overflow: hidden;
}
.surv-modal-header-bg {
    position: absolute; inset: 0;
    background: inherit;
    overflow: hidden;
}
.surv-modal-header-bg img {
    width: 100%; height: 100%;
    object-fit: cover;
    object-position: top center;
    opacity: 0.35;
    mix-blend-mode: luminosity;
}
.surv-modal-header-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(45,20,32,0.85) 0%, rgba(45,20,32,0.2) 100%);
}
.surv-modal-header-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: flex-end;
    gap: 20px;
    width: 100%;
}
.surv-modal-avatar {
    width: 80px; height: 80px;
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,0.5);
    background: rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
    overflow: hidden;
}
.surv-modal-avatar img { width:100%; height:100%; object-fit:cover; object-position:top; }
.surv-modal-identity { flex: 1; }
.surv-modal-identity h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.7rem;
    color: white;
    margin: 0 0 6px;
    line-height: 1.2;
}
.surv-modal-pills {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.surv-modal-pill {
    background: rgba(255,255,255,0.18);
    color: #FFD6E5;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 1px;
    padding: 4px 12px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,0.2);
    text-transform: uppercase;
}
.surv-modal-close {
    position: absolute;
    top: 16px; right: 16px;
    z-index: 10;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    color: white;
    width: 40px; height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1rem;
    transition: background 0.2s;
    backdrop-filter: blur(4px);
}
.surv-modal-close:hover { background: rgba(200,55,95,0.5); }

/* Corps modal */
.surv-modal-body {
    padding: 36px;
    max-height: 60vh;
    overflow-y: auto;
}
.surv-modal-body::-webkit-scrollbar { width: 5px; }
.surv-modal-body::-webkit-scrollbar-track { background: var(--surv-rose-pale); }
.surv-modal-body::-webkit-scrollbar-thumb { background: var(--surv-rose-mid); border-radius: 3px; }

.surv-modal-story {
    font-size: 1rem;
    color: #3D2030;
    line-height: 2;
    margin-bottom: 28px;
}
.surv-modal-story p { margin-bottom: 18px; }

/* Message d'espoir en modal */
.surv-modal-hope {
    background: linear-gradient(135deg, var(--surv-rose-pale), #FFF0F8);
    border-radius: 16px;
    padding: 24px 28px;
    border-left: 4px solid var(--surv-rose);
    position: relative;
    overflow: hidden;
}
.surv-modal-hope::before {
    content: '\201C';
    position: absolute;
    top: -10px; left: 14px;
    font-size: 5rem;
    color: var(--surv-rose);
    opacity: 0.12;
    font-family: 'Playfair Display', serif;
    line-height: 1;
}
.surv-modal-hope-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--surv-rose);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.surv-modal-hope-label::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--surv-rose-mid);
}
.surv-modal-hope p {
    font-family: 'Playfair Display', serif;
    font-style: italic;
    font-size: 1.15rem;
    color: #6B1D3A;
    line-height: 1.7;
    margin: 0;
}

/* ── SECTION CTA TÉMOIGNAGE ──────────────────────────────── */
.surv-cta-section {
    background: linear-gradient(135deg, var(--surv-rose-dark) 0%, var(--surv-rose) 60%, #E8849F 100%);
    padding: 80px 0;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.surv-cta-section::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse 70% 70% at 50% 0%, rgba(255,255,255,0.08), transparent);
}
.surv-cta-section h2 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.7rem, 3vw, 2.5rem);
    color: white;
    margin-bottom: 14px;
    position: relative;
}
.surv-cta-section p {
    color: rgba(255,220,235,0.88);
    font-size: 1.05rem;
    max-width: 560px;
    margin: 0 auto 32px;
    position: relative;
}
.surv-cta-btns {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
    position: relative;
}
.surv-btn-white {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    background: white;
    color: var(--surv-rose);
    font-weight: 700;
    font-size: 0.95rem;
    padding: 14px 32px;
    border-radius: 999px;
    text-decoration: none;
    transition: all 0.25s;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}
.surv-btn-white:hover { background: var(--surv-rose-pale); color: var(--surv-rose-dark); transform: translateY(-3px); }
.surv-btn-outline-white {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    background: rgba(255,255,255,0.12);
    color: white;
    font-weight: 700;
    font-size: 0.95rem;
    padding: 14px 32px;
    border-radius: 999px;
    text-decoration: none;
    border: 2px solid rgba(255,255,255,0.35);
    transition: all 0.25s;
    backdrop-filter: blur(4px);
}
.surv-btn-outline-white:hover { background: rgba(255,255,255,0.22); color: white; transform: translateY(-3px); }

/* ── RESPONSIVE ──────────────────────────────────────────── */
@media (max-width: 1024px) {
    .surv-grid { grid-template-columns: repeat(2, 1fr); gap: 24px; }
}
@media (max-width: 640px) {
    .surv-grid { grid-template-columns: 1fr; gap: 20px; }
    .surv-hero-stats { gap: 16px; }
    .surv-hstat { padding: 12px 16px; min-width: 80px; }
    .surv-hstat-num { font-size: 1.5rem; }
    .surv-modal-body { padding: 22px; }
    .surv-modal-header { height: 220px; }
    .surv-modal-identity h3 { font-size: 1.3rem; }
    .surv-hero { min-height: 420px; padding: 80px 0 70px; }
}
</style>

<!-- ══════════════════════════════════════════════════════════
     HERO
     ══════════════════════════════════════════════════════════ -->
<section class="surv-hero">
    <div class="surv-hero-orbs" aria-hidden="true">
        <span></span><span></span><span></span>
    </div>
    <div class="container">
        <div class="surv-hero-content" data-aos="fade-up">
            <div class="surv-hero-tag">
                <i class="fas fa-heart-pulse"></i>
                Témoignages de vie
            </div>
            <h1>
                Ils ont vaincu le cancer.
                <em>Ils témoignent pour vous.</em>
            </h1>
            <p class="surv-hero-sub">
                Ces femmes et ces hommes ont traversé l'épreuve du cancer en Haïti.
                Aujourd'hui, ils partagent leur parcours pour allumer l'espoir dans le cœur de ceux qui se battent encore.
            </p>
            <div class="surv-hero-stats">
                <div class="surv-hstat">
                    <span class="surv-hstat-num"><?= $total ?></span>
                    <span class="surv-hstat-label">Survivants</span>
                </div>
                <div class="surv-hstat">
                    <span class="surv-hstat-num"><?= $max_annees ?>+</span>
                    <span class="surv-hstat-label">Ans record</span>
                </div>
                <div class="surv-hstat">
                    <span class="surv-hstat-num"><?= $avg_annees ?></span>
                    <span class="surv-hstat-label">Ans en moy.</span>
                </div>
            </div>
        </div>
    </div>
    <div class="surv-hero-ribbon" aria-hidden="true"></div>
</section>

<!-- ══════════════════════════════════════════════════════════
     INTRO
     ══════════════════════════════════════════════════════════ -->
<section class="surv-intro">
    <div class="container" data-aos="fade-up">
        <h2>Chaque histoire est <span>un acte de courage</span></h2>
        <p>
            Ces survivants ont accepté de partager leur vécu dans l'espoir que leurs mots
            atteignent quelqu'un qui en a besoin. Lisez, ressentez, et si vous vivez
            l'épreuve du cancer, sachez que vous n'êtes pas seul(e).
        </p>
        <div class="surv-divider">
            <span class="surv-divider-line"></span>
            <i class="fas fa-heart surv-divider-heart"></i>
            <span class="surv-divider-line"></span>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     GRILLE DES SURVIVANTS
     ══════════════════════════════════════════════════════════ -->
<section class="surv-section">
    <div class="container">
        <?php if (empty($survivants)): ?>
            <div style="text-align:center;padding:60px 20px;color:#7A5060;">
                <i class="fas fa-heart" style="font-size:3rem;opacity:.3;margin-bottom:16px;display:block;"></i>
                <p>Aucun témoignage publié pour le moment.</p>
            </div>
        <?php else: ?>
        <div class="surv-grid">
            <?php foreach ($survivants as $i => $s):
                // Initiales pour l'avatar
                $initiales = strtoupper(substr($s['prenom'],0,1) . substr($s['nom'],0,1));
                $has_photo = !empty($s['photo']);
                $photo_url = $has_photo ? rtrim(SITE_URL,'/').'/images/'.$s['photo'] : null;
                $has_long  = !empty(trim($s['histoire_longue'] ?? ''));
            ?>
            <div class="surv-card" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 100 ?>">

                <!-- Photo / Fond gradient -->
                <div class="surv-card-photo">
                    <!-- Fallback initiales toujours visible en dessous -->
                    <div class="surv-avatar-fallback"><?= $initiales ?></div>
                    <?php if ($photo_url): ?>
                        <!-- Photo par-dessus le fallback en position absolute -->
                        <img src="<?= e($photo_url) ?>" alt="Photo de <?= e($s['prenom'].' '.$s['nom']) ?>"
                             onerror="this.style.display='none';">
                    <?php endif; ?>

                    <!-- Badge années -->
                    <div class="surv-years-badge">
                        <span class="num"><?= $s['annees_survie'] ?></span>
                        <span class="unit">ans<br>survie</span>
                    </div>

                    <!-- Type de cancer -->
                    <div class="surv-cancer-strip">
                        <span class="surv-cancer-type">
                            <i class="fas fa-ribbon"></i> <?= e($s['cancer_type']) ?>
                        </span>
                    </div>
                </div>

                <!-- Corps -->
                <div class="surv-card-body">
                    <div class="surv-card-name"><?= e($s['prenom'].' '.$s['nom']) ?></div>
                    <div class="surv-card-meta">
                        <?php if ($s['age_diagnostic']): ?>
                            <i class="fas fa-user"></i>
                            Diagnostiqué(e) à <?= (int)$s['age_diagnostic'] ?> ans
                            <span class="surv-card-meta-sep">·</span>
                        <?php endif; ?>
                        <i class="fas fa-calendar-check"></i>
                        Depuis <?= (int)$s['annees_survie'] ?> ans
                    </div>

                    <p class="surv-card-excerpt"><?= e($s['histoire_courte']) ?></p>

                    <?php if (!empty($s['message_espoir'])): ?>
                    <div class="surv-card-quote">
                        <?= e($s['message_espoir']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Footer carte -->
                <div class="surv-card-footer">
                    <div class="surv-card-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?= e($s['ville']) ?>
                    </div>
                    <?php if ($has_long): ?>
                    <a href="#" class="surv-btn-read"
                       onclick="openModal(<?= $s['id'] ?>);return false;">
                        Lire l'histoire <i class="fas fa-arrow-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     MODALS — une par survivant
     ══════════════════════════════════════════════════════════ -->
<?php foreach ($survivants as $s):
    if (empty(trim($s['histoire_longue'] ?? ''))) continue;
    $initiales = strtoupper(substr($s['prenom'],0,1) . substr($s['nom'],0,1));
    $has_photo = !empty($s['photo']);
    $photo_url = $has_photo ? rtrim(SITE_URL,'/').'/images/'.$s['photo'] : null;
    // Convertir les sauts de ligne en paragraphes
    $paragraphes = array_filter(explode("\n", trim($s['histoire_longue'])));
?>
<div class="surv-modal-overlay" id="modal-<?= $s['id'] ?>"
     onclick="if(event.target===this)closeModal(<?= $s['id'] ?>)">
    <div class="surv-modal" role="dialog" aria-modal="true"
         aria-label="Histoire de <?= e($s['prenom'].' '.$s['nom']) ?>">

        <!-- En-tête modal avec photo -->
        <div class="surv-modal-header">
            <div class="surv-modal-header-bg">
                <?php if ($photo_url): ?>
                    <img src="<?= e($photo_url) ?>" alt="" aria-hidden="true">
                <?php endif; ?>
            </div>
            <div class="surv-modal-header-overlay"></div>

            <button class="surv-modal-close" onclick="closeModal(<?= $s['id'] ?>)" aria-label="Fermer">
                <i class="fas fa-times"></i>
            </button>

            <div class="surv-modal-header-content">
                <div class="surv-modal-avatar">
                    <?php if ($photo_url): ?>
                        <img src="<?= e($photo_url) ?>" alt="<?= e($s['prenom']) ?>">
                    <?php else: ?>
                        <?= $initiales ?>
                    <?php endif; ?>
                </div>
                <div class="surv-modal-identity">
                    <h3><?= e($s['prenom'].' '.$s['nom']) ?></h3>
                    <div class="surv-modal-pills">
                        <span class="surv-modal-pill">
                            <i class="fas fa-ribbon"></i> <?= e($s['cancer_type']) ?>
                        </span>
                        <span class="surv-modal-pill">
                            <?= (int)$s['annees_survie'] ?> ans de survie
                        </span>
                        <span class="surv-modal-pill">
                            <i class="fas fa-map-marker-alt"></i> <?= e($s['ville']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Corps -->
        <div class="surv-modal-body">
            <div class="surv-modal-story">
                <?php foreach ($paragraphes as $p): ?>
                    <p><?= e(trim($p)) ?></p>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($s['message_espoir'])): ?>
            <div class="surv-modal-hope">
                <div class="surv-modal-hope-label">
                    <i class="fas fa-heart"></i> Message d'espoir
                </div>
                <p><?= e($s['message_espoir']) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- ══════════════════════════════════════════════════════════
     CTA FINAL
     ══════════════════════════════════════════════════════════ -->
<section class="surv-cta-section">
    <div class="container" data-aos="fade-up">
        <h2>Vous aussi, partagez votre histoire</h2>
        <p>
            Votre témoignage peut transformer la vie de quelqu'un qui lutte en ce moment.
            Contactez-nous — nous vous accompagnons.
        </p>
        <div class="surv-cta-btns">
            <a href="contact.php" class="surv-btn-white">
                <i class="fas fa-pen-nib"></i> Partager mon témoignage
            </a>
            <a href="demande-aide.php" class="surv-btn-outline-white">
                <i class="fas fa-hands-holding-heart"></i> Demander de l'aide
            </a>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     JS MODAL
     ══════════════════════════════════════════════════════════ -->
<script>
function openModal(id) {
    var overlay = document.getElementById('modal-' + id);
    if (!overlay) return;
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
    // Focus trap basique
    setTimeout(function() {
        var closeBtn = overlay.querySelector('.surv-modal-close');
        if (closeBtn) closeBtn.focus();
    }, 100);
}
function closeModal(id) {
    var overlay = document.getElementById('modal-' + id);
    if (!overlay) return;
    overlay.classList.remove('open');
    document.body.style.overflow = '';
}
// Fermer avec Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.surv-modal-overlay.open').forEach(function(el) {
            el.classList.remove('open');
            document.body.style.overflow = '';
        });
    }
});
</script>

<?php require_once 'templates/footer.php'; ?>