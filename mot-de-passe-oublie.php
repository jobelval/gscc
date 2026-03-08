<?php
// mot-de-passe-oublie.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (isLoggedIn()) redirect('index.php');

$error   = '';
$success = '';
$step    = !empty($_GET['token']) ? 'reset' : 'request';
$token_get = htmlspecialchars($_GET['token'] ?? '');

// ══════════════════════════════════════════════════
//  ÉTAPE 2 — Enregistrer le nouveau mot de passe
// ══════════════════════════════════════════════════
if ($step === 'reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $token            = sanitize($_GET['token']            ?? '');
        $password         = $_POST['password']                 ?? '';
        $confirm_password = $_POST['confirm_password']         ?? '';

        if (strlen($password) < 8) {
            $error = 'Le mot de passe doit contenir au moins 8 caractères.';
        } elseif ($password !== $confirm_password) {
            $error = 'Les mots de passe ne correspondent pas.';
        } else {
            try {
                $stmt = $pdo->prepare("
                    SELECT * FROM password_resets
                    WHERE token = ? AND expires_at > NOW() AND used = 0
                    LIMIT 1
                ");
                $stmt->execute([$token]);
                $reset = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$reset) {
                    $error = 'Ce lien de réinitialisation est invalide ou a expiré. '
                        . '<a href="mot-de-passe-oublie.php">Demandez un nouveau lien</a>.';
                } else {
                    $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    $pdo->prepare("UPDATE utilisateurs SET password = ? WHERE email = ?")
                        ->execute([$hashed, $reset['email']]);
                    $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?")
                        ->execute([$token]);

                    $success = 'Votre mot de passe a été modifié avec succès.';
                    header("refresh:4;url=connexion.php");
                }
            } catch (PDOException $e) {
                logError("reset_password: " . $e->getMessage());
                $error = 'Une erreur est survenue. Veuillez réessayer.';
            }
        }
    }
}

// ══════════════════════════════════════════════════
//  ÉTAPE 1 — Envoyer l'email de réinitialisation
// ══════════════════════════════════════════════════
if ($step === 'request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);

        if (!$email) {
            $error = 'Veuillez entrer une adresse email valide.';
        } else {
            // Message neutre pour éviter l'énumération d'emails
            $success = "Si cette adresse email est associée à un compte, vous recevrez un lien de réinitialisation dans quelques minutes. Pensez à vérifier vos spams.";

            $user = getUserByEmail($email);
            if ($user) {
                try {
                    // Nettoyer les anciens tokens
                    $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

                    $token      = bin2hex(random_bytes(32));
                    $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 heure

                    $pdo->prepare("INSERT INTO password_resets (email, token, expires_at, used) VALUES (?, ?, ?, 0)")
                        ->execute([$email, $token, $expires_at]);

                    $site_name = defined('SITE_NAME') ? SITE_NAME : 'GSCC';
                    $site_url  = defined('SITE_URL')  ? SITE_URL  : 'https://gscc.org';
                    $reset_url = "$site_url/mot-de-passe-oublie.php?token=$token";
                    $prenom    = htmlspecialchars($user['prenom'] ?? 'Membre');
                    $username  = htmlspecialchars($user['email']);

                    // ── Email exactement comme dans l'image ──────────────
                    $subject = "Réinitialisation de votre mot de passe — $site_name";
                    $body  = "$prenom,\n\n";
                    $body .= "Nous vous remercions pour votre inscription sur $site_name. "
                        . "Vous pouvez maintenant vous connecter en utilisant le lien ci-dessous "
                        . "ou en le copiant dans votre navigateur :\n\n";
                    $body .= "$reset_url\n\n";
                    $body .= "Ce lien ne peut être utilisé qu'une seule fois et vous redirigera "
                        . "vers une page où vous pourrez choisir votre mot de passe.\n\n";
                    $body .= "Après avoir choisi votre mot de passe, vous pourrez vous connecter sur "
                        . "$site_url/connexion.php en utilisant :\n\n";
                    $body .= "Nom d'utilisateur : $username\n";
                    $body .= "Mot de passe : Votre mot de passe\n\n";
                    $body .= "Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.\n"
                        . "Votre mot de passe actuel reste inchangé.\n\n";
                    $body .= "-- L'équipe $site_name";

                    sendEmail($email, $subject, $body);
                } catch (PDOException $e) {
                    logError("password_reset_request: " . $e->getMessage());
                    // Ne pas exposer l'erreur à l'utilisateur
                }
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
    <title><?= $step === 'reset' ? 'Nouveau mot de passe' : 'Mot de passe oublié' ?> — <?= defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'GSCC' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --blue: #003399;
            --blue-dark: #002277;
            --blue-lite: #EBF0FF;
            --green: #4CAF50;
            --green-dark: #388E3C;
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
        }

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

        .auth-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow: 0 2px 4px rgba(0, 51, 153, .04), 0 12px 32px rgba(0, 51, 153, .08);
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

        .auth-ribbon {
            height: 5px;
            background: linear-gradient(90deg, #003399 0%, #FF69B4 50%, #4CAF50 100%);
        }

        .auth-body {
            padding: 40px 40px 32px;
        }

        /* Marque */
        .auth-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 26px;
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
        }

        .brand-text span {
            font-size: 10.5px;
            color: var(--text-3);
        }

        /* Icône centrale de l'étape */
        .step-icon {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 30px;
            transition: transform .4s var(--ease);
        }

        .step-icon:hover {
            transform: scale(1.07) rotate(-5deg);
        }

        .step-icon.key {
            background: var(--blue-lite);
            color: var(--blue);
        }

        .step-icon.lock {
            background: #FEF3C7;
            color: #92400E;
        }

        .step-icon.check {
            background: #D1FAE5;
            color: #065F46;
        }

        .auth-title {
            font-size: 1.65rem;
            font-weight: 700;
            color: var(--text);
            text-align: center;
            margin-bottom: 6px;
            letter-spacing: -.3px;
        }

        .auth-sub {
            font-size: 13.5px;
            color: var(--text-3);
            text-align: center;
            margin-bottom: 26px;
            line-height: 1.65;
        }

        /* Alertes */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 13px 16px;
            border-radius: 10px;
            margin-bottom: 22px;
            font-size: 13.5px;
            line-height: 1.6;
        }

        .alert i {
            font-size: 15px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .alert-error {
            background: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        .alert-success {
            background: #F0FDF4;
            color: #166534;
            border: 1px solid #BBF7D0;
        }

        .alert a {
            color: inherit;
            font-weight: 700;
        }

        /* Note sécurité */
        .sec-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 13px 15px;
            background: var(--blue-lite);
            border-radius: 10px;
            margin-bottom: 22px;
            font-size: 13px;
            color: var(--text-2);
            line-height: 1.6;
        }

        .sec-note i {
            color: var(--blue);
            font-size: 14px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .sec-note strong {
            color: var(--blue);
        }

        /* Champs */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-2);
        }

        .field-wrap {
            position: relative;
        }

        .field-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-3);
            font-size: 14px;
            pointer-events: none;
            transition: color .2s;
        }

        .field-wrap:focus-within .field-icon {
            color: var(--blue);
        }

        .form-control {
            width: 100%;
            padding: 12px 42px 12px 38px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 14.5px;
            color: var(--text);
            background: #F7F9FF;
            outline: none;
            transition: all .25s var(--ease);
        }

        .form-control:focus {
            border-color: var(--blue);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(0, 51, 153, .09);
        }

        .form-control::placeholder {
            color: #C0C4D6;
        }

        .form-control.valid {
            border-color: var(--green);
        }

        .form-control.invalid {
            border-color: #EF4444;
        }

        .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-3);
            font-size: 14px;
            padding: 3px;
            transition: color .2s;
        }

        .toggle-pw:hover {
            color: var(--blue);
        }

        /* Indicateur force */
        .pw-meter {
            margin-top: 8px;
        }

        .pw-bars {
            display: flex;
            gap: 4px;
            margin-bottom: 5px;
        }

        .pw-bar {
            flex: 1;
            height: 3px;
            border-radius: 99px;
            background: var(--border);
            transition: background .3s;
        }

        .pw-bar.weak {
            background: #EF4444;
        }

        .pw-bar.fair {
            background: #F59E0B;
        }

        .pw-bar.good {
            background: #3B82F6;
        }

        .pw-bar.strong {
            background: var(--green);
        }

        .pw-label {
            font-size: 11.5px;
            color: var(--text-3);
        }

        .pw-label.weak {
            color: #EF4444;
        }

        .pw-label.fair {
            color: #D97706;
        }

        .pw-label.good {
            color: #2563EB;
        }

        .pw-label.strong {
            color: var(--green-dark);
        }

        .pw-match {
            font-size: 12px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .pw-match.ok {
            color: var(--green-dark);
        }

        .pw-match.bad {
            color: #EF4444;
        }

        /* Bouton */
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

        /* Pied */
        .auth-footer {
            border-top: 1px solid var(--border);
            padding: 18px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            font-size: 13px;
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

        .footer-sep {
            width: 1px;
            height: 14px;
            background: var(--border);
        }

        @media (max-width:520px) {
            .auth-body {
                padding: 28px 18px 22px;
            }

            .auth-footer {
                padding: 16px 18px;
            }

            .footer-sep {
                display: none;
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

                <?php if ($step === 'request'): ?>
                    <!-- ════════ ÉTAPE 1 : Demande ════════ -->

                    <div class="step-icon key"><i class="fas fa-key"></i></div>
                    <h1 class="auth-title">Mot de passe oublié&nbsp;?</h1>
                    <p class="auth-sub">
                        Entrez votre adresse email et nous vous enverrons
                        un lien pour réinitialiser votre mot de passe.
                    </p>

                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-triangle-exclamation"></i>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-circle-check"></i>
                            <span><?= htmlspecialchars($success) ?></span>
                        </div>
                    <?php else: ?>

                        <div class="sec-note">
                            <i class="fas fa-shield-halved"></i>
                            <span>
                                Le lien de réinitialisation sera valable pendant <strong>1 heure</strong>
                                et ne pourra être utilisé <strong>qu'une seule fois</strong>.
                            </span>
                        </div>

                        <form method="POST" action="" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                            <div class="form-group">
                                <label for="email">Adresse email de votre compte</label>
                                <div class="field-wrap">
                                    <i class="fas fa-envelope field-icon"></i>
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="votre@email.com"
                                        autocomplete="email" required>
                                </div>
                            </div>

                            <button type="submit" class="btn-submit">
                                <i class="fas fa-paper-plane"></i>
                                Envoyer le lien de réinitialisation
                            </button>
                        </form>

                    <?php endif; ?>

                <?php elseif ($step === 'reset'): ?>
                    <!-- ════════ ÉTAPE 2 : Nouveau mot de passe ════════ -->

                    <?php if ($success): ?>
                        <div class="step-icon check"><i class="fas fa-check"></i></div>
                        <h1 class="auth-title">Mot de passe modifié&nbsp;!</h1>
                        <p class="auth-sub">
                            Votre mot de passe a été réinitialisé avec succès.<br>
                            Redirection vers la connexion dans quelques secondes…
                        </p>
                        <div class="alert alert-success">
                            <i class="fas fa-circle-check"></i>
                            <span><?= htmlspecialchars($success) ?></span>
                        </div>
                    <?php else: ?>

                        <div class="step-icon lock"><i class="fas fa-lock"></i></div>
                        <h1 class="auth-title">Nouveau mot de passe</h1>
                        <p class="auth-sub">Choisissez un mot de passe sécurisé pour votre compte.</p>

                        <?php if ($error): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-triangle-exclamation"></i>
                                <span><?= $error /* HTML contrôlé */ ?></span>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="mot-de-passe-oublie.php?token=<?= $token_get ?>" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                            <div class="form-group">
                                <label for="password">Nouveau mot de passe</label>
                                <div class="field-wrap">
                                    <i class="fas fa-lock field-icon"></i>
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="Minimum 8 caractères"
                                        autocomplete="new-password" required>
                                    <button type="button" class="toggle-pw" onclick="togglePw('password',this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
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

                            <div class="form-group">
                                <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                                <div class="field-wrap">
                                    <i class="fas fa-lock field-icon"></i>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                        placeholder="Répétez votre mot de passe"
                                        autocomplete="new-password" required>
                                    <button type="button" class="toggle-pw" onclick="togglePw('confirm_password',this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="pw-match" id="pwMatch" style="display:none"></div>
                            </div>

                            <button type="submit" class="btn-submit">
                                <i class="fas fa-check-circle"></i>
                                Enregistrer le nouveau mot de passe
                            </button>
                        </form>

                    <?php endif; ?>
                <?php endif; ?>

            </div>

            <div class="auth-footer">
                <a href="connexion.php">
                    <i class="fas fa-arrow-left" style="font-size:10px;margin-right:3px"></i>
                    Retour à la connexion
                </a>
                <span class="footer-sep"></span>
                <a href="inscription.php">Créer un compte</a>
            </div>
        </div>
    </div>

    <?php require_once 'templates/footer.php'; ?>
    <script>
        function togglePw(id, btn) {
            const inp = document.getElementById(id);
            const icon = btn.querySelector('i');
            inp.type = inp.type === 'password' ? 'text' : 'password';
            icon.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
        }

        /* Force du mot de passe (étape reset uniquement) */
        const pwIn = document.getElementById('password');
        if (pwIn) {
            const pwMeter = document.getElementById('pwMeter');
            const pwLabel = document.getElementById('pwLabel');
            const bars = ['b1', 'b2', 'b3', 'b4'].map(id => document.getElementById(id));
            const LEVELS = [{
                    cls: '',
                    txt: ''
                },
                {
                    cls: 'weak',
                    txt: '⚠️  Faible'
                },
                {
                    cls: 'fair',
                    txt: '🔶  Moyen'
                },
                {
                    cls: 'good',
                    txt: '👍  Bon'
                },
                {
                    cls: 'strong',
                    txt: '✅  Fort'
                },
            ];

            function score(pw) {
                let s = 0;
                if (pw.length >= 8) s++;
                if (pw.length >= 12) s++;
                if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) s++;
                if (/[0-9]/.test(pw)) s++;
                if (/[^A-Za-z0-9]/.test(pw)) s++;
                return Math.min(4, s);
            }

            pwIn.addEventListener('input', function() {
                const pw = this.value;
                if (!pw) {
                    pwMeter.style.display = 'none';
                    return;
                }
                pwMeter.style.display = 'block';
                const sc = score(pw);
                const lvl = LEVELS[sc];
                bars.forEach((b, i) => {
                    b.className = 'pw-bar' + (i < sc ? ' ' + lvl.cls : '');
                });
                pwLabel.className = 'pw-label ' + lvl.cls;
                pwLabel.textContent = lvl.txt;
                checkMatch();
            });

            const cfm = document.getElementById('confirm_password');
            const matchEl = document.getElementById('pwMatch');

            function checkMatch() {
                if (!cfm || !cfm.value) {
                    if (matchEl) matchEl.style.display = 'none';
                    return;
                }
                matchEl.style.display = 'flex';
                const ok = pwIn.value === cfm.value;
                matchEl.className = 'pw-match ' + (ok ? 'ok' : 'bad');
                matchEl.innerHTML = ok ?
                    '<i class="fas fa-check-circle"></i> Les mots de passe correspondent' :
                    '<i class="fas fa-times-circle"></i> Les mots de passe ne correspondent pas';
                cfm.classList.toggle('valid', ok);
                cfm.classList.toggle('invalid', !ok);
            }
            if (cfm) cfm.addEventListener('input', checkMatch);
        }
    </script>
</body>

</html>