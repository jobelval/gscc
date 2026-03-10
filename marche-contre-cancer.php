<?php
// marche-contre-cancer.php
require_once 'includes/config.php';

$page_title = 'Marche Contre le Cancer';
$page_description = 'Participez à la grande marche solidaire organisée chaque année par le GSCC.';

$annee = date('Y');
$prochaine_marche = mktime(9, 0, 0, 10, 20, $annee); // 20 octobre
$jours_restants = ceil(($prochaine_marche - time()) / (60 * 60 * 24));
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
        .marche-header {
            background: linear-gradient(135deg, #003399 0%, #1a56cc 60%, #1a7abf 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .marche-header h1 {
            font-size: 4rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .marche-header p {
            font-size: 1.5rem;
            opacity: 1;
            color: #ffffff;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .info-section {
            padding: 60px 0;
            background: #f8f9fa;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }
        
        .info-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .info-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .info-icon {
            width: 70px;
            height: 70px;
            background: rgba(26,86,204,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: #1a56cc;
            font-size: 28px;
        }
        
        .info-card h3 {
            color: #111;
            margin-bottom: 10px;
        }
        
        .info-card p {
            color: #444;
        }
        
        .parcours-section {
            padding: 60px 0;
        }
        
        .parcours-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }
        
        .parcours-map {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .parcours-map iframe {
            width: 100%;
            height: 400px;
            border: none;
        }
        
        .parcours-details h2 {
            color: #003399;
            font-size: 32px;
            margin-bottom: 20px;
        }
        
        .parcours-details p {
            color: #444;
            line-height: 1.8;
            margin-bottom: 20px;
        }
        
        .parcours-points {
            list-style: none;
            padding: 0;
        }
        
        .parcours-points li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            color: #333;
        }
        
        .parcours-points i {
            color: #4CAF50;
            font-size: 20px;
        }
        
        .inscription-marche {
            background: linear-gradient(135deg, #003399 0%, #1a56cc 60%, #1a7abf 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .inscription-marche h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }
        
        .inscription-marche p {
            font-size: 18px;
            opacity: 1;
            color: #ffffff;
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .btn-marche {
            background: white;
            color: #1a56cc;
            padding: 15px 50px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 18px;
            transition: all 0.3s ease;
            display: inline-block;
            margin: 0 10px;
        }
        
        .btn-marche:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .btn-marche.outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-marche.outline:hover {
            background: white;
            color: #003399;
        }
        
        .partenaires-marche {
            padding: 60px 0;
            background: #f8f9fa;
        }
        
        .partenaires-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 50px;
            flex-wrap: wrap;
        }
        
        .partenaire-logo {
            width: 150px;
            height: 80px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #555;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .temoignages-marche {
            padding: 60px 0;
        }
        
        .temoignage-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin: 20px;
            position: relative;
        }
        
        .temoignage-card::before {
            content: '"';
            position: absolute;
            top: -20px;
            left: 30px;
            font-size: 80px;
            color: #1a56cc;
            opacity: 0.3;
        }
        
        .faq-section {
            padding: 60px 0;
            background: #f8f9fa;
        }
        
        .faq-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }
        
        .faq-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
        }
        
        .faq-question {
            font-weight: 600;
            color: #003399;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .faq-question i {
            color: #1a56cc;
        }
        
        .faq-answer {
            color: #444;
            padding-left: 30px;
        }
        
        @media (max-width: 768px) {
            .marche-header h1 {
                font-size: 2.5rem;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .parcours-container {
                grid-template-columns: 1fr;
            }
            
            .faq-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <!-- Marche Header -->
    <div class="marche-header">
        <div class="container">
            <h1 data-aos="fade-up">Marche Contre le Cancer</h1>
            <p data-aos="fade-up" data-aos-delay="100">
                Ensemble, faisons un pas de plus vers la guérison
            </p>
        </div>
    </div>
        </div>
    </div>

    <!-- Info Cards -->
    <section class="info-section">
        <div class="container">
            <div class="info-grid">
                <div class="info-card" data-aos="fade-up">
                    <div class="info-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Date et heure</h3>
                    <p><strong>20 octobre <?= $annee ?></strong><br>Rendez-vous à 8h<br>Départ à 9h</p>
                </div>
                
                <div class="info-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Lieu de départ</h3>
                    <p><strong>Parc de Martissant</strong><br>Port-au-Prince<br>Arrivée : Champs de Mars</p>
                </div>
                
                <div class="info-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="info-icon">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <h3>Participation</h3>
                    <p><strong>Entièrement gratuite</strong><br>Ouverte à tous, sans exception<br>Venez comme vous êtes !</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Parcours Section -->
    <section class="parcours-section">
        <div class="container">
            <div class="parcours-container">
                <div class="parcours-map" data-aos="fade-right">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d123456!2d-72.338!3d18.594!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTjCsDM1JzM4LjQiTiA3MsKwMjAnMTYuOCJX!5e0!3m2!1sfr!2sht!4v1234567890" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
                
                <div class="parcours-details" data-aos="fade-left">
                    <h2>Le parcours</h2>
                    <p>
                        Une marche de 5 km à travers les plus beaux quartiers de Port-au-Prince, 
                        ouverte à tous, familles, amis, collègues. Un moment de convivialité 
                        et de solidarité pour soutenir notre cause.
                    </p>
                    
                    <ul class="parcours-points">
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <strong>Départ :</strong> Parc de Martissant - 8h
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <strong>Étape 1 :</strong> Avenue Jean-Paul II - Ravitaillement
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <strong>Étape 2 :</strong> Place Boyer - Animation musicale
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <strong>Arrivée :</strong> Champs de Mars - Village solidaire
                        </li>
                    </ul>
                    
                    <p>
                        <i class="fas fa-wheelchair" style="color: #003399; margin-right: 10px;"></i>
                        Parcours accessible aux personnes à mobilité réduite
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Inscription Section -->
    <section class="inscription-marche">
        <div class="container">
            <h2 data-aos="fade-up">Rejoignez-nous pour cette belle journée solidaire</h2>
            <p data-aos="fade-up" data-aos-delay="100">
                <?= $jours_restants ?> jours avant la grande marche. La participation est <strong>100% gratuite</strong> — venez nombreux avec famille et amis !
            </p>
            
            <div data-aos="fade-up" data-aos-delay="200">
                <a href="contact.php" class="btn-marche">
                    <i class="fas fa-walking"></i>
                    Je participe
                </a>
            </div>
        </div>
    </section>

    <!-- Partenaires -->
    <section class="partenaires-marche">
        <div class="container">
            <h2 style="text-align: center; color: #003399; margin-bottom: 40px;" data-aos="fade-up">
                Nos partenaires
            </h2>
            
            <div class="partenaires-logos" data-aos="fade-up">
                <div class="partenaire-logo"><i class="fas fa-building"></i> Sogebank</div>
                <div class="partenaire-logo"><i class="fas fa-building"></i> Digicel</div>
                <div class="partenaire-logo"><i class="fas fa-building"></i> BNC</div>
                <div class="partenaire-logo"><i class="fas fa-building"></i> CARAIBES</div>
            </div>
        </div>
    </section>

    <!-- Témoignages -->
    <section class="temoignages-marche">
        <div class="container">
            <h2 style="text-align: center; color: #003399; margin-bottom: 40px;" data-aos="fade-up">
                Ils ont marché avec nous
            </h2>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px;">
                <div class="temoignage-card" data-aos="fade-up">
                    <p style="font-style: italic; color: #333; line-height: 1.8;">
                        "Une expérience incroyable ! Voir autant de personnes réunies pour une même cause, 
                        ça donne de l'espoir. Je reviens chaque année avec toute ma famille."
                    </p>
                    <div style="margin-top: 20px;">
                        <strong style="color: #003399;">Marie C.</strong>
                        <p style="color: #555; font-size: 12px;">Participante depuis 2019</p>
                    </div>
                </div>
                
                <div class="temoignage-card" data-aos="fade-up" data-aos-delay="100">
                    <p style="font-style: italic; color: #333; line-height: 1.8;">
                        "J'ai marché pour ma sœur qui se bat contre le cancer. C'était tellement émouvant 
                        de voir tout ce soutien. Merci au GSCC pour cette belle initiative."
                    </p>
                    <div style="margin-top: 20px;">
                        <strong style="color: #003399;">Jean-Paul D.</strong>
                        <p style="color: #555; font-size: 12px;">Première participation</p>
                    </div>
                </div>
                
                <div class="temoignage-card" data-aos="fade-up" data-aos-delay="200">
                    <p style="font-style: italic; color: #333; line-height: 1.8;">
                        "Avec mon entreprise, nous avons formé une équipe de 25 personnes. 
                        C'est devenu un rendez-vous annuel de team building solidaire."
                    </p>
                    <div style="margin-top: 20px;">
                        <strong style="color: #003399;">Sophie L.</strong>
                        <p style="color: #555; font-size: 12px;">Capitaine d'équipe</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="faq-section">
        <div class="container">
            <h2 style="text-align: center; color: #003399; margin-bottom: 40px;" data-aos="fade-up">
                Questions fréquentes
            </h2>
            
            <div class="faq-grid">
                <div class="faq-item" data-aos="fade-up">
                    <div class="faq-question">
                        <i class="fas fa-question-circle"></i>
                        Qui peut participer ?
                    </div>
                    <div class="faq-answer">
                        Tout le monde ! La marche est ouverte à tous, quel que soit l'âge ou la condition physique. 
                        Les enfants sont les bienvenus (accompagnés).
                    </div>
                </div>
                
                <div class="faq-item" data-aos="fade-up" data-aos-delay="50">
                    <div class="faq-question">
                        <i class="fas fa-question-circle"></i>
                        Que contient le kit du marcheur ?
                    </div>
                    <div class="faq-answer">
                        Un T-shirt de la marche, une bouteille d'eau, un en-cas, un bracelet solidaire 
                        et un programme de la journée — distribués sur place, gratuitement.
                    </div>
                </div>
                
                <div class="faq-item" data-aos="fade-up" data-aos-delay="100">
                    <div class="faq-question">
                        <i class="fas fa-question-circle"></i>
                        Faut-il s'inscrire à l'avance ?
                    </div>
                    <div class="faq-answer">
                        Ce n'est pas obligatoire, mais nous vous encourageons à informer le GSCC 
                        de votre venue pour une meilleure organisation. Le jour même, arrivez tôt.
                    </div>
                </div>
                
                <div class="faq-item" data-aos="fade-up" data-aos-delay="150">
                    <div class="faq-question">
                        <i class="fas fa-question-circle"></i>
                        Puis-je venir en famille ?
                    </div>
                    <div class="faq-answer">
                        Absolument ! La marche est pensée pour être un moment familial et convivial. 
                        Enfants, parents, grands-parents — tout le monde est le bienvenu.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>
</body>
</html>