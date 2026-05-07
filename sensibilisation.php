<?php
// sensibilisation.php
require_once 'includes/config.php';

$page_title = 'Sensibilisation';
$page_description = 'Découvrez nos actions de sensibilisation pour informer et prévenir contre le cancer.';

// Récupérer les campagnes de sensibilisation
try {
    $stmt = $pdo->prepare("SELECT * FROM campagnes_projets 
                           WHERE type = 'campagne' AND statut IN ('en_cours', 'termine')
                           AND (titre LIKE '%sensibilisation%' OR description LIKE '%sensibilisation%')
                           ORDER BY date_debut DESC");
    $stmt->execute();
    $campagnes_sensibilisation = $stmt->fetchAll();
} catch (PDOException $e) {
    logError("Erreur sensibilisation: " . $e->getMessage());
    $campagnes_sensibilisation = [];
}
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
            --blue: #003399;
            --blue-dark: #002277;
            --text: #0D1117;
            --text-2: #1F2937;
            --muted: #4B5563;
            --border: #D1D5DB;
            --bg: #F3F4F6;
            --white: #FFFFFF;
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
            text-shadow: 0 1px 3px rgba(0, 0, 0, .3);
        }

        .page-header p {
            font-size: 1.1rem;
            color: #E8F0FE;
        }

        /* ── Section principale ── */
        .sensibilisation-section {
            padding: 60px 0;
            background: var(--bg);
        }

        .intro-text {
            max-width: 800px;
            margin: 0 auto 50px;
            text-align: center;
            color: var(--text-2);
            font-size: 16px;
            font-weight: 400;
            line-height: 1.9;
        }

        /* ── Titre de section ── */
        .section-title {
            font-size: 1.6rem !important;
            font-weight: 800 !important;
            color: var(--blue) !important;
            text-align: center;
            margin-bottom: 40px;
        }

        /* ── Thèmes ── */
        .themes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 60px;
        }

        .theme-card {
            background: var(--white);
            border-radius: 16px;
            padding: 30px 24px;
            text-align: center;
            border: 1px solid var(--border);
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            transition: all .25s ease;
        }

        .theme-card:hover {
            transform: translateY(-8px);
            border-color: var(--blue);
            box-shadow: 0 12px 32px rgba(0, 51, 153, .12);
        }

        .theme-icon {
            width: 76px;
            height: 76px;
            background: #EBF0FF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            color: var(--blue);
            font-size: 30px;
        }

        .theme-card h3 {
            color: var(--text);
            margin-bottom: 12px;
            font-size: 17px;
            font-weight: 700;
        }

        .theme-card p {
            color: var(--muted);
            font-size: 14px;
            font-weight: 400;
            line-height: 1.7;
        }

        /* ── Stats ── */
        .stats-section {
            background: linear-gradient(135deg, #003399 0%, #2E7D32 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 60px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            text-align: center;
        }

        .stat-number {
            font-size: 48px;
            font-weight: 800;
            color: #FFFFFF;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 15px;
            font-weight: 500;
            color: #C7D7FF;
        }

        /* ── Ressources ── */
        .resources-section {
            margin-top: 60px;
        }

        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
            margin-top: 30px;
        }

        .resource-item {
            background: var(--white);
            border-radius: 12px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: var(--text);
            border: 1px solid var(--border);
            transition: all .25s ease;
        }

        .resource-item:hover {
            transform: translateX(6px);
            border-color: var(--blue);
            box-shadow: 0 4px 16px rgba(0, 51, 153, .1);
        }

        .resource-icon {
            width: 48px;
            height: 48px;
            background: #EBF0FF;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--blue);
            font-size: 22px;
            flex-shrink: 0;
        }

        .resource-content h4 {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 4px;
        }

        .resource-content p {
            font-size: 13px;
            font-weight: 500;
            color: var(--muted);
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .themes-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include 'templates/header.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 data-aos="fade-up">Sensibilisation</h1>
            <p data-aos="fade-up" data-aos-delay="100">
                Informer pour mieux prévenir, sensibiliser pour sauver des vies
            </p>
        </div>
    </div>

    <!-- Sensibilisation Section -->
    <section class="sensibilisation-section">
        <div class="container">
            <div class="intro-text" data-aos="fade-up">
                <p>
                    La sensibilisation est au cœur de notre mission. Nous croyons que l'information
                    et la prévention sont les armes les plus efficaces dans la lutte contre le cancer.
                    À travers nos campagnes, nous touchons des milliers de personnes chaque année.
                </p>
            </div>

            <!-- Thèmes de sensibilisation -->
            <h2 class="section-title" style="text-align: center; color: #003399; margin-bottom: 40px;" data-aos="fade-up">
                Nos thèmes de sensibilisation
            </h2>

            <div class="themes-grid">
                <div class="theme-card" data-aos="fade-up">
                    <div class="theme-icon">
                        <i class="fas fa-female"></i>
                    </div>
                    <h3>Octobre Rose</h3>
                    <p>
                        Octobre Rose est la campagne annuelle de sensibilisation du GSCC dédiée à la lutte contre le cancer du sein. À travers l’information, le dépistage précoce, les témoignages et la mobilisation communautaire, cette initiative rappelle depuis 25 ans l’importance de la prévention et de la solidarité pour sauver des vies.
                    </p>
                </div>

                <div class="theme-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="theme-icon">
                        <i class="fas fa-male"></i>
                    </div>
                    <h3>Bouger et Transpirer pour Vivre</h3>
                    <p>

                        Bouger et Transpirer pour Vivre est une campagne de prévention visant à promouvoir l’activité physique comme moyen efficace de réduire les risques de cancer. En encourageant des habitudes de vie actives et saines, le GSCC sensibilise le public à l’importance du mouvement pour protéger durablement sa santé.
                    </p>
                </div>

                <div class="theme-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="theme-icon">
                        <i class="fas fa-lungs"></i>
                    </div>
                    <h3>Un col sain </h3>
                    <p>
                        Un Col Sain est une campagne de sensibilisation consacrée à la prévention du cancer du col de l’utérus. Par l’éducation, l’information médicale simplifiée et l’encouragement au dépistage, cette initiative aide les femmes à mieux comprendre l’importance d’un suivi gynécologique régulier pour préserver leur santé.
                    </p>
                </div>

                <div class="theme-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="theme-icon">
                        <i class="fas fa-child"></i>
                    </div>
                    <h3>Ann Simen Lavi </h3>
                    <p>
                        Ann Simen Lavi est une campagne de solidarité nationale visant à mobiliser des ressources humaines et financières pour soutenir directement les patients atteints de cancer en Haïti. À travers des témoignages, des appels à contribution et des histoires de survivance, cette initiative rappelle qu’un simple geste de soutien peut devenir une semence de vie pour une famille en détresse.
                    </p>
                </div>

                <div class="theme-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="theme-icon">
                        <i class="fas fa-apple-alt"></i>
                    </div>
                    <h3>KWAZAD KONT KANSÈ</h3>
                    <p>
                        Kwazad Kont Kansè est une vaste campagne de mobilisation communautaire menée à Port-au-Prince comme dans plusieurs villes de province afin de rapprocher l’information préventive des populations. Axée sur le cancer du sein, le cancer du col de l’utérus et le cancer de la prostate, cette initiative combine sensibilisation de masse, éducation sanitaire, orientation au dépistage et engagement communautaire pour encourager une détection précoce et sauver davantage de vies.
                    </p>
                </div>

                <div class="theme-card" data-aos="fade-up" data-aos-delay="500">
                    <div class="theme-icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <h3>Novembre Bleu</h3>
                    <p>
                        Novembre Bleu est la campagne annuelle du GSCC consacrée à la sensibilisation sur les cancers masculins, notamment le cancer de la prostate, des testicules et du pénis. À travers l’information, la lutte contre les tabous et la promotion du dépistage, cette initiative encourage les hommes à adopter une attitude plus responsable face à leur santé et à consulter sans attendre.
                    </p>
                </div>

                <div class="theme-card" data-aos="fade-up" data-aos-delay="600">
                    <div class="theme-icon">
                        <i class="fas fa-smoking-ban"></i>
                    </div>
                    <h3>KONNEN LI EGZISTE PA SIFI : PREVANSYON SE KLE</h3>
                    <p>
                        Konnen li Egziste pa Sifi : Prevansyon se Kle est une campagne de sensibilisation axée sur la prévention active du cancer à travers l’adoption de meilleures habitudes de vie. En mettant l’accent sur l’activité physique, une alimentation saine, l’arrêt du tabac, la limitation de la consommation d’alcool, le dépistage régulier ainsi que la santé mentale, cette initiative encourage chacun à comprendre que la véritable protection passe par des gestes quotidiens concrets.
                    </p>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="stats-section" data-aos="zoom-in">
                <div class="container">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number">300 000+</div>
                            <div class="stat-label">Personnes sensibilisées</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">50+</div>
                            <div class="stat-label">Écoles visitées</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">30+</div>
                            <div class="stat-label">Communautés touchées</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">10 000+</div>
                            <div class="stat-label">Personnes dépistées</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ressources pédagogiques -->
            <div class="resources-section">
                <h2 class="section-title" style="text-align: center; color: #003399; margin-bottom: 40px;" data-aos="fade-up">
                    Ressources pédagogiques
                </h2>

                <div class="resources-grid">
                    <a href="https://drive.google.com/file/d/1eWWp_GsP7wba9TKV4bx_saMtOAZkyrZw/view?usp=sharing" target="_blank" class="resource-item" data-aos="fade-up">
                        <div class="resource-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <div class="resource-content">
                            <h4>GSCC 25 ans de Lutte</h4>
                            <p>PDF - 2.5 MB</p>
                        </div>
                    </a>

                    <a href="#" class="resource-item" data-aos="fade-up" data-aos-delay="50">
                        <div class="resource-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <div class="resource-content">
                            <h4>Brochure "Cancer de la prostate"</h4>
                            <p>PDF - 1.8 MB</p>
                        </div>
                    </a>

                    <a href="https://youtu.be/peX2zTu3z8w?si=nJzcEB1_KMiTA6bh" target="_blank" class="resource-item" data-aos="fade-up" data-aos-delay="100">
                        <div class="resource-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <div class="resource-content">
                            <h4>Vidéo témoignage : François L. Péan, Membre Fondateur</h4>
                            <p>YouTube - 8 min</p>
                        </div>
                    </a>
                    <a href="https://drive.google.com/file/d/1ilvcWUTnH7_CfECGTSDdyEBk1wCRUA7t/view?usp=sharing" target="_blank" class="resource-item" data-aos="fade-up">
                        <div class="resource-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <div class="resource-content">
                            <h4>Devenez Partenaire Contre le cancer</h4>
                            <p>PDF - 2.5 MB</p>
                        </div>
                    </a>


                </div>
            </div>
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>
</body>

</html>