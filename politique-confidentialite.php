<?php
// politique-confidentialite.php
require_once 'includes/config.php';

$page_title = 'Politique de confidentialité';
$page_description = 'Notre engagement pour la protection de vos données personnelles.';

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

        .legal-section-title:first-of-type {
            margin-top: 0;
        }

        .legal-text {
            color: var(--charcoal);
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .legal-list {
            margin: 20px 0 20px 20px;
            padding-left: 20px;
        }

        .legal-list li {
            margin-bottom: 10px;
            color: var(--grey);
            line-height: 1.6;
        }

        .legal-list li::marker {
            color: var(--rose);
        }

        .legal-note {
            background: var(--rose-pale);
            border-left: 4px solid var(--rose);
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
            font-style: italic;
            color: var(--charcoal);
        }

        .legal-note i {
            color: var(--rose);
            margin-right: 10px;
            font-size: 20px;
            vertical-align: middle;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }

        .data-table th {
            background: var(--rose);
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }

        .data-table td {
            padding: 12px;
            border-bottom: 1px solid var(--grey-light);
            color: var(--grey);
        }

        .data-table tr:hover {
            background: var(--warm-white);
        }

        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--rose-pale);
            color: var(--rose);
            padding: 12px 25px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            margin-top: 30px;
            transition: all 0.3s;
            border: 1px solid var(--rose-light);
        }

        .btn-print:hover {
            background: var(--rose);
            color: white;
        }

        .contact-card {
            background: var(--warm-white);
            border-radius: 15px;
            padding: 30px;
            margin-top: 40px;
            border: 1px solid var(--grey-light);
            display: flex;
            align-items: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        .contact-icon {
            width: 70px;
            height: 70px;
            background: var(--rose-pale);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--rose);
            font-size: 28px;
        }

        .contact-info h3 {
            color: var(--charcoal);
            margin-bottom: 10px;
        }

        .contact-info p {
            color: var(--grey);
            margin-bottom: 5px;
        }

        .contact-info a {
            color: var(--rose);
            text-decoration: none;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .legal-container {
                padding: 30px 20px;
            }

            .contact-card {
                flex-direction: column;
                text-align: center;
            }

            .data-table {
                font-size: 14px;
            }
        }

        /* ── Lisibilité texte ── */
        :root {
            --charcoal: #0D1117;
            --grey: #4B5563;
            --grey-light: #6B7280;
        }
        p, li, td, th, span:not(.badge):not(.tag):not(.label) {
            color: #0D1117;
        }
        .section-text p,
        .section-content p,
        .legal-text p,
        .faq-answer p,
        .content-text p,
        .intro-text,
        .update-date,
        .toc-link,
        .cookie-desc,
        .stat-label,
        .contact-text,
        .breadcrumb a,
        .breadcrumb span {
            color: #1F2937 !important;
        }
        h1, h2, h3, h4, h5, h6 {
            color: #0D1117;
        }
        .section-title,
        .faq-question,
        .toc-title,
        .page-subtitle {
            color: #0D1117 !important;
        }
        /* Texte sur fond coloré reste blanc */
        .page-header p,
        .page-header h1,
        .cta-section p,
        .cta-section h2,
        .hero p,
        .hero h1 {
            color: #FFFFFF !important;
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
                Comment nous protégeons vos données personnelles
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
                    <a href="#" onclick="window.print(); return false;" class="btn-print">
                        <i class="fas fa-print"></i> Imprimer
                    </a>
                </div>

                <!-- Introduction -->
                <p class="legal-text">
                    Le Groupe de Support Contre le Cancer (GSCC) accorde une importance primordiale à la protection
                    de vos données personnelles. La présente politique de confidentialité vous explique quelles
                    informations nous collectons, pourquoi nous les collectons, et comment nous les protégeons.
                </p>

                <div class="legal-note">
                    <i class="fas fa-shield-alt"></i>
                    <strong>Notre engagement :</strong> Nous ne vendons jamais vos données à des tiers.
                    Vos informations sont utilisées uniquement dans le cadre de nos missions.
                </div>

                <!-- Article 1 -->
                <h2 class="legal-section-title">1. Données collectées</h2>
                <p class="legal-text">
                    Nous collectons différentes catégories de données selon votre interaction avec notre site :
                </p>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type de données</th>
                            <th>Exemples</th>
                            <th>Finalité</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Données d'identification</strong></td>
                            <td>Nom, prénom, email, téléphone</td>
                            <td>Création de compte, contact</td>
                        </tr>
                        <tr>
                            <td><strong>Données de profil</strong></td>
                            <td>Adresse, profession, date de naissance</td>
                            <td>Personnalisation du compte</td>
                        </tr>
                        <tr>
                            <td><strong>Données de don</strong></td>
                            <td>Montant, fréquence, mode de paiement</td>
                            <td>Traitement des dons, reçus fiscaux</td>
                        </tr>
                        <tr>
                            <td><strong>Données de navigation</strong></td>
                            <td>Adresse IP, pages visitées, durée</td>
                            <td>Amélioration du site, statistiques</td>
                        </tr>
                        <tr>
                            <td><strong>Données de demande d'aide</strong></td>
                            <td>Description de la situation, documents</td>
                            <td>Traitement des demandes</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Article 2 -->
                <h2 class="legal-section-title">2. Base légale du traitement</h2>
                <p class="legal-text">
                    Nous traitons vos données sur les bases légales suivantes :
                </p>
                <ul class="legal-list">
                    <li><strong>Votre consentement :</strong> pour l'envoi de newsletters et communications</li>
                    <li><strong>L'exécution d'un contrat :</strong> pour la gestion de votre adhésion</li>
                    <li><strong>Notre intérêt légitime :</strong> pour améliorer nos services</li>
                    <li><strong>Une obligation légale :</strong> pour la conservation des reçus fiscaux</li>
                </ul>

                <!-- Article 3 -->
                <h2 class="legal-section-title">3. Utilisation des données</h2>
                <p class="legal-text">
                    Vos données sont utilisées pour :
                </p>
                <ul class="legal-list">
                    <li>Créer et gérer votre compte membre</li>
                    <li>Traiter vos dons et générer des reçus fiscaux</li>
                    <li>Répondre à vos demandes de contact et d'aide</li>
                    <li>Vous envoyer notre newsletter (avec votre consentement)</li>
                    <li>Vous informer des événements et campagnes</li>
                    <li>Améliorer et personnaliser votre expérience sur le site</li>
                    <li>Respecter nos obligations légales</li>
                </ul>

                <!-- Article 4 -->
                <h2 class="legal-section-title">4. Destinataires des données</h2>
                <p class="legal-text">
                    Vos données sont destinées au personnel habilité du GSCC. Nous pouvons être amenés à les partager
                    avec :
                </p>
                <ul class="legal-list">
                    <li>Nos prestataires techniques (hébergement, email) qui agissent sous notre contrôle</li>
                    <li>Les autorités judiciaires, en cas d'obligation légale</li>
                    <li>Nos partenaires, uniquement avec votre consentement explicite</li>
                </ul>

                <!-- Article 5 -->
                <h2 class="legal-section-title">5. Durée de conservation</h2>
                <p class="legal-text">
                    Nous conservons vos données aussi longtemps que nécessaire pour remplir les finalités décrites :
                </p>
                <ul class="legal-list">
                    <li><strong>Compte membre :</strong> pendant toute la durée de votre adhésion, puis 3 ans après votre dernière activité</li>
                    <li><strong>Dons :</strong> 10 ans (obligation fiscale)</li>
                    <li><strong>Newsletter :</strong> jusqu'à votre désabonnement</li>
                    <li><strong>Demandes d'aide :</strong> 5 ans après le traitement du dossier</li>
                    <li><strong>Données de navigation :</strong> 13 mois maximum</li>
                </ul>

                <!-- Article 6 -->
                <h2 class="legal-section-title">6. Vos droits</h2>
                <p class="legal-text">
                    Conformément à la loi Informatique et Libertés, vous disposez des droits suivants :
                </p>
                <ul class="legal-list">
                    <li><strong>Droit d'accès :</strong> connaître les données que nous détenons</li>
                    <li><strong>Droit de rectification :</strong> modifier vos données si elles sont inexactes</li>
                    <li><strong>Droit à l'effacement :</strong> demander la suppression de vos données</li>
                    <li><strong>Droit à la limitation :</strong> restreindre le traitement de vos données</li>
                    <li><strong>Droit d'opposition :</strong> vous opposer au traitement</li>
                    <li><strong>Droit à la portabilité :</strong> récupérer vos données dans un format structuré</li>
                </ul>

                <!-- Article 7 -->
                <h2 class="legal-section-title">7. Sécurité</h2>
                <p class="legal-text">
                    Nous mettons en œuvre toutes les mesures techniques et organisationnelles appropriées pour garantir
                    un niveau de sécurité adapté aux risques. Cela inclut :
                </p>
                <ul class="legal-list">
                    <li>Le chiffrement des connexions (HTTPS)</li>
                    <li>Le stockage sécurisé des mots de passe (hachés)</li>
                    <li>Des accès restreints au personnel habilité</li>
                    <li>Des sauvegardes régulières</li>
                    <li>La surveillance des tentatives d'intrusion</li>
                </ul>

                <!-- Article 8 -->
                <h2 class="legal-section-title">8. Transferts hors d'Haïti</h2>
                <p class="legal-text">
                    Certains de nos prestataires (hébergement, email) peuvent être situés hors d'Haïti. Dans ce cas,
                    nous nous assurons qu'ils offrent un niveau de protection adéquat conformément aux réglementations
                    applicables.
                </p>

                <!-- Contact -->
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="contact-info">
                        <h3>Délégué à la protection des données</h3>
                        <p>Pour toute question relative à vos données ou pour exercer vos droits :</p>
                        <p><i class="fas fa-envelope"></i> <a href="mailto:dpo@gscc.org">dpo@gscc.org</a></p>
                        <p><i class="fas fa-phone"></i> <a href="tel:+50929474722">2947 47 22</a></p>
                        <p><i class="fas fa-map-marker-alt"></i> 123 Rue du Centre, Port-au-Prince, Haïti</p>
                    </div>
                </div>

                <!-- Réclamation -->
                <div class="legal-note" style="margin-top: 30px;">
                    <i class="fas fa-gavel"></i>
                    <strong>Droit de réclamation :</strong> Si vous estimez que vos droits ne sont pas respectés,
                    vous pouvez introduire une réclamation auprès de la Commission Nationale de l'Informatique et
                    des Libertés (CNIL) haïtienne.
                </div>

            </div>
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>
</body>

</html>