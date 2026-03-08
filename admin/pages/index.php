<?php
// admin/pages/index.php
require_once '../includes/admin_auth.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Filtres
$statut = isset($_GET['statut']) ? sanitize($_GET['statut']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Construction de la requête
$where = [];
$params = [];

if ($statut && $statut !== 'tous') {
    $where[] = "statut = ?";
    $params[] = $statut;
}

if ($search) {
    $where[] = "(titre LIKE ? OR contenu LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Récupérer le nombre total de pages
$sql_count = "SELECT COUNT(*) FROM pages $where_clause";
$stmt = $pdo->prepare($sql_count);
$stmt->execute($params);
$total_pages = $stmt->fetchColumn();
$total_pages_count = ceil($total_pages / $limit);

// Récupérer les pages
$sql = "SELECT p.*, CONCAT(u.prenom, ' ', u.nom) as auteur_nom 
        FROM pages p 
        LEFT JOIN utilisateurs u ON p.created_by = u.id 
        $where_clause 
        ORDER BY p.date_modification DESC, p.date_creation DESC 
        LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des pages - Administration</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
            display: flex;
        }

        .admin-container {
            display: flex;
            width: 100%;
        }

        /* Sidebar (réutiliser le style de l'admin) */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            height: 100vh;
            position: fixed;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h2 {
            font-size: 20px;
            margin-top: 10px;
        }

        .nav-menu {
            padding: 20px 0;
        }

        .nav-item {
            list-style: none;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            gap: 10px;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding-left: 30px;
        }

        .nav-link i {
            width: 20px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }

        .top-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title h1 {
            font-size: 24px;
            color: #333;
        }

        .page-title p {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logout-btn {
            color: #666;
            font-size: 18px;
        }

        /* Styles pour la gestion des pages */
        .pages-header {
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

        .pages-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .pages-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .pages-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .pages-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .pages-table tr:hover {
            background: #f8f9fa;
        }

        .page-title-cell {
            font-weight: 600;
            color: #333;
        }

        .page-slug {
            font-size: 12px;
            color: #999;
            margin-top: 3px;
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

        .btn-duplicate {
            background: #ff9800;
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

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
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
                <p>Gestion des pages</p>
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
                <li class="nav-item">
                    <a href="../articles/index.php" class="nav-link">
                        <i class="fas fa-newspaper"></i>
                        Articles
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../evenements/index.php" class="nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        Événements
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../utilisateurs/index.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        Utilisateurs
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../parametres.php" class="nav-link">
                        <i class="fas fa-cog"></i>
                        Paramètres
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <h1>Gestion des pages</h1>
                    <p><?= $total_pages ?> page(s) au total</p>
                </div>
                <div class="user-info">
                    <span><?= e($_SESSION['user_nom'] ?? 'Admin') ?></span>
                    <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </div>

            <!-- Messages flash -->
            <?php $flash = getFlashMessage(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- En-tête avec bouton d'ajout -->
            <div class="pages-header">
                <div></div>
                <a href="ajouter.php" class="btn-add">
                    <i class="fas fa-plus-circle"></i>
                    Nouvelle page
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
                        </select>
                    </div>

                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                        Filtrer
                    </button>

                    <?php if ($search || $statut): ?>
                        <a href="index.php" class="btn-secondary" style="padding: 10px 20px; background: #f0f0f0; border-radius: 8px; text-decoration: none; color: #333;">
                            <i class="fas fa-times"></i>
                            Réinitialiser
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Liste des pages -->
            <?php if (empty($pages)): ?>
                <div class="empty-state">
                    <i class="fas fa-file-alt"></i>
                    <h3>Aucune page trouvée</h3>
                    <p>Commencez par créer votre première page !</p>
                    <a href="ajouter.php" class="btn-add">
                        <i class="fas fa-plus-circle"></i>
                        Créer une page
                    </a>
                </div>
            <?php else: ?>
                <div class="pages-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>URL</th>
                                <th>Auteur</th>
                                <th>Dernière modif.</th>
                                <th>Vues</th>
                                <th>Statut</th>
                                <th>Template</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pages as $p): ?>
                                <tr>
                                    <td>
                                        <div class="page-title-cell"><?= e($p['titre']) ?></div>
                                        <?php if ($p['meta_description']): ?>
                                            <div class="page-slug"><?= truncate($p['meta_description'], 50) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code>/<?= e($p['slug']) ?></code>
                                    </td>
                                    <td><?= e($p['auteur_nom'] ?? 'Inconnu') ?></td>
                                    <td><?= formatDate($p['date_modification'] ?: $p['date_creation']) ?></td>
                                    <td><?= number_format($p['vue_compteur'] ?? 0) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $p['statut'] ?>">
                                            <?= $p['statut'] === 'publie' ? 'Publié' : 'Brouillon' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <code><?= e($p['template'] ?? 'default') ?></code>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="modifier.php?id=<?= $p['id'] ?>" class="btn-action btn-edit" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="../../<?= $p['slug'] ?>.php" target="_blank" class="btn-action btn-view" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="ajouter.php?duplicate=<?= $p['id'] ?>" class="btn-action btn-duplicate" title="Dupliquer">
                                                <i class="fas fa-copy"></i>
                                            </a>
                                            <a href="supprimer.php?id=<?= $p['id'] ?>"
                                                class="btn-action btn-delete"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette page ?\n\nAttention: Cette action est irréversible.')"
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
                <?php if ($total_pages_count > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_pages_count; $i++): ?>
                            <a href="?page=<?= $i ?>&statut=<?= $statut ?>&search=<?= urlencode($search) ?>"
                                class="page-link <?= $page == $i ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>