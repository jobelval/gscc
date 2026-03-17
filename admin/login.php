<?php
/**
 * GSCC CMS — admin/login.php
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

// Déjà connecté ?
if (isset($_SESSION['admin_id']) && $_SESSION['admin_role'] === 'admin') {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Protection brute-force
    $attempts_key = 'login_attempts_' . md5($_SERVER['REMOTE_ADDR'] ?? '');
    $attempts     = $_SESSION[$attempts_key] ?? ['n' => 0, 't' => time()];

    if ($attempts['n'] >= 5 && (time() - $attempts['t']) < 900) {
        $wait = ceil((900 - (time() - $attempts['t'])) / 60);
        $error = "Trop de tentatives. Réessayez dans {$wait} minute(s).";
    } elseif (!$email || !$password) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "SELECT id, email, mot_de_passe, nom, prenom, role, statut, photo_url
                 FROM utilisateurs WHERE email = ? LIMIT 1"
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                if ($user['statut'] !== 'actif') {
                    $error = 'Votre compte est désactivé. Contactez un administrateur.';
                } elseif (!in_array($user['role'], ['admin', 'moderateur'])) {
                    $error = 'Accès refusé. Droits administrateur requis.';
                } else {
                    // Connexion réussie
                    session_regenerate_id(true);
                    $_SESSION['admin_id']     = $user['id'];
                    $_SESSION['admin_email']  = $user['email'];
                    $_SESSION['admin_nom']    = $user['nom'];
                    $_SESSION['admin_prenom'] = $user['prenom'];
                    $_SESSION['admin_role']   = $user['role'];
                    $_SESSION['admin_photo']  = $user['photo_url'];
                    $_SESSION['admin_ip']     = $_SERVER['REMOTE_ADDR'] ?? '';
                    $_SESSION['admin_last_activity'] = time();

                    // MAJ dernière connexion
                    $pdo->prepare("UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?")
                        ->execute([$user['id']]);

                    // Reset tentatives
                    unset($_SESSION[$attempts_key]);

                    $redirect = $_SESSION['redirect_after_login'] ?? SITE_URL . '/admin/index.php';
                    unset($_SESSION['redirect_after_login']);
                    header('Location: ' . $redirect);
                    exit;
                }
            } else {
                // Incrémenter tentatives
                if ((time() - $attempts['t']) > 900) {
                    $attempts = ['n' => 0, 't' => time()];
                }
                $attempts['n']++;
                $_SESSION[$attempts_key] = $attempts;
                $remaining = 5 - $attempts['n'];
                $error = "Email ou mot de passe incorrect." . ($remaining > 0 ? " ({$remaining} essai(s) restant(s))" : '');
            }
        } catch (PDOException $e) {
            $error = 'Erreur de connexion. Veuillez réessayer.';
        }
    }
}

$login_error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — GSCC CMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0F1C2E 0%, #003399 60%, #0F1C2E 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .login-wrap {
            width: 100%; max-width: 420px;
        }
        .login-logo {
            text-align: center; margin-bottom: 32px;
        }
        .login-logo .icon {
            width: 72px; height: 72px;
            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 34px; margin: 0 auto 14px;
        }
        .login-logo h1 { color: #fff; font-size: 1.4rem; font-weight: 700; }
        .login-logo p  { color: rgba(255,255,255,.45); font-size: .82rem; margin-top: 4px; }

        .login-card {
            background: #fff;
            border-radius: 18px;
            padding: 36px 36px 32px;
            box-shadow: 0 24px 80px rgba(0,0,0,.3);
        }
        .login-card h2 {
            font-size: 1.15rem; font-weight: 700; color: #1E293B;
            margin-bottom: 6px;
        }
        .login-card .subtitle {
            font-size: .82rem; color: #64748B; margin-bottom: 28px;
        }

        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: .82rem; font-weight: 600; color: #1E293B; margin-bottom: 6px; }
        .input-wrap { position: relative; }
        .input-wrap i {
            position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
            color: #94A3B8; font-size: 14px;
        }
        input {
            width: 100%;
            padding: 11px 13px 11px 38px;
            border: 1.5px solid #E2E8F0;
            border-radius: 8px;
            font-size: .9rem; font-family: 'Inter', sans-serif;
            color: #1E293B; outline: none;
            transition: border-color .18s, box-shadow .18s;
        }
        input:focus { border-color: #003399; box-shadow: 0 0 0 3px rgba(0,51,153,.08); }

        .toggle-pw {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: #94A3B8; cursor: pointer;
            font-size: 14px; padding: 4px;
        }
        .toggle-pw:hover { color: #003399; }

        .error-box {
            background: #FFF5F5; border: 1px solid #FCA5A5;
            border-radius: 8px; padding: 12px 14px;
            color: #991B1B; font-size: .84rem;
            display: flex; align-items: center; gap: 9px;
            margin-bottom: 20px;
        }

        .btn-login {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #003399, #1a56cc);
            color: #fff; border: none; border-radius: 8px;
            font-size: .95rem; font-weight: 700; font-family: 'Inter', sans-serif;
            cursor: pointer; transition: all .18s;
            display: flex; align-items: center; justify-content: center; gap: 9px;
            box-shadow: 0 4px 16px rgba(0,51,153,.28);
        }
        .btn-login:hover { background: linear-gradient(135deg, #002277, #003399); transform: translateY(-1px); }
        .btn-login:active { transform: translateY(0); }

        .login-footer {
            margin-top: 20px; text-align: center;
            font-size: .78rem; color: #94A3B8;
        }

        .security-info {
            margin-top: 24px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            color: rgba(255,255,255,.35); font-size: .75rem;
        }
        .security-info i { font-size: 12px; }
    </style>
</head>
<body>
    <div class="login-wrap">

        <div class="login-logo">
            <div class="icon">🎗️</div>
            <h1>GSCC CMS</h1>
            <p>Groupe de Support Contre le Cancer — Haïti</p>
        </div>

        <div class="login-card">
            <h2>Bienvenue 👋</h2>
            <p class="subtitle">Connectez-vous à votre espace d'administration.</p>

            <?php if ($error || $login_error): ?>
                <div class="error-box">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error ?: $login_error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="on">
                <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">

                <div class="form-group">
                    <label class="form-label" for="email">Adresse email</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               placeholder="admin@gscchaiti.com"
                               required autofocus autocomplete="email">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password"
                               placeholder="••••••••••••"
                               required autocomplete="current-password">
                        <button type="button" class="toggle-pw" onclick="togglePw()" id="pwToggle">
                            <i class="fas fa-eye" id="pwIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-right-to-bracket"></i> Se connecter
                </button>
            </form>

            <div class="login-footer">
                © <?= date('Y') ?> GSCC — Accès réservé au personnel autorisé
            </div>
        </div>

        <div class="security-info">
            <i class="fas fa-shield-halved"></i>
            Connexion sécurisée — Session chiffrée
        </div>

    </div>

    <script>
    function togglePw() {
        const pw = document.getElementById('password');
        const ic = document.getElementById('pwIcon');
        if (pw.type === 'password') {
            pw.type = 'text';
            ic.className = 'fas fa-eye-slash';
        } else {
            pw.type = 'password';
            ic.className = 'fas fa-eye';
        }
    }
    </script>
</body>
</html>
