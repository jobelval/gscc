<?php
/**
 * GSCC CMS — admin/includes/header.php
 * Topbar + Sidebar — inclus sur toutes les pages admin.
 *
 * Variables attendues :
 *  $page_title  (string) — titre de la page
 *  $page_section (string) — section active dans le menu
 */

// Auth + helpers requis avant ce fichier
// requireAdmin() doit déjà avoir été appelé.

$admin    = getCurrentAdmin();
$initials = strtoupper(substr($admin['prenom'], 0, 1) . substr($admin['nom'], 0, 1));
$flash    = adminGetFlash();

// Compteurs badge sidebar
try {
    $nb_messages  = $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE lu = 0")->fetchColumn();
    $nb_benevoles = $pdo->query("SELECT COUNT(*) FROM candidatures_benevoles WHERE statut = 'en_attente'")->fetchColumn();
    $nb_demandes  = $pdo->query("SELECT COUNT(*) FROM demandes_aide WHERE statut = 'soumis'")->fetchColumn();
    $nb_temos     = $pdo->query("SELECT COUNT(*) FROM temoignages WHERE statut = 'en_attente'")->fetchColumn();
} catch (Exception $e) {
    $nb_messages = $nb_benevoles = $nb_demandes = $nb_temos = 0;
}

$section = $page_section ?? '';

function navItem(string $href, string $icon, string $label, string $active_key, string $current, int $badge = 0): string {
    $active = ($current === $active_key) ? ' active' : '';
    $b = $badge > 0 ? "<span class=\"nav-badge\">{$badge}</span>" : '';
    return "<a href=\"{$href}\" class=\"nav-item{$active}\"><i class=\"fas fa-{$icon}\"></i>{$label}{$b}</a>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Admin') ?> — GSCC CMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/admin/assets/css/admin.css">
    <?php if (!empty($extra_css)) echo $extra_css; ?>
</head>
<body>

<!-- ── Overlay mobile ── -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- ════════════════════════════════
     SIDEBAR
════════════════════════════════ -->
<aside class="admin-sidebar" id="adminSidebar">

    <a href="<?= SITE_URL ?>/admin/index.php" class="sidebar-logo">
        <div class="logo-icon">🎗️</div>
        <div class="logo-text">
            <strong>GSCC CMS</strong>
            <small>Panneau d'administration</small>
        </div>
    </a>

    <nav class="sidebar-nav">

        <!-- Tableau de bord -->
        <div class="nav-group">
            <?= navItem(SITE_URL.'/admin/index.php', 'gauge-high', 'Tableau de bord', 'dashboard', $section) ?>
        </div>

        <!-- Contenu -->
        <div class="nav-group">
            <div class="nav-group-label">Contenu</div>
            <?= navItem(SITE_URL.'/admin/articles/index.php',    'newspaper',     'Articles',          'articles',    $section) ?>
            <?= navItem(SITE_URL.'/admin/campagnes/index.php',   'bullhorn',      'Campagnes & Projets','campagnes',  $section) ?>
            <?= navItem(SITE_URL.'/admin/evenements/index.php',  'calendar-days', 'Événements',        'evenements',  $section) ?>
            <?= navItem(SITE_URL.'/admin/galerie/index.php',     'images',        'Galerie',           'galerie',     $section) ?>
            <?= navItem(SITE_URL.'/admin/survivants/index.php',  'heart-pulse',   'Survivants',        'survivants',  $section) ?>
            <?= navItem(SITE_URL.'/admin/equipe/index.php',      'users',         'Équipe',            'equipe',      $section) ?>
        </div>

        <!-- Communauté -->
        <div class="nav-group">
            <div class="nav-group-label">Communauté</div>
            <?= navItem(SITE_URL.'/admin/utilisateurs/index.php', 'user-group',  'Utilisateurs',        'utilisateurs',$section) ?>
            <?= navItem(SITE_URL.'/admin/benevoles/index.php',    'hands-helping','Bénévoles',          'benevoles',   $section, (int)$nb_benevoles) ?>
            <?= navItem(SITE_URL.'/admin/forum/index.php',        'comments',    'Forum',               'forum',       $section) ?>
            <?= navItem(SITE_URL.'/admin/temoignages/index.php',  'quote-right', 'Témoignages',         'temoignages', $section, (int)$nb_temos) ?>
        </div>

        <!-- Dons & Aide -->
        <div class="nav-group">
            <div class="nav-group-label">Dons & Aide</div>
            <?= navItem(SITE_URL.'/admin/dons/index.php',      'hand-holding-heart','Dons',          'dons',      $section) ?>
            <?= navItem(SITE_URL.'/admin/demandes/index.php',  'file-medical',      'Demandes d\'aide','demandes', $section, (int)$nb_demandes) ?>
        </div>

        <!-- Communication -->
        <div class="nav-group">
            <div class="nav-group-label">Communication</div>
            <?= navItem(SITE_URL.'/admin/messages/index.php',   'envelope',  'Messages',       'messages',   $section, (int)$nb_messages) ?>
            <?= navItem(SITE_URL.'/admin/newsletter/index.php', 'paper-plane','Newsletter',    'newsletter', $section) ?>
        </div>

        <!-- Système -->
        <div class="nav-group">
            <div class="nav-group-label">Système</div>
            <?= navItem(SITE_URL.'/admin/parametres/index.php', 'gear',       'Paramètres',    'parametres', $section) ?>
        </div>

    </nav>

    <!-- Info admin -->
    <div class="sidebar-footer">
        <div class="sidebar-admin-info">
            <div class="sidebar-admin-avatar"><?= $initials ?></div>
            <div>
                <div class="sidebar-admin-name"><?= htmlspecialchars($admin['prenom'].' '.$admin['nom']) ?></div>
                <div class="sidebar-admin-role"><?= ucfirst($admin['role']) ?></div>
            </div>
            <a href="<?= SITE_URL ?>/admin/logout.php" class="sidebar-logout" title="Déconnexion">
                <i class="fas fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

</aside>

<!-- ════════════════════════════════
     MAIN
════════════════════════════════ -->
<div class="admin-main">

    <!-- Topbar -->
    <header class="admin-topbar">
        <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">
            <i class="fas fa-bars"></i>
        </button>

        <div class="topbar-breadcrumb">
            <a href="<?= SITE_URL ?>/admin/index.php" style="color:var(--text-muted);text-decoration:none;">Accueil</a>
            <?php if (!empty($breadcrumb)): ?>
                <?php foreach ($breadcrumb as $bc): ?>
                    <span class="sep">/</span>
                    <?php if (!empty($bc['url'])): ?>
                        <a href="<?= $bc['url'] ?>" style="color:var(--text-muted);text-decoration:none;"><?= htmlspecialchars($bc['label']) ?></a>
                    <?php else: ?>
                        <strong><?= htmlspecialchars($bc['label']) ?></strong>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <span class="sep">/</span>
                <strong><?= htmlspecialchars($page_title ?? '') ?></strong>
            <?php endif; ?>
        </div>

        <div class="topbar-actions">
            <a href="<?= SITE_URL ?>/admin/messages/index.php" class="topbar-btn" title="Messages">
                <i class="fas fa-envelope"></i>
                <?php if ($nb_messages > 0): ?>
                    <span class="topbar-badge"><?= $nb_messages ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= SITE_URL ?>/admin/benevoles/index.php" class="topbar-btn" title="Candidatures">
                <i class="fas fa-user-plus"></i>
                <?php if ($nb_benevoles > 0): ?>
                    <span class="topbar-badge"><?= $nb_benevoles ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= SITE_URL ?>" target="_blank" class="topbar-view-site">
                <i class="fas fa-external-link-alt"></i> Voir le site
            </a>
        </div>
    </header>

    <!-- Contenu principal -->
    <main class="admin-content">

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'danger' : $flash['type']) ?>">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <span><?= htmlspecialchars($flash['msg']) ?></span>
            </div>
        <?php endif; ?>
