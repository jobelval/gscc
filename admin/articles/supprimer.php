<?php
// admin/articles/supprimer.php
require_once '../includes/admin_auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlashMessage('error', 'ID d\'article invalide.');
    redirect('index.php');
}

try {
    // Récupérer l'article pour avoir le nom de l'image
    $stmt = $pdo->prepare("SELECT image_couverture FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();

    if ($article) {
        // Supprimer l'image si elle existe
        if ($article['image_couverture'] && file_exists(UPLOADS_PATH . $article['image_couverture'])) {
            unlink(UPLOADS_PATH . $article['image_couverture']);
        }

        // Supprimer l'article
        $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->execute([$id]);

        setFlashMessage('success', 'Article supprimé avec succès.');
    } else {
        setFlashMessage('error', 'Article non trouvé.');
    }
} catch (PDOException $e) {
    logError("Erreur suppression article: " . $e->getMessage());
    setFlashMessage('error', 'Erreur lors de la suppression.');
}

redirect('index.php');
