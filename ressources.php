<?php
// sinformer-comprendre.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$page_title = "S'informer et comprendre";
$page_description = "Tout ce que vous devez savoir sur le cancer : types, symptômes, dépistage, traitements et prévention. Informations fiables pour mieux comprendre et agir.";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - <?= defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'GSCC' ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">

    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        :root {
            --blue:        #003399;
            --blue-dark:   #002270;
            --blue-mid:    #1a56cc;
            --blue-soft:   #EBF0FF;
            --rose:        #D94F7A;
            --rose-soft:   #FDE8EF;
            --green:       #2E7D32;
            --green-soft:  #E8F5E9;
            --orange:      #C05621;
            --orange-soft: #FEF3E2;
            --teal:        #0D7377;
            --teal-soft:   #E0F5F5;
            --purple:      #5B2D8E;
            --purple-soft: #F0EAFB;
            --dark:        #0D1117;
            --text:        #1F2937;
            --muted:       #4B5563;
            --light:       #6B7280;
            --border:      #D1D5DB;
            --bg:          #F7F8FC;
            --white:       #FFFFFF;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        .container { max-width: 1140px; margin: 0 auto; padding: 0 24px; }

        /* ══════════════════════════════════
           HERO
        ══════════════════════════════════ */
        .hero {
            background: linear-gradient(135deg, #001a66 0%, #003399 45%, #1a56cc 80%, #1a7abf 100%);
            padding: 80px 0 100px;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.06) 0%, transparent 50%),
                radial-gradient(circle at 10% 80%, rgba(217,79,122,0.15) 0%, transparent 45%);
        }
        .hero-inner {
            position: relative; z-index: 1;
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 60px; align-items: center;
        }
        .hero-eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            color: #E8F0FE; font-size: 11px; font-weight: 600;
            letter-spacing: 2px; text-transform: uppercase;
            padding: 6px 16px; border-radius: 20px; margin-bottom: 22px;
        }
        .hero h1 {
            font-family: 'Lora', serif;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700; color: #FFFFFF;
            line-height: 1.2; margin-bottom: 20px;
        }
        .hero h1 em { color: #93B4FF; font-style: italic; }
        .hero-desc {
            font-size: 16px; color: #C5D5F5;
            line-height: 1.8; margin-bottom: 32px; font-weight: 400;
        }
        .hero-stats {
            display: flex; gap: 32px; flex-wrap: wrap;
        }
        .hero-stat { text-align: left; }
        .hero-stat-num {
            font-family: 'Lora', serif;
            font-size: 2rem; font-weight: 700; color: #FFFFFF; line-height: 1;
        }
        .hero-stat-label { font-size: 12px; color: #93B4FF; font-weight: 500; margin-top: 4px; }

        /* Illustration côté droit */
        .hero-visual {
            display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
        }
        .hero-card {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 14px; padding: 20px 18px;
            backdrop-filter: blur(8px);
            transition: background 0.3s;
        }
        .hero-card:hover { background: rgba(255,255,255,0.16); }
        .hero-card:first-child { grid-column: 1 / -1; }
        .hero-card-icon {
            font-size: 22px; margin-bottom: 10px;
        }
        .hero-card h4 { font-size: 13px; font-weight: 700; color: #FFFFFF; margin-bottom: 6px; }
        .hero-card p  { font-size: 12px; color: #C5D5F5; line-height: 1.6; }

        /* Wave bas */
        .hero-wave { line-height: 0; }
        .hero-wave svg { display: block; }

        /* ══════════════════════════════════
           NAV ANCRES
        ══════════════════════════════════ */
        .sticky-nav {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .sticky-nav-inner {
            display: flex; gap: 0; overflow-x: auto;
            scrollbar-width: none;
        }
        .sticky-nav-inner::-webkit-scrollbar { display: none; }
        .nav-anchor {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 16px 22px; white-space: nowrap;
            font-size: 13.5px; font-weight: 600;
            color: var(--muted); text-decoration: none;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }
        .nav-anchor:hover { color: var(--blue); border-bottom-color: var(--blue-soft); }
        .nav-anchor.active { color: var(--blue); border-bottom-color: var(--blue); }
        .nav-anchor i { font-size: 12px; }

        /* ══════════════════════════════════
           SECTIONS COMMUNES
        ══════════════════════════════════ */
        .info-section { padding: 70px 0; }
        .info-section + .info-section { padding-top: 0; }

        .section-header { margin-bottom: 48px; }
        .section-tag {
            display: inline-flex; align-items: center; gap: 7px;
            font-size: 11px; font-weight: 700; letter-spacing: 2px;
            text-transform: uppercase; padding: 5px 14px;
            border-radius: 20px; margin-bottom: 14px;
        }
        .section-header h2 {
            font-family: 'Lora', serif;
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 700; color: var(--dark);
            line-height: 1.25; margin-bottom: 14px;
        }
        .section-header p {
            font-size: 16px; color: var(--muted);
            max-width: 680px; line-height: 1.8;
        }

        /* Diviseur section */
        .section-divider {
            height: 1px; background: linear-gradient(90deg, transparent, var(--border), transparent);
            margin: 0;
        }

        /* ══════════════════════════════════
           1. QU'EST-CE QUE LE CANCER
        ══════════════════════════════════ */
        #comprendre { background: var(--white); }

        .comprendre-layout {
            display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: start;
        }
        .comprendre-text h3 {
            font-family: 'Lora', serif;
            font-size: 1.3rem; font-weight: 700; color: var(--dark);
            margin-bottom: 14px;
        }
        .comprendre-text p {
            font-size: 15px; color: var(--text); line-height: 1.85;
            margin-bottom: 16px;
        }
        .comprendre-text p strong { color: var(--blue); font-weight: 600; }

        .fact-box {
            background: var(--blue-soft);
            border-left: 4px solid var(--blue);
            border-radius: 0 12px 12px 0;
            padding: 18px 22px; margin: 24px 0;
        }
        .fact-box p { font-size: 14px; color: var(--blue-dark); font-weight: 500; line-height: 1.7; margin: 0; }
        .fact-box strong { color: var(--blue-dark); }

        /* Phases cancer */
        .phases-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
        }
        .phase-card {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 12px; padding: 18px;
        }
        .phase-num {
            width: 32px; height: 32px;
            background: var(--blue); color: white;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 800; margin-bottom: 10px;
        }
        .phase-card h4 { font-size: 14px; font-weight: 700; color: var(--dark); margin-bottom: 6px; }
        .phase-card p  { font-size: 13px; color: var(--muted); line-height: 1.65; }

        /* ══════════════════════════════════
           2. TYPES DE CANCER
        ══════════════════════════════════ */
        #types { background: var(--bg); }

        .types-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .type-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 16px; padding: 26px 22px;
            transition: transform 0.25s, box-shadow 0.25s;
            position: relative; overflow: hidden;
        }
        .type-card::before {
            content: ''; position: absolute;
            top: 0; left: 0; right: 0; height: 4px;
        }
        .type-card:hover { transform: translateY(-5px); box-shadow: 0 14px 36px rgba(0,0,0,0.1); }

        .type-card.rose::before   { background: var(--rose); }
        .type-card.blue::before   { background: var(--blue); }
        .type-card.green::before  { background: var(--green); }
        .type-card.orange::before { background: var(--orange); }
        .type-card.teal::before   { background: var(--teal); }
        .type-card.purple::before { background: var(--purple); }

        .type-icon {
            width: 52px; height: 52px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; margin-bottom: 16px;
        }
        .type-card.rose   .type-icon { background: var(--rose-soft);   color: var(--rose); }
        .type-card.blue   .type-icon { background: var(--blue-soft);   color: var(--blue); }
        .type-card.green  .type-icon { background: var(--green-soft);  color: var(--green); }
        .type-card.orange .type-icon { background: var(--orange-soft); color: var(--orange); }
        .type-card.teal   .type-icon { background: var(--teal-soft);   color: var(--teal); }
        .type-card.purple .type-icon { background: var(--purple-soft); color: var(--purple); }

        .type-card h3 { font-size: 16px; font-weight: 700; color: var(--dark); margin-bottom: 8px; }
        .type-card p  { font-size: 13.5px; color: var(--muted); line-height: 1.7; margin-bottom: 14px; }

        .symptoms-list { list-style: none; padding: 0; }
        .symptoms-list li {
            display: flex; align-items: flex-start; gap: 8px;
            font-size: 13px; color: var(--text); margin-bottom: 5px; font-weight: 500;
        }
        .symptoms-list li::before {
            content: ''; width: 6px; height: 6px; border-radius: 50%;
            flex-shrink: 0; margin-top: 6px;
        }
        .type-card.rose   .symptoms-list li::before { background: var(--rose); }
        .type-card.blue   .symptoms-list li::before { background: var(--blue); }
        .type-card.green  .symptoms-list li::before { background: var(--green); }
        .type-card.orange .symptoms-list li::before { background: var(--orange); }
        .type-card.teal   .symptoms-list li::before { background: var(--teal); }
        .type-card.purple .symptoms-list li::before { background: var(--purple); }

        .type-ribbon {
            display: inline-block; font-size: 11px; font-weight: 700;
            padding: 3px 10px; border-radius: 20px; margin-bottom: 14px;
        }
        .type-card.rose   .type-ribbon { background: var(--rose-soft);   color: var(--rose); }
        .type-card.blue   .type-ribbon { background: var(--blue-soft);   color: var(--blue); }
        .type-card.green  .type-ribbon { background: var(--green-soft);  color: var(--green); }
        .type-card.orange .type-ribbon { background: var(--orange-soft); color: var(--orange); }
        .type-card.teal   .type-ribbon { background: var(--teal-soft);   color: var(--teal); }
        .type-card.purple .type-ribbon { background: var(--purple-soft); color: var(--purple); }

        /* ══════════════════════════════════
           3. DÉPISTAGE
        ══════════════════════════════════ */
        #depistage { background: var(--white); }

        .depistage-layout {
            display: grid; grid-template-columns: 1.2fr 1fr; gap: 60px; align-items: start;
        }

        .depistage-steps { }
        .dep-step {
            display: flex; gap: 20px; margin-bottom: 28px; align-items: flex-start;
        }
        .dep-step-num {
            width: 44px; height: 44px; flex-shrink: 0;
            background: var(--blue); color: white;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 16px; font-weight: 800;
            box-shadow: 0 4px 12px rgba(0,51,153,0.25);
        }
        .dep-step-body h4 { font-size: 15px; font-weight: 700; color: var(--dark); margin-bottom: 6px; }
        .dep-step-body p  { font-size: 14px; color: var(--muted); line-height: 1.7; }

        /* Tableau recommandations */
        .reco-table { width: 100%; border-collapse: collapse; }
        .reco-table th {
            background: var(--blue); color: white;
            padding: 12px 16px; font-size: 13px; font-weight: 700;
            text-align: left;
        }
        .reco-table th:first-child { border-radius: 10px 0 0 0; }
        .reco-table th:last-child  { border-radius: 0 10px 0 0; }
        .reco-table td {
            padding: 11px 16px; font-size: 13.5px; color: var(--text);
            border-bottom: 1px solid var(--border); vertical-align: top;
        }
        .reco-table tr:last-child td { border-bottom: none; }
        .reco-table tr:nth-child(even) td { background: var(--bg); }
        .reco-table td strong { color: var(--dark); font-weight: 700; }

        .alert-info {
            background: var(--blue-soft); border-left: 4px solid var(--blue);
            border-radius: 0 10px 10px 0; padding: 16px 20px; margin-top: 24px;
            font-size: 14px; color: var(--blue-dark); font-weight: 500; line-height: 1.7;
        }
        .alert-info i { margin-right: 8px; }

        /* ══════════════════════════════════
           4. TRAITEMENTS
        ══════════════════════════════════ */
        #traitements { background: var(--bg); }

        .trait-grid {
            display: grid; grid-template-columns: repeat(2, 1fr); gap: 22px;
            margin-bottom: 40px;
        }
        .trait-card {
            background: var(--white); border: 1px solid var(--border);
            border-radius: 16px; padding: 28px; display: flex; gap: 20px;
        }
        .trait-icon-wrap {
            width: 56px; height: 56px; flex-shrink: 0; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; font-size: 24px;
        }
        .trait-card:nth-child(1) .trait-icon-wrap { background: #FDE8EF; color: var(--rose); }
        .trait-card:nth-child(2) .trait-icon-wrap { background: var(--blue-soft); color: var(--blue); }
        .trait-card:nth-child(3) .trait-icon-wrap { background: var(--green-soft); color: var(--green); }
        .trait-card:nth-child(4) .trait-icon-wrap { background: var(--teal-soft); color: var(--teal); }
        .trait-card:nth-child(5) .trait-icon-wrap { background: var(--purple-soft); color: var(--purple); }
        .trait-card:nth-child(6) .trait-icon-wrap { background: var(--orange-soft); color: var(--orange); }

        .trait-body h3 { font-size: 16px; font-weight: 700; color: var(--dark); margin-bottom: 8px; }
        .trait-body p  { font-size: 14px; color: var(--muted); line-height: 1.75; }
        .trait-body .trait-note {
            display: inline-block; margin-top: 10px;
            font-size: 12px; font-weight: 600; color: var(--green);
            background: var(--green-soft); padding: 3px 10px; border-radius: 20px;
        }

        /* Timeline effets secondaires */
        .effects-box {
            background: var(--white); border: 1px solid var(--border);
            border-radius: 16px; padding: 30px 32px;
        }
        .effects-box h3 {
            font-family: 'Lora', serif;
            font-size: 1.15rem; font-weight: 700; color: var(--dark); margin-bottom: 22px;
        }
        .effect-list { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .effect-item {
            background: var(--bg); border-radius: 10px; padding: 14px 16px;
            border-left: 3px solid var(--border);
        }
        .effect-item.yellow { border-left-color: #F59E0B; }
        .effect-item.red    { border-left-color: var(--rose); }
        .effect-item.blue   { border-left-color: var(--blue); }
        .effect-item h5 { font-size: 13px; font-weight: 700; color: var(--dark); margin-bottom: 5px; }
        .effect-item p  { font-size: 12.5px; color: var(--muted); line-height: 1.6; }

        /* ══════════════════════════════════
           5. PRÉVENTION
        ══════════════════════════════════ */
        #prevention { background: var(--white); }

        .prevention-banner {
            background: linear-gradient(135deg, #001a66 0%, #003399 60%, #1a56cc 100%);
            border-radius: 20px; padding: 48px 52px;
            display: grid; grid-template-columns: 1fr 1fr; gap: 50px;
            align-items: center; margin-bottom: 48px; position: relative; overflow: hidden;
        }
        .prevention-banner::after {
            content: '';
            position: absolute; right: -60px; top: -60px;
            width: 300px; height: 300px; border-radius: 50%;
            background: rgba(255,255,255,0.04);
        }
        .prev-banner-text h3 {
            font-family: 'Lora', serif;
            font-size: 1.7rem; font-weight: 700; color: white; margin-bottom: 14px;
        }
        .prev-banner-text p { font-size: 15px; color: #C5D5F5; line-height: 1.8; }

        .prev-stats { display: flex; flex-direction: column; gap: 16px; }
        .prev-stat-row {
            background: rgba(255,255,255,0.1); border-radius: 12px;
            padding: 14px 18px; display: flex; align-items: center; gap: 16px;
        }
        .prev-stat-pct {
            font-family: 'Lora', serif;
            font-size: 1.8rem; font-weight: 700; color: #93B4FF; flex-shrink: 0; min-width: 60px;
        }
        .prev-stat-desc { font-size: 13.5px; color: #C5D5F5; line-height: 1.6; font-weight: 500; }

        /* Piliers prévention */
        .pillars-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px;
        }
        .pillar {
            background: var(--bg); border: 1px solid var(--border);
            border-radius: 14px; padding: 24px 18px; text-align: center;
        }
        .pillar-icon {
            width: 56px; height: 56px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; margin: 0 auto 14px;
        }
        .pillar:nth-child(1) .pillar-icon { background: #FDE8EF; color: var(--rose); }
        .pillar:nth-child(2) .pillar-icon { background: var(--green-soft); color: var(--green); }
        .pillar:nth-child(3) .pillar-icon { background: var(--blue-soft); color: var(--blue); }
        .pillar:nth-child(4) .pillar-icon { background: var(--orange-soft); color: var(--orange); }
        .pillar:nth-child(5) .pillar-icon { background: var(--teal-soft); color: var(--teal); }
        .pillar:nth-child(6) .pillar-icon { background: var(--purple-soft); color: var(--purple); }
        .pillar:nth-child(7) .pillar-icon { background: #FDE8EF; color: var(--rose); }
        .pillar:nth-child(8) .pillar-icon { background: var(--blue-soft); color: var(--blue); }
        .pillar h4 { font-size: 14px; font-weight: 700; color: var(--dark); margin-bottom: 8px; }
        .pillar p  { font-size: 13px; color: var(--muted); line-height: 1.65; }

        /* ══════════════════════════════════
           6. MYTHES & RÉALITÉS
        ══════════════════════════════════ */
        #mythes { background: var(--bg); }

        .mythes-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 18px;
        }
        .mythe-card {
            background: var(--white); border: 1px solid var(--border);
            border-radius: 14px; overflow: hidden;
        }
        .mythe-header {
            padding: 14px 20px;
            display: flex; align-items: center; gap: 10px;
        }
        .mythe-header.faux  { background: #FEE2E2; }
        .mythe-header.vrai  { background: #D1FAE5; }
        .mythe-header i { font-size: 16px; }
        .mythe-header.faux i { color: #DC2626; }
        .mythe-header.vrai i { color: #059669; }
        .mythe-header span { font-size: 12px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
        .mythe-header.faux span { color: #DC2626; }
        .mythe-header.vrai span { color: #059669; }
        .mythe-body { padding: 16px 20px; }
        .mythe-body h4 { font-size: 14.5px; font-weight: 700; color: var(--dark); margin-bottom: 8px; line-height: 1.4; }
        .mythe-body p  { font-size: 13.5px; color: var(--muted); line-height: 1.75; }

        /* ══════════════════════════════════
           7. VIVRE AVEC
        ══════════════════════════════════ */
        #vivre { background: var(--white); }

        .vivre-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px;
        }
        .vivre-card {
            border: 1px solid var(--border); border-radius: 16px; padding: 28px 24px;
            background: var(--white);
        }
        .vivre-card-top {
            display: flex; align-items: center; gap: 14px; margin-bottom: 16px;
        }
        .vivre-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; font-size: 20px;
        }
        .vivre-card:nth-child(1) .vivre-icon { background: var(--blue-soft); color: var(--blue); }
        .vivre-card:nth-child(2) .vivre-icon { background: var(--rose-soft); color: var(--rose); }
        .vivre-card:nth-child(3) .vivre-icon { background: var(--green-soft); color: var(--green); }
        .vivre-card:nth-child(4) .vivre-icon { background: var(--teal-soft); color: var(--teal); }
        .vivre-card:nth-child(5) .vivre-icon { background: var(--orange-soft); color: var(--orange); }
        .vivre-card:nth-child(6) .vivre-icon { background: var(--purple-soft); color: var(--purple); }
        .vivre-card-top h3 { font-size: 15px; font-weight: 700; color: var(--dark); }
        .vivre-card ul { list-style: none; padding: 0; }
        .vivre-card ul li {
            font-size: 13.5px; color: var(--text); padding: 7px 0;
            border-bottom: 1px solid var(--bg); display: flex; align-items: flex-start; gap: 8px;
            line-height: 1.55;
        }
        .vivre-card ul li:last-child { border-bottom: none; }
        .vivre-card ul li i { font-size: 11px; color: var(--blue); margin-top: 4px; flex-shrink: 0; }

        /* ══════════════════════════════════
           FAQ
        ══════════════════════════════════ */
        #faq { background: var(--bg); }

        .faq-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .faq-item {
            background: var(--white); border: 1px solid var(--border);
            border-radius: 12px; overflow: hidden;
        }
        .faq-q {
            padding: 18px 22px; cursor: pointer;
            display: flex; justify-content: space-between; align-items: center; gap: 14px;
            font-size: 14.5px; font-weight: 700; color: var(--dark);
            user-select: none; transition: background 0.2s;
        }
        .faq-q:hover { background: var(--bg); }
        .faq-q i { color: var(--blue); font-size: 13px; transition: transform 0.25s; flex-shrink: 0; }
        .faq-a {
            max-height: 0; overflow: hidden; transition: max-height 0.35s ease, padding 0.25s;
            padding: 0 22px; font-size: 14px; color: var(--muted); line-height: 1.8;
        }
        .faq-item.open .faq-a { max-height: 400px; padding: 0 22px 18px; }
        .faq-item.open .faq-q i { transform: rotate(180deg); }
        .faq-item.open .faq-q { background: var(--blue-soft); color: var(--blue); }
        .faq-item.open .faq-q i { color: var(--blue); }

        /* ══════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════ */
        @media (max-width: 1000px) {
            .types-grid         { grid-template-columns: repeat(2, 1fr); }
            .pillars-grid       { grid-template-columns: repeat(2, 1fr); }
            .prevention-banner  { grid-template-columns: 1fr; gap: 30px; padding: 36px; }
        }
        @media (max-width: 768px) {
            .hero-inner         { grid-template-columns: 1fr; }
            .hero-visual        { display: none; }
            .comprendre-layout  { grid-template-columns: 1fr; }
            .depistage-layout   { grid-template-columns: 1fr; }
            .types-grid         { grid-template-columns: 1fr; }
            .trait-grid         { grid-template-columns: 1fr; }
            .vivre-grid         { grid-template-columns: 1fr; }
            .mythes-grid        { grid-template-columns: 1fr; }
            .faq-grid           { grid-template-columns: 1fr; }
            .effect-list        { grid-template-columns: 1fr; }
            .phases-grid        { grid-template-columns: 1fr; }
            .pillars-grid       { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <!-- ══ HERO ══ -->
    <div class="hero">
        <div class="container">
            <div class="hero-inner">
                <div class="hero-text">
                    <div class="hero-eyebrow"><i class="fas fa-book-medical"></i> Éducation & Santé</div>
                    <h1>S'informer et <em>comprendre</em> le cancer</h1>
                    <p class="hero-desc">
                        Des informations claires, fiables et accessibles pour mieux comprendre la maladie,
                        reconnaître les signes, connaître les traitements et agir en prévention.
                    </p>
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <div class="hero-stat-num">100+</div>
                            <div class="hero-stat-label">Types de cancer</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-num">40%</div>
                            <div class="hero-stat-label">Cas évitables</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-num">9/10</div>
                            <div class="hero-stat-label">Guérisons si détecté tôt</div>
                        </div>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="hero-card">
                        <div class="hero-card-icon">📋</div>
                        <h4>Cette page couvre</h4>
                        <p>Définition · Types · Dépistage · Traitements · Prévention · Mythes · Vivre avec</p>
                    </div>
                    <div class="hero-card">
                        <div class="hero-card-icon">🎗️</div>
                        <h4>Cancer du sein</h4>
                        <p>Le plus fréquent chez la femme. Dépistable et traitable si détecté tôt.</p>
                    </div>
                    <div class="hero-card">
                        <div class="hero-card-icon">🔬</div>
                        <h4>Traitement</h4>
                        <p>Chirurgie, chimio, radiothérapie, immunothérapie et thérapies ciblées.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-wave">
            <svg viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path fill="#F7F8FC" d="M0,60 C480,0 960,0 1440,60 L1440,60 L0,60 Z"/>
            </svg>
        </div>
    </div>

    <!-- ══ NAV ANCRES ══ -->
    <nav class="sticky-nav">
        <div class="container">
            <div class="sticky-nav-inner">
                <a href="#comprendre" class="nav-anchor"><i class="fas fa-dna"></i> Comprendre</a>
                <a href="#types"      class="nav-anchor"><i class="fas fa-list-ul"></i> Types</a>
                <a href="#depistage"  class="nav-anchor"><i class="fas fa-search"></i> Dépistage</a>
                <a href="#traitements"class="nav-anchor"><i class="fas fa-pills"></i> Traitements</a>
                <a href="#prevention" class="nav-anchor"><i class="fas fa-shield-alt"></i> Prévention</a>
                <a href="#mythes"     class="nav-anchor"><i class="fas fa-times-circle"></i> Mythes</a>
                <a href="#vivre"      class="nav-anchor"><i class="fas fa-heart"></i> Vivre avec</a>
                <a href="#faq"        class="nav-anchor"><i class="fas fa-question-circle"></i> FAQ</a>
            </div>
        </div>
    </nav>

    <!-- ══ 1. COMPRENDRE ══ -->
    <section class="info-section" id="comprendre">
        <div class="container">
            <div class="section-header">
                <span class="section-tag" style="background:var(--blue-soft);color:var(--blue);">
                    <i class="fas fa-dna"></i> Fondamentaux
                </span>
                <h2>Qu'est-ce que le cancer ?</h2>
                <p>Comprendre la nature de la maladie est la première étape pour mieux y faire face.</p>
            </div>
            <div class="comprendre-layout">
                <div class="comprendre-text">
                    <h3>Une maladie des cellules</h3>
                    <p>
                        Le cancer est une maladie qui survient quand des cellules du corps se multiplient
                        de façon <strong>anormale et incontrôlée</strong>. Normalement, nos cellules se divisent
                        de manière ordonnée et meurent quand elles sont usées. Dans le cancer, ce mécanisme
                        est perturbé : les cellules mutées ne meurent pas et continuent à se diviser.
                    </p>
                    <p>
                        Cette prolifération forme une <strong>tumeur</strong> (masse de tissu). Certaines tumeurs
                        sont bénignes — elles ne se propagent pas. Les tumeurs malignes, elles,
                        peuvent envahir les tissus voisins et se propager à d'autres organes via
                        le sang ou la lymphe : c'est ce qu'on appelle les <strong>métastases</strong>.
                    </p>
                    <div class="fact-box">
                        <p><strong>À retenir :</strong> le cancer n'est pas une seule maladie. C'est un groupe
                        de plus de 100 maladies différentes, qui peuvent toucher presque n'importe quel organe
                        du corps humain.</p>
                    </div>
                    <h3 style="margin-top:28px;">Les causes principales</h3>
                    <p>
                        Le cancer résulte d'une combinaison de facteurs génétiques et environnementaux.
                        Les mutations cellulaires peuvent être causées par : le tabac, l'alcool,
                        l'exposition aux rayonnements UV, certains virus (HPV, hépatite B/C),
                        la pollution, une alimentation déséquilibrée, et des prédispositions héréditaires.
                    </p>
                </div>
                <div>
                    <h3 style="font-family:'Lora',serif;font-size:1.1rem;font-weight:700;color:var(--dark);margin-bottom:18px;">
                        Les stades de la maladie
                    </h3>
                    <div class="phases-grid">
                        <div class="phase-card">
                            <div class="phase-num">I</div>
                            <h4>Stade localisé</h4>
                            <p>La tumeur est petite et confinée à l'organe d'origine. Taux de guérison très élevé.</p>
                        </div>
                        <div class="phase-card">
                            <div class="phase-num">II</div>
                            <h4>Extension locale</h4>
                            <p>La tumeur a grossi ou commencé à atteindre les ganglions lymphatiques proches.</p>
                        </div>
                        <div class="phase-card">
                            <div class="phase-num">III</div>
                            <h4>Extension régionale</h4>
                            <p>Le cancer s'est propagé aux tissus et ganglions voisins. Traitement plus intensif nécessaire.</p>
                        </div>
                        <div class="phase-card">
                            <div class="phase-num">IV</div>
                            <h4>Métastases</h4>
                            <p>Le cancer a atteint d'autres organes distants. Traitement axé sur le contrôle de la maladie.</p>
                        </div>
                    </div>
                    <div class="alert-info" style="margin-top:18px;">
                        <i class="fas fa-lightbulb"></i>
                        <strong>Plus un cancer est détecté tôt, plus les chances de guérison sont élevées.</strong>
                        Au stade I, beaucoup de cancers se guérissent dans 9 cas sur 10.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- ══ 2. TYPES ══ -->
    <section class="info-section" id="types">
        <div class="container">
            <div class="section-header">
                <span class="section-tag" style="background:var(--rose-soft);color:var(--rose);">
                    <i class="fas fa-list-ul"></i> Catalogue
                </span>
                <h2>Les principaux types de cancer</h2>
                <p>Chaque type de cancer a ses propres caractéristiques, symptômes et protocoles de traitement.</p>
            </div>
            <div class="types-grid">

                <div class="type-card rose">
                    <div class="type-icon"><i class="fas fa-ribbon"></i></div>
                    <span class="type-ribbon">Féminin</span>
                    <h3>Cancer du sein</h3>
                    <p>Le cancer le plus fréquent chez la femme dans le monde et en Haïti. Souvent lié à des facteurs hormonaux, génétiques et environnementaux.</p>
                    <ul class="symptoms-list">
                        <li>Bosse ou épaississement dans le sein</li>
                        <li>Modification de la forme ou taille du sein</li>
                        <li>Écoulement du mamelon inhabituel</li>
                        <li>Peau d'orange ou rougeur</li>
                    </ul>
                </div>

                <div class="type-card blue">
                    <div class="type-icon"><i class="fas fa-male"></i></div>
                    <span class="type-ribbon">Masculin</span>
                    <h3>Cancer de la prostate</h3>
                    <p>Le cancer masculin le plus courant après 50 ans. Évolution souvent lente, bon pronostic si détecté tôt.</p>
                    <ul class="symptoms-list">
                        <li>Difficultés ou douleurs à uriner</li>
                        <li>Besoin fréquent d'uriner la nuit</li>
                        <li>Sang dans les urines ou le sperme</li>
                        <li>Douleurs dans le bas du dos ou les hanches</li>
                    </ul>
                </div>

                <div class="type-card green">
                    <div class="type-icon"><i class="fas fa-lungs"></i></div>
                    <span class="type-ribbon">Fréquent</span>
                    <h3>Cancer du poumon</h3>
                    <p>Premier cancer meurtrier dans le monde. Le tabac est responsable de 85% des cas. Souvent diagnostiqué à un stade avancé.</p>
                    <ul class="symptoms-list">
                        <li>Toux persistante ou sang dans les crachats</li>
                        <li>Essoufflement progressif</li>
                        <li>Douleur thoracique persistante</li>
                        <li>Perte de poids inexpliquée</li>
                    </ul>
                </div>

                <div class="type-card orange">
                    <div class="type-icon"><i class="fas fa-procedures"></i></div>
                    <span class="type-ribbon">Digestif</span>
                    <h3>Cancer colorectal</h3>
                    <p>Troisième cancer le plus fréquent. Fortement lié à l'alimentation et aux polypes. Très curable si détecté tôt.</p>
                    <ul class="symptoms-list">
                        <li>Sang dans les selles (rouge ou noir)</li>
                        <li>Changement des habitudes intestinales</li>
                        <li>Douleurs abdominales persistantes</li>
                        <li>Fatigue et anémie inexpliquées</li>
                    </ul>
                </div>

                <div class="type-card teal">
                    <div class="type-icon"><i class="fas fa-female"></i></div>
                    <span class="type-ribbon">Féminin</span>
                    <h3>Cancer du col de l'utérus</h3>
                    <p>Causé à 99% par le virus HPV. L'un des rares cancers entièrement évitables grâce au vaccin et au dépistage.</p>
                    <ul class="symptoms-list">
                        <li>Saignements entre les règles</li>
                        <li>Saignements après les rapports sexuels</li>
                        <li>Pertes vaginales inhabituelles</li>
                        <li>Douleurs pelviennes persistantes</li>
                    </ul>
                </div>

                <div class="type-card purple">
                    <div class="type-icon"><i class="fas fa-child"></i></div>
                    <span class="type-ribbon">Pédiatrique</span>
                    <h3>Cancers pédiatriques</h3>
                    <p>Les cancers chez l'enfant sont différents de ceux de l'adulte. La leucémie est la plus fréquente. Taux de guérison en hausse.</p>
                    <ul class="symptoms-list">
                        <li>Pâleur, fatigue intense, fièvres fréquentes</li>
                        <li>Ganglions enflés persistants</li>
                        <li>Douleurs osseuses ou abdominales</li>
                        <li>Perte de poids rapide et inexpliquée</li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- ══ 3. DÉPISTAGE ══ -->
    <section class="info-section" id="depistage">
        <div class="container">
            <div class="section-header">
                <span class="section-tag" style="background:var(--green-soft);color:var(--green);">
                    <i class="fas fa-search"></i> Dépistage
                </span>
                <h2>Se faire dépister : pourquoi et comment ?</h2>
                <p>Le dépistage permet de détecter un cancer avant même l'apparition de symptômes, quand il est encore traitable.</p>
            </div>
            <div class="depistage-layout">
                <div>
                    <div class="dep-step">
                        <div class="dep-step-num">1</div>
                        <div class="dep-step-body">
                            <h4>Consulter un médecin régulièrement</h4>
                            <p>Un suivi médical annuel permet de détecter des anomalies tôt. Parlez à votre médecin de votre historique familial et de vos facteurs de risque.</p>
                        </div>
                    </div>
                    <div class="dep-step">
                        <div class="dep-step-num">2</div>
                        <div class="dep-step-body">
                            <h4>Connaître les examens recommandés selon votre âge</h4>
                            <p>Certains examens sont recommandés à partir d'un certain âge ou en présence de facteurs de risque. Ne pas attendre les symptômes.</p>
                        </div>
                    </div>
                    <div class="dep-step">
                        <div class="dep-step-num">3</div>
                        <div class="dep-step-body">
                            <h4>Pratiquer l'auto-examen</h4>
                            <p>L'auto-palpation mensuelle des seins (femmes) ou des testicules (hommes) peut aider à détecter des anomalies. En cas de doute, consulter sans tarder.</p>
                        </div>
                    </div>
                    <div class="dep-step">
                        <div class="dep-step-num">4</div>
                        <div class="dep-step-body">
                            <h4>Ne pas ignorer les signes d'alerte</h4>
                            <p>Tout symptôme inhabituel qui persiste plus de 2 à 3 semaines doit être signalé à un médecin. L'attente peut coûter cher.</p>
                        </div>
                    </div>
                    <div class="alert-info">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Signes d'alerte généraux :</strong> perte de poids inexpliquée, fatigue persistante, fièvre récurrente, douleur chronique, saignement anormal, masse palpable. Consultez si ces signes durent plus de 3 semaines.
                    </div>
                </div>
                <div>
                    <h3 style="font-family:'Lora',serif;font-size:1.1rem;font-weight:700;color:var(--dark);margin-bottom:18px;">
                        Examens recommandés par type
                    </h3>
                    <table class="reco-table">
                        <thead>
                            <tr>
                                <th>Cancer</th>
                                <th>Examen</th>
                                <th>Dès quand</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Sein</strong></td>
                                <td>Mammographie</td>
                                <td>40–50 ans</td>
                            </tr>
                            <tr>
                                <td><strong>Col utérus</strong></td>
                                <td>Frottis (Pap test)</td>
                                <td>Dès 21 ans</td>
                            </tr>
                            <tr>
                                <td><strong>Colorectal</strong></td>
                                <td>Coloscopie / test de sang</td>
                                <td>45–50 ans</td>
                            </tr>
                            <tr>
                                <td><strong>Prostate</strong></td>
                                <td>PSA sanguin</td>
                                <td>50 ans (45 si risque)</td>
                            </tr>
                            <tr>
                                <td><strong>Poumon</strong></td>
                                <td>Scanner low-dose</td>
                                <td>50 ans + tabagisme</td>
                            </tr>
                            <tr>
                                <td><strong>Peau</strong></td>
                                <td>Examen dermatologique</td>
                                <td>Annuel si exposé</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="alert-info" style="margin-top:18px;">
                        <i class="fas fa-map-marker-alt"></i>
                        <strong>En Haïti :</strong> le GSCC organise des séances de dépistage gratuit en mobile et en clinique. Contactez-nous pour connaître les prochaines dates près de chez vous.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- ══ 4. TRAITEMENTS ══ -->
    <section class="info-section" id="traitements">
        <div class="container">
            <div class="section-header">
                <span class="section-tag" style="background:var(--teal-soft);color:var(--teal);">
                    <i class="fas fa-pills"></i> Traitements
                </span>
                <h2>Les options de traitement</h2>
                <p>Les traitements contre le cancer ont considérablement évolué. Plusieurs options peuvent être combinées selon le type et le stade.</p>
            </div>

            <div class="trait-grid">
                <div class="trait-card">
                    <div class="trait-icon-wrap"><i class="fas fa-cut"></i></div>
                    <div class="trait-body">
                        <h3>Chirurgie</h3>
                        <p>Ablation de la tumeur et des tissus environnants. Traitement de référence pour de nombreux cancers solides localisés. Peut être curative ou palliative.</p>
                        <span class="trait-note">✓ Très efficace aux stades I et II</span>
                    </div>
                </div>
                <div class="trait-card">
                    <div class="trait-icon-wrap"><i class="fas fa-syringe"></i></div>
                    <div class="trait-body">
                        <h3>Chimiothérapie</h3>
                        <p>Utilisation de médicaments cytotoxiques pour détruire les cellules cancéreuses. Administrée par perfusion ou voie orale, en cycles. Efficace sur les cancers disséminés.</p>
                        <span class="trait-note">✓ Traitement systémique</span>
                    </div>
                </div>
                <div class="trait-card">
                    <div class="trait-icon-wrap"><i class="fas fa-radiation"></i></div>
                    <div class="trait-body">
                        <h3>Radiothérapie</h3>
                        <p>Utilisation de rayonnements ionisants pour détruire les cellules tumorales. Peut être utilisée seule ou en combinaison avec la chirurgie et la chimio.</p>
                        <span class="trait-note">✓ Ciblée et précise</span>
                    </div>
                </div>
                <div class="trait-card">
                    <div class="trait-icon-wrap"><i class="fas fa-shield-virus"></i></div>
                    <div class="trait-body">
                        <h3>Immunothérapie</h3>
                        <p>Stimulation du système immunitaire pour qu'il reconnaisse et détruise les cellules cancéreuses. Révolution thérapeutique des 15 dernières années.</p>
                        <span class="trait-note">✓ Résultats durables possibles</span>
                    </div>
                </div>
                <div class="trait-card">
                    <div class="trait-icon-wrap"><i class="fas fa-dna"></i></div>
                    <div class="trait-body">
                        <h3>Thérapies ciblées</h3>
                        <p>Médicaments qui ciblent des protéines ou gènes spécifiques des cellules cancéreuses. Moins d'effets secondaires que la chimio classique.</p>
                        <span class="trait-note">✓ Médecine de précision</span>
                    </div>
                </div>
                <div class="trait-card">
                    <div class="trait-icon-wrap"><i class="fas fa-heartbeat"></i></div>
                    <div class="trait-body">
                        <h3>Soins palliatifs</h3>
                        <p>Approche globale visant à améliorer la qualité de vie du patient et de sa famille. Gestion de la douleur, soutien psychologique, accompagnement.</p>
                        <span class="trait-note">✓ Essentiel à tous les stades</span>
                    </div>
                </div>
            </div>

            <div class="effects-box">
                <h3>Effets secondaires courants et comment les gérer</h3>
                <div class="effect-list">
                    <div class="effect-item yellow">
                        <h5>Fatigue intense</h5>
                        <p>Très courante. Repos adapté, activité physique légère et soutien nutritionnel aident à la réduire.</p>
                    </div>
                    <div class="effect-item red">
                        <h5>Nausées / vomissements</h5>
                        <p>Liés à la chimio. Des médicaments antiémétiques modernes permettent de les contrôler efficacement.</p>
                    </div>
                    <div class="effect-item blue">
                        <h5>Perte de cheveux</h5>
                        <p>Temporaire dans la plupart des cas. Les cheveux repoussent après la fin du traitement.</p>
                    </div>
                    <div class="effect-item yellow">
                        <h5>Immunodépression</h5>
                        <p>Risque accru d'infections. Hygiène renforcée, suivi médical régulier et vaccinations indiqués.</p>
                    </div>
                    <div class="effect-item red">
                        <h5>Douleurs</h5>
                        <p>La prise en charge de la douleur est une priorité. Plusieurs options médicamenteuses et non médicamenteuses existent.</p>
                    </div>
                    <div class="effect-item blue">
                        <h5>Impact psychologique</h5>
                        <p>Anxiété, dépression fréquentes. Un soutien psychologique est recommandé dès le diagnostic.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- ══ 5. PRÉVENTION ══ -->
    <section class="info-section" id="prevention">
        <div class="container">
            <div class="section-header">
                <span class="section-tag" style="background:var(--green-soft);color:var(--green);">
                    <i class="fas fa-shield-alt"></i> Prévention
                </span>
                <h2>Prévenir le cancer : ce que vous pouvez faire</h2>
                <p>Si certains facteurs de risque sont inévitables, d'autres sont directement liés à nos habitudes de vie.</p>
            </div>

            <div class="prevention-banner">
                <div class="prev-banner-text">
                    <h3>40 % des cancers sont évitables</h3>
                    <p>Selon l'Organisation Mondiale de la Santé, environ 40% des cancers pourraient être prévenus en modifiant certains comportements et conditions d'environnement. Agir tôt fait une vraie différence.</p>
                </div>
                <div class="prev-stats">
                    <div class="prev-stat-row">
                        <div class="prev-stat-pct">30%</div>
                        <div class="prev-stat-desc">des cancers liés au tabac — arrêter de fumer est le geste préventif le plus efficace</div>
                    </div>
                    <div class="prev-stat-row">
                        <div class="prev-stat-pct">20%</div>
                        <div class="prev-stat-desc">des cancers liés à une alimentation déséquilibrée et au manque d'activité physique</div>
                    </div>
                    <div class="prev-stat-row">
                        <div class="prev-stat-pct">15%</div>
                        <div class="prev-stat-desc">des cancers causés par des infections évitables (HPV, hépatite B) via la vaccination</div>
                    </div>
                </div>
            </div>

            <div class="pillars-grid">
                <div class="pillar">
                    <div class="pillar-icon"><i class="fas fa-smoking-ban"></i></div>
                    <h4>Ne pas fumer</h4>
                    <p>Le tabac est la première cause évitable de cancer. Poumon, gorge, bouche, vessie... l'arrêt réduit le risque dès les premières semaines.</p>
                </div>
                <div class="pillar">
                    <div class="pillar-icon"><i class="fas fa-carrot"></i></div>
                    <h4>Alimentation saine</h4>
                    <p>Plus de légumes, fruits, fibres. Moins de viandes transformées, sucre et graisses saturées. Une alimentation variée protège.</p>
                </div>
                <div class="pillar">
                    <div class="pillar-icon"><i class="fas fa-running"></i></div>
                    <h4>Activité physique</h4>
                    <p>30 minutes d'exercice modéré par jour réduisent le risque de cancers du sein, colorectal et de l'endomètre.</p>
                </div>
                <div class="pillar">
                    <div class="pillar-icon"><i class="fas fa-wine-bottle"></i></div>
                    <h4>Limiter l'alcool</h4>
                    <p>L'alcool augmente le risque de cancers du foie, sein, colorectal, gorge. Moins on en consomme, mieux c'est.</p>
                </div>
                <div class="pillar">
                    <div class="pillar-icon"><i class="fas fa-sun"></i></div>
                    <h4>Protéger sa peau</h4>
                    <p>Crème solaire, vêtements couvrants, éviter les heures de forte chaleur. Le mélanome est évitable dans la grande majorité des cas.</p>
                </div>
                <div class="pillar">
                    <div class="pillar-icon"><i class="fas fa-syringe"></i></div>
                    <h4>Se vacciner</h4>
                    <p>Le vaccin HPV protège contre le cancer du col de l'utérus. Le vaccin hépatite B prévient le cancer du foie.</p>
                </div>
                <div class="pillar">
                    <div class="pillar-icon"><i class="fas fa-weight"></i></div>
                    <h4>Maintenir son poids</h4>
                    <p>L'obésité est un facteur de risque pour au moins 13 types de cancer. Un IMC sain est protecteur.</p>
                </div>
                <div class="pillar">
                    <div class="pillar-icon"><i class="fas fa-stethoscope"></i></div>
                    <h4>Suivi médical</h4>
                    <p>Des consultations régulières permettent de suivre l'évolution de votre santé et de détecter des anomalies précocement.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- ══ 6. MYTHES ══ -->
    <section class="info-section" id="mythes">
        <div class="container">
            <div class="section-header">
                <span class="section-tag" style="background:#FEE2E2;color:#DC2626;">
                    <i class="fas fa-times-circle"></i> Vrai / Faux
                </span>
                <h2>Mythes et réalités sur le cancer</h2>
                <p>Les idées reçues sur le cancer peuvent retarder le diagnostic et nuire à la prise en charge. Faisons le point.</p>
            </div>
            <div class="mythes-grid">
                <div class="mythe-card">
                    <div class="mythe-header faux"><i class="fas fa-times"></i><span>Mythe</span></div>
                    <div class="mythe-body">
                        <h4>«&nbsp;Le cancer est contagieux&nbsp;»</h4>
                        <p>Le cancer ne se transmet pas d'une personne à une autre. On ne peut pas attraper un cancer en touchant, embrassant ou étant proche d'une personne malade. Certains virus oncogènes (HPV, hépatite) se transmettent, mais pas le cancer lui-même.</p>
                    </div>
                </div>
                <div class="mythe-card">
                    <div class="mythe-header vrai"><i class="fas fa-check"></i><span>Réalité</span></div>
                    <div class="mythe-body">
                        <h4>«&nbsp;Le cancer peut toucher tout le monde, même les jeunes&nbsp;»</h4>
                        <p>Si le risque augmente avec l'âge, certains cancers touchent particulièrement les jeunes : leucémies, cancers testiculaires, cancer du col de l'utérus. Il n'y a pas d'âge minimum pour consulter en cas de symptôme persistant.</p>
                    </div>
                </div>
                <div class="mythe-card">
                    <div class="mythe-header faux"><i class="fas fa-times"></i><span>Mythe</span></div>
                    <div class="mythe-body">
                        <h4>«&nbsp;Un diagnostic de cancer est une sentence de mort&nbsp;»</h4>
                        <p>Faux. Grâce aux progrès de la médecine, de nombreux cancers sont aujourd'hui guérissables, surtout s'ils sont détectés tôt. Le taux de survie à 5 ans du cancer du sein dépasse 90% dans les pays avec accès aux soins. L'espoir est réel.</p>
                    </div>
                </div>
                <div class="mythe-card">
                    <div class="mythe-header faux"><i class="fas fa-times"></i><span>Mythe</span></div>
                    <div class="mythe-body">
                        <h4>«&nbsp;Les médicaments du cancer font plus de mal que de bien&nbsp;»</h4>
                        <p>La chimiothérapie a des effets secondaires, mais ils sont gérables et temporaires. Ne pas se traiter est bien plus dangereux. Les médecins équilibrent soigneusement bénéfices et risques pour chaque patient.</p>
                    </div>
                </div>
                <div class="mythe-card">
                    <div class="mythe-header faux"><i class="fas fa-times"></i><span>Mythe</span></div>
                    <div class="mythe-body">
                        <h4>«&nbsp;Si personne dans ma famille n'a eu le cancer, je suis protégé&nbsp;»</h4>
                        <p>Seulement 5 à 10% des cancers sont héréditaires. La grande majorité surviennent sans antécédents familiaux. L'exposition environnementale, le tabac, l'alimentation et d'autres facteurs jouent un rôle tout aussi important.</p>
                    </div>
                </div>
                <div class="mythe-card">
                    <div class="mythe-header vrai"><i class="fas fa-check"></i><span>Réalité</span></div>
                    <div class="mythe-body">
                        <h4>«&nbsp;Le stress peut favoriser l'apparition du cancer&nbsp;»</h4>
                        <p>Un lien indirect existe : le stress chronique affaiblit le système immunitaire et peut pousser vers des comportements à risque (tabac, alcool, mauvaise alimentation). Bien gérer son stress fait partie de la prévention globale.</p>
                    </div>
                </div>
                <div class="mythe-card">
                    <div class="mythe-header faux"><i class="fas fa-times"></i><span>Mythe</span></div>
                    <div class="mythe-body">
                        <h4>«&nbsp;Les plantes ou remèdes naturels guérissent le cancer&nbsp;»</h4>
                        <p>Aucun remède traditionnel n'a fait la preuve scientifique de sa capacité à guérir un cancer. Retarder un traitement médical en favorisant des remèdes non prouvés peut être fatal. Les médecines complémentaires peuvent accompagner, mais pas remplacer.</p>
                    </div>
                </div>
                <div class="mythe-card">
                    <div class="mythe-header vrai"><i class="fas fa-check"></i><span>Réalité</span></div>
                    <div class="mythe-body">
                        <h4>«&nbsp;L'alimentation joue un rôle dans la prévention du cancer&nbsp;»</h4>
                        <p>Vrai. Une alimentation riche en fruits, légumes, fibres et pauvre en viandes transformées, sucre et alcool réduit le risque de plusieurs cancers. La nutrition est une composante clé de la prévention.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- ══ 7. VIVRE AVEC ══ -->
    <section class="info-section" id="vivre">
        <div class="container">
            <div class="section-header">
                <span class="section-tag" style="background:var(--rose-soft);color:var(--rose);">
                    <i class="fas fa-heart"></i> Accompagnement
                </span>
                <h2>Vivre avec le cancer</h2>
                <p>Le diagnostic change une vie, mais de nombreuses ressources et stratégies existent pour traverser cette épreuve avec force et soutien.</p>
            </div>
            <div class="vivre-grid">
                <div class="vivre-card">
                    <div class="vivre-card-top">
                        <div class="vivre-icon"><i class="fas fa-brain"></i></div>
                        <h3>Santé mentale</h3>
                    </div>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Consulter un psychologue ou thérapeute spécialisé en oncologie</li>
                        <li><i class="fas fa-check-circle"></i> Rejoindre un groupe de parole avec d'autres patients</li>
                        <li><i class="fas fa-check-circle"></i> Pratiquer la méditation ou la relaxation</li>
                        <li><i class="fas fa-check-circle"></i> Exprimer ses émotions sans se juger</li>
                    </ul>
                </div>
                <div class="vivre-card">
                    <div class="vivre-card-top">
                        <div class="vivre-icon"><i class="fas fa-users"></i></div>
                        <h3>Soutien familial</h3>
                    </div>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Parler ouvertement avec ses proches de ses besoins</li>
                        <li><i class="fas fa-check-circle"></i> Accepter l'aide sans culpabilité</li>
                        <li><i class="fas fa-check-circle"></i> Impliquer la famille dans le parcours de soin</li>
                        <li><i class="fas fa-check-circle"></i> Protéger les enfants en leur expliquant adapté à leur âge</li>
                    </ul>
                </div>
                <div class="vivre-card">
                    <div class="vivre-card-top">
                        <div class="vivre-icon"><i class="fas fa-utensils"></i></div>
                        <h3>Nutrition pendant le traitement</h3>
                    </div>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Maintenir un apport calorique suffisant</li>
                        <li><i class="fas fa-check-circle"></i> Privilégier les aliments riches en protéines</li>
                        <li><i class="fas fa-check-circle"></i> S'hydrater abondamment</li>
                        <li><i class="fas fa-check-circle"></i> Consulter un diététicien spécialisé en oncologie</li>
                    </ul>
                </div>
                <div class="vivre-card">
                    <div class="vivre-card-top">
                        <div class="vivre-icon"><i class="fas fa-walking"></i></div>
                        <h3>Activité physique adaptée</h3>
                    </div>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Marche quotidienne même courte — 15 à 20 minutes</li>
                        <li><i class="fas fa-check-circle"></i> Yoga doux ou étirements pour réduire la fatigue</li>
                        <li><i class="fas fa-check-circle"></i> Adapter l'intensité à son état du jour</li>
                        <li><i class="fas fa-check-circle"></i> En parler avec l'équipe soignante avant de commencer</li>
                    </ul>
                </div>
                <div class="vivre-card">
                    <div class="vivre-card-top">
                        <div class="vivre-icon"><i class="fas fa-briefcase"></i></div>
                        <h3>Vie professionnelle</h3>
                    </div>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Informer son employeur selon le degré de confiance</li>
                        <li><i class="fas fa-check-circle"></i> Connaître ses droits à un aménagement de poste</li>
                        <li><i class="fas fa-check-circle"></i> Envisager un arrêt de travail si nécessaire</li>
                        <li><i class="fas fa-check-circle"></i> Reprendre progressivement après le traitement</li>
                    </ul>
                </div>
                <div class="vivre-card">
                    <div class="vivre-card-top">
                        <div class="vivre-icon"><i class="fas fa-pray"></i></div>
                        <h3>Dimension spirituelle</h3>
                    </div>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> La foi et la prière sont une source de force pour beaucoup</li>
                        <li><i class="fas fa-check-circle"></i> Trouver du sens dans l'épreuve à son propre rythme</li>
                        <li><i class="fas fa-check-circle"></i> L'accompagnement spirituel fait partie des soins palliatifs</li>
                        <li><i class="fas fa-check-circle"></i> Respecter ses croyances tout en suivant les traitements</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- ══ FAQ ══ -->
    <section class="info-section" id="faq">
        <div class="container">
            <div class="section-header">
                <span class="section-tag" style="background:var(--purple-soft);color:var(--purple);">
                    <i class="fas fa-question-circle"></i> FAQ
                </span>
                <h2>Questions fréquentes</h2>
                <p>Les réponses aux questions les plus posées sur le cancer.</p>
            </div>
            <div class="faq-grid">
                <div class="faq-item open">
                    <div class="faq-q" onclick="toggleFaq(this)">
                        Peut-on guérir d'un cancer ?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-a">
                        Oui, de nombreux cancers sont guérissables, surtout détectés tôt. Le mot "guérison" est utilisé lorsque le patient n'a plus de signe de cancer 5 ans après la fin du traitement. Pour d'autres, le cancer devient une maladie chronique contrôlée sur le long terme.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-q" onclick="toggleFaq(this)">
                        Le cancer est-il héréditaire ?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-a">
                        Environ 5 à 10% des cancers ont une composante héréditaire forte. Avoir un proche atteint augmente légèrement le risque, mais ne signifie pas que vous développerez un cancer. Des consultations d'oncogénétique existent pour évaluer ce risque.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-q" onclick="toggleFaq(this)">
                        Combien de temps dure un traitement ?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-a">
                        La durée varie selon le type de cancer, le stade et le traitement choisi. Une chirurgie peut être un acte unique, alors qu'une chimiothérapie peut durer de 3 à 6 mois en cycles. La radiothérapie dure souvent de 3 à 7 semaines. Les thérapies ciblées ou l'hormonothérapie peuvent se prendre pendant plusieurs années.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-q" onclick="toggleFaq(this)">
                        Peut-on continuer à travailler pendant un traitement ?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-a">
                        Oui, certains patients continuent à travailler selon leur tolérance au traitement et la nature de leur emploi. D'autres ont besoin d'un arrêt total. L'essentiel est d'adapter en fonction de son état physique et de la charge de travail, en accord avec l'équipe médicale.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-q" onclick="toggleFaq(this)">
                        Comment soutenir un proche atteint de cancer ?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-a">
                        Être présent sans envahir. Écouter sans chercher à "trouver le bon mot". Aider concrètement (repas, courses, transport aux rendez-vous). Ne pas disparaître après l'annonce du diagnostic. Éviter les conseils non sollicités sur les traitements alternatifs. Et surtout, continuer à voir la personne au-delà de sa maladie.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-q" onclick="toggleFaq(this)">
                        Le cancer du sein touche-t-il aussi les hommes ?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-a">
                        Oui, bien que rare (moins de 1% des cas), les hommes peuvent développer un cancer du sein. Les symptômes sont similaires : masse palpable, modification du mamelon, écoulement. Le pronostic est généralement bon s'il est détecté tôt.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-q" onclick="toggleFaq(this)">
                        Faut-il changer son alimentation après un diagnostic ?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-a">
                        Pas de régime miracle, mais une alimentation équilibrée aide le corps à mieux supporter les traitements. Les équipes soignantes recommandent généralement d'éviter les suppléments non prescrits, de maintenir un apport protéique suffisant et de rester bien hydraté. Un diététicien spécialisé est le meilleur guide.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-q" onclick="toggleFaq(this)">
                        Comment le GSCC peut-il m'aider ?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-a">
                        Le GSCC offre un accompagnement global : orientations vers des structures de soins, aide financière pour les patients démunis, groupes de soutien, séances de dépistage gratuit, sensibilisation communautaire. Vous pouvez nous contacter directement pour une prise en charge personnalisée.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>

    <script>
        // FAQ toggle
        function toggleFaq(el) {
            const item = el.parentElement;
            item.classList.toggle('open');
        }

        // Nav ancres active
        const sections = document.querySelectorAll('section[id], div[id]');
        const navLinks  = document.querySelectorAll('.nav-anchor');
        const observer  = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    navLinks.forEach(l => l.classList.remove('active'));
                    const active = document.querySelector(`.nav-anchor[href="#${e.target.id}"]`);
                    if (active) active.classList.add('active');
                }
            });
        }, { rootMargin: '-30% 0px -60% 0px' });
        sections.forEach(s => observer.observe(s));

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                const target = document.querySelector(a.getAttribute('href'));
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    </script>
</body>
</html>