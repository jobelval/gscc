<?php
// admin/index.php
require_once 'includes/admin_auth.php';

// Statistiques pour le dashboard
try {
    // Nombre total d'utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'membre'");
    $total_membres = $stmt->fetchColumn();
    
    // Nombre total d'articles
    $stmt = $pdo->query("SELECT COUNT(*) FROM articles WHERE statut = 'publie'");
    $total_articles = $stmt->fetchColumn();
    
    // Nombre total de dons
    $stmt = $pdo->query("SELECT COUNT(*) FROM dons WHERE statut = 'complete'");
    $total_dons = $stmt->fetchColumn();
    
    // Montant total des dons
    $stmt = $pdo->query("SELECT SUM(montant) FROM dons WHERE statut = 'complete'");
    $montant_dons = $stmt->fetchColumn() ?: 0;
    
    // Derniers inscrits
    $stmt = $pdo->query("SELECT * FROM utilisateurs ORDER BY date_inscription DESC LIMIT 5");
    $derniers_inscrits = $stmt->fetchAll();
    
    // Derniers messages de contact
    $stmt = $pdo->query("SELECT * FROM messages_contact ORDER BY date_envoi DESC LIMIT 5");
    $derniers_messages = $stmt->fetchAll();
    
    // Dernières demandes d'aide
    $stmt = $pdo->query("SELECT * FROM demandes_aide ORDER BY date_soumission DESC LIMIT 5");
    $dernieres_demandes = $stmt->fetchAll();
    
} catch (PDOException $e) {
    logError("Erreur dashboard: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administration GSCC</title>
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
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            height: 100vh;
            position: fixed;
            overflow-y: auto;
            transition: all 0.3s ease;
        }
        
        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h2 {
            font-size: 20px;
            margin-top: 10px;
        }
        
        .sidebar-header p {
            font-size: 12px;
            opacity: 0.8;
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
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            gap: 10px;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            padding-left: 30px;
        }
        
        .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            border-left: 4px solid white;
        }
        
        .nav-link i {
            width: 20px;
            font-size: 18px;
        }
        
        .nav-divider {
            height: 1px;
            background: rgba(255,255,255,0.1);
            margin: 15px 20px;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }
        
        .top-bar {
            background: white;
            padding: 15px 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
            gap: 15px;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
        }
        
        .user-details {
            text-align: right;
        }
        
        .user-name {
            font-weight: 600;
            color: #333;
        }
        
        .user-role {
            font-size: 12px;
            color: #666;
        }
        
        /* Dashboard Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        
        .stat-info h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #333;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stat-icon.users { background: #e3f2fd; color: #2196f3; }
        .stat-icon.articles { background: #e8f5e8; color: #4caf50; }
        .stat-icon.dons { background: #fff3e0; color: #ff9800; }
        .stat-icon.montant { background: #f3e5f5; color: #9c27b0; }
        
        /* Recent Activity */
        .recent-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .recent-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .recent-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .recent-header h2 {
            font-size: 18px;
            color: #333;
        }
        
        .view-all {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .item-list {
            list-style: none;
        }
        
        .item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .item:last-child {
            border-bottom: none;
        }
        
        .item-avatar {
            width: 40px;
            height: 40px;
            background: #f0f0f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #666;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
        }
        
        .item-subtitle {
            font-size: 12px;
            color: #999;
        }
        
        .item-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .status-pending { background: #fff3e0; color: #ff9800; }
        .status-approved { background: #e8f5e8; color: #4caf50; }
        .status-rejected { background: #fee; color: #f44336; }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .action-btn {
            background: white;
            border: 2px solid #f0f0f0;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            border-color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.2);
        }
        
        .action-btn i {
            font-size: 24px;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .action-btn span {
            display: block;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-shield-alt" style="font-size: 40px;"></i>
            <h2>GSCC Admin</h2>
            <p>Groupe de Support Contre le Cancer</p>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a href="pages/index.php" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    Pages
                </a>
            </li>
            
            <li class="nav-item">
                <a href="articles/index.php" class="nav-link">
                    <i class="fas fa-newspaper"></i>
                    Articles
                </a>
            </li>
            
            <li class="nav-item">
                <a href="evenements/index.php" class="nav-link">
                    <i class="fas fa-calendar-alt"></i>
                    Événements
                </a>
            </li>
            
            <li class="nav-item">
                <a href="galerie/index.php" class="nav-link">
                    <i class="fas fa-images"></i>
                    Galerie
                </a>
            </li>
            
            <div class="nav-divider"></div>
            
            <li class="nav-item">
                <a href="utilisateurs/index.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    Utilisateurs
                </a>
            </li>
            
            <li class="nav-item">
                <a href="dons/index.php" class="nav-link">
                    <i class="fas fa-hand-holding-heart"></i>
                    Dons
                </a>
            </li>
            
            <li class="nav-item">
                <a href="demandes/index.php" class="nav-link">
                    <i class="fas fa-question-circle"></i>
                    Demandes d'aide
                </a>
            </li>
            
            <li class="nav-item">
                <a href="messages/index.php" class="nav-link">
                    <i class="fas fa-envelope"></i>
                    Messages
                </a>
            </li>
            
            <div class="nav-divider"></div>
            
            <li class="nav-item">
                <a href="forum/index.php" class="nav-link">
                    <i class="fas fa-comments"></i>
                    Forum
                </a>
            </li>
            
            <li class="nav-item">
                <a href="newsletter/index.php" class="nav-link">
                    <i class="fas fa-mail-bulk"></i>
                    Newsletter
                </a>
            </li>
            
            <li class="nav-item">
                <a href="slider/index.php" class="nav-link">
                    <i class="fas fa-images"></i>
                    Slider
                </a>
            </li>
            
            <div class="nav-divider"></div>
            
            <li class="nav-item">
                <a href="parametres.php" class="nav-link">
                    <i class="fas fa-cog"></i>
                    Paramètres
                </a>
            </li>
            
            <li class="nav-item">
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Dashboard</h1>
                <p>Bienvenue dans votre espace d'administration</p>
            </div>
            
            <div class="user-info">
                <div class="user-details">
                    <div class="user-name"><?= e($admin_nom) ?></div>
                    <div class="user-role">Administrateur</div>
                </div>
                <div class="user-avatar">
                    <?= strtoupper(substr($admin_prenom ?? 'A', 0, 1)) ?>
                </div>
            </div>
        </div>
        
        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Membres</h3>
                    <div class="stat-number"><?= number_format($total_membres) ?></div>
                </div>
                <div class="stat-icon users">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Articles</h3>
                    <div class="stat-number"><?= number_format($total_articles) ?></div>
                </div>
                <div class="stat-icon articles">
                    <i class="fas fa-newspaper"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Dons</h3>
                    <div class="stat-number"><?= number_format($total_dons) ?></div>
                </div>
                <div class="stat-icon dons">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Montant total</h3>
                    <div class="stat-number"><?= number_format($montant_dons, 2) ?> G</div>
                </div>
                <div class="stat-icon montant">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        
        <!-- Activité récente -->
        <div class="recent-grid">
            <!-- Derniers inscrits -->
            <div class="recent-card">
                <div class="recent-header">
                    <h2>Derniers membres</h2>
                    <a href="utilisateurs/index.php" class="view-all">Voir tout</a>
                </div>
                
                <ul class="item-list">
                    <?php foreach ($derniers_inscrits as $membre): ?>
                    <li class="item">
                        <div class="item-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="item-info">
                            <div class="item-title"><?= e($membre['prenom'] . ' ' . $membre['nom']) ?></div>
                            <div class="item-subtitle"><?= e($membre['email']) ?></div>
                        </div>
                        <div class="item-date"><?= formatDate($membre['date_inscription']) ?></div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Derniers messages -->
            <div class="recent-card">
                <div class="recent-header">
                    <h2>Derniers messages</h2>
                    <a href="messages/index.php" class="view-all">Voir tout</a>
                </div>
                
                <ul class="item-list">
                    <?php foreach ($derniers_messages as $message): ?>
                    <li class="item">
                        <div class="item-avatar">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="item-info">
                            <div class="item-title"><?= e($message['nom']) ?></div>
                            <div class="item-subtitle"><?= truncate($message['message'], 50) ?></div>
                        </div>
                        <div class="item-date"><?= formatDate($message['date_envoi']) ?></div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Dernières demandes d'aide -->
            <div class="recent-card">
                <div class="recent-header">
                    <h2>Demandes d'aide</h2>
                    <a href="demandes/index.php" class="view-all">Voir tout</a>
                </div>
                
                <ul class="item-list">
                    <?php foreach ($dernieres_demandes as $demande): ?>
                    <li class="item">
                        <div class="item-avatar">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div class="item-info">
                            <div class="item-title"><?= e($demande['type_aide']) ?></div>
                            <div class="item-subtitle">Demande #<?= $demande['id'] ?></div>
                        </div>
                        <div class="item-status status-<?= $demande['statut'] ?>">
                            <?= $demande['statut'] ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <!-- Actions rapides -->
        <div class="recent-card">
            <div class="recent-header">
                <h2>Actions rapides</h2>
            </div>
            
            <div class="quick-actions">
                <a href="articles/ajouter.php" class="action-btn">
                    <i class="fas fa-plus-circle"></i>
                    <span>Nouvel article</span>
                </a>
                
                <a href="evenements/ajouter.php" class="action-btn">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Nouvel événement</span>
                </a>
                
                <a href="pages/ajouter.php" class="action-btn">
                    <i class="fas fa-file-alt"></i>
                    <span>Nouvelle page</span>
                </a>
                
                <a href="newsletter/creer.php" class="action-btn">
                    <i class="fas fa-mail-bulk"></i>
                    <span>Newsletter</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>