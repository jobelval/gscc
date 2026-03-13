<?php
// includes/db_functions.php - Fonctions base de données

/**
 * Récupère un utilisateur par son email
 */
function getUserByEmail($email) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        logError("Erreur getUserByEmail: " . $e->getMessage());
        return false;
    }
}

/**
 * Récupère un utilisateur par son ID
 */
function getUserById($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        logError("Erreur getUserById: " . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les images du slider
 */
function getSliderImages() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM slider_accueil WHERE est_actif = 1 ORDER BY ordre ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        logError("Erreur getSliderImages: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les derniers articles
 */
function getDerniersArticles($limit = 3) {
    global $pdo;
    try {
        $sql = "SELECT a.*, c.nom as categorie_nom,
                CONCAT(u.prenom, ' ', u.nom) as auteur_nom
                FROM articles a
                LEFT JOIN categories c ON a.categorie_id = c.id
                LEFT JOIN utilisateurs u ON a.auteur_id = u.id
                WHERE a.statut = 'publie'
                ORDER BY a.date_publication DESC
                LIMIT ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        logError("Erreur getDerniersArticles: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les prochains événements
 */
function getProchainsEvenements($limit = 3) {
    global $pdo;
    try {
        $sql = "SELECT * FROM evenements
                WHERE statut = 'a_venir'
                AND date_debut >= NOW()
                ORDER BY date_debut ASC
                LIMIT ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        logError("Erreur getProchainsEvenements: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les témoignages approuvés
 */
function getTemoignagesApprouves($limit = 5) {
    global $pdo;
    try {
        $sql = "SELECT * FROM temoignages
                WHERE statut = 'approuve'
                ORDER BY date_creation DESC
                LIMIT ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        logError("Erreur getTemoignagesApprouves: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les partenaires actifs
 */
function getPartenairesActifs() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM partenaires WHERE est_actif = 1 ORDER BY ordre ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        logError("Erreur getPartenairesActifs: " . $e->getMessage());
        return [];
    }
}

/**
 * Ajoute une inscription à la newsletter
 */
function subscribeNewsletter($email, $nom = null) {
    global $pdo;

    try {
        // Vérifier si déjà inscrit
        $stmt = $pdo->prepare("SELECT id FROM newsletter_abonnes WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Cet email est déjà inscrit à la newsletter'];
        }

        // Générer un token de désabonnement
        $token = bin2hex(random_bytes(32));

        $sql  = "INSERT INTO newsletter_abonnes (email, nom, token_desabonnement, date_inscription) VALUES (?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$email, $nom, $token])) {
            $subject  = "Confirmation d'inscription à la newsletter GSCC";
            $message  = "Bonjour " . ($nom ?: "cher visiteur") . ",\n\n";
            $message .= "Merci de votre inscription à notre newsletter !\n\n";
            $message .= "Vous recevrez désormais nos actualités et événements.\n\n";
            $message .= "L'équipe GSCC";

            sendEmail($email, $subject, $message);

            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Erreur lors de l\'inscription'];

    } catch (PDOException $e) {
        logError("Erreur subscribeNewsletter: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur technique'];
    }
}

/**
 * Ajoute un message de contact
 */
function addContactMessage($data) {
    global $pdo;

    try {
        $sql  = "INSERT INTO messages_contact (nom, email, telephone, sujet, message, date_envoi)
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([
            $data['nom'],
            $data['email'],
            $data['telephone'] ?? null,
            $data['sujet']     ?? null,
            $data['message']
        ])) {
            $subject  = "Nouveau message de contact - GSCC";
            $message  = "Nouveau message de " . $data['nom'] . " (" . $data['email'] . ")\n\n";
            $message .= "Sujet: " . ($data['sujet'] ?? 'Sans sujet') . "\n\n";
            $message .= "Message:\n" . $data['message'];

            sendEmail(SITE_EMAIL, $subject, $message);

            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Erreur lors de l\'envoi'];

    } catch (PDOException $e) {
        logError("Erreur addContactMessage: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur technique'];
    }
}

/**
 * Récupère les campagnes et projets avec filtres
 */
function getCampagnesProjets($type = 'tout', $statut = 'tout', $limit = 0, $offset = 0) {
    global $pdo;

    try {
        $where  = ["est_actif = 1"];
        $params = [];

        if ($type !== 'tout') {
            $where[]  = "type = ?";
            $params[] = $type;
        }

        if ($statut !== 'tout') {
            $where[]  = "statut = ?";
            $params[] = $statut;
        }

        $where_clause = "WHERE " . implode(" AND ", $where);

        $sql = "SELECT * FROM campagnes_projets
                $where_clause
                ORDER BY
                    CASE statut
                        WHEN 'en_cours' THEN 1
                        WHEN 'a_venir'  THEN 2
                        WHEN 'termine'  THEN 3
                    END,
                    date_debut DESC";

        if ($limit > 0) {
            $sql     .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();

    } catch (PDOException $e) {
        logError("Erreur getCampagnesProjets: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère une campagne/projet par son ID
 */
function getCampagneById($id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM campagnes_projets WHERE id = ? AND est_actif = 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        logError("Erreur getCampagneById: " . $e->getMessage());
        return false;
    }
}

/**
 * Récupère une campagne/projet par son slug
 */
function getCampagneBySlug($slug) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM campagnes_projets WHERE slug = ? AND est_actif = 1");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        logError("Erreur getCampagneBySlug: " . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les statistiques des campagnes/projets
 */
function getCampagnesStats() {
    global $pdo;

    try {
        $stats = [
            'campagnes' => 0,
            'projets'   => 0,
            'en_cours'  => 0,
            'termines'  => 0,
            'a_venir'   => 0
        ];

        $stmt = $pdo->query("SELECT type, COUNT(*) as count FROM campagnes_projets WHERE est_actif = 1 GROUP BY type");
        while ($row = $stmt->fetch()) {
            $stats[$row['type'] . 's'] = $row['count'];
        }

        $stmt = $pdo->query("SELECT statut, COUNT(*) as count FROM campagnes_projets WHERE est_actif = 1 GROUP BY statut");
        while ($row = $stmt->fetch()) {
            $stats[$row['statut']] = $row['count'];
        }

        return $stats;

    } catch (PDOException $e) {
        logError("Erreur getCampagnesStats: " . $e->getMessage());
        return $stats;
    }
}

/**
 * Récupère les dernières campagnes/projets
 */
function getDernieresCampagnes($limit = 3) {
    global $pdo;

    try {
        $sql = "SELECT * FROM campagnes_projets
                WHERE est_actif = 1 AND statut IN ('en_cours', 'a_venir')
                ORDER BY date_debut ASC
                LIMIT ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        logError("Erreur getDernieresCampagnes: " . $e->getMessage());
        return [];
    }
}