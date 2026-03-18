<?php
/**
 * GSCC CMS — admin/index.php
 * Tableau de bord principal
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

requireModerator();

$page_title   = 'Tableau de bord';
$page_section = 'dashboard';

/* ── Statistiques générales ── */
try {
    $stats = [
        'membres'      => $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'membre'")->fetchColumn(),
        'articles'     => $pdo->query("SELECT COUNT(*) FROM articles WHERE statut = 'publie'")->fetchColumn(),
        'dons_total'   => $pdo->query("SELECT COALESCE(SUM(montant),0) FROM dons WHERE statut = 'complete' OR statut = 'en_attente'")->fetchColumn(),
        'dons_count'   => $pdo->query("SELECT COUNT(*) FROM dons")->fetchColumn(),
        'benevoles'    => $pdo->query("SELECT COUNT(*) FROM candidatures_benevoles WHERE statut = 'en_attente'")->fetchColumn(),
        'messages'     => $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE lu = 0")->fetchColumn(),
        'newsletter'   => $pdo->query("SELECT COUNT(*) FROM newsletter_abonnes WHERE statut = 'actif'")->fetchColumn(),
        'campagnes'    => $pdo->query("SELECT COUNT(*) FROM campagnes_projets WHERE statut = 'en_cours'")->fetchColumn(),
        'demandes'     => $pdo->query("SELECT COUNT(*) FROM demandes_aide WHERE statut = 'soumis'")->fetchColumn(),
        'temoignages'  => $pdo->query("SELECT COUNT(*) FROM temoignages WHERE statut = 'en_attente'")->fetchColumn(),
        'galerie'      => $pdo->query("SELECT COUNT(*) FROM galerie")->fetchColumn(),
        'forum_sujets' => $pdo->query("SELECT COUNT(*) FROM forum_sujets")->fetchColumn(),
    ];

    // Dons des 30 derniers jours
    $dons_30j = $pdo->query(
        "SELECT COALESCE(SUM(montant),0) FROM dons WHERE date_don >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
    )->fetchColumn();

    // Derniers dons
    $derniers_dons = $pdo->query(
        "SELECT nom_donateur, email_donateur, montant, mode_paiement, statut, date_don
         FROM dons ORDER BY date_don DESC LIMIT 6"
    )->fetchAll();

    // Derniers messages
    $derniers_messages = $pdo->query(
        "SELECT nom, email, sujet, date_envoi, lu
         FROM messages_contact ORDER BY date_envoi DESC LIMIT 6"
    )->fetchAll();

    // Dernières candidatures bénévoles
    $candidatures = $pdo->query(
        "SELECT nom, prenom, email, profession, statut, date_candidature
         FROM candidatures_benevoles ORDER BY date_candidature DESC LIMIT 5"
    )->fetchAll();

    // Dons par mois (6 derniers mois)
    $dons_mois = $pdo->query(
        "SELECT DATE_FORMAT(date_don,'%Y-%m') as mois,
                COUNT(*) as nb,
                COALESCE(SUM(montant),0) as total
         FROM dons
         WHERE date_don >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
         GROUP BY DATE_FORMAT(date_don,'%Y-%m')
         ORDER BY mois ASC"
    )->fetchAll();

    // Articles récents
    $articles_recents = $pdo->query(
        "SELECT a.titre, a.statut, a.vue_compteur, a.date_publication,
                c.nom as categorie
         FROM articles a
         LEFT JOIN categories c ON a.categorie_id = c.id
         ORDER BY a.date_creation DESC LIMIT 5"
    )->fetchAll();

} catch (PDOException $e) {
    $stats = array_fill_keys(['membres','articles','dons_total','dons_count','benevoles','messages','newsletter','campagnes','demandes','temoignages','galerie','forum_sujets'], 0);
    $dons_30j = $dons_mois = $derniers_dons = $derniers_messages = $candidatures = $articles_recents = [];
}

// Préparer données Chart.js
$chart_labels  = json_encode(array_column($dons_mois, 'mois'));
$chart_amounts = json_encode(array_map(fn($r) => (float)$r['total'], $dons_mois));
$chart_counts  = json_encode(array_map(fn($r) => (int)$r['nb'],    $dons_mois));

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <div class="page-title">Tableau de bord</div>
        <div class="page-subtitle">Bienvenue, <?= htmlspecialchars($admin['prenom']) ?> — <?= date('l d F Y') ?></div>
    </div>
    <div class="d-flex" style="gap:10px;flex-wrap:wrap;">
        <a href="<?= SITE_URL ?>/admin/articles/create.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nouvel article
        </a>
        <a href="<?= SITE_URL ?>/admin/galerie/upload.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-upload"></i> Upload
        </a>
    </div>
</div>

<!-- ── Stats principales ── -->
<div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr))">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= fmt($stats['membres']) ?></div>
            <div class="stat-label">Membres inscrits</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-hand-holding-heart"></i></div>
        <div class="stat-info">
            <div class="stat-value">$<?= number_format($stats['dons_total'], 0, ',', ' ') ?></div>
            <div class="stat-label">Total dons ($)</div>
            <div class="stat-delta up"><i class="fas fa-arrow-up"></i> $<?= number_format($dons_30j,0,',',' ') ?> ce mois</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon rose"><i class="fas fa-paper-plane"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= fmt($stats['newsletter']) ?></div>
            <div class="stat-label">Abonnés newsletter</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-newspaper"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= fmt($stats['articles']) ?></div>
            <div class="stat-label">Articles publiés</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-bullhorn"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= fmt($stats['campagnes']) ?></div>
            <div class="stat-label">Campagnes en cours</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal"><i class="fas fa-images"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= fmt($stats['galerie']) ?></div>
            <div class="stat-label">Médias en galerie</div>
        </div>
    </div>
</div>

<!-- ── Alertes actions requises ── -->
<?php
$urgents = [];
if ($stats['messages']    > 0) $urgents[] = ['icon'=>'envelope','color'=>'blue','text'=>$stats['messages'].' message(s) non lu(s)','url'=>SITE_URL.'/admin/messages/index.php'];
if ($stats['benevoles']   > 0) $urgents[] = ['icon'=>'user-plus','color'=>'orange','text'=>$stats['benevoles'].' candidature(s) bénévole en attente','url'=>SITE_URL.'/admin/benevoles/index.php'];
if ($stats['demandes']    > 0) $urgents[] = ['icon'=>'file-medical','color'=>'rose','text'=>$stats['demandes'].' demande(s) d\'aide à traiter','url'=>SITE_URL.'/admin/demandes/index.php'];
if ($stats['temoignages'] > 0) $urgents[] = ['icon'=>'quote-right','color'=>'purple','text'=>$stats['temoignages'].' témoignage(s) à approuver','url'=>SITE_URL.'/admin/temoignages/index.php'];
?>
<?php if ($urgents): ?>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:10px;margin-bottom:20px;">
    <?php foreach ($urgents as $u): ?>
    <a href="<?= $u['url'] ?>" style="display:flex;align-items:center;gap:12px;background:#fff;border:1.5px solid #FCD34D;border-radius:10px;padding:13px 16px;text-decoration:none;color:var(--text);transition:all .18s;"
       onmouseover="this.style.background='#FFFBEB'" onmouseout="this.style.background='#fff'">
        <div style="width:36px;height:36px;background:#FEF3C7;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#D97706;flex-shrink:0;">
            <i class="fas fa-<?= $u['icon'] ?>"></i>
        </div>
        <span style="font-size:.84rem;font-weight:600;"><?= $u['text'] ?></span>
        <i class="fas fa-chevron-right" style="margin-left:auto;color:#94A3B8;font-size:11px;"></i>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ── Actions rapides ── -->
<div class="quick-actions" style="grid-template-columns:repeat(auto-fit,minmax(120px,1fr));margin-bottom:24px;">
    <a href="<?= SITE_URL ?>/admin/articles/create.php" class="quick-action-btn">
        <i class="fas fa-pen-to-square"></i> Nouvel article
    </a>
    <a href="<?= SITE_URL ?>/admin/campagnes/create.php" class="quick-action-btn">
        <i class="fas fa-bullhorn"></i> Nouvelle campagne
    </a>
<a href="<?= SITE_URL ?>/admin/galerie/upload.php" class="quick-action-btn">
        <i class="fas fa-cloud-upload-alt"></i> Upload média
    </a>
    <a href="<?= SITE_URL ?>/admin/newsletter/index.php?tab=composer" class="quick-action-btn">
        <i class="fas fa-paper-plane"></i> Newsletter
    </a>
    <a href="<?= SITE_URL ?>/admin/utilisateurs/index.php" class="quick-action-btn">
        <i class="fas fa-user-group"></i> Utilisateurs
    </a>
</div>

<!-- ── Graphiques + Récents ── -->
<div class="dash-grid" style="grid-template-columns:1.5fr 1fr;gap:16px;margin-bottom:16px;">

    <!-- Graphique dons -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-line"></i> Dons — 6 derniers mois</div>
            <a href="<?= SITE_URL ?>/admin/dons/index.php" class="btn btn-secondary btn-xs">Voir tout</a>
        </div>
        <div class="card-body" style="padding:16px;">
            <canvas id="donsChart" height="200"></canvas>
        </div>
    </div>

    <!-- Résumé stats secondaires -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-pie"></i> Vue d'ensemble</div>
        </div>
        <div class="card-body" style="padding:0;">
            <?php
            $overview = [
                ['label'=>'Total dons enregistrés', 'val'=>$stats['dons_count'],    'icon'=>'hand-holding-heart','color'=>'green'],
                ['label'=>'Forum — sujets',          'val'=>$stats['forum_sujets'], 'icon'=>'comments',          'color'=>'blue'],
                ['label'=>'Campagnes actives',       'val'=>$stats['campagnes'],    'icon'=>'bullhorn',          'color'=>'purple'],
            ];
            ?>
            <?php foreach ($overview as $ov): ?>
            <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border);">
                <div class="stat-icon <?= $ov['color'] ?>" style="width:36px;height:36px;border-radius:8px;font-size:14px;">
                    <i class="fas fa-<?= $ov['icon'] ?>"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-size:.8rem;color:var(--text-muted);"><?= $ov['label'] ?></div>
                </div>
                <div style="font-size:1.1rem;font-weight:700;color:var(--text);"><?= fmt($ov['val']) ?></div>
            </div>
            <?php endforeach; ?>
            <!-- Abonnés newsletter + barre -->
            <div style="padding:14px 20px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-size:.82rem;color:var(--text-muted);">Abonnés newsletter</span>
                    <span style="font-size:.9rem;font-weight:700;"><?= fmt($stats['newsletter']) ?></span>
                </div>
                <div class="progress">
                    <div class="progress-bar success" style="width:<?= min(100, ($stats['newsletter']/500)*100) ?>%"></div>
                </div>
                <div style="font-size:.75rem;color:var(--text-muted);margin-top:4px;">Objectif 500 abonnés</div>
            </div>
        </div>
    </div>

</div>

<!-- ── 3 colonnes : Dons récents / Messages / Candidatures ── -->
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px;" class="dash-grid-3">

    <!-- Derniers dons -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-hand-holding-heart"></i> Derniers dons</div>
            <a href="<?= SITE_URL ?>/admin/dons/index.php" class="btn btn-secondary btn-xs">Tout voir</a>
        </div>
        <div class="card-body" style="padding:0 20px;">
            <?php if ($derniers_dons): ?>
            <ul class="recent-list">
                <?php foreach ($derniers_dons as $don): ?>
                <li>
                    <div class="avatar avatar-sm">
                        <?= strtoupper(substr($don['nom_donateur'] ?? 'D', 0, 1)) ?>
                    </div>
                    <div class="rl-info">
                        <div class="rl-title"><?= htmlspecialchars(truncate($don['nom_donateur'] ?? 'Anonyme', 22)) ?></div>
                        <div class="rl-sub"><?= htmlspecialchars($don['mode_paiement']) ?> · <?= statusBadge($don['statut']) ?></div>
                    </div>
                    <div class="rl-meta">
                        <div class="rl-amount"><?= number_format($don['montant'],0,',',' ') ?></div>
                        <div class="rl-date"><?= dateFr($don['date_don']) ?></div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <div style="text-align:center;padding:30px;color:var(--text-muted);font-size:.85rem;">Aucun don enregistré</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Derniers messages -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-envelope"></i> Messages récents</div>
            <a href="<?= SITE_URL ?>/admin/messages/index.php" class="btn btn-secondary btn-xs">Tout voir</a>
        </div>
        <div class="card-body" style="padding:0 20px;">
            <?php if ($derniers_messages): ?>
            <ul class="recent-list">
                <?php foreach ($derniers_messages as $msg): ?>
                <li>
                    <div class="avatar avatar-sm">
                        <?= strtoupper(substr($msg['nom'] ?? 'M', 0, 1)) ?>
                    </div>
                    <div class="rl-info">
                        <div class="rl-title" style="<?= !$msg['lu'] ? 'color:var(--primary);' : '' ?>">
                            <?= htmlspecialchars(truncate($msg['nom'], 18)) ?>
                            <?php if (!$msg['lu']): ?><span style="display:inline-block;width:7px;height:7px;background:var(--rose);border-radius:50%;margin-left:5px;"></span><?php endif; ?>
                        </div>
                        <div class="rl-sub"><?= htmlspecialchars(truncate($msg['sujet'] ?? '—', 22)) ?></div>
                    </div>
                    <div class="rl-meta">
                        <div class="rl-date"><?= dateFr($msg['date_envoi']) ?></div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <div style="text-align:center;padding:30px;color:var(--text-muted);font-size:.85rem;">Aucun message</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Candidatures bénévoles -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-user-plus"></i> Candidatures</div>
            <a href="<?= SITE_URL ?>/admin/benevoles/index.php" class="btn btn-secondary btn-xs">Tout voir</a>
        </div>
        <div class="card-body" style="padding:0 20px;">
            <?php if ($candidatures): ?>
            <ul class="recent-list">
                <?php foreach ($candidatures as $c): ?>
                <li>
                    <div class="avatar avatar-sm">
                        <?= strtoupper(substr($c['prenom'], 0, 1)) ?>
                    </div>
                    <div class="rl-info">
                        <div class="rl-title"><?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?></div>
                        <div class="rl-sub"><?= htmlspecialchars(truncate($c['profession'] ?? 'N/A', 20)) ?></div>
                    </div>
                    <div class="rl-meta">
                        <?= statusBadge($c['statut']) ?>
                        <div class="rl-date"><?= dateFr($c['date_candidature']) ?></div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <div style="text-align:center;padding:30px;color:var(--text-muted);font-size:.85rem;">Aucune candidature</div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ── Articles récents ── -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-newspaper"></i> Articles récents</div>
        <a href="<?= SITE_URL ?>/admin/articles/index.php" class="btn btn-secondary btn-xs">Gérer les articles</a>
    </div>
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Catégorie</th>
                    <th>Statut</th>
                    <th>Vues</th>
                    <th>Date</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($articles_recents): ?>
                    <?php foreach ($articles_recents as $art): ?>
                    <tr>
                        <td><span class="fw-600"><?= htmlspecialchars(truncate($art['titre'], 50)) ?></span></td>
                        <td><span class="badge badge-primary"><?= htmlspecialchars($art['categorie'] ?? '—') ?></span></td>
                        <td><?= statusBadge($art['statut']) ?></td>
                        <td><?= fmt($art['vue_compteur']) ?></td>
                        <td><?= dateFr($art['date_publication']) ?></td>
                        <td class="col-actions">
                            <a href="<?= SITE_URL ?>/admin/articles/edit.php?slug=<?= urlencode($art['titre']) ?>" class="btn btn-xs btn-secondary">
                                <i class="fas fa-pen"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted" style="padding:30px;">Aucun article</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const ctx = document.getElementById('donsChart');
    if (!ctx) return;

    const labels  = <?= $chart_labels ?>;
    const amounts = <?= $chart_amounts ?>;
    const counts  = <?= $chart_counts ?>;

    // Formater les labels mois
    const moisFr = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
    const niceLabels = labels.map(l => {
        const [y, m] = l.split('-');
        return moisFr[parseInt(m)-1] + ' ' + y;
    });

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: niceLabels,
            datasets: [
                {
                    label: 'Montant ($)',
                    data: amounts,
                    backgroundColor: 'rgba(0,51,153,.15)',
                    borderColor: '#003399',
                    borderWidth: 2,
                    borderRadius: 6,
                    yAxisID: 'y',
                },
                {
                    label: 'Nombre de dons',
                    data: counts,
                    type: 'line',
                    borderColor: '#D94F7A',
                    backgroundColor: 'rgba(217,79,122,.08)',
                    borderWidth: 2.5,
                    pointRadius: 5,
                    pointBackgroundColor: '#D94F7A',
                    tension: .4,
                    yAxisID: 'y1',
                    fill: true,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { font: { family: 'Inter', size: 12 }, boxWidth: 12 }},
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            if (ctx.datasetIndex === 0)
                                return ' $' + ctx.parsed.y.toLocaleString('fr-FR');
                            return ' ' + ctx.parsed.y + ' don(s)';
                        }
                    }
                }
            },
            scales: {
                y:  { position: 'left',  beginAtZero: true, grid: { color: '#F1F5F9' },
                      ticks: { font: { size: 11 }, callback: v => v.toLocaleString('fr-FR') }},
                y1: { position: 'right', beginAtZero: true, grid: { display: false },
                      ticks: { font: { size: 11 }, stepSize: 1 }},
                x:  { grid: { display: false }, ticks: { font: { size: 11 }}}
            }
        }
    });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
