<?php
// inscription.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (isLoggedIn()) redirect('index.php');

$error   = '';
$success = '';

// ── Construction URL Google OAuth ────────────────────────────
$google_auth_url = '';
if (defined('GOOGLE_CLIENT_ID') && defined('GOOGLE_REDIRECT_URI')) {
    $_SESSION['oauth_state'] = bin2hex(random_bytes(16));
    $params = http_build_query([
        'client_id'     => GOOGLE_CLIENT_ID,
        'redirect_uri'  => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope'         => 'openid email profile',
        'state'         => $_SESSION['oauth_state'],
        'prompt'        => 'select_account',
    ]);
    $google_auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;
}

// ── Traitement du formulaire POST ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $email            = filter_var(trim($_POST['email']             ?? ''), FILTER_VALIDATE_EMAIL);
        $password         = $_POST['password']                          ?? '';
        $confirm_password = $_POST['confirm_password']                  ?? '';
        $nom              = sanitize($_POST['nom']                      ?? '');
        $prenom           = sanitize($_POST['prenom']                   ?? '');
        $telephone        = sanitize($_POST['telephone']                ?? '');
        $conditions       = isset($_POST['conditions']);

        if (!$email) {
            $error = 'Adresse email invalide.';
        } elseif (empty($nom) || empty($prenom)) {
            $error = 'Le nom et le prénom sont obligatoires.';
        } elseif (!preg_match("/^[\p{L}\s'\-]{2,60}$/u", $nom)) {
            $error = 'Le nom ne doit contenir que des lettres.';
        } elseif (!preg_match("/^[\p{L}\s'\-]{2,60}$/u", $prenom)) {
            $error = 'Le prénom ne doit contenir que des lettres.';
        } elseif (!empty($telephone) && !preg_match('/^[0-9+\s\-().]{7,20}$/', $telephone)) {
            $error = 'Numéro de téléphone invalide.';
        } elseif (strlen($password) < 8) {
            $error = 'Le mot de passe doit contenir au moins 8 caractères.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $error = 'Le mot de passe doit contenir au moins une lettre majuscule.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $error = 'Le mot de passe doit contenir au moins un chiffre.';
        } elseif ($password !== $confirm_password) {
            $error = 'Les mots de passe ne correspondent pas.';
        } elseif (!$conditions) {
            $error = "Vous devez accepter les conditions d'utilisation.";
        } else {
            // ── registerUser() est défini dans includes/functions.php ──
            $result = registerUser($nom, $prenom, $email, $password, $telephone);

            if ($result['success']) {
                // Email de bienvenue
                $site_name = defined('SITE_NAME') ? SITE_NAME : 'GSCC';
                $site_url  = defined('SITE_URL')  ? SITE_URL  : 'https://gscchaiti.org';

                $subject = "Bienvenue sur $site_name — Votre compte a été créé";
                $body    = "Bonjour $prenom,\n\n";
                $body   .= "Votre compte sur $site_name a bien été créé.\n\n";
                $body   .= "Vous pouvez maintenant vous connecter à :\n";
                $body   .= "$site_url/connexion.php\n\n";
                $body   .= "Identifiant : $email\n\n";
                $body   .= "Merci de faire partie de la communauté GSCC.\n\n";
                $body   .= "— L'équipe $site_name";

                if (function_exists('sendEmail')) {
                    sendEmail($email, $subject, $body);
                }

                $success = "Bienvenue <strong>"
                    . htmlspecialchars($prenom . ' ' . $nom)
                    . "</strong>&nbsp;! Votre compte a bien été créé.";

            } elseif (!empty($result['error']) && str_contains($result['error'], 'déjà utilisée')) {
                $error = 'Cette adresse email est déjà utilisée. '
                    . '<a href="connexion.php">Connectez-vous</a> ou '
                    . '<a href="mot-de-passe-oublie.php">réinitialisez votre mot de passe</a>.';
            } else {
                $error = $result['error'] ?? "Erreur lors de l'inscription. Veuillez réessayer.";
            }
        }
    }
}

$csrf_token = generateCSRFToken();

// Valeurs à conserver en cas d'erreur
$val_nom    = htmlspecialchars($_POST['nom']       ?? '');
$val_prenom = htmlspecialchars($_POST['prenom']    ?? '');
$val_email  = htmlspecialchars($_POST['email']     ?? '');
$val_tel    = htmlspecialchars($_POST['telephone'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — <?= defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'GSCC' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --blue:       #003399;
            --blue-dark:  #002277;
            --blue-lite:  #EBF0FF;
            --green:      #4CAF50;
            --green-dark: #388E3C;
            --pink:       #FF69B4;
            --bg:         #F0F4FB;
            --white:      #FFFFFF;
            --text:       #1A1A2E;
            --text-2:     #4A4A6A;
            --text-3:     #9A9AB0;
            --border:     #DDE3F0;
            --ease:       cubic-bezier(.4, 0, .2, 1);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        /* ── Page ───────────────────────────────────────── */
        .auth-page {
            min-height: calc(100vh - 140px);
            display: flex; align-items: center; justify-content: center;
            padding: 52px 20px;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(0,51,153,.07) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(76,175,80,.06) 0%, transparent 55%),
                var(--bg);
        }

        /* ── Carte ──────────────────────────────────────── */
        .auth-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow:
                0 2px 4px rgba(0,51,153,.04),
                0 12px 32px rgba(0,51,153,.08),
                0 40px 80px rgba(0,51,153,.05);
            width: 100%;
            max-width: 520px;
            overflow: hidden;
            animation: cardIn .5s var(--ease) both;
        }

        @keyframes cardIn {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .auth-ribbon {
            height: 5px;
            background: linear-gradient(90deg, #003399 0%, #FF69B4 50%, #4CAF50 100%);
        }

        .auth-body { padding: 38px 40px 30px; }

        /* ── Marque ─────────────────────────────────────── */
        .auth-brand {
            display: flex; align-items: center; justify-content: center;
            gap: 12px; margin-bottom: 26px;
        }
        .brand-icon {
            width: 50px; height: 50px; border-radius: 13px;
            background: linear-gradient(135deg, #003399 0%, #4CAF50 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; color: #fff; flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(0,51,153,.3);
        }
        .brand-text strong { display: block; font-size: 1.05rem; font-weight: 700; color: var(--blue); }
        .brand-text span   { font-size: 10.5px; color: var(--text-3); }

        .auth-title {
            font-size: 1.65rem; font-weight: 700; color: var(--text);
            text-align: center; margin-bottom: 4px; letter-spacing: -.3px;
        }
        .auth-sub {
            font-size: 14px; color: var(--text-3);
            text-align: center; margin-bottom: 26px;
        }

        /* ── Alertes ────────────────────────────────────── */
        .alert {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 13px 16px; border-radius: 10px;
            margin-bottom: 20px; font-size: 13.5px; line-height: 1.6;
        }
        .alert i { font-size: 15px; margin-top: 2px; flex-shrink: 0; }
        .alert-error   { background:#FEF2F2; color:#991B1B; border:1px solid #FECACA; }
        .alert-success { background:#F0FDF4; color:#166534; border:1px solid #BBF7D0; }
        .alert a { color: inherit; font-weight: 700; }

        /* ── Bouton Google ──────────────────────────────── */
        .btn-google {
            width: 100%;
            display: flex; align-items: center; justify-content: center; gap: 12px;
            padding: 13px 20px;
            background: var(--white); color: var(--text);
            border: 1.5px solid var(--border); border-radius: 11px;
            font-family: 'Inter', sans-serif; font-size: 14.5px; font-weight: 600;
            cursor: pointer; text-decoration: none;
            transition: all .25s var(--ease);
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            margin-bottom: 20px;
        }
        .btn-google:hover {
            background: #F8FAFF; border-color: #A0B4E0;
            box-shadow: 0 4px 16px rgba(0,0,0,.10);
            transform: translateY(-1px);
        }
        .btn-google:active { transform: translateY(0); }
        .google-logo { width: 20px; height: 20px; flex-shrink: 0; }

        .google-disabled {
            width: 100%;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            padding: 13px 20px;
            background: #F9FAFB; color: var(--text-3);
            border: 1.5px dashed var(--border); border-radius: 11px;
            font-size: 13.5px; margin-bottom: 20px; cursor: not-allowed;
        }

        /* ── Séparateur ─────────────────────────────────── */
        .divider {
            display: flex; align-items: center; gap: 12px; margin-bottom: 20px;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: var(--border);
        }
        .divider span { font-size: 12px; font-weight: 500; color: var(--text-3); white-space: nowrap; }

        /* ── Grille 2 colonnes ──────────────────────────── */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        /* ── Champs ─────────────────────────────────────── */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: var(--text-2); }
        .form-group label .req { color: var(--blue); }

        .field-wrap { position: relative; }
        .field-icon {
            position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
            color: var(--text-3); font-size: 14px; pointer-events: none; transition: color .2s;
        }
        .field-wrap:focus-within .field-icon { color: var(--blue); }

        .form-control {
            width: 100%; padding: 11px 42px 11px 38px;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-family: 'Inter', sans-serif; font-size: 14px; color: var(--text);
            background: #F7F9FF; outline: none; transition: all .25s var(--ease);
        }
        .form-control:focus {
            border-color: var(--blue); background: var(--white);
            box-shadow: 0 0 0 3px rgba(0,51,153,.09);
        }
        .form-control::placeholder { color: #C0C4D6; }
        .form-control.valid   { border-color: var(--green); }
        .form-control.invalid { border-color: #EF4444; }
        /* Champ sans bouton toggle (pas de padding droit excessif) */
        .form-control.no-icon-right { padding-right: 14px; }

        .toggle-pw {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--text-3); font-size: 14px; transition: color .2s; padding: 3px;
        }
        .toggle-pw:hover { color: var(--blue); }

        /* ── Force du mot de passe ──────────────────────── */
        .pw-meter { margin-top: 8px; }
        .pw-bars  { display: flex; gap: 4px; margin-bottom: 5px; }
        .pw-bar   {
            flex: 1; height: 3px; border-radius: 99px;
            background: var(--border); transition: background .3s var(--ease);
        }
        .pw-bar.weak   { background: #EF4444; }
        .pw-bar.fair   { background: #F59E0B; }
        .pw-bar.good   { background: #3B82F6; }
        .pw-bar.strong { background: var(--green); }

        .pw-label { font-size: 11.5px; color: var(--text-3); display: flex; align-items: center; gap: 5px; }
        .pw-label.weak   { color: #EF4444; }
        .pw-label.fair   { color: #D97706; }
        .pw-label.good   { color: #2563EB; }
        .pw-label.strong { color: var(--green-dark); }

        /* ── Correspondance mot de passe ────────────────── */
        .pw-match { font-size: 12px; margin-top: 6px; display: flex; align-items: center; gap: 5px; }
        .pw-match.ok  { color: var(--green-dark); }
        .pw-match.bad { color: #EF4444; }

        /* ── Checkbox conditions ────────────────────────── */
        .checkbox-row {
            display: flex; align-items: flex-start; gap: 9px;
            margin-bottom: 18px; font-size: 13.5px; color: var(--text-2);
        }
        .checkbox-row input[type="checkbox"] {
            width: 17px; height: 17px; flex-shrink: 0; margin-top: 2px;
            accent-color: var(--blue); cursor: pointer;
        }
        .checkbox-row a { color: var(--blue); font-weight: 600; }

        /* ── Bouton soumettre ───────────────────────────── */
        .btn-submit {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #003399 0%, #4CAF50 100%);
            color: #fff; border: none; border-radius: 11px;
            font-family: 'Inter', sans-serif; font-size: 15px; font-weight: 700;
            cursor: pointer; letter-spacing: .2px;
            display: flex; align-items: center; justify-content: center; gap: 9px;
            box-shadow: 0 4px 18px rgba(0,51,153,.25);
            transition: all .3s var(--ease);
        }
        .btn-submit:hover  { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,51,153,.35); }
        .btn-submit:active { transform: translateY(0); }

        /* ── Bloc succès ────────────────────────────────── */
        .success-block { text-align: center; padding: 24px 0 16px; }
        .success-block .success-icon {
            width: 68px; height: 68px; border-radius: 50%;
            background: linear-gradient(135deg, #4CAF50, #22C55E);
            display: flex; align-items: center; justify-content: center;
            font-size: 30px; color: #fff; margin: 0 auto 18px;
            box-shadow: 0 6px 22px rgba(76,175,80,.35);
            animation: popIn .45s var(--ease) both;
        }
        @keyframes popIn {
            from { transform: scale(.6); opacity:0; }
            to   { transform: scale(1);  opacity:1; }
        }
        .success-block h2 { font-size: 1.3rem; font-weight: 700; color: var(--text); margin-bottom: 10px; }
        .success-block p  { font-size: 14px; color: var(--text-2); margin-bottom: 22px; }
        .btn-go {
            display: inline-flex; align-items: center; gap: 9px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #003399 0%, #4CAF50 100%);
            color: #fff; border-radius: 11px; text-decoration: none;
            font-weight: 700; font-size: 14.5px;
            box-shadow: 0 4px 14px rgba(0,51,153,.25);
            transition: all .3s var(--ease);
        }
        .btn-go:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(0,51,153,.35); }

        /* ── Pied de carte ──────────────────────────────── */
        .auth-footer {
            border-top: 1px solid var(--border);
            padding: 20px 40px; text-align: center;
            font-size: 13.5px; color: var(--text-3);
            background: #FAFBFF;
        }
        .auth-footer a { color: var(--blue); font-weight: 600; text-decoration: none; }
        .auth-footer a:hover { text-decoration: underline; }

        /* ── Responsive ─────────────────────────────────── */
        @media (max-width: 540px) {
            .auth-body   { padding: 26px 18px 20px; }
            .auth-footer { padding: 18px; }
            .form-row    { grid-template-columns: 1fr; gap: 0; }
        }
    </style>
</head>

<body>
    <?php require_once 'templates/header.php'; ?>

    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-ribbon"></div>
            <div class="auth-body">

                <!-- Marque GSCC -->
                <div class="auth-brand">
                    <div class="brand-icon"><i class="fas fa-ribbon"></i></div>
                    <div class="brand-text">
                        <strong>GSCC</strong>
                        <span>Groupe de Support Contre le Cancer</span>
                    </div>
                </div>

                <h1 class="auth-title">Créer un compte</h1>
                <p class="auth-sub">Rejoignez la communauté GSCC gratuitement</p>

                <!-- ════════════════════════════════════════
                     BLOC SUCCÈS (remplace le formulaire)
                     ════════════════════════════════════════ -->
                <?php if ($success): ?>

                    <div class="success-block">
                        <div class="success-icon"><i class="fas fa-check"></i></div>
                        <h2>Inscription réussie !</h2>
                        <p><?= $success ?></p>
                        <a href="index.php" class="btn-go">
                            <i class="fas fa-home"></i> Aller à l'accueil
                        </a>
                    </div>
                    <script>
                        // Redirection automatique dans 3 secondes
                        setTimeout(() => { window.location.href = 'index.php'; }, 3000);
                    </script>

                <?php else: ?>

                    <!-- Alerte erreur -->
                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-triangle-exclamation"></i>
                            <span><?= $error /* HTML contrôlé, contient des liens */ ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- ══════════════════════════════════════
                         BOUTON GOOGLE
                         ══════════════════════════════════════ -->
                    <?php if ($google_auth_url): ?>
                        <a href="<?= htmlspecialchars($google_auth_url) ?>" class="btn-google">
                            <!-- Logo Google officiel SVG -->
                            <svg class="google-logo" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                            </svg>
                            S'inscrire avec Google
                        </a>
                    <?php else: ?>
                        <div class="google-disabled" title="Inscription Google non configurée">
                            <i class="fas fa-circle-info"></i>
                            Inscription Google non disponible pour l'instant
                        </div>
                    <?php endif; ?>

                    <!-- Séparateur -->
                    <div class="divider">
                        <span>ou créez un compte avec votre email</span>
                    </div>

                    <!-- ══════════════════════════════════════
                         FORMULAIRE D'INSCRIPTION
                         ══════════════════════════════════════ -->
                    <form method="POST" action="" id="regForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                        <!-- Nom / Prénom -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nom">Nom <span class="req">*</span></label>
                                <div class="field-wrap">
                                    <i class="fas fa-user field-icon"></i>
                                    <input type="text" class="form-control no-icon-right" id="nom" name="nom"
                                        placeholder="Dupont" autocomplete="family-name"
                                        value="<?= $val_nom ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="prenom">Prénom <span class="req">*</span></label>
                                <div class="field-wrap">
                                    <i class="fas fa-user field-icon"></i>
                                    <input type="text" class="form-control no-icon-right" id="prenom" name="prenom"
                                        placeholder="Marie" autocomplete="given-name"
                                        value="<?= $val_prenom ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label for="email">Adresse email <span class="req">*</span></label>
                            <div class="field-wrap">
                                <i class="fas fa-envelope field-icon"></i>
                                <input type="email" class="form-control no-icon-right" id="email" name="email"
                                    placeholder="votre@email.com" autocomplete="email"
                                    value="<?= $val_email ?>" required>
                            </div>
                        </div>

                        <!-- Téléphone -->
                        <div class="form-group">
                            <label for="telephone">
                                Téléphone
                                <span style="font-weight:400;color:var(--text-3)">(optionnel)</span>
                            </label>
                            <div class="field-wrap">
                                <i class="fas fa-phone field-icon"></i>
                                <input type="tel" class="form-control no-icon-right" id="telephone" name="telephone"
                                    placeholder="+509 37 00 00 00" autocomplete="tel"
                                    value="<?= $val_tel ?>">
                            </div>
                        </div>

                        <!-- Mot de passe -->
                        <div class="form-group">
                            <label for="password">Mot de passe <span class="req">*</span></label>
                            <div class="field-wrap">
                                <i class="fas fa-lock field-icon"></i>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Min. 8 car., 1 majuscule, 1 chiffre"
                                    autocomplete="new-password" required>
                                <button type="button" class="toggle-pw" onclick="togglePw('password',this)" title="Afficher/masquer">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <!-- Indicateur de force -->
                            <div class="pw-meter" id="pwMeter" style="display:none">
                                <div class="pw-bars">
                                    <div class="pw-bar" id="b1"></div>
                                    <div class="pw-bar" id="b2"></div>
                                    <div class="pw-bar" id="b3"></div>
                                    <div class="pw-bar" id="b4"></div>
                                </div>
                                <div class="pw-label" id="pwLabel"></div>
                            </div>
                        </div>

                        <!-- Confirmation mot de passe -->
                        <div class="form-group">
                            <label for="confirm_password">Confirmer le mot de passe <span class="req">*</span></label>
                            <div class="field-wrap">
                                <i class="fas fa-lock field-icon"></i>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                    placeholder="Répétez votre mot de passe"
                                    autocomplete="new-password" required>
                                <button type="button" class="toggle-pw" onclick="togglePw('confirm_password',this)" title="Afficher/masquer">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="pw-match" id="pwMatch" style="display:none"></div>
                        </div>

                        <!-- Conditions d'utilisation -->
                        <div class="checkbox-row">
                            <input type="checkbox" id="conditions" name="conditions" required>
                            <label for="conditions">
                                J'accepte les
                                <a href="conditions-utilisation.php" target="_blank" rel="noopener">conditions d'utilisation</a>
                                et la
                                <a href="politique-confidentialite.php" target="_blank" rel="noopener">politique de confidentialité</a>
                                <span class="req">*</span>
                            </label>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-user-plus"></i>
                            Créer mon compte
                        </button>
                    </form>

                <?php endif; ?>

            </div><!-- /.auth-body -->

            <div class="auth-footer">
                Déjà membre&nbsp;?
                <a href="connexion.php">Se connecter</a>
            </div>
        </div>
    </div>

    <?php require_once 'templates/footer.php'; ?>

    <script>
        // ══════════════════════════════════════════════════════
        //  Toggle affichage mot de passe
        // ══════════════════════════════════════════════════════
        function togglePw(id, btn) {
            const inp  = document.getElementById(id);
            const icon = btn.querySelector('i');
            inp.type = inp.type === 'password' ? 'text' : 'password';
            icon.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
        }

        // ══════════════════════════════════════════════════════
        //  Indicateur de force du mot de passe
        // ══════════════════════════════════════════════════════
        const pwInput = document.getElementById('password');
        const pwMeter = document.getElementById('pwMeter');
        const pwLabel = document.getElementById('pwLabel');
        const bars    = ['b1','b2','b3','b4'].map(id => document.getElementById(id));

        const LEVELS = [
            { cls: '',       txt: '' },
            { cls: 'weak',   txt: '⚠️  Faible — ajoutez des chiffres ou symboles' },
            { cls: 'fair',   txt: '🔶  Moyen — essayez des majuscules' },
            { cls: 'good',   txt: '👍  Bon' },
            { cls: 'strong', txt: '✅  Fort' },
        ];

        function scorePassword(pw) {
            let s = 0;
            if (pw.length >= 8)  s++;
            if (pw.length >= 12) s++;
            if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) s++;
            if (/[0-9]/.test(pw)) s++;
            if (/[^A-Za-z0-9]/.test(pw)) s++;
            return Math.min(4, s);
        }

        if (pwInput) {
            pwInput.addEventListener('input', function () {
                const pw = this.value;
                if (!pw) { pwMeter.style.display = 'none'; return; }
                pwMeter.style.display = 'block';
                const score = scorePassword(pw);
                const lvl   = LEVELS[score];
                bars.forEach((b, i) => {
                    b.className = 'pw-bar' + (i < score ? ' ' + lvl.cls : '');
                });
                pwLabel.className   = 'pw-label ' + lvl.cls;
                pwLabel.textContent = lvl.txt;
                checkMatch();
            });
        }

        // ══════════════════════════════════════════════════════
        //  Correspondance des mots de passe
        // ══════════════════════════════════════════════════════
        const confirmPw = document.getElementById('confirm_password');
        const matchEl   = document.getElementById('pwMatch');

        function checkMatch() {
            if (!confirmPw || !confirmPw.value) {
                if (matchEl) matchEl.style.display = 'none';
                return;
            }
            matchEl.style.display = 'flex';
            const ok = pwInput.value === confirmPw.value;
            matchEl.className = 'pw-match ' + (ok ? 'ok' : 'bad');
            matchEl.innerHTML = ok
                ? '<i class="fas fa-check-circle"></i> Les mots de passe correspondent'
                : '<i class="fas fa-times-circle"></i> Les mots de passe ne correspondent pas';
            confirmPw.classList.toggle('valid',   ok);
            confirmPw.classList.toggle('invalid', !ok);
        }

        if (confirmPw) confirmPw.addEventListener('input', checkMatch);
    </script>

    <script>
        // ══════════════════════════════════════════════════════
        //  Validation en temps réel des champs (blur)
        // ══════════════════════════════════════════════════════
        (function () {

            function addError(input, msg) {
                removeError(input);
                input.style.borderColor = '#DC2626';
                var el = document.createElement('span');
                el.className = 'field-error-msg';
                el.style.cssText = 'color:#DC2626;font-size:12px;margin-top:4px;display:block;';
                el.textContent = msg;
                input.parentNode.insertBefore(el, input.nextSibling);
            }

            function removeError(input) {
                input.style.borderColor = '';
                var next = input.nextSibling;
                while (next) {
                    if (next.classList && next.classList.contains('field-error-msg')) {
                        next.parentNode.removeChild(next);
                        break;
                    }
                    next = next.nextSibling;
                }
            }

            function addOk(input) {
                removeError(input);
                input.style.borderColor = '#16A34A';
            }

            // ── NOM & PRÉNOM ────────────────────────────────
            document.querySelectorAll('input[name="nom"], input[name="prenom"]').forEach(function (inp) {
                inp.setAttribute('autocomplete', inp.name === 'nom' ? 'family-name' : 'given-name');
                inp.setAttribute('maxlength', '60');
                inp.addEventListener('keypress', function (e) {
                    if (!/[\p{L}\s\-']/u.test(e.key) && e.key.length === 1) e.preventDefault();
                });
                inp.addEventListener('input', function () {
                    this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-']/g, '');
                });
                inp.addEventListener('blur', function () {
                    var v = this.value.trim();
                    if (v.length < 2) addError(this, 'Minimum 2 caractères.');
                    else addOk(this);
                });
            });

            // ── TÉLÉPHONE ───────────────────────────────────
            document.querySelectorAll('input[type="tel"], input[name="telephone"]').forEach(function (inp) {
                inp.setAttribute('inputmode', 'tel');
                inp.setAttribute('maxlength', '20');
                inp.addEventListener('keypress', function (e) {
                    if (!/[0-9+\s\-().]/.test(e.key) && e.key.length === 1) e.preventDefault();
                });
                inp.addEventListener('input', function () {
                    var clean = this.value.replace(/[^0-9+\s\-(). ]/g, '');
                    if (this.value !== clean) this.value = clean;
                });
                inp.addEventListener('blur', function () {
                    var v = this.value.trim();
                    if (!v) { removeError(this); return; }
                    if (v.length < 7 || !/^[0-9+\s\-().]+$/.test(v))
                        addError(this, 'Numéro invalide. Utilisez uniquement des chiffres.');
                    else addOk(this);
                });
            });

            // ── EMAIL ───────────────────────────────────────
            document.querySelectorAll('input[type="email"]').forEach(function (inp) {
                inp.addEventListener('blur', function () {
                    var v = this.value.trim();
                    if (!v) { removeError(this); return; }
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v))
                        addError(this, 'Adresse email invalide.');
                    else addOk(this);
                });
                inp.addEventListener('input', function () {
                    removeError(this);
                    this.style.borderColor = '';
                });
            });

            // ── MOT DE PASSE ────────────────────────────────
            var pwIn   = document.getElementById('password');
            var confIn = document.getElementById('confirm_password');

            if (pwIn) {
                pwIn.setAttribute('minlength', '8');
                pwIn.addEventListener('blur', function () {
                    var v = this.value;
                    if (!v) { removeError(this); return; }
                    if (v.length < 8)          addError(this, 'Minimum 8 caractères requis.');
                    else if (!/[A-Z]/.test(v)) addError(this, 'Ajoutez au moins une lettre majuscule.');
                    else if (!/[0-9]/.test(v)) addError(this, 'Ajoutez au moins un chiffre.');
                    else addOk(this);
                });
            }

            if (confIn && pwIn) {
                confIn.addEventListener('blur', function () {
                    if (!this.value) { removeError(this); return; }
                    if (this.value !== pwIn.value)
                        addError(this, 'Les mots de passe ne correspondent pas.');
                    else addOk(this);
                });
            }

            // ── PROFESSION (si présent sur d'autres pages) ──
            document.querySelectorAll('input[name="profession"]').forEach(function (inp) {
                inp.setAttribute('maxlength', '80');
                inp.addEventListener('input', function () {
                    this.value = this.value.replace(/[^a-zA-ZÀ-ÿ0-9\s\-',.()]/g, '');
                });
            });

            // ── VILLE (si présent sur d'autres pages) ───────
            var ville = document.getElementById('ville');
            if (ville) {
                ville.setAttribute('maxlength', '60');
                ville.addEventListener('keypress', function (e) {
                    if (!/[a-zA-ZÀ-ÿ\s\-']/.test(e.key) && e.key.length === 1) e.preventDefault();
                });
                ville.addEventListener('input', function () {
                    this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-']/g, '');
                });
            }

        })();
    </script>

</body>
</html>