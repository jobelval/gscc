<?php
/**
 * GSCC CMS — admin/demandes/index.php
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$page_title   = 'Demandes d\'aide';
$page_section = 'demandes';
$breadcrumb   = [['label' => 'Demandes d\'aide']];

/* ── Actions ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $action  = $_POST['action'] ?? '';
    $did     = (int)($_POST['did'] ?? 0);
    $comment = trim($_POST['commentaire'] ?? '');
    $montant = (float)($_POST['montant_accorde'] ?? 0);

    if ($did) {
        try {
            $valid = ['en_cours','approuve','refuse'];
            if (in_array($action, $valid)) {
                $pdo->prepare(
                    "UPDATE demandes_aide SET statut=?,commentaires_admin=?,montant_accorde=?,
                     date_traitement=NOW(),traite_par=? WHERE id=?"
                )->execute([$action, $comment?:null, $montant?:null, $_SESSION['admin_id'], $did]);
                adminFlash('success','Demande mise à jour : '.ucfirst($action));
            } elseif ($action==='delete') {
                $pdo->prepare("DELETE FROM demandes_aide WHERE id=?")->execute([$did]);
                adminFlash('success','Demande supprimée.');
            }
        } catch (PDOException $e) { adminFlash('error',$e->getMessage()); }
        header('Location: index.php'); exit;
    }
}

/* ── Filtres ── */
$statut = $_GET['statut'] ?? '';
$type   = $_GET['type']   ?? '';
$search = trim($_GET['q'] ?? '');
$page   = max(1,(int)($_GET['p']??1));
$per    = 20;
$where  = ['1=1']; $params = [];
if ($statut) { $where[]="statut=?"; $params[]=$statut; }
if ($type)   { $where[]="type_aide=?"; $params[]=$type; }
if ($search) { $where[]="description_demande LIKE ?"; $params[]="%$search%"; }
$sw = implode(' AND ',$where);

try {
    $cnt=$pdo->prepare("SELECT COUNT(*) FROM demandes_aide WHERE $sw"); $cnt->execute($params); $total=(int)$cnt->fetchColumn();
    $pages=(int)ceil($total/$per); $offset=($page-1)*$per;
    $stmt=$pdo->prepare(
        "SELECT d.*,u.nom u_nom,u.prenom u_prenom,u.email u_email
         FROM demandes_aide d
         LEFT JOIN utilisateurs u ON d.utilisateur_id=u.id
         WHERE $sw ORDER BY FIELD(d.statut,'soumis','en_cours','approuve','refuse'),d.date_soumission DESC
         LIMIT $per OFFSET $offset"
    );
    $stmt->execute($params); $demandes=$stmt->fetchAll();
    $cs=[];
    foreach ($pdo->query("SELECT statut,COUNT(*) n FROM demandes_aide GROUP BY statut")->fetchAll() as $r) $cs[$r['statut']]=$r['n'];
} catch (PDOException $e) { $demandes=[]; $total=$pages=0; $cs=[]; }

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Demandes d'aide
            <span style="font-size:1rem;font-weight:400;color:var(--text-muted);">(<?= $total ?>)</span>
        </div>
        <div class="page-subtitle">Gérer les demandes de soutien des patients</div>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px;">
<?php foreach ([
    'soumis'   =>['Soumis',   $cs['soumis']??0,   'orange','inbox'],
    'en_cours' =>['En cours', $cs['en_cours']??0,  'blue',  'spinner'],
    'approuve' =>['Approuvés',$cs['approuve']??0,  'green', 'check-circle'],
    'refuse'   =>['Refusés',  $cs['refuse']??0,    'rose',  'times-circle'],
] as $val=>[$label,$nb,$color,$icon]):
    $active = ($statut===$val)?'border-color:var(--primary);background:var(--primary-light);':'';
?>
<a href="?statut=<?= $val ?>" style="text-decoration:none;">
    <div class="stat-card" style="<?= $active ?>">
        <div class="stat-icon <?= $color ?>"><i class="fas fa-<?= $icon ?>"></i></div>
        <div class="stat-info"><div class="stat-value"><?= $nb ?></div><div class="stat-label"><?= $label ?></div></div>
    </div>
</a>
<?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="toolbar" style="margin:0;width:100%;">
            <div class="search-box"><i class="fas fa-search"></i>
                <input type="text" name="q" class="form-control" placeholder="Description…" value="<?= htmlspecialchars($search) ?>">
            </div>
            <select name="type" class="form-control" style="width:180px;">
                <option value="">Tous types</option>
                <?php foreach (['financiere'=>'Financière','medicale'=>'Médicale','psychologique'=>'Psychologique','accompagnement'=>'Accompagnement'] as $k=>$v): ?>
                <option value="<?= $k ?>" <?= $type===$k?'selected':'' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="statut" value="<?= htmlspecialchars($statut) ?>">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i></button>
            <?php if ($search||$type||$statut): ?><a href="index.php" class="btn btn-ghost"><i class="fas fa-times"></i></a><?php endif; ?>
        </form>
    </div>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Demandeur</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Documents</th>
                    <th>Montant demandé</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th class="col-actions" style="width:120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($demandes): foreach ($demandes as $d):
                $docs = [];
                try { $docs = json_decode($d['documents_justificatifs']??'[]',true)?:[]; } catch(Exception $e){}
            ?>
            <tr style="<?= $d['statut']==='soumis'?'background:#FFFBEB;':'' ?>">
                <td>
                    <div class="fw-600"><?= $d['utilisateur_id'] ? htmlspecialchars(($d['u_prenom']??'').' '.($d['u_nom']??'')) : '<em style="color:var(--text-muted)">Anonyme</em>' ?></div>
                    <?php if ($d['u_email']): ?>
                    <div style="font-size:.76rem;color:var(--text-muted);"><?= htmlspecialchars($d['u_email']) ?></div>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    $type_labels=['financiere'=>['💰','info'],'medicale'=>['🏥','danger'],'psychologique'=>['🧠','purple'],'accompagnement'=>['🤝','success']];
                    [$emoji,$badgetype] = $type_labels[$d['type_aide']] ?? ['📋','secondary'];
                    ?>
                    <span class="badge badge-<?= $badgetype ?>"><?= $emoji ?> <?= ucfirst(str_replace('_',' ',$d['type_aide'])) ?></span>
                </td>
                <td style="font-size:.83rem;max-width:200px;">
                    <div class="truncate" style="max-width:200px;" title="<?= htmlspecialchars($d['description_demande']) ?>">
                        <?= htmlspecialchars(truncate($d['description_demande'],70)) ?>
                    </div>
                </td>
                <td style="text-align:center;">
                    <?php if ($docs): ?>
                        <span class="badge badge-primary"><?= count($docs) ?> fichier(s)</span>
                    <?php else: ?>
                        <span style="color:var(--text-muted);font-size:.78rem;">Aucun</span>
                    <?php endif; ?>
                </td>
                <td style="font-size:.85rem;">
                    <?= $d['montant_demande'] ? '$'.number_format($d['montant_demande'],2,'.',' ') : '—' ?>
                    <?php if ($d['montant_accorde']): ?>
                    <br><span style="color:var(--success);font-size:.75rem;">Accordé : <?= number_format($d['montant_accorde'],2,',',' ') ?></span>
                    <?php endif; ?>
                </td>
                <td><?= statusBadge($d['statut']) ?></td>
                <td style="font-size:.78rem;color:var(--text-muted);"><?= dateFr($d['date_soumission'],'d/m/Y') ?></td>
                <td class="col-actions">
                    <button type="button" class="btn btn-xs btn-primary"
                            onclick="openDemande(<?= $d['id'] ?>,'<?= htmlspecialchars(addslashes(($d['u_prenom']??'').' '.($d['u_nom']??'Anonyme'))) ?>','<?= $d['statut'] ?>','<?= htmlspecialchars(addslashes($d['commentaires_admin']??'')) ?>',<?= $d['montant_accorde']??0 ?>)">
                        <i class="fas fa-edit"></i> Traiter
                    </button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cette demande ?')">
                        <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                        <input type="hidden" name="did" value="<?= $d['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="8"><div class="empty-state"><i class="fas fa-file-medical"></i><h3>Aucune demande</h3></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pages>1): ?>
    <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between;">
        <span>Page <?= $page ?>/<?= $pages ?></span>
        <div class="pagination"><?php for ($i=1;$i<=$pages;$i++): ?>
            <a href="?p=<?= $i ?>&statut=<?= $statut ?>&type=<?= $type ?>&q=<?= urlencode($search) ?>" class="page-link <?= $i==$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?></div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal traitement demande -->
<div class="modal-overlay" id="demandeModal">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <span class="modal-title">Traiter la demande — <span id="dName"></span></span>
            <button class="modal-close" onclick="document.getElementById('demandeModal').classList.remove('show')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
            <input type="hidden" name="did" id="dId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Décision</label>
                    <select name="action" id="dAction" class="form-control">
                        <option value="en_cours">🔄 En cours de traitement</option>
                        <option value="approuve">✅ Approuver</option>
                        <option value="refuse">❌ Refuser</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Montant accordé ($)</label>
                    <input type="number" name="montant_accorde" id="dMontant" class="form-control" min="0" step="0.01" placeholder="0.00">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Commentaire admin</label>
                    <textarea name="commentaire" id="dComment" class="form-control" rows="3"
                              placeholder="Raison de la décision, instructions…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('demandeModal').classList.remove('show')" class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDemande(id,name,statut,comment,montant){
    document.getElementById('dId').value=id;
    document.getElementById('dName').textContent=name;
    document.getElementById('dAction').value=statut==='soumis'?'en_cours':statut;
    document.getElementById('dComment').value=comment;
    document.getElementById('dMontant').value=montant||'';
    document.getElementById('demandeModal').classList.add('show');
}
</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
