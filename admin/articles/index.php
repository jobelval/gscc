<?php
// admin/articles/index.php
require_once '../includes/admin_auth.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Filtres
$statut = isset($_GET['statut']) ? sanitize($_GET['statut']) : '';
$categorie = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Construction de la requête
$where = [];
$params = [];

if ($statut && $statut !== 'tous') {
    $where[] = "a.statut = ?";
    $params[] = $statut;
}

if ($categorie > 0) {
    $where[] = "a.categorie_id = ?";
    $params[] = $categorie;
}

if ($search) {
    $where[] = "(a.titre LIKE ? OR a.contenu LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Récupérer le nombre total d'articles
$sql_count = "SELECT COUNT(*) FROM articles a $where_clause";
$stmt = $pdo->prepare($sql_count);
$stmt->execute($params);
$total_articles = $stmt->fetchColumn();
$total_pages = ceil($total_articles / $limit);

// Récupérer les articles
$sql = "SELECT a.*, c.nom as categorie_nom, 
        CONCAT(u.prenom, ' ', u.nom) as auteur_nom 
        FROM articles a 
        LEFT JOIN categories c ON a.categorie_id = c.id 
        LEFT JOIN utilisateurs u ON a.auteur_id = u.id 
        $where_clause 
        ORDER BY a.date_creation DESC 
        LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Récupérer les catégories pour le filtre
$stmt = $pdo->query("SELECT * FROM categories WHERE type = 'blog' ORDER BY nom");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des articles - Administration</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        /* Styles spécifiques à la gestion des articles */
        .articles-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .btn-add {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .filters-bar {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .filter-control {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #f0f0f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .filter-control:focus {
            outline: none;
            border-color: #667eea;
        }

        .search-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            align-self: flex-end;
        }

        .articles-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .articles-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .articles-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .articles-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }

        .articles-table tr:hover {
            background: #f8f9fa;
        }

        .article-thumb {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
        }

        .article-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
        }

        .article-meta {
            font-size: 12px;
            color: #999;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-publie {
            background: #e8f5e8;
            color: #4caf50;
        }

        .badge-brouillon {
            background: #fff3e0;
            color: #ff9800;
        }

        .badge-archive {
            background: #fee;
            color: #f44336;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn-action {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: #2196f3;
        }

        .btn-delete {
            background: #f44336;
        }

        .btn-view {
            background: #4caf50;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .page-link {
            padding: 8px 15px;
            border-radius: 8px;
            background: white;
            color: #333;
            text-decoration: none;
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        .page-link:hover,
        .page-link.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
        }

        .empty-state i {
            font-size: 60px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #999;
            margin-bottom: 20px;
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
                    <h1>Gestion des articles</h1>
                    <p><?= $total_articles ?> article(s) au total</p>
                </div>
                <div class="user-info">
                    <span><?= e($admin_nom) ?></span>
                    <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </div>

            <!-- En-tête avec bouton d'ajout -->
            <div class="articles-header">
                <div></div>
                <a href="ajouter.php" class="btn-add">
                    <i class="fas fa-plus-circle"></i>
                    Nouvel article
                </a>
            </div>

            <!-- Filtres -->
            <div class="filters-bar">
                <form method="GET" style="width: 100%; display: flex; flex-wrap: wrap; gap: 15px;">
                    <div class="filter-group">
                        <label>Rechercher</label>
                        <input type="text" name="search" class="filter-control"
                            placeholder="Titre ou contenu..." value="<?= e($search) ?>">
                    </div>

                    <div class="filter-group">
                        <label>Statut</label>
                        <select name="statut" class="filter-control">
                            <option value="tous">Tous les statuts</option>
                            <option value="publie" <?= $statut === 'publie' ? 'selected' : '' ?>>Publié</option>
                            <option value="brouillon" <?= $statut === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
                            <option value="archive" <?= $statut === 'archive' ? 'selected' : '' ?>>Archivé</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Catégorie</label>
                        <select name="categorie" class="filter-control">
                            <option value="0">Toutes les catégories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $categorie == $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                        Filtrer
                    </button>
                </form>
            </div>

            <!-- Liste des articles -->
            <?php if (empty($articles)): ?>
                <div class="empty-state">
                    <i class="fas fa-newspaper"></i>
                    <h3>Aucun article trouvé</h3>
                    <p>Commencez par créer votre premier article !</p>
                    <a href="ajouter.php" class="btn-add">
                        <i class="fas fa-plus-circle"></i>
                        Créer un article
                    </a>
                </div>
            <?php else: ?>
                <div class="articles-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Titre</th>
                                <th>Catégorie</th>
                                <th>Auteur</th>
                                <th>Date</th>
                                <th>Vues</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($articles as $article): ?>
                                <tr>
                                    <td>
                                        <?php if ($article['image_couverture']): ?>
                                            <img src="<?= UPLOADS_URL . $article['image_couverture'] ?>"
                                                alt="<?= e($article['titre']) ?>" class="article-thumb">
                                        <?php else: ?>
                                            <div style="width: 60px; height: 60px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-image" style="color: #999;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="article-title"><?= e($article['titre']) ?></div>
                                        <div class="article-meta"><?= truncate(strip_tags($article['resume']), 50) ?></div>
                                    </td>
                                    <td><?= e($article['categorie_nom'] ?? 'Non catégorisé') ?></td>
                                    <td><?= e($article['auteur_nom'] ?? 'Inconnu') ?></td>
                                    <td><?= formatDate($article['date_publication'] ?? $article['date_creation']) ?></td>
                                    <td><?= number_format($article['vue_compteur'] ?? 0) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $article['statut'] ?>">
                                            <?= $article['statut'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="modifier.php?id=<?= $article['id'] ?>" class="btn-action btn-edit" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="../../article.php?id=<?= $article['id'] ?>" target="_blank"
                                                class="btn-action btn-view" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="supprimer.php?id=<?= $article['id'] ?>"
                                                class="btn-action btn-delete"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')"
                                                title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?= $i ?>&statut=<?= $statut ?>&categorie=<?= $categorie ?>&search=<?= urlencode($search) ?>"
                                class="page-link <?= $page == $i ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Confirmation de suppression
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (!confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>

</html>