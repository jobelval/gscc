<?php
// admin/articles/categories.php
require_once '../includes/admin_auth.php';

$error = '';
$success = '';

// Récupérer toutes les catégories
$stmt = $pdo->query("SELECT * FROM categories WHERE type = 'blog' ORDER BY nom");
$categories = $stmt->fetchAll();

// Ajouter une catégorie
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité.';
    } else {
        $nom = sanitize($_POST['nom'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        
        if (empty($nom)) {
            $error = 'Le nom de la catégorie est requis.';
        } else {
            $slug = createSlug($nom);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (nom, slug, description, type) VALUES (?, ?, ?, 'blog')");
                if ($stmt->execute([$nom, $slug, $description])) {
                    setFlashMessage('success', 'Catégorie ajoutée avec succès.');
                    redirect('categories.php');
                }
            } catch (PDOException $e) {
                $error = 'Cette catégorie existe déjà.';
            }
        }
    }
}

// Modifier une catégorie
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité.';
    } else {
        $id = (int)$_POST['id'];
        $nom = sanitize($_POST['nom'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        
        if (empty($nom)) {
            $error = 'Le nom de la catégorie est requis.';
        } else {
            $slug = createSlug($nom);
            
            try {
                $stmt = $pdo->prepare("UPDATE categories SET nom = ?, slug = ?, description = ? WHERE id = ?");
                if ($stmt->execute([$nom, $slug, $description, $id])) {
                    setFlashMessage('success', 'Catégorie modifiée avec succès.');
                    redirect('categories.php');
                }
            } catch (PDOException $e) {
                $error = 'Erreur lors de la modification.';
            }
        }
    }
}

// Supprimer une catégorie
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Vérifier si des articles utilisent cette catégorie
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE categorie_id = ?");
    $stmt->execute([$id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        setFlashMessage('error', 'Impossible de supprimer : des articles sont liés à cette catégorie.');
    } else {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        if ($stmt->execute([$id])) {
            setFlashMessage('success', 'Catégorie supprimée avec succès.');
        }
    }
    redirect('categories.php');
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des catégories - Administration</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .categories-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .add-form {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 2px solid #f0f0f0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .categories-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .categories-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
        }
        
        .categories-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .btn-edit {
            background: #2196f3;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 5px;
        }
        
        .btn-delete {
            background: #f44336;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            width: 500px;
            margin: 100px auto;
            padding: 30px;
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <h1>Gestion des catégories</h1>
                    <p>Organisez vos articles par catégories</p>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            
            <div class="categories-container">
                <!-- Formulaire d'ajout -->
                <div class="add-form">
                    <h3>Ajouter une catégorie</h3>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group">
                            <label>Nom de la catégorie</label>
                            <input type="text" name="nom" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-plus"></i>
                            Ajouter
                        </button>
                    </form>
                </div>
                
                <!-- Liste des catégories -->
                <h3>Catégories existantes</h3>
                <table class="categories-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Articles</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <?php
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE categorie_id = ?");
                        $stmt->execute([$cat['id']]);
                        $nb_articles = $stmt->fetchColumn();
                        ?>
                        <tr>
                            <td><?= e($cat['nom']) ?></td>
                            <td><?= e($cat['slug']) ?></td>
                            <td><?= e(truncate($cat['description'], 50)) ?></td>
                            <td><?= $nb_articles ?></td>
                            <td>
                                <a href="#" onclick="openEditModal(<?= $cat['id'] ?>, '<?= e($cat['nom']) ?>', '<?= e($cat['description']) ?>')" 
                                   class="btn-edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($nb_articles == 0): ?>
                                <a href="?delete=<?= $cat['id'] ?>" 
                                   onclick="return confirm('Supprimer cette catégorie ?')"
                                   class="btn-delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal d'édition -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Modifier la catégorie</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" id="edit_nom" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                </div>
                
                <button type="submit" class="btn-primary">Enregistrer</button>
                <button type="button" onclick="closeEditModal()" class="btn-secondary">Annuler</button>
            </form>
        </div>
    </div>
    
    <script>
        function openEditModal(id, nom, description) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nom').value = nom;
            document.getElementById('edit_description').value = description;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>