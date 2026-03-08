<?php
// recherche-forum.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title       = 'Recherche Forum';
$page_description = 'Recherchez dans les sujets et réponses du forum communautaire GSCC.';

// ── Paramètres ────────────────────────────────────────────────
$q          = isset($_GET['q'])          ? trim(sanitize($_GET['q']))          : '';
$filtre_cat = isset($_GET['categorie'])  ? (int)$_GET['categorie']             : 0;
$filtre_tri = isset($_GET['tri'])        ? sanitize($_GET['tri'])               : 'pertinence';
$filtre_type = isset($_GET['type'])       ? sanitize($_GET['type'])              : 'tout';
$page       = isset($_GET['page'])       ? max(1, (int)$_GET['page'])          : 1;
$par_page   = 15;
$offset     = ($page - 1) * $par_page;

$erreur     = '';
$resultats  = [];
$total      = 0;
$categories = [];

// ── Récupérer les catégories pour le filtre ───────────────────
try {
    $categories = $pdo->query(
        "SELECT id, nom FROM forum_categories WHERE est_actif = 1 ORDER BY ordre ASC"
    )->fetchAll();
} catch (PDOException $e) {
    logError('recherche-forum categories: ' . $e->getMessage());
}

// ── Recherche ─────────────────────────────────────────────────
if (mb_strlen($q) >= 2) {
    $like = '%' . $q . '%';

    try {
        // Construire la condition catégorie
        $cat_cond = $filtre_cat > 0 ? 'AND s.categorie_id = :cat' : '';

        // ── Requête sujets ────────────────────────────────────
        if ($filtre_type !== 'reponses') {
            $sql_s = "SELECT
                        'sujet'                             AS rtype,
                        s.id,
                        s.titre,
                        SUBSTRING(s.contenu, 1, 280)        AS extrait,
                        s.auteur_id,
                        CONCAT(u.prenom, ' ', u.nom)        AS auteur_nom,
                        s.date_creation,
                        s.date_derniere_reponse,
                        s.vue_compteur,
                        s.est_resolu,
                        s.est_epingle,
                        s.est_ferme,
                        c.nom                               AS categorie_nom,
                        c.id                                AS categorie_id,
                        (SELECT COUNT(*) FROM forum_reponses r WHERE r.sujet_id = s.id) AS nb_reponses,
                        NULL                                AS sujet_id_rep,
                        NULL                                AS sujet_titre_rep
                    FROM forum_sujets s
                    LEFT JOIN forum_categories c ON s.categorie_id = c.id
                    LEFT JOIN utilisateurs u ON s.auteur_id = u.id
                    WHERE (s.titre LIKE :q1 OR s.contenu LIKE :q2)
                    $cat_cond";
        }

        // ── Requête réponses ──────────────────────────────────
        if ($filtre_type !== 'sujets') {
            $sql_r = "SELECT
                        'reponse'                            AS rtype,
                        r.id,
                        CONCAT('Réponse dans : ', s.titre)  AS titre,
                        SUBSTRING(r.contenu, 1, 280)         AS extrait,
                        r.auteur_id,
                        CONCAT(u.prenom, ' ', u.nom)         AS auteur_nom,
                        r.date_creation,
                        NULL                                 AS date_derniere_reponse,
                        NULL                                 AS vue_compteur,
                        s.est_resolu,
                        s.est_epingle,
                        s.est_ferme,
                        c.nom                                AS categorie_nom,
                        c.id                                 AS categorie_id,
                        NULL                                 AS nb_reponses,
                        s.id                                 AS sujet_id_rep,
                        s.titre                              AS sujet_titre_rep
                    FROM forum_reponses r
                    JOIN forum_sujets s ON r.sujet_id = s.id
                    LEFT JOIN forum_categories c ON s.categorie_id = c.id
                    LEFT JOIN utilisateurs u ON r.auteur_id = u.id
                    WHERE r.contenu LIKE :q3
                    $cat_cond";
        }

        // ── Combiner selon le filtre type ─────────────────────
        if ($filtre_type === 'sujets') {
            $sql_union = $sql_s;
        } elseif ($filtre_type === 'reponses') {
            $sql_union = $sql_r;
        } else {
            $sql_union = "($sql_s) UNION ALL ($sql_r)";
        }

        // ── Tri ───────────────────────────────────────────────
        $order = match ($filtre_tri) {
            'date'     => 'date_creation DESC',
            'vues'     => 'vue_compteur DESC, date_creation DESC',
            'reponses' => 'nb_reponses DESC, date_creation DESC',
            default    => 'date_creation DESC'   // pertinence ≈ date
        };

        // Compter le total
        $sql_count = "SELECT COUNT(*) FROM ($sql_union) AS total";
        $stmt_count = $pdo->prepare($sql_count);
        $stmt_count->bindValue(':q1', $like);
        $stmt_count->bindValue(':q2', $like);
        if ($filtre_type !== 'sujets') $stmt_count->bindValue(':q3', $like);
        if ($filtre_cat > 0) $stmt_count->bindValue(':cat', $filtre_cat, PDO::PARAM_INT);
        $stmt_count->execute();
        $total = (int)$stmt_count->fetchColumn();

        // Récupérer la page
        $sql_final = "SELECT * FROM ($sql_union) AS r ORDER BY $order LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql_final);
        $stmt->bindValue(':q1', $like);
        $stmt->bindValue(':q2', $like);
        if ($filtre_type !== 'sujets') $stmt->bindValue(':q3', $like);
        if ($filtre_cat > 0) $stmt->bindValue(':cat', $filtre_cat, PDO::PARAM_INT);
        $stmt->bindValue(':limit',  $par_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
        $stmt->execute();
        $resultats = $stmt->fetchAll();
    } catch (PDOException $e) {
        logError('recherche-forum: ' . $e->getMessage());
        $erreur = 'Une erreur est survenue lors de la recherche.';
    }
} elseif ($q !== '' && mb_strlen($q) < 2) {
    $erreur = 'Veuillez saisir au moins 2 caractères.';
}

// ── Nombre de pages ───────────────────────────────────────────
$nb_pages = $total > 0 ? (int)ceil($total / $par_page) : 0;

// ── Surligner les mots-clés ───────────────────────────────────
function hl(string $text, string $q): string
{
    $text = strip_tags($text);
    if (!$q) return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    return preg_replace(
        '/(' . preg_quote(htmlspecialchars($q, ENT_QUOTES, 'UTF-8'), '/') . ')/iu',
        '<mark>$1</mark>',
        htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
    );
}

// ── Construire l'URL de pagination ───────────────────────────
function buildUrl(int $p): string
{
    $params = $_GET;
    $params['page'] = $p;
    return 'recherche-forum.php?' . http_build_query($params);
}

// ── Stats du forum pour la sidebar ────────────────────────────
$stats = ['sujets' => 0, 'reponses' => 0, 'membres' => 0];
try {
    $stats['sujets']   = (int)$pdo->query("SELECT COUNT(*) FROM forum_sujets")->fetchColumn();
    $stats['reponses'] = (int)$pdo->query("SELECT COUNT(*) FROM forum_reponses")->fetchColumn();
    $stats['membres']  = (int)$pdo->query("SELECT COUNT(DISTINCT auteur_id) FROM forum_sujets WHERE auteur_id IS NOT NULL")->fetchColumn();
} catch (PDOException $e) {
}

// ── Derniers sujets pour la sidebar ──────────────────────────
$derniers_sujets = [];
try {
    $derniers_sujets = $pdo->query(
        "SELECT s.id, s.titre, s.date_creation,
                CONCAT(u.prenom, ' ', u.nom) AS auteur_nom
         FROM forum_sujets s
         LEFT JOIN utilisateurs u ON s.auteur_id = u.id
         ORDER BY s.date_creation DESC LIMIT 5"
    )->fetchAll();
} catch (PDOException $e) {
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> — <?= SITE_NAME ?></title>
    <meta name="description" content="<?= e($page_description) ?>">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        /* ── VARIABLES ── */
        :root {
            --blue: #003399;
            --blue-dark: #002270;
            --blue-soft: rgba(0, 51, 153, .08);
            --rose: #D94F7A;
            --rose-pale: rgba(217, 79, 122, .08);
            --green: #2E7D32;
            --bg: #F4F6FB;
            --bg-light: #EEF1F8;
            --gray: #6B7280;
            --border: #E5E9F2;
            --white: #FFFFFF;
            --dark: #1A2240;
            --r: 12px;
            --sh: 0 4px 24px rgba(0, 51, 153, .08);
            --sh-h: 0 16px 48px rgba(0, 51, 153, .15);
        }

        /* ── PAGE HEADER ── */
        .page-header {
            background: linear-gradient(135deg, #003399 0%, #1a56cc 60%, #1a7abf 100%);
            color: white;
            padding: 72px 0 90px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before,
        .page-header::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            opacity: .07;
            background: white;
            pointer-events: none;
        }

        .page-header::before {
            width: 420px;
            height: 420px;
            top: -160px;
            right: -80px;
        }

        .page-header::after {
            width: 260px;
            height: 260px;
            bottom: -100px;
            left: -60px;
        }

        .page-header-wave {
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            line-height: 0;
        }

        .page-header-wave svg {
            display: block;
        }

        .header-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, .15);
            border: 1px solid rgba(255, 255, 255, .25);
            color: white;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 6px 16px;
            border-radius: 20px;
            margin-bottom: 20px;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 700;
            margin-bottom: 14px;
            letter-spacing: -.5px;
        }

        .page-header p {
            font-size: 1.05rem;
            color: rgba(255, 255, 255, .82);
            max-width: 480px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* ── BARRE DE RECHERCHE dans le hero ── */
        .hero-search {
            max-width: 620px;
            margin: 28px auto 0;
            position: relative;
            z-index: 2;
        }

        .hero-search form {
            display: flex;
            gap: 0;
            background: white;
            border-radius: 50px;
            padding: 6px 6px 6px 22px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, .22);
        }

        .hero-search input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            background: transparent;
            min-width: 0;
        }

        .hero-search input::placeholder {
            color: var(--gray);
        }

        .hero-search button {
            background: linear-gradient(135deg, var(--rose), #C0306A);
            color: white;
            border: none;
            cursor: pointer;
            padding: 12px 24px;
            border-radius: 40px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: opacity .2s;
            white-space: nowrap;
        }

        .hero-search button:hover {
            opacity: .9;
        }

        /* ── SECTION PRINCIPALE ── */
        .search-section {
            padding: 48px 0 80px;
            background: var(--bg);
        }

        /* ── LAYOUT ── */
        .search-layout {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 32px;
            align-items: start;
        }

        /* ── FILTRES ── */
        .filters-bar {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 16px 20px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px;
        }

        .filters-bar form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            width: 100%;
            align-items: center;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .filter-group label {
            font-size: 13px;
            font-weight: 600;
            color: var(--dark);
            white-space: nowrap;
        }

        .filter-group select {
            padding: 7px 12px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            background: var(--bg);
            cursor: pointer;
            outline: none;
            transition: border-color .2s;
        }

        .filter-group select:focus {
            border-color: var(--blue);
        }

        .filter-sep {
            width: 1px;
            height: 28px;
            background: var(--border);
        }

        .btn-filter {
            background: var(--blue);
            color: white;
            border: none;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background .2s;
        }

        .btn-filter:hover {
            background: var(--blue-dark);
        }

        .btn-reset {
            color: var(--gray);
            font-size: 13px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 8px 10px;
            border-radius: 8px;
            transition: color .2s;
        }

        .btn-reset:hover {
            color: var(--rose);
        }

        /* ── RÉSUMÉ ── */
        .results-summary {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }

        .results-summary strong {
            color: var(--dark);
        }

        .results-summary .breadcrumb a {
            color: var(--blue);
            text-decoration: none;
            font-size: 13px;
        }

        .results-summary .breadcrumb a:hover {
            text-decoration: underline;
        }

        .results-summary .breadcrumb span {
            color: var(--gray);
            margin: 0 5px;
        }

        /* ── CARTE RÉSULTAT ── */
        .result-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 20px 22px;
            margin-bottom: 14px;
            text-decoration: none;
            color: inherit;
            display: block;
            transition: transform .25s, box-shadow .25s, border-color .25s;
            position: relative;
            overflow: hidden;
        }

        .result-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            border-radius: 3px 0 0 3px;
            transition: background .25s;
        }

        .result-card.type-sujet::before {
            background: var(--blue);
        }

        .result-card.type-reponse::before {
            background: var(--rose);
        }

        .result-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--sh-h);
            border-color: #d0d8ee;
        }

        .card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .card-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            padding: 3px 10px;
            border-radius: 20px;
            flex-shrink: 0;
        }

        .badge-sujet {
            background: var(--blue-soft);
            color: var(--blue);
        }

        .badge-reponse {
            background: var(--rose-pale);
            color: var(--rose);
        }

        .card-tags {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            flex-shrink: 0;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            padding: 3px 9px;
            border-radius: 12px;
            font-weight: 600;
        }

        .tag-resolu {
            background: #e7f5e8;
            color: var(--green);
        }

        .tag-epingle {
            background: #fff8e1;
            color: #b45309;
        }

        .tag-ferme {
            background: #f5f5f5;
            color: var(--gray);
        }

        .card-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.4;
            margin-bottom: 8px;
        }

        .card-title mark {
            background: rgba(217, 79, 122, .15);
            color: var(--rose);
            border-radius: 3px;
            padding: 0 2px;
        }

        .card-excerpt {
            font-size: 14px;
            color: var(--gray);
            line-height: 1.65;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-excerpt mark {
            background: rgba(0, 51, 153, .1);
            color: var(--blue);
            border-radius: 3px;
            padding: 0 2px;
            font-weight: 600;
        }

        .card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            font-size: 12px;
            color: var(--gray);
            padding-top: 12px;
            border-top: 1px solid var(--bg-light);
            align-items: center;
        }

        .card-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .card-meta i {
            color: var(--blue);
            font-size: 10px;
        }

        .card-meta .cat-pill {
            background: var(--blue-soft);
            color: var(--blue);
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 11px;
        }

        .card-meta .arrow {
            margin-left: auto;
            color: var(--blue);
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ── SUJET PARENT (pour les réponses) ── */
        .card-parent {
            background: var(--bg);
            border-radius: 8px;
            padding: 9px 14px;
            margin-bottom: 10px;
            font-size: 13px;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .card-parent i {
            color: var(--blue);
        }

        .card-parent strong {
            color: var(--dark);
        }

        /* ── ÉTAT VIDE / ERREUR ── */
        .empty-state {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 60px 30px;
            text-align: center;
        }

        .empty-state i {
            font-size: 48px;
            color: var(--border);
            display: block;
            margin-bottom: 16px;
        }

        .empty-state h3 {
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 18px;
        }

        .empty-state p {
            color: var(--gray);
            font-size: 14.5px;
            line-height: 1.7;
            max-width: 400px;
            margin: 0 auto 20px;
        }

        /* Suggestions */
        .suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin-top: 14px;
        }

        .suggestion-tag {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--blue);
            font-size: 13px;
            text-decoration: none;
            transition: all .2s;
            font-weight: 500;
        }

        .suggestion-tag:hover {
            background: var(--blue);
            color: white;
            border-color: var(--blue);
        }

        /* ── PAGINATION ── */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 6px;
            margin-top: 28px;
            flex-wrap: wrap;
        }

        .page-btn {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all .2s;
            border: 1.5px solid var(--border);
            color: var(--dark);
            background: var(--white);
        }

        .page-btn:hover {
            background: var(--blue-soft);
            border-color: var(--blue);
            color: var(--blue);
        }

        .page-btn.active {
            background: var(--blue);
            border-color: var(--blue);
            color: white;
        }

        .page-btn.disabled {
            opacity: .4;
            pointer-events: none;
        }

        .page-btn.wide {
            width: auto;
            padding: 0 14px;
        }

        /* ── SIDEBAR ── */
        .forum-sidebar {
            position: sticky;
            top: 100px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .sidebar-widget {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 20px 22px;
            box-shadow: var(--sh);
        }

        .widget-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--bg-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .widget-title i {
            color: var(--rose);
        }

        /* Stats mini */
        .mini-stats {
            display: flex;
            gap: 10px;
        }

        .mini-stat {
            flex: 1;
            text-align: center;
            padding: 12px 8px;
            background: var(--bg);
            border-radius: 10px;
        }

        .mini-stat .val {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
        }

        .mini-stat .lbl {
            font-size: 11px;
            color: var(--gray);
            margin-top: 2px;
        }

        /* Derniers sujets sidebar */
        .topic-list {
            list-style: none;
        }

        .topic-item {
            padding: 10px 0;
            border-bottom: 1px solid var(--bg-light);
        }

        .topic-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .topic-item:first-child {
            padding-top: 0;
        }

        .topic-link {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            font-size: 13px;
            line-height: 1.4;
            display: block;
            margin-bottom: 4px;
            transition: color .2s;
        }

        .topic-link:hover {
            color: var(--blue);
        }

        .topic-meta {
            font-size: 11.5px;
            color: var(--gray);
            display: flex;
            gap: 10px;
        }

        .topic-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .topic-meta i {
            color: var(--blue);
            font-size: 10px;
        }

        /* Boutons sidebar */
        .btn-new-topic {
            background: linear-gradient(135deg, var(--blue), #1a56cc);
            color: white;
            padding: 13px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            transition: transform .2s, box-shadow .2s;
            width: 100%;
        }

        .btn-new-topic:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 51, 153, .28);
        }

        .btn-back-forum {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            margin-top: 10px;
            padding: 10px;
            border-radius: 8px;
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--dark);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all .2s;
        }

        .btn-back-forum:hover {
            background: var(--blue-soft);
            color: var(--blue);
        }

        /* Recherche rapide sidebar */
        .quick-search {
            position: relative;
        }

        .quick-search input {
            width: 100%;
            padding: 9px 38px 9px 12px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 13.5px;
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--dark);
            outline: none;
            transition: border-color .2s;
        }

        .quick-search input:focus {
            border-color: var(--blue);
            background: white;
        }

        .quick-search button {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            padding: 6px 8px;
            transition: color .2s;
        }

        .quick-search button:hover {
            color: var(--blue);
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            .search-layout {
                grid-template-columns: 1fr;
            }

            .forum-sidebar {
                position: static;
            }
        }

        @media (max-width: 640px) {
            .page-header {
                padding: 52px 0 72px;
            }

            .hero-search form {
                padding: 5px 5px 5px 16px;
            }

            .hero-search input {
                font-size: 14px;
            }

            .hero-search button {
                padding: 10px 16px;
                font-size: 13px;
            }

            .filter-group {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }

            .filter-sep {
                display: none;
            }

            .card-meta .arrow {
                display: none;
            }
        }
    </style>
</head>

<body>
    <?php include 'templates/header.php'; ?>

    <!-- ── Page Header ── -->
    <div class="page-header">
        <div class="container" style="position:relative;z-index:1;">
            <div class="header-eyebrow" data-aos="fade-down">
                <i class="fas fa-search"></i> Forum GSCC — Recherche
            </div>
            <h1 data-aos="fade-up">Rechercher dans le forum</h1>
            <p data-aos="fade-up" data-aos-delay="80">
                Trouvez des sujets, des discussions et des réponses de la communauté
            </p>

            <!-- Barre de recherche principale -->
            <div class="hero-search" data-aos="fade-up" data-aos-delay="160">
                <form action="recherche-forum.php" method="GET">
                    <input type="text"
                        name="q"
                        value="<?= htmlspecialchars($q, ENT_QUOTES) ?>"
                        placeholder="Rechercher un sujet, une question, un mot-clé…"
                        autocomplete="off"
                        autofocus>
                    <?php if ($filtre_cat):  ?><input type="hidden" name="categorie" value="<?= $filtre_cat ?>"><?php endif; ?>
                    <?php if ($filtre_tri !== 'pertinence'): ?><input type="hidden" name="tri" value="<?= e($filtre_tri) ?>"><?php endif; ?>
                    <?php if ($filtre_type !== 'tout'): ?><input type="hidden" name="type" value="<?= e($filtre_type) ?>"><?php endif; ?>
                    <button type="submit">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                </form>
            </div>
        </div>
        <div class="page-header-wave">
            <svg viewBox="0 0 1440 48" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path fill="#F4F6FB" d="M0,48 C360,0 1080,0 1440,48 L1440,48 L0,48 Z" />
            </svg>
        </div>
    </div>

    <!-- ── Section résultats ── -->
    <section class="search-section">
        <div class="container">
            <div class="search-layout">

                <!-- ═══ COLONNE PRINCIPALE ═══ -->
                <div class="search-main">

                    <!-- Filtres -->
                    <?php if ($q !== ''): ?>
                        <div class="filters-bar" data-aos="fade-up">
                            <form action="recherche-forum.php" method="GET">
                                <input type="hidden" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES) ?>">

                                <div class="filter-group">
                                    <label for="f-cat"><i class="fas fa-folder"></i> Catégorie</label>
                                    <select name="categorie" id="f-cat">
                                        <option value="0">Toutes les catégories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>" <?= $filtre_cat == $cat['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="filter-sep"></div>

                                <div class="filter-group">
                                    <label for="f-type"><i class="fas fa-filter"></i> Type</label>
                                    <select name="type" id="f-type">
                                        <option value="tout" <?= $filtre_type === 'tout'     ? 'selected' : '' ?>>Tout</option>
                                        <option value="sujets" <?= $filtre_type === 'sujets'   ? 'selected' : '' ?>>Sujets uniquement</option>
                                        <option value="reponses" <?= $filtre_type === 'reponses' ? 'selected' : '' ?>>Réponses uniquement</option>
                                    </select>
                                </div>

                                <div class="filter-sep"></div>

                                <div class="filter-group">
                                    <label for="f-tri"><i class="fas fa-sort"></i> Trier par</label>
                                    <select name="tri" id="f-tri">
                                        <option value="pertinence" <?= $filtre_tri === 'pertinence' ? 'selected' : '' ?>>Plus récent</option>
                                        <option value="vues" <?= $filtre_tri === 'vues'       ? 'selected' : '' ?>>Plus vus</option>
                                        <option value="reponses" <?= $filtre_tri === 'reponses'   ? 'selected' : '' ?>>Plus de réponses</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn-filter">
                                    <i class="fas fa-sliders-h"></i> Appliquer
                                </button>

                                <a href="recherche-forum.php?q=<?= urlencode($q) ?>" class="btn-reset">
                                    <i class="fas fa-times"></i> Réinitialiser
                                </a>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if ($erreur): ?>
                        <!-- ── Erreur ── -->
                        <div class="empty-state" data-aos="fade-up">
                            <i class="fas fa-exclamation-circle" style="color:#e08888;"></i>
                            <h3>Oops !</h3>
                            <p><?= htmlspecialchars($erreur) ?></p>
                        </div>

                    <?php elseif ($q === ''): ?>
                        <!-- ── Aucune recherche ── -->
                        <div class="empty-state" data-aos="fade-up">
                            <i class="fas fa-search"></i>
                            <h3>Que cherchez-vous ?</h3>
                            <p>Entrez un mot-clé dans la barre ci-dessus pour trouver des sujets ou des réponses dans la communauté.</p>
                            <div class="suggestions">
                                <?php foreach (['cancer', 'soutien', 'traitement', 'anxiété', 'famille', 'espoir', 'médecin', 'bénévole', 'dépistage'] as $s): ?>
                                    <a href="recherche-forum.php?q=<?= urlencode($s) ?>" class="suggestion-tag"><?= $s ?></a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    <?php elseif (empty($resultats)): ?>
                        <!-- ── Aucun résultat ── -->
                        <div class="empty-state" data-aos="fade-up">
                            <i class="fas fa-search-minus"></i>
                            <h3>Aucun résultat pour « <?= htmlspecialchars($q) ?> »</h3>
                            <p>Aucun sujet ni réponse ne correspond à votre recherche.<br>
                                Essayez avec d'autres mots-clés ou parcourez les catégories.</p>
                            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:4px;">
                                <a href="forum.php" class="suggestion-tag" style="background:var(--blue);color:white;border-color:var(--blue);">
                                    <i class="fas fa-th-large"></i> Parcourir le forum
                                </a>
                                <?php if (isLoggedIn()): ?>
                                    <a href="nouveau-sujet.php" class="suggestion-tag" style="background:var(--rose);color:white;border-color:var(--rose);">
                                        <i class="fas fa-plus"></i> Poser une question
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- ── Résumé ── -->
                        <div class="results-summary" data-aos="fade-up">
                            <span>
                                <strong><?= number_format($total) ?></strong> résultat<?= $total > 1 ? 's' : '' ?>
                                pour « <strong><?= htmlspecialchars($q) ?></strong> »
                                <?php if ($filtre_cat > 0): ?>
                                    <?php foreach ($categories as $cat) if ($cat['id'] == $filtre_cat) echo ' dans <strong>' . htmlspecialchars($cat['nom']) . '</strong>'; ?>
                                <?php endif; ?>
                            </span>
                            <span class="breadcrumb">
                                <a href="forum.php"><i class="fas fa-comments"></i> Forum</a>
                                <span>›</span>
                                Recherche
                            </span>
                        </div>

                        <!-- ── Résultats ── -->
                        <?php foreach ($resultats as $i => $r):
                            $is_sujet  = $r['rtype'] === 'sujet';
                            $href = $is_sujet
                                ? 'forum-sujet.php?id=' . $r['id']
                                : 'forum-sujet.php?id=' . ($r['sujet_id_rep'] ?? $r['id']);
                        ?>
                            <a href="<?= $href ?>"
                                class="result-card type-<?= $r['rtype'] ?>"
                                data-aos="fade-up"
                                data-aos-delay="<?= ($i % 5) * 50 ?>">

                                <!-- Ligne du haut : badge + tags statut -->
                                <div class="card-top">
                                    <span class="card-type-badge badge-<?= $r['rtype'] ?>">
                                        <i class="fas fa-<?= $is_sujet ? 'comments' : 'reply' ?>"></i>
                                        <?= $is_sujet ? 'Sujet' : 'Réponse' ?>
                                    </span>
                                    <div class="card-tags">
                                        <?php if ($r['est_epingle']): ?><span class="tag tag-epingle"><i class="fas fa-thumbtack"></i> Épinglé</span><?php endif; ?>
                                        <?php if ($r['est_resolu']):  ?><span class="tag tag-resolu"><i class="fas fa-check-circle"></i> Résolu</span><?php endif; ?>
                                        <?php if ($r['est_ferme']):   ?><span class="tag tag-ferme"><i class="fas fa-lock"></i> Fermé</span><?php endif; ?>
                                    </div>
                                </div>

                                <!-- Pour les réponses : montrer le sujet parent -->
                                <?php if (!$is_sujet && !empty($r['sujet_titre_rep'])): ?>
                                    <div class="card-parent">
                                        <i class="fas fa-arrow-right"></i>
                                        <span>Dans le sujet : <strong><?= htmlspecialchars($r['sujet_titre_rep']) ?></strong></span>
                                    </div>
                                <?php endif; ?>

                                <!-- Titre -->
                                <div class="card-title"><?= hl($r['titre'] ?? '', $q) ?></div>

                                <!-- Extrait -->
                                <?php if (!empty($r['extrait'])): ?>
                                    <div class="card-excerpt"><?= hl($r['extrait'], $q) ?></div>
                                <?php endif; ?>

                                <!-- Méta -->
                                <div class="card-meta">
                                    <?php if (!empty($r['categorie_nom'])): ?>
                                        <span class="cat-pill">
                                            <i class="fas fa-folder" style="font-size:9px;"></i>
                                            <?= htmlspecialchars($r['categorie_nom']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span><i class="fas fa-user"></i> <?= htmlspecialchars($r['auteur_nom'] ?? 'Anonyme') ?></span>
                                    <span><i class="far fa-clock"></i> <?= isset($r['date_creation']) ? date('d/m/Y', strtotime($r['date_creation'])) : '' ?></span>
                                    <?php if ($is_sujet && isset($r['vue_compteur'])): ?>
                                        <span><i class="fas fa-eye"></i> <?= number_format((int)$r['vue_compteur']) ?> vue<?= (int)$r['vue_compteur'] > 1 ? 's' : '' ?></span>
                                    <?php endif; ?>
                                    <?php if ($is_sujet && isset($r['nb_reponses'])): ?>
                                        <span><i class="fas fa-reply"></i> <?= (int)$r['nb_reponses'] ?> réponse<?= (int)$r['nb_reponses'] > 1 ? 's' : '' ?></span>
                                    <?php endif; ?>
                                    <span class="arrow">Voir <i class="fas fa-arrow-right"></i></span>
                                </div>
                            </a>
                        <?php endforeach; ?>

                        <!-- ── Pagination ── -->
                        <?php if ($nb_pages > 1): ?>
                            <div class="pagination">
                                <!-- Précédent -->
                                <a href="<?= buildUrl($page - 1) ?>"
                                    class="page-btn wide <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>

                                <?php
                                $start = max(1, $page - 2);
                                $end   = min($nb_pages, $page + 2);
                                if ($start > 1):
                                ?>
                                    <a href="<?= buildUrl(1) ?>" class="page-btn">1</a>
                                    <?php if ($start > 2): ?><span class="page-btn disabled" style="border:none;color:var(--gray);">…</span><?php endif; ?>
                                <?php endif; ?>

                                <?php for ($p = $start; $p <= $end; $p++): ?>
                                    <a href="<?= buildUrl($p) ?>"
                                        class="page-btn <?= $p === $page ? 'active' : '' ?>">
                                        <?= $p ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($end < $nb_pages): ?>
                                    <?php if ($end < $nb_pages - 1): ?><span class="page-btn disabled" style="border:none;color:var(--gray);">…</span><?php endif; ?>
                                    <a href="<?= buildUrl($nb_pages) ?>" class="page-btn"><?= $nb_pages ?></a>
                                <?php endif; ?>

                                <!-- Suivant -->
                                <a href="<?= buildUrl($page + 1) ?>"
                                    class="page-btn wide <?= $page >= $nb_pages ? 'disabled' : '' ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                            <p style="text-align:center;font-size:13px;color:var(--gray);margin-top:10px;">
                                Page <?= $page ?> sur <?= $nb_pages ?>
                                — <?= $total ?> résultat<?= $total > 1 ? 's' : '' ?> au total
                            </p>
                        <?php endif; ?>

                    <?php endif; ?>

                </div><!-- /.search-main -->

                <!-- ═══ SIDEBAR ═══ -->
                <aside class="forum-sidebar">

                    <!-- Nouveau sujet / Login -->
                    <div class="sidebar-widget">
                        <?php if (isLoggedIn()): ?>
                            <a href="nouveau-sujet.php" class="btn-new-topic">
                                <i class="fas fa-plus-circle"></i> Nouveau sujet
                            </a>
                        <?php else: ?>
                            <div style="text-align:center;padding:8px 0;">
                                <div style="width:44px;height:44px;background:var(--blue-soft);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:var(--blue);font-size:18px;">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <p style="font-size:13px;color:var(--gray);margin-bottom:12px;line-height:1.5;">
                                    Connectez-vous pour participer au forum.
                                </p>
                                <div style="display:flex;gap:8px;justify-content:center;">
                                    <a href="connexion.php" style="background:var(--blue);color:white;padding:7px 14px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600;">Se connecter</a>
                                    <a href="inscription.php" style="background:var(--bg-light);color:var(--dark);padding:7px 14px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600;">S'inscrire</a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <a href="forum.php" class="btn-back-forum">
                            <i class="fas fa-arrow-left"></i> Retour au forum
                        </a>
                    </div>

                    <!-- Statistiques du forum -->
                    <div class="sidebar-widget">
                        <h3 class="widget-title">
                            <span>Statistiques</span>
                            <i class="fas fa-chart-bar"></i>
                        </h3>
                        <div class="mini-stats">
                            <div class="mini-stat">
                                <div class="val"><?= number_format($stats['sujets']) ?></div>
                                <div class="lbl">Sujets</div>
                            </div>
                            <div class="mini-stat">
                                <div class="val"><?= number_format($stats['reponses']) ?></div>
                                <div class="lbl">Réponses</div>
                            </div>
                            <div class="mini-stat">
                                <div class="val"><?= number_format($stats['membres']) ?></div>
                                <div class="lbl">Membres</div>
                            </div>
                        </div>
                    </div>

                    <!-- Recherche rapide par catégorie -->
                    <?php if (!empty($categories)): ?>
                        <div class="sidebar-widget">
                            <h3 class="widget-title">
                                <span>Catégories</span>
                                <i class="fas fa-folder-open"></i>
                            </h3>
                            <ul style="list-style:none;">
                                <?php foreach ($categories as $cat): ?>
                                    <li style="border-bottom:1px solid var(--bg-light);">
                                        <a href="recherche-forum.php?q=<?= urlencode($q) ?>&categorie=<?= $cat['id'] ?>"
                                            style="display:flex;align-items:center;justify-content:space-between;
                                          padding:9px 4px;text-decoration:none;font-size:13.5px;
                                          color:<?= $filtre_cat == $cat['id'] ? 'var(--blue)' : 'var(--dark)' ?>;
                                          font-weight:<?= $filtre_cat == $cat['id'] ? '700' : '400' ?>;
                                          transition:color .2s;"
                                            onmouseover="this.style.color='var(--blue)'"
                                            onmouseout="this.style.color='<?= $filtre_cat == $cat['id'] ? 'var(--blue)' : 'var(--dark)' ?>'">
                                            <span><i class="fas fa-folder" style="font-size:11px;margin-right:7px;color:var(--blue);opacity:.6;"></i><?= htmlspecialchars($cat['nom']) ?></span>
                                            <?php if ($filtre_cat == $cat['id']): ?><i class="fas fa-check" style="font-size:11px;color:var(--blue);"></i><?php endif; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                                <?php if ($filtre_cat > 0): ?>
                                    <li style="padding-top:8px;">
                                        <a href="recherche-forum.php?q=<?= urlencode($q) ?>"
                                            style="font-size:12px;color:var(--rose);text-decoration:none;display:flex;align-items:center;gap:5px;">
                                            <i class="fas fa-times-circle"></i> Toutes les catégories
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Derniers sujets -->
                    <div class="sidebar-widget">
                        <h3 class="widget-title">
                            <span>Derniers sujets</span>
                            <i class="fas fa-clock"></i>
                        </h3>
                        <?php if (empty($derniers_sujets)): ?>
                            <p style="font-size:13px;color:var(--gray);">Aucun sujet pour le moment.</p>
                        <?php else: ?>
                            <ul class="topic-list">
                                <?php foreach ($derniers_sujets as $s): ?>
                                    <li class="topic-item">
                                        <a href="forum-sujet.php?id=<?= $s['id'] ?>" class="topic-link">
                                            <?= htmlspecialchars(mb_strlen($s['titre']) > 45 ? mb_substr($s['titre'], 0, 42) . '…' : $s['titre']) ?>
                                        </a>
                                        <div class="topic-meta">
                                            <span><i class="fas fa-user"></i> <?= htmlspecialchars($s['auteur_nom'] ?? 'Anonyme') ?></span>
                                            <span><i class="far fa-clock"></i> <?= isset($s['date_creation']) ? date('d/m/Y', strtotime($s['date_creation'])) : '' ?></span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                </aside>

            </div><!-- /.search-layout -->
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 550,
            once: true,
            offset: 40
        });
    </script>
</body>

</html>