<?php
// devenir-membre.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$page_title       = 'Devenir Membre';
$page_description = 'Devenez membre du GSCC et contribuez activement à la lutte contre le cancer en Haïti par vos dons et votre engagement.';

// ══════════════════════════════════════════════════════════
//  CONFIGURATION — REMPLACE PAR TON VRAI LIEN DE FORMULAIRE
// ══════════════════════════════════════════════════════════
$lien_formulaire = 'https://forms.google.com/TON-LIEN-ICI'; // ← mets ton lien ici
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
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<style>
/* ==========================================================
   GSCC · Devenir Membre — Page reformulée
   Bleu nuit · Rose grenat · Or · Crème ivoire
========================================================== */
:root {
    --navy:      #0D1B35;
    --navy-2:    #162544;
    --navy-3:    #1E3461;
    --rose:      #AC2F58;
    --rose-lite: #F5E6EC;
    --rose-mid:  #C94070;
    --gold:      #C4933F;
    --gold-lite: #FBF3E3;
    --cream:     #FAF8F5;
    --white:     #FFFFFF;
    --sage:      #2E7D6B;
    --text:      #141425;
    --text-2:    #3A3A5C;
    --text-3:    #7A7A9A;
    --border:    #E6E2F5;
    --r:         18px;
    --r2:        12px;
    --sh1:       0 2px 16px rgba(13,27,53,.07);
    --sh2:       0 10px 40px rgba(13,27,53,.12);
    --ease:      cubic-bezier(.4,0,.2,1);
}
*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
html { scroll-behavior:smooth; }
body { font-family:'Outfit',sans-serif; background:var(--cream); color:var(--text); line-height:1.6; }
.container { max-width:1180px; margin:0 auto; padding:0 28px; }



/* ── HERO ──────────────────────────────────────────────── */
.hero {
    position:relative; background:var(--navy);
    padding:120px 0 100px; overflow:hidden; text-align:center;
}
/* Supprime toute grille/carreau venant du style.css global */
.hero::before,
.hero::after { display:none !important; content:none !important; background-image:none !important; }
.cta-section::before,
.cta-section::after { display:none !important; content:none !important; background-image:none !important; }

.hero-glow-rose {
    position:absolute; width:700px; height:700px; border-radius:50%;
    background:radial-gradient(circle, rgba(172,47,88,.22) 0%, transparent 70%);
    top:-200px; left:-150px; pointer-events:none;
}
.hero-glow-gold {
    position:absolute; width:500px; height:500px; border-radius:50%;
    background:radial-gradient(circle, rgba(196,147,63,.16) 0%, transparent 70%);
    bottom:-100px; right:-80px; pointer-events:none;
}
.hero-line {
    position:absolute; bottom:0; left:0; right:0; height:3px;
    background:linear-gradient(90deg, transparent, var(--gold), var(--rose), var(--gold), transparent);
}
.hero .container { position:relative; z-index:1; }

.hero-tag {
    display:inline-flex; align-items:center; gap:8px;
    background:rgba(196,147,63,.15); border:1px solid rgba(196,147,63,.35);
    color:var(--gold); font-size:11px; font-weight:600;
    letter-spacing:2.5px; text-transform:uppercase;
    padding:7px 20px; border-radius:99px; margin-bottom:28px;
}
.hero h1 {
    font-family:'Cormorant Garamond', serif;
    font-size:clamp(3rem,6vw,5rem); font-weight:700;
    color:var(--white); line-height:1.1; margin-bottom:20px;
}
.hero h1 em {
    font-style:italic; color:transparent;
    background:linear-gradient(90deg, var(--gold), #E8B86D);
    -webkit-background-clip:text; background-clip:text;
}
.hero-sub {
    font-size:1.08rem; font-weight:400;
    color:rgba(255,255,255,.92);
    max-width:580px; margin:0 auto 44px; line-height:1.85;
}

/* Bouton CTA hero */
.btn-hero {
    display:inline-flex; align-items:center; gap:12px;
    padding:18px 44px;
    background:linear-gradient(135deg, var(--rose), var(--rose-mid));
    color:var(--white); font-size:16px; font-weight:700;
    border-radius:99px; text-decoration:none;
    box-shadow:0 8px 32px rgba(172,47,88,.40);
    transition:all .3s var(--ease);
    letter-spacing:.3px;
}
.btn-hero:hover {
    transform:translateY(-3px);
    box-shadow:0 16px 48px rgba(172,47,88,.55);
    color:var(--white);
}
.btn-hero:active { transform:translateY(-1px); }
.btn-hero i { font-size:18px; }

/* Stats rapides sous le CTA */
.hero-stats {
    display:flex; justify-content:center; gap:48px;
    margin-top:52px;
    padding-top:44px;
    border-top:1px solid rgba(255,255,255,.10);
}
.hstat { text-align:center; }
.hstat-num {
    font-family:'Cormorant Garamond', serif;
    font-size:2.4rem; font-weight:700;
    color:var(--gold); line-height:1;
}
.hstat-lbl {
    font-size:12px; font-weight:500;
    color:rgba(255,255,255,.75);
    margin-top:5px; letter-spacing:.5px;
}

/* ── CE QU'EST UN MEMBRE ────────────────────────────────── */
.definition-section {
    padding:90px 0 70px;
    background:var(--white);
}
.section-tag {
    display:inline-flex; align-items:center; gap:7px;
    background:var(--rose-lite); color:var(--rose);
    font-size:11px; font-weight:600; letter-spacing:2px; text-transform:uppercase;
    padding:6px 16px; border-radius:99px; margin-bottom:18px;
}
.section-title {
    font-family:'Cormorant Garamond', serif;
    font-size:clamp(2rem,4vw,3rem); font-weight:700;
    color:var(--navy); line-height:1.15; margin-bottom:18px;
}
.section-title em {
    font-style:italic; color:var(--rose);
}
.section-rule {
    width:60px; height:3px; border-radius:99px;
    background:linear-gradient(90deg, var(--rose), var(--gold));
    margin:0 0 28px;
}
.section-text {
    font-size:1.02rem; color:var(--text);
    line-height:1.85; max-width:640px;
}

.def-grid {
    display:grid; grid-template-columns:1fr 1fr;
    gap:60px; align-items:center;
}
.def-visual {
    display:grid; grid-template-columns:1fr 1fr;
    gap:16px;
}
.def-card {
    background:var(--cream); border:1px solid var(--border);
    border-radius:var(--r); padding:28px 24px;
    transition:all .3s var(--ease);
}
.def-card:hover {
    background:var(--white);
    box-shadow:var(--sh2);
    transform:translateY(-4px);
}
.def-card-ico {
    width:52px; height:52px; border-radius:14px;
    background:linear-gradient(135deg, var(--rose), var(--rose-mid));
    display:flex; align-items:center; justify-content:center;
    font-size:20px; color:#fff !important; margin-bottom:16px;
    flex-shrink:0;
}
.def-card-ico i,
.def-card-ico .fas { color:#fff !important; font-size:20px !important; }
.def-card:nth-child(2) .def-card-ico { background:linear-gradient(135deg, var(--gold), #E8B86D); }
.def-card:nth-child(3) .def-card-ico { background:linear-gradient(135deg, var(--navy-3), var(--navy-2)); }
.def-card:nth-child(4) .def-card-ico { background:linear-gradient(135deg, var(--sage), #3DA085); }
.def-card h4 { font-size:15px; font-weight:700; color:var(--navy); margin-bottom:7px; }
.def-card p  { font-size:13.5px; color:var(--text); line-height:1.6; }

/* ── TYPES DE MEMBRES ───────────────────────────────────── */
.types-section {
    padding:90px 0;
    background:var(--cream);
}
.types-section .section-rule { margin:0 auto 48px; }
.types-section .section-tag,
.types-section .section-title { text-align:center; }
.types-section .section-title { margin:0 auto 8px; }

.types-grid {
    display:grid; grid-template-columns:repeat(3,1fr);
    gap:24px; margin-top:48px;
}
.type-card {
    background:var(--white); border:1px solid var(--border);
    border-radius:var(--r); padding:40px 32px;
    text-align:center; position:relative; overflow:hidden;
    transition:all .35s var(--ease);
}
.type-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:4px;
    background:linear-gradient(90deg, var(--rose), var(--gold));
    opacity:0; transition:opacity .3s;
}
.type-card:hover {
    transform:translateY(-8px);
    box-shadow:var(--sh2);
}
.type-card:hover::before { opacity:1; }
.type-card.featured {
    background:var(--navy); border-color:var(--navy);
}
.type-card.featured::before { opacity:1; }
.type-card.featured h3,
.type-card.featured p { color:#FFFFFF; }
.type-card.featured p { color:#FFFFFF; }
.type-card.featured .type-tag { background:rgba(196,147,63,.2); color:var(--gold); }
.type-card.featured .type-ico { background:rgba(255,255,255,.1); color:var(--gold); }
.type-card.featured .type-detail { color:rgba(255,255,255,.65); }

.type-ico {
    width:72px; height:72px; border-radius:20px;
    background:var(--rose-lite);
    display:flex; align-items:center; justify-content:center;
    font-size:28px; color:var(--rose) !important;
    margin:0 auto 20px;
    transition:transform .3s var(--ease);
}
.type-ico i, .type-ico .fas { color:var(--rose) !important; font-size:28px !important; }
.type-card.featured .type-ico { background:rgba(255,255,255,.1); }
.type-card.featured .type-ico i,
.type-card.featured .type-ico .fas { color:var(--gold) !important; }
.type-card:hover .type-ico { transform:scale(1.1); }

.type-tag {
    display:inline-block;
    background:var(--rose-lite); color:var(--rose);
    font-size:10px; font-weight:700; letter-spacing:2px;
    text-transform:uppercase; padding:4px 12px;
    border-radius:99px; margin-bottom:14px;
}
.type-card h3 {
    font-family:'Cormorant Garamond', serif;
    font-size:1.55rem; font-weight:700;
    color:var(--navy); margin-bottom:12px;
}
.type-card p {
    font-size:14px; color:var(--text);
    line-height:1.7; margin-bottom:20px;
}
.type-details { list-style:none; text-align:left; }
.type-details li {
    display:flex; align-items:flex-start; gap:10px;
    font-size:13.5px; color:var(--text-2); padding:7px 0;
    border-bottom:1px solid var(--border);
}
.type-card.featured .type-details li {
    border-color:rgba(255,255,255,.15);
    color:#FFFFFF;
}
.type-details li:last-child { border-bottom:none; }
.type-details li i { color:var(--rose); margin-top:3px; font-size:11px; flex-shrink:0; }
.type-card.featured .type-details li i { color:var(--gold); }
.type-detail { font-size:11px; color:var(--text-2); margin-top:2px; } /* was text-3 (#7A7A9A) → text-2 (#3A3A5C) */
.type-card.featured .type-detail { color:rgba(255,255,255,.82) !important; } /* was .65 → .82 */

/* ── AVANTAGES ──────────────────────────────────────────── */
.benefits-section {
    padding:90px 0;
    background:var(--white);
}
.benefits-grid {
    display:grid; grid-template-columns:repeat(3,1fr);
    gap:24px; margin-top:48px;
}
.benefit-card {
    padding:32px 28px;
    border:1px solid var(--border); border-radius:var(--r);
    background:var(--cream);
    transition:all .3s var(--ease);
    position:relative; overflow:hidden;
}
.benefit-card::after {
    content:''; position:absolute;
    bottom:0; left:0; right:0; height:3px;
    background:linear-gradient(90deg, var(--rose), var(--gold));
    transform:scaleX(0); transition:transform .3s var(--ease);
    transform-origin:left;
}
.benefit-card:hover { background:var(--white); box-shadow:var(--sh1); transform:translateY(-4px); }
.benefit-card:hover::after { transform:scaleX(1); }
.benefit-card-ico {
    width:56px; height:56px; border-radius:16px;
    background:linear-gradient(135deg, var(--rose), var(--rose-mid));
    display:flex; align-items:center; justify-content:center;
    font-size:22px; color:#fff !important; margin-bottom:20px;
    flex-shrink:0;
}
/* Forcer les icônes FontAwesome à rester blanches dans tous les cas */
.benefit-card-ico i,
.benefit-card-ico .fas,
.benefit-card-ico .far,
.benefit-card-ico .fab {
    color:#fff !important;
    font-size:22px !important;
}
.benefit-card h4 { font-size:16px; font-weight:700; color:var(--navy); margin-bottom:8px; }
.benefit-card p  { font-size:14px; color:var(--text); line-height:1.65; }

/* ── MEMBRES ACTUELS ────────────────────────────────────── */
.members-section {
    padding:90px 0;
    background:var(--cream);
}
.members-intro {
    display:grid; grid-template-columns:1fr 1fr;
    gap:60px; align-items:center; margin-bottom:60px;
}
.members-counter {
    background:var(--navy); border-radius:var(--r);
    padding:40px; text-align:center; position:relative; overflow:hidden;
}
.members-counter::before {
    content:''; position:absolute; top:0; left:0; right:0; height:3px;
    background:linear-gradient(90deg, var(--rose), var(--gold));
}
.members-counter-num {
    font-family:'Cormorant Garamond', serif;
    font-size:4.5rem; font-weight:700; color:var(--gold); line-height:1;
}
.members-counter-lbl {
    font-size:14px; color:rgba(255,255,255,.88); margin-top:8px; /* was .60 → .88 */
    font-weight:500;
}
.members-counter-sub {
    font-size:12px; color:rgba(255,255,255,.72); margin-top:12px; /* was .35 → .72 */
    padding-top:12px; border-top:1px solid rgba(255,255,255,.15);
}

/* Grille avatars membres */
.members-avatars {
    display:grid; grid-template-columns:repeat(4,1fr);
    gap:16px;
}
.member-avatar-card {
    background:var(--white); border:1px solid var(--border);
    border-radius:var(--r2); padding:20px 16px; text-align:center;
    transition:all .3s var(--ease);
}
.member-avatar-card:hover { box-shadow:var(--sh1); transform:translateY(-3px); }
.member-avatar {
    width:58px; height:58px; border-radius:50%;
    background:linear-gradient(135deg, var(--rose), var(--rose-mid));
    display:flex; align-items:center; justify-content:center;
    font-size:22px; color:var(--white);
    margin:0 auto 12px;
    font-family:'Cormorant Garamond', serif;
    font-weight:700; font-size:20px;
}
.member-avatar.gold   { background:linear-gradient(135deg, var(--gold), #E8B86D); }
.member-avatar.navy   { background:linear-gradient(135deg, var(--navy-3), var(--navy-2)); }
.member-avatar.sage   { background:linear-gradient(135deg, var(--sage), #3DA085); }
.member-name  { font-size:13px; font-weight:600; color:var(--navy); }
.member-type  { font-size:11px; color:var(--text-3); margin-top:3px; }
.member-since { font-size:10.5px; color:var(--rose); margin-top:4px; font-weight:500; }

/* ── CTA FINAL ──────────────────────────────────────────── */
.cta-section {
    padding:100px 0;
    /* Fond légèrement plus clair que le navy pur pour améliorer le contraste visuel */
    background: linear-gradient(160deg, #162544 0%, #1E3461 50%, #162544 100%) !important;
    text-align:center;
    position:relative; overflow:hidden;
}

.cta-glow {
    position:absolute; width:700px; height:700px; border-radius:50%;
    background:radial-gradient(circle, rgba(196,147,63,.18) 0%, transparent 70%);
    top:50%; left:50%; transform:translate(-50%,-50%); pointer-events:none;
}
.cta-section .container { position:relative; z-index:1; }

/* CORRIGÉ : blanc forcé avec !important pour écraser tout style global */
.cta-section h2 {
    font-family:'Cormorant Garamond', serif;
    font-size:clamp(2.2rem,4.5vw,3.5rem); font-weight:700;
    color:#FFFFFF !important;
    margin-bottom:18px; line-height:1.15;
}

/*
 * CORRIGÉ : bug "texte invisible"
 * color:transparent + background-clip:text peut rendre l'em entièrement
 * invisible si background-clip:text n'est pas supporté ou si un style
 * global réinitialise la couleur.
 * Solution : utiliser -webkit-text-fill-color:transparent (plus fiable)
 * et garder color:var(--gold) comme couleur de repli visible.
 */
.cta-section h2 em {
    font-style:italic;
    color: var(--gold);                            /* repli visible */
    background: linear-gradient(90deg, #E8B86D, #C4933F, #E8B86D);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;          /* plus fiable que color:transparent */
}

/* CORRIGÉ : !important pour écraser la règle globale p { color: var(--grey) } */
.cta-section p {
    font-size:1.05rem;
    color:#FFFFFF !important;
    max-width:560px; margin:0 auto 44px; line-height:1.85;
    opacity: 1 !important;
    text-shadow: 0 1px 3px rgba(0,0,0,.25); /* légère ombre pour améliorer lisibilité */
}

/* Bouton principal — fond rose + texte blanc forcé */
.btn-cta-main {
    display:inline-flex; align-items:center; gap:12px;
    padding:20px 52px;
    background:linear-gradient(135deg, var(--rose), var(--rose-mid));
    color:#FFFFFF !important; font-size:17px; font-weight:700;
    border-radius:99px; text-decoration:none;
    box-shadow:0 8px 36px rgba(172,47,88,.50);
    transition:all .3s var(--ease); letter-spacing:.3px;
    border: 2px solid rgba(255,255,255,.15);
}
.btn-cta-main:hover {
    transform:translateY(-4px);
    box-shadow:0 18px 52px rgba(172,47,88,.65);
    color:#FFFFFF !important;
    border-color: rgba(255,255,255,.30);
}
.btn-cta-main i { font-size:20px; color:#FFFFFF !important; }

/* Note de confidentialité — blanc bien visible avec icône or */
.cta-note {
    margin-top:24px; font-size:13.5px;
    color:rgba(255,255,255,.95) !important; /* quasi-blanc, très lisible */
    font-weight:500;
    display:inline-flex; align-items:center; gap:8px;
    justify-content:center;
    background:rgba(255,255,255,.08);
    padding:10px 20px; border-radius:99px;
    border:1px solid rgba(255,255,255,.12);
}
.cta-note i { color:var(--gold) !important; margin-right:0; font-size:14px; }

/* ── RESPONSIVE ─────────────────────────────────────────── */
@media(max-width:1024px) {
    .types-grid { grid-template-columns:1fr 1fr; }
    .benefits-grid { grid-template-columns:1fr 1fr; }
    .members-intro { grid-template-columns:1fr; gap:32px; }
    .members-avatars { grid-template-columns:repeat(4,1fr); }
}
@media(max-width:768px) {
    .def-grid { grid-template-columns:1fr; gap:40px; }
    .def-visual { grid-template-columns:1fr 1fr; }
    .types-grid { grid-template-columns:1fr; }
    .benefits-grid { grid-template-columns:1fr; }
    .members-avatars { grid-template-columns:repeat(2,1fr); }
    .hero-stats { gap:28px; flex-wrap:wrap; }
}
@media(max-width:480px) {
    .def-visual { grid-template-columns:1fr; }
    .hero-stats { gap:20px; }
    .hstat-num { font-size:2rem; }
    .members-avatars { grid-template-columns:1fr 1fr; }
}
</style>
</head>
<body>
<?php require_once 'templates/header.php'; ?>

<!-- ══════════════════════════════════════════════════════
     HERO
     ══════════════════════════════════════════════════════ -->
<header class="hero">
    <div class="hero-glow-rose"></div>
    <div class="hero-glow-gold"></div>
    <div class="hero-line"></div>
    <div class="container">

        <div class="hero-tag" data-aos="fade-down">
            <i class="fas fa-ribbon"></i>
            Membres GSCC
        </div>

        <h1 data-aos="fade-up" data-aos-delay="80">
            Contribuez à <em>notre mission</em><br>contre le cancer
        </h1>

        <p class="hero-sub" data-aos="fade-up" data-aos-delay="160">
            Un membre GSCC, c'est une personne qui s'engage concrètement —
            par ses dons réguliers ou son implication active — pour que chaque
            Haïtien touché par le cancer reçoive le soutien dont il a besoin.
        </p>

        <div data-aos="fade-up" data-aos-delay="240">
            <a href="<?= htmlspecialchars($lien_formulaire) ?>" target="_blank" rel="noopener" class="btn-hero">
                <i class="fas fa-file-signature"></i>
                Remplir le formulaire d'adhésion
            </a>
        </div>

        <div class="hero-stats" data-aos="fade-up" data-aos-delay="320">
            <div class="hstat">
                <div class="hstat-num">120+</div>
                <div class="hstat-lbl">Membres actifs</div>
            </div>
            <div class="hstat">
                <div class="hstat-num">5</div>
                <div class="hstat-lbl">Années d'impact</div>
            </div>
            <div class="hstat">
                <div class="hstat-num">3</div>
                <div class="hstat-lbl">Types d'adhésion</div>
            </div>
            <div class="hstat">
                <div class="hstat-num">100%</div>
                <div class="hstat-lbl">Dons réinvestis</div>
            </div>
        </div>

    </div>
</header>

<!-- ══════════════════════════════════════════════════════
     QU'EST-CE QU'UN MEMBRE GSCC ?
     ══════════════════════════════════════════════════════ -->
<section class="definition-section">
    <div class="container">
        <div class="def-grid">

            <div data-aos="fade-right">
                <div class="section-tag">
                    <i class="fas fa-users"></i> Définition
                </div>
                <h2 class="section-title">
                    Qui peut devenir<br><em>membre</em> du GSCC ?
                </h2>
                <div class="section-rule"></div>
                <p class="section-text">
                    Être membre du GSCC, c'est bien plus qu'une inscription.
                    C'est un <strong>engagement concret et régulier</strong> envers notre mission.
                </p>
                <p class="section-text" style="margin-top:16px;">
                    Nos membres sont des personnes qui contribuent activement à nos programmes —
                    que ce soit par des <strong>dons financiers réguliers</strong>, par leur
                    <strong>temps et compétences</strong>, ou par leur <strong>rayonnement</strong>
                    autour de la cause.
                </p>
                <p class="section-text" style="margin-top:16px;">
                    Ce sont eux qui rendent possibles nos journées de dépistage gratuit,
                    nos programmes d'accompagnement psychologique et notre présence
                    dans les communautés haïtiennes.
                </p>
            </div>

            <div class="def-visual" data-aos="fade-left" data-aos-delay="100">
                <div class="def-card">
                    <div class="def-card-ico"><i class="fas fa-hand-holding-dollar"></i></div>
                    <h4>Dons réguliers</h4>
                    <p>Contribuez financièrement chaque mois pour soutenir nos programmes de soins et de sensibilisation.</p>
                </div>
                <div class="def-card">
                    <div class="def-card-ico"><i class="fas fa-people-group"></i></div>
                    <h4>Participation active</h4>
                    <p>Prenez part à nos événements, marches, formations et journées de dépistage sur le terrain.</p>
                </div>
                <div class="def-card">
                    <div class="def-card-ico"><i class="fas fa-bullhorn"></i></div>
                    <h4>Sensibilisation</h4>
                    <p>Diffusez le message autour de vous et contribuez à briser les tabous sur le cancer en Haïti.</p>
                </div>
                <div class="def-card">
                    <div class="def-card-ico"><i class="fas fa-briefcase-medical"></i></div>
                    <h4>Expertise</h4>
                    <p>Mettez vos compétences professionnelles — médicales, juridiques, techniques — au service de notre cause.</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     TYPES DE MEMBRES
     ══════════════════════════════════════════════════════ -->
<section class="types-section">
    <div class="container">
        <div style="text-align:center" data-aos="fade-up">
            <div class="section-tag" style="display:inline-flex;">
                <i class="fas fa-layer-group"></i> Types d'adhésion
            </div>
            <h2 class="section-title">Trois façons de<br><em>s'engager</em></h2>
            <div class="section-rule" style="margin:0 auto 0;"></div>
        </div>

        <div class="types-grid">

            <!-- Membre bienfaiteur -->
            <div class="type-card" data-aos="fade-up" data-aos-delay="0">
                <div class="type-ico"><i class="fas fa-hand-holding-heart"></i></div>
                <div class="type-tag">Bienfaiteur</div>
                <h3>Membre Bienfaiteur</h3>
                <p>Vous soutenez financièrement et régulièrement les programmes du GSCC. Vos dons alimentent directement nos actions sur le terrain.</p>
                <ul class="type-details">
                    <li><i class="fas fa-check-circle"></i> Dons mensuels ou annuels</li>
                    <li><i class="fas fa-check-circle"></i> Reçu fiscal pour vos contributions</li>
                    <li><i class="fas fa-check-circle"></i> Rapport d'impact trimestriel</li>
                    <li><i class="fas fa-check-circle"></i> Invitation aux événements VIP</li>
                </ul>
            </div>

            <!-- Membre actif — mis en avant -->
            <div class="type-card featured" data-aos="fade-up" data-aos-delay="100">
                <div class="type-ico"><i class="fas fa-user-check"></i></div>
                <div class="type-tag">Le plus courant</div>
                <h3>Membre Actif</h3>
                <p>Vous participez aux activités, événements et missions du GSCC. Présent sur le terrain, vous êtes l'incarnation de notre mission.</p>
                <ul class="type-details">
                    <li><i class="fas fa-check-circle"></i> Participation aux journées de dépistage</li>
                    <li><i class="fas fa-check-circle"></i> Accès aux formations internes</li>
                    <li><i class="fas fa-check-circle"></i> Vote aux assemblées générales</li>
                    <li><i class="fas fa-check-circle"></i> Newsletter et informations exclusives</li>
                </ul>
            </div>

            <!-- Membre honoraire -->
            <div class="type-card" data-aos="fade-up" data-aos-delay="200">
                <div class="type-ico"><i class="fas fa-award"></i></div>
                <div class="type-tag">Honoraire</div>
                <h3>Membre Honoraire</h3>
                <p>Personnalité, expert ou institution qui, par son rayonnement et son prestige, soutient et porte la cause du GSCC dans la société.</p>
                <ul class="type-details">
                    <li><i class="fas fa-check-circle"></i> Attribution sur dossier ou invitation</li>
                    <li><i class="fas fa-check-circle"></i> Ambassadeur de la cause</li>
                    <li><i class="fas fa-check-circle"></i> Présence aux conseils d'orientation</li>
                    <li><i class="fas fa-check-circle"></i> Visibilité dans nos communications</li>
                </ul>
            </div>

        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     AVANTAGES
     ══════════════════════════════════════════════════════ -->
<section class="benefits-section">
    <div class="container">
        <div data-aos="fade-up">
            <div class="section-tag">
                <i class="fas fa-star"></i> Avantages
            </div>
            <h2 class="section-title">Ce que vous gagnez<br>en <em>rejoignant</em> le GSCC</h2>
            <div class="section-rule"></div>
        </div>

        <div class="benefits-grid">

            <div class="benefit-card" data-aos="fade-up" data-aos-delay="0">
                <div class="benefit-card-ico"><i class="fas fa-users"></i></div>
                <h4>Une communauté soudée</h4>
                <p>Rejoignez un réseau de personnes engagées, partageant les mêmes valeurs de solidarité et d'humanité face au cancer.</p>
            </div>

            <div class="benefit-card" data-aos="fade-up" data-aos-delay="80">
                <div class="benefit-card-ico"><i class="fas fa-calendar-check"></i></div>
                <h4>Événements en avant-première</h4>
                <p>Accédez en priorité à nos conférences médicales, formations, marches et journées de dépistage gratuit.</p>
            </div>

            <div class="benefit-card" data-aos="fade-up" data-aos-delay="160">
                <div class="benefit-card-ico"><i class="fas fa-newspaper"></i></div>
                <h4>Newsletter mensuelle</h4>
                <p>Recevez chaque mois nos actualités, avancements de programmes et informations médicales fiables sur le cancer.</p>
            </div>

            <div class="benefit-card" data-aos="fade-up" data-aos-delay="0">
                <div class="benefit-card-ico" style="background:linear-gradient(135deg,var(--gold),#E8B86D)"><i class="fas fa-chart-line"></i></div>
                <h4>Impact mesurable</h4>
                <p>Suivez concrètement l'utilisation de vos contributions à travers nos rapports transparents d'activités et d'impact.</p>
            </div>

            <div class="benefit-card" data-aos="fade-up" data-aos-delay="80">
                <div class="benefit-card-ico" style="background:linear-gradient(135deg,var(--navy-3),var(--navy-2))"><i class="fas fa-comments"></i></div>
                <h4>Forum réservé aux membres</h4>
                <p>Échangez librement avec les autres membres, partagez vos expériences et accédez aux ressources documentaires exclusives.</p>
            </div>

            <div class="benefit-card" data-aos="fade-up" data-aos-delay="160">
                <div class="benefit-card-ico" style="background:linear-gradient(135deg,var(--sage),#3DA085)"><i class="fas fa-hand-holding-heart"></i></div>
                <h4>Priorité de soutien</h4>
                <p>En cas de besoin personnel ou familial, les membres bénéficient d'un traitement prioritaire pour nos demandes d'accompagnement.</p>
            </div>

        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     MEMBRES ACTUELS
     ══════════════════════════════════════════════════════ -->
<section class="members-section">
    <div class="container">

        <div class="members-intro">
            <div data-aos="fade-right">
                <div class="section-tag">
                    <i class="fas fa-heart"></i> Notre communauté
                </div>
                <h2 class="section-title">Ils font partie<br>de la <em>famille GSCC</em></h2>
                <div class="section-rule"></div>
                <p class="section-text">
                    Chaque membre qui nous rejoint renforce notre capacité à agir.
                    Ensemble, nous formons une force de solidarité unique en Haïti
                    contre le cancer.
                </p>
                <p class="section-text" style="margin-top:14px;">
                    Donateurs réguliers, bénévoles sur le terrain, professionnels de santé,
                    entrepreneurs engagés — tous ont choisi de faire la différence.
                </p>
            </div>

            <div class="members-counter" data-aos="fade-left" data-aos-delay="100">
                <div class="members-counter-num">120+</div>
                <div class="members-counter-lbl">membres actifs en 2026</div>
                <div class="members-counter-sub">
                    <i class="fas fa-arrow-trend-up" style="color:var(--gold);margin-right:6px"></i>
                    +35 nouveaux membres depuis janvier 2026
                </div>
            </div>
        </div>

        <!-- Aperçu de quelques membres -->
        <div class="members-avatars" data-aos="fade-up">

            <div class="member-avatar-card">
                <div class="member-avatar">MJ</div>
                <div class="member-name">Marie Jean-Baptiste</div>
                <div class="member-type">Membre bienfaiteur</div>
                <div class="member-since">Depuis 2021</div>
            </div>

            <div class="member-avatar-card">
                <div class="member-avatar gold">PR</div>
                <div class="member-name">Pierre Richard</div>
                <div class="member-type">Membre actif</div>
                <div class="member-since">Depuis 2022</div>
            </div>

            <div class="member-avatar-card">
                <div class="member-avatar navy">RC</div>
                <div class="member-name">Rose-Merline Charles</div>
                <div class="member-type">Membre honoraire</div>
                <div class="member-since">Depuis 2020</div>
            </div>

            <div class="member-avatar-card">
                <div class="member-avatar sage">LA</div>
                <div class="member-name">Loucerie Aime</div>
                <div class="member-type">Membre actif</div>
                <div class="member-since">Depuis 2023</div>
            </div>

            <div class="member-avatar-card">
                <div class="member-avatar">GC</div>
                <div class="member-name">Guy Calixte</div>
                <div class="member-type">Membre bienfaiteur</div>
                <div class="member-since">Depuis 2022</div>
            </div>

            <div class="member-avatar-card">
                <div class="member-avatar gold">KL</div>
                <div class="member-name">Kerby Louis</div>
                <div class="member-type">Membre actif</div>
                <div class="member-since">Depuis 2024</div>
            </div>

            <div class="member-avatar-card">
                <div class="member-avatar navy">SP</div>
                <div class="member-name">Sophonie Pierre</div>
                <div class="member-type">Membre actif</div>
                <div class="member-since">Depuis 2023</div>
            </div>

            <!-- Carte "et bien d'autres" -->
            <div class="member-avatar-card" style="background:var(--navy);border-color:var(--navy);display:flex;flex-direction:column;align-items:center;justify-content:center;">
                <div class="member-avatar" style="background:rgba(255,255,255,.1);font-size:22px;">
                    <i class="fas fa-plus" style="color:var(--gold)"></i>
                </div>
                <div class="member-name" style="color:rgba(255,255,255,.95)">113 autres</div>
                <div class="member-type" style="color:rgba(255,255,255,.72)">membres engagés</div>
                <div class="member-since" style="color:var(--gold)">Rejoignez-les !</div>
            </div>

        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     CTA FINAL
     ══════════════════════════════════════════════════════ -->
<section class="cta-section">
    <div class="cta-glow"></div>
    <div class="container">

        <div data-aos="fade-up">
            <h2>Prêt à rejoindre<br><em>l'aventure GSCC</em> ?</h2>
            <p>
                Remplissez notre formulaire d'adhésion en ligne.
                Notre équipe prendra contact avec vous sous 48 heures
                pour finaliser votre inscription.
            </p>

            <a href="<?= htmlspecialchars($lien_formulaire) ?>" target="_blank" rel="noopener" class="btn-cta-main">
                <i class="fas fa-file-signature"></i>
                Remplir le formulaire d'adhésion
            </a>

            <p class="cta-note">
                <i class="fas fa-lock"></i>
                Vos informations sont confidentielles et ne seront jamais partagées
            </p>
        </div>

    </div>
</section>

<?php require_once 'templates/footer.php'; ?>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="assets/js/main.js"></script>
<script>
AOS.init({ duration: 680, once: true, offset: 50 });
</script>
</body>
</html>