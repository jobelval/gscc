<?php
/**
 * GSCC CMS — admin/galerie/index.php
 * Gestion de la galerie photos & vidéos
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$page_title   = 'Galerie';
$page_section = 'galerie';
$breadcrumb   = [['label' => 'Galerie']];

/* ── Actions bulk ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $action = $_POST['bulk_action'] ?? $_POST['action'] ?? '';
    $ids    = array_map('intval', $_POST['ids'] ?? []);
    $gid    = (int)($_POST['gid'] ?? 0);

    if ($gid && $action === 'toggle_public') {
        $cur = $pdo->prepare("SELECT est_public FROM galerie WHERE id=?"); $cur->execute([$gid]);
        $cur = $cur->fetchColumn();
        $pdo->prepare("UPDATE galerie SET est_public=? WHERE id=?")->execute([$cur ? 0 : 1, $gid]);
        adminFlash('success', 'Visibilité mise à jour.');
        header('Location: index.php'); exit;
    }

    if ($gid && $action === 'delete_one') {
        $row = $pdo->prepare("SELECT url_fichier,url_thumbnail,type FROM galerie WHERE id=?");
        $row->execute([$gid]); $r = $row->fetch();
        if ($r && $r['type'] === 'photo') {
            foreach ([$r['url_fichier'], $r['url_thumbnail']] as $f) {
                if ($f && file_exists(ROOT_PATH . $f)) @unlink(ROOT_PATH . $f);
            }
        }
        $pdo->prepare("DELETE FROM galerie WHERE id=?")->execute([$gid]);
        adminFlash('success', 'Média supprimé.');
        header('Location: index.php'); exit;
    }

    if ($ids && $action === 'delete') {
        foreach ($ids as $i) {
            $row = $pdo->prepare("SELECT url_fichier,url_thumbnail,type FROM galerie WHERE id=?");
            $row->execute([$i]); $r = $row->fetch();
            if ($r && $r['type'] === 'photo') {
                foreach ([$r['url_fichier'], $r['url_thumbnail']] as $f) {
                    if ($f && file_exists(ROOT_PATH . $f)) @unlink(ROOT_PATH . $f);
                }
            }
        }
        $pdo->prepare("DELETE FROM galerie WHERE id IN (" . implode(',', $ids) . ")")->execute();
        adminFlash('success', count($ids) . ' média(s) supprimé(s).');
        header('Location: index.php'); exit;
    }

    if ($ids && $action === 'set_public') {
        $pdo->prepare("UPDATE galerie SET est_public=1 WHERE id IN (" . implode(',', $ids) . ")")->execute();
        adminFlash('success', count($ids) . ' média(s) rendu(s) public(s).');
        header('Location: index.php'); exit;
    }
}

/* ── Filtres ── */
$type   = $_GET['type']   ?? '';
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['p'] ?? 1));
$per    = 24;
$where  = ['1=1']; $params = [];
if ($type)   { $where[] = "type=?";    $params[] = $type; }
if ($search) { $where[] = "(titre LIKE ? OR description LIKE ?)"; $p = "%$search%"; $params[] = $p; $params[] = $p; }
$sw = implode(' AND ', $where);

try {
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM galerie WHERE $sw"); $cnt->execute($params);
    $total = (int)$cnt->fetchColumn();
    $pages = (int)ceil($total / $per); $offset = ($page - 1) * $per;
    $stmt = $pdo->prepare("SELECT g.*,CONCAT(u.prenom,' ',u.nom) uploaded_by_name FROM galerie g LEFT JOIN utilisateurs u ON g.uploaded_by=u.id WHERE $sw ORDER BY g.date_upload DESC LIMIT $per OFFSET $offset");
    $stmt->execute($params); $medias = $stmt->fetchAll();
    $nb_photos = (int)$pdo->query("SELECT COUNT(*) FROM galerie WHERE type='photo'")->fetchColumn();
    $nb_videos = (int)$pdo->query("SELECT COUNT(*) FROM galerie WHERE type='video'")->fetchColumn();
    $nb_public = (int)$pdo->query("SELECT COUNT(*) FROM galerie WHERE est_public=1")->fetchColumn();
} catch (PDOException $e) { $medias = []; $total = $pages = 0; $nb_photos = $nb_videos = $nb_public = 0; }

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Galerie médias <span style="font-size:1rem;font-weight:400;color:var(--text-muted);">(<?= $total ?>)</span></div>
        <div class="page-subtitle">Photos et vidéos du GSCC</div>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="upload.php" class="btn btn-primary"><i class="fas fa-upload"></i> Ajouter des photos</a>
        <a href="add-video.php" class="btn btn-secondary"><i class="fab fa-youtube"></i> Ajouter vidéo</a>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px;">
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-images"></i></div><div class="stat-info"><div class="stat-value"><?= $total ?></div><div class="stat-label">Total médias</div></div></div>
    <div class="stat-card"><div class="stat-icon green"><i class="fas fa-camera"></i></div><div class="stat-info"><div class="stat-value"><?= $nb_photos ?></div><div class="stat-label">Photos</div></div></div>
    <div class="stat-card"><div class="stat-icon rose"><i class="fab fa-youtube"></i></div><div class="stat-info"><div class="stat-value"><?= $nb_videos ?></div><div class="stat-label">Vidéos YouTube</div></div></div>
    <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-eye"></i></div><div class="stat-info"><div class="stat-value"><?= $nb_public ?></div><div class="stat-label">Publics</div></div></div>
</div>

<!-- Tabs type -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <?php foreach ([''=> ['Tous',$total,'secondary'],'photo'=>['Photos',$nb_photos,'success'],'video'=>['Vidéos',$nb_videos,'danger']] as $v=>[$l,$n,$t]):
        $active=($type===$v)?'border-color:var(--primary);background:var(--primary-light);color:var(--primary);':''; ?>
    <a href="?type=<?= $v ?>&q=<?= urlencode($search) ?>" style="display:inline-flex;align-items:center;gap:8px;padding:7px 14px;border-radius:8px;border:1.5px solid var(--border);font-size:.82rem;font-weight:600;text-decoration:none;color:var(--text);<?= $active ?>">
        <?= $l ?> <span class="badge badge-<?= $t ?>"><?= $n ?></span>
    </a>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="toolbar" style="margin:0;width:100%;">
            <div class="search-box"><i class="fas fa-search"></i>
                <input type="text" name="q" class="form-control" placeholder="Titre, description…" value="<?= htmlspecialchars($search) ?>">
            </div>
            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
            <?php if ($search): ?><a href="?type=<?= $type ?>" class="btn btn-ghost"><i class="fas fa-times"></i></a><?php endif; ?>
        </form>
    </div>

    <form method="POST">
        <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
        <div style="display:flex;gap:10px;padding:10px 16px;background:#FAFBFF;border-bottom:1px solid var(--border);">
            <select name="bulk_action" class="form-control" style="width:200px;">
                <option value="">— Action groupée —</option>
                <option value="set_public">Rendre publics</option>
                <option value="delete">Supprimer</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Confirmer ?')">Appliquer</button>
            <span style="margin-left:auto;font-size:.8rem;color:var(--text-muted);" id="selCount"></span>
        </div>

        <!-- Grille médias -->
        <div style="padding:16px;">
            <?php if ($medias): ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
                <?php foreach ($medias as $m): ?>
                <div style="border:1px solid var(--border);border-radius:10px;overflow:hidden;background:var(--card-bg);position:relative;transition:box-shadow .18s;" class="media-card">

                    <!-- Checkbox -->
                    <div style="position:absolute;top:8px;left:8px;z-index:2;">
                        <input type="checkbox" name="ids[]" value="<?= $m['id'] ?>" class="row-check"
                               style="width:18px;height:18px;cursor:pointer;">
                    </div>

                    <!-- Badge visibilité -->
                    <div style="position:absolute;top:8px;right:8px;z-index:2;">
                        <span class="badge <?= $m['est_public']?'badge-success':'badge-secondary' ?>" style="font-size:.62rem;">
                            <?= $m['est_public']?'🌐 Public':'🔒 Privé' ?>
                        </span>
                    </div>

                    <!-- Thumbnail -->
                    <div style="height:150px;background:var(--body-bg);overflow:hidden;position:relative;">
                        <?php if ($m['type'] === 'photo'): ?>
                            <?php $imgUrl = SITE_URL . '/' . ($m['url_thumbnail'] ?: $m['url_fichier']); ?>
                            <img src="<?= htmlspecialchars($imgUrl) ?>"
                                 style="width:100%;height:100%;object-fit:cover;"
                                 onerror="this.src='';this.parentElement.style.background='#E2E8F0';this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;font-size:36px;color:#94A3B8;\'>📷</div>';"
                                 alt="">
                        <?php else: ?>
                            <div style="width:100%;height:100%;background:linear-gradient(135deg,#1a1a2e,#16213e);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;">
                                <img src="https://img.youtube.com/vi/<?= htmlspecialchars($m['url_fichier']) ?>/mqdefault.jpg"
                                     style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;"
                                     onerror="this.style.display='none'" alt="">
                                <div style="position:relative;z-index:1;width:44px;height:44px;background:rgba(255,0,0,.9);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                    <i class="fab fa-youtube" style="color:#fff;font-size:20px;"></i>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Infos -->
                    <div style="padding:10px 12px;">
                        <div style="font-size:.82rem;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= htmlspecialchars($m['titre']??'') ?>">
                            <?= htmlspecialchars(truncate($m['titre']??'Sans titre', 28)) ?>
                        </div>
                        <div style="display:flex;align-items:center;gap:6px;margin-top:6px;">
                            <span class="badge <?= $m['type']==='photo'?'badge-info':'badge-danger' ?>" style="font-size:.62rem;">
                                <?= $m['type']==='photo'?'📷 Photo':'▶ Vidéo' ?>
                            </span>
                            <span style="font-size:.72rem;color:var(--text-muted);"><?= dateFr($m['date_upload'],'d/m/Y') ?></span>
                        </div>
                        <div style="display:flex;gap:5px;margin-top:8px;">
                            <a href="edit.php?id=<?= $m['id'] ?>" class="btn btn-xs btn-secondary" style="flex:1;justify-content:center;"><i class="fas fa-pen"></i></a>
                            <form method="POST" style="flex:1;">
                                <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                                <input type="hidden" name="gid" value="<?= $m['id'] ?>">
                                <input type="hidden" name="action" value="toggle_public">
                                <button type="submit" class="btn btn-xs btn-secondary w-100" title="Changer visibilité">
                                    <i class="fas fa-<?= $m['est_public']?'eye-slash':'eye' ?>"></i>
                                </button>
                            </form>
                            <form method="POST" style="flex:1;" onsubmit="return confirm('Supprimer ce média ?')">
                                <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                                <input type="hidden" name="gid" value="<?= $m['id'] ?>">
                                <input type="hidden" name="action" value="delete_one">
                                <button type="submit" class="btn btn-xs btn-danger w-100"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state"><i class="fas fa-images"></i><h3>Aucun média</h3><p>Commencez par uploader des photos.</p>
                <a href="upload.php" class="btn btn-primary" style="margin-top:14px;"><i class="fas fa-upload"></i> Upload</a>
            </div>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($pages > 1): ?>
    <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between;">
        <span>Page <?= $page ?>/<?= $pages ?> — <?= $total ?> média(s)</span>
        <div class="pagination"><?php for ($i=1;$i<=$pages;$i++): ?>
            <a href="?p=<?= $i ?>&type=<?= $type ?>&q=<?= urlencode($search) ?>" class="page-link <?= $i==$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?></div>
    </div>
    <?php endif; ?>
</div>

<style>
.media-card:hover { box-shadow: var(--shadow); }
</style>
<script>
document.querySelectorAll('.row-check').forEach(cb=>cb.addEventListener('change',updateCount));
function updateCount(){
    const n=document.querySelectorAll('.row-check:checked').length;
    document.getElementById('selCount').textContent=n>0?n+' sélectionné(s)':'';
}
</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
