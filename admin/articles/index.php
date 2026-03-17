<?php
/**
 * GSCC CMS — admin/articles/index.php
 * Liste et gestion des articles
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$page_title   = 'Articles';
$page_section = 'articles';
$breadcrumb   = [['label' => 'Articles']];

/* ── Actions bulk ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $action = $_POST['bulk_action'] ?? '';
    $ids    = array_map('intval', $_POST['ids'] ?? []);

    if ($ids && $action) {
        try {
            switch ($action) {
                case 'publish':
                    $pdo->prepare("UPDATE articles SET statut='publie' WHERE id IN (" . implode(',', $ids) . ")")->execute();
                    adminFlash('success', count($ids) . ' article(s) publié(s).');
                    break;
                case 'draft':
                    $pdo->prepare("UPDATE articles SET statut='brouillon' WHERE id IN (" . implode(',', $ids) . ")")->execute();
                    adminFlash('success', count($ids) . ' article(s) passé(s) en brouillon.');
                    break;
                case 'delete':
                    $pdo->prepare("DELETE FROM articles WHERE id IN (" . implode(',', $ids) . ")")->execute();
                    adminFlash('success', count($ids) . ' article(s) supprimé(s).');
                    break;
            }
        } catch (PDOException $e) {
            adminFlash('error', 'Erreur : ' . $e->getMessage());
        }
        header('Location: index.php');
        exit;
    }
}

/* ── Filtres & pagination ── */
$search   = trim($_GET['q'] ?? '');
$statut   = $_GET['statut'] ?? '';
$cat_id   = (int)($_GET['cat'] ?? 0);
$page     = max(1, (int)($_GET['p'] ?? 1));
$per_page = 20;
$offset   = ($page - 1) * $per_page;

$where  = ['1=1'];
$params = [];

if ($search) {
    $where[]  = "(a.titre LIKE ? OR a.tags LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($statut) {
    $where[]  = "a.statut = ?";
    $params[] = $statut;
}
if ($cat_id) {
    $where[]  = "a.categorie_id = ?";
    $params[] = $cat_id;
}

$sql_where = implode(' AND ', $where);

try {
    $total = $pdo->prepare("SELECT COUNT(*) FROM articles a WHERE $sql_where");
    $total->execute($params);
    $total = (int)$total->fetchColumn();

    $stmt = $pdo->prepare(
        "SELECT a.*, c.nom as cat_nom,
                CONCAT(u.prenom,' ',u.nom) as auteur_nom
         FROM articles a
         LEFT JOIN categories c ON a.categorie_id = c.id
         LEFT JOIN utilisateurs u ON a.auteur_id = u.id
         WHERE $sql_where
         ORDER BY a.date_creation DESC
         LIMIT $per_page OFFSET $offset"
    );
    $stmt->execute($params);
    $articles = $stmt->fetchAll();

    $categories = $pdo->query("SELECT id, nom FROM categories WHERE type='blog' ORDER BY nom")->fetchAll();

    // Stats rapides
    $st = $pdo->query("SELECT statut, COUNT(*) n FROM articles GROUP BY statut")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    $articles = $categories = [];
    $total = 0;
    $st = [];
}

$pages = (int)ceil($total / $per_page);

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Articles <span style="font-size:1rem;font-weight:400;color:var(--text-muted);">(<?= $total ?>)</span></div>
        <div class="page-subtitle">Gérer le contenu éditorial du site</div>
    </div>
    <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvel article</a>
</div>

<!-- Mini stats -->
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;">
    <?php
    $tabs = [
        ''          => ['Tous',       $total,            'secondary'],
        'publie'    => ['Publiés',    $st['publie']??0,  'success'],
        'brouillon' => ['Brouillons', $st['brouillon']??0,'warning'],
        'archive'   => ['Archivés',   $st['archive']??0, 'secondary'],
    ];
    foreach ($tabs as $val => $tab):
        $active = ($statut === $val) ? 'border-color:var(--primary);background:var(--primary-light);color:var(--primary);' : '';
    ?>
    <a href="?statut=<?= $val ?>&q=<?= urlencode($search) ?>"
       style="display:inline-flex;align-items:center;gap:7px;padding:7px 14px;border-radius:8px;border:1.5px solid var(--border);font-size:.82rem;font-weight:600;text-decoration:none;color:var(--text);<?= $active ?>">
        <?= $tab[0] ?> <span class="badge badge-<?= $tab[2] ?>"><?= $tab[1] ?></span>
    </a>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="toolbar" style="margin:0;width:100%;">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="q" class="form-control" placeholder="Rechercher un article…"
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <select name="cat" class="form-control" style="width:180px;">
                <option value="">Toutes catégories</option>
                <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $cat_id==$c['id']?'selected':'' ?>>
                    <?= htmlspecialchars($c['nom']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <select name="statut" class="form-control" style="width:150px;">
                <option value="">Tous statuts</option>
                <option value="publie"    <?= $statut=='publie'   ?'selected':'' ?>>Publié</option>
                <option value="brouillon" <?= $statut=='brouillon'?'selected':'' ?>>Brouillon</option>
                <option value="archive"   <?= $statut=='archive'  ?'selected':'' ?>>Archivé</option>
            </select>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filtrer</button>
            <?php if ($search || $statut || $cat_id): ?>
                <a href="index.php" class="btn btn-ghost"><i class="fas fa-times"></i> Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>

    <form method="POST" id="bulkForm">
        <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">

        <!-- Bulk toolbar -->
        <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;background:#FAFBFF;border-bottom:1px solid var(--border);">
            <select name="bulk_action" class="form-control" style="width:200px;">
                <option value="">— Action groupée —</option>
                <option value="publish">Publier la sélection</option>
                <option value="draft">Passer en brouillon</option>
                <option value="delete">Supprimer la sélection</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm"
                    onclick="return confirm('Appliquer cette action ?')">Appliquer</button>
            <span style="margin-left:auto;font-size:.8rem;color:var(--text-muted);" id="selCount"></span>
        </div>

        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="col-check"><input type="checkbox" id="selectAll"></th>
                        <th>Titre</th>
                        <th>Catégorie</th>
                        <th>Auteur</th>
                        <th>Statut</th>
                        <th>Vues</th>
                        <th>Date</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($articles): ?>
                    <?php foreach ($articles as $a): ?>
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="<?= $a['id'] ?>" class="row-check"></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <?php if ($a['image_couverture']): ?>
                                    <img src="<?= SITE_URL ?>/assets/<?= htmlspecialchars($a['image_couverture']) ?>"
                                         class="thumb-sm" onerror="this.style.display='none'">
                                <?php endif; ?>
                                <div>
                                    <div class="fw-600 truncate" style="max-width:280px;">
                                        <?= htmlspecialchars($a['titre']) ?>
                                    </div>
                                    <?php if ($a['est_vedette']): ?>
                                        <span class="badge badge-warning" style="font-size:.65rem;">⭐ Vedette</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-primary"><?= htmlspecialchars($a['cat_nom'] ?? '—') ?></span></td>
                        <td style="font-size:.82rem;color:var(--text-muted);"><?= htmlspecialchars($a['auteur_nom'] ?? '—') ?></td>
                        <td><?= statusBadge($a['statut']) ?></td>
                        <td style="font-size:.85rem;"><?= number_format($a['vue_compteur']) ?></td>
                        <td style="font-size:.82rem;color:var(--text-muted);white-space:nowrap;">
                            <?= $a['date_publication'] ? dateFr($a['date_publication']) : dateFr($a['date_creation']) ?>
                        </td>
                        <td class="col-actions">
                            <a href="edit.php?id=<?= $a['id'] ?>" class="btn btn-xs btn-secondary" title="Modifier">
                                <i class="fas fa-pen"></i>
                            </a>
                            <a href="<?= SITE_URL ?>/article.php?slug=<?= urlencode($a['slug']) ?>" target="_blank"
                               class="btn btn-xs btn-secondary" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button type="button" class="btn btn-xs btn-danger" title="Supprimer"
                                    onclick="deleteArticle(<?= $a['id'] ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8">
                        <div class="empty-state">
                            <i class="fas fa-newspaper"></i>
                            <h3>Aucun article trouvé</h3>
                            <p>Modifiez vos filtres ou créez votre premier article.</p>
                            <a href="create.php" class="btn btn-primary" style="margin-top:14px;">
                                <i class="fas fa-plus"></i> Créer un article
                            </a>
                        </div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between;">
        <span>Page <?= $page ?> sur <?= $pages ?> — <?= $total ?> article(s)</span>
        <div class="pagination">
            <?php
            $qs = http_build_query(['q'=>$search,'statut'=>$statut,'cat'=>$cat_id]);
            for ($i = 1; $i <= $pages; $i++):
            ?>
                <a href="?p=<?= $i ?>&<?= $qs ?>" class="page-link <?= $i==$page?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Delete modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Confirmer la suppression</span>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p>Voulez-vous vraiment supprimer cet article ? Cette action est irréversible.</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeModal()" class="btn btn-secondary">Annuler</button>
            <form method="POST" id="deleteForm" style="display:inline;">
                <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                <input type="hidden" name="bulk_action" value="delete">
                <input type="hidden" name="ids[]" id="deleteId">
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Supprimer</button>
            </form>
        </div>
    </div>
</div>

<script>
function deleteArticle(id) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteModal').classList.add('show');
}
function closeModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

// Compteur sélection
document.querySelectorAll('.row-check').forEach(cb => {
    cb.addEventListener('change', updateCount);
});
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
