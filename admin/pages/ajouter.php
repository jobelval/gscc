<?php
// admin/pages/ajouter.php
require_once '../includes/admin_auth.php';

$error = '';
$success = '';

// Liste des templates disponibles
$templates = [
    'default' => 'Template par défaut',
    'full-width' => 'Pleine largeur',
    'with-sidebar' => 'Avec sidebar',
    'contact' => 'Page de contact',
    'presentation' => 'Page de présentation',
    'gallery' => 'Page galerie'
];

// Si c'est une duplication
$duplicate_id = isset($_GET['duplicate']) ? (int)$_GET['duplicate'] : 0;
if ($duplicate_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
        $stmt->execute([$duplicate_id]);
        $original = $stmt->fetch();
        if ($original) {
            $_POST['titre'] = $original['titre'] . ' (copie)';
            $_POST['contenu'] = $original['contenu'];
            $_POST['template'] = $original['template'];
            $_POST['meta_description'] = $original['meta_description'];
            $_POST['meta_keywords'] = $original['meta_keywords'];
        }
    } catch (PDOException $e) {
        logError("Erreur duplication page: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        // Récupérer et valider les données
        $titre = sanitize($_POST['titre'] ?? '');
        $contenu = $_POST['contenu'] ?? '';
        $template = sanitize($_POST['template'] ?? 'default');
        $statut = sanitize($_POST['statut'] ?? 'brouillon');
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
            $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                $slug .= '-' . uniqid();
            }
            
            try {
                $sql = "INSERT INTO pages (titre, slug, contenu, template, statut, 
                        meta_description, meta_keywords, created_by, date_creation) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([
                    $titre,
                    $slug,
                    $contenu,
                    $template,
                    $statut,
                    $meta_description,
                    $meta_keywords,
                    $admin_id
                ])) {
                    setFlashMessage('success', 'Page créée avec succès !');
                    redirect('index.php');
                } else {
                    $error = 'Erreur lors de la création de la page.';
                }
            } catch (PDOException $e) {
                logError("Erreur ajout page: " . $e->getMessage());
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
    <title>Nouvelle page - Administration</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/votre-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
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
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 10px;
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
        
        .slug-preview {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            background: #e0e0e0;
            color: #666;
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
                <p>Nouvelle page</p>
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
                    <h1>Créer une nouvelle page</h1>
                    <p>Remplissez les informations ci-dessous</p>
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
                                       value="<?= e($_POST['titre'] ?? '') ?>" required 
                                       onkeyup="updateSlug(this.value)">
                                <div class="slug-preview" id="slugPreview">
                                    /<span id="slugValue"><?= isset($_POST['titre']) ? createSlug($_POST['titre']) : 'titre-de-la-page' ?></span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="contenu">Contenu de la page *</label>
                                <textarea class="form-control" id="contenu" name="contenu" rows="20"><?= $_POST['contenu'] ?? '' ?></textarea>
                                <small class="form-text text-muted">Utilisez l'éditeur pour formater votre contenu</small>
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
                                
                                <div class="info-item">
                                    <i class="far fa-clock"></i>
                                    Création: <?= date('d/m/Y H:i') ?>
                                </div>
                            </div>
                            
                            <div class="sidebar-info">
                                <h3>Template</h3>
                                
                                <div class="form-group">
                                    <select class="form-control" id="template" name="template">
                                        <?php foreach ($templates as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= ($_POST['template'] ?? 'default') === $value ? 'selected' : '' ?>>
                                                <?= e($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="info-item">
                                    <i class="fas fa-info-circle"></i>
                                    Le template détermine la mise en page
                                </div>
                            </div>
                            
                            <div class="sidebar-info">
                                <h3>SEO (Référencement)</h3>
                                
                                <div class="form-group">
                                    <label for="meta_description">Meta description</label>
                                    <textarea class="form-control" id="meta_description" name="meta_description" 
                                              rows="3" maxlength="160"><?= e($_POST['meta_description'] ?? '') ?></textarea>
                                    <small class="form-text text-muted">Maximum 160 caractères</small>
                                    <div id="metaDescriptionCount" class="text-right" style="font-size: 12px; margin-top: 5px;">
                                        <span id="currentCount">0</span>/160
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="meta_keywords">Mots-clés</label>
                                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" 
                                           value="<?= e($_POST['meta_keywords'] ?? '') ?>"
                                           placeholder="cancer, soutien, haïti, santé">
                                    <small class="form-text text-muted">Séparés par des virgules</small>
                                </div>
                            </div>
                            
                            <div class="sidebar-info">
                                <h3>Informations</h3>
                                <div class="info-item">
                                    <i class="fas fa-file-alt"></i>
                                    Cette page sera accessible à l'URL:
                                </div>
                                <code style="display: block; padding: 10px; background: #fff; border-radius: 5px;">
                                    /<span id="finalSlug"><?= isset($_POST['titre']) ? createSlug($_POST['titre']) : 'titre-de-la-page' ?></span>
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
                            Enregistrer la page
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
                'removeformat | help | image media link',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
            images_upload_url: 'upload.php',
            images_upload_handler: function (blobInfo, progress) {
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', 'upload.php');

                    xhr.onload = function() {
                        if (xhr.status !== 200) {
                            reject('HTTP Error: ' + xhr.status);
                            return;
                        }

                        const json = JSON.parse(xhr.responseText);

                        if (!json || typeof json.location != 'string') {
                            reject('Invalid JSON: ' + xhr.responseText);
                            return;
                        }

                        resolve(json.location);
                    };

                    const formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());

                    xhr.send(formData);
                });
            }
        });
        
        // Mise à jour du slug en temps réel
        function updateSlug(title) {
            const slug = title.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/--+/g, '-')
                .replace(/^-+|-+$/g, '');
            
            document.getElementById('slugValue').textContent = slug || 'titre-de-la-page';
            document.getElementById('finalSlug').textContent = slug || 'titre-de-la-page';
        }
        
        // Compteur de caractères pour meta description
        document.getElementById('meta_description').addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('currentCount').textContent = count;
            
            if (count > 160) {
                this.style.borderColor = '#f44336';
                document.getElementById('metaDescriptionCount').style.color = '#f44336';
            } else {
                this.style.borderColor = '';
                document.getElementById('metaDescriptionCount').style.color = '';
            }
        });
        
        // Initialiser le compteur
        document.addEventListener('DOMContentLoaded', function() {
            const metaDesc = document.getElementById('meta_description');
            if (metaDesc.value) {
                document.getElementById('currentCount').textContent = metaDesc.value.length;
            }
        });
    </script>
</body>
</html>