<?php
/**
 * GSCC CMS — admin/evenements/index.php
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
$page_title='Événements'; $page_section='evenements';
$breadcrumb=[['label'=>'Événements']];

if ($_SERVER['REQUEST_METHOD']==='POST' && adminCheckCsrf()) {
    $action = $_POST['bulk_action'] ?? $_POST['action'] ?? '';
    $ids    = array_map('intval',$_POST['ids']??[]);
    $eid    = (int)($_POST['eid']??0);
    if ($ids && $action==='delete') {
        $pdo->prepare("DELETE FROM evenements WHERE id IN (".implode(',',$ids).")")->execute();
        adminFlash('success',count($ids).' événement(s) supprimé(s).');
        header('Location:index.php');exit;
    }
    if ($eid && $action==='change_status') {
        $new_s = $_POST['new_status']??'';
        if (in_array($new_s,['a_venir','en_cours','termine','annule']))
            $pdo->prepare("UPDATE evenements SET statut=? WHERE id=?")->execute([$new_s,$eid]);
        header('Location:index.php');exit;
    }
}

$statut=$_GET['statut']??''; $search=trim($_GET['q']??''); $page=max(1,(int)($_GET['p']??1)); $per=20;
$where=['1=1'];$params=[];
if($statut){$where[]="statut=?";$params[]=$statut;}
if($search){$where[]="(titre LIKE ? OR lieu LIKE ?)";$p="%$search%";$params[]=$p;$params[]=$p;}
$sw=implode(' AND ',$where);
try {
    $cnt=$pdo->prepare("SELECT COUNT(*) FROM evenements WHERE $sw");$cnt->execute($params);$total=(int)$cnt->fetchColumn();
    $pages=(int)ceil($total/$per);$offset=($page-1)*$per;
    $stmt=$pdo->prepare("SELECT e.*,CONCAT(u.prenom,' ',u.nom) created_by_name FROM evenements e LEFT JOIN utilisateurs u ON e.created_by=u.id WHERE $sw ORDER BY e.date_debut DESC LIMIT $per OFFSET $offset");
    $stmt->execute($params);$items=$stmt->fetchAll();
    $cs=[];foreach($pdo->query("SELECT statut,COUNT(*) n FROM evenements GROUP BY statut")->fetchAll() as $r) $cs[$r['statut']]=$r['n'];
} catch(PDOException $e){$items=[];$total=$pages=0;$cs=[];}

require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="page-header">
    <div><div class="page-title">Événements <span style="font-size:1rem;font-weight:400;color:var(--text-muted);">(<?= $total ?>)</span></div>
    <div class="page-subtitle">Agenda et événements GSCC</div></div>
    <a href="create.php" class="btn btn-primary"><i class="fas fa-calendar-plus"></i> Nouvel événement</a>
</div>

<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
<?php foreach ([''=> ['Tous',array_sum($cs),'secondary'],'a_venir'=>['À venir',$cs['a_venir']??0,'info'],'en_cours'=>['En cours',$cs['en_cours']??0,'success'],'termine'=>['Terminés',$cs['termine']??0,'secondary'],'annule'=>['Annulés',$cs['annule']??0,'danger']] as $v=>[$l,$n,$t]):
    $act=($statut===$v)?'border-color:var(--primary);background:var(--primary-light);color:var(--primary);':''?>
<a href="?statut=<?= $v ?>&q=<?= urlencode($search) ?>" style="display:inline-flex;align-items:center;gap:8px;padding:7px 14px;border-radius:8px;border:1.5px solid var(--border);font-size:.82rem;font-weight:600;text-decoration:none;color:var(--text);<?= $act ?>">
    <?= $l ?> <span class="badge badge-<?= $t ?>"><?= $n ?></span>
</a>
<?php endforeach;?>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="toolbar" style="margin:0;width:100%;">
            <div class="search-box"><i class="fas fa-search"></i>
                <input type="text" name="q" class="form-control" placeholder="Titre, lieu…" value="<?= htmlspecialchars($search) ?>">
            </div>
            <input type="hidden" name="statut" value="<?= htmlspecialchars($statut) ?>">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
            <?php if($search||$statut):?><a href="index.php" class="btn btn-ghost"><i class="fas fa-times"></i></a><?php endif;?>
        </form>
    </div>
    <form method="POST">
        <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
        <div style="display:flex;gap:10px;padding:10px 16px;background:#FAFBFF;border-bottom:1px solid var(--border);">
            <select name="bulk_action" class="form-control" style="width:180px;">
                <option value="">— Action groupée —</option>
                <option value="delete">Supprimer</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Confirmer ?')">Appliquer</button>
        </div>
        <div class="table-wrap">
            <table class="admin-table">
                <thead><tr>
                    <th class="col-check"><input type="checkbox" id="selectAll"></th>
                    <th>Événement</th><th>Lieu</th><th>Date début</th><th>Date fin</th>
                    <th>Capacité</th><th>Prix</th><th>Statut</th>
                    <th class="col-actions" style="width:150px;">Actions</th>
                </tr></thead>
                <tbody>
                <?php if($items): foreach($items as $ev):?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?= $ev['id'] ?>" class="row-check"></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <?php if($ev['image']): ?>
                            <img src="<?= SITE_URL ?>/assets/<?= htmlspecialchars($ev['image']) ?>" class="thumb-sm" onerror="this.style.display='none'">
                            <?php endif;?>
                            <div>
                                <div class="fw-600"><?= htmlspecialchars(truncate($ev['titre'],45)) ?></div>
                                <div style="font-size:.75rem;color:var(--text-muted);"><?= htmlspecialchars(truncate($ev['description']??'',50)) ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:.84rem;"><?= htmlspecialchars($ev['lieu']?:'—') ?></td>
                    <td style="font-size:.82rem;white-space:nowrap;color:var(--text-muted);"><?= dateFr($ev['date_debut'],'d/m/Y H:i') ?></td>
                    <td style="font-size:.82rem;white-space:nowrap;color:var(--text-muted);"><?= $ev['date_fin']?dateFr($ev['date_fin'],'d/m/Y H:i'):'—' ?></td>
                    <td style="font-size:.84rem;text-align:center;"><?= $ev['capacite_max']?$ev['capacite_max']:'∞' ?></td>
                    <td style="font-size:.84rem;"><?= $ev['prix']>0?'$'.number_format($ev['prix'],2,'.',' '):'Gratuit' ?></td>
                    <td><?= statusBadge($ev['statut']) ?></td>
                    <td class="col-actions">
                        <a href="edit.php?id=<?= $ev['id'] ?>" class="btn btn-xs btn-primary"><i class="fas fa-pen"></i></a>
                        <!-- Changer statut rapide -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                            <input type="hidden" name="action" value="change_status">
                            <input type="hidden" name="eid" value="<?= $ev['id'] ?>">
                            <?php
                            $next=['a_venir'=>['en_cours','▶'],'en_cours'=>['termine','✅'],'termine'=>['a_venir','🔄'],'annule'=>['a_venir','🔄']];
                            [$ns,$nl]=$next[$ev['statut']]??['a_venir','🔄'];
                            ?>
                            <input type="hidden" name="new_status" value="<?= $ns ?>">
                            <button type="submit" class="btn btn-xs btn-secondary" title="Passer à : <?= $ns ?>"><?= $nl ?></button>
                        </form>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ?')">
                            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                            <input type="hidden" name="bulk_action" value="delete">
                            <input type="hidden" name="ids[]" value="<?= $ev['id'] ?>">
                            <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; else:?>
                <tr><td colspan="9"><div class="empty-state"><i class="fas fa-calendar-days"></i><h3>Aucun événement</h3><a href="create.php" class="btn btn-primary" style="margin-top:12px;"><i class="fas fa-plus"></i> Créer</a></div></td></tr>
                <?php endif;?>
                </tbody>
            </table>
        </div>
    </form>
    <?php if($pages>1):?>
    <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between;">
        <span>Page <?= $page ?>/<?= $pages ?></span>
        <div class="pagination"><?php for($i=1;$i<=$pages;$i++):?>
            <a href="?p=<?= $i ?>&statut=<?= $statut ?>&q=<?= urlencode($search) ?>" class="page-link <?= $i==$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor;?></div>
    </div>
    <?php endif;?>
</div>
<script>document.getElementById('selectAll').addEventListener('change',function(){document.querySelectorAll('.row-check').forEach(cb=>cb.checked=this.checked);});</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
