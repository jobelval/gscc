<?php
/**
 * GSCC CMS — admin/utilisateurs/index.php
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$page_title   = 'Utilisateurs';
$page_section = 'utilisateurs';
$breadcrumb   = [['label' => 'Utilisateurs']];

/* ── Actions bulk ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $action = $_POST['bulk_action'] ?? '';
    $ids    = array_map('intval', $_POST['ids'] ?? []);
    $single_id = (int)($_POST['single_id'] ?? 0);

    // Action sur un seul utilisateur via modal
    if ($single_id && $action) {
        $ids = [$single_id];
    }

    if ($ids && $action) {
        // Empêcher de se supprimer soi-même
        $ids = array_filter($ids, fn($i) => $i !== (int)$_SESSION['admin_id']);
        if ($ids) {
            $in = implode(',', $ids);
            try {
                match($action) {
                    'activate'   => $pdo->prepare("UPDATE utilisateurs SET statut='actif' WHERE id IN ($in)")->execute(),
                    'deactivate' => $pdo->prepare("UPDATE utilisateurs SET statut='inactif' WHERE id IN ($in)")->execute(),
                    'make_admin' => $pdo->prepare("UPDATE utilisateurs SET role='admin' WHERE id IN ($in)")->execute(),
                    'make_mod'   => $pdo->prepare("UPDATE utilisateurs SET role='moderateur' WHERE id IN ($in)")->execute(),
                    'make_member'=> $pdo->prepare("UPDATE utilisateurs SET role='membre' WHERE id IN ($in)")->execute(),
                    'delete'     => $pdo->prepare("DELETE FROM utilisateurs WHERE id IN ($in)")->execute(),
                    default      => null,
                };
                adminFlash('success', 'Action appliquée sur ' . count($ids) . ' utilisateur(s).');
            } catch (PDOException $e) {
                adminFlash('error', $e->getMessage());
            }
        } else {
            adminFlash('error', 'Impossible de modifier votre propre compte via cette action.');
        }
        header('Location: index.php'); exit;
    }
}

/* ── Filtres ── */
$search  = trim($_GET['q'] ?? '');
$role    = $_GET['role']   ?? '';
$statut  = $_GET['statut'] ?? '';
$page    = max(1, (int)($_GET['p'] ?? 1));
$per     = 25;

$where  = ['1=1'];
$params = [];

if ($search) {
    $where[]  = "(email LIKE ? OR nom LIKE ? OR prenom LIKE ? OR telephone LIKE ?)";
    $params   = array_merge($params, ["%$search%","%$search%","%$search%","%$search%"]);
}
if ($role)   { $where[] = "role=?";   $params[] = $role; }
if ($statut) { $where[] = "statut=?"; $params[] = $statut; }

$sql_where = implode(' AND ', $where);

try {
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE $sql_where");
    $cnt->execute($params);
    $total = (int)$cnt->fetchColumn();
    $pages = (int)ceil($total / $per);
    $offset = ($page-1)*$per;

    $stmt = $pdo->prepare(
        "SELECT u.*,
                (SELECT COUNT(*) FROM dons WHERE utilisateur_id = u.id) as nb_dons,
                (SELECT COALESCE(SUM(montant),0) FROM dons WHERE utilisateur_id = u.id) as total_dons
         FROM utilisateurs u
         WHERE $sql_where
         ORDER BY u.date_inscription DESC
         LIMIT $per OFFSET $offset"
    );
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    // Stats
    $role_stats = [];
    foreach ($pdo->query("SELECT role, COUNT(*) n FROM utilisateurs GROUP BY role")->fetchAll() as $r) {
        $role_stats[$r['role']] = $r['n'];
    }
    $total_all = array_sum($role_stats);
} catch (PDOException $e) {
    $users = []; $total = $pages = 0; $role_stats = []; $total_all = 0;
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Utilisateurs <span style="font-size:1rem;font-weight:400;color:var(--text-muted);">(<?= $total ?>)</span></div>
        <div class="page-subtitle">Gérer les membres, modérateurs et administrateurs</div>
    </div>
    <a href="create.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Ajouter</a>
</div>

<!-- Stats rôles -->
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="stat-info"><div class="stat-value"><?= $total_all ?></div><div class="stat-label">Total membres</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon rose"><i class="fas fa-user-shield"></i></div>
        <div class="stat-info"><div class="stat-value"><?= $role_stats['admin']??0 ?></div><div class="stat-label">Administrateurs</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-user-gear"></i></div>
        <div class="stat-info"><div class="stat-value"><?= $role_stats['moderateur']??0 ?></div><div class="stat-label">Modérateurs</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
        <div class="stat-info"><div class="stat-value"><?= $role_stats['membre']??0 ?></div><div class="stat-label">Membres actifs</div></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="toolbar" style="margin:0;width:100%;">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="q" class="form-control" placeholder="Email, nom, téléphone…"
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <select name="role" class="form-control" style="width:160px;">
                <option value="">Tous rôles</option>
                <option value="admin"      <?= $role==='admin'      ?'selected':'' ?>>Admin</option>
                <option value="moderateur" <?= $role==='moderateur' ?'selected':'' ?>>Modérateur</option>
                <option value="membre"     <?= $role==='membre'     ?'selected':'' ?>>Membre</option>
            </select>
            <select name="statut" class="form-control" style="width:150px;">
                <option value="">Tous statuts</option>
                <option value="actif"   <?= $statut==='actif'  ?'selected':'' ?>>Actif</option>
                <option value="inactif" <?= $statut==='inactif'?'selected':'' ?>>Inactif</option>
            </select>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filtrer</button>
            <?php if ($search||$role||$statut): ?>
                <a href="index.php" class="btn btn-ghost"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </form>
    </div>

    <form method="POST" id="bulkForm">
        <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
        <div style="display:flex;gap:10px;padding:10px 16px;background:#FAFBFF;border-bottom:1px solid var(--border);">
            <select name="bulk_action" class="form-control" style="width:220px;">
                <option value="">— Action groupée —</option>
                <option value="activate">Activer</option>
                <option value="deactivate">Désactiver</option>
                <option value="make_admin">Passer en Admin</option>
                <option value="make_mod">Passer en Modérateur</option>
                <option value="make_member">Passer en Membre</option>
                <option value="delete">Supprimer</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm"
                    onclick="return confirm('Appliquer cette action sur la sélection ?')">Appliquer</button>
            <span style="margin-left:auto;font-size:.8rem;color:var(--text-muted);" id="selCount"></span>
        </div>

        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="col-check"><input type="checkbox" id="selectAll"></th>
                        <th>Utilisateur</th>
                        <th>Contact</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Dons</th>
                        <th>Inscrit le</th>
                        <th>Dernière connexion</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($users): ?>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <?php if ($u['id'] != $_SESSION['admin_id']): ?>
                            <input type="checkbox" name="ids[]" value="<?= $u['id'] ?>" class="row-check">
                            <?php else: ?>
                            <span title="Votre compte" style="color:#CBD5E1;font-size:12px;">👤</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <?php if ($u['photo_url']): ?>
                                    <img src="<?= htmlspecialchars($u['photo_url']) ?>" class="avatar avatar-sm"
                                         onerror="this.style.display='none'">
                                <?php else: ?>
                                    <div class="avatar avatar-sm">
                                        <?= strtoupper(substr($u['prenom']??'U',0,1).substr($u['nom']??'',0,1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-600">
                                        <?= htmlspecialchars(trim(($u['prenom']??'').' '.($u['nom']??''))) ?: '<em style="color:var(--text-muted)">Sans nom</em>' ?>
                                        <?php if ($u['id'] == $_SESSION['admin_id']): ?>
                                            <span class="badge badge-primary" style="font-size:.63rem;">Vous</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size:.78rem;color:var(--text-muted);"><?= htmlspecialchars($u['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:.82rem;color:var(--text-muted);">
                            <?= htmlspecialchars($u['telephone'] ?? '—') ?><br>
                            <span style="font-size:.75rem;"><?= htmlspecialchars($u['ville'] ?? '') ?></span>
                        </td>
                        <td><?= statusBadge($u['role']) ?></td>
                        <td><?= statusBadge($u['statut']) ?></td>
                        <td style="font-size:.82rem;">
                            <span class="fw-600" style="color:var(--success);"><?= $u['nb_dons'] ?></span>
                            <span style="color:var(--text-muted);">don(s)</span>
                            <?php if ($u['total_dons'] > 0): ?>
                            <br><span style="font-size:.75rem;color:var(--text-muted);"><?= number_format($u['total_dons'],0,',',' ') ?> HTG</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:.78rem;color:var(--text-muted);"><?= dateFr($u['date_inscription']) ?></td>
                        <td style="font-size:.78rem;color:var(--text-muted);">
                            <?= $u['derniere_connexion'] ? dateFr($u['derniere_connexion'],'d/m/Y H:i') : '—' ?>
                        </td>
                        <td class="col-actions">
                            <a href="edit.php?id=<?= $u['id'] ?>" class="btn btn-xs btn-secondary" title="Modifier">
                                <i class="fas fa-pen"></i>
                            </a>
                            <a href="view.php?id=<?= $u['id'] ?>" class="btn btn-xs btn-secondary" title="Détails">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($u['id'] != $_SESSION['admin_id']): ?>
                            <button type="button" class="btn btn-xs btn-danger"
                                    onclick="quickAction(<?= $u['id'] ?>,'delete')" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9">
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <h3>Aucun utilisateur trouvé</h3>
                        </div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>

    <?php if ($pages > 1): ?>
    <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between;">
        <span>Page <?= $page ?> / <?= $pages ?> — <?= $total ?> utilisateur(s)</span>
        <div class="pagination">
            <?php
            $qs = http_build_query(['q'=>$search,'role'=>$role,'statut'=>$statut]);
            for ($i=1;$i<=$pages;$i++): ?>
                <a href="?p=<?= $i ?>&<?= $qs ?>" class="page-link <?= $i==$page?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Quick action form -->
<form method="POST" id="quickActionForm" style="display:none;">
    <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
    <input type="hidden" name="single_id" id="quickActionId">
    <input type="hidden" name="bulk_action" id="quickActionType">
</form>

<script>
function quickAction(id, action) {
    const msgs = {
        delete: 'Supprimer définitivement cet utilisateur ?',
    };
    if (!confirm(msgs[action] || 'Confirmer ?')) return;
    document.getElementById('quickActionId').value = id;
    document.getElementById('quickActionType').value = action;
    document.getElementById('quickActionForm').submit();
}

// Sélection
document.querySelectorAll('.row-check').forEach(cb => cb.addEventListener('change', updateCount));
document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    updateCount();
});
function updateCount() {
    const n = document.querySelectorAll('.row-check:checked').length;
    document.getElementById('selCount').textContent = n > 0 ? n + ' sélectionné(s)' : '';
}
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
