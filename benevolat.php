<?php
// devenir-benevole.php
require_once 'includes/config.php';

$page_title = 'Devenir bénévole';
$page_description = 'Rejoignez notre équipe de bénévoles et donnez de votre temps pour soutenir notre mission.';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom           = trim(strip_tags($_POST['nom']            ?? ''));
    $prenom        = trim(strip_tags($_POST['prenom']         ?? ''));
    $email         = trim(strip_tags($_POST['email']          ?? ''));
    $telephone     = trim(strip_tags($_POST['telephone']      ?? ''));
    $date_naissance= trim(strip_tags($_POST['date_naissance'] ?? ''));
    $profession    = trim(strip_tags($_POST['profession']     ?? ''));
    $disponibilites= trim(strip_tags($_POST['disponibilites'] ?? ''));
    $motivations   = trim(strip_tags($_POST['motivations']    ?? ''));
    $competences   = isset($_POST['competences']) && is_array($_POST['competences'])
                     ? array_map('strip_tags', $_POST['competences']) : [];
    $engagement    = isset($_POST['engagement']);

    // Validation
    if (empty($nom) || empty($prenom) || empty($email) || empty($telephone)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide.';
    } elseif (empty($motivations) || strlen($motivations) < 50) {
        $error = 'Veuillez décrire vos motivations (minimum 50 caractères).';
    } elseif (!$engagement) {
        $error = 'Vous devez accepter la charte du bénévole.';
    } else {

        /* ── INSERT en base ── */
        try {
            $competences_json = !empty($competences) ? json_encode($competences, JSON_UNESCAPED_UNICODE) : null;
            $dob = !empty($date_naissance) ? $date_naissance : null;

            $stmt = $pdo->prepare(
                "INSERT INTO candidatures_benevoles
                    (nom, prenom, email, telephone, date_naissance, profession,
                     disponibilites, competences, motivations, statut, date_candidature)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW())"
            );
            $stmt->execute([
                $nom, $prenom, $email, $telephone, $dob, $profession,
                $disponibilites, $competences_json, $motivations
            ]);

            /* ── Email confirmation candidat ── */
            try {
                $sname = defined('SITE_NAME') ? SITE_NAME : 'GSCC';
                $subject_c = "Candidature de bénévolat — $sname";
                $msg_c = "Bonjour $prenom $nom,

"
                    . "Nous avons bien reçu votre candidature pour devenir bénévole au $sname.
"
                    . "Notre équipe va l'étudier et vous recontactera dans les plus brefs délais.

"
                    . "Compétences mentionnées : " . (!empty($competences) ? implode(', ', $competences) : 'Non précisées') . "

"
                    . "Merci pour votre engagement !

Cordialement,
L'équipe $sname";
                if (function_exists('sendEmail')) sendEmail($email, $subject_c, $msg_c);
            } catch (Exception $ignored) {}

            /* ── Email notification admin ── */
            try {
                $semail = defined('SITE_EMAIL') ? SITE_EMAIL : '';
                $sname  = defined('SITE_NAME')  ? SITE_NAME  : 'GSCC';
                $msg_a = "Nouvelle candidature de bénévolat reçue sur $sname

"
                    . "Nom complet  : $prenom $nom
"
                    . "Email        : $email
"
                    . "Téléphone    : $telephone
"
                    . "Profession   : " . ($profession ?: 'Non précisée') . "
"
                    . "Disponibilités: " . ($disponibilites ?: 'Non précisées') . "
"
                    . "Compétences  : " . (!empty($competences) ? implode(', ', $competences) : 'Non précisées') . "

"
                    . "Motivations :
$motivations

"
                    . "Statut : En attente de traitement";
                if ($semail && function_exists('sendEmail'))
                    sendEmail($semail, "🙋 Nouvelle candidature bénévole — $sname", $msg_a);
            } catch (Exception $ignored) {}

            $success = 'Votre candidature a été envoyée avec succès. Nous vous contacterons rapidement.';
            $_POST   = [];

        } catch (Exception $e) {
            $error = "Erreur lors de l'enregistrement : " . $e->getMessage();
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
        :root {
            --blue:      #003399;
            --blue-dark: #002277;
            --text:      #0D1117;
            --text-2:    #1F2937;
            --muted:     #4B5563;
            --border:    #D1D5DB;
            --bg:        #F3F4F6;
            --white:     #FFFFFF;
        }

        /* ── Header ── */
        .page-header {
            background: linear-gradient(135deg, #003399 0%, #2E7D32 100%);
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
        }

        /* ── Section ── */
        .benevole-section { padding: 60px 0; background: var(--bg); }

        .benevole-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
        }

        /* ── Formulaire ── */
        .form-container {
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            border: 1px solid var(--border);
        }
        .form-container h2 {
            color: var(--blue);
            font-size: 1.4rem;
            font-weight: 800;
            margin-bottom: 28px;
            position: relative;
            padding-bottom: 14px;
        }
        .form-container h2::after {
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
        .form-group label {
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
        textarea.form-control { min-height: 120px; resize: vertical; }

        /* ── Compétences checkboxes ── */
        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin: 12px 0 0;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 9px;
        }
        .checkbox-item input[type="checkbox"] {
            width: 17px; height: 17px;
            accent-color: var(--blue);
            flex-shrink: 0;
            cursor: pointer;
        }
        .checkbox-item label {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-2);
            cursor: pointer;
            margin: 0;
        }

        /* ── Charte ── */
        .charte-box {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 18px 20px;
            margin: 20px 0;
            max-height: 200px;
            overflow-y: auto;
        }
        .charte-box h4 {
            color: var(--blue);
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .charte-box ul { padding-left: 18px; }
        .charte-box li {
            color: var(--text-2);
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 6px;
            line-height: 1.6;
        }

        /* ── Engagement ── */
        .engagement-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 16px 0 24px;
        }
        .engagement-row input[type="checkbox"] {
            width: 18px; height: 18px;
            margin-top: 2px;
            flex-shrink: 0;
            accent-color: var(--blue);
            cursor: pointer;
        }
        .engagement-row label {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-2);
            cursor: pointer;
            line-height: 1.6;
        }

        /* ── Bouton ── */
        .btn-submit {
            background: var(--blue);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 10px;
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
        .btn-submit i { margin-right: 8px; }

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
        .alert-success { background: #D1FAE5; color: #065F46; border: 1.5px solid #6EE7B7; }
        .alert-error   { background: #FEE2E2; color: #991B1B; border: 1.5px solid #FCA5A5; }

        /* ── Sidebar ── */
        .sidebar-info {
            background: var(--white);
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 24px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            border: 1px solid var(--border);
        }
        .sidebar-info h3 {
            color: var(--blue);
            font-size: 1rem;
            font-weight: 800;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--border);
        }
        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 0;
            border-bottom: 1px solid var(--border);
        }
        .info-item:last-child { border-bottom: none; }
        .info-icon {
            width: 44px; height: 44px;
            background: #EBF0FF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--blue);
            font-size: 18px;
            flex-shrink: 0;
        }
        .info-text h4 {
            color: var(--text);
            font-size: 14.5px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .info-text p {
            color: var(--muted);
            font-size: 13px;
            font-weight: 400;
            line-height: 1.6;
        }

        /* ── Témoignage ── */
        .testimonial-mini {
            background: var(--bg);
            border-radius: 10px;
            padding: 18px;
            margin-top: 16px;
            font-style: italic;
            font-size: 14px;
            color: var(--text-2);
            line-height: 1.7;
            border: 1px solid var(--border);
        }
        .testimonial-mini i { color: var(--blue); opacity: .5; font-size: 18px; margin-right: 6px; }
        .testimonial-author {
            margin-top: 10px;
            font-style: normal;
            font-weight: 700;
            font-size: 13px;
            color: var(--blue);
        }

        /* ── Contact sidebar ── */
        .sidebar-info p {
            font-size: 13.5px;
            color: var(--text-2);
            font-weight: 400;
            line-height: 1.7;
            margin-bottom: 10px;
        }
        .sidebar-info p strong { color: var(--text); font-weight: 700; }
        .sidebar-info p i { color: var(--blue); margin-right: 6px; }

        @media (max-width: 768px) {
            .benevole-layout  { grid-template-columns: 1fr; }
            .form-row         { grid-template-columns: 1fr; }
            .checkbox-group   { grid-template-columns: 1fr; }
            .form-container   { padding: 24px; }
        }
    </style>
</head>

<body>
    <?php include 'templates/header.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 data-aos="fade-up">Devenir bénévole</h1>
            <p data-aos="fade-up" data-aos-delay="100">
                Donnez de votre temps, changez des vies
            </p>
        </div>
    </div>

    <!-- Benevole Section -->
    <section class="benevole-section">
        <div class="container">
            <div class="benevole-layout">
                <!-- Formulaire -->
                <div class="form-container" data-aos="fade-right">
                    <h2>Formulaire de candidature</h2>

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

                    <form method="POST" id="benevoleForm">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label>Nom *</label>
                                <input type="text" name="nom" class="form-control" value="<?= e($_POST['nom'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Prénom *</label>
                                <input type="text" name="prenom" class="form-control" value="<?= e($_POST['prenom'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Téléphone *</label>
                                <input type="tel" name="telephone" class="form-control" value="<?= e($_POST['telephone'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Date de naissance</label>
                                <input type="date" name="date_naissance" class="form-control" value="<?= e($_POST['date_naissance'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label>Profession</label>
                                <input type="text" name="profession" class="form-control" value="<?= e($_POST['profession'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Disponibilités</label>
                            <input type="text" name="disponibilites" class="form-control" placeholder="Ex: Soirées, week-ends, quelques heures par semaine..."
                                value="<?= e($_POST['disponibilites'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Compétences / Centres d'intérêt</label>
                            <div class="checkbox-group">
                                <div class="checkbox-item">
                                    <input type="checkbox" name="competences[]" value="Accompagnement" id="comp1" <?= in_array('Accompagnement', $_POST['competences'] ?? []) ? 'checked' : '' ?>>
                                    <label for="comp1">Accompagnement</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" name="competences[]" value="Administratif" id="comp2" <?= in_array('Administratif', $_POST['competences'] ?? []) ? 'checked' : '' ?>>
                                    <label for="comp2">Administratif</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" name="competences[]" value="Communication" id="comp3" <?= in_array('Communication', $_POST['competences'] ?? []) ? 'checked' : '' ?>>
                                    <label for="comp3">Communication</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" name="competences[]" value="Événementiel" id="comp4" <?= in_array('Événementiel', $_POST['competences'] ?? []) ? 'checked' : '' ?>>
                                    <label for="comp4">Événementiel</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" name="competences[]" value="Informatique" id="comp5" <?= in_array('Informatique', $_POST['competences'] ?? []) ? 'checked' : '' ?>>
                                    <label for="comp5">Informatique</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" name="competences[]" value="Santé" id="comp6" <?= in_array('Santé', $_POST['competences'] ?? []) ? 'checked' : '' ?>>
                                    <label for="comp6">Santé</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Vos motivations *</label>
                            <textarea name="motivations" class="form-control" required placeholder="Décrivez pourquoi vous souhaitez devenir bénévole..."><?= e($_POST['motivations'] ?? '') ?></textarea>
                        </div>

                        <!-- Charte du bénévole -->
                        <div class="charte-box">
                            <h4>Charte du bénévole</h4>
                            <ul>
                                <li>Respecter la confidentialité des informations</li>
                                <li>Être ponctuel et assidu dans ses engagements</li>
                                <li>Adopter une attitude bienveillante envers les patients</li>
                                <li>Travailler en équipe et respecter les consignes</li>
                                <li>Signaler toute difficulté à son référent</li>
                            </ul>
                        </div>

                        <div class="engagement-row">
                            <input type="checkbox" name="engagement" id="engagement" required>
                            <label for="engagement">J'ai lu et j'accepte la charte du bénévole *</label>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane"></i>
                            Envoyer ma candidature
                        </button>
                    </form>
                </div>

                <!-- Sidebar -->
                <div class="sidebar" data-aos="fade-left">
                    <div class="sidebar-info">
                        <h3>Pourquoi devenir bénévole ?</h3>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="info-text">
                                <h4>Donner du sens</h4>
                                <p>Apportez votre pierre à l'édifice et aidez ceux qui en ont besoin</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="info-text">
                                <h4>Rencontrer</h4>
                                <p>Faites partie d'une équipe passionnée et rencontrez des personnes inspirantes</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="info-text">
                                <h4>Apprendre</h4>
                                <p>Développez de nouvelles compétences et enrichissez votre expérience</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="info-text">
                                <h4>Flexibilité</h4>
                                <p>Choisissez vos missions selon vos disponibilités et envies</p>
                            </div>
                        </div>
                    </div>

                    <div class="sidebar-info">
                        <h3>Témoignage</h3>
                        <div class="testimonial-mini">
                            <i class="fas fa-quote-left"></i>
                            Être bénévole au GSCC m'a permis de donner un sens à mon temps libre.
                            Accompagner les patients et voir leur sourire, c'est une richesse inestimable.
                            <div class="testimonial-author">- Marie, bénévole depuis 2 ans</div>
                        </div>
                    </div>

                    <div class="sidebar-info">
                        <h3>Besoin d'info ?</h3>
                        <p style="margin-bottom: 15px;">
                            Notre responsable bénévolat est à votre disposition pour répondre à vos questions.
                        </p>
                        <p><i class="fas fa-phone"></i> <strong>+(509) 29 47 47 22</strong></p>
                        <p><i class="fas fa-envelope"></i> <strong>benevolat@gscc.org</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>

    <script>
        // Validation du formulaire
        document.getElementById('benevoleForm').addEventListener('submit', function(e) {
            const motivations = document.querySelector('textarea[name="motivations"]').value;
            if (motivations.length < 50) {
                e.preventDefault();
                alert('Veuillez détailler un peu plus vos motivations (minimum 50 caractères).');
            }
        });
    </script>
</body>

</html>