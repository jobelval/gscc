<?php
/**
 * GSCC CMS — admin/forum/index.php
 * Modération du forum
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$page_title   = 'Forum';
$page_section = 'forum';
$breadcrumb   = [['label' => 'Forum']];

/* ── Actions ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $action = $_POST['action'] ?? '';
    $sid    = (int)($_POST['sid'] ?? 0); // sujet id
    $rid    = (int)($_POST['rid'] ?? 0); // réponse id

    try {
        if ($sid) {
            match($action){
                'pin'     => $pdo->prepare("UPDATE forum_sujets SET est_epingle=1 WHERE id=?")->execute([$sid]),
                'unpin'   => $pdo->prepare("UPDATE forum_sujets SET est_epingle=0 WHERE id=?")->execute([$sid]),
                'close'   => $pdo->prepare("UPDATE forum_sujets SET est_ferme=1 WHERE id=?")->execute([$sid]),
                'open'    => $pdo->prepare("UPDATE forum_sujets SET est_ferme=0 WHERE id=?")->execute([$sid]),
                'resolve' => $pdo->prepare("UPDATE forum_sujets SET est_resolu=1 WHERE id=?")->execute([$sid]),
                'del_subject' => (function() use ($pdo,$sid){
                    $pdo->prepare("DELETE FROM forum_reponses WHERE sujet_id=?")->execute([$sid]);
                    $pdo->prepare("DELETE FROM forum_sujets WHERE id=?")->execute([$sid]);
                })(),
                default   => null,
            };
            adminFlash('success','Action appliquée sur le sujet #'.$sid);
        }
        if ($rid && $action==='del_reply') {
            $pdo->prepare("DELETE FROM forum_reponses WHERE id=?")->execute([$rid]);
            adminFlash('success','Réponse supprimée.');
        }
    } catch (PDOException $e) { adminFlash('error',$e->getMessage()); }
    header('Location: index.php'); exit;
}

/* ── Onglets ── */
$tab    = $_GET['tab'] ?? 'sujets';
$search = trim($_GET['q'] ?? '');
$page   = max(1,(int)($_GET['p']??1));
$per    = 20;

try {
    // Stats globales
    $nb_sujets   = (int)$pdo->query("SELECT COUNT(*) FROM forum_sujets")->fetchColumn();
    $nb_reponses = (int)$pdo->query("SELECT COUNT(*) FROM forum_reponses")->fetchColumn();
    $nb_cats     = (int)$pdo->query("SELECT COUNT(*) FROM forum_categories")->fetchColumn();
    $nb_epingles = (int)$pdo->query("SELECT COUNT(*) FROM forum_sujets WHERE est_epingle=1")->fetchColumn();

    if ($tab==='sujets') {
        $where=['1=1']; $params=[];
        if ($search) { $where[]="(s.titre LIKE ? OR u.nom LIKE ? OR u.prenom LIKE ?)"; $p="%$search%"; $params=array_merge($params,[$p,$p,$p]); }
        $sw=implode(' AND ',$where);
        $cnt=$pdo->prepare("SELECT COUNT(*) FROM forum_sujets s LEFT JOIN utilisateurs u ON s.auteur_id=u.id WHERE $sw");
        $cnt->execute($params); $total=(int)$cnt->fetchColumn();
        $pages=(int)ceil($total/$per); $offset=($page-1)*$per;
        $stmt=$pdo->prepare(
            "SELECT s.*,fc.nom cat_nom,
                    CONCAT(u.prenom,' ',u.nom) auteur_nom, u.email auteur_email,
                    (SELECT COUNT(*) FROM forum_reponses r WHERE r.sujet_id=s.id) nb_rep
             FROM forum_sujets s
             LEFT JOIN forum_categories fc ON s.categorie_id=fc.id
             LEFT JOIN utilisateurs u ON s.auteur_id=u.id
             WHERE $sw ORDER BY s.est_epingle DESC,s.date_creation DESC LIMIT $per OFFSET $offset"
        );
        $stmt->execute($params); $items=$stmt->fetchAll();

        // Pré-charger les réponses pour tous les sujets visibles
        $replies_map = [];
        if ($items) {
            $ids = implode(',', array_map(fn($s)=>(int)$s['id'], $items));
            $rep_stmt = $pdo->query(
                "SELECT r.*, CONCAT(u.prenom,' ',u.nom) auteur_nom
                 FROM forum_reponses r
                 LEFT JOIN utilisateurs u ON r.auteur_id=u.id
                 WHERE r.sujet_id IN ($ids)
                 ORDER BY r.date_creation ASC"
            );
            foreach ($rep_stmt->fetchAll() as $rep) {
                $replies_map[$rep['sujet_id']][] = $rep;
            }
        }
    } else {
        // Catégories
        $cats=$pdo->query(
            "SELECT fc.*,(SELECT COUNT(*) FROM forum_sujets s WHERE s.categorie_id=fc.id) nb_sujets
             FROM forum_categories fc ORDER BY fc.ordre ASC"
        )->fetchAll();
    }
} catch (PDOException $e) { $items=[]; $cats=[]; $total=0; $pages=0; $nb_sujets=$nb_reponses=$nb_cats=$nb_epingles=0; }

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Forum</div>
        <div class="page-subtitle">Modérer les sujets et gérer les catégories</div>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px;">
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-comments"></i></div><div class="stat-info"><div class="stat-value"><?= $nb_sujets ?></div><div class="stat-label">Sujets</div></div></div>
    <div class="stat-card"><div class="stat-icon green"><i class="fas fa-reply"></i></div><div class="stat-info"><div class="stat-value"><?= $nb_reponses ?></div><div class="stat-label">Réponses</div></div></div>
    <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-folder"></i></div><div class="stat-info"><div class="stat-value"><?= $nb_cats ?></div><div class="stat-label">Catégories</div></div></div>
    <div class="stat-card"><div class="stat-icon rose"><i class="fas fa-thumbtack"></i></div><div class="stat-info"><div class="stat-value"><?= $nb_epingles ?></div><div class="stat-label">Épinglés</div></div></div>
</div>

<!-- Onglets -->
<div style="display:flex;gap:0;margin-bottom:20px;border-bottom:2px solid var(--border);">
    <a href="?tab=sujets" style="padding:10px 20px;font-weight:600;font-size:.9rem;text-decoration:none;border-bottom:2px solid <?= $tab==='sujets'?'var(--primary)':'transparent' ?>;color:<?= $tab==='sujets'?'var(--primary)':'var(--text-muted)' ?>;margin-bottom:-2px;">
        <i class="fas fa-list"></i> Sujets (<?= $nb_sujets ?>)
    </a>
    <a href="?tab=categories" style="padding:10px 20px;font-weight:600;font-size:.9rem;text-decoration:none;border-bottom:2px solid <?= $tab==='categories'?'var(--primary)':'transparent' ?>;color:<?= $tab==='categories'?'var(--primary)':'var(--text-muted)' ?>;margin-bottom:-2px;">
        <i class="fas fa-folder"></i> Catégories (<?= $nb_cats ?>)
    </a>
</div>

<?php if ($tab==='sujets'): ?>

<div class="card">
    <div class="card-header">
        <form method="GET" class="toolbar" style="margin:0;width:100%;">
            <div class="search-box"><i class="fas fa-search"></i>
                <input type="text" name="q" class="form-control" placeholder="Titre, auteur…" value="<?= htmlspecialchars($search) ?>">
            </div>
            <input type="hidden" name="tab" value="sujets">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
            <?php if ($search): ?><a href="?tab=sujets" class="btn btn-ghost"><i class="fas fa-times"></i></a><?php endif; ?>
        </form>
    </div>
    <div class="table-wrap">
        <table class="admin-table">
            <thead><tr>
                <th>Titre</th><th>Catégorie</th><th>Auteur</th>
                <th>Réponses</th><th>Vues</th><th>Flags</th><th>Date</th>
                <th class="col-actions" style="width:160px;">Actions</th>
            </tr></thead>
            <tbody>
            <?php if (!empty($items)): foreach ($items as $s): ?>
            <tr>
                <td>
                    <div class="fw-600" style="max-width:250px;">
                        <?php if ($s['est_epingle']): ?><i class="fas fa-thumbtack" style="color:var(--primary);font-size:11px;margin-right:4px;"></i><?php endif; ?>
                        <?php if ($s['est_ferme']): ?><i class="fas fa-lock" style="color:var(--warning);font-size:11px;margin-right:4px;"></i><?php endif; ?>
                        <?= htmlspecialchars(truncate($s['titre'],50)) ?>
                    </div>
                </td>
                <td><span class="badge badge-info"><?= htmlspecialchars($s['cat_nom']??'—') ?></span></td>
                <td style="font-size:.82rem;">
                    <div><?= htmlspecialchars($s['auteur_nom']??'—') ?></div>
                    <div style="font-size:.74rem;color:var(--text-muted);"><?= htmlspecialchars($s['auteur_email']??'') ?></div>
                </td>
                <td style="text-align:center;">
                    <?php if ($s['nb_rep'] > 0): ?>
                    <button type="button" onclick="toggleReplies(<?= $s['id'] ?>)"
                            class="badge badge-secondary" style="cursor:pointer;border:none;background:var(--secondary);">
                        <i class="fas fa-reply" style="font-size:.65rem;"></i> <?= $s['nb_rep'] ?>
                    </button>
                    <?php else: ?>
                    <span class="badge badge-secondary">0</span>
                    <?php endif; ?>
                </td>
                <td style="font-size:.84rem;"><?= number_format($s['vue_compteur']) ?></td>
                <td>
                    <?php if ($s['est_resolu']): ?><span class="badge badge-success" style="font-size:.65rem;">✅ Résolu</span><?php endif; ?>
                    <?php if ($s['est_ferme']): ?><span class="badge badge-warning" style="font-size:.65rem;">🔒 Fermé</span><?php endif; ?>
                </td>
                <td style="font-size:.78rem;color:var(--text-muted);"><?= dateFr($s['date_creation'],'d/m/Y') ?></td>
                <td class="col-actions">
                    <div style="display:flex;gap:3px;flex-wrap:wrap;">
                        <!-- Épingler/désépingler -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                            <input type="hidden" name="sid" value="<?= $s['id'] ?>">
                            <input type="hidden" name="action" value="<?= $s['est_epingle']?'unpin':'pin' ?>">
                            <button type="submit" class="btn btn-xs <?= $s['est_epingle']?'btn-primary':'btn-secondary' ?>" title="<?= $s['est_epingle']?'Désépingler':'Épingler' ?>">
                                <i class="fas fa-thumbtack"></i>
                            </button>
                        </form>
                        <!-- Fermer/ouvrir -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                            <input type="hidden" name="sid" value="<?= $s['id'] ?>">
                            <input type="hidden" name="action" value="<?= $s['est_ferme']?'open':'close' ?>">
                            <button type="submit" class="btn btn-xs <?= $s['est_ferme']?'btn-warning':'btn-secondary' ?>" title="<?= $s['est_ferme']?'Ouvrir':'Fermer' ?>">
                                <i class="fas fa-<?= $s['est_ferme']?'lock-open':'lock' ?>"></i>
                            </button>
                        </form>
                        <!-- Supprimer -->
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce sujet et toutes ses réponses ?')">
                            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                            <input type="hidden" name="sid" value="<?= $s['id'] ?>">
                            <input type="hidden" name="action" value="del_subject">
                            <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php if (!empty($replies_map[$s['id']])): ?>
            <tr id="replies-<?= $s['id'] ?>" style="display:none;background:#FAFBFF;">
                <td colspan="8" style="padding:0;">
                    <div style="padding:10px 16px 14px 40px;">
                        <div style="font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px;">
                            <i class="fas fa-reply"></i> Réponses (<?= count($replies_map[$s['id']]) ?>)
                        </div>
                        <?php foreach ($replies_map[$s['id']] as $rep): ?>
                        <div style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-bottom:1px solid var(--border);">
                            <div style="flex:1;min-width:0;">
                                <span style="font-weight:600;font-size:.82rem;">
                                    <?= htmlspecialchars($rep['auteur_nom'] ?? 'Anonyme') ?>
                                </span>
                                <span style="font-size:.75rem;color:var(--text-muted);margin-left:8px;">
                                    <?= dateFr($rep['date_creation'],'d/m/Y H:i') ?>
                                </span>
                                <div style="font-size:.84rem;color:var(--text);margin-top:4px;line-height:1.5;">
                                    <?= htmlspecialchars(truncate($rep['contenu'] ?? '',200)) ?>
                                </div>
                            </div>
                            <form method="POST" style="flex-shrink:0;" onsubmit="return confirm('Supprimer cette réponse ?')">
                                <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                                <input type="hidden" name="rid" value="<?= $rep['id'] ?>">
                                <input type="hidden" name="action" value="del_reply">
                                <button type="submit" class="btn btn-xs btn-danger" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
            <?php endforeach; else: ?>
            <tr><td colspan="8"><div class="empty-state"><i class="fas fa-comments"></i><h3>Aucun sujet</h3></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (!empty($pages) && $pages>1): ?>
    <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between;">
        <span>Page <?= $page ?>/<?= $pages ?></span>
        <div class="pagination"><?php for ($i=1;$i<=$pages;$i++): ?>
            <a href="?tab=sujets&p=<?= $i ?>&q=<?= urlencode($search) ?>" class="page-link <?= $i==$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?></div>
    </div>
    <?php endif; ?>
</div>

<?php else: ?>

<!-- Catégories -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-folder"></i> Catégories du forum</div>
    </div>
    <div class="table-wrap">
        <table class="admin-table">
            <thead><tr><th>#</th><th>Nom</th><th>Description</th><th>Sujets</th><th>Ordre</th><th>Actif</th><th class="col-actions">Actions</th></tr></thead>
            <tbody>
            <?php if (!empty($cats)): foreach ($cats as $cat): ?>
            <tr>
                <td style="color:var(--text-muted);font-size:.78rem;">#<?= $cat['id'] ?></td>
                <td class="fw-600"><?= htmlspecialchars($cat['nom']) ?></td>
                <td style="font-size:.84rem;color:var(--text-muted);"><?= htmlspecialchars(truncate($cat['description']??'—',60)) ?></td>
                <td><span class="badge badge-primary"><?= $cat['nb_sujets'] ?></span></td>
                <td style="font-size:.84rem;"><?= $cat['ordre'] ?></td>
                <td><?= $cat['est_actif'] ? '<span class="badge badge-success">Oui</span>' : '<span class="badge badge-secondary">Non</span>' ?></td>
                <td class="col-actions">
                    <button type="button" class="btn btn-xs btn-secondary"
                            onclick="editCat(<?= $cat['id'] ?>,'<?= htmlspecialchars(addslashes($cat['nom'])) ?>','<?= htmlspecialchars(addslashes($cat['description']??'')) ?>',<?= $cat['ordre'] ?>,<?= $cat['est_actif'] ?>)">
                        <i class="fas fa-pen"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="7"><div class="empty-state"><i class="fas fa-folder-open"></i><h3>Aucune catégorie</h3></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal catégorie -->
<div class="modal-overlay" id="catModal">
    <div class="modal" style="max-width:440px;">
        <div class="modal-header">
            <span class="modal-title">Modifier la catégorie</span>
            <button class="modal-close" onclick="document.getElementById('catModal').classList.remove('show')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="categories.php">
            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
            <input type="hidden" name="cat_id" id="catId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" id="catNom" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="catDesc" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Ordre</label>
                        <input type="number" name="ordre" id="catOrdre" class="form-control" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Actif</label>
                        <select name="est_actif" id="catActif" class="form-control">
                            <option value="1">Oui</option>
                            <option value="0">Non</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('catModal').classList.remove('show')" class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>
<script>
function editCat(id,nom,desc,ordre,actif){
    document.getElementById('catId').value=id;
    document.getElementById('catNom').value=nom;
    document.getElementById('catDesc').value=desc;
    document.getElementById('catOrdre').value=ordre;
    document.getElementById('catActif').value=actif;
    document.getElementById('catModal').classList.add('show');
}
</script>
<?php endif; ?>

<script>
function toggleReplies(id) {
    var row = document.getElementById('replies-' + id);
    if (!row) return;
    row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
}
</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
