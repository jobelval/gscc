<?php
// admin/pages/supprimer.php
require_once '../includes/admin_auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlashMessage('error', 'ID de page invalide.');
    redirect('index.php');
}

try {
    // Empêcher la suppression de pages essentielles
    $stmt = $pdo->prepare("SELECT slug FROM pages WHERE id = ?");
    $stmt->execute([$id]);
    $page = $stmt->fetch();
    
    if ($page) {
        $protected_pages = ['index', 'accueil', 'home', '404', 'contact'];
        if (in_array($page['slug'], $protected_pages)) {
            setFlashMessage('error', 'Cette page est protégée et ne peut pas être supprimée.');
            redirect('index.php');
        }
        
        $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
        $stmt->execute([$id]);
        
        setFlashMessage('success', 'Page supprimée avec succès.');
    } else {
        setFlashMessage('error', 'Page non trouvée.');
    }
} catch (PDOException $e) {
    logError("Erreur suppression page: " . $e->getMessage());
    setFlashMessage('error', 'Erreur lors de la suppression.');
}

redirect('index.php');
?>