<?php
// politique-cookies.php
require_once 'includes/config.php';

$page_title = 'Politique en matière de cookies';
$page_description = 'Comment nous utilisons les cookies sur notre site.';

$date_maj = '15 janvier 2025';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> - <?= SITE_NAME ?></title>
    <meta name="description" content="<?= e($page_description) ?>">

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= CSS_URL ?>style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        .page-header {
            background: linear-gradient(135deg, var(--rose), var(--teal));
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .legal-section {
            padding: 60px 0;
            background: var(--warm-white);
        }

        .legal-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
        }

        .legal-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--grey-light);
            color: var(--grey);
            font-size: 14px;
        }

        .legal-meta i {
            margin-right: 5px;
            color: var(--rose);
        }

        .legal-section-title {
            color: var(--charcoal);
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            margin: 40px 0 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .legal-section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--rose);
        }

        .legal-text {
            color: var(--charcoal);
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .cookie-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .cookie-table th {
            background: var(--rose);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .cookie-table td {
            padding: 15px;
            border-bottom: 1px solid var(--grey-light);
            color: var(--grey);
        }

        .cookie-table tr:last-child td {
            border-bottom: none;
        }

        .cookie-table tr:hover td {
            background: var(--warm-white);
        }

        .cookie-type {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .type-essentiel {
            background: #2A7F7F20;
            color: #2A7F7F;
        }

        .type-fonctionnel {
            background: #D94F7A20;
            color: #D94F7A;
        }

        .type-analytique {
            background: #C9933A20;
            color: #C9933A;
        }

        .cookie-controls {
            background: var(--warm-white);
            border-radius: 15px;
            padding: 30px;
            margin: 40px 0;
            border: 1px solid var(--grey-light);
        }

        .cookie-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid var(--grey-light);
        }

        .cookie-option:last-child {
            border-bottom: none;
        }

        .cookie-option-info h4 {
            color: var(--charcoal);
            margin-bottom: 5px;
        }

        .cookie-option-info p {
            color: var(--grey);
            font-size: 14px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: var(--rose);
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        input:disabled+.slider {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-save-cookies {
            background: var(--rose);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
            width: 100%;
        }

        .btn-save-cookies:hover {
            background: #C0306A;
            transform: translateY(-2px);
        }

        .cookie-icon {
            font-size: 60px;
            color: var(--rose-light);
            text-align: center;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .legal-container {
                padding: 30px 20px;
            }

            .cookie-table {
                font-size: 13px;
            }

            .cookie-table th,
            .cookie-table td {
                padding: 10px;
            }

            .cookie-option {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }

        /* ── Lisibilité texte ── */
        :root {
            --charcoal: #0D1117;
            --grey: #4B5563;
            --grey-light: #6B7280;
        }

        /* Texte sur fond coloré — toujours blanc */
        .page-header, .page-header *,
        .cta-section, .cta-section *,
        .hero, .hero *,
        .btn-contact, .btn-contact *,
        .faq-search button,
        .faq-category-btn.active,
        .faq-category-btn:hover {
            color: #FFFFFF !important;
        }

        /* Texte sur fond clair — gris lisible */
        .section-text p, .section-content p,
        .legal-text p, .faq-answer p,
        .content-text p, .intro-text,
        .update-date, .toc-link, .cookie-desc,
        .stat-label, .contact-text,
        .breadcrumb a, .breadcrumb span {
            color: #374151 !important;
        }

        /* Titres dans le contenu — noir profond */
        .main-content h1, .main-content h2, .main-content h3,
        .main-content h4, .main-content h5, .main-content h6,
        .legal-section h1, .legal-section h2, .legal-section h3,
        .legal-section h4, .legal-section h5, .legal-section h6,
        .faq-section h1, .faq-section h2, .faq-section h3,
        .faq-section h4, .faq-section h5, .faq-section h6,
        .section-title, .toc-title, .page-subtitle {
            color: #0D1117 !important;
        }

        /* Boutons non-actifs (fond blanc) */
        .faq-category-btn:not(.active):not(:hover) {
            color: #0D1117 !important;
        }
    </style>
</head>

<body>
    <?php include 'templates/header.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 data-aos="fade-up"><?= e($page_title) ?></h1>
            <p data-aos="fade-up" data-aos-delay="100">
                Comment nous utilisons les cookies sur notre site
            </p>
        </div>
    </div>

    <!-- Legal Section -->
    <section class="legal-section">
        <div class="container">
            <div class="legal-container" data-aos="fade-up">

                <!-- Meta informations -->
                <div class="legal-meta">
                    <span><i class="far fa-calendar-alt"></i> Dernière mise à jour : <?= $date_maj ?></span>
                </div>

                <div class="cookie-icon">
                    <i class="fas fa-cookie-bite"></i>
                </div>

                <!-- Introduction -->
                <p class="legal-text">
                    Lors de votre visite sur notre site, des cookies peuvent être déposés sur votre navigateur.
                    Cette page vous explique ce que sont les cookies, comment nous les utilisons, et comment
                    vous pouvez les contrôler.
                </p>

                <div class="legal-note">
                    <i class="fas fa-info-circle"></i>
                    <strong>Qu'est-ce qu'un cookie ?</strong> Un cookie est un petit fichier texte déposé sur
                    votre ordinateur ou votre appareil mobile lors de votre visite sur un site. Il permet de
                    stocker des informations sur votre navigation pour faciliter votre expérience.
                </div>

                <!-- Types de cookies -->
                <h2 class="legal-section-title">Types de cookies utilisés</h2>

                <table class="cookie-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Nom</th>
                            <th>Durée</th>
                            <th>Finalité</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="cookie-type type-essentiel">Essentiel</span></td>
                            <td><code>PHPSESSID</code></td>
                            <td>Session</td>
                            <td>Maintient votre session utilisateur (connexion)</td>
                        </tr>
                        <tr>
                            <td><span class="cookie-type type-essentiel">Essentiel</span></td>
                            <td><code>csrf_token_gscc</code></td>
                            <td>Session</td>
                            <td>Sécurise les formulaires contre les attaques CSRF</td>
                        </tr>
                        <tr>
                            <td><span class="cookie-type type-fonctionnel">Fonctionnel</span></td>
                            <td><code>lang</code></td>
                            <td>30 jours</td>
                            <td>Mémorise votre préférence de langue</td>
                        </tr>
                        <tr>
                            <td><span class="cookie-type type-fonctionnel">Fonctionnel</span></td>
                            <td><code>cookie_consent</code></td>
                            <td>6 mois</td>
                            <td>Enregistre votre consentement aux cookies</td>
                        </tr>
                        <tr>
                            <td><span class="cookie-type type-analytique">Analytique</span></td>
                            <td><code>_ga</code></td>
                            <td>2 ans</td>
                            <td>Identifie les visiteurs (Google Analytics)</td>
                        </tr>
                        <tr>
                            <td><span class="cookie-type type-analytique">Analytique</span></td>
                            <td><code>_gid</code></td>
                            <td>24h</td>
                            <td>Distingue les utilisateurs (Google Analytics)</td>
                        </tr>
                        <tr>
                            <td><span class="cookie-type type-analytique">Analytique</span></td>
                            <td><code>_gat</code></td>
                            <td>1 min</td>
                            <td>Limite le taux de requêtes (Google Analytics)</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Gestion des cookies -->
                <h2 class="legal-section-title">Gérer vos préférences</h2>

                <p class="legal-text">
                    Vous pouvez à tout moment choisir d'accepter ou de refuser certains types de cookies.
                    Les cookies essentiels au fonctionnement du site ne peuvent pas être désactivés.
                </p>

                <div class="cookie-controls">

                    <div class="cookie-option">
                        <div class="cookie-option-info">
                            <h4>Cookies essentiels <span class="cookie-type type-essentiel">Toujours actifs</span></h4>
                            <p>Nécessaires au fonctionnement du site (connexion, sécurité). Ils ne peuvent pas être désactivés.</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked disabled>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="cookie-option">
                        <div class="cookie-option-info">
                            <h4>Cookies fonctionnels</h4>
                            <p>Permettent de mémoriser vos préférences (langue, région) pour améliorer votre expérience.</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="functionalCookies" checked>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="cookie-option">
                        <div class="cookie-option-info">
                            <h4>Cookies analytiques</h4>
                            <p>Nous aident à comprendre comment les visiteurs interagissent avec le site (pages visitées, temps passé).</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="analyticsCookies" checked>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <button class="btn-save-cookies" onclick="saveCookiePreferences()">
                        <i class="fas fa-save"></i> Enregistrer mes préférences
                    </button>

                </div>

                <!-- Cookies tiers -->
                <h2 class="legal-section-title">Cookies tiers</h2>

                <p class="legal-text">
                    Lorsque vous utilisez notre site, des cookies tiers peuvent être déposés :
                </p>

                <ul class="legal-list">
                    <li><strong>Google Analytics :</strong> mesure d'audience et analyse du comportement des utilisateurs</li>
                    <li><strong>YouTube :</strong> lorsque vous visionnez des vidéos intégrées</li>
                    <li><strong>PayPal/Stripe :</strong> pour le traitement sécurisé des paiements</li>
                    <li><strong>Facebook/Twitter :</strong> boutons de partage et intégration de contenus</li>
                </ul>

                <!-- Consentement -->
                <h2 class="legal-section-title">Votre consentement</h2>

                <p class="legal-text">
                    Lors de votre première visite, une bannière vous informe de l'utilisation des cookies et vous
                    permet de paramétrer vos préférences. En continuant votre navigation sans paramétrer, vous
                    consentez à l'utilisation des cookies.
                </p>

                <p class="legal-text">
                    Vous pouvez modifier vos préférences à tout moment en cliquant sur le lien
                    <a href="#cookie-controls">"Gérer les cookies"</a> en bas de page.
                </p>

                <!-- Comment refuser -->
                <h2 class="legal-section-title">Comment refuser les cookies ?</h2>

                <p class="legal-text">
                    Outre notre interface de paramétrage, vous pouvez configurer votre navigateur pour refuser
                    les cookies :
                </p>

                <ul class="legal-list">
                    <li><strong>Google Chrome :</strong> Paramètres → Confidentialité et sécurité → Cookies</li>
                    <li><strong>Firefox :</strong> Options → Vie privée et sécurité → Cookies</li>
                    <li><strong>Safari :</strong> Préférences → Confidentialité → Cookies</li>
                    <li><strong>Edge :</strong> Paramètres → Cookies et autorisations</li>
                </ul>

                <!-- Contact -->
                <div class="legal-note" style="margin-top: 40px;">
                    <i class="fas fa-question-circle"></i>
                    <strong>Une question sur les cookies ?</strong><br>
                    Contactez-nous à <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a> ou par téléphone au
                    <a href="tel:+50929474722">2947 47 22</a>.
                </div>

            </div>
        </div>
    </section>

    <script>
        function saveCookiePreferences() {
            const functional = document.getElementById('functionalCookies').checked;
            const analytics = document.getElementById('analyticsCookies').checked;

            // Ici vous pouvez ajouter un appel AJAX pour sauvegarder les préférences
            alert('Préférences enregistrées ! (Functional: ' + functional + ', Analytics: ' + analytics + ')');
        }

        // Simuler le chargement des préférences existantes
        document.addEventListener('DOMContentLoaded', function() {
            // Vérifier si des préférences sont stockées dans un cookie
            // et cocher les cases correspondantes
        });
    </script>

    <?php include 'templates/footer.php'; ?>
</body>

</html>