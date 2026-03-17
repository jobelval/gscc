<?php
/**
 * GSCC CMS — admin/temoignages/index.php
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$page_title   = 'Témoignages';
$page_section = 'temoignages';
$breadcrumb   = [['label' => 'Témoignages']];

/* ── POST actions ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $action = $_POST['action'] ?? '';
    $tid    = (int)($_POST['tid'] ?? 0);
    $ids    = array_map('intval', $_POST['ids'] ?? []);

    if ($tid && in_array($action, ['approuve','en_attente','refuse'])) {
        $pdo->prepare("UPDATE temoignages SET statut=? WHERE id=?")->execute([$action,$tid]);
        adminFlash('success','Statut mis à jour.');
        header('Location:index.php'); exit;
    }
    if ($tid && $action === 'delete') {
        // Supprimer photo si existante
        $row = $pdo->prepare("SELECT photo FROM temoignages WHERE id=?"); $row->execute([$tid]); $r=$row->fetch();
        if ($r && $r['photo'] && file_exists(UPLOADS_PATH.'temoignages/'.$r['photo'])) @unlink(UPLOADS_PATH.'temoignages/'.$r['photo']);
        $pdo->prepare("DELETE FROM temoignages WHERE id=?")->execute([$tid]);
        adminFlash('success','Témoignage supprimé.');
        header('Location:index.php'); exit;
    }
    if ($ids) {
        match($action){
            'bulk_approve'=>$pdo->prepare("UPDATE temoignages SET statut='approuve' WHERE id IN (".implode(',',$ids).")")->execute(),
            'bulk_delete' =>$pdo->prepare("DELETE FROM temoignages WHERE id IN (".implode(',',$ids).")")->execute(),
            default=>null,
        };
        adminFlash('success','Action appliquée sur '.count($ids).' témoignage(s).');
        header('Location:index.php'); exit;
    }

    // Créer / modifier témoignage
    if (in_array($action, ['create','update'])) {
        $nom    = trim($_POST['nom']??'');
        $fonc   = trim($_POST['fonction']??'');
        $temo   = trim($_POST['temoignage']??'');
        $note   = min(5,max(1,(int)($_POST['note']??5)));
        $statut = in_array($_POST['statut']??'',['approuve','en_attente','refuse'])?$_POST['statut']:'en_attente';
        $edit_id= (int)($_POST['edit_id']??0);

        if ($nom && $temo) {
            $photo = '';
            if (!empty($_FILES['photo']['name'])) {
                $up = uploadFile($_FILES['photo'], UPLOADS_PATH.'temoignages/', ['jpg','jpeg','png','webp']);
                if ($up['success']) $photo = $up['filename'];
            }
            if ($action==='create') {
                $pdo->prepare("INSERT INTO temoignages (nom,fonction,temoignage,note,photo,statut,date_creation,created_by) VALUES (?,?,?,?,?,?,NOW(),?)")
                    ->execute([$nom,$fonc,$temo,$note,$photo?:'default-avatar.png',$statut,$_SESSION['admin_id']]);
                adminFlash('success','Témoignage ajouté.');
            } else {
                $sql = "UPDATE temoignages SET nom=?,fonction=?,temoignage=?,note=?,statut=?";
                $p   = [$nom,$fonc,$temo,$note,$statut];
                if ($photo) { $sql.=",photo=?"; $p[]=$photo; }
                $sql.=" WHERE id=?"; $p[]=$edit_id;
                $pdo->prepare($sql)->execute($p);
                adminFlash('success','Témoignage mis à jour.');
            }
        } else { adminFlash('error','Nom et témoignage obligatoires.'); }
        header('Location:index.php'); exit;
    }
}

/* ── Filtres ── */
$statut = $_GET['statut']??'';
$page   = max(1,(int)($_GET['p']??1));
$per    = 20;
$where  = ['1=1']; $params=[];
if ($statut) { $where[]="statut=?"; $params[]=$statut; }
$sw = implode(' AND ',$where);

try {
    $cnt=$pdo->prepare("SELECT COUNT(*) FROM temoignages WHERE $sw"); $cnt->execute($params); $total=(int)$cnt->fetchColumn();
    $pages=(int)ceil($total/$per); $offset=($page-1)*$per;
    $stmt=$pdo->prepare("SELECT * FROM temoignages WHERE $sw ORDER BY FIELD(statut,'en_attente','approuve','refuse'),date_creation DESC LIMIT $per OFFSET $offset");
    $stmt->execute($params); $temos=$stmt->fetchAll();
    $cs=[];
    foreach ($pdo->query("SELECT statut,COUNT(*) n FROM temoignages GROUP BY statut")->fetchAll() as $r) $cs[$r['statut']]=$r['n'];
} catch(PDOException $e){$temos=[];$total=$pages=0;$cs=[];}

require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="page-header">
    <div>
        <div class="page-title">Témoignages <span style="font-size:1rem;font-weight:400;color:var(--text-muted);">(<?= $total ?>)</span></div>
        <div class="page-subtitle">Modérer et gérer les témoignages affichés sur le site</div>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('show')">
        <i class="fas fa-plus"></i> Ajouter
    </button>
</div>

<!-- Tabs statuts -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
<?php foreach ([''=> ['Tous',array_sum($cs),'secondary'],'en_attente'=>['En attente',$cs['en_attente']??0,'warning'],'approuve'=>['Approuvés',$cs['approuve']??0,'success'],'refuse'=>['Refusés',$cs['refuse']??0,'danger']] as $v=>[$l,$n,$t]):
    $active=($statut===$v)?'border-color:var(--primary);background:var(--primary-light);color:var(--primary);':'' ?>
<a href="?statut=<?= $v ?>" style="display:inline-flex;align-items:center;gap:8px;padding:7px 14px;border-radius:8px;border:1.5px solid var(--border);font-size:.82rem;font-weight:600;text-decoration:none;color:var(--text);<?= $active ?>">
    <?= $l ?> <span class="badge badge-<?= $t ?>"><?= $n ?></span>
</a>
<?php endforeach; ?>
</div>

<div class="card">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
        <div style="display:flex;gap:10px;padding:10px 16px;background:#FAFBFF;border-bottom:1px solid var(--border);">
            <select name="action" class="form-control" style="width:220px;">
                <option value="">— Action groupée —</option>
                <option value="bulk_approve">Approuver la sélection</option>
                <option value="bulk_delete">Supprimer la sélection</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Confirmer ?')">Appliquer</button>
        </div>
        <div class="table-wrap">
            <table class="admin-table">
                <thead><tr>
                    <th class="col-check"><input type="checkbox" id="selectAll"></th>
                    <th>Personne</th><th>Témoignage</th><th>Note</th><th>Statut</th><th>Date</th>
                    <th class="col-actions" style="width:130px;">Actions</th>
                </tr></thead>
                <tbody>
                <?php if ($temos): foreach ($temos as $t): ?>
                <tr style="<?= $t['statut']==='en_attente'?'background:#FFFBEB;':'' ?>">
                    <td><input type="checkbox" name="ids[]" value="<?= $t['id'] ?>" class="row-check"></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:9px;">
                            <?php
                            $photoPath = UPLOADS_PATH.'temoignages/'.($t['photo']??'');
                            if ($t['photo'] && file_exists($photoPath)): ?>
                                <img src="<?= SITE_URL ?>/assets/uploads/temoignages/<?= htmlspecialchars($t['photo']) ?>"
                                     class="avatar avatar-sm" style="border-radius:50%;object-fit:cover;" onerror="this.style.display='none'">
                            <?php else: ?>
                                <div class="avatar avatar-sm"><?= strtoupper(substr($t['nom'],0,1)) ?></div>
                            <?php endif; ?>
                            <div>
                                <div class="fw-600"><?= htmlspecialchars($t['nom']) ?></div>
                                <div style="font-size:.76rem;color:var(--text-muted);"><?= htmlspecialchars($t['fonction']?:'—') ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:.84rem;max-width:250px;">
                        <div class="truncate" style="max-width:250px;"><?= htmlspecialchars(truncate($t['temoignage'],80)) ?></div>
                    </td>
                    <td>
                        <span style="color:#F59E0B;letter-spacing:2px;"><?= str_repeat('★',min(5,max(1,(int)$t['note']))) ?><span style="color:#E2E8F0;"><?= str_repeat('★',5-min(5,(int)$t['note'])) ?></span></span>
                    </td>
                    <td><?= statusBadge($t['statut']) ?></td>
                    <td style="font-size:.78rem;color:var(--text-muted);"><?= dateFr($t['date_creation'],'d/m/Y') ?></td>
                    <td class="col-actions">
                        <?php if ($t['statut']!=='approuve'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                            <input type="hidden" name="action" value="approuve">
                            <input type="hidden" name="tid" value="<?= $t['id'] ?>">
                            <button type="submit" class="btn btn-xs btn-success" title="Approuver"><i class="fas fa-check"></i></button>
                        </form>
                        <?php endif; ?>
                        <button type="button" class="btn btn-xs btn-secondary"
                                onclick="editTemo(<?= $t['id'] ?>,'<?= htmlspecialchars(addslashes($t['nom'])) ?>','<?= htmlspecialchars(addslashes($t['fonction']??'')) ?>','<?= htmlspecialchars(addslashes($t['temoignage'])) ?>','<?= $t['note'] ?>','<?= $t['statut'] ?>')">
                            <i class="fas fa-pen"></i>
                        </button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ?')">
                            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="tid" value="<?= $t['id'] ?>">
                            <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="7"><div class="empty-state"><i class="fas fa-quote-right"></i><h3>Aucun témoignage</h3></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
    <?php if ($pages>1): ?>
    <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between;">
        <span>Page <?= $page ?>/<?= $pages ?></span>
        <div class="pagination"><?php for ($i=1;$i<=$pages;$i++): ?>
            <a href="?statut=<?= $statut ?>&p=<?= $i ?>" class="page-link <?= $i==$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?></div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal ajouter/éditer -->
<div class="modal-overlay" id="addModal">
    <div class="modal" style="max-width:540px;">
        <div class="modal-header">
            <span class="modal-title" id="modalTemoTitle">Ajouter un témoignage</span>
            <button class="modal-close" onclick="closeTemoModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
            <input type="hidden" name="action" id="temoAction" value="create">
            <input type="hidden" name="edit_id" id="temoEditId" value="0">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nom <span class="required">*</span></label>
                        <input type="text" name="nom" id="tNom" class="form-control" required placeholder="Marie-Claire">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fonction / Titre</label>
                        <input type="text" name="fonction" id="tFonc" class="form-control" placeholder="Survivante, bénévole…">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Témoignage <span class="required">*</span></label>
                    <textarea name="temoignage" id="tTemo" class="form-control" rows="4" required placeholder="Texte du témoignage…"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Note (1-5)</label>
                        <select name="note" id="tNote" class="form-control">
                            <?php for ($i=5;$i>=1;$i--): ?><option value="<?= $i ?>"><?= str_repeat('★',$i) ?> (<?= $i ?>)</option><?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Statut</label>
                        <select name="statut" id="tStatut" class="form-control">
                            <option value="approuve">✅ Approuvé</option>
                            <option value="en_attente">🕐 En attente</option>
                            <option value="refuse">❌ Refusé</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Photo (optionnel)</label>
                    <input type="file" name="photo" class="form-control" accept="image/*" style="font-size:.82rem;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeTemoModal()" class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
function closeTemoModal(){ document.getElementById('addModal').classList.remove('show'); }
function editTemo(id,nom,fonc,temo,note,statut){
    document.getElementById('modalTemoTitle').textContent='Modifier le témoignage';
    document.getElementById('temoAction').value='update';
    document.getElementById('temoEditId').value=id;
    document.getElementById('tNom').value=nom;
    document.getElementById('tFonc').value=fonc;
    document.getElementById('tTemo').value=temo;
    document.getElementById('tNote').value=note;
    document.getElementById('tStatut').value=statut;
    document.getElementById('addModal').classList.add('show');
}
document.getElementById('selectAll').addEventListener('change',function(){
    document.querySelectorAll('.row-check').forEach(cb=>cb.checked=this.checked);
});
</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
