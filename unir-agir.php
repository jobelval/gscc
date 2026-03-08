<?php
// unir-agir.php
require_once 'includes/config.php';

$page_title = 'Unir et agir';
$page_description = 'Ensemble, mobilisons-nous pour lutter contre le cancer. Découvrez comment vous pouvez nous rejoindre.';
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
        
        .unir-section {
            padding: 60px 0;
            background: #F3F4F6;
        }
        
        .mission-statement {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 800px;
            margin: 0 auto 60px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
            border: 1px solid #D1D5DB;
        }
        
        .mission-statement h2 {
            color: #003399;
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 20px;
        }
        
        .mission-statement p {
            color: #1F2937;
            line-height: 1.8;
            font-size: 17px;
            font-weight: 400;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 60px;
        }
        
        .action-card {
            background: white;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid #D1D5DB;
            transition: all 0.3s ease;
        }
        
        .action-card:hover {
            transform: translateY(-8px);
            border-color: #003399;
            box-shadow: 0 12px 32px rgba(0,51,153,0.12);
        }
        
        .action-icon {
            width: 80px;
            height: 80px;
            background: #EBF0FF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: #003399;
            font-size: 32px;
        }
        
        .action-card h3 {
            color: #0D1117;
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 19px;
        }
        
        .action-card p {
            color: #4B5563;
            font-weight: 400;
            line-height: 1.7;
            margin-bottom: 20px;
        }
        
        .btn-action {
            display: inline-block;
            background: #003399;
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-action:hover {
            background: #002277;
            transform: translateY(-2px);
        }
        
        .partnership-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 60px;
        }
        
        .partnership-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
        }
        
        .partnership-text h3 {
            color: #003399;
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 20px;
        }
        
        .partnership-text p {
            color: #1F2937;
            font-weight: 400;
            line-height: 1.8;
            margin-bottom: 20px;
        }
        
        .partnership-text ul {
            list-style: none;
            margin-bottom: 30px;
        }
        
        .partnership-text li {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #1F2937;
            font-weight: 500;
        }
        
        .partnership-text li i {
            color: #4CAF50;
        }
        
        .partnership-image {
            border-radius: 15px;
            overflow: hidden;
        }
        
        .partnership-image img {
            width: 100%;
            height: auto;
        }
        
        .testimonials-slider {
            margin: 60px 0;
        }
        
        .testimonial-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid #D1D5DB;
            margin: 0;
        }
        
        .testimonial-text {
            font-style: italic;
            color: #1F2937;
            font-weight: 400;
            line-height: 1.8;
            margin-bottom: 20px;
            position: relative;
            padding-left: 30px;
        }
        
        .testimonial-text::before {
            content: '"';
            position: absolute;
            left: 0;
            top: -10px;
            font-size: 40px;
            color: #003399;
            opacity: 0.3;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .testimonial-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #003399;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        
        .testimonial-info h4 {
            color: #0D1117;
            font-weight: 700;
            margin-bottom: 3px;
        }
        
        .testimonial-info p {
            color: #4B5563;
            font-size: 13px;
            font-weight: 500;
        }
        
        .join-section {
            background: linear-gradient(135deg, #003399 0%, #2E7D32 100%);
            color: white;
            border-radius: 20px;
            padding: 60px;
            text-align: center;
        }
        
        .join-section h2 {
            font-size: 2rem;
            font-weight: 800;
            color: #FFFFFF;
            margin-bottom: 20px;
            text-shadow: 0 1px 3px rgba(0,0,0,.3);
        }
        
        .join-section p {
            font-size: 17px;
            font-weight: 500;
            color: #FFFFFF;
            opacity: 1;
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.8;
        }
        
        .join-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
        }
        
        .btn-join {
            background: white;
            color: #003399;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-join:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .btn-join.outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-join.outline:hover {
            background: white;
            color: #003399;
        }
        
        @media (max-width: 768px) {
            .actions-grid {
                grid-template-columns: 1fr;
            }
            
            .partnership-content {
                grid-template-columns: 1fr;
            }
            
            .join-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 data-aos="fade-up">Unir et agir</h1>
            <p data-aos="fade-up" data-aos-delay="100">
                Ensemble, nous sommes plus forts dans la lutte contre le cancer
            </p>
        </div>
    </div>

    <!-- Unir Section -->
    <section class="unir-section">
        <div class="container">
            <!-- Mission Statement -->
            <div class="mission-statement" data-aos="fade-up">
                <h2>Notre conviction</h2>
                <p>
                    "Seul on va plus vite, ensemble on va plus loin." C'est dans cet esprit 
                    que le GSCC rassemble patients, familles, professionnels de santé et 
                    partenaires pour créer une force collective contre le cancer.
                </p>
            </div>
            
            <!-- Actions possibles -->
            <div class="actions-grid">
                <div class="action-card" data-aos="fade-up">
                    <div class="action-icon">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <h3>Devenez bénévole</h3>
                    <p>
                        Donnez de votre temps et de votre énergie pour soutenir nos actions 
                        sur le terrain. Accompagnement des patients, organisation d'événements, 
                        sensibilisation...
                    </p>
                    <a href="devenir-benevole.php" class="btn-action">Je m'engage</a>
                </div>
                
                <div class="action-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="action-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Devenez membre</h3>
                    <p>
                        Rejoignez notre communauté de membres actifs et participez à la vie 
                        de l'association. Votez aux assemblées, proposez des actions, 
                        faites entendre votre voix.
                    </p>
                    <a href="devenir-membre.php" class="btn-action">Je rejoins</a>
                </div>
                
                <div class="action-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="action-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3>Devenez partenaire</h3>
                    <p>
                        Entreprises, institutions, associations : soutenez nos actions 
                        et développez votre responsabilité sociétale en vous engageant 
                        à nos côtés.
                    </p>
                    <a href="partenaires.php" class="btn-action">Devenir partenaire</a>
                </div>
            </div>
            
            <!-- Section Partenariat -->
            <div class="partnership-section" data-aos="fade-up">
                <div class="partnership-content">
                    <div class="partnership-text">
                        <h3>Pourquoi devenir partenaire ?</h3>
                        <p>
                            En tant que partenaire du GSCC, vous participez activement à la 
                            lutte contre le cancer en Haïti tout en bénéficiant d'une visibilité 
                            et d'une reconnaissance auprès de notre communauté.
                        </p>
                        <ul>
                            <li><i class="fas fa-check-circle"></i> Visibilité de votre logo sur nos supports</li>
                            <li><i class="fas fa-check-circle"></i> Association à une cause noble</li>
                            <li><i class="fas fa-check-circle"></i> Déduction fiscale des dons</li>
                            <li><i class="fas fa-check-circle"></i> Participation à nos événements</li>
                            <li><i class="fas fa-check-circle"></i> Communication sur vos engagements RSE</li>
                        </ul>
                        <a href="contact.php?objet=partenariat" class="btn-action">Nous contacter</a>
                    </div>
                    <div class="partnership-image">
                        <img src="uploads/galerie/IMG_4049.jpg" alt="Partenaires">
                    </div>
                </div>
            </div>
            
            <!-- Témoignages -->
            <h2 style="text-align:center;color:#003399;font-size:1.6rem;font-weight:800;margin-bottom:40px;" data-aos="fade-up">
                Ils se sont engagés avec nous
            </h2>
            
            <div class="testimonials-slider" data-aos="fade-up">
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px;">
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            Être bénévole au GSCC m'a permis de donner un sens à mon temps libre. 
                            Accompagner les patients et voir leur sourire, c'est une richesse inestimable.
                        </div>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="testimonial-info">
                                <h4>Marie-Claude B.</h4>
                                <p>Bénévole depuis 2022</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            Notre entreprise est fière de soutenir le GSCC depuis 3 ans. 
                            C'est un partenariat riche de sens qui mobilise nos équipes.
                        </div>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="testimonial-info">
                                <h4>Jean-Marc D.</h4>
                                <p>Directeur, Groupe Sogebank</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            En tant que membre, je participe aux décisions et je vois concrètement 
                            l'impact de nos actions. C'est une expérience très enrichissante.
                        </div>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="testimonial-info">
                                <h4>Pierre R.</h4>
                                <p>Membre actif</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Call to Action -->
            <div class="join-section" data-aos="zoom-in">
                <h2>Rejoignez le mouvement</h2>
                <p>
                    Ensemble, construisons un avenir sans cancer. Que vous soyez particulier, 
                    entreprise ou association, votre place est parmi nous.
                </p>
                <div class="join-buttons">
                    <a href="devenir-membre.php" class="btn-join">Devenir membre</a>
                    <a href="contact.php" class="btn-join outline">Nous contacter</a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>
</body>
</html>