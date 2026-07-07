<?php
/**
 * GSCC CMS — admin/sensibilisation/index.php
 * Gestion des activités de sensibilisation
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$page_title   = 'Sensibilisation';
$page_section = 'sensibilisation';
$breadcrumb   = [['label' => 'Sensibilisation']];

/* ── Actions POST ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($id) {
        try {
            match ($action) {
                'delete'     => $pdo->prepare("DELETE FROM sensibilisation_activites WHERE id=?")->execute([$id]),
                'toggle'     => $pdo->prepare("UPDATE sensibilisation_activites SET est_actif = 1 - est_actif WHERE id=?")->execute([$id]),
                default      => null,
            };
            adminFlash('success', 'Action effectuée.');
        } catch (PDOException $e) {
            adminFlash('error', $e->getMessage());
        }
        header('Location: index.php'); exit;
    }
}

/* ── Liste ── */
try {
    $activites = $pdo->query(
        "SELECT * FROM sensibilisation_activites ORDER BY ordre ASC, id ASC"
    )->fetchAll();
} catch (PDOException $e) {
    $activites = [];
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Sensibilisation</div>
        <div class="page-subtitle">Gérez les activités affichées sur la page de sensibilisation</div>
    </div>
    <a href="edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvelle activité</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ordre</th>
                    <th>Couleur</th>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>Statut</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($activites): ?>
                <?php foreach ($activites as $a): ?>
                <tr style="<?= !$a['est_actif'] ? 'opacity:.5;' : '' ?>">
                    <td style="color:var(--text-muted);font-size:.8rem;"><?= (int)$a['ordre'] ?></td>
                    <td>
                        <div style="width:28px;height:28px;border-radius:6px;background:<?= htmlspecialchars($a['couleur']) ?>;
                                    display:inline-block;vertical-align:middle;box-shadow:0 2px 6px rgba(0,0,0,.2);"></div>
                        <span style="font-size:.75rem;color:var(--text-muted);margin-left:6px;"><?= htmlspecialchars($a['couleur']) ?></span>
                    </td>
                    <td><strong><?= htmlspecialchars($a['titre']) ?></strong></td>
                    <td style="font-size:.82rem;color:var(--text-muted);max-width:300px;">
                        <?= htmlspecialchars(mb_substr($a['description'], 0, 90)) ?>…
                    </td>
                    <td>
                        <?php if ($a['est_actif']): ?>
                            <span class="badge badge-success">Visible</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Masquée</span>
                        <?php endif; ?>
                    </td>
                    <td class="col-actions">
                        <a href="edit.php?id=<?= $a['id'] ?>" class="btn btn-xs btn-secondary" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </a>
                        <!-- Toggle visible/masquée -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <input type="hidden" name="action" value="toggle">
                            <button type="submit" class="btn btn-xs <?= $a['est_actif'] ? 'btn-warning' : 'btn-success' ?>"
                                    title="<?= $a['est_actif'] ? 'Masquer' : 'Afficher' ?>">
                                <i class="fas fa-<?= $a['est_actif'] ? 'eye-slash' : 'eye' ?>"></i>
                            </button>
                        </form>
                        <!-- Supprimer -->
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cette activité ?')">
                            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-xs btn-danger" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">
                    <div class="empty-state">
                        <i class="fas fa-bullhorn"></i>
                        <h3>Aucune activité</h3>
                        <p>Ajoutez une première activité de sensibilisation.</p>
                        <a href="edit.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Ajouter</a>
                    </div>
                </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
