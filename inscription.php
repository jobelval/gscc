<?php
// inscription.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (isLoggedIn()) redirect('index.php');

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $email            = filter_var(trim($_POST['email']            ?? ''), FILTER_VALIDATE_EMAIL);
        $password         = $_POST['password']                         ?? '';
        $confirm_password = $_POST['confirm_password']                 ?? '';
        $nom              = sanitize($_POST['nom']                     ?? '');
        $prenom           = sanitize($_POST['prenom']                  ?? '');
        $telephone        = sanitize($_POST['telephone']               ?? '');
        $conditions       = isset($_POST['conditions']);

        if (!$email) {
            $error = 'Adresse email invalide.';
        } elseif (strlen($password) < 8) {
            $error = 'Le mot de passe doit contenir au moins 8 caractères.';
        } elseif ($password !== $confirm_password) {
            $error = 'Les mots de passe ne correspondent pas.';
        } elseif (empty($nom) || empty($prenom)) {
            $error = 'Le nom et le prénom sont obligatoires.';
        } elseif (!$conditions) {
            $error = "Vous devez accepter les conditions d'utilisation.";
        } else {
            $existingUser = getUserByEmail($email);
            if ($existingUser) {
                $error = 'Cette adresse email est déjà utilisée. '
                    . '<a href="connexion.php">Connectez-vous</a> ou '
                    . '<a href="mot-de-passe-oublie.php">réinitialisez votre mot de passe</a>.';
            } else {
                $result = createUser([
                    'email'     => $email,
                    'password'  => $password,
                    'nom'       => $nom,
                    'prenom'    => $prenom,
                    'telephone' => $telephone,
                ]);

                if ($result) {
                    // ── Email de confirmation (style image) ──────────────
                    $site_name = defined('SITE_NAME') ? SITE_NAME : 'GSCC';
                    $site_url  = defined('SITE_URL')  ? SITE_URL  : 'https://gscc.org';

                    // Token de confirmation email (optionnel mais professionnel)
                    $confirm_token = bin2hex(random_bytes(32));
                    $confirm_url   = "$site_url/confirmer-email.php?token=$confirm_token";

                    // Stocker le token en base
                    try {
                        $pdo->prepare("INSERT INTO email_confirmations (email, token, created_at) VALUES (?, ?, NOW())")
                            ->execute([$email, $confirm_token]);
                    } catch (Exception $e) { /* table optionnelle */
                    }

                    $subject = "Bienvenue sur $site_name — Confirmez votre compte";
                    $body  = "$prenom,\n\n";
                    $body .= "Nous vous remercions pour votre inscription sur $site_name. "
                        . "Vous pouvez maintenant vous connecter en utilisant le lien ci-dessous "
                        . "ou en le copiant dans votre navigateur :\n\n";
                    $body .= "$confirm_url\n\n";
                    $body .= "Ce lien ne peut être utilisé qu'une seule fois et vous redirigera "
                        . "vers une page où vous pourrez confirmer votre compte.\n\n";
                    $body .= "Après confirmation, vous pourrez vous connecter sur "
                        . "$site_url/connexion.php en utilisant :\n\n";
                    $body .= "Nom d'utilisateur : $email\n";
                    $body .= "Mot de passe : Votre mot de passe\n\n";
                    $body .= "-- L'équipe $site_name";

                    sendEmail($email, $subject, $body);

                    $success = "Inscription réussie&nbsp;! Un email de confirmation a été envoyé à <strong>"
                        . htmlspecialchars($email) . "</strong>. "
                        . "Vérifiez votre boîte mail (et vos spams). "
                        . "Vous allez être redirigé vers la connexion dans 5 secondes…";

                    header("refresh:5;url=connexion.php");
                } else {
                    $error = "Erreur lors de l'inscription. Veuillez réessayer.";
                }
            }
        }
    }
}

$csrf_token = generateCSRFToken();

// Valeurs à conserver en cas d'erreur
$val_nom      = htmlspecialchars($_POST['nom']      ?? '');
$val_prenom   = htmlspecialchars($_POST['prenom']   ?? '');
$val_email    = htmlspecialchars($_POST['email']    ?? '');
$val_tel      = htmlspecialchars($_POST['telephone'] ?? '');
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
            max-width: 520px;
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
            padding: 38px 40px 30px;
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

        .auth-title {
            font-size: 1.65rem;
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
            margin-bottom: 26px;
        }

        /* Alertes */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 13px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
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

        /* Grille 2 colonnes */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        /* Champs */
        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-2);
        }

        .form-group label .req {
            color: var(--blue);
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
            padding: 11px 42px 11px 38px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
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
            transition: color .2s;
            padding: 3px;
        }

        .toggle-pw:hover {
            color: var(--blue);
        }

        /* ── Indicateur de force du mot de passe ── */
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
            transition: background .3s var(--ease);
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
            display: flex;
            align-items: center;
            gap: 5px;
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

        /* ── Correspondance ── */
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

        /* Checkbox conditions */
        .checkbox-row {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin-bottom: 18px;
            font-size: 13.5px;
            color: var(--text-2);
        }

        .checkbox-row input[type="checkbox"] {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
            margin-top: 2px;
            accent-color: var(--blue);
            cursor: pointer;
        }

        .checkbox-row a {
            color: var(--blue);
            font-weight: 600;
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

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Pied */
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

        /* Responsive */
        @media (max-width:540px) {
            .auth-body {
                padding: 26px 18px 20px;
            }

            .auth-footer {
                padding: 18px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
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

                <div class="auth-brand">
                    <div class="brand-icon"><i class="fas fa-ribbon"></i></div>
                    <div class="brand-text">
                        <strong>GSCC</strong>
                        <span>Groupe de Support Contre le Cancer</span>
                    </div>
                </div>

                <h1 class="auth-title">Créer un compte</h1>
                <p class="auth-sub">Rejoignez la communauté GSCC gratuitement</p>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-triangle-exclamation"></i>
                        <span><?= $error /* HTML contrôlé */ ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-circle-check"></i>
                        <span><?= $success ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="regForm" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                    <!-- Nom / Prénom -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom <span class="req">*</span></label>
                            <div class="field-wrap">
                                <i class="fas fa-user field-icon"></i>
                                <input type="text" class="form-control" id="nom" name="nom"
                                    placeholder="Dupont" autocomplete="family-name"
                                    value="<?= $val_nom ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="prenom">Prénom <span class="req">*</span></label>
                            <div class="field-wrap">
                                <i class="fas fa-user field-icon"></i>
                                <input type="text" class="form-control" id="prenom" name="prenom"
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
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="votre@email.com" autocomplete="email"
                                value="<?= $val_email ?>" required>
                        </div>
                    </div>

                    <!-- Téléphone -->
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <div class="field-wrap">
                            <i class="fas fa-phone field-icon"></i>
                            <input type="tel" class="form-control" id="telephone" name="telephone"
                                placeholder="+(509) 37 00 00 00" autocomplete="tel"
                                value="<?= $val_tel ?>">
                        </div>
                    </div>

                    <!-- Mot de passe -->
                    <div class="form-group">
                        <label for="password">Mot de passe <span class="req">*</span></label>
                        <div class="field-wrap">
                            <i class="fas fa-lock field-icon"></i>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Minimum 8 caractères"
                                autocomplete="new-password" required>
                            <button type="button" class="toggle-pw" onclick="togglePw('password',this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <!-- Indicateur force -->
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

                    <!-- Confirmer mot de passe -->
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe <span class="req">*</span></label>
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

                    <!-- Conditions -->
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

            </div>
            <div class="auth-footer">
                Déjà membre&nbsp;?
                <a href="connexion.php">Se connecter</a>
            </div>
        </div>
    </div>

    <?php require_once 'templates/footer.php'; ?>
    <script>
        /* ── Toggle mot de passe ── */
        function togglePw(id, btn) {
            const inp = document.getElementById(id);
            const icon = btn.querySelector('i');
            inp.type = inp.type === 'password' ? 'text' : 'password';
            icon.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
        }

        /* ── Force du mot de passe ── */
        const pwInput = document.getElementById('password');
        const pwMeter = document.getElementById('pwMeter');
        const pwLabel = document.getElementById('pwLabel');
        const bars = [document.getElementById('b1'), document.getElementById('b2'),
            document.getElementById('b3'), document.getElementById('b4')
        ];

        const LEVELS = [{
                cls: '',
                txt: ''
            },
            {
                cls: 'weak',
                txt: '⚠️  Faible — ajoutez des chiffres ou symboles'
            },
            {
                cls: 'fair',
                txt: '🔶  Moyen — essayez des majuscules'
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

        function scorePassword(pw) {
            let s = 0;
            if (pw.length >= 8) s++;
            if (pw.length >= 12) s++;
            if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) s++;
            if (/[0-9]/.test(pw)) s++;
            if (/[^A-Za-z0-9]/.test(pw)) s++;
            return Math.min(4, s);
        }

        pwInput.addEventListener('input', function() {
            const pw = this.value;
            if (!pw) {
                pwMeter.style.display = 'none';
                return;
            }
            pwMeter.style.display = 'block';
            const score = scorePassword(pw);
            const lvl = LEVELS[score];
            bars.forEach((b, i) => {
                b.className = 'pw-bar' + (i < score ? ' ' + lvl.cls : '');
            });
            pwLabel.className = 'pw-label ' + lvl.cls;
            pwLabel.textContent = lvl.txt;
            checkMatch();
        });

        /* ── Correspondance ── */
        const confirmPw = document.getElementById('confirm_password');
        const matchEl = document.getElementById('pwMatch');

        function checkMatch() {
            if (!confirmPw.value) {
                matchEl.style.display = 'none';
                return;
            }
            matchEl.style.display = 'flex';
            const ok = pwInput.value === confirmPw.value;
            matchEl.className = 'pw-match ' + (ok ? 'ok' : 'bad');
            matchEl.innerHTML = ok ?
                '<i class="fas fa-check-circle"></i> Les mots de passe correspondent' :
                '<i class="fas fa-times-circle"></i> Les mots de passe ne correspondent pas';
            confirmPw.classList.toggle('valid', ok);
            confirmPw.classList.toggle('invalid', !ok);
        }
        confirmPw.addEventListener('input', checkMatch);
    </script>
</body>

</html>