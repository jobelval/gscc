<?php
// admin/pages/modifier.php
require_once '../includes/admin_auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlashMessage('error', 'ID de page invalide.');
    redirect('index.php');
}

// Récupérer la page
try {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$id]);
    $page = $stmt->fetch();
    
    if (!$page) {
        setFlashMessage('error', 'Page non trouvée.');
        redirect('index.php');
    }
} catch (PDOException $e) {
    logError("Erreur récupération page: " . $e->getMessage());
    setFlashMessage('error', 'Erreur technique.');
    redirect('index.php');
}

$error = '';
$success = '';

// Liste des templates
$templates = [
    'default' => 'Template par défaut',
    'full-width' => 'Pleine largeur',
    'with-sidebar' => 'Avec sidebar',
    'contact' => 'Page de contact',
    'presentation' => 'Page de présentation',
    'gallery' => 'Page galerie'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $titre = sanitize($_POST['titre'] ?? '');
        $contenu = $_POST['contenu'] ?? '';
        $template = sanitize($_POST['template'] ?? 'default');
        $statut = sanitize($_POST['statut'] ?? 'brouillon');
        $meta_description = sanitize($_POST['meta_description'] ?? '');
        $meta_keywords = sanitize($_POST['meta_keywords'] ?? '');
        
        if (empty($titre)) {
            $error = 'Le titre est requis.';
        } elseif (empty($contenu)) {
            $error = 'Le contenu est requis.';
        } else {
            // Générer le slug
            $slug = createSlug($titre);
            
            // Vérifier si le slug existe déjà (pour une autre page)
            $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $id]);
            if ($stmt->fetch()) {
                $slug .= '-' . uniqid();
            }
            
            try {
                $sql = "UPDATE pages SET 
                        titre = ?, slug = ?, contenu = ?, template = ?, 
                        statut = ?, meta_description = ?, meta_keywords = ?,
                        date_modification = NOW()
                        WHERE id = ?";
                
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([
                    $titre,
                    $slug,
                    $contenu,
                    $template,
                    $statut,
                    $meta_description,
                    $meta_keywords,
                    $id
                ])) {
                    setFlashMessage('success', 'Page modifiée avec succès !');
                    redirect('index.php');
                } else {
                    $error = 'Erreur lors de la modification.';
                }
            } catch (PDOException $e) {
                logError("Erreur modification page: " . $e->getMessage());
                $error = 'Erreur technique. Veuillez réessayer.';
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
    <title>Modifier une page - Administration</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/votre-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        /* Mêmes styles que ajouter.php */
        .admin-container {
            display: flex;
            width: 100%;
        }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            height: 100vh;
            position: fixed;
            overflow-y: auto;
        }
        
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }
        
        .top-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
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
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            padding: 14px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
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
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 10px;
        }
        
        .slug-preview {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .last-modified {
            background: #fff3e0;
            padding: 10px;
            border-radius: 5px;
            font-size: 13px;
            color: #ff9800;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-shield-alt" style="font-size: 40px;"></i>
                <h2>GSCC Admin</h2>
                <p>Modifier une page</p>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../index.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php" class="nav-link active">
                        <i class="fas fa-file-alt"></i>
                        Pages
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <h1>Modifier la page</h1>
                    <p>Modifiez le contenu de la page</p>
                </div>
                <div class="user-info">
                    <span><?= e($_SESSION['user_nom'] ?? 'Admin') ?></span>
                    <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= e($error) ?>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="form-grid">
                        <div class="main-form">
                            <div class="form-group">
                                <label for="titre">Titre de la page *</label>
                                <input type="text" class="form-control" id="titre" name="titre" 
                                       value="<?= e($_POST['titre'] ?? $page['titre']) ?>" required 
                                       onkeyup="updateSlug(this.value)">
                                <div class="slug-preview" id="slugPreview">
                                    /<span id="slugValue"><?= e($page['slug']) ?></span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="contenu">Contenu de la page *</label>
                                <textarea class="form-control" id="contenu" name="contenu" rows="20"><?= $_POST['contenu'] ?? $page['contenu'] ?></textarea>
                            </div>
                        </div>
                        
                        <div class="sidebar-form">
                            <div class="sidebar-info">
                                <h3>Publication</h3>
                                
                                <div class="form-group">
                                    <label for="statut">Statut</label>
                                    <select class="form-control" id="statut" name="statut">
                                        <option value="brouillon" <?= ($_POST['statut'] ?? $page['statut']) === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
                                        <option value="publie" <?= ($_POST['statut'] ?? $page['statut']) === 'publie' ? 'selected' : '' ?>>Publié</option>
                                    </select>
                                </div>
                                
                                <div class="info-item">
                                    <i class="far fa-calendar-alt"></i>
                                    Créée le: <?= formatDate($page['date_creation']) ?>
                                </div>
                                
                                <?php if ($page['date_modification']): ?>
                                <div class="info-item">
                                    <i class="far fa-clock"></i>
                                    Dernière modification: <?= formatDate($page['date_modification']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="sidebar-info">
                                <h3>Template</h3>
                                
                                <div class="form-group">
                                    <select class="form-control" id="template" name="template">
                                        <?php foreach ($templates as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= ($_POST['template'] ?? $page['template']) === $value ? 'selected' : '' ?>>
                                                <?= e($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="sidebar-info">
                                <h3>SEO</h3>
                                
                                <div class="form-group">
                                    <label for="meta_description">Meta description</label>
                                    <textarea class="form-control" id="meta_description" name="meta_description" 
                                              rows="3"><?= e($_POST['meta_description'] ?? $page['meta_description']) ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="meta_keywords">Mots-clés</label>
                                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" 
                                           value="<?= e($_POST['meta_keywords'] ?? $page['meta_keywords']) ?>">
                                </div>
                            </div>
                            
                            <div class="sidebar-info">
                                <h3>URL actuelle</h3>
                                <code style="display: block; padding: 10px; background: #fff; border-radius: 5px;">
                                    /<?= e($page['slug']) ?>
                                </code>
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
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
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
                'removeformat | help | image media link',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
            images_upload_url: 'upload.php'
        });
        
        function updateSlug(title) {
            const slug = title.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/--+/g, '-')
                .replace(/^-+|-+$/g, '');
            
            document.getElementById('slugValue').textContent = slug || '<?= e($page['slug']) ?>';
        }
    </script>
</body>
</html>