-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 08, 2026 at 07:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gscc`
--

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `contenu` longtext DEFAULT NULL,
  `resume` text DEFAULT NULL,
  `image_couverture` varchar(255) DEFAULT NULL,
  `categorie_id` int(11) DEFAULT NULL,
  `auteur_id` int(11) DEFAULT NULL,
  `date_publication` datetime DEFAULT NULL,
  `statut` enum('publie','brouillon','archive') DEFAULT 'brouillon',
  `vue_compteur` int(11) DEFAULT 0,
  `temps_lecture` int(11) DEFAULT NULL,
  `est_vedette` tinyint(1) DEFAULT 0,
  `tags` text DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_modification` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `titre`, `slug`, `contenu`, `resume`, `image_couverture`, `categorie_id`, `auteur_id`, `date_publication`, `statut`, `vue_compteur`, `temps_lecture`, `est_vedette`, `tags`, `meta_description`, `meta_keywords`, `date_creation`, `date_modification`) VALUES
(1, 'L\'importance du dépistage précoce', 'importance-depistage-precoce', '<p>Le dépistage précoce du cancer peut sauver des vies. Découvrez pourquoi il est essentiel de se faire dépister régulièrement.</p>\r\n<h2>Pourquoi se faire dépister ?</h2>\r\n<p>Le dépistage permet de détecter le cancer à un stade précoce, quand il est le plus traitable. Cela augmente considérablement les chances de guérison.</p>\r\n<p>En Haïti, beaucoup de cancers sont diagnostiqués trop tard, simplement parce que les patients attendent l\'apparition de symptômes visibles. Le GSCC vous encourage à ne pas attendre — un examen régulier peut tout changer.</p>', 'Découvrez pourquoi le dépistage précoce peut sauver des vies et comment le GSCC vous accompagne dans cette démarche essentielle.', 'uploads/galerie/Depistage-.jpg', 1, 1, '2026-02-28 07:22:26', 'publie', 13, 2, 0, 'dépistage,cancer,prévention,santé', 'Pourquoi le dépistage précoce du cancer est essentiel en Haïti. Le GSCC vous explique tout.', NULL, '2026-02-28 07:22:26', '2026-03-07 21:50:22'),
(2, 'Grande marche contre le cancer', 'grande-marche-contre-cancer', '<p>Rejoignez-nous pour notre marche annuelle de sensibilisation et de collecte de fonds contre le cancer.</p>\r\n<h2>Ensemble, faisons la différence</h2>\r\n<p>Cette marche est l\'occasion de montrer notre solidarité avec les personnes touchées par le cancer et de collecter des fonds pour nos programmes d\'accompagnement.</p>\r\n<p>Chaque année, des centaines de participants se rassemblent dans les rues de Port-au-Prince pour rappeler que le cancer ne doit plus être un sujet tabou en Haïti. Venez nombreux, en famille, entre amis ou entre collègues.</p>', 'Rejoignez-nous pour notre marche annuelle de sensibilisation et de collecte de fonds contre le cancer en Haïti.', 'uploads/galerie/Awareness-campaign-at-Marie-Louise-Trichet-.jpg', 2, 1, '2026-02-28 07:22:26', 'publie', 7, 2, 0, 'marche,événement,sensibilisation,solidarité', 'La grande marche annuelle du GSCC contre le cancer à Port-au-Prince. Rejoignez le mouvement.', NULL, '2026-02-28 07:22:26', '2026-03-07 19:19:53'),
(3, 'Nouveau programme d\'accompagnement', 'nouveau-programme-accompagnement', '<p>Le GSCC lance un programme innovant de soutien psychologique pour les patients et leurs familles.</p>\r\n<h2>Un soutien personnalisé</h2>\r\n<p>Notre équipe de psychologues propose un accompagnement adapté à chaque situation. Que vous soyez en cours de traitement ou en période de rémission, nous sommes là pour vous.</p>\r\n<p>Ce programme comprend des séances individuelles, des groupes de parole et des ateliers de bien-être. Tout est gratuit et confidentiel.</p>', 'Le GSCC lance un programme innovant de soutien psychologique gratuit pour les patients atteints de cancer et leurs familles.', 'uploads/galerie/20251008_093818.jpg', 3, 1, '2026-02-28 07:22:26', 'publie', 6, 2, 0, 'accompagnement,soutien,psychologie,programme', 'Nouveau programme d\'accompagnement psychologique gratuit du GSCC pour les patients atteints de cancer.', NULL, '2026-02-28 07:22:26', '2026-03-07 08:48:49'),
(4, 'Le dépistage précoce du cancer : pourquoi il peut vous sauver la vie', 'depistage-precoce-cancer-sauver-vie', '<p>En Haïti, comme dans de nombreux pays en développement, le cancer est souvent diagnostiqué à un stade avancé, ce qui rend le traitement plus difficile et réduit considérablement les chances de guérison. Le Groupe de Support Contre le Cancer (GSCC) place le dépistage précoce au cœur de sa mission, convaincu que détecter la maladie tôt peut faire toute la différence.</p>\r\n\r\n<h2>Qu\'est-ce que le dépistage précoce ?</h2>\r\n<p>Le dépistage précoce consiste à rechercher la présence d\'un cancer chez une personne qui ne présente pas encore de symptômes visibles. L\'objectif est de détecter la maladie avant qu\'elle ne se propage, à un stade où le traitement est généralement plus simple, moins coûteux et plus efficace.</p>\r\n\r\n<p>Pour le cancer du sein par exemple, une mammographie réalisée régulièrement peut détecter une tumeur plusieurs années avant qu\'elle ne devienne palpable. Pour le cancer du col de l\'utérus, un simple frottis cervical permet de repérer des cellules anormales avant qu\'elles ne deviennent cancéreuses.</p>\r\n\r\n<h2>Les cancers les plus fréquents en Haïti</h2>\r\n<p>Selon les données disponibles, les cancers les plus diagnostiqués en Haïti sont :</p>\r\n<ul>\r\n<li><strong>Le cancer du sein</strong> — premier cancer chez la femme</li>\r\n<li><strong>Le cancer du col de l\'utérus</strong> — fortement lié au virus HPV</li>\r\n<li><strong>Le cancer de la prostate</strong> — premier cancer chez l\'homme après 50 ans</li>\r\n<li><strong>Le cancer colorectal</strong> — en augmentation progressive</li>\r\n</ul>\r\n\r\n<h2>Les obstacles au dépistage en Haïti</h2>\r\n<ul>\r\n<li><strong>La méconnaissance</strong> : beaucoup de personnes ignorent l\'existence des examens de dépistage</li>\r\n<li><strong>La peur du diagnostic</strong> : certains préfèrent ne pas savoir</li>\r\n<li><strong>L\'accès limité aux soins</strong> : les structures médicales spécialisées sont concentrées dans les grandes villes</li>\r\n<li><strong>Le coût</strong> : les examens restent inaccessibles pour une grande partie de la population</li>\r\n</ul>\r\n\r\n<h2>Ce que fait le GSCC pour vous</h2>\r\n<p>Le GSCC organise régulièrement des <strong>journées de dépistage gratuit</strong> dans différentes zones du pays. Notre équipe de bénévoles et de professionnels de santé se déplace pour apporter les examens directement dans les communautés.</p>\r\n\r\n<blockquote style=\"border-left: 4px solid #D94F7A; padding-left: 16px; margin: 20px 0; color: #555; font-style: italic;\">\r\n« Un cancer détecté tôt, c\'est une vie sauvée. N\'attendez pas les symptômes — agissez maintenant. »\r\n</blockquote>\r\n\r\n<h2>Quand se faire dépister ?</h2>\r\n<ul>\r\n<li><strong>Cancer du sein</strong> : examen clinique annuel dès 25 ans, mammographie dès 40 ans</li>\r\n<li><strong>Cancer du col</strong> : frottis cervical dès le début de l\'activité sexuelle, tous les 2-3 ans</li>\r\n<li><strong>Cancer de la prostate</strong> : dosage du PSA dès 50 ans (45 ans si antécédents familiaux)</li>\r\n<li><strong>Cancer colorectal</strong> : coloscopie dès 50 ans ou en cas de symptômes</li>\r\n</ul>\r\n\r\n<p>N\'attendez plus. Contactez le GSCC pour connaître les prochaines dates de dépistage près de chez vous.</p>', 'Le dépistage précoce peut sauver des vies. Découvrez pourquoi il est essentiel de ne pas attendre les symptômes et comment le GSCC vous accompagne.', 'uploads/galerie/IMG_3910.jpg\r\n', 1, 1, '2025-10-01 08:00:00', 'publie', 2, 5, 1, 'dépistage,cancer,prévention,santé,Haïti', 'Le dépistage précoce du cancer sauve des vies. Le GSCC vous explique pourquoi se faire dépister tôt est essentiel en Haïti.', NULL, '2025-10-01 08:00:00', '2026-03-07 19:30:26'),
(5, 'Retour sur notre journée de sensibilisation du 8 octobre 2025', 'journee-sensibilisation-octobre-2025', '<p>Le 8 octobre 2025, le Groupe de Support Contre le Cancer (GSCC) a organisé une journée complète de sensibilisation et de formation à Port-au-Prince. Cette journée, qui s\'inscrit dans le cadre du <strong>Mois de la Sensibilisation au Cancer du Sein</strong>, a réuni bénévoles, professionnels de santé et membres de la communauté autour d\'un objectif commun : informer pour mieux protéger.</p>\r\n\r\n<h2>Une matinée de cohésion sous le signe du ruban rose</h2>\r\n<p>La journée a débuté en plein air avec un rassemblement de l\'équipe GSCC, reconnaissable à ses t-shirts officiels ornés du slogan <em>« Kansè Pa Ka Tann — Ann Aji Kounyà »</em> (Le cancer n\'attend pas — Agissons maintenant). Ce moment de cohésion a permis de rappeler les valeurs qui animent notre association : solidarité, engagement et espoir.</p>\r\n\r\n<h2>Une conférence médicale de qualité</h2>\r\n<p>L\'après-midi a été consacrée à une <strong>conférence médicale</strong> animée par le Dr Paul Jr Fontilus, gynécologue-obstétricien, sur le thème : <em>« Tout Sa Nou Dwe Konnen sou Kansè Kòl Matris »</em>.</p>\r\n<p>Le Dr Fontilus a abordé avec pédagogie et clarté :</p>\r\n<ul>\r\n<li>L\'anatomie du col de l\'utérus et les stades de développement du cancer</li>\r\n<li>Le rôle du virus HPV dans le développement de la maladie</li>\r\n<li>Les signes d\'alerte à ne pas ignorer</li>\r\n<li>Les méthodes de dépistage disponibles en Haïti</li>\r\n<li>Les options de traitement selon le stade du cancer</li>\r\n</ul>\r\n\r\n<h2>Un public touché et mobilisé</h2>\r\n<p>Plus d\'une trentaine de personnes ont participé à cette conférence. Plusieurs participants ont exprimé leur reconnaissance envers le GSCC pour avoir rendu accessible une information médicale souvent réservée aux professionnels.</p>\r\n\r\n<blockquote style=\"border-left: 4px solid #003399; padding-left: 16px; margin: 20px 0; color: #555; font-style: italic;\">\r\n« Aujourd\'hui j\'ai appris des choses que j\'aurais dû savoir bien avant. Je vais en parler à toutes mes amies. » — Une participante\r\n</blockquote>', 'Retour sur la journée de sensibilisation du GSCC le 8 octobre 2025 : marche, conférence médicale du Dr Fontilus et mobilisation communautaire.', 'uploads/galerie/20251008_103755.jpg', 2, 1, '2025-10-10 09:00:00', 'publie', 5, 4, 0, 'événement,sensibilisation,cancer du col,Dr Fontilus,octobre 2025', 'Retour sur la journée de sensibilisation GSCC du 8 octobre 2025 à Port-au-Prince.', NULL, '2025-10-10 09:00:00', '2026-03-06 19:44:01'),
(6, 'Cancer du col de l\'utérus : ce que toute femme haïtienne doit savoir', 'cancer-col-uterus-femme-haitienne', '<p>Le cancer du col de l\'utérus est l\'un des cancers les plus fréquents chez la femme en Haïti. Pourtant, c\'est aussi l\'un des plus évitables. Grâce au dépistage et à la vaccination, des milliers de vies pourraient être sauvées chaque année.</p>\r\n\r\n<h2>Qu\'est-ce que le cancer du col de l\'utérus ?</h2>\r\n<p>Le col de l\'utérus est la partie inférieure de l\'utérus qui s\'ouvre sur le vagin. Le cancer du col se développe lorsque des cellules de cette zone deviennent anormales et se multiplient de façon incontrôlée.</p>\r\n<p>Dans la très grande majorité des cas (plus de 99%), ce cancer est causé par le <strong>Papillomavirus humain (HPV)</strong>, un virus qui se transmet par contact sexuel.</p>\r\n\r\n<h2>Quels sont les signes d\'alerte ?</h2>\r\n<p>Aux stades précoces, le cancer du col ne provoque souvent <strong>aucun symptôme</strong>. À un stade plus avancé :</p>\r\n<ul>\r\n<li>Des saignements vaginaux anormaux (entre les règles, après les rapports sexuels)</li>\r\n<li>Des pertes vaginales inhabituelles</li>\r\n<li>Des douleurs pelviennes ou lombaires persistantes</li>\r\n</ul>\r\n<p><strong>Si vous présentez l\'un de ces signes, consultez un médecin sans attendre.</strong></p>\r\n\r\n<h2>Comment se protéger ?</h2>\r\n<h3>1. Le frottis cervical</h3>\r\n<p>Examen simple et rapide, recommandé dès le début de l\'activité sexuelle, tous les 2 à 3 ans.</p>\r\n\r\n<h3>2. La vaccination contre le HPV</h3>\r\n<p>Le vaccin anti-HPV est le plus efficace entre 9 et 14 ans, avant le début de l\'activité sexuelle.</p>\r\n\r\n<h3>3. Le préservatif</h3>\r\n<p>Réduit le risque de transmission du HPV et d\'autres infections sexuellement transmissibles.</p>\r\n\r\n<h2>Le rôle du GSCC</h2>\r\n<p>Le GSCC organise des <strong>séances d\'information gratuites</strong> sur le cancer du col dans les écoles, les marchés et les centres communautaires. Contactez-nous pour en savoir plus.</p>', 'Le cancer du col de l\'utérus est évitable. Le GSCC vous explique les causes, les signes d\'alerte et comment vous protéger efficacement.', 'uploads/galerie/20251008_105014.jpg', 1, 1, '2025-10-15 08:00:00', 'publie', 1, 5, 0, 'cancer du col,utérus,HPV,dépistage,frottis,prévention', 'Cancer du col de l\'utérus en Haïti : causes, signes d\'alerte et prévention expliqués par le GSCC.', NULL, '2025-10-15 08:00:00', NULL),
(7, 'Le GSCC lance son programme de soutien aux patients en traitement', 'programme-soutien-patients-traitement-gscc', '<p>Face aux défis que représente un parcours de soins contre le cancer en Haïti, le GSCC lance un nouveau programme d\'accompagnement destiné aux patients en cours de traitement et à leurs familles. Ce programme, entièrement gratuit, vise à réduire l\'isolement et à améliorer la qualité de vie.</p>\r\n\r\n<h2>Pourquoi ce programme ?</h2>\r\n<p>Un diagnostic de cancer bouleverse une vie entière. Les patients font face à de nombreux défis :</p>\r\n<ul>\r\n<li>La charge émotionnelle et psychologique du diagnostic</li>\r\n<li>La fatigue liée aux traitements (chimiothérapie, radiothérapie)</li>\r\n<li>Les difficultés financières générées par les frais de soins</li>\r\n<li>L\'isolement social et familial</li>\r\n<li>Le manque d\'information sur la maladie et les traitements</li>\r\n</ul>\r\n\r\n<h2>Ce que propose le programme</h2>\r\n\r\n<h3>Groupes de parole</h3>\r\n<p>Des rencontres régulières entre patients pour partager les expériences, briser l\'isolement et trouver du réconfort auprès de personnes qui vivent la même situation.</p>\r\n\r\n<h3>Orientation médicale</h3>\r\n<p>Notre équipe aide les patients à s\'orienter dans le système de santé haïtien : trouver le bon spécialiste, comprendre les résultats d\'examens.</p>\r\n\r\n<h3>Soutien psychologique</h3>\r\n<p>Des entretiens individuels sont proposés aux patients et à leurs proches pour traverser les moments les plus difficiles.</p>\r\n\r\n<h3>Information et éducation</h3>\r\n<p>Des fiches d\'information claires sont remises aux patients sur leur type de cancer, les traitements disponibles et les effets secondaires attendus.</p>\r\n\r\n<h2>Comment bénéficier du programme ?</h2>\r\n<p>Le programme est ouvert à tout patient atteint de cancer en Haïti, quelle que soit sa situation. Contactez le GSCC via notre formulaire en ligne ou présentez-vous lors de l\'une de nos permanences.</p>\r\n\r\n<blockquote style=\"border-left: 4px solid #D94F7A; padding-left: 16px; margin: 20px 0; color: #555; font-style: italic;\">\r\n« Vous n\'êtes pas seul(e). Le GSCC est à vos côtés, à chaque étape de votre parcours. »\r\n</blockquote>', 'Le GSCC lance un programme gratuit de soutien aux patients en traitement : groupes de parole, orientation médicale et soutien psychologique.', 'uploads/galerie/20251008_105308.jpg', 3, 1, '2025-11-01 09:00:00', 'publie', 1, 4, 1, 'soutien,accompagnement,patients,traitement,GSCC', 'Programme gratuit d\'accompagnement GSCC pour les patients atteints de cancer en Haïti.', NULL, '2025-11-01 09:00:00', '2026-03-06 19:39:05'),
(8, 'Le GSCC m\'a appris que je n\'étais pas seule — Témoignage de Marie-Claire', 'temoignage-marie-claire-gscc-soutien', '<p><em>Marie-Claire, 42 ans, a été diagnostiquée d\'un cancer du sein en 2024. Elle nous raconte comment le GSCC l\'a accompagnée tout au long de son parcours de soins.</em></p>\r\n\r\n<h2>Le choc du diagnostic</h2>\r\n<p>« Quand le médecin m\'a annoncé que j\'avais un cancer du sein, le sol s\'est dérobé sous mes pieds. J\'ai pensé que c\'était une condamnation. Je suis rentrée chez moi et je n\'ai rien dit à personne pendant trois jours. Je ne savais pas quoi faire, je ne savais pas vers qui me tourner. »</p>\r\n\r\n<p>Marie-Claire, mère de trois enfants, travaille comme commerçante à Port-au-Prince. C\'est lors d\'une journée de dépistage organisée par le GSCC dans son quartier qu\'une anomalie a été détectée.</p>\r\n\r\n<h2>La rencontre avec le GSCC</h2>\r\n<p>« C\'est la bénévole du GSCC qui m\'avait fait le dépistage qui m\'a rappelée. Elle ne m\'a pas simplement dit vous avez un problème, allez voir un médecin. Elle m\'a accompagnée, m\'a aidée à prendre rendez-vous, m\'a expliqué ce qui allait se passer. J\'ai senti que quelqu\'un prenait soin de moi. »</p>\r\n\r\n<p>Marie-Claire a ensuite été orientée vers un oncologue partenaire du GSCC. Un cancer du sein au stade II a été diagnostiqué — un stade où le traitement est encore très efficace.</p>\r\n\r\n<h2>L\'accompagnement pendant le traitement</h2>\r\n<p>« La chimiothérapie, c\'est dur. Mais le groupe de parole du GSCC m\'a beaucoup aidée. Savoir que d\'autres femmes traversent la même chose, pouvoir en parler librement sans être jugée... ça change tout. »</p>\r\n\r\n<h2>Aujourd\'hui</h2>\r\n<p>« Je suis en rémission depuis six mois. Les médecins sont optimistes. Et maintenant, je veux à mon tour aider d\'autres femmes. C\'est pour ça que je suis devenue bénévole au GSCC. »</p>\r\n\r\n<blockquote style=\"border-left: 4px solid #D94F7A; padding-left: 16px; margin: 20px 0; color: #555; font-style: italic;\">\r\n« Si je n\'avais pas participé à cette journée de dépistage du GSCC, je n\'aurais peut-être rien su avant qu\'il soit trop tard. Ces gens m\'ont sauvé la vie. »\r\n</blockquote>', 'Marie-Claire, 42 ans, raconte comment le GSCC l\'a accompagnée depuis la détection de son cancer du sein jusqu\'à sa rémission.', 'uploads/galerie/20251008_105348.jpg', 4, 1, '2025-11-15 09:00:00', 'publie', 1, 4, 0, 'témoignage,cancer du sein,rémission,soutien,GSCC,espoir', 'Témoignage de Marie-Claire : le GSCC l\'a accompagnée après son diagnostic de cancer du sein.', NULL, '2025-11-15 09:00:00', '2026-03-07 19:17:15'),
(9, 'Octobre rose 2025 : le GSCC mobilise ses équipes partout en Haïti', 'octobre-rose-2025-gscc-mobilisation', '<p>Chaque année, le mois d\'octobre est dédié à la sensibilisation au cancer du sein à travers le monde. En Haïti, le GSCC s\'engage pleinement dans cette mobilisation avec une série d\'activités gratuites et ouvertes à tous.</p>\r\n\r\n<h2>Pourquoi octobre rose est important pour Haïti</h2>\r\n<p>Le cancer du sein est le cancer le plus fréquent chez la femme haïtienne. Chaque année, des centaines de femmes reçoivent ce diagnostic, souvent à un stade avancé faute de dépistage précoce. Pourtant, lorsqu\'il est détecté tôt, le taux de survie à 5 ans dépasse 90%.</p>\r\n\r\n<h2>Les activités du GSCC en octobre 2025</h2>\r\n\r\n<h3>8 octobre — Journée de sensibilisation à Port-au-Prince</h3>\r\n<p>Rassemblement de l\'équipe GSCC, marche symbolique et conférence médicale animée par le Dr Paul Jr Fontilus sur le cancer du col de l\'utérus. Plus de 30 participants ont assisté à cette journée.</p>\r\n\r\n<h3>Dépistages gratuits</h3>\r\n<p>Tout au long du mois, le GSCC a proposé des consultations gynécologiques et des examens de dépistage gratuits en partenariat avec des cliniques et des médecins bénévoles.</p>\r\n\r\n<h3>Sensibilisation dans les écoles</h3>\r\n<p>Des équipes de bénévoles se sont déplacées dans plusieurs établissements scolaires pour sensibiliser les jeunes à la prévention du cancer et à la vaccination HPV.</p>\r\n\r\n<h3>Campagne sur les réseaux sociaux</h3>\r\n<p>Le GSCC a lancé une campagne digitale sous le hashtag <strong>#KansèPaKaTann</strong> avec des infographies, des témoignages et des conseils de professionnels de santé.</p>\r\n\r\n<h2>Rejoignez le mouvement</h2>\r\n<p>Ensemble, nous pouvons changer le cours de cette maladie en Haïti. Chaque action compte, chaque information partagée peut sauver une vie.</p>', 'Le GSCC retrace ses actions du mois d\'octobre rose 2025 : dépistages gratuits, conférences et sensibilisation partout en Haïti.', 'uploads/galerie/20251008_103754.jpg', 2, 1, '2025-10-28 10:00:00', 'publie', 1, 3, 0, 'octobre rose,cancer du sein,événement,sensibilisation,2025', 'Octobre rose 2025 : le GSCC mobilise ses équipes en Haïti avec dépistages et sensibilisation.', NULL, '2025-10-28 10:00:00', '2026-03-06 19:38:32');

-- --------------------------------------------------------

--
-- Table structure for table `campagnes_projets`
--

CREATE TABLE `campagnes_projets` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `type` enum('campagne','projet') NOT NULL,
  `statut` enum('a_venir','en_cours','termine') DEFAULT 'a_venir',
  `description` text DEFAULT NULL,
  `contenu` longtext DEFAULT NULL,
  `image_couverture` varchar(255) DEFAULT NULL,
  `galerie` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`galerie`)),
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `objectif` varchar(255) DEFAULT NULL,
  `objectif_montant` decimal(10,2) DEFAULT NULL,
  `montant_collecte` decimal(10,2) DEFAULT 0.00,
  `progression` int(11) DEFAULT 0,
  `partenaires` text DEFAULT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents`)),
  `created_by` int(11) DEFAULT NULL,
  `vue_compteur` int(11) DEFAULT 0,
  `est_actif` tinyint(1) DEFAULT 1,
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_modification` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `campagnes_projets`
--

INSERT INTO `campagnes_projets` (`id`, `titre`, `slug`, `type`, `statut`, `description`, `contenu`, `image_couverture`, `galerie`, `date_debut`, `date_fin`, `objectif`, `objectif_montant`, `montant_collecte`, `progression`, `partenaires`, `lieu`, `contact`, `documents`, `created_by`, `vue_compteur`, `est_actif`, `date_creation`, `date_modification`) VALUES
(1, 'Octobre Rose 2024', 'octobre-rose-2024', 'campagne', 'en_cours', 'Campagne de sensibilisation au dépistage du cancer du sein. Mobilisation nationale dans tout le pays.', '<p>Octobre Rose est l\'occasion de rappeler l\'importance du dépistage précoce du cancer du sein. Tout au long du mois, le GSCC organise des activités de sensibilisation, des consultations gratuites et des marches de soutien.</p>\r\n    <h3>Au programme :</h3>\r\n    <ul>\r\n        <li>Dépistages gratuits dans les centres partenaires</li>\r\n        <li>Marche rose le 20 octobre</li>\r\n        <li>Conférences sur l\'auto-palpation</li>\r\n        <li>Témoignages de patientes</li>\r\n    </ul>', 'uploads/galerie/Sensibilisation-EHH-2.jpg', '[\"https://picsum.photos/800/600?random=11\",\"https://picsum.photos/800/600?random=12\",\"https://picsum.photos/800/600?random=13\",\"https://picsum.photos/800/600?random=14\"]', '2024-10-01', '2024-10-31', 'Sensibiliser 10 000 femmes', NULL, 0.00, 65, 'Ministère de la Santé, Fondation pour la Vie', 'National', 'campagnes@gscc.org', NULL, NULL, 0, 1, '2026-03-01 06:49:56', '2026-03-07 19:35:07'),
(2, 'Construction du Centre d\'Accueil', 'centre-accueil', 'projet', 'en_cours', 'Construction d\'un centre d\'accueil pour les patients et leurs familles à Port-au-Prince.', '<p>Le futur centre d\'accueil du GSCC offrira un hébergement temporaire aux patients venant de provinces pour leurs traitements, ainsi qu\'un espace de soutien psychologique et d\'information.</p>\r\n    <h3>Le projet comprend :</h3>\r\n    <ul>\r\n        <li>10 chambres pour l\'hébergement</li>\r\n        <li>Une salle de consultation</li>\r\n        <li>Un espace de documentation</li>\r\n        <li>Un jardin thérapeutique</li>\r\n    </ul>', 'uploads/galerie/Sensibilisation-3.jpg', '[\"https://picsum.photos/800/600?random=21\",\"https://picsum.photos/800/600?random=22\",\"https://picsum.photos/800/600?random=23\"]', '2024-06-01', '2025-12-31', 'Collecter 5 millions G', NULL, 0.00, 35, 'Architecture sans Frontières', 'Port-au-Prince', 'projets@gscc.org', NULL, NULL, 0, 1, '2026-03-01 06:49:56', '2026-03-07 19:36:41'),
(3, 'Dépistage Mobile en Province', 'depistage-mobile', 'projet', 'termine', 'Unité mobile de dépistage dans les zones rurales pour détecter précocement les cancers.', '<p>Grâce à une unité mobile équipée, nous avons parcouru 5 départements pour offrir des consultations de dépistage gratuites dans les zones reculées.</p>\r\n    <h3>Résultats :</h3>\r\n    <ul>\r\n        <li>2 500 personnes consultées</li>\r\n        <li>120 cas suspects détectés</li>\r\n        <li>15 diagnostics précoces</li>\r\n    </ul>', 'uploads/galerie/Sensibilisation-2.jpg', '[\"https://picsum.photos/800/600?random=31\",\"https://picsum.photos/800/600?random=32\",\"https://picsum.photos/800/600?random=33\",\"https://picsum.photos/800/600?random=34\"]', '2024-01-15', '2024-03-30', 'Toucher 2 000 personnes', NULL, 0.00, 100, 'Médecins du Monde', 'Nord, Sud, Artibonite', NULL, NULL, NULL, 0, 1, '2026-03-01 06:49:56', '2026-03-07 19:37:33'),
(4, 'Formation des Personnels de Santé', 'formation-personnels', 'projet', 'en_cours', 'Programme de formation des infirmiers et médecins sur la prise en charge des patients atteints de cancer.', '<p>Ce programme vise à renforcer les compétences des professionnels de santé dans l\'accompagnement des patients et la détection précoce.</p>', 'uploads/galerie/MEM_9302.JPG', '[\"https://picsum.photos/800/600?random=41\",\"https://picsum.photos/800/600?random=42\"]', '2024-09-01', '2025-09-01', 'Former 200 professionnels', NULL, 0.00, 25, 'Faculté de Médecine, OMS', 'Port-au-Prince, Cap-Haïtien', 'formations@gscc.org', NULL, NULL, 0, 1, '2026-03-01 06:49:56', '2026-03-07 19:39:17'),
(5, 'Movember : Santé Masculine', 'movember-2024', 'campagne', 'a_venir', 'Campagne de sensibilisation aux cancers masculins (prostate, testicules) durant le mois de novembre.', '<p>Movember est le mois dédié à la santé masculine. Le GSCC organise des consultations gratuites et des ateliers d\'information.</p>', 'uploads/galerie/WhatsApp Image 2025-10-09 at 16.10.22 (3).jpeg', NULL, '2024-11-01', '2024-11-30', 'Sensibiliser 5 000 hommes', NULL, 0.00, 0, 'Association des Urologues', 'National', 'campagnes@gscc.org', NULL, NULL, 0, 1, '2026-03-01 06:49:56', '2026-03-07 19:41:35'),
(6, 'Aide aux Patients Démunis', 'aide-patients-demunis', 'projet', 'en_cours', 'Programme d\'aide financière et matérielle aux patients à faibles revenus pour leurs traitements.', '<p>Ce projet permet de prendre en charge partiellement ou totalement les frais médicaux des patients les plus démunis.</p>', 'uploads/galerie/WhatsApp Image 2025-10-09 at 16.10.17.jpeg', '[\"https://picsum.photos/800/600?random=61\",\"https://picsum.photos/800/600?random=62\",\"https://picsum.photos/800/600?random=63\"]', '2024-01-01', '2024-12-31', 'Aider 100 patients', NULL, 0.00, 72, 'Donateurs privés, Fondation de France', 'National', 'aide@gscc.org', NULL, NULL, 0, 1, '2026-03-01 06:49:56', '2026-03-07 19:43:39');

-- --------------------------------------------------------

--
-- Table structure for table `candidatures_benevoles`
--

CREATE TABLE `candidatures_benevoles` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(180) NOT NULL,
  `telephone` varchar(30) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `profession` varchar(150) DEFAULT NULL,
  `disponibilites` varchar(255) DEFAULT NULL,
  `competences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`competences`)),
  `motivations` text NOT NULL,
  `statut` enum('en_attente','contacte','accepte','refuse') NOT NULL DEFAULT 'en_attente',
  `notes_admin` text DEFAULT NULL,
  `date_candidature` datetime NOT NULL DEFAULT current_timestamp(),
  `date_traitement` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `candidatures_benevoles`
--

INSERT INTO `candidatures_benevoles` (`id`, `nom`, `prenom`, `email`, `telephone`, `date_naissance`, `profession`, `disponibilites`, `competences`, `motivations`, `statut`, `notes_admin`, `date_candidature`, `date_traitement`) VALUES
(1, 'Roldy', 'Raphael', 'roldy.raphael@gmail.com', '+509 33 93 9333', '2002-09-25', '', 'week-ends', '[\"Communication\"]', 'J\'aime cet initiative, je veux rendre des services', 'en_attente', NULL, '2026-03-07 14:03:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('blog','galerie','evenement','projet') DEFAULT 'blog',
  `parent_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `nom`, `slug`, `description`, `type`, `parent_id`, `image`, `created_at`) VALUES
(1, 'Prévention', 'prevention', NULL, 'blog', NULL, NULL, '2026-02-28 07:22:26'),
(2, 'Événements', 'evenements', NULL, 'blog', NULL, NULL, '2026-02-28 07:22:26'),
(3, 'Projets', 'projets', NULL, 'blog', NULL, NULL, '2026-02-28 07:22:26'),
(4, 'Témoignages', 'temoignages', NULL, 'blog', NULL, NULL, '2026-02-28 07:22:26');

-- --------------------------------------------------------

--
-- Table structure for table `demandes_aide`
--

CREATE TABLE `demandes_aide` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `type_aide` enum('financiere','medicale','psychologique','accompagnement') NOT NULL,
  `description_demande` text NOT NULL,
  `documents_justificatifs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents_justificatifs`)),
  `statut` enum('soumis','en_cours','approuve','refuse') DEFAULT 'soumis',
  `montant_demande` decimal(10,2) DEFAULT NULL,
  `montant_accorde` decimal(10,2) DEFAULT NULL,
  `date_soumission` datetime DEFAULT current_timestamp(),
  `date_traitement` datetime DEFAULT NULL,
  `traite_par` int(11) DEFAULT NULL,
  `commentaires_admin` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demandes_aide`
--

INSERT INTO `demandes_aide` (`id`, `utilisateur_id`, `type_aide`, `description_demande`, `documents_justificatifs`, `statut`, `montant_demande`, `montant_accorde`, `date_soumission`, `date_traitement`, `traite_par`, `commentaires_admin`) VALUES
(1, NULL, 'financiere', 'Demande d\'aide pour traitement', NULL, 'en_cours', NULL, NULL, '2026-02-23 07:22:26', NULL, NULL, NULL),
(2, NULL, 'psychologique', 'Besoin de soutien psychologique', NULL, 'soumis', NULL, NULL, '2026-02-26 07:22:26', NULL, NULL, NULL),
(3, 3, 'medicale', 'la moitié des cancers du sein apparaissent chez des femmes qui ne présentent aucun facteur de risque spécifique autre que le sexe et l’âge. Dans 157 pays sur 185, le cancer du sein était la première cause de cancer chez les femmes en 2022.', '[\"doc_69ac497e8f3375.13963219.png\"]', 'soumis', NULL, NULL, '2026-03-07 10:51:26', NULL, NULL, NULL),
(4, 3, 'accompagnement', 'la moitié des cancers du sein apparaissent chez des femmes qui ne présentent aucun facteur de risque spécifique autre que le sexe et l’âge. Dans 157 pays sur 185, le cancer du sein était la première cause de cancer chez les femmes en 2022.', '[\"doc_69ac49db938609.77845201.pdf\"]', 'soumis', NULL, NULL, '2026-03-07 10:52:59', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dons`
--

CREATE TABLE `dons` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `nom_donateur` varchar(100) DEFAULT NULL,
  `email_donateur` varchar(255) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `montant` decimal(10,2) NOT NULL,
  `type_don` enum('ponctuel','mensuel','annuel') DEFAULT 'ponctuel',
  `mode_paiement` enum('paypal','stripe','virement','especes','cheque') NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `statut` enum('en_attente','complete','echoue','rembourse') DEFAULT 'en_attente',
  `date_don` datetime DEFAULT current_timestamp(),
  `recu_genere` tinyint(1) DEFAULT 0,
  `newsletter` tinyint(1) DEFAULT 0,
  `commentaire` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dons`
--

INSERT INTO `dons` (`id`, `utilisateur_id`, `nom_donateur`, `email_donateur`, `telephone`, `montant`, `type_don`, `mode_paiement`, `transaction_id`, `statut`, `date_don`, `recu_genere`, `newsletter`, `commentaire`) VALUES
(1, NULL, 'Johnsley BELVAL', 'johnsleybelval@gmail.com', '43567898', 2500.00, 'ponctuel', 'paypal', NULL, 'en_attente', '2026-02-28 08:07:48', 0, 1, 'gggggggg'),
(6, NULL, 'Whitchy AUGUSTIN', 'whitchy.augustin@gmail.com', '+50943567898', 100.00, 'ponctuel', 'paypal', NULL, 'en_attente', '2026-03-06 20:20:28', 0, 1, 'Pour le bien de DIeu'),
(7, NULL, 'Merlene Aime', 'merlene.aime@gmail.com', '+509 43 56 7890', 100.00, 'ponctuel', 'paypal', NULL, 'en_attente', '2026-03-06 21:36:07', 0, 1, 'Pour l&#039;aide de Dieu'),
(8, 3, 'ALCERO Taylor', 'alcero.taylor@gmail.com', '+509 43 56 7890', 250.00, 'ponctuel', 'paypal', NULL, 'en_attente', '2026-03-07 06:57:02', 0, 1, 'pour l&#039;amour de Dieu'),
(9, 7, 'Karl Joseph', 'karl.josehp@gmail.com', '+509 34 67 1011', 50.00, 'ponctuel', 'paypal', NULL, 'en_attente', '2026-03-07 14:54:41', 0, 1, 'pour aider les personnes malades');

-- --------------------------------------------------------

--
-- Table structure for table `equipe`
--

CREATE TABLE `equipe` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `fonction` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `ordre` int(11) DEFAULT 0,
  `est_actif` tinyint(1) DEFAULT 1,
  `reseaux_sociaux` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`reseaux_sociaux`)),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equipe`
--

INSERT INTO `equipe` (`id`, `nom`, `prenom`, `fonction`, `bio`, `photo`, `email`, `telephone`, `ordre`, `est_actif`, `reseaux_sociaux`, `created_at`) VALUES
(1, 'Jean-Baptiste', 'Marie', 'Présidente Fondatrice', 'Oncologue avec plus de 20 ans d\'expérience, dédiée à la lutte contre le cancer en Haïti.', 'images/equipe/marie-jean-baptiste.jpg', NULL, NULL, 1, 1, NULL, '2026-02-28 07:22:26'),
(2, 'Alexandre', 'Pierre Richard', 'Coordinateur des Programmes', 'Expert en gestion de projets humanitaires, coordonne les actions sur le terrain.', 'images/equipe/pierre-richard-alexandre.jpg', NULL, NULL, 2, 1, NULL, '2026-02-28 07:22:26'),
(3, 'Charles', 'Rose-Merline', 'Psychologue Clinicienne', 'Spécialiste en accompagnement psychologique des patients et familles.', 'images/equipe/rose-merline-charles.jpg', NULL, NULL, 3, 1, NULL, '2026-02-28 07:22:26'),
(4, 'Michel', 'Jean-Claude', 'Responsable Administratif', 'Gère les aspects administratifs et financiers de l\'organisation.', 'images/equipe/jean-claude-michel.jpg', NULL, NULL, 4, 1, NULL, '2026-02-28 07:22:26'),
(5, 'Dupont', 'Marc-André', 'Responsable Communication', 'Chargé de la communication externe et des réseaux sociaux du GSCC. Coordonne les campagnes de sensibilisation au niveau national.', 'images/equipe/marc-andre-dupont.jpg', NULL, NULL, 5, 1, NULL, '2026-03-08 10:35:46'),
(6, 'Pierre', 'Sophonie', 'Infirmière Coordinatrice', 'Infirmière spécialisée en oncologie, assure le suivi médical des patients bénéficiaires et la coordination avec les équipes soignantes.', 'images/equipe/sophonie-pierre.jpg', NULL, NULL, 6, 1, NULL, '2026-03-08 10:35:46'),
(7, 'Louis', 'Kerby', 'Responsable des Levées de Fonds', 'Expert en mobilisation de ressources et partenariats. Pilote les campagnes de financement et les relations avec les donateurs.', 'images/equipe/kerby-louis.jpg', NULL, NULL, 7, 1, NULL, '2026-03-08 10:35:46'),
(8, 'Clerveau', 'Nadège', 'Chargée de Formation', 'Spécialiste en éducation à la santé, conçoit et anime les programmes de formation pour les professionnels et le grand public.', 'images/equipe/nadege-clerveau.jpg', NULL, NULL, 8, 1, NULL, '2026-03-08 10:35:46');

-- --------------------------------------------------------

--
-- Table structure for table `evenements`
--

CREATE TABLE `evenements` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `contenu` longtext DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime DEFAULT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `capacite_max` int(11) DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT 0.00,
  `statut` enum('a_venir','en_cours','termine','annule') DEFAULT 'a_venir',
  `created_by` int(11) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `vue_compteur` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `evenements`
--

INSERT INTO `evenements` (`id`, `titre`, `slug`, `description`, `contenu`, `image`, `date_debut`, `date_fin`, `lieu`, `adresse`, `capacite_max`, `prix`, `statut`, `created_by`, `date_creation`, `vue_compteur`) VALUES
(1, 'Marche contre le cancer', 'marche-contre-cancer-2024', 'Grande marche de sensibilisation', '<p>Rejoignez-nous pour cette marche solidaire</p>', NULL, '2026-03-15 07:22:26', '2026-03-15 07:22:26', 'Port-au-Prince', NULL, NULL, 0.00, 'a_venir', NULL, '2026-02-28 07:22:26', 0),
(2, 'Conférence sur la prévention', 'conference-prevention-2024', 'Conférence sur l\'importance du dépistage', '<p>Des experts vous informent sur la prévention</p>', NULL, '2026-03-30 07:22:26', '2026-03-30 07:22:26', 'Pétion-Ville', NULL, NULL, 0.00, 'a_venir', NULL, '2026-02-28 07:22:26', 0),
(3, 'Collecte de fonds', 'collecte-fonds-2024', 'Journée de collecte de fonds', '<p>Venez nombreux soutenir notre cause</p>', NULL, '2026-04-14 07:22:26', '2026-04-14 07:22:26', 'Delmas', NULL, NULL, 0.00, 'a_venir', NULL, '2026-02-28 07:22:26', 0);

-- --------------------------------------------------------

--
-- Table structure for table `forum_categories`
--

CREATE TABLE `forum_categories` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ordre` int(11) DEFAULT 0,
  `est_actif` tinyint(1) DEFAULT 1,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forum_categories`
--

INSERT INTO `forum_categories` (`id`, `nom`, `description`, `ordre`, `est_actif`, `date_creation`) VALUES
(1, 'Présentations', 'Nouveaux membres : présentez-vous !', 1, 1, '2026-02-28 07:22:26'),
(2, 'Soutien et entraide', 'Partagez vos expériences et soutenez-vous mutuellement', 2, 1, '2026-02-28 07:22:26'),
(3, 'Questions médicales', 'Questions sur les traitements et la santé', 3, 1, '2026-02-28 07:22:26'),
(4, 'Actualités et recherche', 'Discussions sur les avancées médicales', 4, 1, '2026-02-28 07:22:26'),
(5, 'Vie de l\'association', 'Informations sur les activités du GSCC', 5, 1, '2026-02-28 07:22:26');

-- --------------------------------------------------------

--
-- Table structure for table `forum_reponses`
--

CREATE TABLE `forum_reponses` (
  `id` int(11) NOT NULL,
  `sujet_id` int(11) DEFAULT NULL,
  `contenu` text NOT NULL,
  `auteur_id` int(11) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `est_solution` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forum_reponses`
--

INSERT INTO `forum_reponses` (`id`, `sujet_id`, `contenu`, `auteur_id`, `date_creation`, `est_solution`) VALUES
(1, 1, 'Bienvenue parmi nous !', NULL, '2026-02-24 07:22:26', 0),
(2, 2, 'La méditation peut aider...', NULL, '2026-02-26 07:22:26', 0),
(4, 4, 'Iran is bounded to the north by Azerbaijan, Armenia, Turkmenistan, and the Caspian Sea, to the east by Pakistan and Afghanistan, to the south by the Persian Gulf and the Gulf of Oman, and to the west by Turkey and Iraq. Iran also controls about a dozen islands in the Persian Gulf. About one-third of its 4,770-mile (7,680-km) boundary is seacoast.', 3, '2026-03-03 21:47:14', 0),
(5, 4, 'Le cancer du sein est la forme de cancer féminin la plus répandue. Globalement, le risque pour une femme canadienne de présenter un cancer du sein pendant sa vie est de 1 sur 8. La probabilité de cancer du sein augmente de façon spectaculaire avec l\'âge. À 30 ans, le risque de contracter la maladie est de 1 sur 209, à l\'âge de 50 ans, ce risque est de 1 sur 42 et à 70 ans, il est de 1 sur 25.', 2, '2026-03-03 21:51:02', 0);

-- --------------------------------------------------------

--
-- Table structure for table `forum_sujets`
--

CREATE TABLE `forum_sujets` (
  `id` int(11) NOT NULL,
  `categorie_id` int(11) DEFAULT NULL,
  `titre` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `auteur_id` int(11) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_derniere_reponse` datetime DEFAULT NULL,
  `vue_compteur` int(11) DEFAULT 0,
  `est_resolu` tinyint(1) DEFAULT 0,
  `est_epingle` tinyint(1) DEFAULT 0,
  `est_ferme` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forum_sujets`
--

INSERT INTO `forum_sujets` (`id`, `categorie_id`, `titre`, `contenu`, `auteur_id`, `date_creation`, `date_derniere_reponse`, `vue_compteur`, `est_resolu`, `est_epingle`, `est_ferme`) VALUES
(1, 1, 'Bonjour à tous !', 'Je suis nouveau ici et je souhaitais me présenter...', NULL, '2026-02-23 07:22:26', NULL, 4, 0, 0, 0),
(2, 2, 'Comment gérer l\'anxiété ?', 'Des conseils pour gérer le stress lié à la maladie ?', NULL, '2026-02-25 07:22:26', NULL, 1, 0, 0, 0),
(3, 5, 'Prochaine réunion mensuelle', 'Rendez-vous le 15 pour notre réunion mensuelle', NULL, '2026-02-27 07:22:26', NULL, 0, 0, 1, 0),
(4, 4, 'Cancer poumon', 'c\'est tres dificile de combattre', 3, '2026-03-03 21:35:50', NULL, 5, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `galerie`
--

CREATE TABLE `galerie` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `type` enum('photo','video') DEFAULT 'photo',
  `url_fichier` varchar(255) NOT NULL,
  `url_thumbnail` varchar(255) DEFAULT NULL,
  `categorie_id` int(11) DEFAULT NULL,
  `date_upload` datetime DEFAULT current_timestamp(),
  `uploaded_by` int(11) DEFAULT NULL,
  `est_public` tinyint(1) DEFAULT 1,
  `ordre` int(11) DEFAULT 0,
  `vue_compteur` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `galerie`
--

INSERT INTO `galerie` (`id`, `titre`, `description`, `type`, `url_fichier`, `url_thumbnail`, `categorie_id`, `date_upload`, `uploaded_by`, `est_public`, `ordre`, `vue_compteur`) VALUES
(1, 'Marche de sensibilisation contre le cancer — Photo 1', 'L\'équipe GSCC réunie sur les escaliers lors de la marche de sensibilisation du 8 octobre 2025.', 'photo', 'uploads/galerie/20251008_093809.jpg', 'uploads/galerie/Activités-Sportives.jpg', NULL, '2025-10-08 09:38:09', NULL, 1, 1, 0),
(2, 'Marche de sensibilisation contre le cancer — Photo 2', 'Les membres de l\'équipe GSCC en t-shirts officiels lors de la marche du 8 octobre 2025.', 'photo', 'uploads/galerie/20251008_105348.jpg', 'uploads/galerie/MEM_9268.JPG', NULL, '2025-10-08 09:38:12', NULL, 1, 2, 0),
(3, 'Marche de sensibilisation contre le cancer — Photo 3', 'Photo de groupe de l\'équipe GSCC lors de la marche de sensibilisation, octobre 2025.', 'photo', 'uploads/galerie/20251008_093818.jpg', 'uploads/galerie/20251008_093818.jpg', NULL, '2025-10-08 09:38:18', NULL, 1, 3, 0),
(4, 'Conférence de sensibilisation — Présentation', 'Présentation sur le dépistage du cancer du sein lors de la conférence GSCC du 8 octobre 2025.', 'photo', 'uploads/galerie/20251008_103754.jpg', 'uploads/galerie/20251008_103754.jpg', NULL, '2025-10-08 10:37:54', NULL, 1, 4, 0),
(5, 'Conférence de sensibilisation — Intervention', 'Intervention d\'une membre de l\'équipe GSCC devant les participants à la conférence d\'octobre 2025.', 'photo', 'uploads/galerie/20251008_103755.jpg', 'uploads/galerie/20251008_103755.jpg', NULL, '2025-10-08 10:37:55', NULL, 1, 5, 0),
(6, 'Conférence de sensibilisation — Échanges', 'Moments d\'échanges et de questions-réponses lors de la conférence GSCC sur le cancer, octobre 2025.', 'photo', 'uploads/galerie/20251008_105014.jpg', 'uploads/galerie/20251008_105014.jpg', NULL, '2025-10-08 10:50:14', NULL, 1, 6, 0),
(7, 'Conférence de sensibilisation — Présentation médicale', 'La présidente de GSCC présente les données médicales sur le cancer du sein aux participants, octobre 2025.', 'photo', 'uploads/galerie/20251008_105308.jpg', 'uploads/galerie/20251008_105308.jpg', NULL, '2025-10-08 10:53:08', NULL, 1, 7, 0),
(8, 'Le GSCC — Son Histoire et sa mission en Haïti', 'Découvrez l\'histoire du Groupe de Support Contre le Cancer et sa mission en Haïti.', 'video', 'zGajkhajg38', NULL, NULL, '2026-03-06 18:25:23', NULL, 1, 1, 0),
(9, 'Le GSCC | Son Histoire et sa mission en Haïti', 'Présentation complète du GSCC, de ses origines à ses actions sur le terrain en Haïti.', 'video', '2za5WfYL3gc', NULL, NULL, '2026-03-06 18:25:23', NULL, 1, 2, 0),
(10, 'Tout Sa Nou Dwe Konnen sou Kansè Kòl Matris — Dr Paul Jr Fontilus', 'Tout ce qu\'il faut savoir sur le cancer du col de l\'utérus, présenté par le Dr Paul Jr Fontilus.', 'video', 'etCB9T1qexM', NULL, NULL, '2026-03-06 18:25:23', NULL, 1, 3, 0),
(11, 'Séance de dépistage communautaire', 'Séance de dépistage gratuit organisée dans le quartier de Delmas, Port-au-Prince.', 'photo', 'uploads/galerie/MEM_9296.jpg', 'uploads/galerie/MEM_9296.jpg', NULL, '2026-03-08 12:25:37', NULL, 1, 1, 0),
(12, 'Atelier de sensibilisation', 'Atelier de sensibilisation au cancer du sein animé par notre équipe médicale bénévole.', 'photo', 'uploads/galerie/Sensibilisation-EHH-2.jpg', 'uploads/galerie/Sensibilisation-EHH-2.jpg', NULL, '2026-03-08 12:25:37', NULL, 1, 2, 0),
(13, 'Remise de kits médicaux', 'Distribution de kits médicaux aux patients bénéficiaires lors de notre journée portes ouvertes.', 'photo', 'uploads/galerie/WhatsApp Image 2025-10-09 at 16.10.19 (2).jpeg', 'uploads/galerie/WhatsApp Image 2025-10-09 at 16.10.19 (2).jpeg', NULL, '2026-03-08 12:25:37', NULL, 1, 3, 0),
(14, 'Formation des bénévoles', 'Session de formation pour les nouveaux bénévoles sur l\'accompagnement psychologique des patients.', 'photo', 'uploads/galerie/WhatsApp Image 2025-10-09 at 16.10.17.jpeg', 'uploads/galerie/WhatsApp Image 2025-10-09 at 16.10.17.jpeg', NULL, '2026-03-08 12:25:37', NULL, 1, 4, 0),
(15, 'Marche contre le cancer', 'Marche de sensibilisation organisée au Champ de Mars avec plus de 300 participants.', 'photo', 'uploads/galerie/TEam-GSCC.jpg', 'uploads/galerie/TEam-GSCC.jpg', NULL, '2026-03-08 12:25:37', NULL, 1, 5, 0),
(16, 'Conférence médicale annuelle', 'Conférence annuelle réunissant les oncologues et professionnels de santé d\'Haïti.', 'photo', 'uploads/galerie/IMG_3926.jpg', 'uploads/galerie/IMG_3926.jpg', NULL, '2026-03-08 12:25:37', NULL, 1, 6, 0),
(17, 'Groupe de soutien patients', 'Réunion mensuelle du groupe de soutien pour les patients et leurs proches au centre GSCC.', 'photo', 'uploads/galerie/IMG_3910.jpg', 'uploads/galerie/IMG_3910.jpg', NULL, '2026-03-08 12:25:37', NULL, 1, 7, 0),
(18, 'Levée de fonds gala', 'Soirée de gala organisée pour la levée de fonds annuelle du GSCC à l\'Hôtel Montana.', 'photo', 'uploads/galerie/Awareness-campaign-at-Marie-Louise-Trichet-.jpg', 'uploads/galerie/Awareness-campaign-at-Marie-Louise-Trichet-.jpg', NULL, '2026-03-08 12:25:37', NULL, 1, 8, 0),
(19, 'Visite centre oncologique', 'Visite du centre oncologique de l\'Hôpital de l\'Université d\'État d\'Haïti avec notre équipe.', 'photo', 'uploads/galerie/volley-for-a-cause.jpg', 'uploads/galerie/volley-for-a-cause.jpg', NULL, '2026-03-08 12:25:37', NULL, 1, 9, 0),
(20, 'Campagne octobre rose', 'Actions de terrain menées durant le mois d\'octobre rose dans plusieurs villes d\'Haïti.', 'photo', 'uploads/galerie/MEM_9184.jpg', 'uploads/galerie/MEM_9184.jpg', NULL, '2026-03-08 12:25:37', NULL, 1, 10, 0),
(21, 'Remise de diplômes formation', 'Cérémonie de remise de certificats aux participants de notre programme de formation en oncologie.', 'photo', 'uploads/galerie/Shooting-2.jpg', 'uploads/galerie/Shooting-2.jpg', NULL, '2026-03-08 12:25:37', NULL, 1, 11, 0);

-- --------------------------------------------------------

--
-- Table structure for table `messages_contact`
--

CREATE TABLE `messages_contact` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `sujet` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `lu` tinyint(1) DEFAULT 0,
  `date_envoi` datetime DEFAULT current_timestamp(),
  `repondu` tinyint(1) DEFAULT 0,
  `date_reponse` datetime DEFAULT NULL,
  `reponse_par` int(11) DEFAULT NULL,
  `notes_privees` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages_contact`
--

INSERT INTO `messages_contact` (`id`, `nom`, `email`, `telephone`, `sujet`, `message`, `lu`, `date_envoi`, `repondu`, `date_reponse`, `reponse_par`, `notes_privees`) VALUES
(1, 'Test', 'test@example.com', NULL, NULL, 'Ceci est un message de test', 1, '2026-02-26 07:22:26', 0, NULL, NULL, NULL),
(2, 'Whitchy AUGUSTIN', 'whitchy.augustin@gmail.com', '43567898', 'Aide', 'j&#039;ai besoin de l&#039;aide', 0, '2026-03-06 21:04:30', 0, NULL, NULL, NULL),
(3, 'Falandy JEAN', 'jean.falandy@gmail.com', '0123456700', '', 'c\'est une bonne initiative !!!', 0, '2026-03-07 06:20:51', 0, NULL, NULL, NULL),
(4, 'Falandy JEAN', 'jean.falandy@gmail.com', '0123456700', '', 'c\'est une bonne initiative !!!', 0, '2026-03-07 06:24:37', 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_abonnes`
--

CREATE TABLE `newsletter_abonnes` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `statut` enum('actif','desabonne') DEFAULT 'actif',
  `date_inscription` datetime DEFAULT current_timestamp(),
  `token_desabonnement` varchar(255) DEFAULT NULL,
  `derniere_envoi` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `newsletter_abonnes`
--

INSERT INTO `newsletter_abonnes` (`id`, `email`, `nom`, `statut`, `date_inscription`, `token_desabonnement`, `derniere_envoi`) VALUES
(4, 'whitchy.augustin@gmail.com', 'Whitchy AUGUSTIN', 'actif', '2026-03-03 23:31:49', '6c45de2d59eec91f43cbb9b0257cf0813e28533e2becdb4c4993d445a79547af', NULL),
(5, 'belvaljohnsley@gmail.com', 'Johnsley BELVAL', 'actif', '2026-03-03 23:41:47', '77b947733d82b688dcfc4de89857067999aea95292436bbefb3ca4e36e7e813c', NULL),
(6, 'jobelval@uhelp.net', NULL, 'actif', '2026-03-06 20:58:10', 'c76c7ac3f6d7a7ad74068ddad875ba9abea95759ddc66384e4cbd3100eddf63c', NULL),
(7, 'it.intern@bijgroup.org', 'Withnay Pierre', 'actif', '2026-03-06 21:23:50', 'bc5059d91a8e409fb45a4d117fb9e62c84390e895a3c8a95f4b5ebfde3e49293', NULL),
(8, 'merlene.aime@gmail.com', 'Merlene Aime', 'actif', '2026-03-06 21:27:33', '69d22a661648c464417828cd8a967d492fd284ff9b0663ca98e474830bd7812b', NULL),
(9, 'fredy.ponturat@gmail.com', 'Fredy Ponturat', 'actif', '2026-03-06 21:38:19', '121d2e3a76e8437443d25df244de27420b8324cb3c6a203878253bcb8e480be3', NULL),
(10, 'allen.decleus@gmail.com', 'Allen Pierre', 'actif', '2026-03-06 21:46:12', 'b3f5ff5ba329d138a11cb212ad4d9a34508a2287d287ed3c8b28a1082437d191', NULL),
(11, 'pierre.jean@gmail.com', 'Pierre Jean', 'actif', '2026-03-06 21:50:01', '2e1438c8ab73a858687bb93b5b0e4813aef8def2c9136bef530ee206e070cf76', NULL),
(12, 'kerrye.pierre@gmail.com', 'Kerry Pierre', 'actif', '2026-03-06 21:55:07', 'c9aca1590221afb17613129e6fc6f6a77849b8e0cbb188cfa92e48b030fcc046', NULL),
(13, 'oscar.nehemie@gmail.com', 'Oscar Nehemie', 'actif', '2026-03-06 22:01:15', '444f418eea0c9026b19f39ea8f9e04080995f9c618187e20b8ba102887af2b5d', NULL),
(14, 'jonathan.georges@gmail.com', 'Jonathan Georges', 'actif', '2026-03-06 22:08:20', 'd04bae334b1076c63bd45c37c6d5b87f64de8e6dccc2b73ddd4a6b5afa7dcd48', NULL),
(15, 'guy.calixte@gmail.com', 'Guy Calixte', 'actif', '2026-03-06 22:11:21', 'd7dc69340463f063c2e15dc16888180ea9e354085375cf1f0c616a8d25ee6197', NULL),
(16, 'loveline.d@gmail.com', 'Loveline Dieujuste', 'actif', '2026-03-06 22:19:32', 'd8ea6a568f9733154c37414f241ae5771271852a38681e3b592cb265cf99113c', NULL),
(17, 'alce.gad@gmail.com', 'Alce Gad', 'actif', '2026-03-06 22:32:35', '1b27501cbf1d29748f653ff55ce7bb53118875b47fd569f9015fd261ee9f2a86', NULL),
(18, 'alcero.taylor@gmail.com', NULL, 'actif', '2026-03-07 09:59:39', 'e19d6bffcafc8594270843b075d356988f348805edcdbb97d3bd1c139336a96e', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `contenu` longtext DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `image_principale` varchar(255) DEFAULT NULL,
  `template` varchar(100) DEFAULT 'default',
  `statut` enum('publie','brouillon') DEFAULT 'brouillon',
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_modification` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `vue_compteur` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `titre`, `slug`, `contenu`, `meta_description`, `meta_keywords`, `image_principale`, `template`, `statut`, `date_creation`, `date_modification`, `created_by`, `vue_compteur`) VALUES
(1, 'Accueil', 'index', '<h1>Bienvenue au GSCC</h1><p>Groupe de Support Contre le Cancer</p>', NULL, NULL, NULL, 'default', 'publie', '2026-02-28 07:22:26', NULL, NULL, 0),
(2, 'Présentation', 'presentation', '<h1>Qui sommes-nous ?</h1><p>Découvrez notre organisation</p>', NULL, NULL, NULL, 'presentation', 'publie', '2026-02-28 07:22:26', NULL, NULL, 0),
(3, 'Contact', 'contact', '<h1>Contactez-nous</h1><p>Nous sommes à votre écoute</p>', NULL, NULL, NULL, 'contact', 'publie', '2026-02-28 07:22:26', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `parametres`
--

CREATE TABLE `parametres` (
  `id` int(11) NOT NULL,
  `cle` varchar(100) NOT NULL,
  `valeur` text DEFAULT NULL,
  `type` varchar(50) DEFAULT 'text',
  `description` text DEFAULT NULL,
  `date_modification` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `parametres`
--

INSERT INTO `parametres` (`id`, `cle`, `valeur`, `type`, `description`, `date_modification`) VALUES
(1, 'nom_site', 'GSCC - Groupe de Support Contre le Cancer', 'text', 'Nom du site', NULL),
(2, 'slogan', 'Vivre pour Aimer, Vivre pour Aider, Vivre pour Partager, Vivre Intensément', 'text', 'Slogan du site', NULL),
(3, 'email_contact', 'gscc@gscchaiti.com', 'email', 'Email de contact principal', NULL),
(4, 'telephone', '2947 47 22', 'text', 'Téléphone de contact', NULL),
(5, 'adresse', 'Port-au-Prince, Haïti', 'text', 'Adresse principale', NULL),
(6, 'facebook', '#', 'url', 'Lien Facebook', NULL),
(7, 'twitter', '#', 'url', 'Lien Twitter', NULL),
(8, 'instagram', '#', 'url', 'Lien Instagram', NULL),
(9, 'linkedin', '#', 'url', 'Lien LinkedIn', NULL),
(10, 'youtube', '#', 'url', 'Lien YouTube', NULL),
(11, 'tiktok', '#', 'url', 'Lien TikTok', NULL),
(12, 'whatsapp', '#', 'url', 'Lien WhatsApp', NULL),
(13, 'heure_ouverture', 'Lun-Ven: 9h-18h, Sam: 9h-14h', 'text', 'Horaires d\'ouverture', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stats_quotidiennes`
--

CREATE TABLE `stats_quotidiennes` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `visites` int(11) DEFAULT 0,
  `nouveaux_membres` int(11) DEFAULT 0,
  `dons` int(11) DEFAULT 0,
  `montant_dons` decimal(10,2) DEFAULT 0.00,
  `messages_contact` int(11) DEFAULT 0,
  `inscriptions_newsletter` int(11) DEFAULT 0,
  `date_mise_a_jour` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stats_quotidiennes`
--

INSERT INTO `stats_quotidiennes` (`id`, `date`, `visites`, `nouveaux_membres`, `dons`, `montant_dons`, `messages_contact`, `inscriptions_newsletter`, `date_mise_a_jour`) VALUES
(1, '2026-02-28', 0, 0, 0, 0.00, 0, 0, '2026-02-28 07:22:26'),
(2, '2026-03-03', 0, 0, 0, 0.00, 0, 5, '2026-03-03 23:41:47'),
(7, '2026-03-06', 0, 0, 0, 0.00, 0, 12, '2026-03-06 22:32:35'),
(19, '2026-03-07', 0, 0, 0, 0.00, 0, 1, '2026-03-07 09:59:39');

-- --------------------------------------------------------

--
-- Table structure for table `temoignages`
--

CREATE TABLE `temoignages` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `fonction` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `temoignage` text NOT NULL,
  `note` int(11) DEFAULT NULL CHECK (`note` >= 1 and `note` <= 5),
  `statut` enum('en_attente','approuve','rejete') DEFAULT 'en_attente',
  `date_creation` datetime DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `temoignages`
--

INSERT INTO `temoignages` (`id`, `nom`, `fonction`, `photo`, `temoignage`, `note`, `statut`, `date_creation`, `created_by`) VALUES
(1, 'Marie C.', 'Patiente accompagnée', 'images/equipe/marie-jean-baptiste.jpg', 'Grâce au soutien du GSCC, j\'ai pu suivre mon traitement dans de bonnes conditions. Leur accompagnement a été essentiel dans mon combat contre le cancer.', 5, 'approuve', '2026-02-28 07:22:26', NULL),
(2, 'Jean-Paul D.', 'Bénévole', NULL, 'Je suis bénévole au GSCC depuis 2 ans. Voir l\'impact positif de nos actions sur les patients et leurs familles est une source de motivation incroyable.', 5, 'approuve', '2026-02-28 07:22:26', NULL),
(3, 'Sophie L.', 'Proche de patient', NULL, 'Le GSCC m\'a apporté un soutien psychologique précieux quand j\'en avais le plus besoin. Leur équipe est à l\'écoute et vraiment dévouée.', 5, 'approuve', '2026-02-28 07:22:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `code_postal` varchar(20) DEFAULT NULL,
  `pays` varchar(100) DEFAULT 'Haïti',
  `profession` varchar(100) DEFAULT NULL,
  `type_membre` enum('actif','bienfaiteur','honoraire') DEFAULT 'actif',
  `role` enum('membre','moderateur','admin') DEFAULT 'membre',
  `statut` enum('actif','inactif','banni') DEFAULT 'actif',
  `date_inscription` datetime DEFAULT current_timestamp(),
  `derniere_connexion` datetime DEFAULT NULL,
  `token_reset` varchar(255) DEFAULT NULL,
  `token_expire` datetime DEFAULT NULL,
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`)),
  `newsletter` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `email`, `mot_de_passe`, `nom`, `prenom`, `telephone`, `adresse`, `ville`, `code_postal`, `pays`, `profession`, `type_membre`, `role`, `statut`, `date_inscription`, `derniere_connexion`, `token_reset`, `token_expire`, `preferences`, `newsletter`) VALUES
(1, 'admin@gscchaiti.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur', 'GSCC', NULL, NULL, NULL, NULL, 'Haïti', NULL, 'actif', 'admin', 'actif', '2026-02-28 07:22:26', NULL, NULL, NULL, NULL, 0),
(2, 'belvaljohnsley@gmail.com', '$2y$10$8q7WwfM5zymTnOXO5dFIne9.NOyPbI9ruLzQRTftBU3mGNxXvdi2S', 'BELVAL', 'Johnsley', '+509 36 57 1516', NULL, NULL, NULL, 'Haïti', NULL, 'actif', 'membre', 'actif', '2026-03-01 23:03:06', '2026-03-07 06:32:48', NULL, NULL, NULL, 0),
(3, 'alcero.taylor@gmail.com', '$2y$10$uXlzkYUi000LBanGEm65Ce7kSaAGt2a4cC0DN9D.NEHyi6.OCTT4i', 'Taylor A.', 'ALCERO', '+509 43 56 7898', '#19, angle rue st-anne et stenio vincent', 'Fort-Liberte', NULL, 'Haïti', 'ING. Informatique', 'actif', 'membre', 'actif', '2026-03-02 00:20:33', '2026-03-07 10:49:58', NULL, NULL, NULL, 1),
(4, 'loucerie.jean@gmail.com', '$2y$10$aK6T2babg6iflOO3cUpgkOnuRWI2.xqTbIZtnyl4VimdPh0wfNtBy', 'JEAN', 'Loucerie', '+509 33 64 7040', NULL, NULL, NULL, 'Haïti', NULL, 'actif', 'membre', 'actif', '2026-03-06 21:11:09', '2026-03-07 10:41:28', NULL, NULL, NULL, 0),
(5, 'merlene.aime@gmail.com', '$2y$10$OoNcXqtuTPVxEyegvwMeIeNtcM/gosZYGwcuzjYxlVObmVuuwLMqK', 'Aimee', 'Merlene', '+509 43 56 7890', '', '', NULL, 'Haïti', '', 'actif', 'membre', 'actif', '2026-03-06 21:26:47', '2026-03-07 06:45:57', NULL, NULL, NULL, 0),
(6, 'guy.calixte@gmail.com', '$2y$10$W7ppMTqSePACcc2/JDw1heWOfiUmGragLRHik05bgsfy0ytjISa46', 'Calixte', 'Guy', '+509 35 56 1213', NULL, NULL, NULL, 'Haïti', NULL, 'actif', 'membre', 'actif', '2026-03-06 22:10:22', '2026-03-06 22:10:48', NULL, NULL, NULL, 0),
(7, 'karl.josehp@gmail.com', '$2y$10$TZE6FdV.8Ej3oLHoF90UMu0Gp/HgHlZK3ENZb611z9zy.Y3jfD7t2', 'Joseph', 'Karl', '+509 36 67 1619', NULL, NULL, NULL, 'Haïti', NULL, 'actif', 'membre', 'actif', '2026-03-07 13:43:13', '2026-03-07 13:43:17', NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `valeurs`
--

CREATE TABLE `valeurs` (
  `id` int(11) NOT NULL,
  `titre` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `icone` varchar(50) DEFAULT NULL,
  `couleur` varchar(20) DEFAULT NULL,
  `ordre` int(11) DEFAULT 0,
  `est_actif` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `valeurs`
--

INSERT INTO `valeurs` (`id`, `titre`, `description`, `icone`, `couleur`, `ordre`, `est_actif`, `created_at`) VALUES
(1, 'Confiance', 'Nous construisons des relations basées sur la transparence et l\'intégrité avec nos bénéficiaires, partenaires et donateurs.', 'fa-heart', '#003399', 1, 1, '2026-02-28 07:22:26'),
(2, 'Espoir', 'Nous apportons de l\'espoir à travers des programmes de soutien et d\'accompagnement personnalisés.', 'fa-dove', '#C9933A', 2, 1, '2026-02-28 07:22:26'),
(3, 'Solidarité', 'Ensemble, nous sommes plus forts. La solidarité est au cœur de notre action et de notre engagement.', 'fa-hand-holding-heart', '#FF69B4', 3, 1, '2026-02-28 07:22:26'),
(4, 'Vie et guérison', 'Nous œuvrons sans relâche pour offrir une meilleure qualité de vie et favoriser la guérison.', 'fa-leaf', '#4CAF50', 4, 1, '2026-02-28 07:22:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `categorie_id` (`categorie_id`),
  ADD KEY `auteur_id` (`auteur_id`),
  ADD KEY `idx_date_publication` (`date_publication`),
  ADD KEY `idx_statut` (`statut`);
ALTER TABLE `articles` ADD FULLTEXT KEY `idx_recherche` (`titre`,`contenu`);

--
-- Indexes for table `campagnes_projets`
--
ALTER TABLE `campagnes_projets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_dates` (`date_debut`,`date_fin`);
ALTER TABLE `campagnes_projets` ADD FULLTEXT KEY `idx_recherche` (`titre`,`description`,`contenu`);

--
-- Indexes for table `candidatures_benevoles`
--
ALTER TABLE `candidatures_benevoles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_date` (`date_candidature`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `demandes_aide`
--
ALTER TABLE `demandes_aide`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `traite_par` (`traite_par`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_date` (`date_soumission`);

--
-- Indexes for table `dons`
--
ALTER TABLE `dons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_date` (`date_don`);

--
-- Indexes for table `equipe`
--
ALTER TABLE `equipe`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `evenements`
--
ALTER TABLE `evenements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_dates` (`date_debut`,`date_fin`),
  ADD KEY `idx_statut` (`statut`);

--
-- Indexes for table `forum_categories`
--
ALTER TABLE `forum_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forum_reponses`
--
ALTER TABLE `forum_reponses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sujet_id` (`sujet_id`),
  ADD KEY `auteur_id` (`auteur_id`);

--
-- Indexes for table `forum_sujets`
--
ALTER TABLE `forum_sujets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categorie_id` (`categorie_id`),
  ADD KEY `auteur_id` (`auteur_id`),
  ADD KEY `idx_date_creation` (`date_creation`);

--
-- Indexes for table `galerie`
--
ALTER TABLE `galerie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categorie_id` (`categorie_id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `messages_contact`
--
ALTER TABLE `messages_contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lu` (`lu`),
  ADD KEY `idx_date` (`date_envoi`);

--
-- Indexes for table `newsletter_abonnes`
--
ALTER TABLE `newsletter_abonnes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `token_desabonnement` (`token_desabonnement`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_statut` (`statut`);

--
-- Indexes for table `parametres`
--
ALTER TABLE `parametres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cle` (`cle`);

--
-- Indexes for table `stats_quotidiennes`
--
ALTER TABLE `stats_quotidiennes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `date` (`date`);

--
-- Indexes for table `temoignages`
--
ALTER TABLE `temoignages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `valeurs`
--
ALTER TABLE `valeurs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `campagnes_projets`
--
ALTER TABLE `campagnes_projets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `candidatures_benevoles`
--
ALTER TABLE `candidatures_benevoles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `demandes_aide`
--
ALTER TABLE `demandes_aide`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `dons`
--
ALTER TABLE `dons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `equipe`
--
ALTER TABLE `equipe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `evenements`
--
ALTER TABLE `evenements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `forum_categories`
--
ALTER TABLE `forum_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `forum_reponses`
--
ALTER TABLE `forum_reponses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `forum_sujets`
--
ALTER TABLE `forum_sujets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `galerie`
--
ALTER TABLE `galerie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `messages_contact`
--
ALTER TABLE `messages_contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `newsletter_abonnes`
--
ALTER TABLE `newsletter_abonnes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `parametres`
--
ALTER TABLE `parametres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `stats_quotidiennes`
--
ALTER TABLE `stats_quotidiennes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `temoignages`
--
ALTER TABLE `temoignages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `valeurs`
--
ALTER TABLE `valeurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `campagnes_projets`
--
ALTER TABLE `campagnes_projets`
  ADD CONSTRAINT `campagnes_projets_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `demandes_aide`
--
ALTER TABLE `demandes_aide`
  ADD CONSTRAINT `demandes_aide_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `demandes_aide_ibfk_2` FOREIGN KEY (`traite_par`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `dons`
--
ALTER TABLE `dons`
  ADD CONSTRAINT `dons_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `evenements`
--
ALTER TABLE `evenements`
  ADD CONSTRAINT `evenements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `forum_reponses`
--
ALTER TABLE `forum_reponses`
  ADD CONSTRAINT `forum_reponses_ibfk_1` FOREIGN KEY (`sujet_id`) REFERENCES `forum_sujets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_reponses_ibfk_2` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `forum_sujets`
--
ALTER TABLE `forum_sujets`
  ADD CONSTRAINT `forum_sujets_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `forum_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_sujets_ibfk_2` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `galerie`
--
ALTER TABLE `galerie`
  ADD CONSTRAINT `galerie_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `galerie_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `temoignages`
--
ALTER TABLE `temoignages`
  ADD CONSTRAINT `temoignages_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
