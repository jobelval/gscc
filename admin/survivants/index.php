<?php
/**
 * GSCC CMS — admin/survivants/index.php
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
$page_title='Survivants'; $page_section='survivants';
$breadcrumb=[['label'=>'Survivants']];

if($_SERVER['REQUEST_METHOD']==='POST'&&adminCheckCsrf()){
    $action=($_POST['action']??($_POST['bulk_action']??''));
    $sid=(int)($_POST['sid']??0);
    $ids=array_map('intval',$_POST['ids']??[]);

    if($sid&&in_array($action,['publie','brouillon'])){
        $pdo->prepare("UPDATE survivants SET statut=? WHERE id=?")->execute([$action,$sid]);
        adminFlash('success','Statut mis à jour.');header('Location:index.php');exit;
    }
    if(($sid||$ids)&&$action==='delete'){
        $del_ids=$ids?:[$sid];
        foreach($del_ids as $i){
            $r=$pdo->prepare("SELECT photo FROM survivants WHERE id=?");$r->execute([$i]);$rw=$r->fetch();
            if($rw&&$rw['photo']&&file_exists(UPLOADS_PATH.'survivants/'.$rw['photo'])) @unlink(UPLOADS_PATH.'survivants/'.$rw['photo']);
        }
        $pdo->prepare("DELETE FROM survivants WHERE id IN (".implode(',',$del_ids).")")->execute();
        adminFlash('success','Supprimé(s).');header('Location:index.php');exit;
    }
    if(in_array($action,['save_new','save_edit'])){
        $nom    =trim($_POST['nom']??'');
        $prenom =trim($_POST['prenom']??'');
        $cancer =trim($_POST['cancer_type']??'');
        $survie =(int)($_POST['annees_survie']??1);
        $age    =(int)($_POST['age_diagnostic']??0);
        $ville  =trim($_POST['ville']??'Haiti');
        $court  =trim($_POST['histoire_courte']??'');
        $long   =$_POST['histoire_longue']??'';
        $espoir =trim($_POST['message_espoir']??'');
        $statut =in_array($_POST['statut_s']??'',['publie','brouillon'])?$_POST['statut_s']:'publie';
        $edit_id=(int)($_POST['edit_id']??0);

        if(!$nom||!$prenom||!$cancer||!$court){adminFlash('error','Champs obligatoires manquants.');header('Location:index.php');exit;}

        $photo='';
        if(!empty($_FILES['photo']['name'])){
            if(!is_dir(UPLOADS_PATH.'survivants/')) mkdir(UPLOADS_PATH.'survivants/',0755,true);
            $up=uploadFile($_FILES['photo'],UPLOADS_PATH.'survivants/',['jpg','jpeg','png','webp']);
            if($up['success']) $photo=$up['filename'];
        }

        if($action==='save_edit'&&$edit_id){
            $cur=$pdo->prepare("SELECT photo FROM survivants WHERE id=?");$cur->execute([$edit_id]);$cur_photo=$cur->fetchColumn();
            $sql="UPDATE survivants SET nom=?,prenom=?,cancer_type=?,annees_survie=?,age_diagnostic=?,ville=?,histoire_courte=?,histoire_longue=?,message_espoir=?,statut=?";
            $p=[$nom,$prenom,$cancer,$survie,$age?:null,$ville,$court,$long?:null,$espoir?:null,$statut];
            if($photo){$sql.=',photo=?';$p[]=$photo;}
            $sql.=" WHERE id=?";$p[]=$edit_id;
            $pdo->prepare($sql)->execute($p);
            adminFlash('success','Survivant mis à jour.');
        } else {
            $pdo->prepare("INSERT INTO survivants (nom,prenom,photo,cancer_type,annees_survie,age_diagnostic,ville,histoire_courte,histoire_longue,message_espoir,statut,date_creation) VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW())")
                ->execute([$nom,$prenom,$photo?:null,$cancer,$survie,$age?:null,$ville,$court,$long?:null,$espoir?:null,$statut]);
            adminFlash('success','Survivant ajouté !');
        }
        header('Location:index.php');exit;
    }
}

$statut_f=$_GET['statut']??''; $page=max(1,(int)($_GET['p']??1)); $per=20;
$where=['1=1'];$params=[];
if($statut_f){$where[]="statut=?";$params[]=$statut_f;}
$sw=implode(' AND ',$where);
try{
    $cnt=$pdo->prepare("SELECT COUNT(*) FROM survivants WHERE $sw");$cnt->execute($params);$total=(int)$cnt->fetchColumn();
    $pages=(int)ceil($total/$per);$offset=($page-1)*$per;
    $stmt=$pdo->prepare("SELECT * FROM survivants WHERE $sw ORDER BY date_creation DESC LIMIT $per OFFSET $offset");
    $stmt->execute($params);$survivants=$stmt->fetchAll();
    $nb_pub=(int)$pdo->query("SELECT COUNT(*) FROM survivants WHERE statut='publie'")->fetchColumn();
    $nb_bro=(int)$pdo->query("SELECT COUNT(*) FROM survivants WHERE statut='brouillon'")->fetchColumn();
}catch(PDOException $e){$survivants=[];$total=$pages=0;$nb_pub=$nb_bro=0;}

require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="page-header">
    <div><div class="page-title">Survivants <span style="font-size:1rem;font-weight:400;color:var(--text-muted);">(<?= $total ?>)</span></div>
    <div class="page-subtitle">Histoires de survivants affichées sur le site</div></div>
    <button class="btn btn-primary" onclick="document.getElementById('survModal').classList.add('show')">
        <i class="fas fa-plus"></i> Ajouter
    </button>
</div>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:16px;">
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-heart-pulse"></i></div><div class="stat-info"><div class="stat-value"><?= $total ?></div><div class="stat-label">Total survivants</div></div></div>
    <div class="stat-card"><div class="stat-icon green"><i class="fas fa-eye"></i></div><div class="stat-info"><div class="stat-value"><?= $nb_pub ?></div><div class="stat-label">Publiés</div></div></div>
    <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-eye-slash"></i></div><div class="stat-info"><div class="stat-value"><?= $nb_bro ?></div><div class="stat-label">Brouillons</div></div></div>
</div>

<div class="card">
    <div class="card-header">
        <div style="display:flex;gap:8px;">
            <a href="?statut=" class="btn btn-<?= !$statut_f?'primary':'secondary' ?> btn-sm">Tous (<?= $total ?>)</a>
            <a href="?statut=publie" class="btn btn-<?= $statut_f==='publie'?'primary':'secondary' ?> btn-sm">Publiés (<?= $nb_pub ?>)</a>
            <a href="?statut=brouillon" class="btn btn-<?= $statut_f==='brouillon'?'primary':'secondary' ?> btn-sm">Brouillons (<?= $nb_bro ?>)</a>
        </div>
    </div>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
        <div style="display:flex;gap:10px;padding:10px 16px;background:#FAFBFF;border-bottom:1px solid var(--border);">
            <select name="bulk_action" class="form-control" style="width:180px;"><option value="">— Action groupée —</option><option value="delete">Supprimer</option></select>
            <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Confirmer ?')">Appliquer</button>
        </div>
        <div class="table-wrap">
            <table class="admin-table">
                <thead><tr>
                    <th class="col-check"><input type="checkbox" id="selectAll"></th>
                    <th>Survivant</th><th>Cancer</th><th>Années survie</th><th>Âge diagnostic</th>
                    <th>Ville</th><th>Statut</th><th class="col-actions" style="width:140px;">Actions</th>
                </tr></thead>
                <tbody>
                <?php if($survivants): foreach($survivants as $s):?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?= $s['id'] ?>" class="row-check"></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <?php if($s['photo']&&file_exists(UPLOADS_PATH.'survivants/'.$s['photo'])):?>
                            <img src="<?= SITE_URL ?>/assets/uploads/survivants/<?= htmlspecialchars($s['photo']) ?>" class="avatar avatar-sm" style="object-fit:cover;" onerror="this.style.display='none'">
                            <?php else:?>
                            <div class="avatar avatar-sm">🎗️</div>
                            <?php endif;?>
                            <div>
                                <div class="fw-600"><?= htmlspecialchars($s['prenom'].' '.$s['nom']) ?></div>
                                <div style="font-size:.76rem;color:var(--text-muted);"><?= htmlspecialchars(truncate($s['histoire_courte'],45)) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge badge-rose" style="background:#FFF1F2;color:var(--rose);"><?= htmlspecialchars($s['cancer_type']) ?></span></td>
                    <td style="text-align:center;"><strong style="font-size:1rem;color:var(--success);"><?= $s['annees_survie'] ?></strong> <span style="font-size:.75rem;color:var(--text-muted);">an(s)</span></td>
                    <td style="text-align:center;font-size:.84rem;"><?= $s['age_diagnostic']?$s['age_diagnostic'].' ans':'—' ?></td>
                    <td style="font-size:.84rem;"><?= htmlspecialchars($s['ville']?:'—') ?></td>
                    <td><?= statusBadge($s['statut']) ?></td>
                    <td class="col-actions">
                        <button type="button" class="btn btn-xs btn-primary" onclick="editSurv(<?= htmlspecialchars(json_encode($s)) ?>)"><i class="fas fa-pen"></i></button>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                            <input type="hidden" name="sid" value="<?= $s['id'] ?>">
                            <input type="hidden" name="action" value="<?= $s['statut']==='publie'?'brouillon':'publie' ?>">
                            <button type="submit" class="btn btn-xs btn-secondary" title="<?= $s['statut']==='publie'?'Masquer':'Publier' ?>">
                                <i class="fas fa-<?= $s['statut']==='publie'?'eye-slash':'eye' ?>"></i>
                            </button>
                        </form>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ?')">
                            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                            <input type="hidden" name="sid" value="<?= $s['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; else:?>
                <tr><td colspan="8"><div class="empty-state"><i class="fas fa-heart-pulse"></i><h3>Aucun survivant</h3><p>Partagez des histoires inspirantes.</p></div></td></tr>
                <?php endif;?>
                </tbody>
            </table>
        </div>
    </form>
    <?php if($pages>1):?>
    <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between;">
        <span>Page <?= $page ?>/<?= $pages ?></span>
        <div class="pagination"><?php for($i=1;$i<=$pages;$i++):?>
            <a href="?statut=<?= $statut_f ?>&p=<?= $i ?>" class="page-link <?= $i==$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor;?></div>
    </div>
    <?php endif;?>
</div>

<!-- Modal survivant -->
<div class="modal-overlay" id="survModal">
    <div class="modal" style="max-width:640px;">
        <div class="modal-header">
            <span class="modal-title" id="survModalTitle">Ajouter un survivant</span>
            <button class="modal-close" onclick="document.getElementById('survModal').classList.remove('show')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
            <input type="hidden" name="action" id="survAction" value="save_new">
            <input type="hidden" name="edit_id" id="survEditId" value="0">
            <div class="modal-body" style="max-height:70vh;overflow-y:auto;">
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Prénom <span class="required">*</span></label><input type="text" name="prenom" id="sPrenom" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Nom <span class="required">*</span></label><input type="text" name="nom" id="sNom" class="form-control" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Type de cancer <span class="required">*</span></label><input type="text" name="cancer_type" id="sCancer" class="form-control" required placeholder="Ex. Cancer du sein…"></div>
                    <div class="form-group"><label class="form-label">Années de survie <span class="required">*</span></label><input type="number" name="annees_survie" id="sSurvie" class="form-control" required min="1" value="1"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Âge au diagnostic</label><input type="number" name="age_diagnostic" id="sAge" class="form-control" min="0" placeholder="ex. 38"></div>
                    <div class="form-group"><label class="form-label">Ville</label><input type="text" name="ville" id="sVille" class="form-control" value="Haïti" placeholder="Port-au-Prince…"></div>
                </div>
                <div class="form-group"><label class="form-label">Histoire courte <span class="required">*</span></label><textarea name="histoire_courte" id="sCourt" class="form-control" rows="2" required placeholder="Résumé de quelques lignes…"></textarea></div>
                <div class="form-group"><label class="form-label">Histoire complète</label><textarea name="histoire_longue" id="sLong" class="form-control" rows="5" placeholder="Témoignage complet…"></textarea></div>
                <div class="form-group"><label class="form-label">Message d'espoir</label><textarea name="message_espoir" id="sEspoir" class="form-control" rows="2" placeholder="Message inspirant pour les autres…"></textarea></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Statut</label>
                        <select name="statut_s" id="sStatut" class="form-control">
                            <option value="publie">✅ Publié</option>
                            <option value="brouillon">📝 Brouillon</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Photo</label><input type="file" name="photo" class="form-control" accept="image/*" style="font-size:.82rem;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('survModal').classList.remove('show')" class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>
<script>
function editSurv(s){
    document.getElementById('survModalTitle').textContent='Modifier : '+s.prenom+' '+s.nom;
    document.getElementById('survAction').value='save_edit';
    document.getElementById('survEditId').value=s.id;
    document.getElementById('sPrenom').value=s.prenom;
    document.getElementById('sNom').value=s.nom;
    document.getElementById('sCancer').value=s.cancer_type;
    document.getElementById('sSurvie').value=s.annees_survie;
    document.getElementById('sAge').value=s.age_diagnostic||'';
    document.getElementById('sVille').value=s.ville||'';
    document.getElementById('sCourt').value=s.histoire_courte;
    document.getElementById('sLong').value=s.histoire_longue||'';
    document.getElementById('sEspoir').value=s.message_espoir||'';
    document.getElementById('sStatut').value=s.statut;
    document.getElementById('survModal').classList.add('show');
}
document.getElementById('selectAll').addEventListener('change',function(){document.querySelectorAll('.row-check').forEach(cb=>cb.checked=this.checked);});
</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
