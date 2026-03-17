<?php
/**
 * GSCC CMS — admin/benevoles/index.php
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$page_title   = 'Bénévoles';
$page_section = 'benevoles';
$breadcrumb   = [['label' => 'Candidatures bénévoles']];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $action = $_POST['action'] ?? '';
    $cid    = (int)($_POST['cid'] ?? 0);
    $note   = trim($_POST['notes_admin'] ?? '');
    if ($cid) {
        try {
            $valid = ['en_attente','contacte','accepte','refuse'];
            if (in_array($action, $valid)) {
                $pdo->prepare("UPDATE candidatures_benevoles SET statut=?,notes_admin=?,date_traitement=NOW() WHERE id=?")
                    ->execute([$action, $note ?: null, $cid]);
                adminFlash('success', 'Statut mis à jour : ' . ucfirst($action));
            } elseif ($action === 'delete') {
                $pdo->prepare("DELETE FROM candidatures_benevoles WHERE id=?")->execute([$cid]);
                adminFlash('success', 'Candidature supprimée.');
            }
        } catch (PDOException $e) { adminFlash('error', $e->getMessage()); }
        header('Location: index.php'); exit;
    }
}

$statut = $_GET['statut'] ?? '';
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['p'] ?? 1));
$per    = 20;
$where  = ['1=1']; $params = [];
if ($statut) { $where[] = "statut=?"; $params[] = $statut; }
if ($search) { $where[] = "(nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR profession LIKE ?)"; $p = "%$search%"; $params = array_merge($params,[$p,$p,$p,$p]); }
$sw = implode(' AND ', $where);

try {
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM candidatures_benevoles WHERE $sw");
    $cnt->execute($params); $total = (int)$cnt->fetchColumn();
    $pages = (int)ceil($total/$per); $offset = ($page-1)*$per;
    $stmt = $pdo->prepare("SELECT * FROM candidatures_benevoles WHERE $sw ORDER BY FIELD(statut,'en_attente','contacte','accepte','refuse'), date_candidature DESC LIMIT $per OFFSET $offset");
    $stmt->execute($params); $candidatures = $stmt->fetchAll();
    $cs = []; foreach ($pdo->query("SELECT statut,COUNT(*) n FROM candidatures_benevoles GROUP BY statut")->fetchAll() as $r) $cs[$r['statut']]=$r['n'];
    $cs_total = array_sum($cs);
} catch (PDOException $e) { $candidatures=[]; $total=$pages=0; $cs=[]; $cs_total=0; }

require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="page-header">
    <div>
        <div class="page-title">Candidatures bénévoles <span style="font-size:1rem;font-weight:400;color:var(--text-muted);">(<?= $total ?>)</span></div>
        <div class="page-subtitle">Gérer les demandes de bénévolat reçues</div>
    </div>
    <a href="export.php" class="btn btn-secondary"><i class="fas fa-download"></i> Exporter CSV</a>
</div>

<div class="stats-grid" style="grid-template-columns:repeat(5,1fr);margin-bottom:20px;">
<?php foreach ([''=>['Total',$cs_total,'blue','users'],'en_attente'=>['En attente',$cs['en_attente']??0,'orange','clock'],'contacte'=>['Contactés',$cs['contacte']??0,'info','phone'],'accepte'=>['Acceptés',$cs['accepte']??0,'green','user-check'],'refuse'=>['Refusés',$cs['refuse']??0,'rose','user-times']] as $val=>[$label,$nb,$color,$icon]): ?>
<a href="?statut=<?= $val ?>&q=<?= urlencode($search) ?>" style="text-decoration:none;">
    <div class="stat-card" style="<?= $statut===$val?'border-color:var(--primary);background:var(--primary-light);':'' ?>">
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
                <input type="text" name="q" class="form-control" placeholder="Nom, email, profession…" value="<?= htmlspecialchars($search) ?>">
            </div>
            <input type="hidden" name="statut" value="<?= htmlspecialchars($statut) ?>">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
            <?php if ($search||$statut): ?><a href="index.php" class="btn btn-ghost"><i class="fas fa-times"></i></a><?php endif; ?>
        </form>
    </div>
    <div class="table-wrap">
        <table class="admin-table">
            <thead><tr><th>Candidat</th><th>Profession</th><th>Disponibilités</th><th>Compétences</th><th>Statut</th><th>Date</th><th class="col-actions" style="width:160px;">Actions</th></tr></thead>
            <tbody>
            <?php if ($candidatures): foreach ($candidatures as $c):
                $competences = [];
                try { $competences = json_decode($c['competences']??'[]',true)?:[]; } catch(Exception $e){}
            ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="avatar avatar-sm"><?= strtoupper(substr($c['prenom'],0,1).substr($c['nom'],0,1)) ?></div>
                        <div>
                            <div class="fw-600"><?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?></div>
                            <div style="font-size:.76rem;color:var(--text-muted);"><?= htmlspecialchars($c['email']) ?></div>
                            <div style="font-size:.76rem;color:var(--text-muted);"><?= htmlspecialchars($c['telephone']) ?></div>
                        </div>
                    </div>
                </td>
                <td style="font-size:.84rem;"><?= htmlspecialchars($c['profession']?:'—') ?></td>
                <td style="font-size:.82rem;color:var(--text-muted);"><?= htmlspecialchars($c['disponibilites']?:'—') ?></td>
                <td><?php foreach ($competences as $comp): ?><span class="badge badge-info" style="font-size:.68rem;margin:1px;"><?= htmlspecialchars($comp) ?></span><?php endforeach; ?></td>
                <td><?= statusBadge($c['statut']) ?></td>
                <td style="font-size:.78rem;color:var(--text-muted);"><?= dateFr($c['date_candidature'],'d/m/Y') ?></td>
                <td class="col-actions">
                    <button type="button" class="btn btn-xs btn-primary"
                            onclick="openModal(<?= $c['id'] ?>,'<?= htmlspecialchars(addslashes($c['prenom'].' '.$c['nom'])) ?>','<?= $c['statut'] ?>','<?= htmlspecialchars(addslashes($c['notes_admin']??'')) ?>')">
                        <i class="fas fa-edit"></i> Traiter
                    </button>
                    <a href="view.php?id=<?= $c['id'] ?>" class="btn btn-xs btn-secondary"><i class="fas fa-eye"></i></a>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="7"><div class="empty-state"><i class="fas fa-hands-helping"></i><h3>Aucune candidature</h3></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pages>1): ?>
    <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between;">
        <span>Page <?= $page ?>/<?= $pages ?></span>
        <div class="pagination"><?php for ($i=1;$i<=$pages;$i++): ?>
            <a href="?p=<?= $i ?>&statut=<?= $statut ?>&q=<?= urlencode($search) ?>" class="page-link <?= $i==$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?></div>
    </div>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="treatModal">
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <span class="modal-title">Traiter — <span id="modalName"></span></span>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
            <input type="hidden" name="cid" id="modalCid">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Statut</label>
                    <select name="action" id="modalAction" class="form-control">
                        <option value="en_attente">🕐 En attente</option>
                        <option value="contacte">📞 Contacté</option>
                        <option value="accepte">✅ Accepté</option>
                        <option value="refuse">❌ Refusé</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Notes internes</label>
                    <textarea name="notes_admin" id="modalNotes" class="form-control" rows="3" placeholder="Visible uniquement par l'équipe admin…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                <button type="submit" name="action" value="delete" onclick="return confirm('Supprimer ?')" class="btn btn-danger"><i class="fas fa-trash"></i></button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id,name,statut,notes){
    document.getElementById('modalCid').value=id;
    document.getElementById('modalName').textContent=name;
    document.getElementById('modalAction').value=statut;
    document.getElementById('modalNotes').value=notes;
    document.getElementById('treatModal').classList.add('show');
}
function closeModal(){ document.getElementById('treatModal').classList.remove('show'); }
</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
