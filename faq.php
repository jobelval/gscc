<?php
// faq.php
require_once 'includes/config.php';

$page_title = 'Foire aux questions';
$page_description = 'Trouvez les réponses aux questions les plus fréquentes sur le GSCC.';

$date_maj = '15 janvier 2025';

// Catégories de FAQ
$faq_categories = [
    'general' => 'Questions générales',
    'membre' => 'Adhésion et compte membre',
    'don' => 'Dons et financement',
    'aide' => 'Demandes d\'aide',
    'evenements' => 'Événements et activités',
    'forum' => 'Forum et communauté'
];

// Questions fréquentes
$faqs = [
    'general' => [
        [
            'q' => 'Qu\'est-ce que le GSCC ?',
            'a' => 'Le Groupe de Support Contre le Cancer (GSCC) est une organisation haïtienne à but non lucratif créée en 2014. Notre mission est de soutenir les personnes atteintes de cancer et leurs familles, de sensibiliser le public à la prévention, et de collecter des fonds pour améliorer l\'accès aux soins.'
        ],
        [
            'q' => 'Où êtes-vous situés ?',
            'a' => 'Notre siège social est à Port-au-Prince, mais nous intervenons dans tout le pays à travers nos programmes et nos partenaires locaux.'
        ],
        [
            'q' => 'Comment puis-je contacter le GSCC ?',
            'a' => 'Vous pouvez nous contacter par téléphone au 2947 47 22, par email à gscc@gscchaiti.com, ou via notre formulaire de contact en ligne.'
        ],
        [
            'q' => 'Quels sont vos horaires d\'ouverture ?',
            'a' => 'Notre bureau est ouvert du lundi au vendredi de 9h à 18h, et le samedi de 9h à 14h. Nous restons joignables par email en dehors de ces horaires.'
        ]
    ],
    'membre' => [
        [
            'q' => 'Comment devenir membre ?',
            'a' => 'Pour devenir membre, il vous suffit de remplir le formulaire d\'inscription en ligne. L\'adhésion est gratuite et vous donne accès au forum, aux événements exclusifs, et à la newsletter.'
        ],
        [
            'q' => 'Quels sont les avantages d\'être membre ?',
            'a' => 'En tant que membre, vous pouvez participer au forum, vous inscrire à nos événements en priorité, recevoir notre newsletter mensuelle, et voter lors de l\'assemblée générale annuelle.'
        ],
        [
            'q' => 'Comment me connecter à mon compte ?',
            'a' => 'Utilisez le bouton "Connexion" en haut du site. Si vous avez oublié votre mot de passe, cliquez sur "Mot de passe oublié" pour le réinitialiser.'
        ],
        [
            'q' => 'Comment modifier mes informations personnelles ?',
            'a' => 'Une fois connecté, rendez-vous dans "Mon compte" où vous pourrez modifier vos informations (nom, email, téléphone, etc.) à tout moment.'
        ]
    ],
    'don' => [
        [
            'q' => 'Comment faire un don ?',
            'a' => 'Vous pouvez faire un don en ligne via notre page "Faire un don", par virement bancaire, ou en espèces à notre bureau. Tous les dons sont sécurisés.'
        ],
        [
            'q' => 'Les dons sont-ils déductibles fiscalement ?',
            'a' => 'Oui, en Haïti, les dons aux organisations reconnues d\'utilité publique sont déductibles des impôts. Un reçu fiscal vous sera envoyé par email pour tout don supérieur à 500 gourdes.'
        ],
        [
            'q' => 'Puis-je faire un don mensuel ?',
            'a' => 'Oui, vous pouvez opter pour un don mensuel lors du paiement. Vous serez prélevé automatiquement chaque mois (par carte bancaire ou PayPal).'
        ],
        [
            'q' => 'Comment savoir où va mon argent ?',
            'a' => 'Nous publions chaque année un rapport financier détaillé. 85% des dons sont directement affectés à nos programmes d\'aide, 15% aux frais de fonctionnement.'
        ]
    ],
    'aide' => [
        [
            'q' => 'Comment demander de l\'aide ?',
            'a' => 'Rendez-vous sur la page "Demande d\'aide" et remplissez le formulaire. Vous devrez être connecté à votre compte et fournir les documents justificatifs nécessaires.'
        ],
        [
            'q' => 'Quels types d\'aide proposez-vous ?',
            'a' => 'Nous proposons une aide financière (médicaments, traitements), une aide médicale (consultations spécialisées), un soutien psychologique, et un accompagnement administratif.'
        ],
        [
            'q' => 'Quels sont les critères pour obtenir une aide ?',
            'a' => 'Les critères varient selon les programmes. En général, nous privilégions les personnes à faibles revenus, sans couverture médicale, et avec un diagnostic médical confirmé.'
        ],
        [
            'q' => 'Combien de temps faut-il pour obtenir une réponse ?',
            'a' => 'Notre équipe traite les demandes sous 48h ouvrées. En cas d\'urgence, contactez directement notre permanence téléphonique.'
        ]
    ],
    'evenements' => [
        [
            'q' => 'Comment participer à vos événements ?',
            'a' => 'Vous pouvez vous inscrire directement sur la page de chaque événement. Certains événements sont gratuits, d\'autres nécessitent une inscription payante.'
        ],
        [
            'q' => 'Où puis-je trouver le calendrier des événements ?',
            'a' => 'Tous nos événements sont listés sur la page "Événements" avec leurs dates, lieux et modalités d\'inscription.'
        ],
        [
            'q' => 'Puis-je proposer une activité ou un événement ?',
            'a' => 'Oui, si vous avez une idée d\'événement en lien avec notre mission, contactez-nous par email pour en discuter.'
        ]
    ],
    'forum' => [
        [
            'q' => 'Comment accéder au forum ?',
            'a' => 'Le forum est accessible à tous les membres connectés. Une fois votre compte créé et validé, vous pourrez participer aux discussions.'
        ],
        [
            'q' => 'Quelles sont les règles du forum ?',
            'a' => 'Nous demandons à tous les membres de respecter la charte : pas de propos injurieux, pas de publicité, respect de la confidentialité, et bienveillance envers les autres membres.'
        ],
        [
            'q' => 'Comment signaler un abus ?',
            'a' => 'Vous pouvez signaler tout message inapproprié à un modérateur en utilisant le bouton "Signaler" présent sur chaque message, ou par email.'
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> - <?= SITE_NAME ?></title>
    <meta name="description" content="<?= e($page_description) ?>">

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display- swap" rel="stylesheet">
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

        .faq-section {
            padding: 60px 0;
            background: var(--warm-white);
        }

        .faq-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .faq-meta {
            text-align: center;
            color: var(--grey);
            margin-bottom: 40px;
            font-size: 15px;
        }

        .faq-meta i {
            color: var(--rose);
            margin-right: 5px;
        }

        .faq-search {
            background: white;
            border-radius: 60px;
            padding: 5px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            display: flex;
            margin-bottom: 50px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .faq-search input {
            flex: 1;
            border: none;
            padding: 15px 25px;
            border-radius: 60px;
            outline: none;
            font-size: 16px;
            background: transparent;
        }

        .faq-search button {
            background: var(--rose);
            color: white;
            border: none;
            padding: 0 30px;
            border-radius: 60px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }

        .faq-search button:hover {
            background: #C0306A;
        }

        .faq-categories {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            margin-bottom: 50px;
        }

        .faq-category-btn {
            padding: 10px 22px;
            border-radius: 30px;
            background: white;
            color: var(--charcoal);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid var(--grey-light);
            transition: all 0.3s;
        }

        .faq-category-btn:hover,
        .faq-category-btn.active {
            background: var(--rose);
            color: white;
            border-color: var(--rose);
        }

        .faq-category-btn i {
            margin-right: 5px;
        }

        .faq-category {
            margin-bottom: 50px;
            scroll-margin-top: 150px;
        }

        .faq-category-title {
            color: var(--charcoal);
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
        }

        .faq-category-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--rose);
        }

        .faq-item {
            background: white;
            border-radius: 15px;
            margin-bottom: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--grey-light);
            overflow: hidden;
        }

        .faq-question {
            padding: 20px 25px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s;
        }

        .faq-question:hover {
            background: var(--rose-pale);
        }

        .faq-question h3 {
            font-size: 18px;
            color: var(--charcoal);
            font-weight: 600;
            margin: 0;
            padding-right: 20px;
        }

        .faq-question i {
            color: var(--rose);
            font-size: 16px;
            transition: transform 0.3s;
        }

        .faq-question.open i {
            transform: rotate(180deg);
        }

        .faq-answer {
            padding: 0 25px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
            background: var(--warm-white);
        }

        .faq-answer.open {
            padding: 20px 25px;
            max-height: 500px;
            border-top: 1px solid var(--grey-light);
        }

        .faq-answer p {
            color: var(--grey);
            line-height: 1.8;
            margin: 0;
        }

        .faq-answer ul {
            margin: 15px 0 0 20px;
            color: var(--grey);
        }

        .faq-answer li {
            margin-bottom: 8px;
        }

        .faq-not-found {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            margin-top: 30px;
        }

        .faq-not-found i {
            font-size: 50px;
            color: var(--grey-light);
            margin-bottom: 20px;
        }

        .faq-not-found h3 {
            color: var(--charcoal);
            margin-bottom: 10px;
        }

        .faq-not-found p {
            color: var(--grey);
            margin-bottom: 25px;
        }

        .faq-contact {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            margin-top: 60px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--grey-light);
        }

        .faq-contact h3 {
            color: var(--charcoal);
            margin-bottom: 15px;
            font-family: 'Playfair Display', serif;
        }

        .faq-contact p {
            color: var(--grey);
            margin-bottom: 25px;
        }

        .btn-contact {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--rose);
            color: white;
            padding: 14px 35px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-contact:hover {
            background: #C0306A;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(217, 79, 122, 0.3);
        }

        @media (max-width: 768px) {
            .faq-search {
                flex-direction: column;
                border-radius: 20px;
            }

            .faq-search input {
                width: 100%;
                padding: 15px 20px;
            }

            .faq-search button {
                width: 100%;
                padding: 15px;
                border-radius: 20px;
            }

            .faq-category-title {
                font-size: 24px;
            }

            .faq-question h3 {
                font-size: 16px;
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
                Trouvez rapidement les réponses à vos questions
            </p>
        </div>
    </div>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div class="faq-container">

                <!-- Meta informations -->
                <div class="faq-meta" data-aos="fade-up">
                    <i class="far fa-calendar-alt"></i> Dernière mise à jour : <?= $date_maj ?>
                </div>

                <!-- Barre de recherche FAQ -->
                <div class="faq-search" data-aos="fade-up">
                    <input type="text" id="faqSearch" placeholder="Rechercher dans la FAQ...">
                    <button onclick="searchFAQ()"><i class="fas fa-search"></i> Rechercher</button>
                </div>

                <!-- Catégories -->
                <div class="faq-categories" data-aos="fade-up">
                    <a href="#general" class="faq-category-btn active" onclick="showCategory('general'); return false;">
                        <i class="fas fa-question-circle"></i> Général
                    </a>
                    <a href="#membre" class="faq-category-btn" onclick="showCategory('membre'); return false;">
                        <i class="fas fa-user"></i> Adhésion
                    </a>
                    <a href="#don" class="faq-category-btn" onclick="showCategory('don'); return false;">
                        <i class="fas fa-heart"></i> Dons
                    </a>
                    <a href="#aide" class="faq-category-btn" onclick="showCategory('aide'); return false;">
                        <i class="fas fa-hand-holding-heart"></i> Demande d'aide
                    </a>
                    <a href="#evenements" class="faq-category-btn" onclick="showCategory('evenements'); return false;">
                        <i class="fas fa-calendar"></i> Événements
                    </a>
                    <a href="#forum" class="faq-category-btn" onclick="showCategory('forum'); return false;">
                        <i class="fas fa-comments"></i> Forum
                    </a>
                </div>

                <!-- Contenu des FAQ -->
                <div id="faqContent">
                    <?php foreach ($faq_categories as $cat_key => $cat_name): ?>
                        <div id="<?= $cat_key ?>" class="faq-category" data-category="<?= $cat_key ?>">
                            <h2 class="faq-category-title"><?= $cat_name ?></h2>

                            <?php foreach ($faqs[$cat_key] as $faq): ?>
                                <div class="faq-item">
                                    <div class="faq-question" onclick="toggleFAQ(this)">
                                        <h3><?= e($faq['q']) ?></h3>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <div class="faq-answer">
                                        <p><?= nl2br(e($faq['a'])) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Message si aucun résultat -->
                <div id="noResults" class="faq-not-found" style="display: none;">
                    <i class="fas fa-search"></i>
                    <h3>Aucun résultat trouvé</h3>
                    <p>Désolé, aucune question ne correspond à votre recherche.<br>Essayez avec d'autres mots-clés.</p>
                    <a href="#" onclick="resetSearch(); return false;" class="btn-contact" style="background: var(--grey);">
                        <i class="fas fa-redo"></i> Réinitialiser
                    </a>
                </div>

                <!-- Contact -->
                <div class="faq-contact" data-aos="fade-up">
                    <h3>Vous n'avez pas trouvé votre réponse ?</h3>
                    <p>Notre équipe est à votre disposition pour répondre à toutes vos questions.</p>
                    <a href="contact.php" class="btn-contact">
                        <i class="fas fa-envelope"></i> Nous contacter
                    </a>
                </div>

            </div>
        </div>
    </section>

    <script>
        // Fonction pour ouvrir/fermer une réponse
        function toggleFAQ(element) {
            const question = element;
            const answer = element.nextElementSibling;
            const icon = question.querySelector('i');

            question.classList.toggle('open');
            answer.classList.toggle('open');

            if (answer.classList.contains('open')) {
                icon.style.transform = 'rotate(180deg)';
            } else {
                icon.style.transform = 'rotate(0deg)';
            }
        }

        // Fonction pour afficher une catégorie
        function showCategory(category) {
            // Mettre à jour les boutons actifs
            document.querySelectorAll('.faq-category-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.closest('.faq-category-btn').classList.add('active');

            // Afficher la catégorie sélectionnée et masquer les autres
            document.querySelectorAll('.faq-category').forEach(cat => {
                if (cat.id === category) {
                    cat.style.display = 'block';
                } else {
                    cat.style.display = 'none';
                }
            });

            // Réinitialiser la recherche
            document.getElementById('faqSearch').value = '';
            document.getElementById('noResults').style.display = 'none';
        }

        // Fonction de recherche dans la FAQ
        function searchFAQ() {
            const searchTerm = document.getElementById('faqSearch').value.toLowerCase().trim();
            let hasResults = false;

            if (searchTerm === '') {
                resetSearch();
                return;
            }

            // Afficher toutes les catégories
            document.querySelectorAll('.faq-category').forEach(cat => {
                cat.style.display = 'block';
            });

            // Mettre à jour les boutons
            document.querySelectorAll('.faq-category-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Parcourir toutes les questions
            document.querySelectorAll('.faq-item').forEach(item => {
                const question = item.querySelector('.faq-question h3').innerText.toLowerCase();
                const answer = item.querySelector('.faq-answer p').innerText.toLowerCase();

                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = 'block';
                    hasResults = true;

                    // Ouvrir automatiquement la réponse
                    const questionEl = item.querySelector('.faq-question');
                    const answerEl = item.querySelector('.faq-answer');
                    if (!answerEl.classList.contains('open')) {
                        questionEl.classList.add('open');
                        answerEl.classList.add('open');
                        questionEl.querySelector('i').style.transform = 'rotate(180deg)';
                    }
                } else {
                    item.style.display = 'none';
                }
            });

            // Afficher le message si aucun résultat
            document.getElementById('noResults').style.display = hasResults ? 'none' : 'block';
        }

        // Fonction pour réinitialiser la recherche
        function resetSearch() {
            document.getElementById('faqSearch').value = '';
            document.getElementById('noResults').style.display = 'none';

            // Afficher toutes les catégories et tous les items
            document.querySelectorAll('.faq-category').forEach(cat => {
                cat.style.display = 'block';
            });

            document.querySelectorAll('.faq-item').forEach(item => {
                item.style.display = 'block';
            });

            // Fermer toutes les réponses
            document.querySelectorAll('.faq-question').forEach(q => {
                q.classList.remove('open');
            });
            document.querySelectorAll('.faq-answer').forEach(a => {
                a.classList.remove('open');
            });
            document.querySelectorAll('.faq-question i').forEach(i => {
                i.style.transform = 'rotate(0deg)';
            });

            // Réactiver le bouton Général
            document.querySelectorAll('.faq-category-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector('.faq-category-btn').classList.add('active');
        }

        // Initialisation : afficher la première catégorie
        document.addEventListener('DOMContentLoaded', function() {
            showCategory('general');
        });
    </script>

    <?php include 'templates/footer.php'; ?>
</body>

</html>