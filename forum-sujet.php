<?php
// forum-sujet.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: forum.php'); exit; }

// Pagination des réponses
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$errors  = [];
$success = isset($_GET['nouveau']) ? 'Votre sujet a été publié avec succès !' : null;

// ── Récupérer le sujet ────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare(
        "SELECT s.*,
         CONCAT(u.prenom, ' ', u.nom) as auteur_nom,
         u.email as auteur_email,
         c.nom as categorie_nom,
         c.id  as categorie_id_val
         FROM forum_sujets s
         LEFT JOIN utilisateurs u ON s.auteur_id = u.id
         LEFT JOIN forum_categories c ON s.categorie_id = c.id
         WHERE s.id = ?"
    );
    $stmt->execute([$id]);
    $sujet = $stmt->fetch();

    if (!$sujet) { header('Location: forum.php'); exit; }

    // Incrémenter le compteur de vues (une seule fois par session)
    if (!isset($_SESSION['viewed_topics'][$id])) {
        $pdo->prepare("UPDATE forum_sujets SET vue_compteur = vue_compteur + 1 WHERE id = ?")->execute([$id]);
        $_SESSION['viewed_topics'][$id] = true;
    }

    // Compter les réponses
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM forum_reponses WHERE sujet_id = ?");
    $stmt->execute([$id]);
    $total_reponses = (int)$stmt->fetchColumn();
    $total_pages = max(1, ceil($total_reponses / $limit));

    // Récupérer les réponses
    $stmt = $pdo->prepare(
        "SELECT r.*,
         CONCAT(u.prenom, ' ', u.nom) as auteur_nom,
         u.email as auteur_email
         FROM forum_reponses r
         LEFT JOIN utilisateurs u ON r.auteur_id = u.id
         WHERE r.sujet_id = ?
         ORDER BY r.date_creation ASC
         LIMIT ? OFFSET ?"
    );
    $stmt->execute([$id, $limit, $offset]);
    $reponses = $stmt->fetchAll();

} catch (PDOException $e) {
    logError("Erreur forum-sujet.php: " . $e->getMessage());
    header('Location: forum.php'); exit;
}

// ── Traitement du formulaire de réponse ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'repondre') {

    if (!isLoggedIn()) {
        header('Location: connexion.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }

    if ($sujet['est_ferme']) {
        $errors[] = 'Ce sujet est fermé, vous ne pouvez plus y répondre.';
    } else {
        $contenu = trim($_POST['contenu'] ?? '');
        $user_id = $_SESSION['user_id'] ?? 0;

        if (strlen($contenu) < 10)  $errors[] = 'Votre message doit comporter au moins 10 caractères.';
        if (strlen($contenu) > 5000) $errors[] = 'Votre message ne doit pas dépasser 5000 caractères.';

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO forum_reponses (sujet_id, auteur_id, contenu, date_creation)
                     VALUES (?, ?, ?, NOW())"
                );
                $stmt->execute([$id, $user_id, $contenu]);

                // Recalculer la page pour aller à la dernière réponse
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM forum_reponses WHERE sujet_id = ?");
                $stmt->execute([$id]);
                $new_total = (int)$stmt->fetchColumn();
                $last_page = max(1, ceil($new_total / $limit));

                header("Location: forum-sujet.php?id={$id}&page={$last_page}&repondu=1#reponses");
                exit;
            } catch (PDOException $e) {
                logError("Erreur réponse forum: " . $e->getMessage());
                $errors[] = 'Une erreur est survenue. Veuillez réessayer.';
            }
        }
    }
}

if (isset($_GET['repondu'])) $success = 'Votre réponse a été publiée !';

$page_title = $sujet['titre'];

// Génère un identicon SVG style GitHub (grille 5x5 symétrique)
function generateIdenticon($nom, $size = 42) {
    $nom   = trim($nom ?? 'Membre');
    $hash  = md5(strtolower($nom));

    // Couleur principale depuis les 3 premiers octets du hash
    $r = hexdec(substr($hash, 0, 2));
    $g = hexdec(substr($hash, 2, 2));
    $b = hexdec(substr($hash, 4, 2));
    // Saturation forcée pour éviter les couleurs trop ternes
    $max = max($r, $g, $b);
    if ($max > 0) {
        $r = intval(($r / $max) * 180 + 30);
        $g = intval(($g / $max) * 180 + 30);
        $b = intval(($b / $max) * 180 + 30);
    }
    $color = sprintf('#%02x%02x%02x', min($r,255), min($g,255), min($b,255));
    $bg    = '#F0F0F0';

    // Grille 5x5 symétrique (on utilise seulement les 3 colonnes gauches, miroir sur la droite)
    $cells = [];
    for ($row = 0; $row < 5; $row++) {
        for ($col = 0; $col < 3; $col++) {
            $idx         = $row * 3 + $col;
            $byte        = hexdec(substr($hash, 6 + $idx, 2));
            $cells[$row][$col] = ($byte % 2 === 0);
        }
    }

    $cell  = $size / 5;
    $rects = '';
    for ($row = 0; $row < 5; $row++) {
        for ($col = 0; $col < 5; $col++) {
            $srcCol = $col < 3 ? $col : (4 - $col); // symétrie
            if ($cells[$row][$srcCol]) {
                $x = $col * $cell;
                $y = $row * $cell;
                $rects .= "<rect x='{$x}' y='{$y}' width='{$cell}' height='{$cell}' fill='{$color}'/>";
            }
        }
    }

    $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='{$size}' height='{$size}' viewBox='0 0 {$size} {$size}'>"
         . "<rect width='{$size}' height='{$size}' fill='{$bg}'/>"
         . $rects
         . "</svg>";

    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> — Forum <?= SITE_NAME ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --blue:      #003399;
            --blue-dark: #002270;
            --blue-soft: rgba(0,51,153,0.08);
            --rose:      #D94F7A;
            --orange:    #F59E0B;
            --green:     #2E7D32;
            --gray-bg:   #F4F6FB;
            --gray-light:#EEF1F8;
            --gray-text: #6B7280;
            --border:    #E5E9F2;
            --white:     #FFFFFF;
            --dark:      #1A2240;
            --radius:    12px;
            --shadow:    0 4px 24px rgba(0,51,153,0.08);
            --red:       #DC2626;
        }

        /* ── Header ── */
        .forum-header {
            background: linear-gradient(135deg, #003399 0%, #1a56cc 60%, #1a7abf 100%);
            color: white;
            padding: 48px 0 68px;
            position: relative; overflow: hidden;
        }
        .forum-header::before {
            content:''; position:absolute;
            width:360px; height:360px; border-radius:50%;
            opacity:0.07; background:white;
            top:-140px; right:-60px; pointer-events:none;
        }
        .forum-header-wave { position:absolute; bottom:-1px; left:0; width:100%; line-height:0; }
        .forum-header-wave svg { display:block; }
        .forum-header-inner { position:relative; z-index:1; }
        .forum-header-eyebrow {
            display:inline-flex; align-items:center; gap:8px;
            background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.25);
            color:white; font-size:11px; font-weight:600;
            letter-spacing:2px; text-transform:uppercase;
            padding:5px 14px; border-radius:20px; margin-bottom:12px;
        }
        .forum-header h1 {
            font-family:'Playfair Display',serif;
            font-size:clamp(1.5rem,3.5vw,2.4rem);
            font-weight:700; line-height:1.3; margin-bottom:0;
        }
        .topic-status-row {
            display:flex; gap:10px; flex-wrap:wrap; margin-top:12px;
        }
        .status-badge {
            display:inline-flex; align-items:center; gap:6px;
            padding:4px 12px; border-radius:20px;
            font-size:11px; font-weight:700;
            letter-spacing:0.4px; text-transform:uppercase;
        }
        .status-epingle { background:rgba(245,158,11,0.2); color:#FCD34D; }
        .status-resolu  { background:rgba(46,125,50,0.2);  color:#86EFAC; }
        .status-ferme   { background:rgba(255,255,255,0.15); color:rgba(255,255,255,0.8); }

        /* ── Breadcrumb ── */
        .breadcrumb-bar { background:var(--white); border-bottom:1px solid var(--border); }
        .breadcrumb {
            display:flex; gap:6px; align-items:center;
            color:var(--gray-text); font-size:13.5px; padding:14px 0;
        }
        .breadcrumb a {
            color:var(--blue); text-decoration:none; font-weight:500;
            transition:color 0.2s; display:flex; align-items:center; gap:5px;
        }
        .breadcrumb a:hover { color:var(--rose); }
        .breadcrumb .sep { color:var(--border); font-size:11px; }
        .breadcrumb .current { color:var(--dark); font-weight:600; }

        /* ── Section ── */
        .sujet-section { padding:40px 0 80px; background:var(--gray-bg); }

        /* ── Layout ── */
        .sujet-layout {
            display:grid;
            grid-template-columns: 1fr 300px;
            gap:28px; align-items:start;
        }

        /* ── Alertes ── */
        .alert {
            border-radius:var(--radius); padding:14px 18px;
            margin-bottom:22px; font-size:14px;
            display:flex; align-items:flex-start; gap:10px;
        }
        .alert-success { background:#F0FDF4; border:1px solid #BBF7D0; color:#166534; }
        .alert-error   { background:#FEF2F2; border:1px solid #FECACA; color:var(--red); }
        .alert ul { margin:6px 0 0 16px; }

        /* ── Message (post) ── */
        .post {
            background:var(--white);
            border-radius:var(--radius);
            border:1px solid var(--border);
            box-shadow:var(--shadow);
            margin-bottom:16px;
            overflow:hidden;
        }
        .post.post-op { border-left:4px solid var(--blue); }
        .post.post-reponse { border-left:4px solid var(--gray-light); }

        .post-header {
            display:flex; align-items:center;
            justify-content:space-between;
            gap:14px; padding:16px 20px;
            background:var(--gray-light);
            border-bottom:1px solid var(--border);
            flex-wrap:wrap;
        }
        .post-author {
            display:flex; align-items:center; gap:12px;
        }
        .post-avatar {
            width:42px; height:42px; border-radius:50%;
            flex-shrink:0; object-fit:cover;
            border:2px solid var(--border);
            background:#F0F0F0;
        }
        .post-author-name {
            font-size:14px; font-weight:700; color:var(--dark);
        }
        .post-author-role {
            font-size:11.5px; color:var(--gray-text); margin-top:1px;
        }
        .post-date {
            font-size:12px; color:var(--gray-text);
            display:flex; align-items:center; gap:5px;
        }
        .post-date i { color:var(--blue); font-size:10px; }

        .post-body {
            padding:22px 24px;
            font-size:15px; color:#374151;
            line-height:1.8;
            white-space:pre-wrap; word-break:break-word;
        }

        /* ── Séparateur réponses ── */
        .replies-heading {
            font-size:16px; font-weight:700;
            color:var(--dark); margin:32px 0 16px;
            display:flex; align-items:center; gap:10px;
            padding-bottom:12px;
            border-bottom:2px solid var(--gray-light);
        }
        .replies-heading i { color:var(--rose); font-size:14px; }
        .replies-heading .count {
            background:var(--blue-soft); color:var(--blue);
            font-size:12px; padding:2px 10px; border-radius:20px;
            font-weight:600;
        }

        /* ── Pagination ── */
        .pagination {
            display:flex; justify-content:center; align-items:center;
            gap:6px; margin:24px 0;
        }
        .page-link {
            display:inline-flex; align-items:center; justify-content:center;
            width:38px; height:38px; border-radius:8px;
            background:var(--white); color:var(--dark);
            text-decoration:none; font-size:14px; font-weight:500;
            border:1px solid var(--border); transition:all 0.2s;
        }
        .page-link:hover, .page-link.active {
            background:var(--blue); color:white;
            border-color:var(--blue);
            box-shadow:0 4px 12px rgba(0,51,153,0.25);
        }

        /* ── Formulaire réponse ── */
        #reponses { scroll-margin-top:90px; }
        .reply-box {
            background:var(--white);
            border-radius:var(--radius);
            border:1px solid var(--border);
            box-shadow:var(--shadow);
            margin-top:28px; overflow:hidden;
        }
        .reply-box-head {
            padding:16px 22px;
            background:var(--gray-light);
            border-bottom:1px solid var(--border);
            font-size:15px; font-weight:700; color:var(--dark);
            display:flex; align-items:center; gap:9px;
        }
        .reply-box-head i { color:var(--blue); }
        .reply-box-body { padding:22px 24px; }

        .field-group { margin-bottom:18px; }
        .field-group label {
            display:block; font-size:14px; font-weight:600;
            color:var(--dark); margin-bottom:7px;
        }
        .field-group textarea {
            width:100%; padding:12px 14px;
            border:1.5px solid var(--border); border-radius:8px;
            font-size:14px; font-family:'Inter',sans-serif;
            color:var(--dark); background:var(--gray-bg);
            line-height:1.65; resize:vertical; min-height:160px;
            transition:border-color 0.2s, background 0.2s;
        }
        .field-group textarea:focus {
            outline:none; border-color:var(--blue); background:var(--white);
        }
        .field-hint { font-size:12px; color:var(--gray-text); margin-top:5px; }

        .btn-submit {
            background:linear-gradient(135deg, var(--blue) 0%, #1a56cc 100%);
            color:white; padding:12px 28px;
            border:none; border-radius:8px;
            font-size:14px; font-weight:600; cursor:pointer;
            display:inline-flex; align-items:center; gap:8px;
            transition:transform 0.22s, box-shadow 0.22s;
        }
        .btn-submit:hover {
            transform:translateY(-2px);
            box-shadow:0 8px 24px rgba(0,51,153,0.28);
        }

        /* Login prompt */
        .login-prompt-reply {
            text-align:center; padding:32px 20px;
            background:var(--gray-bg); border-radius:8px;
        }
        .login-prompt-reply i { font-size:36px; color:var(--border); margin-bottom:14px; display:block; }
        .login-prompt-reply p { color:var(--gray-text); font-size:14px; margin-bottom:16px; }
        .login-prompt-reply a {
            color:white; background:var(--blue);
            padding:10px 22px; border-radius:8px;
            text-decoration:none; font-weight:600; font-size:14px;
            display:inline-flex; align-items:center; gap:7px;
            transition:background 0.2s;
        }
        .login-prompt-reply a:hover { background:var(--blue-dark); }

        /* Sujet fermé */
        .closed-notice {
            background:#FFF8E6; border:1px solid #FCD34D;
            border-radius:10px; padding:16px 18px;
            display:flex; align-items:center; gap:10px;
            color:#92400E; font-size:14px; margin-top:28px;
        }
        .closed-notice i { color:var(--orange); font-size:18px; flex-shrink:0; }

        /* ── Sidebar ── */
        .sujet-sidebar { position:sticky; top:100px; display:flex; flex-direction:column; gap:20px; }
        .sidebar-card {
            background:var(--white); border-radius:var(--radius);
            border:1px solid var(--border); box-shadow:var(--shadow);
            padding:20px 22px;
        }
        .sidebar-card h4 {
            font-size:14px; font-weight:700; color:var(--dark);
            margin-bottom:14px; padding-bottom:10px;
            border-bottom:2px solid var(--gray-light);
            display:flex; align-items:center; gap:8px;
        }
        .sidebar-card h4 i { color:var(--rose); font-size:13px; }
        .sidebar-info-list { list-style:none; }
        .sidebar-info-list li {
            padding:7px 0; border-bottom:1px solid var(--gray-light);
            font-size:13px; color:var(--gray-text);
            display:flex; justify-content:space-between; gap:10px;
        }
        .sidebar-info-list li:last-child { border-bottom:none; }
        .sidebar-info-list li strong { color:var(--dark); }
        .sidebar-actions { display:flex; flex-direction:column; gap:8px; }
        .sidebar-btn {
            display:flex; align-items:center; gap:9px;
            padding:10px 14px; border-radius:8px;
            font-size:13px; font-weight:600; text-decoration:none;
            transition:all 0.2s; cursor:pointer; border:none;
            width:100%;
        }
        .sidebar-btn-blue  { background:var(--blue-soft); color:var(--blue); }
        .sidebar-btn-blue:hover  { background:var(--blue); color:white; }
        .sidebar-btn-rose  { background:rgba(217,79,122,0.08); color:var(--rose); }
        .sidebar-btn-rose:hover  { background:var(--rose); color:white; }

        /* Responsive */
        @media (max-width: 900px) {
            .sujet-layout { grid-template-columns:1fr; }
            .sujet-sidebar { position:static; }
        }
        @media (max-width: 640px) {
            .post-header { flex-direction:column; align-items:flex-start; }
            .forum-header { padding:36px 0 56px; }
        }
    </style>
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <!-- Header -->
    <div class="forum-header">
        <div class="container forum-header-inner">
            <div class="forum-header-eyebrow">
                <i class="fas fa-comments"></i> <?= e($sujet['categorie_nom']) ?>
            </div>
            <h1><?= e($sujet['titre']) ?></h1>
            <div class="topic-status-row">
                <?php if ($sujet['est_epingle']): ?>
                    <span class="status-badge status-epingle"><i class="fas fa-thumbtack"></i> Épinglé</span>
                <?php endif; ?>
                <?php if ($sujet['est_resolu']): ?>
                    <span class="status-badge status-resolu"><i class="fas fa-check-circle"></i> Résolu</span>
                <?php endif; ?>
                <?php if ($sujet['est_ferme']): ?>
                    <span class="status-badge status-ferme"><i class="fas fa-lock"></i> Fermé</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="forum-header-wave">
            <svg viewBox="0 0 1440 48" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path fill="#F4F6FB" d="M0,48 C360,0 1080,0 1440,48 L1440,48 L0,48 Z"/>
            </svg>
        </div>
    </div>

    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <div class="container">
            <nav class="breadcrumb">
                <a href="index.php"><i class="fas fa-home"></i> Accueil</a>
                <i class="fas fa-chevron-right sep"></i>
                <a href="forum.php">Forum</a>
                <i class="fas fa-chevron-right sep"></i>
                <a href="forum-categorie.php?id=<?= $sujet['categorie_id_val'] ?>"><?= e($sujet['categorie_nom']) ?></a>
                <i class="fas fa-chevron-right sep"></i>
                <span class="current"><?= e(mb_strimwidth($sujet['titre'], 0, 50, '…')) ?></span>
            </nav>
        </div>
    </div>

    <!-- Contenu -->
    <section class="sujet-section">
        <div class="container">

            <!-- Alertes -->
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Erreur :</strong>
                    <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
                </div>
            </div>
            <?php endif; ?>

            <div class="sujet-layout">

                <!-- ── Colonne principale ── -->
                <div class="sujet-main">

                    <!-- Message original -->
                    <div class="post post-op" data-aos="fade-up">
                        <div class="post-header">
                            <div class="post-author">
                                <?php
                                    $nom_op  = $sujet['auteur_nom'] ?? 'Membre';
                                    $icon_op = generateIdenticon($nom_op, 42);
                                ?>
                                <img class="post-avatar" src="<?= $icon_op ?>" alt="<?= e($nom_op) ?>" title="<?= e($nom_op) ?>">
                                <div>
                                    <div class="post-author-name"><?= e($sujet['auteur_nom'] ?? 'Membre supprimé') ?></div>
                                    <div class="post-author-role">Auteur du sujet</div>
                                </div>
                            </div>
                            <div class="post-date">
                                <i class="far fa-clock"></i>
                                <?= formatDateFr($sujet['date_creation']) ?>
                            </div>
                        </div>
                        <div class="post-body"><?= htmlspecialchars($sujet['contenu']) ?></div>
                    </div>

                    <!-- Réponses -->
                    <?php if ($total_reponses > 0): ?>
                    <div id="reponses">
                        <div class="replies-heading">
                            <i class="fas fa-reply"></i>
                            Réponses
                            <span class="count"><?= $total_reponses ?></span>
                        </div>

                        <!-- Pagination haut -->
                        <?php if ($total_pages > 1): ?>
                        <div class="pagination" style="margin-top:0; margin-bottom:18px;">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?id=<?= $id ?>&page=<?= $i ?>#reponses"
                                   class="page-link <?= $page === $i ? 'active' : '' ?>"><?= $i ?></a>
                            <?php endfor; ?>
                        </div>
                        <?php endif; ?>

                        <?php foreach ($reponses as $idx => $rep): ?>
                        <div class="post post-reponse" data-aos="fade-up" data-aos-delay="<?= ($idx % 5) * 60 ?>">
                            <div class="post-header">
                                <div class="post-author">
                                    <?php
                                        $nom_rep  = $rep['auteur_nom'] ?? 'Membre';
                                        $icon_rep = generateIdenticon($nom_rep, 42);
                                    ?>
                                    <img class="post-avatar" src="<?= $icon_rep ?>" alt="<?= e($nom_rep) ?>" title="<?= e($nom_rep) ?>">
                                    <div>
                                        <div class="post-author-name"><?= e($rep['auteur_nom'] ?? 'Membre supprimé') ?></div>
                                        <div class="post-author-role">Membre</div>
                                    </div>
                                </div>
                                <div class="post-date">
                                    <i class="far fa-clock"></i>
                                    <?= formatDateFr($rep['date_creation']) ?>
                                    <span style="color:var(--border);">·</span>
                                    #<?= ($offset + $idx + 1) ?>
                                </div>
                            </div>
                            <div class="post-body"><?= htmlspecialchars($rep['contenu']) ?></div>
                        </div>
                        <?php endforeach; ?>

                        <!-- Pagination bas -->
                        <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?id=<?= $id ?>&page=<?= $i ?>#reponses"
                                   class="page-link <?= $page === $i ? 'active' : '' ?>"><?= $i ?></a>
                            <?php endfor; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- ── Zone de réponse ── -->
                    <?php if ($sujet['est_ferme']): ?>
                        <div class="closed-notice">
                            <i class="fas fa-lock"></i>
                            <span>Ce sujet est <strong>fermé</strong>. Il n'est plus possible d'y répondre.</span>
                        </div>

                    <?php elseif (!isLoggedIn()): ?>
                        <div class="reply-box" id="repondre">
                            <div class="reply-box-head"><i class="fas fa-reply"></i> Répondre à ce sujet</div>
                            <div class="reply-box-body">
                                <div class="login-prompt-reply">
                                    <i class="fas fa-lock"></i>
                                    <p>Vous devez être connecté pour répondre à ce sujet.</p>
                                    <a href="connexion.php?redirect=<?= urlencode("forum-sujet.php?id={$id}#repondre") ?>">
                                        <i class="fas fa-sign-in-alt"></i> Se connecter
                                    </a>
                                    &nbsp;
                                    <a href="inscription.php" style="background:var(--gray-light); color:var(--dark); margin-left:8px; padding:10px 22px; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px; display:inline-flex; align-items:center; gap:7px;">
                                        S'inscrire
                                    </a>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="reply-box" id="repondre">
                            <div class="reply-box-head"><i class="fas fa-reply"></i> Votre réponse</div>
                            <div class="reply-box-body">
                                <form method="POST" action="forum-sujet.php?id=<?= $id ?>#repondre">
                                    <input type="hidden" name="action" value="repondre">
                                    <div class="field-group">
                                        <label for="contenu">Message <span style="color:var(--rose);">*</span></label>
                                        <textarea name="contenu" id="contenu"
                                                  placeholder="Partagez votre réponse, expérience ou conseil..."
                                                  required><?= htmlspecialchars($_POST['contenu'] ?? '') ?></textarea>
                                        <div class="field-hint"><i class="fas fa-info-circle"></i> Minimum 10 caractères. Soyez respectueux et constructif.</div>
                                    </div>
                                    <button type="submit" class="btn-submit">
                                        <i class="fas fa-paper-plane"></i> Publier ma réponse
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- ── Sidebar ── -->
                <div class="sujet-sidebar">

                    <!-- Infos du sujet -->
                    <div class="sidebar-card" data-aos="fade-left">
                        <h4><i class="fas fa-info-circle"></i> Informations</h4>
                        <ul class="sidebar-info-list">
                            <li><span>Catégorie</span> <strong><?= e($sujet['categorie_nom']) ?></strong></li>
                            <li><span>Auteur</span> <strong><?= e($sujet['auteur_nom'] ?? '—') ?></strong></li>
                            <li><span>Créé le</span> <strong><?= formatDateFr($sujet['date_creation']) ?></strong></li>
                            <li><span>Réponses</span> <strong><?= number_format($total_reponses) ?></strong></li>
                            <li><span>Vues</span> <strong><?= number_format($sujet['vue_compteur'] ?? 0) ?></strong></li>
                        </ul>
                    </div>

                    <!-- Actions -->
                    <div class="sidebar-card" data-aos="fade-left" data-aos-delay="80">
                        <h4><i class="fas fa-bolt"></i> Actions</h4>
                        <div class="sidebar-actions">
                            <?php if (!$sujet['est_ferme'] && isLoggedIn()): ?>
                            <a href="#repondre" class="sidebar-btn sidebar-btn-blue">
                                <i class="fas fa-reply"></i> Répondre au sujet
                            </a>
                            <?php endif; ?>
                            <a href="nouveau-sujet.php?categorie=<?= $sujet['categorie_id_val'] ?>" class="sidebar-btn sidebar-btn-blue">
                                <i class="fas fa-plus-circle"></i> Nouveau sujet
                            </a>
                            <a href="forum-categorie.php?id=<?= $sujet['categorie_id_val'] ?>" class="sidebar-btn sidebar-btn-rose">
                                <i class="fas fa-arrow-left"></i> Retour à la catégorie
                            </a>
                        </div>
                    </div>

                    <!-- Règles -->
                    <div class="sidebar-card" data-aos="fade-left" data-aos-delay="160">
                        <h4><i class="fas fa-gavel"></i> Règles du forum</h4>
                        <ul style="list-style:none; padding:0; margin:0;">
                            <?php foreach ([
                                'Respectez les autres membres',
                                'Pas de propos discriminatoires',
                                'Pas de spam ou publicité',
                                'Restez dans le sujet',
                                'Signalez les abus',
                            ] as $rule): ?>
                            <li style="padding:6px 0; border-bottom:1px solid var(--gray-light); font-size:13px; color:var(--gray-text); display:flex; align-items:center; gap:8px;">
                                <i class="fas fa-check-circle" style="color:var(--green); font-size:11px; flex-shrink:0;"></i>
                                <?= $rule ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 500, once: true, offset: 40 });
    </script>
</body>
</html>