<?php
/**
 * GSCC CMS — admin/campagnes/index.php
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$page_title   = 'Campagnes & Projets';
$page_section = 'campagnes';
$breadcrumb   = [['label' => 'Campagnes & Projets']];

/* ── Actions ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $action = $_POST['bulk_action'] ?? '';
    $ids    = array_map('intval', $_POST['ids'] ?? []);
    if ($ids && $action) {
        try {
            match ($action) {
                'activate'   => $pdo->prepare("UPDATE campagnes_projets SET est_actif=1 WHERE id IN (".implode(',',$ids).")")->execute(),
                'deactivate' => $pdo->prepare("UPDATE campagnes_projets SET est_actif=0 WHERE id IN (".implode(',',$ids).")")->execute(),
                'delete'     => $pdo->prepare("DELETE FROM campagnes_projets WHERE id IN (".implode(',',$ids).")")->execute(),
                default => null,
            };
            adminFlash('success', 'Action appliquée sur '.count($ids).' élément(s).');
        } catch (PDOException $e) {
            adminFlash('error', $e->getMessage());
        }
        header('Location: index.php'); exit;
    }
}

/* ── Filtres ── */
$type   = $_GET['type']   ?? '';
$statut = $_GET['statut'] ?? '';
$search = trim($_GET['q'] ?? '');
$page   = max(1,(int)($_GET['p'] ?? 1));
$per    = 20;

$where  = ['1=1'];
$params = [];
if ($type)   { $where[] = "type=?";   $params[] = $type; }
if ($statut) { $where[] = "statut=?"; $params[] = $statut; }
if ($search) { $where[] = "titre LIKE ?"; $params[] = "%$search%"; }
$sql_where = implode(' AND ', $where);

try {
    $total = (int)$pdo->prepare("SELECT COUNT(*) FROM campagnes_projets WHERE $sql_where")->execute($params) ?
             $pdo->prepare("SELECT COUNT(*) FROM campagnes_projets WHERE $sql_where")->execute($params) && false ? 0 :
             (function() use ($pdo, $sql_where, $params) {
                 $s = $pdo->prepare("SELECT COUNT(*) FROM campagnes_projets WHERE $sql_where");
                 $s->execute($params); return (int)$s->fetchColumn();
             })() : 0;

    $cnt = $pdo->prepare("SELECT COUNT(*) FROM campagnes_projets WHERE $sql_where");
    $cnt->execute($params);
    $total = (int)$cnt->fetchColumn();

    $pages = (int)ceil($total / $per);
    $offset = ($page-1)*$per;

    $stmt = $pdo->prepare(
        "SELECT * FROM campagnes_projets WHERE $sql_where
         ORDER BY FIELD(statut,'en_cours','a_venir','termine'), date_debut DESC
         LIMIT $per OFFSET $offset"
    );
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    // Stats
    $sts = [];
    foreach ($pdo->query("SELECT statut, type, COUNT(*) n FROM campagnes_projets GROUP BY statut, type")->fetchAll() as $r) {
        $sts[$r['statut']][$r['type']] = $r['n'];
    }
} catch (PDOException $e) {
    $items = []; $total = $pages = 0; $sts = [];
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Campagnes & Projets <span style="font-size:1rem;font-weight:400;color:var(--text-muted);">(<?= $total ?>)</span></div>
        <div class="page-subtitle">Gérer les campagnes de sensibilisation et les projets</div>
    </div>
    <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouveau</a>
</div>

<!-- Tabs type -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <?php
    $tabs = [
        ''          => ['Tous',       $total],
        'campagne'  => ['Campagnes',  array_sum(array_column($sts, 'campagne'))],
        'projet'    => ['Projets',    array_sum(array_column($sts, 'projet'))],
    ];
    foreach ($tabs as $val => [$label, $nb]):
        $active = ($type === $val) ? 'border-color:var(--primary);background:var(--primary-light);color:var(--primary);' : '';
    ?>
    <a href="?type=<?= $val ?>&q=<?= urlencode($search) ?>"
       style="display:inline-flex;align-items:center;gap:8px;padding:7px 14px;border-radius:8px;border:1.5px solid var(--border);font-size:.82rem;font-weight:600;text-decoration:none;color:var(--text);<?= $active ?>">
        <?= $label ?> <span class="badge badge-secondary"><?= $nb ?></span>
    </a>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="toolbar" style="margin:0;width:100%;">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="q" class="form-control" placeholder="Rechercher…"
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
            <select name="statut" class="form-control" style="width:160px;">
                <option value="">Tous statuts</option>
                <option value="en_cours" <?= $statut=='en_cours'?'selected':'' ?>>En cours</option>
                <option value="a_venir"  <?= $statut=='a_venir' ?'selected':'' ?>>À venir</option>
                <option value="termine"  <?= $statut=='termine' ?'selected':'' ?>>Terminé</option>
            </select>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filtrer</button>
            <?php if ($search||$statut): ?>
                <a href="?type=<?= $type ?>" class="btn btn-ghost"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </form>
    </div>

    <form method="POST">
        <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
        <div style="display:flex;gap:10px;padding:10px 16px;background:#FAFBFF;border-bottom:1px solid var(--border);">
            <select name="bulk_action" class="form-control" style="width:200px;">
                <option value="">— Action groupée —</option>
                <option value="activate">Activer</option>
                <option value="deactivate">Désactiver</option>
                <option value="delete">Supprimer</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Confirmer ?')">Appliquer</button>
        </div>

        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="col-check"><input type="checkbox" id="selectAll"></th>
                        <th>Titre</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Progression</th>
                        <th>Dates</th>
                        <th>Lieu</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($items): ?>
                    <?php foreach ($items as $item): ?>
                    <tr style="<?= !$item['est_actif'] ? 'opacity:.55;' : '' ?>">
                        <td><input type="checkbox" name="ids[]" value="<?= $item['id'] ?>" class="row-check"></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <?php if ($item['image_couverture']): ?>
                                    <img src="<?= SITE_URL ?>/assets/<?= htmlspecialchars($item['image_couverture']) ?>"
                                         class="thumb-sm" onerror="this.style.display='none'">
                                <?php endif; ?>
                                <div>
                                    <div class="fw-600 truncate" style="max-width:220px;"><?= htmlspecialchars($item['titre']) ?></div>
                                    <?php if (!$item['est_actif']): ?>
                                        <span class="badge badge-secondary" style="font-size:.63rem;">Inactif</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $item['type']==='campagne'?'badge-info':'badge-primary' ?>">
                                <i class="fas fa-<?= $item['type']==='campagne'?'bullhorn':'diagram-project' ?>"></i>
                                <?= ucfirst($item['type']) ?>
                            </span>
                        </td>
                        <td><?= statusBadge($item['statut']) ?></td>
                        <td style="min-width:120px;">
                            <div class="progress" style="margin-bottom:4px;">
                                <?php
                                $prog = min(100, max(0, (int)$item['progression']));
                                $pc   = $prog >= 80 ? 'success' : ($prog >= 40 ? '' : 'warning');
                                ?>
                                <div class="progress-bar <?= $pc ?>" style="width:<?= $prog ?>%"></div>
                            </div>
                            <span style="font-size:.75rem;color:var(--text-muted);"><?= $prog ?>%</span>
                        </td>
                        <td style="font-size:.78rem;color:var(--text-muted);white-space:nowrap;">
                            <?= dateFr($item['date_debut']) ?> →<br><?= dateFr($item['date_fin']) ?>
                        </td>
                        <td style="font-size:.82rem;"><?= htmlspecialchars(truncate($item['lieu'] ?? '—', 20)) ?></td>
                        <td class="col-actions">
                            <a href="edit.php?id=<?= $item['id'] ?>" class="btn btn-xs btn-secondary" title="Modifier">
                                <i class="fas fa-pen"></i>
                            </a>
                            <a href="<?= SITE_URL ?>/campagne-detail.php?slug=<?= urlencode($item['slug']) ?>"
                               target="_blank" class="btn btn-xs btn-secondary" title="Voir"><i class="fas fa-eye"></i></a>
                            <button type="button" class="btn btn-xs btn-danger"
                                    onclick="if(confirm('Supprimer ?')){document.getElementById('del<?= $item['id'] ?>').submit();}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <form id="del<?= $item['id'] ?>" method="POST" style="display:none;">
                        <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                        <input type="hidden" name="bulk_action" value="delete">
                        <input type="hidden" name="ids[]" value="<?= $item['id'] ?>">
                    </form>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8">
                        <div class="empty-state">
                            <i class="fas fa-bullhorn"></i>
                            <h3>Aucune campagne/projet</h3>
                            <a href="create.php" class="btn btn-primary" style="margin-top:12px;">
                                <i class="fas fa-plus"></i> Créer
                            </a>
                        </div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>

    <?php if ($pages > 1): ?>
    <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between;">
        <span>Page <?= $page ?> / <?= $pages ?></span>
        <div class="pagination">
            <?php for ($i=1;$i<=$pages;$i++): ?>
                <a href="?p=<?= $i ?>&type=<?= $type ?>&statut=<?= $statut ?>&q=<?= urlencode($search) ?>"
                   class="page-link <?= $i==$page?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
