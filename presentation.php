<?php
// presentation.php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title       = 'Présentation';
$page_description = 'Découvrez notre mission, notre vision, notre histoire et notre équipe dévouée.';

try {
    $stmt   = $pdo->query("SELECT * FROM equipe WHERE est_actif = 1 ORDER BY ordre ASC");
    $equipe = $stmt->fetchAll();

    $stmt    = $pdo->query("SELECT * FROM valeurs WHERE est_actif = 1 ORDER BY ordre ASC");
    $valeurs = $stmt->fetchAll();

    if (empty($valeurs)) {
        $valeurs = [
            ['titre' => 'Confiance',       'description' => 'Nous construisons des relations basées sur la transparence et l\'intégrité avec nos bénéficiaires, partenaires et donateurs.', 'icone' => 'fa-heart',              'couleur' => '#003399'],
            ['titre' => 'Espoir',          'description' => 'Nous apportons de l\'espoir à travers des programmes de soutien et d\'accompagnement personnalisés.',                          'icone' => 'fa-dove',               'couleur' => '#C8375A'],
            ['titre' => 'Solidarité',      'description' => 'Ensemble, nous sommes plus forts. La solidarité est au cœur de notre action et de notre engagement.',                          'icone' => 'fa-hand-holding-heart', 'couleur' => '#2E7D32'],
            ['titre' => 'Vie et guérison', 'description' => 'Nous œuvrons sans relâche pour offrir une meilleure qualité de vie et favoriser la guérison.',                                 'icone' => 'fa-leaf',               'couleur' => '#E8A020'],
        ];
    }
} catch (PDOException $e) {
    logError("Erreur presentation.php: " . $e->getMessage());
    $equipe  = [];
    $valeurs = [];
}

$stats_bg_image = '';
try {
    $stmt = $pdo->prepare("SELECT valeur FROM parametres WHERE cle = 'stats_bg_image'");
    $stmt->execute();
    $stats_bg_image = $stmt->fetchColumn() ?: '';
} catch (Exception $e) {}

require_once 'templates/header.php';
?>

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

    /* ── Offset ancres ── */
    #propos, #actions, #dates, #mission, #vision, #historique, #valeurs, #equipe {
        scroll-margin-top: 100px;
    }

    /* ── CE QUE NOUS FAISONS ── */
    .nos-actions {
        padding: 88px 0;
        background: var(--bg);
        position: relative;
        overflow: hidden;
    }
    .na-blob { position: absolute; border-radius: 50%; pointer-events: none; }
    .na-blob-tr {
        top: -180px; right: -180px; width: 500px; height: 500px;
        background: radial-gradient(circle, rgba(200,55,90,.07), transparent 60%);
    }
    .na-blob-bl {
        bottom: -140px; left: -140px; width: 440px; height: 440px;
        background: radial-gradient(circle, rgba(0,51,153,.06), transparent 60%);
    }

    /* Strips */
    .action-strip {
        position: relative;
        padding: 66px 0;
        overflow: hidden;
    }
    .action-strip + .action-strip {
        border-top: 1px solid var(--border);
    }
    .strip-01 { background: linear-gradient(115deg, rgba(217,79,122,.13) 0%, rgba(217,79,122,.04) 55%, transparent 100%); }
    .strip-02 { background: linear-gradient(245deg, rgba(0,51,153,.13) 0%, rgba(0,51,153,.04) 55%, transparent 100%); }

    /* Content block — left accent bar via ::before */
    .action-sc {
        max-width: 720px;
        position: relative; z-index: 1;
        padding-left: 24px;
    }
    .strip-01 .action-sc { margin-left: 0; }
    .strip-02 .action-sc { margin-left: auto; }
    .action-sc::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 3px; border-radius: 3px;
    }
    .strip-01 .action-sc::before {
        background: linear-gradient(180deg, var(--rose) 0%, rgba(200,55,90,.08) 100%);
    }
    .strip-02 .action-sc::before {
        background: linear-gradient(180deg, var(--blue) 0%, rgba(0,51,153,.08) 100%);
    }

    /* Number */
    .action-top { margin-bottom: 8px; }
    .action-big-num {
        display: inline-block;
        font-size: 2.4rem;
        font-weight: 900;
        line-height: 1;
        letter-spacing: -2px;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .strip-01 .action-big-num { background-image: linear-gradient(135deg, #E8607E, #6B001E); }
    .strip-02 .action-big-num { background-image: linear-gradient(135deg, #4E9FE5, #001450); }

    /* Dash rule */
    .action-rule {
        width: 30px; height: 2px; border-radius: 2px;
        margin: 10px 0 16px;
    }
    .strip-01 .action-rule { background: var(--rose); }
    .strip-02 .action-rule { background: var(--blue); }

    /* Title */
    .action-sc h3 {
        font-size: 1.38rem;
        font-weight: 800;
        color: var(--text);
        text-transform: uppercase;
        letter-spacing: .3px;
        line-height: 1.3;
        margin-bottom: 14px;
    }

    /* Desc */
    .action-sc > p {
        color: var(--text-2);
        line-height: 1.88;
        font-size: .97rem;
        margin-bottom: 28px;
        max-width: 580px;
    }

    /* Bullet list */
    .action-blist { list-style: none; padding: 0; margin: 0 0 28px; }
    .action-blist li {
        display: flex;
        align-items: flex-start;
        gap: 13px;
        color: var(--text-2);
        font-size: .96rem;
        line-height: 1.75;
        margin-bottom: 11px;
    }
    .bdot {
        width: 8px; height: 8px;
        border-radius: 50%;
        flex-shrink: 0; margin-top: 7px;
    }
    .strip-01 .bdot { background: var(--rose); }
    .strip-02 .bdot { background: var(--blue); }

    /* Stat cards — 3 en ligne */
    .action-metrics {
        display: flex;
        flex-direction: row;
        gap: 10px;
        flex-wrap: wrap;
    }
    .action-metric {
        display: grid;
        grid-template-columns: auto 1fr;
        align-items: center;
        background: var(--white);
        border: 1px solid var(--border);
        border-left: 3px solid transparent;
        border-radius: 10px;
        padding: 12px 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,.05);
        transition: transform .22s, box-shadow .22s;
        flex: 1;
        min-width: 0;
    }
    .strip-01 .action-metric { border-left-color: var(--rose); }
    .strip-02 .action-metric { border-left-color: var(--blue); }
    .action-metric:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,.09);
    }
    .metric-val {
        font-size: 1rem;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
        padding-right: 14px;
    }
    .strip-01 .metric-val { color: var(--rose); }
    .strip-02 .metric-val { color: var(--blue); }
    .metric-lab {
        font-size: .77rem;
        color: var(--text-2);
        line-height: 1.38;
        padding-left: 14px;
        border-left: 1px solid var(--border);
    }

    @media (max-width: 860px) {
        .strip-02 .action-sc  { margin-left: 0; }
        .action-sc h3         { font-size: 1.2rem; }
        .action-metrics       { max-width: none; }
    }
    @media (max-width: 520px) {
        .action-strip         { padding: 46px 0; }
        .action-sc            { padding-left: 16px; }
        .action-big-num       { font-size: 2rem; }
        .action-metrics       { flex-direction: column; gap: 8px; }
        .action-metric        { display: flex; flex-direction: row; align-items: center; gap: 12px; flex: none; }
        .metric-val           { font-size: 1.05rem; min-width: 72px; padding-right: 0; }
        .metric-lab           { font-size: .78rem; padding-left: 12px; border-left: 1px solid var(--border); }
        .action-metric:hover  { transform: none; box-shadow: 0 2px 12px rgba(0,0,0,.05); }
    }

    /* ── DATES CLÉS ── */
    .dates-section {
        padding: 80px 0 90px;
        background: var(--white);
        border-top: 1px solid var(--border);
    }
    .htl-outer {
        display: flex;
        align-items: center;
        gap: 20px;
        margin: 40px 0 44px;
    }
    .htl-btn {
        width: 48px; height: 48px;
        border-radius: 50%;
        border: none;
        background: linear-gradient(135deg, var(--rose), #8B0030);
        color: #fff;
        font-size: 14px;
        cursor: pointer;
        flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 4px 18px rgba(200,55,90,.30);
        transition: opacity .2s, transform .2s;
    }
    .htl-btn:hover  { opacity: .85; transform: scale(1.08); }
    .htl-btn:disabled {
        background: var(--border);
        box-shadow: none;
        cursor: default;
        transform: none;
        opacity: 1;
    }
    .htl-viewport { flex: 1; overflow: hidden; }
    .htl-track { position: relative; padding: 10px 0 30px; }

    .htl-line-bg {
        position: absolute;
        top: 15px; left: 0; right: 0;
        height: 2px;
        background: var(--border);
        border-radius: 2px;
    }
    .htl-line-progress {
        position: absolute; top: 0; left: 0;
        height: 100%; width: 0;
        background: linear-gradient(90deg, var(--blue), var(--blue-mid));
        border-radius: 2px;
        transition: width .4s cubic-bezier(.4,0,.2,1);
    }
    .htl-dots-row {
        display: flex;
        justify-content: space-between;
        position: relative; z-index: 1;
    }
    .htl-dot-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }
    .htl-dot {
        width: 13px; height: 13px;
        border-radius: 50%;
        background: var(--white);
        border: 2px solid #D1D5DB;
        transition: all .25s;
    }
    .htl-dot-wrap.is-past .htl-dot {
        background: var(--blue);
        border-color: var(--blue);
    }
    .htl-dot-wrap.is-active .htl-dot {
        width: 22px; height: 22px;
        margin-top: -4px;
        background: var(--rose);
        border-color: var(--rose);
        box-shadow: 0 0 0 5px rgba(200,55,90,.18);
    }
    .htl-year-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--muted);
        transition: color .25s;
        white-space: nowrap;
    }
    .htl-dot-wrap.is-past   .htl-year-label { color: var(--blue); }
    .htl-dot-wrap.is-active .htl-year-label { color: var(--rose); font-size: 12px; }

    .htl-display {
        text-align: center;
        min-height: 110px;
        padding: 0 80px;
        transition: opacity .18s;
    }
    .htl-display.fading { opacity: 0; }
    .htl-display-year {
        font-size: 2.6rem;
        font-weight: 900;
        line-height: 1;
        margin-bottom: 10px;
        background: linear-gradient(135deg, var(--rose), #8B0030);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .htl-display-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 8px;
    }
    .htl-display-text {
        font-size: .89rem;
        color: var(--muted);
        max-width: 520px;
        margin: 0 auto;
        line-height: 1.72;
    }
    @media (max-width: 640px) {
        .htl-display { padding: 0; }
        .htl-display-year { font-size: 2rem; }
        .htl-btn { width: 38px; height: 38px; font-size: 12px; }
        .htl-year-label { font-size: 9.5px; }
    }

    /* ── HERO ── */
    .page-hero {
        background:
            linear-gradient(140deg, rgba(0,10,40,.88) 0%, rgba(0,26,102,.82) 55%, rgba(21,101,192,.76) 100%),
            url('images/site/image6.jpg') center / cover no-repeat;
        padding: 90px 0 130px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .page-hero::before {
        content: '';
        position: absolute; inset: 0;
        background: radial-gradient(ellipse 55% 60% at 85% 40%, rgba(200,55,90,.15), transparent);
        pointer-events: none;
    }
    .page-hero .container { position: relative; z-index: 2; }

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
        text-decoration: none;
    }
    .page-hero h1 {
        font-size: clamp(2.4rem, 5vw, 3.8rem);
        font-weight: 800;
        color: #fff;
        line-height: 1.1;
        margin-bottom: 18px;
    }
    .page-hero h1 span { color: #F9A8C4; }
    .hero-desc {
        font-size: 1.02rem;
        color: rgba(255,255,255,.88);
        max-width: 660px;
        margin: 0 auto 36px;
        line-height: 1.85;
    }
    .anchor-nav {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .anchor-nav a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,.12);
        color: rgba(255,255,255,.92);
        padding: 8px 20px;
        border-radius: 100px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid rgba(255,255,255,.2);
        transition: background .2s, transform .2s, border-color .2s;
    }
    .anchor-nav a:hover {
        background: var(--rose);
        border-color: var(--rose);
        color: #fff;
        transform: translateY(-2px);
    }
    .hero-wave {
        position: absolute;
        bottom: -1px; left: 0;
        width: 100%; line-height: 0; z-index: 1;
    }

    /* ── SECTION HEADER ── */
    .sec-header {
        text-align: center;
        margin-bottom: 56px;
    }
    .sec-tag {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: var(--blue-lite);
        color: var(--blue);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1.8px;
        text-transform: uppercase;
        padding: 5px 14px;
        border-radius: 100px;
        margin-bottom: 12px;
    }
    .sec-header h2 {
        font-size: clamp(1.7rem, 3vw, 2.4rem);
        font-weight: 800;
        color: var(--text);
        margin-bottom: 10px;
    }
    .sec-divider {
        width: 52px; height: 4px;
        background: linear-gradient(90deg, var(--rose), var(--blue-mid));
        border-radius: 4px;
        margin: 0 auto 16px;
    }
    .sec-sub {
        font-size: 1rem;
        color: var(--muted);
        max-width: 480px;
        margin: 0 auto;
        line-height: 1.7;
    }

    /* ── MISSION & VISION ── */
    .mission-vision {
        background: var(--white);
        padding: 80px 0;
    }
    .mv-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 28px;
    }
    .mv-card {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 22px;
        padding: 44px 40px;
        transition: transform .3s, box-shadow .3s;
        position: relative;
        overflow: hidden;
    }
    .mv-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; bottom: 0;
        width: 5px;
        border-radius: 22px 0 0 22px;
    }
    .mv-card.mission::before { background: var(--blue); }
    .mv-card.vision::before  { background: var(--rose); }
    .mv-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 48px rgba(0,0,0,.09);
        background: var(--white);
    }
    .mv-icon {
        width: 68px; height: 68px;
        border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        font-size: 28px;
        margin-bottom: 24px;
    }
    .mv-card.mission .mv-icon { background: var(--blue-lite); color: var(--blue); }
    .mv-card.vision  .mv-icon { background: var(--rose-lite); color: var(--rose); }
    .mv-card h3 {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 16px;
    }
    .mv-card p, .mv-card li {
        color: var(--text-2);
        line-height: 1.8;
        font-size: .97rem;
        margin-bottom: 10px;
    }
    .mv-card ul { padding-left: 20px; margin-bottom: 16px; }
    .mv-card li { margin-bottom: 6px; }
    .mv-quote {
        font-style: italic;
        color: var(--blue);
        font-size: .96rem;
        border-left: 3px solid var(--blue-lite);
        padding-left: 14px;
        margin-top: 16px;
        line-height: 1.7;
    }
    .mv-card.vision .mv-quote { color: var(--rose); border-left-color: var(--rose-lite); }

    /* ── STATS ── */
    .stats-section {
        background: linear-gradient(140deg, var(--blue-dark) 0%, var(--blue) 55%, #1565C0 100%);
        padding: 70px 0;
        position: relative;
        overflow: hidden;
    }
    .stats-section::before, .stats-section::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        background: rgba(255,255,255,.05);
        pointer-events: none;
    }
    .stats-section::before { width: 400px; height: 400px; top: -150px; right: -80px; }
    .stats-section::after  { width: 280px; height: 280px; bottom: -100px; left: -60px; }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0;
        position: relative; z-index: 2;
    }
    .stat-item {
        padding: 28px 20px;
        text-align: center;
        border-right: 1px solid rgba(255,255,255,.12);
    }
    .stat-item:last-child { border-right: none; }
    .stat-number {
        font-size: 3rem;
        font-weight: 800;
        color: #fff;
        line-height: 1;
        margin-bottom: 8px;
    }
    .stat-label {
        font-size: 13px;
        color: rgba(255,255,255,.78);
        font-weight: 500;
    }

    /* ── TIMELINE ── */
    .historique {
        padding: 80px 0;
        background: var(--bg);
    }
    .timeline {
        position: relative;
        max-width: 960px;
        margin: 0 auto;
        padding: 10px 0;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        width: 3px;
        height: 100%;
        background: linear-gradient(180deg, var(--blue), var(--rose), var(--sage));
        border-radius: 3px;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 48px;
        width: 100%;
        display: flex;
        justify-content: flex-end;
    }
    .timeline-item:nth-child(even) { justify-content: flex-start; }

    .timeline-content {
        width: 40%;
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 28px 30px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        transition: transform .3s, box-shadow .3s;
        position: relative;
    }
    .timeline-item:nth-child(odd)  .timeline-content { margin-right: 7%; }
    .timeline-item:nth-child(even) .timeline-content { margin-left: 7%; }
    .timeline-content:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0,0,0,.1);
    }

    .timeline-dot {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        width: 18px; height: 18px;
        background: var(--white);
        border: 4px solid var(--blue);
        border-radius: 50%;
        top: 28px;
        z-index: 1;
        transition: transform .3s, border-color .3s;
    }
    .timeline-item:nth-child(even) .timeline-dot { border-color: var(--rose); }
    .timeline-item:hover .timeline-dot { transform: translateX(-50%) scale(1.3); }

    .timeline-year {
        display: inline-flex;
        align-items: center;
        background: var(--blue);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        padding: 4px 16px;
        border-radius: 100px;
        margin-bottom: 12px;
    }
    .timeline-item:nth-child(even) .timeline-year { background: var(--rose); }

    .timeline-content h3 {
        font-size: 1rem;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 8px;
    }
    .timeline-content p {
        color: var(--muted);
        line-height: 1.7;
        font-size: .93rem;
        margin: 0;
    }

    /* ── VALEURS ── */
    .valeurs {
        padding: 80px 0;
        background: var(--white);
    }
    .valeurs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
    }
    .valeur-card {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 38px 28px;
        text-align: center;
        transition: transform .3s, box-shadow .3s, background .3s;
        position: relative;
        overflow: hidden;
    }
    .valeur-card::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 4px;
        opacity: 0;
        transition: opacity .3s;
        border-radius: 0 0 20px 20px;
    }
    .valeur-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 40px rgba(0,0,0,.09);
        background: var(--white);
    }
    .valeur-card:hover::after { opacity: 1; }
    .valeur-icon {
        width: 72px; height: 72px;
        border-radius: 20px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px;
        font-size: 28px;
        transition: transform .3s;
    }
    .valeur-card:hover .valeur-icon { transform: scale(1.1); }
    .valeur-card h3 {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 10px;
    }
    .valeur-card p {
        color: var(--muted);
        line-height: 1.7;
        font-size: .93rem;
    }

    /* ── ÉQUIPE ── */
    .equipe {
        padding: 80px 0;
        background: var(--bg);
    }
    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 24px;
    }
    .team-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 22px;
        overflow: hidden;
        transition: transform .3s, box-shadow .3s;
    }
    .team-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 48px rgba(0,0,0,.11);
    }
    .team-image {
        height: 280px;
        overflow: hidden;
        background: var(--bg);
    }
    .team-image img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform .5s;
    }
    .team-card:hover .team-image img { transform: scale(1.07); }
    .team-info { padding: 24px 24px 22px; }
    .team-info h3 {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 4px;
    }
    .team-role {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--blue);
        text-transform: uppercase;
        letter-spacing: .8px;
        margin-bottom: 12px;
    }
    .team-bio {
        font-size: .9rem;
        color: var(--muted);
        line-height: 1.65;
        margin-bottom: 16px;
    }
    .team-social {
        display: flex;
        gap: 8px;
    }
    .team-social a {
        width: 34px; height: 34px;
        border-radius: 50%;
        background: var(--blue-lite);
        display: flex; align-items: center; justify-content: center;
        color: var(--blue);
        font-size: 13px;
        text-decoration: none;
        transition: background .25s, color .25s, transform .25s;
    }
    .team-social a:hover {
        background: var(--blue);
        color: #fff;
        transform: translateY(-3px);
    }

    /* ── BOUTON LIRE LA SUITE ── */
    .btn-lire-suite {
        display: inline-flex; align-items: center; gap: 6px;
        background: none;
        border: 1.5px solid var(--blue);
        color: var(--blue);
        font-size: 12px;
        font-weight: 600;
        padding: 6px 16px;
        border-radius: 100px;
        cursor: pointer;
        transition: background .2s, color .2s, transform .2s;
        margin-top: 4px; margin-bottom: 14px;
        font-family: 'Inter', sans-serif;
    }
    .btn-lire-suite:hover {
        background: var(--blue); color: #fff; transform: translateY(-2px);
    }

    /* ── MODAL BIOGRAPHIE ── */
    .bio-modal-overlay {
        display: none; position: fixed; inset: 0; z-index: 9000;
        background: rgba(0,20,60,.6);
        backdrop-filter: blur(4px);
        align-items: center; justify-content: center; padding: 20px;
    }
    .bio-modal-overlay.active { display: flex; }
    .bio-modal {
        background: var(--white);
        border-radius: 24px;
        max-width: 560px; width: 100%;
        padding: 44px 40px;
        position: relative;
        box-shadow: 0 28px 70px rgba(0,0,0,.22);
        max-height: 85vh; overflow-y: auto;
        animation: modalIn .25s ease;
    }
    @keyframes modalIn {
        from { opacity: 0; transform: translateY(16px) scale(.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .bio-modal-close {
        position: absolute; top: 16px; right: 16px;
        width: 36px; height: 36px; border-radius: 50%;
        border: none; background: var(--bg); cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; color: var(--muted); transition: background .2s;
    }
    .bio-modal-close:hover { background: var(--border); color: var(--text); }
    .bio-modal-avatar {
        width: 70px; height: 70px; border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--blue-lite);
        margin-bottom: 14px;
    }
    .bio-modal-name {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 3px;
    }
    .bio-modal-fonction {
        color: var(--blue);
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .8px;
        margin-bottom: 18px;
    }
    .bio-modal-divider {
        height: 4px; width: 48px; border-radius: 4px;
        background: linear-gradient(90deg, var(--rose), var(--blue-mid));
        margin-bottom: 18px;
    }
    .bio-modal-text { color: var(--text-2); line-height: 1.85; font-size: .96rem; }

    /* ── RESPONSIVE ── */
    @media (max-width: 960px) {
        .mv-grid    { grid-template-columns: 1fr; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .stat-item  { border-right: none; border-bottom: 1px solid rgba(255,255,255,.12); }
        .stat-item:nth-child(2n) { border-bottom: none; }
    }
    @media (max-width: 768px) {
        .timeline::before { left: 24px; }
        .timeline-item,
        .timeline-item:nth-child(even) { justify-content: flex-end; }
        .timeline-content {
            width: 82%;
            margin-left: 52px !important;
            margin-right: 0 !important;
        }
        .timeline-dot { left: 24px; }
    }
    @media (max-width: 560px) {
        .stats-grid { grid-template-columns: 1fr 1fr; }
        .bio-modal  { padding: 32px 20px; }
    }
</style>

<!-- ── HERO ── -->
<section class="page-hero" id="propos">
    <div class="container">
        <div data-aos="fade-up">
            <a href="#propos" class="hero-badge">
                <i class="fas fa-info-circle"></i> À propos du GSCC
            </a>
            <h1>Qui sommes-<span>nous</span>&nbsp;?</h1>
            <p class="hero-desc">
                Le Groupe de Support Contre le Cancer (GSCC) est une organisation à but non lucratif fondée en 1999 par un groupe de dames, qui, ayant vécu le cancer personnellement ou à travers un proche, ont décidé de donner gracieusement de leur temps et de partager leurs expériences. Reconnu par le Ministère des Affaires Sociales et du Travail, le GSCC travaille depuis sa fondation à l'amélioration du sort des personnes atteintes de cancer, à la sensibilisation de la population, à sa prévention et à son dépistage.
            </p>
            <nav class="anchor-nav" aria-label="Navigation rapide">
                <a href="#actions"><i class="fas fa-star"></i> Nos actions</a>
                <a href="#mission"><i class="fas fa-bullseye"></i> Mission</a>
                <a href="#vision"><i class="fas fa-chart-line"></i> Impact</a>
                <a href="#historique"><i class="fas fa-clock-rotate-left"></i> Historique</a>
                <a href="#valeurs"><i class="fas fa-heart"></i> Valeurs</a>
                <a href="#equipe"><i class="fas fa-users"></i> Équipe</a>
            </nav>
        </div>
    </div>
    <div class="hero-wave">
        <svg viewBox="0 0 1440 64" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0,32 C360,64 1080,0 1440,32 L1440,64 L0,64 Z" fill="#FFFFFF"/>
        </svg>
    </div>
</section>

<!-- ── CE QUE NOUS FAISONS ── -->
<section id="actions" class="nos-actions">
    <div class="na-blob na-blob-tr"></div>
    <div class="na-blob na-blob-bl"></div>
    <div class="container">

        <div class="sec-header" data-aos="fade-up">
            <div class="sec-tag"><i class="fas fa-star"></i> Nos programmes</div>
            <h2>Ce que nous faisons</h2>
            <div class="sec-divider"></div>
            <p class="sec-sub">25 ans d'actions concrètes pour réduire la mortalité par cancer en Haïti</p>
        </div>

        <!-- ── Strip 01 ── -->
        <div class="action-strip strip-01" data-aos="fade-up">
            <div class="action-sc">
                <div class="action-top">
                    <span class="action-big-num">01</span>
                </div>
                <div class="action-rule"></div>
                <h3>Campagnes de sensibilisation et dépistage</h3>
                <p>Organisation de campagnes dans la zone métropolitaine et les provinces et dans les médias (radio, télé, réseaux sociaux) pour informer et dépister la population.</p>
                <div class="action-metrics">
                    <div class="action-metric">
                        <span class="metric-val">+250 000</span>
                        <span class="metric-lab">personnes sensibilisées en 25 ans</span>
                    </div>
                    <div class="action-metric">
                        <span class="metric-val">8 697</span>
                        <span class="metric-lab">personnes touchées (2021–2023)</span>
                    </div>
                    <div class="action-metric">
                        <span class="metric-val">4 166</span>
                        <span class="metric-lab">personnes dépistées</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Strip 02 ── -->
        <div class="action-strip strip-02" data-aos="fade-up">
            <div class="action-sc">
                <div class="action-top">
                    <span class="action-big-num">02</span>
                </div>
                <div class="action-rule"></div>
                <h3>Accompagnement des patients</h3>
                <ul class="action-blist">
                    <li><span class="bdot"></span>Soutien médical, psychologique, nutritionnel et social</li>
                    <li><span class="bdot"></span>Suivis médicaux, subventions, dons de médicaments</li>
                    <li><span class="bdot"></span>Suivi auprès des ambassades pour faciliter les traitements à l'étranger</li>
                </ul>
                <div class="action-metrics">
                    <div class="action-metric">
                        <span class="metric-val">+2 593</span>
                        <span class="metric-lab">patients accompagnés en 25 ans</span>
                    </div>
                    <div class="action-metric">
                        <span class="metric-val">+2 625</span>
                        <span class="metric-lab">subventions accordées</span>
                    </div>
                    <div class="action-metric">
                        <span class="metric-val">170</span>
                        <span class="metric-lab">patients suivis en 2024, dont 36 avec subvention directe</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- ── DATES CLÉS ── -->
<section id="dates" class="dates-section">
    <div class="container">

        <div class="sec-header" data-aos="fade-up">
           <!-- <div class="sec-tag"><i class="fas fa-clock-rotate-left"></i> Jalons historiques</div> -->
            <h2>Nos dates clés</h2>
            <div class="sec-divider"></div>
        </div>

        <div class="htl-outer" data-aos="fade-up" data-aos-delay="80">
            <button class="htl-btn" id="htlPrev" aria-label="Précédent">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="htl-viewport">
                <div class="htl-track">
                    <div class="htl-line-bg">
                        <div class="htl-line-progress" id="htlProgress"></div>
                    </div>
                    <div class="htl-dots-row" id="htlDotsRow"></div>
                </div>
            </div>
            <button class="htl-btn" id="htlNext" aria-label="Suivant">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <div class="htl-display" id="htlDisplay" data-aos="fade-up" data-aos-delay="120">
            <div class="htl-display-year"  id="htlYear"></div>
            <div class="htl-display-title" id="htlTitle"></div>
            <div class="htl-display-text"  id="htlText"></div>
        </div>

    </div>
</section>

<!-- ── MISSION & VISION ── -->
<section id="mission" class="mission-vision">
    <div class="container">
        <div class="mv-grid">

            <div class="mv-card mission" data-aos="fade-right">
                <div class="mv-icon"><i class="fas fa-bullseye"></i></div>
                <h3>Notre Mission</h3>
                <p>Réduire la mortalité par cancer en Haïti en :</p>
                <ul>
                    <li>Informant et sensibilisant la population sur le cancer.</li>
                    <li>Éduquant sur la maladie.</li>
                    <li>Dépistant précocement la maladie.</li>
                    <li>Accompagnant les patients atteints de cancer en Haïti.</li>
                    <li>Formant des professionnels engagés dans la lutte contre le cancer.</li>
                </ul>
                <blockquote class="mv-quote">"Vivre pour Aimer, Vivre pour Aider, Vivre pour Partager, Vivre Intensément"</blockquote>
            </div>

            <div id="vision" class="mv-card vision" data-aos="fade-left" data-aos-delay="80">
                <div class="mv-icon"><i class="fas fa-chart-line"></i></div>
                <h3>Notre Impact</h3>
                <p>Depuis 25 ans, le GSCC agit chaque jour pour sauver des vies, informer la population et accompagner les patients les plus vulnérables à travers tout le pays.</p>
                <ul>
                    <li>Plus de 250 000 personnes sensibilisées à la prévention et au dépistage du cancer</li>
                    <li>Plus de 2 500 patients accompagnés depuis la création du GSCC</li>
                </ul>
                <blockquote class="mv-quote">"Grâce à vous, nous continuons à agir. Grâce à vous, nous continuons à sauver."</blockquote>
            </div>

        </div>
    </div>
</section>

<!-- ── STATISTIQUES ── -->
<section class="stats-section"<?php if ($stats_bg_image):
    $bg_url = htmlspecialchars(rtrim(SITE_URL, '/') . '/' . ltrim($stats_bg_image, '/'));
    echo ' style="background-image:linear-gradient(rgba(0,20,80,.80),rgba(0,51,153,.82)),url(\''.$bg_url.'\');background-size:cover;background-position:center;background-attachment:fixed;"';
endif; ?>>
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item" data-aos="fade-up">
                <div class="stat-number">25+</div>
                <div class="stat-label">Années d'engagement</div>
            </div>
            <div class="stat-item" data-aos="fade-up" data-aos-delay="80">
                <div class="stat-number">500+</div>
                <div class="stat-label">Membres actifs</div>
            </div>
            <div class="stat-item" data-aos="fade-up" data-aos-delay="160">
                <div class="stat-number">50+</div>
                <div class="stat-label">Projets réalisés</div>
            </div>
            <div class="stat-item" data-aos="fade-up" data-aos-delay="240">
                <div class="stat-number">2 500+</div>
                <div class="stat-label">Personnes aidées</div>
            </div>
        </div>
    </div>
</section>

<!-- ── HISTORIQUE ── -->
<section id="historique" class="historique">
    <div class="container">
        <div class="sec-header" data-aos="fade-up">
            <div class="sec-tag"><i class="fas fa-clock-rotate-left"></i> Notre parcours</div>
            <h2>Notre Histoire</h2>
            <div class="sec-divider"></div>
        </div>

        <div class="timeline">
            <div class="timeline-item" data-aos="fade-right">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="timeline-year">1999</span>
                    <h3>Fondation du GSCC</h3>
                    <p>Création du Groupe de Support Contre le Cancer par un groupe de dames bénévoles, animées par la volonté d'apporter du soutien aux personnes atteintes de cancer en Haïti.</p>
                </div>
            </div>
            <div class="timeline-item" data-aos="fade-left">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="timeline-year">2003</span>
                    <h3>Reconnaissance officielle</h3>
                    <p>Reconnaissance par le Ministère des Affaires Sociales et du Travail, marquant une étape clé dans la légitimité de l'organisation.</p>
                </div>
            </div>
            <div class="timeline-item" data-aos="fade-right">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="timeline-year">2010</span>
                    <h3>Premier programme d'aide financière</h3>
                    <p>Lancement du premier programme d'aide financière pour permettre aux patients à faibles revenus d'accéder aux traitements nécessaires.</p>
                </div>
            </div>
            <div class="timeline-item" data-aos="fade-left">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="timeline-year">2015</span>
                    <h3>Ouverture du centre d'écoute</h3>
                    <p>Inauguration du premier centre d'écoute et de soutien psychologique pour les patients et leurs familles à travers Haïti.</p>
                </div>
            </div>
            <div class="timeline-item" data-aos="fade-right">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="timeline-year">2020</span>
                    <h3>Campagnes de sensibilisation nationale</h3>
                    <p>Lancement de grandes campagnes de sensibilisation à travers tout le pays sur la prévention et le dépistage précoce du cancer.</p>
                </div>
            </div>
            <div class="timeline-item" data-aos="fade-left">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="timeline-year">2024</span>
                    <h3>Plus de 2 500 personnes aidées</h3>
                    <p>Aujourd'hui, le GSCC a accompagné plus de 2 500 personnes dans leur combat contre le cancer et continue d'étendre ses actions à travers tout le pays.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── VALEURS ── -->
<section id="valeurs" class="valeurs">
    <div class="container">
        <div class="sec-header" data-aos="fade-up">
            <div class="sec-tag"><i class="fas fa-heart"></i> Ce qui nous guide</div>
            <h2>Nos Valeurs &amp; Engagements</h2>
            <div class="sec-divider"></div>
        </div>
        <div class="valeurs-grid">
            <?php
            $couleurs_defaut = ['#003399','#C8375A','#2E7D32','#E8A020'];
            foreach ($valeurs as $index => $valeur):
                $couleur = htmlspecialchars($valeur['couleur'] ?? $couleurs_defaut[$index % 4]);
                $icone   = htmlspecialchars($valeur['icone']   ?? 'fa-heart');
            ?>
            <div class="valeur-card" data-aos="fade-up" data-aos-delay="<?= $index * 80 ?>"
                 style="--vc:<?= $couleur ?>">
                <style>.valeur-card:nth-child(<?= $index+1 ?>)::after{ background:<?= $couleur ?>; }</style>
                <div class="valeur-icon" style="background:<?= $couleur ?>1A; color:<?= $couleur ?>;">
                    <i class="fas <?= $icone ?>"></i>
                </div>
                <h3><?= e($valeur['titre']) ?></h3>
                <p><?= e($valeur['description']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── ÉQUIPE ── -->
<section id="equipe" class="equipe">
    <div class="container">
        <div class="sec-header" data-aos="fade-up">
            <div class="sec-tag"><i class="fas fa-users"></i> Les visages du GSCC</div>
            <h2>Notre Équipe</h2>
            <div class="sec-divider"></div>
            <p class="sec-sub">Des professionnels dévoués et passionnés à votre service</p>
        </div>

        <div class="team-grid">
            <?php if (empty($equipe)):
                $membres_defaut = [
                    ['nom' => 'Dr. Marie Jean-Baptiste',  'fonction' => 'Présidente Fondatrice',      'bio' => 'Oncologue avec plus de 20 ans d\'expérience, dédiée à la lutte contre le cancer en Haïti.', 'rand' => 10],
                    ['nom' => 'Pierre Richard Alexandre', 'fonction' => 'Coordinateur des Programmes', 'bio' => 'Expert en gestion de projets humanitaires, coordonne les actions sur le terrain.',         'rand' => 11],
                    ['nom' => 'Rose-Merline Charles',     'fonction' => 'Psychologue Clinicienne',     'bio' => 'Spécialiste en accompagnement psychologique des patients et familles.',                   'rand' => 12],
                    ['nom' => 'Jean-Claude Michel',       'fonction' => 'Responsable Administratif',   'bio' => 'Gère les aspects administratifs et financiers de l\'organisation.',                       'rand' => 13],
                ];
                foreach ($membres_defaut as $i => $m): ?>
                <div class="team-card" data-aos="fade-up" data-aos-delay="<?= $i * 80 ?>">
                    <div class="team-image">
                        <img src="https://picsum.photos/400/500?random=<?= $m['rand'] ?>" alt="<?= htmlspecialchars($m['nom']) ?>">
                    </div>
                    <div class="team-info">
                        <h3><?= htmlspecialchars($m['nom']) ?></h3>
                        <div class="team-role"><?= htmlspecialchars($m['fonction']) ?></div>
                        <p class="team-bio"><?= htmlspecialchars($m['bio']) ?></p>
                        <div class="team-social">
                            <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" aria-label="Email"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach;
            else:
                foreach ($equipe as $i => $membre):
                    if (!empty($membre['photo'])) {
                        $photo_src = rtrim(SITE_URL, '/') . '/' . ltrim($membre['photo'], '/');
                    } else {
                        $photo_src = 'https://ui-avatars.com/api/?name='
                            . urlencode(($membre['prenom'] ?? '') . '+' . ($membre['nom'] ?? ''))
                            . '&size=400&background=003399&color=fff&font-size=0.33';
                    }
                    $fallback = 'https://ui-avatars.com/api/?name='
                        . urlencode(($membre['prenom'] ?? '') . '+' . ($membre['nom'] ?? ''))
                        . '&size=400&background=003399&color=fff&font-size=0.33';
                ?>
                <div class="team-card" data-aos="fade-up" data-aos-delay="<?= $i * 80 ?>">
                    <div class="team-image">
                        <img src="<?= htmlspecialchars($photo_src) ?>"
                             alt="<?= htmlspecialchars(($membre['prenom'] ?? '') . ' ' . ($membre['nom'] ?? '')) ?>"
                             onerror="this.onerror=null;this.src='<?= htmlspecialchars($fallback) ?>';">
                    </div>
                    <div class="team-info">
                        <h3><?= e(($membre['prenom'] ?? '') . ' ' . ($membre['nom'] ?? '')) ?></h3>
                        <div class="team-role"><?= e($membre['fonction'] ?? '') ?></div>
                        <?php if (!empty($membre['bio'])): ?>
                        <p class="team-bio"><?= e(mb_substr($membre['bio'], 0, 130)) ?><?= mb_strlen($membre['bio']) > 130 ? '…' : '' ?></p>
                        <?php endif; ?>
                        <?php if (!empty($membre['bio_complete'] ?? '')): ?>
                        <button class="btn-lire-suite" onclick="openBioModal(<?= $i ?>)">
                            Lire la suite <i class="fas fa-arrow-right" style="font-size:.7rem;"></i>
                        </button>
                        <?php endif; ?>
                        <div class="team-social">
                            <?php if (!empty($membre['email'])): ?>
                                <a href="mailto:<?= e($membre['email']) ?>" aria-label="Email"><i class="fas fa-envelope"></i></a>
                            <?php endif; ?>
                            <?php
                            $reseaux = [];
                            if (!empty($membre['reseaux_sociaux'])) {
                                $reseaux = is_array($membre['reseaux_sociaux'])
                                    ? $membre['reseaux_sociaux']
                                    : json_decode($membre['reseaux_sociaux'], true) ?? [];
                            }
                            ?>
                            <?php if (!empty($reseaux['facebook'])): ?>
                                <a href="<?= e($reseaux['facebook']) ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($reseaux['linkedin'])): ?>
                                <a href="<?= e($reseaux['linkedin']) ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($reseaux['twitter'])): ?>
                                <a href="<?= e($reseaux['twitter']) ?>" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($reseaux['instagram'])): ?>
                                <a href="<?= e($reseaux['instagram']) ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach;
            endif; ?>
        </div>
    </div>
</section>

<!-- ── MODAL BIOGRAPHIE ── -->
<div class="bio-modal-overlay" id="bioModal" onclick="if(event.target===this)closeBioModal()">
    <div class="bio-modal" role="dialog" aria-modal="true">
        <button class="bio-modal-close" onclick="closeBioModal()" aria-label="Fermer">
            <i class="fas fa-times"></i>
        </button>
        <img id="bioModalAvatar" class="bio-modal-avatar" src="" alt="">
        <div class="bio-modal-name"     id="bioModalName"></div>
        <div class="bio-modal-fonction" id="bioModalFonction"></div>
        <div class="bio-modal-divider"></div>
        <div class="bio-modal-text"     id="bioModalText"></div>
    </div>
</div>

<?php if (!empty($equipe)): ?>
<script>
const bioData = <?= json_encode(array_values(array_map(function($m) {
    $photo = !empty($m['photo'])
        ? rtrim(SITE_URL, '/') . '/' . ltrim($m['photo'], '/')
        : 'https://ui-avatars.com/api/?name=' . urlencode(($m['prenom'] ?? '') . '+' . ($m['nom'] ?? '')) . '&size=200&background=003399&color=fff&font-size=0.33';
    return [
        'nom'          => (($m['prenom'] ?? '') . ' ' . ($m['nom'] ?? '')),
        'fonction'     => $m['fonction'] ?? '',
        'photo'        => $photo,
        'bio_complete' => $m['bio_complete'] ?? '',
    ];
}, $equipe)), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

function openBioModal(i) {
    const d = bioData[i];
    document.getElementById('bioModalName').textContent     = d.nom;
    document.getElementById('bioModalFonction').textContent = d.fonction;
    document.getElementById('bioModalAvatar').src           = d.photo;
    document.getElementById('bioModalAvatar').alt           = d.nom;
    document.getElementById('bioModalText').innerHTML       =
        d.bio_complete.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
    document.getElementById('bioModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeBioModal() {
    document.getElementById('bioModal').classList.remove('active');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeBioModal(); });
</script>
<?php endif; ?>

<script>
(function () {
    const events = [
        { year: '1999', title: 'Fondation du GSCC',
          text: 'Création du Groupe de Support Contre le Cancer par un groupe de dames bénévoles, animées par la volonté d\'apporter du soutien aux personnes atteintes de cancer en Haïti.' },
        { year: '2003', title: 'Reconnaissance officielle',
          text: 'Reconnaissance par le Ministère des Affaires Sociales et du Travail, marquant une étape clé dans la légitimité et la crédibilité de l\'organisation.' },
        { year: '2006', title: 'Premiers partenariats internationaux',
          text: 'Développement des premiers partenariats avec des organisations internationales de lutte contre le cancer pour renforcer les capacités du GSCC.' },
        { year: '2010', title: 'Programme d\'aide financière',
          text: 'Lancement du premier programme d\'aide financière pour permettre aux patients à faibles revenus d\'accéder aux traitements nécessaires.' },
        { year: '2013', title: 'Extension aux provinces',
          text: 'Extension des activités de sensibilisation et d\'accompagnement au-delà de la zone métropolitaine, vers les provinces haïtiennes.' },
        { year: '2015', title: 'Centre d\'écoute',
          text: 'Inauguration du premier centre d\'écoute et de soutien psychologique pour les patients et leurs familles à travers Haïti.' },
        { year: '2018', title: '1 000 patients accompagnés',
          text: 'Franchissement du cap symbolique de 1 000 patients accompagnés dans leur combat contre le cancer depuis la fondation du GSCC.' },
        { year: '2020', title: 'Campagnes nationales',
          text: 'Lancement de grandes campagnes de sensibilisation à travers tout le pays sur la prévention et le dépistage précoce du cancer.' },
        { year: '2022', title: 'Football Rose',
          text: 'Lancement de l\'initiative Football Rose, alliant sport et sensibilisation pour toucher un public plus large et promouvoir le dépistage.' },
        { year: '2024', title: '25ème anniversaire — 2 500+ patients',
          text: 'Célébration de 25 ans d\'engagement avec plus de 2 500 personnes accompagnées, 250 000 sensibilisées et 2 625 subventions accordées.' },
    ];

    let cur = 0;
    const dotsRow = document.getElementById('htlDotsRow');
    const progress = document.getElementById('htlProgress');
    const display  = document.getElementById('htlDisplay');
    const elYear   = document.getElementById('htlYear');
    const elTitle  = document.getElementById('htlTitle');
    const elText   = document.getElementById('htlText');
    const btnPrev  = document.getElementById('htlPrev');
    const btnNext  = document.getElementById('htlNext');

    if (!dotsRow) return;

    /* Build dots */
    events.forEach(function (ev, i) {
        var wrap = document.createElement('div');
        wrap.className = 'htl-dot-wrap';
        wrap.innerHTML = '<div class="htl-dot"></div><span class="htl-year-label">' + ev.year + '</span>';
        wrap.addEventListener('click', function () { goTo(i); });
        dotsRow.appendChild(wrap);
    });

    function refresh(idx, animate) {
        var ev  = events[idx];
        var pct = idx / (events.length - 1) * 100;

        if (animate) {
            display.classList.add('fading');
            setTimeout(function () {
                elYear.textContent  = ev.year;
                elTitle.textContent = ev.title;
                elText.textContent  = ev.text;
                display.classList.remove('fading');
            }, 180);
        } else {
            elYear.textContent  = ev.year;
            elTitle.textContent = ev.title;
            elText.textContent  = ev.text;
        }

        progress.style.width = pct + '%';

        dotsRow.querySelectorAll('.htl-dot-wrap').forEach(function (d, i) {
            d.classList.remove('is-active', 'is-past');
            if (i < idx) d.classList.add('is-past');
            if (i === idx) d.classList.add('is-active');
        });

        btnPrev.disabled = idx === 0;
        btnNext.disabled = idx === events.length - 1;
    }

    function goTo(idx) { cur = idx; refresh(idx, true); }

    btnPrev.addEventListener('click', function () { if (cur > 0) goTo(cur - 1); });
    btnNext.addEventListener('click', function () { if (cur < events.length - 1) goTo(cur + 1); });

    /* Keyboard navigation */
    document.addEventListener('keydown', function (e) {
        var section = document.getElementById('dates');
        if (!section) return;
        var r = section.getBoundingClientRect();
        if (r.top > window.innerHeight || r.bottom < 0) return;
        if (e.key === 'ArrowLeft'  && cur > 0) goTo(cur - 1);
        if (e.key === 'ArrowRight' && cur < events.length - 1) goTo(cur + 1);
    });

    refresh(0, false);
})();
</script>

<?php require_once 'templates/footer.php'; ?>
