<?php
// connexion.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (isLoggedIn()) redirect('index.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (!$email || empty($password)) {
            $error = 'Veuillez remplir tous les champs.';
        } else {
            $result = loginUser($email, $password);

            if ($result['success']) {
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true);
                    // TODO: stocker $token en base de données
                }
                redirect('index.php');
            } else {
                $error = $result['error'] ?? 'Email ou mot de passe incorrect.';
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — <?= defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'GSCC' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ── Variables ─────────────────────────────── */
        :root {
            --blue: #003399;
            --blue-dark: #002277;
            --blue-lite: #EBF0FF;
            --green: #4CAF50;
            --pink: #FF69B4;
            --bg: #F0F4FB;
            --white: #FFFFFF;
            --text: #1A1A2E;
            --text-2: #4A4A6A;
            --text-3: #9A9AB0;
            --border: #DDE3F0;
            --ease: cubic-bezier(.4, 0, .2, 1);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* ── Fond avec dégradé doux ──────────────── */
        .auth-page {
            min-height: calc(100vh - 140px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 52px 20px;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(0, 51, 153, .07) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(76, 175, 80, .06) 0%, transparent 55%),
                var(--bg);
        }

        /* ── Carte ───────────────────────────────── */
        .auth-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow:
                0 2px 4px rgba(0, 51, 153, .04),
                0 12px 32px rgba(0, 51, 153, .08),
                0 40px 80px rgba(0, 51, 153, .05);
            width: 100%;
            max-width: 460px;
            overflow: hidden;
            animation: cardIn .5s var(--ease) both;
        }

        @keyframes cardIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Bande dégradé en haut */
        .auth-ribbon {
            height: 5px;
            background: linear-gradient(90deg, #003399 0%, #FF69B4 50%, #4CAF50 100%);
        }

        /* ── Corps ───────────────────────────────── */
        .auth-body {
            padding: 40px 40px 32px;
        }

        /* Logo GSCC */
        .auth-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 30px;
        }

        .brand-icon {
            width: 50px;
            height: 50px;
            border-radius: 13px;
            background: linear-gradient(135deg, #003399 0%, #4CAF50 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(0, 51, 153, .3);
        }

        .brand-text strong {
            display: block;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--blue);
            line-height: 1.2;
        }

        .brand-text span {
            font-size: 10.5px;
            color: var(--text-3);
            letter-spacing: .3px;
        }

        .auth-title {
            font-size: 1.7rem;
            font-weight: 700;
            color: var(--text);
            text-align: center;
            margin-bottom: 4px;
            letter-spacing: -.3px;
        }

        .auth-sub {
            font-size: 14px;
            color: var(--text-3);
            text-align: center;
            margin-bottom: 28px;
        }

        /* ── Alerte ──────────────────────────────── */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 13px 16px;
            border-radius: 10px;
            margin-bottom: 22px;
            font-size: 13.5px;
            line-height: 1.55;
        }

        .alert i {
            font-size: 15px;
            margin-top: 1px;
            flex-shrink: 0;
        }

        .alert-error {
            background: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        /* ── Groupes de champs ───────────────────── */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-2);
            letter-spacing: .1px;
        }

        .field-wrap {
            position: relative;
        }

        .field-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-3);
            font-size: 14.5px;
            pointer-events: none;
            transition: color .2s;
        }

        .field-wrap:focus-within .field-icon {
            color: var(--blue);
        }

        .form-control {
            width: 100%;
            padding: 12px 44px 12px 42px;
            border: 1.5px solid var(--border);
            border-radius: 11px;
            font-family: 'Inter', sans-serif;
            font-size: 14.5px;
            color: var(--text);
            background: #F7F9FF;
            transition: all .25s var(--ease);
            outline: none;
        }

        .form-control:focus {
            border-color: var(--blue);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(0, 51, 153, .09);
        }

        .form-control::placeholder {
            color: #C0C4D6;
        }

        /* Bouton afficher/masquer mdp */
        .toggle-pw {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-3);
            font-size: 14px;
            padding: 4px;
            transition: color .2s;
        }

        .toggle-pw:hover {
            color: var(--blue);
        }

        /* ── Options (se souvenir / oublié) ─────── */
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13.5px;
            color: var(--text-2);
            cursor: pointer;
            user-select: none;
        }

        .remember-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--blue);
            cursor: pointer;
        }

        .link-forgot {
            font-size: 13px;
            font-weight: 600;
            color: var(--blue);
            text-decoration: none;
            transition: color .2s;
        }

        .link-forgot:hover {
            color: var(--blue-dark);
            text-decoration: underline;
        }

        /* ── Bouton principal ────────────────────── */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #003399 0%, #4CAF50 100%);
            color: #fff;
            border: none;
            border-radius: 11px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: .2px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            box-shadow: 0 4px 18px rgba(0, 51, 153, .25);
            transition: all .3s var(--ease);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(0, 51, 153, .35);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* ── Pied de carte ───────────────────────── */
        .auth-footer {
            border-top: 1px solid var(--border);
            padding: 20px 40px;
            text-align: center;
            font-size: 13.5px;
            color: var(--text-3);
            background: #FAFBFF;
        }

        .auth-footer a {
            color: var(--blue);
            font-weight: 600;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        /* ── Responsive ──────────────────────────── */
        @media (max-width: 520px) {
            .auth-body {
                padding: 28px 20px 22px;
            }

            .auth-footer {
                padding: 18px 20px;
            }

            .auth-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <?php require_once 'templates/header.php'; ?>

    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-ribbon"></div>
            <div class="auth-body">

                <!-- Marque -->
                <div class="auth-brand">
                    <div class="brand-icon"><i class="fas fa-ribbon"></i></div>
                    <div class="brand-text">
                        <strong>GSCC</strong>
                        <span>Groupe de Support Contre le Cancer</span>
                    </div>
                </div>

                <h1 class="auth-title">Connexion</h1>
                <p class="auth-sub">Accédez à votre espace membre</p>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-triangle-exclamation"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <div class="field-wrap">
                            <i class="fas fa-envelope field-icon"></i>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="votre@email.com"
                                autocomplete="email" required>
                        </div>
                    </div>

                    <!-- Mot de passe -->
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <div class="field-wrap">
                            <i class="fas fa-lock field-icon"></i>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="••••••••"
                                autocomplete="current-password" required>
                            <button type="button" class="toggle-pw" onclick="togglePw('password',this)" title="Afficher/masquer">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Se souvenir / Oublié -->
                    <div class="form-options">
                        <label class="remember-label">
                            <input type="checkbox" name="remember">
                            <span>Se souvenir de moi</span>
                        </label>
                        <a href="mot-de-passe-oublie.php" class="link-forgot">Mot de passe oublié&nbsp;?</a>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-arrow-right-to-bracket"></i>
                        Se connecter
                    </button>
                </form>

            </div>

            <div class="auth-footer">
                Pas encore membre&nbsp;?
                <a href="inscription.php">Créer un compte gratuitement</a>
            </div>
        </div>
    </div>

    <?php require_once 'templates/footer.php'; ?>
    <script>
        function togglePw(id, btn) {
            const inp = document.getElementById(id);
            const icon = btn.querySelector('i');
            if (inp.type === 'password') {
                inp.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                inp.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
    </script>
</body>

</html>