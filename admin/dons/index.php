<?php
/**
 * GSCC CMS — admin/dons/index.php
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$page_title   = 'Dons';
$page_section = 'dons';
$breadcrumb   = [['label' => 'Dons']];

/* ── Changement de statut ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $action = $_POST['action'] ?? '';
    $don_id = (int)($_POST['don_id'] ?? 0);

    if ($don_id) {
        try {
            match ($action) {
                'complete'  => $pdo->prepare("UPDATE dons SET statut='complete'  WHERE id=?")->execute([$don_id]),
                'echoue'    => $pdo->prepare("UPDATE dons SET statut='echoue'    WHERE id=?")->execute([$don_id]),
                'rembourse' => $pdo->prepare("UPDATE dons SET statut='rembourse' WHERE id=?")->execute([$don_id]),
                'delete'    => $pdo->prepare("DELETE FROM dons WHERE id=?")->execute([$don_id]),
                default     => null,
            };
            adminFlash('success', 'Don mis à jour.');
        } catch (PDOException $e) {
            adminFlash('error', $e->getMessage());
        }
        header('Location: index.php'); exit;
    }
}

/* ── Filtres ── */
$search  = trim($_GET['q'] ?? '');
$statut  = $_GET['statut'] ?? '';
$mode    = $_GET['mode']   ?? '';
$page    = max(1,(int)($_GET['p'] ?? 1));
$per     = 25;

$where  = ['1=1'];
$params = [];
if ($search) { $where[] = "(nom_donateur LIKE ? OR email_donateur LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($statut) { $where[] = "statut=?";        $params[] = $statut; }
if ($mode)   { $where[] = "mode_paiement=?"; $params[] = $mode; }
$sql_where = implode(' AND ', $where);

try {
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM dons WHERE $sql_where");
    $cnt->execute($params); $total = (int)$cnt->fetchColumn();
    $pages  = (int)ceil($total/$per);
    $offset = ($page-1)*$per;

    $stmt = $pdo->prepare(
        "SELECT d.*, u.prenom, u.nom as u_nom
         FROM dons d
         LEFT JOIN utilisateurs u ON d.utilisateur_id = u.id
         WHERE $sql_where
         ORDER BY d.date_don DESC
         LIMIT $per OFFSET $offset"
    );
    $stmt->execute($params);
    $dons = $stmt->fetchAll();

    // Totaux
    $totaux = $pdo->query(
        "SELECT
            COUNT(*) as nb_total,
            COALESCE(SUM(montant),0) as total,
            COALESCE(SUM(CASE WHEN statut='complete' THEN montant ELSE 0 END),0) as total_complete,
            COALESCE(SUM(CASE WHEN statut='en_attente' THEN montant ELSE 0 END),0) as total_attente,
            COUNT(CASE WHEN statut='complete' THEN 1 END) as nb_complete,
            COUNT(CASE WHEN statut='en_attente' THEN 1 END) as nb_attente
         FROM dons"
    )->fetch();

    // Stats par mode
    $modes_stats = $pdo->query(
        "SELECT mode_paiement, COUNT(*) n, COALESCE(SUM(montant),0) tot
         FROM dons GROUP BY mode_paiement ORDER BY tot DESC"
    )->fetchAll();

} catch (PDOException $e) {
    $dons = []; $total = $pages = 0; $totaux = []; $modes_stats = [];
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Dons <span style="font-size:1rem;font-weight:400;color:var(--text-muted);">(<?= $total ?>)</span></div>
        <div class="page-subtitle">Suivi et gestion de tous les dons reçus</div>
    </div>
    <a href="export.php" class="btn btn-secondary"><i class="fas fa-download"></i> Exporter CSV</a>
</div>

<!-- Stats financières -->
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-sack-dollar"></i></div>
        <div class="stat-info">
            <div class="stat-value">$<?= number_format($totaux['total']??0,0,',',' ') ?></div>
            <div class="stat-label">Total cumulé ($)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value">$<?= number_format($totaux['total_complete']??0,0,',',' ') ?></div>
            <div class="stat-label">Dons complétés</div>
            <div class="stat-delta up"><i class="fas fa-check"></i> <?= $totaux['nb_complete']??0 ?> transactions</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-value">$<?= number_format($totaux['total_attente']??0,0,',',' ') ?></div>
            <div class="stat-label">En attente ($)</div>
            <div class="stat-delta" style="color:var(--warning);"><?= $totaux['nb_attente']??0 ?> transaction(s)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-hand-holding-heart"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $totaux['nb_total']??0 ?></div>
            <div class="stat-label">Total transactions</div>
        </div>
    </div>
</div>

<!-- Modes de paiement -->
<?php if ($modes_stats): ?>
<div class="card" style="margin-bottom:16px;">
    <div class="card-header"><div class="card-title"><i class="fas fa-credit-card"></i> Répartition par mode de paiement</div></div>
    <div class="card-body" style="display:flex;gap:12px;flex-wrap:wrap;">
        <?php foreach ($modes_stats as $ms):
            $icons = ['paypal'=>'fab fa-paypal','stripe'=>'fas fa-credit-card',
                      'virement'=>'fas fa-building-columns','especes'=>'fas fa-money-bills',
                      'cheque'=>'fas fa-file-invoice'];
            $colors = ['paypal'=>'blue','stripe'=>'purple','virement'=>'green','especes'=>'orange','cheque'=>'teal'];
            $ic = $icons[$ms['mode_paiement']] ?? 'fas fa-money-bill';
            $cl = $colors[$ms['mode_paiement']] ?? 'blue';
        ?>
        <div style="flex:1;min-width:140px;background:var(--body-bg);border-radius:10px;padding:14px;border:1px solid var(--border);">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                <div class="stat-icon <?= $cl ?>" style="width:30px;height:30px;border-radius:6px;font-size:12px;">
                    <i class="<?= $ic ?>"></i>
                </div>
                <span style="font-size:.8rem;font-weight:600;text-transform:capitalize;"><?= $ms['mode_paiement'] ?></span>
            </div>
            <div style="font-size:1rem;font-weight:700;">$<?= number_format($ms['tot'],0,',',' ') ?></div>
            <div style="font-size:.75rem;color:var(--text-muted);"><?= $ms['n'] ?> don(s)</div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <form method="GET" class="toolbar" style="margin:0;width:100%;">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="q" class="form-control" placeholder="Nom, email…"
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <select name="statut" class="form-control" style="width:160px;">
                <option value="">Tous statuts</option>
                <option value="en_attente" <?= $statut==='en_attente'?'selected':'' ?>>En attente</option>
                <option value="complete"   <?= $statut==='complete'  ?'selected':'' ?>>Complété</option>
                <option value="echoue"     <?= $statut==='echoue'    ?'selected':'' ?>>Échoué</option>
                <option value="rembourse"  <?= $statut==='rembourse' ?'selected':'' ?>>Remboursé</option>
            </select>
            <select name="mode" class="form-control" style="width:150px;">
                <option value="">Tous modes</option>
                <option value="paypal"   <?= $mode==='paypal'  ?'selected':'' ?>>PayPal</option>
                <option value="stripe"   <?= $mode==='stripe'  ?'selected':'' ?>>Stripe</option>
                <option value="virement" <?= $mode==='virement'?'selected':'' ?>>Virement</option>
                <option value="especes"  <?= $mode==='especes' ?'selected':'' ?>>Espèces</option>
            </select>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filtrer</button>
            <?php if ($search||$statut||$mode): ?>
                <a href="index.php" class="btn btn-ghost"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Donateur</th>
                    <th>Montant</th>
                    <th>Mode</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Commentaire</th>
                    <th class="col-actions" style="width:140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($dons): ?>
                <?php foreach ($dons as $don): ?>
                <tr>
                    <td style="font-size:.78rem;color:var(--text-muted);">#<?= $don['id'] ?></td>
                    <td>
                        <div class="fw-600"><?= htmlspecialchars($don['nom_donateur'] ?? 'Anonyme') ?></div>
                        <div style="font-size:.76rem;color:var(--text-muted);"><?= htmlspecialchars($don['email_donateur'] ?? '') ?></div>
                        <?php if ($don['utilisateur_id']): ?>
                        <span class="badge badge-info" style="font-size:.62rem;">Membre</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="font-size:1rem;font-weight:700;color:var(--success);">
                            $<?= number_format($don['montant'],2,'.',' ') ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        $icons = ['paypal'=>'fab fa-paypal','stripe'=>'fas fa-credit-card',
                                  'virement'=>'fas fa-building-columns','especes'=>'fas fa-money-bills'];
                        $ic = $icons[$don['mode_paiement']] ?? 'fas fa-money-bill';
                        ?>
                        <span style="display:inline-flex;align-items:center;gap:5px;font-size:.82rem;">
                            <i class="<?= $ic ?>" style="color:var(--primary);"></i>
                            <?= ucfirst($don['mode_paiement']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-secondary"><?= ucfirst($don['type_don']) ?></span>
                    </td>
                    <td><?= statusBadge($don['statut']) ?></td>
                    <td style="font-size:.78rem;white-space:nowrap;color:var(--text-muted);">
                        <?= dateFr($don['date_don'],'d/m/Y') ?><br>
                        <?= date('H:i', strtotime($don['date_don'])) ?>
                    </td>
                    <td style="font-size:.8rem;color:var(--text-muted);">
                        <?= htmlspecialchars(truncate(html_entity_decode($don['commentaire'] ?? ''), 30)) ?>
                    </td>
                    <td class="col-actions">
                        <!-- Menu actions -->
                        <div style="display:flex;gap:4px;flex-wrap:wrap;">
                            <?php if ($don['statut'] === 'en_attente'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                                <input type="hidden" name="don_id" value="<?= $don['id'] ?>">
                                <input type="hidden" name="action" value="complete">
                                <button type="submit" class="btn btn-xs btn-success" title="Marquer complété">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                                <input type="hidden" name="don_id" value="<?= $don['id'] ?>">
                                <input type="hidden" name="action" value="echoue">
                                <button type="submit" class="btn btn-xs btn-warning" title="Marquer échoué">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <a href="view.php?id=<?= $don['id'] ?>" class="btn btn-xs btn-secondary" title="Détails">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form method="POST" style="display:inline;"
                                  onsubmit="return confirm('Supprimer ce don ?')">
                                <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                                <input type="hidden" name="don_id" value="<?= $don['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-xs btn-danger" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9">
                    <div class="empty-state">
                        <i class="fas fa-hand-holding-heart"></i>
                        <h3>Aucun don trouvé</h3>
                    </div>
                </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
    <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between;">
        <span>Page <?= $page ?> / <?= $pages ?> — <?= $total ?> don(s)</span>
        <div class="pagination">
            <?php
            $qs = http_build_query(['q'=>$search,'statut'=>$statut,'mode'=>$mode]);
            for ($i=1;$i<=$pages;$i++): ?>
                <a href="?p=<?= $i ?>&<?= $qs ?>" class="page-link <?= $i==$page?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
