<?php
// ajouter-temoignage.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isLoggedIn()) {
    setFlashMessage('info', 'Veuillez vous connecter pour partager votre témoignage.');
    redirect('connexion.php');
}

$page_title       = 'Partager mon témoignage';
$page_description = 'Partagez votre expérience avec le GSCC et inspirez d\'autres personnes.';

$errors  = [];
$success = false;

$nom_defaut = trim(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $nom       = trim(strip_tags($_POST['nom']       ?? ''));
        $fonction  = trim(strip_tags($_POST['fonction']  ?? ''));
        $temoignage = trim(strip_tags($_POST['temoignage'] ?? ''));
        $note      = min(5, max(1, (int)($_POST['note'] ?? 5)));

        if (empty($nom)) {
            $errors[] = 'Votre nom est obligatoire.';
        }
        if (empty($fonction)) {
            $errors[] = 'Votre rôle est obligatoire.';
        }
        if (empty($temoignage)) {
            $errors[] = 'Le témoignage ne peut pas être vide.';
        } elseif (mb_strlen($temoignage) < 30) {
            $errors[] = 'Le témoignage doit contenir au moins 30 caractères.';
        }

        $photo = null;
        if (!empty($_FILES['photo']['name'])) {
            $temoDir = ROOT_PATH . 'uploads/temoignages/';
            if (!is_dir($temoDir)) mkdir($temoDir, 0755, true);
            $up = uploadFile($_FILES['photo'], $temoDir, ['jpg', 'jpeg', 'png', 'webp']);
            if ($up['success']) {
                $photo = $up['filename'];
            } else {
                $errors[] = 'Photo : ' . $up['error'];
            }
        }

        if (empty($errors)) {
            try {
                $pdo->prepare(
                    "INSERT INTO temoignages (nom, fonction, temoignage, note, photo, statut, date_creation)
                     VALUES (?, ?, ?, ?, ?, 'en_attente', NOW())"
                )->execute([$nom, $fonction, $temoignage, $note, $photo]);

                setFlashMessage('success', 'Merci pour votre témoignage ! Il sera publié après validation par notre équipe.');
                redirect('temoignages.php');
            } catch (PDOException $e) {
                logError('Erreur ajouter-temoignage.php: ' . $e->getMessage());
                $errors[] = 'Une erreur est survenue. Veuillez réessayer.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — <?= defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'GSCC' ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        :root {
            --blue:      #003399;
            --blue-dark: #001f6b;
            --blue-mid:  #1a56cc;
            --blue-lite: #EBF0FF;
            --rose:      #C8375A;
            --rose-lite: #FDF0F3;
            --gold:      #E8A020;
            --text:      #0D1117;
            --text-2:    #1F2937;
            --muted:     #6B7280;
            --border:    #E5E7EB;
            --bg:        #F8FAFF;
            --white:     #FFFFFF;
        }

        /* ── HERO ── */
        .page-hero {
            background: linear-gradient(135deg, #7B1535 0%, #C8375F 55%, #D94F7A 100%);
            padding: 72px 0 100px;
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        .page-hero::before, .page-hero::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
            pointer-events: none;
        }
        .page-hero::before { width: 460px; height: 460px; top: -120px; right: -80px; }
        .page-hero::after  { width: 280px; height: 280px; bottom: 10px; left: -60px; }
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
            margin-bottom: 20px;
        }
        .page-hero h1 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            color: #fff;
            margin-bottom: 14px;
        }
        .page-hero h1 span { color: #F9A8C4; }
        .page-hero > .container > p {
            font-size: 1rem;
            color: rgba(255,255,255,0.88);
            max-width: 480px;
            margin: 0 auto;
            line-height: 1.8;
        }
        .hero-wave {
            position: absolute;
            bottom: -1px; left: 0;
            width: 100%; line-height: 0; z-index: 1;
        }

        /* ── FORM SECTION ── */
        .form-section {
            padding: 64px 0 80px;
            background: var(--bg);
        }

        .form-card {
            max-width: 700px;
            margin: 0 auto;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 48px 44px;
            box-shadow: 0 4px 24px rgba(0,0,0,.07);
        }

        /* ── ERROR / SUCCESS banners ── */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 28px;
            font-size: 14px;
            font-weight: 500;
        }
        .alert-error {
            background: #FEF2F2;
            border: 1px solid #FCA5A5;
            color: #991B1B;
        }
        .alert-error ul { margin: 6px 0 0 18px; }

        /* ── FORM ELEMENTS ── */
        .form-group { margin-bottom: 22px; }

        label {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text-2);
            margin-bottom: 7px;
        }
        label .req { color: var(--rose); margin-left: 2px; }

        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14.5px;
            color: var(--text);
            background: var(--white);
            transition: border-color .2s, box-shadow .2s;
            font-family: inherit;
            outline: none;
        }
        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            border-color: var(--blue-mid);
            box-shadow: 0 0 0 3px rgba(26,86,204,.12);
        }
        textarea { resize: vertical; min-height: 140px; line-height: 1.7; }

        /* ── STAR RATING ── */
        .star-rating {
            display: flex;
            gap: 6px;
            margin-top: 2px;
        }
        .star-rating input { display: none; }
        .star-rating label {
            font-size: 28px;
            color: var(--border);
            cursor: pointer;
            transition: color .15s, transform .15s;
            margin-bottom: 0;
            font-weight: 400;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: var(--gold);
        }
        .star-rating label:hover { transform: scale(1.15); }

        /* reverse order trick for CSS-only stars */
        .star-rating { flex-direction: row-reverse; justify-content: flex-end; }
        .star-rating label:hover,
        .star-rating label:hover ~ label { color: var(--gold); }
        .star-rating input:checked ~ label { color: var(--gold); }

        /* ── PHOTO UPLOAD ── */
        .file-zone {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
            position: relative;
        }
        .file-zone:hover { border-color: var(--blue-mid); background: var(--blue-lite); }
        .file-zone input[type="file"] {
            position: absolute; inset: 0;
            opacity: 0; cursor: pointer;
            width: 100%; height: 100%;
        }
        .file-zone i { font-size: 28px; color: var(--muted); margin-bottom: 8px; display: block; }
        .file-zone p { font-size: 13.5px; color: var(--muted); margin: 0; line-height: 1.6; }
        .file-zone span { font-size: 12px; color: var(--blue-mid); font-weight: 600; }

        /* ── SUBMIT ── */
        .btn-submit {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, var(--rose), #E05070);
            color: #fff;
            padding: 15px 40px;
            border-radius: 100px;
            border: none;
            cursor: pointer;
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            box-shadow: 0 6px 20px rgba(200,55,90,.38);
            transition: transform .25s, box-shadow .25s;
            text-decoration: none;
        }
        .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(200,55,90,.52); color: #fff; }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            margin-left: 16px;
            transition: color .2s;
        }
        .btn-back:hover { color: var(--blue); }

        .form-note {
            margin-top: 20px;
            font-size: 12.5px;
            color: var(--muted);
            display: flex;
            align-items: flex-start;
            gap: 8px;
            line-height: 1.7;
        }
        .form-note i { color: var(--gold); margin-top: 2px; flex-shrink: 0; }

        /* ── RESPONSIVE ── */
        @media (max-width: 600px) {
            .form-card { padding: 32px 22px; }
        }
    </style>
</head>

<body>
    <?php require_once 'templates/header.php'; ?>

    <section class="page-hero">
        <div class="container">
            <div class="hero-badge">
                <i class="fas fa-pen"></i> Votre parole compte
            </div>
            <h1>Partagez votre <span>témoignage</span></h1>
            <p>
                Votre expérience peut aider et inspirer d'autres personnes touchées par le cancer.
            </p>
        </div>
        <div class="hero-wave">
            <svg viewBox="0 0 1440 64" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path d="M0,32 C360,64 1080,0 1440,32 L1440,64 L0,64 Z" fill="#F8FAFF"/>
            </svg>
        </div>
    </section>

    <section class="form-section">
        <div class="container">
            <div class="form-card">

                <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <strong><i class="fas fa-exclamation-circle"></i> Veuillez corriger les erreurs suivantes :</strong>
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCSRFToken()) ?>">

                    <div class="form-group">
                        <label for="nom">Votre nom <span class="req">*</span></label>
                        <input type="text" id="nom" name="nom" required
                               value="<?= htmlspecialchars($_POST['nom'] ?? $nom_defaut) ?>"
                               placeholder="Prénom Nom (ou initiales si vous préférez)">
                    </div>

                    <div class="form-group">
                        <label for="fonction">Votre rôle <span class="req">*</span></label>
                        <select id="fonction" name="fonction" required>
                            <option value="">— Choisissez votre rôle —</option>
                            <?php
                            $roles = [
                                'Patient accompagné',
                                'Proche de patient',
                                'Bénévole',
                                'Médecin partenaire',
                                'Donateur',
                                'Autre',
                            ];
                            $sel = $_POST['fonction'] ?? '';
                            foreach ($roles as $r) {
                                $selected = ($sel === $r) ? ' selected' : '';
                                echo '<option value="' . htmlspecialchars($r) . '"' . $selected . '>' . htmlspecialchars($r) . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="temoignage">Votre témoignage <span class="req">*</span></label>
                        <textarea id="temoignage" name="temoignage" required
                                  placeholder="Racontez votre expérience avec le GSCC (minimum 30 caractères)..."><?= htmlspecialchars($_POST['temoignage'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Votre note</label>
                        <div class="star-rating">
                            <?php
                            $selected_note = (int)($_POST['note'] ?? 5);
                            for ($i = 5; $i >= 1; $i--):
                            ?>
                                <input type="radio" id="star<?= $i ?>" name="note" value="<?= $i ?>"
                                       <?= ($selected_note === $i) ? 'checked' : '' ?>>
                                <label for="star<?= $i ?>">&#9733;</label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Photo (optionnel)</label>
                        <div class="file-zone">
                            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
                            <i class="fas fa-image"></i>
                            <p>Glissez une photo ou <span>parcourez…</span></p>
                            <p>JPG, PNG, WEBP — max 5 Mo</p>
                        </div>
                    </div>

                    <div style="margin-top:32px; display:flex; align-items:center; flex-wrap:wrap; gap:8px;">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane"></i> Envoyer mon témoignage
                        </button>
                        <a href="temoignages.php" class="btn-back">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>

                    <p class="form-note">
                        <i class="fas fa-shield-halved"></i>
                        Votre témoignage sera relu par notre équipe avant publication. Nous nous réservons le droit de ne pas publier les contenus inappropriés.
                    </p>
                </form>

            </div>
        </div>
    </section>

    <?php require_once 'templates/footer.php'; ?>

    <script src="assets/js/main.js"></script>

    <script>
        // Affiche le nom du fichier sélectionné
        const fileInput = document.querySelector('.file-zone input[type="file"]');
        const fileZone  = document.querySelector('.file-zone');
        if (fileInput && fileZone) {
            fileInput.addEventListener('change', () => {
                const name = fileInput.files[0]?.name;
                if (name) {
                    fileZone.querySelector('p').textContent = name;
                }
            });
        }
    </script>
</body>

</html>
