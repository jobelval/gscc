<?php
// devenir-membre.php
require_once 'includes/config.php';

$page_title = 'Devenir membre';
$page_description = 'Rejoignez la communauté GSCC et participez activement à la lutte contre le cancer.';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $data = [
            'email' => sanitize($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'nom' => sanitize($_POST['nom'] ?? ''),
            'prenom' => sanitize($_POST['prenom'] ?? ''),
            'telephone' => sanitize($_POST['telephone'] ?? ''),
            'adresse' => sanitize($_POST['adresse'] ?? ''),
            'ville' => sanitize($_POST['ville'] ?? ''),
            'code_postal' => sanitize($_POST['code_postal'] ?? ''),
            'profession' => sanitize($_POST['profession'] ?? ''),
            'type_membre' => sanitize($_POST['type_membre'] ?? 'actif'),
            'newsletter' => isset($_POST['newsletter'])
        ];

        // Validation
        if (empty($data['nom'])) {
            $error = 'Le nom est requis.';
        } elseif (!preg_match("/^[\p{L}\s'\-]{2,60}$/u", $data['nom'])) {
            $error = 'Le nom ne doit contenir que des lettres.';
        } elseif (empty($data['prenom'])) {
            $error = 'Le prénom est requis.';
        } elseif (!preg_match("/^[\p{L}\s'\-]{2,60}$/u", $data['prenom'])) {
            $error = 'Le prénom ne doit contenir que des lettres.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'Email invalide.';
        } elseif (!empty($data['telephone']) && !preg_match('/^[0-9+\s\-().]{7,20}$/', $data['telephone'])) {
            $error = 'Numéro de téléphone invalide. Chiffres uniquement.';
        } elseif (!empty($data['code_postal']) && !preg_match('/^[0-9]{3,10}$/', $data['code_postal'])) {
            $error = 'Code postal invalide (chiffres uniquement).';
        } elseif (!empty($data['ville']) && !preg_match("/^[\p{L}\s'\-]{2,60}$/u", $data['ville'])) {
            $error = 'La ville ne doit contenir que des lettres.';
        } elseif (strlen($data['password']) < 8) {
            $error = 'Le mot de passe doit contenir au moins 8 caractères.';
        } elseif (!preg_match('/[A-Z]/', $data['password'])) {
            $error = 'Le mot de passe doit contenir au moins une majuscule.';
        } elseif (!preg_match('/[0-9]/', $data['password'])) {
            $error = 'Le mot de passe doit contenir au moins un chiffre.';
        } elseif ($data['password'] !== $data['confirm_password']) {
            $error = 'Les mots de passe ne correspondent pas.';
        } elseif (!isset($_POST['conditions'])) {
            $error = 'Vous devez accepter les conditions d\'adhésion.';
        } else {
            // Vérifier si l'email existe déjà
            $existingUser = getUserByEmail($data['email']);
            if ($existingUser) {
                $error = 'Cet email est déjà utilisé.';
            } else {
                // Créer l'utilisateur
                $result = createUser($data);

                if ($result) {
                    // Envoyer email de bienvenue
                    $subject = "Bienvenue au GSCC !";
                    $message = "Bonjour {$data['prenom']} {$data['nom']},\n\n";
                    $message .= "Nous vous remercions d'avoir rejoint le GSCC. Votre adhésion a été enregistrée avec succès.\n\n";
                    $message .= "Vous pouvez dès maintenant :\n";
                    $message .= "- Participer à nos événements\n";
                    $message .= "- Accéder au forum\n";
                    $message .= "- Faire un don\n";
                    $message .= "- Demander de l'aide\n\n";
                    $message .= "Votre soutien est précieux dans notre lutte contre le cancer.\n\n";
                    $message .= "Cordialement,\nL'équipe GSCC";

                    sendEmail($data['email'], $subject, $message);

                    // Notifier l'admin
                    $adminMessage = "Nouvelle adhésion : {$data['prenom']} {$data['nom']} ({$data['email']})";
                    sendEmail(SITE_EMAIL, "Nouveau membre GSCC", $adminMessage);

                    $success = 'Votre adhésion a été enregistrée avec succès ! Vous allez recevoir un email de confirmation.';

                    // Connecter automatiquement l'utilisateur
                    $login = loginUser($data['email'], $data['password']);
                    if ($login['success']) {
                        header("refresh:3;url=mon-compte.php");
                    }
                } else {
                    $error = 'Erreur lors de l\'inscription. Veuillez réessayer.';
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
    <title><?= e($page_title) ?> - <?= SITE_NAME ?></title>
    <meta name="description" content="<?= e($page_description) ?>">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        /* ── Variables ── */
        :root {
            --blue:      #003399;
            --blue-dark: #002277;
            --green:     #2E7D32;
            --text:      #0D1117;
            --text-2:    #1F2937;
            --text-3:    #374151;
            --muted:     #4B5563;
            --border:    #D1D5DB;
            --bg:        #F3F4F6;
            --white:     #FFFFFF;
        }

        /* ── Header ── */
        .page-header {
            background: linear-gradient(135deg, #003399 0%, #1a56cc 60%, #1a7abf 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        .page-header h1 {
            font-size: 2.4rem;
            font-weight: 800;
            color: #FFFFFF;
            margin-bottom: 12px;
            text-shadow: 0 1px 3px rgba(0,0,0,.3);
        }
        .page-header p {
            font-size: 1.1rem;
            color: #E8F0FE;
            font-weight: 400;
        }

        /* ── Section ── */
        .membership-section {
            padding: 80px 0;
            background: var(--bg);
        }

        .membership-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 40px;
        }

        /* ── Carte avantages ── */
        .benefits-card {
            background: var(--white);
            border-radius: 20px;
            padding: 36px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            position: sticky;
            top: 100px;
            border: 1px solid var(--border);
        }

        .benefits-card h3 {
            color: var(--blue);
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 28px;
            position: relative;
            padding-bottom: 14px;
        }
        .benefits-card h3::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0;
            width: 56px; height: 3px;
            background: var(--blue);
            border-radius: 2px;
        }

        .benefit-item {
            display: flex;
            gap: 14px;
            margin-bottom: 20px;
            padding: 14px;
            background: var(--bg);
            border-radius: 12px;
            border: 1px solid var(--border);
            transition: all .25s ease;
        }
        .benefit-item:hover {
            transform: translateX(6px);
            border-color: var(--blue);
            background: #EBF0FF;
        }

        .benefit-icon {
            width: 44px;
            height: 44px;
            background: var(--blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 17px;
            flex-shrink: 0;
        }

        .benefit-content h4 {
            color: var(--text);
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .benefit-content p {
            color: var(--muted);
            font-size: 13.5px;
            line-height: 1.6;
            font-weight: 400;
        }

        /* ── Formulaire ── */
        .membership-form {
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            border: 1px solid var(--border);
        }

        .membership-form h3 {
            color: var(--blue);
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 28px;
            position: relative;
            padding-bottom: 14px;
        }
        .membership-form h3::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0;
            width: 56px; height: 3px;
            background: var(--blue);
            border-radius: 2px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 0;
        }

        .form-group { margin-bottom: 20px; }

        .form-label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
            font-size: 14px;
            color: var(--text-2);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 15px;
            color: var(--text);
            background: var(--white);
            transition: all .25s ease;
        }
        .form-control::placeholder { color: #9CA3AF; }
        .form-control:focus {
            outline: none;
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(0,51,153,.1);
        }

        .form-control + small {
            display: block;
            margin-top: 5px;
            font-size: 12.5px;
            color: var(--muted);
            font-weight: 500;
        }

        /* ── Alerts ── */
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 14.5px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success {
            background: #D1FAE5;
            color: #065F46;
            border: 1.5px solid #6EE7B7;
        }
        .alert-error {
            background: #FEE2E2;
            color: #991B1B;
            border: 1.5px solid #FCA5A5;
        }

        /* ── Checkboxes ── */
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 18px 0;
        }
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            flex-shrink: 0;
            accent-color: var(--blue);
            cursor: pointer;
        }
        .checkbox-group label {
            font-size: 14px;
            color: var(--text-2);
            font-weight: 500;
            line-height: 1.6;
            cursor: pointer;
        }
        .checkbox-group label a {
            color: var(--blue);
            font-weight: 600;
            text-decoration: underline;
        }

        /* ── Bouton ── */
        .btn-submit {
            background: var(--blue);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all .25s ease;
            width: 100%;
            letter-spacing: .3px;
        }
        .btn-submit:hover {
            background: var(--blue-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,51,153,.35);
        }

        /* ── Lien connexion ── */
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--muted);
            font-weight: 500;
        }
        .login-link a {
            color: var(--blue);
            font-weight: 700;
            text-decoration: none;
        }
        .login-link a:hover { text-decoration: underline; }

        @media (max-width: 768px) {
            .membership-grid  { grid-template-columns: 1fr; }
            .form-row         { grid-template-columns: 1fr; }
            .benefits-card    { position: static; }
            .membership-form  { padding: 24px; }
        }
    </style>
</head>

<body>
    <?php include 'templates/header.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 data-aos="fade-up">Devenir membre</h1>
            <p data-aos="fade-up" data-aos-delay="100">
                Rejoignez notre communauté et participez activement à la lutte contre le cancer
            </p>
        </div>
    </div>

    <!-- Membership Section -->
    <section class="membership-section">
        <div class="container">
            <div class="membership-grid">
                <!-- Avantages -->
                <div class="benefits-card" data-aos="fade-right">
                    <h3>Avantages membres</h3>

                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Communauté active</h4>
                            <p>Rejoignez une communauté de personnes engagées dans la lutte contre le cancer</p>
                        </div>
                    </div>

                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Événements exclusifs</h4>
                            <p>Accès privilégié à nos événements, formations et conférences</p>
                        </div>
                    </div>

                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Newsletter mensuelle</h4>
                            <p>Recevez nos actualités et informations sur la recherche</p>
                        </div>
                    </div>

                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Forum privé</h4>
                            <p>Échangez avec d'autres membres et partagez vos expériences</p>
                        </div>
                    </div>

                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Priorité d'aide</h4>
                            <p>Traitement prioritaire pour les demandes d'aide et de soutien</p>
                        </div>
                    </div>

                    <!-- <div style="margin-top: 30px; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; color: white;">
                        <h4 style="color: white; margin-bottom: 10px;">Adhésion gratuite</h4>
                        <p style="opacity: 0.9;">Devenir membre du GSCC est totalement gratuit. Votre engagement est notre plus grande force !</p>
                    </div> -->
                </div>

                <!-- Formulaire -->
                <div class="membership-form" data-aos="fade-left">
                    <h3>Formulaire d'adhésion</h3>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?= e($success) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= e($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="prenom">Prénom *</label>
                                <input type="text" class="form-control" id="prenom" name="prenom"
                                    value="<?= e($_POST['prenom'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="nom">Nom *</label>
                                <input type="text" class="form-control" id="nom" name="nom"
                                    value="<?= e($_POST['nom'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">Email *</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?= e($_POST['email'] ?? '') ?>" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="telephone">Téléphone</label>
                                <input type="tel" class="form-control" id="telephone" name="telephone"
                                    value="<?= e($_POST['telephone'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="profession">Profession</label>
                                <input type="text" class="form-control" id="profession" name="profession"
                                    value="<?= e($_POST['profession'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="adresse">Adresse</label>
                            <input type="text" class="form-control" id="adresse" name="adresse"
                                value="<?= e($_POST['adresse'] ?? '') ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="ville">Ville</label>
                                <input type="text" class="form-control" id="ville" name="ville"
                                    value="<?= e($_POST['ville'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="code_postal">Code postal</label>
                                <input type="text" class="form-control" id="code_postal" name="code_postal"
                                    value="<?= e($_POST['code_postal'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="password">Mot de passe *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small style="color: #666;">Minimum 8 caractères</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="confirm_password">Confirmer le mot de passe *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>

                        <!-- <div class="membership-type">
                            <label class="form-label">Type d'adhésion</label>
                            <div class="type-option">
                                <input type="radio" name="type_membre" id="type_actif" value="actif" checked>
                                <label for="type_actif">Membre actif - Participation aux activités</label>
                            </div>
                            <div class="type-option">
                                <input type="radio" name="type_membre" id="type_bienfaiteur" value="bienfaiteur">
                                <label for="type_bienfaiteur">Membre bienfaiteur - Soutien financier régulier</label>
                            </div>
                            <div class="type-option">
                                <input type="radio" name="type_membre" id="type_honoraire" value="honoraire">
                                <label for="type_honoraire">Membre honoraire - Personnalité soutenant la cause</label>
                            </div>
                        </div> -->

                        <div class="checkbox-group">
                            <input type="checkbox" name="newsletter" id="newsletter" checked>
                            <label for="newsletter">Je souhaite recevoir la newsletter du GSCC</label>
                        </div>

                        <div class="checkbox-group">
                            <input type="checkbox" name="conditions" id="conditions" required>
                            <label for="conditions">J'accepte les <a href="conditions-utilisation.php" target="_blank">conditions d'adhésion</a> et la <a href="politique-confidentialite.php" target="_blank">politique de confidentialité</a> *</label>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-user-plus"></i>
                            Devenir membre
                        </button>

                        <p class="login-link">
                            Déjà membre ? <a href="connexion.php">Connectez-vous</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        // Validation du mot de passe en temps réel
        document.getElementById('password').addEventListener('input', function() {
            if (this.value.length < 8) {
                this.style.borderColor = '#f44336';
            } else {
                this.style.borderColor = '#4CAF50';
            }
        });

        // Vérification de la correspondance des mots de passe
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            if (this.value !== password) {
                this.style.borderColor = '#f44336';
            } else {
                this.style.borderColor = '#4CAF50';
            }
        });
    </script>

    <script>
// ════════════════════════════════════════════════════════════
// RESTRICTIONS FORMULAIRE — commun aux 3 pages
// ════════════════════════════════════════════════════════════
(function () {

    // ── Utilitaires ────────────────────────────────────────
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

    // ── 1. NOM & PRÉNOM — lettres, espaces, tirets, apostrophes seulement ──
    document.querySelectorAll('input[name="nom"], input[name="prenom"]').forEach(function (inp) {
        inp.setAttribute('autocomplete', inp.name === 'nom' ? 'family-name' : 'given-name');
        inp.setAttribute('maxlength', '60');

        inp.addEventListener('keypress', function (e) {
            // Autoriser : lettres (toutes langues via regex unicode), espace, tiret, apostrophe
            if (!/[\p{L}\s\-']/u.test(e.key) && e.key.length === 1) {
                e.preventDefault();
            }
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

    // ── 2. TÉLÉPHONE — chiffres, +, espaces, tirets, parenthèses ──────────
    document.querySelectorAll('input[type="tel"], input[name="telephone"]').forEach(function (inp) {
        inp.setAttribute('inputmode', 'tel');
        inp.setAttribute('maxlength', '20');
        inp.setAttribute('placeholder', inp.placeholder || 'Ex: +509 37 00 00 00');

        inp.addEventListener('keypress', function (e) {
            if (!/[0-9+\s\-().]/.test(e.key) && e.key.length === 1) {
                e.preventDefault();
            }
        });
        inp.addEventListener('input', function () {
            var clean = this.value.replace(/[^0-9+\s\-(). ]/g, '');
            if (this.value !== clean) this.value = clean;
        });
        inp.addEventListener('blur', function () {
            var v = this.value.trim();
            if (v.length === 0 && !this.required) { removeError(this); return; }
            if (v.length < 7 || !/^[0-9+\s\-().]+$/.test(v)) {
                addError(this, 'Numéro invalide. Utilisez uniquement des chiffres.');
            } else {
                addOk(this);
            }
        });
    });

    // ── 3. EMAIL ────────────────────────────────────────────────────────────
    document.querySelectorAll('input[type="email"]').forEach(function (inp) {
        inp.addEventListener('blur', function () {
            var v = this.value.trim();
            if (v.length === 0) { removeError(this); return; }
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!re.test(v)) {
                addError(this, 'Adresse email invalide.');
            } else {
                addOk(this);
            }
        });
        inp.addEventListener('input', function () {
            removeError(this);
            this.style.borderColor = '';
        });
    });

    // ── 4. MOT DE PASSE — minimum 8 car, 1 majuscule, 1 chiffre ───────────
    var pwInput = document.getElementById('password');
    var confirmInput = document.getElementById('confirm_password');

    if (pwInput) {
        pwInput.setAttribute('minlength', '8');
        pwInput.addEventListener('blur', function () {
            var v = this.value;
            if (v.length === 0) { removeError(this); return; }
            if (v.length < 8) {
                addError(this, 'Minimum 8 caractères requis.');
            } else if (!/[A-Z]/.test(v)) {
                addError(this, 'Ajoutez au moins une lettre majuscule.');
            } else if (!/[0-9]/.test(v)) {
                addError(this, 'Ajoutez au moins un chiffre.');
            } else {
                addOk(this);
            }
        });
    }

    if (confirmInput && pwInput) {
        confirmInput.addEventListener('blur', function () {
            if (this.value.length === 0) { removeError(this); return; }
            if (this.value !== pwInput.value) {
                addError(this, 'Les mots de passe ne correspondent pas.');
            } else {
                addOk(this);
            }
        });
    }

    // ── 5. CODE POSTAL — chiffres uniquement ───────────────────────────────
    var cp = document.getElementById('code_postal');
    if (cp) {
        cp.setAttribute('inputmode', 'numeric');
        cp.setAttribute('maxlength', '10');
        cp.addEventListener('keypress', function (e) {
            if (!/[0-9]/.test(e.key) && e.key.length === 1) e.preventDefault();
        });
        cp.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        cp.addEventListener('blur', function () {
            var v = this.value.trim();
            if (v.length === 0) { removeError(this); return; }
            if (v.length < 3) addError(this, 'Code postal invalide.');
            else addOk(this);
        });
    }

    // ── 6. DATE DE NAISSANCE — âge entre 16 et 100 ans ────────────────────
    var dob = document.querySelector('input[name="date_naissance"]');
    if (dob) {
        var today = new Date();
        var maxDate = new Date(today.getFullYear() - 16, today.getMonth(), today.getDate());
        var minDate = new Date(today.getFullYear() - 100, today.getMonth(), today.getDate());
        dob.setAttribute('max', maxDate.toISOString().split('T')[0]);
        dob.setAttribute('min', minDate.toISOString().split('T')[0]);

        dob.addEventListener('blur', function () {
            var v = this.value;
            if (!v) { removeError(this); return; }
            var d = new Date(v);
            if (d > maxDate) {
                addError(this, 'Vous devez avoir au moins 16 ans.');
            } else if (d < minDate) {
                addError(this, 'Date invalide.');
            } else {
                addOk(this);
            }
        });
    }

    // ── 7. MOTIVATIONS — minimum 50 caractères ────────────────────────────
    var motiv = document.querySelector('textarea[name="motivations"]');
    if (motiv) {
        // Compteur de caractères
        var counter = document.createElement('span');
        counter.style.cssText = 'font-size:12px;color:#6B7280;display:block;margin-top:4px;text-align:right;';
        counter.textContent = '0 / 50 caractères minimum';
        motiv.parentNode.appendChild(counter);

        motiv.addEventListener('input', function () {
            var len = this.value.length;
            counter.textContent = len + ' / 50 caractères minimum';
            counter.style.color = len >= 50 ? '#16A34A' : '#6B7280';
            if (len >= 50) removeError(this);
        });
        motiv.addEventListener('blur', function () {
            if (this.value.length > 0 && this.value.length < 50) {
                addError(this, 'Décrivez vos motivations (minimum 50 caractères).');
            } else if (this.value.length >= 50) {
                addOk(this);
            }
        });
    }

    // ── 8. DISPONIBILITÉS — longueur raisonnable ──────────────────────────
    var dispo = document.querySelector('input[name="disponibilites"]');
    if (dispo) {
        dispo.setAttribute('maxlength', '200');
    }

    // ── 9. PROFESSION — lettres uniquement (pas de chiffres seuls) ────────
    document.querySelectorAll('input[name="profession"]').forEach(function (inp) {
        inp.setAttribute('maxlength', '80');
        inp.addEventListener('input', function () {
            this.value = this.value.replace(/[^a-zA-ZÀ-ÿ0-9\s\-',.()]/g, '');
        });
    });

    // ── 10. VILLE — lettres et espaces ────────────────────────────────────
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