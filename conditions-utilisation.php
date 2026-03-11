<?php
// conditions-utilisation.php
require_once 'includes/config.php';

$page_title = 'Conditions d\'utilisation';
$page_description = 'Conditions générales d\'utilisation du site web du GSCC.';

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

        .legal-highlight {
            background: #FDF8E7;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border: 1px solid #F2DDB3;
        }

        .legal-highlight strong {
            color: #B85C1A;
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

        .btn-print i {
            font-size: 16px;
        }

        .toc {
            background: var(--warm-white);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 40px;
            border: 1px solid var(--grey-light);
        }

        .toc h3 {
            color: var(--rose);
            margin-bottom: 15px;
            font-size: 18px;
        }

        .toc ul {
            list-style: none;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
        }

        .toc li a {
            color: var(--grey);
            text-decoration: none;
            font-size: 14px;
            padding: 5px 0;
            display: inline-block;
            transition: color 0.2s;
        }

        .toc li a:hover {
            color: var(--rose);
        }

        .toc li a::before {
            content: '•';
            color: var(--rose);
            margin-right: 8px;
        }

        @media (max-width: 768px) {
            .legal-container {
                padding: 30px 20px;
            }

            .toc ul {
                grid-template-columns: 1fr;
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
                Conditions générales d'utilisation du site et des services
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
                    Les présentes conditions générales d'utilisation (ci-après "CGU") régissent l'accès et l'utilisation
                    du site internet du Groupe de Support Contre le Cancer (GSCC). En naviguant sur ce site, vous acceptez
                    sans réserve les présentes conditions. Si vous ne les acceptez pas, nous vous invitons à ne pas utiliser
                    notre site.
                </p>

                <!-- Table des matières -->
                <div class="toc">
                    <h3><i class="fas fa-list"></i> Sommaire</h3>
                    <ul>
                        <li><a href="#article1">Article 1 : Objet</a></li>
                        <li><a href="#article2">Article 2 : Accès au site</a></li>
                        <li><a href="#article3">Article 3 : Propriété intellectuelle</a></li>
                        <li><a href="#article4">Article 4 : Données personnelles</a></li>
                        <li><a href="#article5">Article 5 : Utilisation du forum</a></li>
                        <li><a href="#article6">Article 6 : Dons et contributions</a></li>
                        <li><a href="#article7">Article 7 : Responsabilité</a></li>
                        <li><a href="#article8">Article 8 : Liens hypertextes</a></li>
                        <li><a href="#article9">Article 9 : Modification des CGU</a></li>
                        <li><a href="#article10">Article 10 : Droit applicable</a></li>
                    </ul>
                </div>

                <!-- Article 1 -->
                <h2 id="article1" class="legal-section-title">Article 1 : Objet</h2>
                <p class="legal-text">
                    Le présent site web a pour objet de fournir des informations sur les activités du GSCC, de permettre
                    la collecte de dons, l'inscription des membres, la gestion des demandes d'aide, ainsi que la diffusion
                    d'informations relatives à la lutte contre le cancer en Haïti.
                </p>

                <!-- Article 2 -->
                <h2 id="article2" class="legal-section-title">Article 2 : Accès au site</h2>
                <p class="legal-text">
                    Le site est accessible gratuitement à tout utilisateur disposant d'un accès à internet. Tous les coûts
                    supportés par l'utilisateur pour accéder au site (matériel, logiciels, connexion internet) sont à sa
                    charge. Le GSCC met en œuvre tous les moyens raisonnables pour assurer un accès de qualité au site,
                    mais n'est tenu à aucune obligation de résultat.
                </p>

                <div class="legal-note">
                    <i class="fas fa-info-circle"></i>
                    Le GSCC se réserve le droit de suspendre l'accès au site pour des raisons de maintenance, de mise à jour
                    ou pour toute autre nécessité technique, sans que cela puisse ouvrir droit à indemnisation.
                </div>

                <!-- Article 3 -->
                <h2 id="article3" class="legal-section-title">Article 3 : Propriété intellectuelle</h2>
                <p class="legal-text">
                    L'ensemble du contenu du site (textes, images, vidéos, logos, etc.) est protégé par le droit d'auteur
                    et reste la propriété exclusive du GSCC ou de ses partenaires. Toute reproduction, représentation,
                    modification ou exploitation, totale ou partielle, sans autorisation préalable est interdite.
                </p>
                <ul class="legal-list">
                    <li>Les textes et articles peuvent être partagés à condition d'en citer la source</li>
                    <li>Les photos et vidéos ne peuvent être réutilisées sans autorisation expresse</li>
                    <li>Le logo GSCC ne peut être utilisé sans accord écrit</li>
                </ul>

                <!-- Article 4 -->
                <h2 id="article4" class="legal-section-title">Article 4 : Données personnelles</h2>
                <p class="legal-text">
                    Le GSCC s'engage à protéger la vie privée de ses utilisateurs. Les informations collectées via les
                    formulaires (inscription, don, contact) sont utilisées uniquement dans le cadre de nos missions et
                    ne sont jamais cédées à des tiers sans votre consentement explicite.
                </p>
                <p class="legal-text">
                    Conformément à la loi Informatique et Libertés, vous disposez d'un droit d'accès, de rectification
                    et de suppression des données vous concernant. Pour l'exercer, contactez-nous à :
                    <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>.
                </p>

                <!-- Article 5 -->
                <h2 id="article5" class="legal-section-title">Article 5 : Utilisation du forum</h2>
                <p class="legal-text">
                    Le forum est un espace d'échange réservé aux membres. En y participant, vous vous engagez à :
                </p>
                <ul class="legal-list">
                    <li>Respecter les autres membres et la charte de bonne conduite</li>
                    <li>Ne pas diffuser de propos injurieux, discriminatoires ou illicites</li>
                    <li>Ne pas faire de publicité ou de prospection commerciale</li>
                    <li>Ne pas partager d'informations médicales personnelles</li>
                </ul>
                <p class="legal-text">
                    Le GSCC se réserve le droit de modérer ou supprimer tout message ne respectant pas ces règles,
                    et de bannir un utilisateur en cas de manquement grave.
                </p>

                <!-- Article 6 -->
                <h2 id="article6" class="legal-section-title">Article 6 : Dons et contributions</h2>
                <div class="legal-highlight">
                    <p><strong>Important :</strong> Les dons effectués via le site sont définitifs et non remboursables,
                        sauf erreur manifeste de notre part. Un reçu fiscal est envoyé par email pour tout don supérieur à
                        500 gourdes, conformément à la législation haïtienne.</p>
                </div>
                <p class="legal-text">
                    Les transactions financières sont sécurisées via les plateformes PayPal et Stripe. Le GSCC n'a pas
                    accès aux informations bancaires des donateurs.
                </p>

                <!-- Article 7 -->
                <h2 id="article7" class="legal-section-title">Article 7 : Responsabilité</h2>
                <p class="legal-text">
                    Le GSCC s'efforce de fournir des informations exactes et à jour, mais ne peut garantir l'exhaustivité
                    ou l'absence d'erreurs. Les informations médicales présentes sur le site ne remplacent en aucun cas
                    une consultation professionnelle. En cas de problème de santé, consultez un médecin.
                </p>
                <p class="legal-text">
                    Le GSCC ne pourra être tenu responsable des dommages directs ou indirects résultant de l'utilisation
                    du site ou de l'impossibilité d'y accéder.
                </p>

                <!-- Article 8 -->
                <h2 id="article8" class="legal-section-title">Article 8 : Liens hypertextes</h2>
                <p class="legal-text">
                    Le site peut contenir des liens vers des sites tiers. Le GSCC n'exerce aucun contrôle sur ces sites
                    et décline toute responsabilité quant à leur contenu ou leurs pratiques. Ces liens ne constituent pas
                    une approbation de leur part.
                </p>

                <!-- Article 9 -->
                <h2 id="article9" class="legal-section-title">Article 9 : Modification des CGU</h2>
                <p class="legal-text">
                    Le GSCC se réserve le droit de modifier les présentes conditions à tout moment. Les nouvelles conditions
                    entrent en vigueur dès leur publication sur le site. Il est conseillé aux utilisateurs de consulter
                    régulièrement cette page.
                </p>

                <!-- Article 10 -->
                <h2 id="article10" class="legal-section-title">Article 10 : Droit applicable</h2>
                <p class="legal-text">
                    Les présentes conditions sont régies par le droit haïtien. Tout litige relatif à leur interprétation
                    ou à leur exécution relève de la compétence exclusive des tribunaux de Port-au-Prince.
                </p>

                <!-- Contact -->
                <div class="legal-note" style="margin-top: 50px;">
                    <i class="fas fa-envelope"></i>
                    <strong>Pour toute question relative aux conditions d'utilisation :</strong><br>
                    Email : <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a><br>
                    Téléphone : <a href="tel:+50929474722">2947 47 22</a>
                </div>

            </div>
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>
</body>

</html>