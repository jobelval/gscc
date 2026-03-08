<?php
// mon-compte.php
require_once 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Rediriger si non connecté
if (!function_exists('isLoggedIn') || !isLoggedIn()) {
    header('Location: connexion.php?redirect=mon-compte.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? 0;
$success = '';
$error   = '';

// ── Charger l'utilisateur depuis la BDD ──────────────────────────────────────
try {
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ? AND statut = 'actif' LIMIT 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user) {
        session_destroy();
        header('Location: connexion.php');
        exit;
    }
} catch (Exception $e) {
    $error = 'Erreur lors du chargement de votre profil.';
    $user  = [];
}

// ── Traitement des formulaires ────────────────────────────────────────────────
$onglet = $_GET['tab'] ?? 'profil';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- Mise à jour profil ---
    if ($action === 'update_profil') {
        $nom        = trim(strip_tags($_POST['nom']        ?? ''));
        $prenom     = trim(strip_tags($_POST['prenom']     ?? ''));
        $telephone  = trim(strip_tags($_POST['telephone']  ?? ''));
        $adresse    = trim(strip_tags($_POST['adresse']    ?? ''));
        $ville      = trim(strip_tags($_POST['ville']      ?? ''));
        $pays       = trim(strip_tags($_POST['pays']       ?? 'Haïti'));
        $profession = trim(strip_tags($_POST['profession'] ?? ''));
        $newsletter = isset($_POST['newsletter']) ? 1 : 0;

        if (empty($nom) || empty($prenom)) {
            $error  = 'Le nom et le prénom sont obligatoires.';
            $onglet = 'profil';
        } else {
            try {
                $pdo->prepare(
                    "UPDATE utilisateurs
                     SET nom=?, prenom=?, telephone=?, adresse=?, ville=?, pays=?, profession=?, newsletter=?
                     WHERE id=?"
                )->execute([$nom, $prenom, $telephone, $adresse, $ville, $pays, $profession, $newsletter, $user_id]);

                // Mettre à jour la session
                $_SESSION['user_nom']    = $nom;
                $_SESSION['user_prenom'] = $prenom;

                // Recharger
                $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();

                $success = 'Votre profil a été mis à jour avec succès.';
                $onglet  = 'profil';
            } catch (Exception $e) {
                $error  = 'Erreur lors de la mise à jour du profil.';
                $onglet = 'profil';
            }
        }
    }

    // --- Changement de mot de passe ---
    if ($action === 'change_password') {
        $actuel     = $_POST['mot_de_passe_actuel']  ?? '';
        $nouveau    = $_POST['nouveau_mot_de_passe'] ?? '';
        $confirmer  = $_POST['confirmer_mot_de_passe'] ?? '';
        $onglet     = 'securite';

        if (empty($actuel) || empty($nouveau) || empty($confirmer)) {
            $error = 'Veuillez remplir tous les champs.';
        } elseif (!password_verify($actuel, $user['mot_de_passe'])) {
            $error = 'Mot de passe actuel incorrect.';
        } elseif (strlen($nouveau) < 8) {
            $error = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
        } elseif ($nouveau !== $confirmer) {
            $error = 'Les mots de passe ne correspondent pas.';
        } else {
            try {
                $hash = password_hash($nouveau, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE utilisateurs SET mot_de_passe=? WHERE id=?")->execute([$hash, $user_id]);
                $success = 'Votre mot de passe a été modifié avec succès.';
            } catch (Exception $e) {
                $error = 'Erreur lors du changement de mot de passe.';
            }
        }
    }
}

// ── Données pour les onglets ──────────────────────────────────────────────────
// Dons — lier par utilisateur_id OU par email (dons faits sans compte)
try {
    // D'abord lier les dons orphelins qui ont le même email
    $pdo->prepare(
        "UPDATE dons SET utilisateur_id = ?
         WHERE email_donateur = ? AND utilisateur_id IS NULL"
    )->execute([$user_id, $user['email']]);

    $stmt = $pdo->prepare(
        "SELECT * FROM dons
         WHERE utilisateur_id = ? OR email_donateur = ?
         ORDER BY date_don DESC LIMIT 20"
    );
    $stmt->execute([$user_id, $user['email']]);
    $dons = $stmt->fetchAll();
} catch (Exception $e) {
    $dons = [];
}

// Sujets forum
try {
    $stmt = $pdo->prepare(
        "SELECT fs.*, fc.nom AS categorie_nom
         FROM forum_sujets fs
         LEFT JOIN forum_categories fc ON fc.id = fs.categorie_id
         WHERE fs.auteur_id = ?
         ORDER BY fs.date_creation DESC LIMIT 10"
    );
    $stmt->execute([$user_id]);
    $sujets = $stmt->fetchAll();
} catch (Exception $e) {
    $sujets = [];
}



$page_title = 'Mon Compte';
$csrf_token = generateCSRFToken();

// Initiales pour avatar
$initiales = strtoupper(
    substr($user['prenom'] ?? 'U', 0, 1) . substr($user['nom'] ?? '', 0, 1)
);

$badge_role = [
    'admin'      => ['label' => 'Administrateur', 'color' => '#003399'],
    'moderateur' => ['label' => 'Modérateur',     'color' => '#7C3AED'],
    'membre'     => ['label' => 'Membre',          'color' => '#059669'],
];
$badge = $badge_role[$user['role'] ?? 'membre'] ?? $badge_role['membre'];

$type_labels = [
    'actif'       => 'Membre Actif',
    'bienfaiteur' => 'Membre Bienfaiteur',
    'honoraire'   => 'Membre Honoraire',
];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> - <?= SITE_NAME ?></title>
    <meta name="robots" content="noindex, nofollow">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        :root {
            --blue: #003399;
            --rose: #D94F7A;
            --teal: #1a7abf;
            --green: #059669;
            --bg: #F4F6FB;
            --card: #ffffff;
            --border: #E5E9F2;
            --text: #1A2240;
            --muted: #6B7280;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
            font-family: 'DM Sans', sans-serif;
            color: var(--text);
            margin: 0;
        }

        /* ── Page header ── */
        .compte-header {
            background: linear-gradient(135deg, var(--blue) 0%, #1a56cc 60%, var(--teal) 100%);
            padding: 50px 0 80px;
            position: relative;
            overflow: hidden;
        }

        .compte-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .compte-header .container {
            position: relative;
            z-index: 1;
        }

        .header-profile {
            display: flex;
            align-items: center;
            gap: 28px;
        }

        .avatar-cercle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            border: 3px solid rgba(255, 255, 255, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
            font-family: 'DM Serif Display', serif;
            backdrop-filter: blur(8px);
        }

        .header-info h1 {
            font-family: 'DM Serif Display', serif;
            font-size: 28px;
            color: white;
            margin: 0 0 6px;
        }

        .header-info p {
            color: rgba(255, 255, 255, 0.78);
            margin: 0 0 10px;
            font-size: 14px;
        }

        .badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.18);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* ── Layout ── */
        .compte-body {
            max-width: 1100px;
            margin: -40px auto 60px;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }

        .compte-grid {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 24px;
            align-items: start;
        }

        /* ── Sidebar nav ── */
        .sidebar-nav {
            background: var(--card);
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 51, 153, .08);
            overflow: hidden;
            position: sticky;
            top: 20px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            color: var(--muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all .2s;
        }

        .sidebar-nav a:hover {
            color: var(--blue);
            background: var(--bg);
        }

        .sidebar-nav a.active {
            color: var(--blue);
            background: rgba(0, 51, 153, .05);
            border-left-color: var(--blue);
            font-weight: 600;
        }

        .sidebar-nav a i {
            width: 18px;
            text-align: center;
            font-size: 15px;
        }

        .sidebar-nav .nav-divider {
            height: 1px;
            background: var(--border);
            margin: 4px 0;
        }

        .sidebar-nav .logout-link {
            color: #DC2626 !important;
        }

        .sidebar-nav .logout-link:hover {
            background: #FEF2F2 !important;
        }

        /* ── Alerte ── */
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        .alert-error {
            background: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        /* ── Cartes ── */
        .card {
            background: var(--card);
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 51, 153, .08);
            overflow: hidden;
        }

        .card-header {
            padding: 20px 28px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
            margin: 0;
            font-family: 'DM Serif Display', serif;
        }

        .card-header .card-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--blue), var(--teal));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 15px;
        }

        .card-body {
            padding: 28px;
        }

        /* ── Formulaire ── */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .form-control {
            padding: 11px 14px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            color: var(--text);
            transition: border-color .2s, box-shadow .2s;
            background: #fff;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(0, 51, 153, .1);
        }

        .form-control:read-only {
            background: var(--bg);
            color: var(--muted);
        }

        /* ── Toggle switch newsletter ── */
        .toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            background: var(--bg);
            border-radius: 10px;
            margin-top: 8px;
        }

        .toggle-row label {
            font-size: 14px;
            font-weight: 500;
            color: var(--text);
            cursor: pointer;
        }

        .toggle-row small {
            color: var(--muted);
            font-size: 12px;
            display: block;
        }

        .toggle {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            inset: 0;
            cursor: pointer;
            background: #CBD5E1;
            border-radius: 24px;
            transition: .3s;
        }

        .slider:before {
            content: '';
            position: absolute;
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background: white;
            border-radius: 50%;
            transition: .3s;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .2);
        }

        input:checked+.slider {
            background: var(--blue);
        }

        input:checked+.slider:before {
            transform: translateX(20px);
        }

        /* ── Boutons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all .2s;
            font-family: 'DM Sans', sans-serif;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--blue), var(--teal));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0, 51, 153, .3);
        }

        .btn-danger {
            background: #FEF2F2;
            color: #DC2626;
            border: 1px solid #FECACA;
        }

        .btn-danger:hover {
            background: #DC2626;
            color: white;
        }

        /* ── Stats cards ── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--card);
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0, 51, 153, .06);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .stat-card .stat-val {
            font-size: 24px;
            font-weight: 700;
            color: var(--text);
            line-height: 1;
        }

        .stat-card .stat-lbl {
            font-size: 12px;
            color: var(--muted);
            margin-top: 3px;
        }

        /* ── Tableaux ── */
        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }

        thead th {
            padding: 10px 14px;
            text-align: left;
            background: var(--bg);
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 1px solid var(--border);
        }

        tbody td {
            padding: 13px 14px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover td {
            background: rgba(0, 51, 153, .02);
        }

        .pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .pill-success {
            background: #ECFDF5;
            color: #065F46;
        }

        .pill-warning {
            background: #FFFBEB;
            color: #92400E;
        }

        .pill-danger {
            background: #FEF2F2;
            color: #991B1B;
        }

        .pill-info {
            background: #EFF6FF;
            color: #1D4ED8;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--muted);
        }

        .empty-state i {
            font-size: 40px;
            opacity: .3;
            margin-bottom: 12px;
            display: block;
        }

        .empty-state p {
            margin: 0;
            font-size: 14px;
        }

        /* ── Sécurité ── */
        .security-card {
            background: var(--bg);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .security-card .sec-info {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .security-card .sec-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: var(--blue);
            box-shadow: 0 2px 8px rgba(0, 51, 153, .1);
        }

        .security-card .sec-title {
            font-weight: 600;
            font-size: 14px;
        }

        .security-card .sec-desc {
            font-size: 12px;
            color: var(--muted);
            margin-top: 2px;
        }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .compte-grid {
                grid-template-columns: 1fr;
            }

            .sidebar-nav {
                position: static;
                display: flex;
                flex-wrap: wrap;
            }

            .sidebar-nav a {
                flex: 1;
                min-width: 100px;
                border-left: none;
                border-bottom: 3px solid transparent;
                justify-content: center;
                flex-direction: column;
                gap: 4px;
                font-size: 11px;
                padding: 12px 8px;
            }

            .sidebar-nav a.active {
                border-bottom-color: var(--blue);
                border-left: none;
            }

            .sidebar-nav .nav-divider {
                display: none;
            }
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .stats-row {
                grid-template-columns: 1fr;
            }

            .header-profile {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <?php include 'templates/header.php'; ?>

    <!-- ── Header ── -->
    <div class="compte-header">
        <div class="container">
            <div class="header-profile">
                <div class="avatar-cercle"><?= $initiales ?></div>
                <div class="header-info">
                    <h1><?= e(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?></h1>
                    <p><i class="fas fa-envelope" style="margin-right:6px;opacity:.7"></i><?= e($user['email'] ?? '') ?></p>
                    <div class="badges">
                        <span class="badge"><i class="fas fa-shield-alt"></i> <?= $badge['label'] ?></span>
                        <span class="badge"><i class="fas fa-id-card"></i> <?= $type_labels[$user['type_membre'] ?? 'actif'] ?? 'Membre' ?></span>
                        <?php if ($user['derniere_connexion']): ?>
                            <span class="badge"><i class="fas fa-clock"></i> Connecté le <?= date('d/m/Y', strtotime($user['derniere_connexion'])) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Body ── -->
    <div class="compte-body">
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
        <?php endif; ?>

        <div class="compte-grid">

            <!-- ── Sidebar ── -->
            <nav class="sidebar-nav">
                <a href="?tab=profil" class="<?= $onglet === 'profil'         ? 'active' : '' ?>"><i class="fas fa-user"></i> Mon profil</a>
                <a href="?tab=dons" class="<?= $onglet === 'dons'           ? 'active' : '' ?>"><i class="fas fa-hand-holding-heart"></i> Mes dons</a>

                <a href="?tab=forum" class="<?= $onglet === 'forum'          ? 'active' : '' ?>"><i class="fas fa-comments"></i> Forum</a>
                <a href="?tab=securite" class="<?= $onglet === 'securite'       ? 'active' : '' ?>"><i class="fas fa-lock"></i> Sécurité</a>
                <div class="nav-divider"></div>
                <?php if (($user['role'] ?? '') === 'admin'): ?>
                    <a href="admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Administration</a>
                    <div class="nav-divider"></div>
                <?php endif; ?>
                <a href="deconnexion.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Se déconnecter</a>
            </nav>

            <!-- ── Contenu ── -->
            <div class="content-area">

                <?php // ════════════════ ONGLET PROFIL ════════════════
                if ($onglet === 'profil'): ?>

                    <!-- Stats rapides -->
                    <div class="stats-row">
                        <div class="stat-card">
                            <div class="stat-icon" style="background:linear-gradient(135deg,#D94F7A22,#D94F7A44)">
                                <i class="fas fa-hand-holding-heart" style="color:#D94F7A"></i>
                            </div>
                            <div>
                                <div class="stat-val"><?= count($dons) ?></div>
                                <div class="stat-lbl">Don(s) effectué(s)</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon" style="background:linear-gradient(135deg,#003399,#1a56cc)">
                                <i class="fas fa-comments" style="color:white"></i>
                            </div>
                            <div>
                                <div class="stat-val"><?= count($sujets) ?></div>
                                <div class="stat-lbl">Sujet(s) du forum</div>
                            </div>
                        </div>

                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon"><i class="fas fa-user-edit"></i></div>
                            <h2>Modifier mon profil</h2>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_profil">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>Prénom *</label>
                                        <input type="text" name="prenom" class="form-control"
                                            value="<?= e($user['prenom'] ?? '') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Nom *</label>
                                        <input type="text" name="nom" class="form-control"
                                            value="<?= e($user['nom'] ?? '') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Adresse email</label>
                                        <input type="email" class="form-control"
                                            value="<?= e($user['email'] ?? '') ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Téléphone</label>
                                        <input type="tel" name="telephone" class="form-control"
                                            value="<?= e($user['telephone'] ?? '') ?>"
                                            placeholder="+509 XXXX XXXX">
                                    </div>
                                    <div class="form-group full">
                                        <label>Adresse</label>
                                        <input type="text" name="adresse" class="form-control"
                                            value="<?= e($user['adresse'] ?? '') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Ville</label>
                                        <input type="text" name="ville" class="form-control"
                                            value="<?= e($user['ville'] ?? '') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Pays</label>
                                        <input type="text" name="pays" class="form-control"
                                            value="<?= e($user['pays'] ?? 'Haïti') ?>">
                                    </div>
                                    <div class="form-group full">
                                        <label>Profession</label>
                                        <input type="text" name="profession" class="form-control"
                                            value="<?= e($user['profession'] ?? '') ?>"
                                            placeholder="Votre profession (optionnel)">
                                    </div>
                                </div>

                                <div class="toggle-row" style="margin-top:16px">
                                    <div>
                                        <label for="newsletter_toggle">Newsletter GSCC</label>
                                        <small>Recevoir nos actualités par email</small>
                                    </div>
                                    <label class="toggle">
                                        <input type="checkbox" id="newsletter_toggle" name="newsletter"
                                            <?= ($user['newsletter'] ?? 0) ? 'checked' : '' ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>

                                <div style="margin-top:24px">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Enregistrer les modifications
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                <?php // ════════════════ ONGLET DONS ════════════════
                elseif ($onglet === 'dons'): ?>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon"><i class="fas fa-hand-holding-heart"></i></div>
                            <h2>Mes dons</h2>
                        </div>
                        <div class="card-body">
                            <?php if (empty($dons)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-hand-holding-heart"></i>
                                    <p>Vous n'avez pas encore effectué de don.</p>
                                    <a href="faire-un-don.php" class="btn btn-primary" style="margin-top:16px">
                                        <i class="fas fa-heart"></i> Faire un don
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-wrap">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Montant</th>
                                                <th>Type</th>
                                                <th>Mode</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dons as $don): ?>
                                                <?php
                                                $statut_pill = match ($don['statut']) {
                                                    'complete'   => 'pill-success',
                                                    'en_attente' => 'pill-warning',
                                                    'echoue'     => 'pill-danger',
                                                    'rembourse'  => 'pill-info',
                                                    default      => 'pill-info'
                                                };
                                                $statut_label = match ($don['statut']) {
                                                    'complete'   => 'Complété',
                                                    'en_attente' => 'En attente',
                                                    'echoue'     => 'Échoué',
                                                    'rembourse'  => 'Remboursé',
                                                    default      => $don['statut']
                                                };
                                                ?>
                                                <tr>
                                                    <td><?= date('d/m/Y', strtotime($don['date_don'])) ?></td>
                                                    <td><strong><?= number_format($don['montant'], 2) ?> USD</strong></td>
                                                    <td><?= ucfirst($don['type_don'] ?? '') ?></td>
                                                    <td><?= ucfirst($don['mode_paiement'] ?? '') ?></td>
                                                    <td><span class="pill <?= $statut_pill ?>"><?= $statut_label ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div style="margin-top:20px">
                                    <a href="faire-un-don.php" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Faire un nouveau don
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>



                <?php // ════════════════ ONGLET FORUM ════════════════
                elseif ($onglet === 'forum'): ?>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon"><i class="fas fa-comments"></i></div>
                            <h2>Mes sujets du forum</h2>
                        </div>
                        <div class="card-body">
                            <?php if (empty($sujets)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-comments"></i>
                                    <p>Vous n'avez pas encore créé de sujet.</p>
                                    <a href="forum.php" class="btn btn-primary" style="margin-top:16px">
                                        <i class="fas fa-plus"></i> Créer un sujet
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-wrap">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Titre</th>
                                                <th>Catégorie</th>
                                                <th>Date</th>
                                                <th>Vues</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($sujets as $sujet): ?>
                                                <tr>
                                                    <td>
                                                        <a href="forum-sujet.php?id=<?= $sujet['id'] ?>"
                                                            style="color:var(--blue);font-weight:600;text-decoration:none;">
                                                            <?= e($sujet['titre']) ?>
                                                        </a>
                                                    </td>
                                                    <td><?= e($sujet['categorie_nom'] ?? '—') ?></td>
                                                    <td><?= date('d/m/Y', strtotime($sujet['date_creation'])) ?></td>
                                                    <td><?= (int)$sujet['vue_compteur'] ?></td>
                                                    <td>
                                                        <?php if ($sujet['est_resolu']): ?>
                                                            <span class="pill pill-success">Résolu</span>
                                                        <?php elseif ($sujet['est_epingle']): ?>
                                                            <span class="pill pill-warning">Épinglé</span>
                                                        <?php else: ?>
                                                            <span class="pill pill-info">Ouvert</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php // ════════════════ ONGLET SÉCURITÉ ════════════════
                elseif ($onglet === 'securite'): ?>

                    <!-- Infos sécurité -->
                    <div class="card" style="margin-bottom:24px">
                        <div class="card-header">
                            <div class="card-icon"><i class="fas fa-shield-alt"></i></div>
                            <h2>Sécurité du compte</h2>
                        </div>
                        <div class="card-body">
                            <div class="security-card">
                                <div class="sec-info">
                                    <div class="sec-icon"><i class="fas fa-envelope"></i></div>
                                    <div>
                                        <div class="sec-title">Adresse email</div>
                                        <div class="sec-desc"><?= e($user['email'] ?? '') ?></div>
                                    </div>
                                </div>
                                <span class="pill pill-success">Vérifiée</span>
                            </div>
                            <div class="security-card">
                                <div class="sec-info">
                                    <div class="sec-icon"><i class="fas fa-calendar"></i></div>
                                    <div>
                                        <div class="sec-title">Membre depuis</div>
                                        <div class="sec-desc"><?= $user['date_inscription'] ? date('d F Y', strtotime($user['date_inscription'])) : '—' ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php if ($user['derniere_connexion']): ?>
                                <div class="security-card">
                                    <div class="sec-info">
                                        <div class="sec-icon"><i class="fas fa-sign-in-alt"></i></div>
                                        <div>
                                            <div class="sec-title">Dernière connexion</div>
                                            <div class="sec-desc"><?= date('d/m/Y à H:i', strtotime($user['derniere_connexion'])) ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Changer mot de passe -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon"><i class="fas fa-key"></i></div>
                            <h2>Changer le mot de passe</h2>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="change_password">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                                <div class="form-grid">
                                    <div class="form-group full">
                                        <label>Mot de passe actuel *</label>
                                        <input type="password" name="mot_de_passe_actuel"
                                            class="form-control" required autocomplete="current-password">
                                    </div>
                                    <div class="form-group">
                                        <label>Nouveau mot de passe *</label>
                                        <input type="password" name="nouveau_mot_de_passe"
                                            class="form-control" required autocomplete="new-password"
                                            placeholder="8 caractères minimum">
                                    </div>
                                    <div class="form-group">
                                        <label>Confirmer le nouveau mot de passe *</label>
                                        <input type="password" name="confirmer_mot_de_passe"
                                            class="form-control" required autocomplete="new-password">
                                    </div>
                                </div>

                                <div style="margin-top:24px; display:flex; gap:12px; align-items:center; flex-wrap:wrap">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-lock"></i> Mettre à jour le mot de passe
                                    </button>
                                    <span style="font-size:12px;color:var(--muted)">
                                        <i class="fas fa-info-circle"></i> Minimum 8 caractères
                                    </span>
                                </div>
                            </form>

                            <div style="margin-top:32px;padding-top:24px;border-top:1px solid var(--border)">
                                <p style="font-size:13px;color:var(--muted);margin:0 0 12px">
                                    <i class="fas fa-exclamation-triangle" style="color:#D97706"></i>
                                    Zone de danger — cette action est irréversible
                                </p>
                                <a href="deconnexion.php" class="btn btn-danger">
                                    <i class="fas fa-sign-out-alt"></i> Se déconnecter
                                </a>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>
            </div><!-- /content-area -->
        </div><!-- /compte-grid -->
    </div><!-- /compte-body -->

    <?php include 'templates/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>

</html>