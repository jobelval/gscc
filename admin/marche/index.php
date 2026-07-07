<?php
/**
 * GSCC CMS — admin/marche/index.php
 * Gestion des éditions de la Marche Contre le Cancer
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$page_title   = 'Marche Contre le Cancer';
$page_section = 'marche';
$breadcrumb   = [['label' => 'Marche Contre le Cancer']];

/* ── Actions POST ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($id && $action === 'delete') {
        try {
            $row = $pdo->prepare("SELECT image FROM marche_editions WHERE id=?");
            $row->execute([$id]);
            $img = $row->fetchColumn();
            if ($img && file_exists(ROOT_PATH . $img)) unlink(ROOT_PATH . $img);
            $pdo->prepare("DELETE FROM marche_editions WHERE id=?")->execute([$id]);
            adminFlash('success', 'Édition supprimée.');
        } catch (PDOException $e) {
            adminFlash('error', $e->getMessage());
        }
        header('Location: index.php'); exit;
    }
}

/* ── Liste ── */
try {
    $editions = $pdo->query(
        "SELECT * FROM marche_editions ORDER BY ordre ASC, annee DESC"
    )->fetchAll();
} catch (PDOException $e) {
    $editions = [];
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Marche Contre le Cancer</div>
        <div class="page-subtitle">Gérez les éditions annuelles de la marche</div>
    </div>
    <a href="edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvelle édition</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ordre</th>
                    <th>Année</th>
                    <th>Thème</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Participants</th>
                    <th>Distance</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($editions): ?>
                <?php foreach ($editions as $e): ?>
                <?php
                $statut_colors = [
                    'a_venir'  => 'badge-info',
                    'en_cours' => 'badge-success',
                    'termine'  => 'badge-secondary',
                    'annule'   => 'badge-danger',
                ];
                $statut_labels = [
                    'a_venir'  => 'À venir',
                    'en_cours' => 'En cours',
                    'termine'  => 'Terminée',
                    'annule'   => 'Annulée',
                ];
                ?>
                <tr>
                    <td style="color:var(--text-muted);font-size:.8rem;"><?= (int)$e['ordre'] ?></td>
                    <td><strong><?= htmlspecialchars($e['annee']) ?></strong></td>
                    <td style="max-width:260px;">
                        <?php if ($e['image']): ?>
                        <img src="<?= SITE_URL ?>/<?= htmlspecialchars($e['image']) ?>"
                             style="width:48px;height:36px;object-fit:cover;border-radius:4px;margin-right:8px;vertical-align:middle;"
                             alt="">
                        <?php endif; ?>
                        <?= htmlspecialchars($e['theme']) ?>
                    </td>
                    <td style="font-size:.83rem;color:var(--text-muted);">
                        <?= $e['date_evenement'] ? dateFr($e['date_evenement'], 'd/m/Y') : '—' ?>
                    </td>
                    <td><span class="badge <?= $statut_colors[$e['statut']] ?? 'badge-secondary' ?>"><?= $statut_labels[$e['statut']] ?? $e['statut'] ?></span></td>
                    <td><?= htmlspecialchars($e['participants'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($e['km'] ?? '—') ?></td>
                    <td class="col-actions">
                        <a href="edit.php?id=<?= $e['id'] ?>" class="btn btn-xs btn-secondary" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cette édition ?')">
                            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                            <input type="hidden" name="id" value="<?= $e['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-xs btn-danger" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8">
                    <div class="empty-state">
                        <i class="fas fa-person-walking"></i>
                        <h3>Aucune édition</h3>
                        <p>Ajoutez la première édition de la marche.</p>
                        <a href="edit.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Ajouter</a>
                    </div>
                </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
