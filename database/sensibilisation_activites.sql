-- Migration : table sensibilisation_activites
-- À importer via phpMyAdmin sur le serveur d'hébergement

CREATE TABLE IF NOT EXISTS `sensibilisation_activites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `couleur` varchar(20) DEFAULT '#E91E8C',
  `ordre` int(11) DEFAULT 0,
  `est_actif` tinyint(1) DEFAULT 1,
  `date_creation` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données des 7 activités de sensibilisation
INSERT INTO `sensibilisation_activites` (`titre`, `description`, `couleur`, `ordre`, `est_actif`) VALUES
('Octobre Rose', 'Octobre Rose est la campagne annuelle de sensibilisation du GSCC dédiée à la lutte contre le cancer du sein. À travers l\'information, le dépistage précoce, les témoignages et la mobilisation communautaire, cette initiative rappelle depuis 25 ans l\'importance de la prévention et de la solidarité pour sauver des vies.', '#E91E8C', 1, 1),
('Bouger et Transpirer pour Vivre', 'Bouger et Transpirer pour Vivre est une campagne de prévention visant à promouvoir l\'activité physique comme moyen efficace de réduire les risques de cancer. En encourageant des habitudes de vie actives et saines, le GSCC sensibilise le public à l\'importance du mouvement pour protéger durablement sa santé.', '#2E7D32', 2, 1),
('Un col sain', 'Un Col Sain est une campagne de sensibilisation consacrée à la prévention du cancer du col de l\'utérus. Par l\'éducation, l\'information médicale simplifiée et l\'encouragement au dépistage, cette initiative aide les femmes à mieux comprendre l\'importance d\'un suivi gynécologique régulier pour préserver leur santé.', '#00897B', 3, 1),
('Ann Simen Lavi', 'Ann Simen Lavi est une campagne de solidarité nationale visant à mobiliser des ressources humaines et financières pour soutenir directement les patients atteints de cancer en Haïti. À travers des témoignages et des appels à contribution, cette initiative rappelle qu\'un simple geste de soutien peut devenir une semence de vie pour une famille en détresse.', '#F57C00', 4, 1),
('KWAZAD KONT KANSÈ', 'Kwazad Kont Kansè est une vaste campagne de mobilisation communautaire menée à Port-au-Prince et dans plusieurs villes de province. Axée sur le cancer du sein, du col de l\'utérus et de la prostate, elle combine sensibilisation de masse, éducation sanitaire et orientation au dépistage pour encourager une détection précoce.', '#6A1B9A', 5, 1),
('Novembre Bleu', 'Novembre Bleu est la campagne annuelle du GSCC consacrée aux cancers masculins : prostate, testicules et pénis. À travers l\'information, la lutte contre les tabous et la promotion du dépistage, cette initiative encourage les hommes à adopter une attitude plus responsable face à leur santé et à consulter sans attendre.', '#003399', 6, 1),
('KONNEN LI EGZISTE PA SIFI : PREVANSYON SE KLE', 'Konnen li Egziste pa Sifi : Prevansyon se Kle est une campagne axée sur la prévention active du cancer à travers l\'adoption de meilleures habitudes de vie : activité physique, alimentation saine, arrêt du tabac, limitation de l\'alcool, dépistage régulier et santé mentale.', '#C62828', 7, 1);
