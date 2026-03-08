<?php
// nouveau-sujet.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Doit être connecté
if (!isLoggedIn()) {
    header('Location: connexion.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$categorie_id = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;
$errors   = [];
$success  = false;

// Charger les catégories disponibles
try {
    $stmt = $pdo->query("SELECT * FROM forum_categories WHERE est_actif = 1 ORDER BY ordre ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Catégorie sélectionnée
$categorie = null;
if ($categorie_id > 0) {
    foreach ($categories as $cat) {
        if ($cat['id'] === $categorie_id) { $categorie = $cat; break; }
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre       = trim($_POST['titre']       ?? '');
    $contenu     = trim($_POST['contenu']     ?? '');
    $cat_id      = (int)($_POST['categorie_id'] ?? 0);
    $user_id     = $_SESSION['user_id'] ?? 0;

    // Validation
    if (strlen($titre) < 5)    $errors[] = 'Le titre doit comporter au moins 5 caractères.';
    if (strlen($titre) > 200)  $errors[] = 'Le titre ne doit pas dépasser 200 caractères.';
    if (strlen($contenu) < 20) $errors[] = 'Le message doit comporter au moins 20 caractères.';
    if ($cat_id <= 0)          $errors[] = 'Veuillez choisir une catégorie.';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO forum_sujets (categorie_id, auteur_id, titre, contenu, date_creation, vue_compteur, est_epingle, est_resolu, est_ferme)
                 VALUES (?, ?, ?, ?, NOW(), 0, 0, 0, 0)"
            );
            $stmt->execute([$cat_id, $user_id, $titre, $contenu]);
            $nouveau_id = $pdo->lastInsertId();

            header('Location: forum-sujet.php?id=' . $nouveau_id . '&nouveau=1');
            exit;
        } catch (PDOException $e) {
            logError("Erreur nouveau-sujet.php: " . $e->getMessage());
            $errors[] = 'Une erreur est survenue. Veuillez réessayer.';
        }
    }
}

$page_title = 'Nouveau sujet';
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

    <style>
        :root {
            --blue:      #003399;
            --blue-dark: #002270;
            --blue-soft: rgba(0,51,153,0.08);
            --rose:      #D94F7A;
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

        /* ── Page header ── */
        .page-header {
            background: linear-gradient(135deg, #003399 0%, #1a56cc 60%, #1a7abf 100%);
            color: white;
            padding: 52px 0 72px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .page-header::before {
            content: ''; position: absolute;
            width: 360px; height: 360px; border-radius: 50%;
            opacity: 0.07; background: white;
            top: -140px; right: -60px; pointer-events: none;
        }
        .page-header-wave {
            position: absolute; bottom: -1px; left: 0;
            width: 100%; line-height: 0;
        }
        .page-header-wave svg { display: block; }
        .header-eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.25);
            color: white; font-size: 11px; font-weight: 600;
            letter-spacing: 2px; text-transform: uppercase;
            padding: 6px 16px; border-radius: 20px; margin-bottom: 16px;
        }
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 700; margin-bottom: 0;
        }

        /* ── Breadcrumb ── */
        .breadcrumb-bar {
            background: var(--white);
            border-bottom: 1px solid var(--border);
        }
        .breadcrumb {
            display: flex; gap: 6px; align-items: center;
            color: var(--gray-text); font-size: 13.5px; padding: 14px 0;
        }
        .breadcrumb a {
            color: var(--blue); text-decoration: none;
            font-weight: 500; transition: color 0.2s;
            display: flex; align-items: center; gap: 5px;
        }
        .breadcrumb a:hover { color: var(--rose); }
        .breadcrumb .sep { color: var(--border); font-size: 11px; }
        .breadcrumb .current { color: var(--dark); font-weight: 600; }

        /* ── Section ── */
        .form-section {
            padding: 48px 0 80px;
            background: var(--gray-bg);
        }
        .form-wrap {
            max-width: 820px; margin: 0 auto;
        }

        /* ── Alertes ── */
        .alert {
            border-radius: var(--radius);
            padding: 14px 18px;
            margin-bottom: 24px;
            font-size: 14px;
            display: flex; align-items: flex-start; gap: 10px;
        }
        .alert-error {
            background: #FEF2F2; border: 1px solid #FECACA;
            color: var(--red);
        }
        .alert-error ul { margin: 6px 0 0 16px; }
        .alert-error i { color: var(--red); margin-top: 2px; }

        /* ── Carte formulaire ── */
        .form-card {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            padding: 36px 40px;
        }

        .form-card h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem; color: var(--dark);
            margin-bottom: 28px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--gray-light);
            display: flex; align-items: center; gap: 10px;
        }
        .form-card h2 i { color: var(--rose); font-size: 1.1rem; }

        /* ── Champs ── */
        .field-group { margin-bottom: 22px; }
        .field-group label {
            display: block;
            font-size: 14px; font-weight: 600;
            color: var(--dark); margin-bottom: 7px;
        }
        .field-group label span.req { color: var(--rose); margin-left: 3px; }

        .field-group input,
        .field-group select,
        .field-group textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            background: var(--gray-bg);
            transition: border-color 0.2s, background 0.2s;
        }
        .field-group input:focus,
        .field-group select:focus,
        .field-group textarea:focus {
            outline: none;
            border-color: var(--blue);
            background: var(--white);
        }
        .field-group textarea {
            min-height: 240px;
            resize: vertical;
            line-height: 1.65;
        }
        .field-group .hint {
            font-size: 12px; color: var(--gray-text);
            margin-top: 5px;
        }
        .field-group .char-count {
            font-size: 11px; color: var(--gray-text);
            text-align: right; margin-top: 4px;
        }

        /* ── Règles de rédaction ── */
        .writing-rules {
            background: var(--blue-soft);
            border: 1px solid rgba(0,51,153,0.15);
            border-radius: 10px;
            padding: 16px 18px;
            margin-bottom: 24px;
        }
        .writing-rules h4 {
            font-size: 13px; font-weight: 700;
            color: var(--blue); margin-bottom: 10px;
            display: flex; align-items: center; gap: 7px;
        }
        .writing-rules ul {
            list-style: none; padding: 0; margin: 0;
        }
        .writing-rules ul li {
            font-size: 13px; color: var(--dark);
            padding: 4px 0;
            display: flex; align-items: flex-start; gap: 7px;
        }
        .writing-rules ul li i {
            color: var(--blue); font-size: 11px; margin-top: 3px; flex-shrink: 0;
        }

        /* ── Boutons ── */
        .form-actions {
            display: flex; gap: 12px;
            align-items: center; margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-light);
        }
        .btn-submit {
            background: linear-gradient(135deg, var(--blue) 0%, #1a56cc 100%);
            color: white;
            padding: 13px 32px;
            border: none; border-radius: 8px;
            font-size: 15px; font-weight: 600;
            cursor: pointer;
            display: inline-flex; align-items: center; gap: 9px;
            transition: transform 0.22s, box-shadow 0.22s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,51,153,0.28);
        }
        .btn-cancel {
            color: var(--gray-text);
            text-decoration: none;
            font-size: 14px; font-weight: 500;
            padding: 13px 20px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--white);
            transition: all 0.2s;
        }
        .btn-cancel:hover {
            background: var(--gray-light);
            color: var(--dark);
        }

        @media (max-width: 640px) {
            .form-card { padding: 24px 20px; }
            .form-actions { flex-wrap: wrap; }
        }
    </style>
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <!-- Header -->
    <div class="page-header">
        <div class="container" style="position:relative;z-index:1;">
            <div class="header-eyebrow">
                <i class="fas fa-comments"></i> Forum communautaire
            </div>
            <h1>Nouveau sujet</h1>
        </div>
        <div class="page-header-wave">
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
                <?php if ($categorie): ?>
                    <i class="fas fa-chevron-right sep"></i>
                    <a href="forum-categorie.php?id=<?= $categorie['id'] ?>"><?= e($categorie['nom']) ?></a>
                <?php endif; ?>
                <i class="fas fa-chevron-right sep"></i>
                <span class="current">Nouveau sujet</span>
            </nav>
        </div>
    </div>

    <!-- Formulaire -->
    <section class="form-section">
        <div class="container">
            <div class="form-wrap">

                <!-- Erreurs -->
                <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Veuillez corriger les erreurs suivantes :</strong>
                        <ul>
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Règles -->
                <div class="writing-rules">
                    <h4><i class="fas fa-info-circle"></i> Règles de bonne rédaction</h4>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Choisissez un titre clair et descriptif</li>
                        <li><i class="fas fa-check-circle"></i> Soyez respectueux envers les autres membres</li>
                        <li><i class="fas fa-check-circle"></i> Vérifiez qu'un sujet similaire n'existe pas déjà</li>
                        <li><i class="fas fa-check-circle"></i> Pas de publicité, ni de propos offensants</li>
                    </ul>
                </div>

                <!-- Formulaire -->
                <div class="form-card">
                    <h2><i class="fas fa-pencil-alt"></i> Créer votre sujet</h2>

                    <form method="POST" action="nouveau-sujet.php<?= $categorie_id ? '?categorie='.$categorie_id : '' ?>" novalidate>

                        <!-- Catégorie -->
                        <div class="field-group">
                            <label for="categorie_id">Catégorie <span class="req">*</span></label>
                            <select name="categorie_id" id="categorie_id" required>
                                <option value="">— Choisissez une catégorie —</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"
                                        <?= ((int)($_POST['categorie_id'] ?? $categorie_id) === (int)$cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Titre -->
                        <div class="field-group">
                            <label for="titre">Titre du sujet <span class="req">*</span></label>
                            <input type="text" name="titre" id="titre"
                                   value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>"
                                   placeholder="Résumez votre question ou sujet en une phrase..."
                                   maxlength="200" required>
                            <div class="char-count"><span id="titre-count">0</span> / 200</div>
                        </div>

                        <!-- Contenu -->
                        <div class="field-group">
                            <label for="contenu">Message <span class="req">*</span></label>
                            <textarea name="contenu" id="contenu"
                                      placeholder="Développez votre sujet, donnez du contexte, posez votre question en détail..."
                                      required><?= htmlspecialchars($_POST['contenu'] ?? '') ?></textarea>
                            <div class="hint"><i class="fas fa-info-circle"></i> Minimum 20 caractères. Soyez le plus précis possible.</div>
                        </div>

                        <!-- Actions -->
                        <div class="form-actions">
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-paper-plane"></i> Publier le sujet
                            </button>
                            <a href="<?= $categorie ? 'forum-categorie.php?id='.$categorie_id : 'forum.php' ?>" class="btn-cancel">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>

    <script>
        // Compteur de caractères pour le titre
        const titreInput = document.getElementById('titre');
        const titreCount = document.getElementById('titre-count');
        function updateTitreCount() {
            titreCount.textContent = titreInput.value.length;
        }
        titreInput.addEventListener('input', updateTitreCount);
        updateTitreCount();
    </script>
</body>
</html>