<?php
// admin/articles/ajouter.php
require_once '../includes/admin_auth.php';

$error = '';
$success = '';

// Récupérer les catégories pour le select
$stmt = $pdo->query("SELECT * FROM categories WHERE type = 'blog' ORDER BY nom");
$categories = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        // Récupérer et valider les données
        $titre = sanitize($_POST['titre'] ?? '');
        $contenu = $_POST['contenu'] ?? '';
        $resume = sanitize($_POST['resume'] ?? '');
        $categorie_id = !empty($_POST['categorie_id']) ? (int)$_POST['categorie_id'] : null;
        $statut = sanitize($_POST['statut'] ?? 'brouillon');
        $tags = sanitize($_POST['tags'] ?? '');
        $meta_description = sanitize($_POST['meta_description'] ?? '');
        $meta_keywords = sanitize($_POST['meta_keywords'] ?? '');
        
        // Validation
        if (empty($titre)) {
            $error = 'Le titre est requis.';
        } elseif (empty($contenu)) {
            $error = 'Le contenu est requis.';
        } else {
            // Générer le slug
            $slug = createSlug($titre);
            
            // Vérifier si le slug existe déjà
            $stmt = $pdo->prepare("SELECT id FROM articles WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                $slug .= '-' . uniqid();
            }
            
            // Gérer l'upload de l'image
            $image_filename = null;
            if (isset($_FILES['image_couverture']) && $_FILES['image_couverture']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadFile($_FILES['image_couverture'], UPLOADS_PATH);
                if ($upload['success']) {
                    $image_filename = $upload['filename'];
                } else {
                    $error = $upload['error'];
                }
            }
            
            if (empty($error)) {
                try {
                    $sql = "INSERT INTO articles (titre, slug, contenu, resume, image_couverture, 
                            categorie_id, auteur_id, statut, tags, meta_description, meta_keywords, 
                            date_creation, date_publication) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
                    
                    $stmt = $pdo->prepare($sql);
                    $date_publication = ($statut === 'publie') ? date('Y-m-d H:i:s') : null;
                    
                    if ($stmt->execute([
                        $titre,
                        $slug,
                        $contenu,
                        $resume,
                        $image_filename,
                        $categorie_id,
                        $admin_id,
                        $statut,
                        $tags,
                        $meta_description,
                        $meta_keywords,
                        $date_publication
                    ])) {
                        setFlashMessage('success', 'Article créé avec succès !');
                        redirect('index.php');
                    } else {
                        $error = 'Erreur lors de la création de l\'article.';
                    }
                } catch (PDOException $e) {
                    logError("Erreur ajout article: " . $e->getMessage());
                    $error = 'Erreur technique. Veuillez réessayer.';
                }
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvel article - Administration</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- TinyMCE pour l'éditeur de texte -->
    <script src="https://cdn.tiny.cloud/1/votre-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        .form-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #f0f0f0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .image-preview {
            margin-top: 10px;
            max-width: 200px;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #f0f0f0;
        }
        
        .image-preview img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102,126,234,0.3);
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            padding: 14px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        
        .sidebar-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .sidebar-info h3 {
            font-size: 16px;
            margin-bottom: 15px;
            color: #333;
        }
        
        .info-item {
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .info-item i {
            width: 20px;
            color: #667eea;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include '../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <h1>Nouvel article</h1>
                    <p>Créez un nouvel article pour le blog</p>
                </div>
                <div class="user-info">
                    <span><?= e($admin_nom) ?></span>
                    <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= e($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= e($success) ?>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="form-grid">
                        <div class="main-form">
                            <div class="form-group">
                                <label for="titre">Titre de l'article *</label>
                                <input type="text" class="form-control" id="titre" name="titre" 
                                       value="<?= e($_POST['titre'] ?? '') ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="resume">Résumé</label>
                                <textarea class="form-control" id="resume" name="resume" rows="3"><?= e($_POST['resume'] ?? '') ?></textarea>
                                <small class="form-text text-muted">Court résumé qui apparaîtra dans la liste des articles</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="contenu">Contenu de l'article *</label>
                                <textarea class="form-control" id="contenu" name="contenu" rows="15"><?= $_POST['contenu'] ?? '' ?></textarea>
                            </div>
                        </div>
                        
                        <div class="sidebar-form">
                            <div class="sidebar-info">
                                <h3>Publication</h3>
                                
                                <div class="form-group">
                                    <label for="statut">Statut</label>
                                    <select class="form-control" id="statut" name="statut">
                                        <option value="brouillon" <?= ($_POST['statut'] ?? '') === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
                                        <option value="publie" <?= ($_POST['statut'] ?? '') === 'publie' ? 'selected' : '' ?>>Publier immédiatement</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="sidebar-info">
                                <h3>Catégorie</h3>
                                
                                <div class="form-group">
                                    <select class="form-control" id="categorie_id" name="categorie_id">
                                        <option value="">Sélectionner une catégorie</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>" <?= ($_POST['categorie_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                                <?= e($cat['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="info-item">
                                    <i class="fas fa-plus-circle"></i>
                                    <a href="categories.php">Gérer les catégories</a>
                                </div>
                            </div>
                            
                            <div class="sidebar-info">
                                <h3>Image à la une</h3>
                                
                                <div class="form-group">
                                    <input type="file" class="form-control" id="image_couverture" name="image_couverture" accept="image/*">
                                </div>
                                
                                <div id="imagePreview" class="image-preview" style="display: none;">
                                    <img src="" alt="Aperçu">
                                </div>
                            </div>
                            
                            <div class="sidebar-info">
                                <h3>Tags</h3>
                                
                                <div class="form-group">
                                    <input type="text" class="form-control" id="tags" name="tags" 
                                           value="<?= e($_POST['tags'] ?? '') ?>" 
                                           placeholder="santé, prévention, cancer">
                                    <small class="form-text text-muted">Séparés par des virgules</small>
                                </div>
                            </div>
                            
                            <div class="sidebar-info">
                                <h3>SEO</h3>
                                
                                <div class="form-group">
                                    <label for="meta_description">Meta description</label>
                                    <textarea class="form-control" id="meta_description" name="meta_description" rows="2"><?= e($_POST['meta_description'] ?? '') ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="meta_keywords">Meta keywords</label>
                                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" 
                                           value="<?= e($_POST['meta_keywords'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="index.php" class="btn-secondary">
                            <i class="fas fa-times"></i>
                            Annuler
                        </a>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i>
                            Enregistrer l'article
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Initialisation de TinyMCE
        tinymce.init({
            selector: '#contenu',
            height: 500,
            menubar: true,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
        });
        
        // Aperçu de l'image avant upload
        document.getElementById('image_couverture').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.style.display = 'block';
                    preview.querySelector('img').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Génération automatique du résumé si vide
        document.getElementById('contenu').addEventListener('blur', function() {
            const resume = document.getElementById('resume');
            if (!resume.value && this.value) {
                // Prendre les premiers 150 caractères du contenu
                const plainText = this.value.replace(/<[^>]*>/g, '');
                resume.value = plainText.substring(0, 150) + '...';
            }
        });
    </script>
</body>
</html>