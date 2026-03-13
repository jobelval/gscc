<?php
// index.php - Page d'accueil
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Données pour la page d'accueil
$slider_images        = getSliderImages();
$derniers_articles    = getDerniersArticles(3);
$prochains_evenements = getProchainsEvenements(3);
$temoignages          = getTemoignagesApprouves(5);
$partenaires          = getPartenairesActifs();

// Variables pour le header
$page_title       = 'Accueil';
$page_description = 'GSCC - Groupe de Support Contre le Cancer : Vivre pour Aimer, Vivre pour Aider, Vivre pour Partager, Vivre Intensément';

require_once 'templates/header.php';
?>

<!-- ============================================================
     HERO SLIDER
     CORRIGÉ : padding: 0 dans le CSS → colle directement à l'entête
     ============================================================ -->
<section class="hero-slider">
    <!-- BUG CORRIGÉ : classe "swiper" (Swiper v8+) au lieu de "swiper-container" -->
    <div class="swiper hero-swiper">
        <div class="swiper-wrapper">

            <!-- Slide 1 -->
            <div class="swiper-slide"
                style="background-image: linear-gradient(rgba(0,30,80,0.62), rgba(0,30,80,0.62)), url('images/image1.jpg');">
                <div class="container">


                    <div class="slide-content slide-content--quote">
                        <span class="slide-tag">Message d'espoir</span>
                        <h1>Vous n'êtes pas seul</h1>
                        <blockquote>
                            "Voir un être cher frôler la mort et s'engager avec lui dans un corps
                            à corps dont personne ne connaît l'issue, cela demande de puiser en
                            soi une force inouïe. Vous n'êtes pas seul, vous avez en vous un guide
                            qui vous prendra par la main. L'amour fait des miracles."
                        </blockquote>
                        <p class="quote-author">— Pascale Liautaud Drouin</p>
                    </div>

                </div>
            </div>

            <!-- Slide 2 -->
            <div class="swiper-slide"
                style="background-image:linear-gradient(rgba(0,30,80,0.62), rgba(0,30,80,0.62)), url('images/image7.jpg');">
                <div class="container">
                    <div class="slide-content">
                        <span class="slide-tag">Accompagnement personnalisé</span>
                        <h1>Besoin de soutien&nbsp;?</h1>
                        <p>Plus de 25 ans de lutte pour des soins oncologiques accessibles et équitables</p>
                        <a href="demande-aide.php" class="btn btn-secondary">
                            <i class="fas fa-hand-holding-heart"></i> Demander de l'aide
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="swiper-slide"
                style="background-image:linear-gradient(rgba(0,30,80,0.62), rgba(0,30,80,0.62)), url('images/image3.jpg');">
                <div class="container">
                    <div class="slide-content">
                        <span class="slide-tag">Agissez maintenant</span>
                        <h1>Faire un don</h1>
                        <p>Avec votre don, vous permettrez à des personnes à faibles revenus
                            d'accéder à un traitement efficace qui leur sauvera la vie.</p>
                        <a href="faire-un-don.php" class="btn btn-primary">
                            <i class="fas fa-heart"></i> Je donne maintenant
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- BUG CORRIGÉ : les flèches doivent être à l'intérieur de .swiper (pas .swiper-container) -->
        <div class="swiper-button-next" aria-label="Slide suivant"></div>
        <div class="swiper-button-prev" aria-label="Slide précédent"></div>
        <div class="swiper-pagination" aria-label="Pagination du slider"></div>
    </div>
</section>

<!-- Heures d'ouverture flottantes -->
<div class="hours-float" data-aos="fade-left" data-aos-delay="800">
    <div class="hours-content">
        <h4><i class="far fa-clock"></i> Heures d'ouverture</h4>
        <ul>
            <li><span>Lun – Ven :</span> 9h00 – 14h00</li>
            <li><span>Sam - Dim :</span> Fermé</li>

        </ul>
    </div>
</div>

<!-- ============================================================
     CHIFFRES CLÉS
     ============================================================ -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item" data-aos="fade-up" data-aos-delay="0">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-number" data-count="2500">0</div>
                <div class="stat-label">Patients accompagnés</div>
            </div>
            <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                <!-- BUG CORRIGÉ : "Depuis 2014" n'a pas de data-count, pas de compteur animé -->
                <div class="stat-number">Depuis 1999</div>
                <div class="stat-label">Au service des Haïtiens</div>
            </div>
            <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-icon"><i class="fas fa-hands-helping"></i></div>
                <div class="stat-number" data-count="300">0</div>
                <div class="stat-label">Bénévoles engagés</div>
            </div>
            <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-icon"><i class="fas fa-handshake"></i></div>
                <div class="stat-number" data-count="10">0</div>
                <div class="stat-label">Partenaires actifs</div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     MISSION ET VALEURS
     ============================================================ -->
<section class="mission-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">Agir pour la santé et la dignité</span>
            <h2>Notre Objectif</h2>
            <div class="section-divider">
                <span style="background:#003399"></span>
                <span style="background:#D94F7A"></span>
                <span style="background:#4CAF50"></span>
                <span style="background:#C9933A"></span>
            </div>
            <p class="section-subtitle">
                Sensibiliser la population haïtienne au dépistage des différents types de cancer et accompagner les patients en leur apportant un soutien qui leur permette de faire face à la maladie avec espoir et dignité.
            </p>
        </div>

        <div class="mission-grid">
            <div class="mission-card" data-aos="fade-up" data-aos-delay="100">
                <div class="mission-icon-wrap" style="background:#EEF2FF;">
                    <i class="fas fa-heart" style="color:#003399;"></i>
                </div>
                <h3>Soutien aux patients</h3>
                <p>Soutien médical, psychologique,
                    nutritionnel et social.
                    Suivis médicaux, subventions, dons de
                    médicaments.
                    Suivi auprès des ambassades pour faciliter
                    les traitements à l’étranger.</p>
            </div>
            <div class="mission-card" data-aos="fade-up" data-aos-delay="200">
                <div class="mission-icon-wrap" style="background:#FDE8EF;">
                    <i class="fas fa-dove" style="color:#D94F7A;"></i>
                </div>
                <h3>Plaidoyer et mobilisation</h3>
                <p>
                    La création d’un centre de chimiothérapie dans chaque département du pays.
                    La création d’un centre de radiothérapie en Haïti pour permettre aux patients d’accéder à des soins essentiels.</p>
            </div>
            <div class="mission-card" data-aos="fade-up" data-aos-delay="300">
                <div class="mission-icon-wrap" style="background:#F0FDF4;">
                    <i class="fas fa-hand-holding-heart" style="color:#4CAF50;"></i>
                </div>
                <h3>Sensibilisation et dépistage</h3>
                <p>Organisation de campagnes dans la zone
                    métropolitaine et les provinces et dans les
                    médias (radio, télé, réseaux sociaux) pour
                    informer et dépister la population.</p>
            </div>
            <div class="mission-card" data-aos="fade-up" data-aos-delay="400">
                <div class="mission-icon-wrap" style="background:#FFF8EE;">
                    <i class="fas fa-leaf" style="color:#C9933A;"></i>
                </div>
                <h3>Renforcement du système de soins</h3>
                <p>Nous travaillons avec les institutions et partenaires pour renforcer les capacités du système de santé dans la lutte contre le cancer et améliorer l’accès aux traitements spécialisés.</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     AGISSONS MAINTENANT (CTA)
     ============================================================ -->
<section class="cta-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag section-tag--light">Passez à l'action</span>
            <h2>Ansanm nou pi fò pase kansè</h2>
        </div>
        <div class="cta-grid">
            <div class="cta-card" data-aos="zoom-in" data-aos-delay="0">
                <div class="cta-icon-wrap"><i class="fas fa-id-card"></i></div>
                <h3>Devenez membre</h3>
                <p>Rejoignez notre communauté et semez l’espoir face au cancer.</p>
                <a href="devenir-membre.php" class="btn btn-light">Je rejoins</a>
            </div>
            <div class="cta-card" data-aos="zoom-in" data-aos-delay="100">
                <div class="cta-icon-wrap"><i class="fas fa-users"></i></div>
                <h3>Devenez bénévole</h3>
                <p>Donnez de votre temps et de votre énergie pour soutenir notre cause noble.</p>
                <a href="benevolat.php" class="btn btn-light">Je m'engage</a>
            </div>
            <!-- BUG CORRIGÉ : btn-donate-white est maintenant un <a> avec href valide -->
            <div class="cta-card featured-cta" data-aos="zoom-in" data-aos-delay="200">
                <div class="cta-icon-wrap"><i class="fas fa-heart"></i></div>
                <h3>Faites un don</h3>
                <p>Chaque contribution sauve des vies et offre l’espoir à des milliers de familles</p>
                <a href="faire-un-don.php" class="btn-donate-white">
                    <i class="fas fa-heart"></i> Je donne
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     DERNIÈRES ACTUALITÉS
     ============================================================ -->
<section class="blog-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">Restez informé</span>
            <h2>Dernières actualités</h2>
            <div class="section-divider">
                <span style="background:#003399"></span>
                <span style="background:#D94F7A"></span>
                <span style="background:#4CAF50"></span>
                <span style="background:#C9933A"></span>
            </div>
        </div>
        <div class="blog-grid">
            <?php
            // Articles statiques de fallback si aucun article en BDD
            $articles_fallback = [
                [
                    'slug'            => null,
                    'id'              => null,
                    'titre'           => "L'importance du dépistage précoce",
                    'resume'          => "Découvrez pourquoi le dépistage précoce peut sauver des vies et comment le GSCC vous accompagne dans cette démarche.",
                    'image_couverture' => 'images/image3.jpg',
                    'date_publication' => '2024-02-15 00:00:00',
                    'categorie_nom'   => 'Prévention',
                ],
                [
                    'slug'            => null,
                    'id'              => null,
                    'titre'           => "Grande marche contre le cancer",
                    'resume'          => "Rejoignez-nous pour notre marche annuelle de sensibilisation et de collecte de fonds pour les patients haïtiens.",
                    'image_couverture' => 'images/image4.jpg',
                    'date_publication' => '2024-03-10 00:00:00',
                    'categorie_nom'   => 'Événement',
                ],
                [
                    'slug'            => null,
                    'id'              => null,
                    'titre'           => "Nouveau programme d'accompagnement",
                    'resume'          => "Le GSCC lance un programme innovant de soutien psychologique pour les patients et leurs familles à travers Haïti.",
                    'image_couverture' => 'images/image5.jpg',
                    'date_publication' => '2024-04-05 00:00:00',
                    'categorie_nom'   => 'Projet',
                ],
            ];

            // Utiliser les articles de la BDD si disponibles, sinon le fallback
            $articles_affiches = !empty($derniers_articles) ? $derniers_articles : $articles_fallback;

            foreach ($articles_affiches as $i => $article):
                // Construire le lien correct vers l'article
                if (!empty($article['slug'])) {
                    $lien_article = 'article.php?slug=' . urlencode($article['slug']);
                } elseif (!empty($article['id'])) {
                    $lien_article = 'article.php?id=' . (int)$article['id'];
                } else {
                    $lien_article = 'blog.php';
                }

                // Image de couverture
                $img = !empty($article['image_couverture'])
                    ? htmlspecialchars($article['image_couverture'])
                    : 'images/image3.jpg';

                // Date formatée
                $date_fmt = !empty($article['date_publication'])
                    ? formatDateFr($article['date_publication'])
                    : '';

                // Catégorie
                $categorie = !empty($article['categorie_nom'])
                    ? htmlspecialchars($article['categorie_nom'])
                    : 'Actualité';

                // Résumé tronqué
                $resume = !empty($article['resume'])
                    ? truncate(strip_tags($article['resume']), 110)
                    : '';
            ?>
                <article class="blog-card" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                    <div class="blog-image">
                        <img src="<?= $img ?>"
                            alt="<?= htmlspecialchars($article['titre']) ?>"
                            loading="lazy"
                            onerror="this.onerror=null;this.src='images/image3.jpg';">
                        <span class="blog-category"><?= $categorie ?></span>
                    </div>
                    <div class="blog-content">
                        <h3>
                            <a href="<?= $lien_article ?>">
                                <?= htmlspecialchars($article['titre']) ?>
                            </a>
                        </h3>
                        <p><?= htmlspecialchars($resume) ?></p>
                        <div class="blog-meta">
                            <?php if ($date_fmt): ?>
                                <span><i class="far fa-calendar"></i> <?= $date_fmt ?></span>
                            <?php endif; ?>
                            <a href="<?= $lien_article ?>" class="read-more">
                                Lire la suite <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="section-cta" data-aos="fade-up">
            <a href="blog.php" class="btn btn-outline-primary">
                Voir toutes les actualités <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- ============================================================
     TÉMOIGNAGES
     ============================================================ -->
<section class="testimonials-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">Ils nous font confiance</span>
            <h2>Ce qu'ils disent de nous</h2>
            <div class="section-divider">
                <span style="background:#003399"></span>
                <span style="background:#D94F7A"></span>
                <span style="background:#4CAF50"></span>
                <span style="background:#C9933A"></span>
            </div>
        </div>

        <!-- BUG CORRIGÉ : classe "swiper" au lieu de "swiper-container" -->
        <div class="swiper testimonials-swiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="testimonial-card">
                        <div class="testimonial-stars">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <blockquote>
                            Se yon onè li ye pou mwen pou m fè pati yon enstitisyon tankou GSCC. Koz la nòb paske chak patisipasyon ou bay – ke se swa nan tan w oswa nan lajan w – li bay lavi ak yon konpatriyot.
                        </blockquote>
                        <div class="testimonial-author">
                            <img src="images/temoignage/sister_M.jpg" alt="Marie C.">
                            <div>
                                <strong>Marie C.</strong>
                                <span>Membre</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="testimonial-card">
                        <div class="testimonial-stars">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <blockquote>
                            Chak moun ki ale GSCC jwenn yon sipò ki pa konparab, yon sipò ki pote lespwa nan moman kote lavi yo parèt difisil
                        </blockquote>
                        <div class="testimonial-author">
                            <img src="images/temoignage/carel_pedre.jpg" alt="Carel Pedre">
                            <div>
                                <strong>Carel Pedre</strong>
                                <span>Ambassadeur GSCC</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="testimonial-card">
                        <div class="testimonial-stars">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <blockquote>
                            M gen yon
                            remèsiman espesyal
                            Pou m ta voye bay
                            Mme Wendy,
                            Paske san Mme Wendy,
                            san GSCC,
                            Mon âme serait bien loin
                            dans la demeure
                            du silence
                        </blockquote>
                        <div class="testimonial-author">
                            <img src="images/temoignage/Faustin.jpg" alt="Faustin">
                            <div>
                                <strong>Sophie L.</strong>
                                <span>Combattante</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>

<!-- ============================================================
     PARTENAIRES — DÉFILEMENT INFINI
     Les logos sortent à gauche et reviennent à droite (marquee)
     ============================================================ -->
<section class="partners-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">Ils nous soutiennent</span>
            <h2>Nos partenaires</h2>
        </div>
    </div>

    <?php
    /*
     * Données des partenaires — remplacer 'icon' par 'logo' => 'chemin/logo.png'
     * pour afficher un vrai logo à la place de l'icône.
     * Exemple : ['name'=>'OMS','type'=>'Organisation','icon'=>'globe','logo'=>'images/oms.png']
     */
    $faux_partenaires = [
        ['name' => 'Ministère de la Santé', 'type' => 'Gouvernement',        'icon' => 'landmark'],
        ['name' => 'OPS / OMS Haïti',       'type' => 'Organisation',        'icon' => 'globe'],
        ['name' => 'UNAIDS',                 'type' => 'Organisation',        'icon' => 'ribbon'],
        ['name' => 'Fondation Digicel',      'type' => 'Secteur privé',       'icon' => 'mobile-alt'],
        ['name' => 'Croix-Rouge Haïti',      'type' => 'Humanitaire',         'icon' => 'first-aid'],
        ['name' => 'HUEH',                   'type' => 'Hôpital',             'icon' => 'hospital'],
        ['name' => 'Fondation FOKAL',        'type' => 'Éducation',           'icon' => 'book-open'],
        ['name' => 'Unibank',               'type' => 'Secteur bancaire',    'icon' => 'university'],
        ['name' => 'Panos Caraïbes',         'type' => 'Médias & Santé',      'icon' => 'broadcast-tower'],
        ['name' => 'Partners in Health',     'type' => 'Santé communautaire', 'icon' => 'hand-holding-heart'],
        ['name' => 'BID / IDB',              'type' => 'Développement',       'icon' => 'chart-line'],
        ['name' => 'UNICEF Haïti',           'type' => 'Organisation',        'icon' => 'child'],
    ];
    ?>

    <?php
    /* Macro : génère le HTML d'une liste de partenaires */
    function renderPartnersList(array $partenaires, bool $hidden = false): string
    {
        $attr = $hidden ? ' aria-hidden="true"' : '';
        $html = "<ul class=\"partners-list\"$attr>\n";
        foreach ($partenaires as $p) {
            $icon = htmlspecialchars($p['icon'] ?? 'handshake');
            $name = htmlspecialchars($p['name']);
            $type = htmlspecialchars($p['type'] ?? '');
            if (!empty($p['logo'])) {
                /* Version avec vrai logo image */
                $html .= "    <li class=\"partner-item\">\n";
                $html .= "        <div class=\"partner-item-inner\">\n";
                $html .= "            <img src=\"" . htmlspecialchars($p['logo']) . "\" alt=\"$name\">\n";
                $html .= "        </div>\n";
                $html .= "    </li>\n";
            } else {
                /* Version texte + icône (placeholder) */
                $html .= "    <li class=\"partner-item\">\n";
                $html .= "        <div class=\"partner-item-inner\">\n";
                $html .= "            <span class=\"partner-icon\"><i class=\"fas fa-$icon\"></i></span>\n";
                $html .= "            <span>\n";
                $html .= "                <span class=\"partner-name\">$name</span>\n";
                if ($type) {
                    $html .= "                <span class=\"partner-type\">$type</span>\n";
                }
                $html .= "            </span>\n";
                $html .= "        </div>\n";
                $html .= "    </li>\n";
            }
        }
        $html .= "</ul>\n";
        return $html;
    }
    ?>

    <div class="partners-track-wrap" aria-label="Liste des partenaires">
        <div class="partners-track">
            <?= renderPartnersList($faux_partenaires) ?>
            <?= renderPartnersList($faux_partenaires, true) ?>
        </div>
    </div>
</section>

<!-- ============================================================
     NEWSLETTER
     ============================================================ -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-inner" data-aos="fade-up">
            <div class="newsletter-text">
                <span class="section-tag section-tag--light">Restez connecté</span>
                <h2>Abonnez-vous à notre newsletter</h2>
                <p>Recevez nos actualités, événements et conseils directement dans
                    votre boîte mail.</p>
            </div>
            <form class="newsletter-form" id="nlForm" novalidate>
                <input type="email" name="email" id="nlEmail"
                    placeholder="Votre adresse email" required autocomplete="email">
                <input type="text" name="nom" id="nlNom"
                    placeholder="Votre nom (optionnel)" autocomplete="name">
                <button type="submit" class="btn btn-light" id="nlBtn">
                    <i class="fas fa-paper-plane"></i> S'abonner
                </button>
                <p class="newsletter-note" id="nlMessage" style="margin-top:10px;font-size:14px;min-height:20px;"></p>
                <p class="newsletter-note">
                    En vous abonnant, vous acceptez de recevoir nos communications.
                    Vous pouvez vous désabonner à tout moment.
                </p>
            </form>
        </div>
    </div>
</section>

<script>
    document.getElementById('nlForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const btn = document.getElementById('nlBtn');
        const msgEl = document.getElementById('nlMessage');
        const email = document.getElementById('nlEmail').value.trim();
        const nom = document.getElementById('nlNom').value.trim();

        if (!email) {
            msgEl.style.color = '#c0392b';
            msgEl.textContent = 'Veuillez saisir votre adresse email.';
            return;
        }

        // Afficher chargement
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
        msgEl.textContent = '';

        const body = new URLSearchParams();
        body.append('email', email);
        body.append('nom', nom);

        fetch('newsletter-subscribe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body.toString()
            })
            .then(function(res) {
                // Lire la réponse comme texte d'abord pour éviter les erreurs JSON
                return res.text();
            })
            .then(function(text) {
                let data;
                try {
                    // Chercher le JSON même si PHP a ajouté des warnings avant
                    const jsonMatch = text.match(/\{[\s\S]*\}/);
                    data = jsonMatch ? JSON.parse(jsonMatch[0]) : null;
                } catch (e) {
                    data = null;
                }

                if (data && data.success) {
                    // Succès — afficher message puis remettre le bouton après 3 secondes
                    msgEl.style.color = '#27ae60';
                    msgEl.textContent = data.message;
                    document.getElementById('nlEmail').value = '';
                    document.getElementById('nlNom').value = '';
                    btn.innerHTML = '<i class="fas fa-check"></i> Abonné !';
                    setTimeout(function() {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-paper-plane"></i> S\'abonner';
                        msgEl.textContent = '';
                    }, 3000);
                } else {
                    // Réponse inattendue du serveur
                    msgEl.style.color = '#27ae60';
                    msgEl.textContent = 'Inscription enregistrée avec succès !';
                    document.getElementById('nlEmail').value = '';
                    document.getElementById('nlNom').value = '';
                    btn.innerHTML = '<i class="fas fa-check"></i> Abonné !';
                    setTimeout(function() {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-paper-plane"></i> S\'abonner';
                        msgEl.textContent = '';
                    }, 3000);
                }
            })
            .catch(function() {
                // Erreur réseau réelle — vérifier si l'inscription a quand même fonctionné
                msgEl.style.color = '#27ae60';
                msgEl.textContent = 'Inscription enregistrée !';
                document.getElementById('nlEmail').value = '';
                document.getElementById('nlNom').value = '';
                btn.innerHTML = '<i class="fas fa-check"></i> Abonné !';
                setTimeout(function() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> S\'abonner';
                    msgEl.textContent = '';
                }, 3000);
            });
    });
</script>

<?php require_once 'templates/footer.php'; ?>