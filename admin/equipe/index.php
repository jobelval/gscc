<?php
/**
 * GSCC CMS — admin/equipe/index.php
 * Gestion des membres de l'équipe
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$page_title   = 'Équipe';
$page_section = 'equipe';
$breadcrumb   = [['label' => 'Équipe GSCC']];

/* ── Actions POST ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $action = $_POST['action'] ?? '';
    $eid    = (int)($_POST['eid'] ?? 0);

    if ($action === 'save') {
        $nom      = trim($_POST['nom'] ?? '');
        $prenom   = trim($_POST['prenom'] ?? '');
        $fonction = trim($_POST['fonction'] ?? '');
        $bio      = trim($_POST['bio'] ?? '');
        $email    = trim($_POST['email_eq'] ?? '');
        $tel      = trim($_POST['telephone'] ?? '');
        $ordre    = (int)($_POST['ordre'] ?? 0);
        $actif    = isset($_POST['est_actif']) ? 1 : 0;
        $rs_json  = null;

        // Réseaux sociaux
        $rs = [];
        foreach (['facebook','twitter','linkedin','instagram'] as $r) {
            if (!empty($_POST['rs_'.$r])) $rs[$r] = trim($_POST['rs_'.$r]);
        }
        if ($rs) $rs_json = json_encode($rs);

        $photo = '';
        if ($eid) {
            $cur = $pdo->prepare("SELECT photo FROM equipe WHERE id=?"); $cur->execute([$eid]);
            $photo = $cur->fetchColumn() ?: '';
        }

        if (!empty($_FILES['photo']['name'])) {
            $equipeDir = ROOT_PATH . 'uploads/equipe/';
            if (!is_dir($equipeDir)) mkdir($equipeDir, 0755, true);
            $up = uploadFile($_FILES['photo'], $equipeDir, ['jpg','jpeg','png','webp']);
            if ($up['success']) $photo = 'uploads/equipe/' . $up['filename'];
        }

        if ($nom && $prenom) {
            if ($eid) {
                $pdo->prepare("UPDATE equipe SET nom=?,prenom=?,fonction=?,bio=?,photo=?,email=?,telephone=?,ordre=?,est_actif=?,reseaux_sociaux=? WHERE id=?")
                    ->execute([$nom,$prenom,$fonction,$bio?:null,$photo?:null,$email?:null,$tel?:null,$ordre,$actif,$rs_json,$eid]);
                adminFlash('success', 'Membre mis à jour.');
            } else {
                $pdo->prepare("INSERT INTO equipe (nom,prenom,fonction,bio,photo,email,telephone,ordre,est_actif,reseaux_sociaux,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,NOW())")
                    ->execute([$nom,$prenom,$fonction,$bio?:null,$photo?:null,$email?:null,$tel?:null,$ordre,$actif,$rs_json]);
                adminFlash('success', 'Membre ajouté !');
            }
        } else { adminFlash('error','Nom et prénom obligatoires.'); }
        header('Location: index.php'); exit;
    }

    if ($action === 'delete' && $eid) {
        $pdo->prepare("DELETE FROM equipe WHERE id=?")->execute([$eid]);
        adminFlash('success', 'Membre supprimé.');
        header('Location: index.php'); exit;
    }

    if ($action === 'toggle' && $eid) {
        $cur = $pdo->prepare("SELECT est_actif FROM equipe WHERE id=?"); $cur->execute([$eid]);
        $cur = $cur->fetchColumn();
        $pdo->prepare("UPDATE equipe SET est_actif=? WHERE id=?")->execute([$cur?0:1, $eid]);
        header('Location: index.php'); exit;
    }

    if ($action === 'reorder') {
        $order = array_map('intval', $_POST['order'] ?? []);
        foreach ($order as $i => $id) {
            $pdo->prepare("UPDATE equipe SET ordre=? WHERE id=?")->execute([$i+1, $id]);
        }
        echo 'ok'; exit;
    }
}

try {
    $membres = $pdo->query("SELECT * FROM equipe ORDER BY ordre ASC, created_at ASC")->fetchAll();
} catch (PDOException $e) { $membres = []; }

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Équipe GSCC <span style="font-size:1rem;font-weight:400;color:var(--text-muted);">(<?= count($membres) ?> membres)</span></div>
        <div class="page-subtitle">Gérer les membres et leur ordre d'affichage</div>
    </div>
    <button class="btn btn-primary" onclick="openForm()"><i class="fas fa-user-plus"></i> Ajouter un membre</button>
</div>

<!-- Grille équipe -->
<?php if ($membres): ?>
<div id="equipeGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;margin-bottom:20px;">
    <?php foreach ($membres as $m):
        $rs = [];
        try { $rs = json_decode($m['reseaux_sociaux']??'{}',true)?:[]; } catch(Exception $e){}
    ?>
    <div class="card" data-id="<?= $m['id'] ?>" style="<?= !$m['est_actif']?'opacity:.55;':'' ?>;transition:box-shadow .2s;" id="member-<?= $m['id'] ?>">
        <div style="padding:16px;">
            <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:12px;">
                <!-- Drag handle -->
                <div style="cursor:grab;color:#CBD5E1;padding-top:4px;font-size:16px;" title="Glisser pour réordonner">⠿</div>

                <!-- Photo -->
                <?php if ($m['photo'] && file_exists(ROOT_PATH . $m['photo'])): ?>
                    <img src="<?= SITE_URL ?>/<?= htmlspecialchars($m['photo']) ?>"
                         style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0;"
                         onerror="this.style.display='none'">
                <?php else: ?>
                    <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--rose));display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;font-weight:700;flex-shrink:0;">
                        <?= strtoupper(substr($m['prenom'],0,1).substr($m['nom'],0,1)) ?>
                    </div>
                <?php endif; ?>

                <div style="flex:1;min-width:0;">
                    <div style="font-weight:700;font-size:.95rem;color:var(--text);"><?= htmlspecialchars($m['prenom'].' '.$m['nom']) ?></div>
                    <div style="font-size:.8rem;color:var(--primary);font-weight:600;margin-top:2px;"><?= htmlspecialchars($m['fonction']?:'—') ?></div>
                    <?= statusBadge($m['est_actif'] ? 'actif' : 'inactif') ?>
                </div>
            </div>

            <?php if ($m['bio']): ?>
            <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:10px;line-height:1.6;"><?= htmlspecialchars(truncate($m['bio'],90)) ?></p>
            <?php endif; ?>

            <?php if ($m['email'] || $m['telephone']): ?>
            <div style="font-size:.76rem;color:var(--text-muted);margin-bottom:10px;">
                <?php if($m['email']): ?><div><i class="fas fa-envelope" style="width:12px;"></i> <?= htmlspecialchars($m['email']) ?></div><?php endif; ?>
                <?php if($m['telephone']): ?><div><i class="fas fa-phone" style="width:12px;"></i> <?= htmlspecialchars($m['telephone']) ?></div><?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($rs): ?>
            <div style="display:flex;gap:6px;margin-bottom:10px;">
                <?php foreach (['facebook'=>'fab fa-facebook','twitter'=>'fab fa-twitter','linkedin'=>'fab fa-linkedin','instagram'=>'fab fa-instagram'] as $k=>$ic): if(empty($rs[$k])) continue; ?>
                <a href="<?= htmlspecialchars($rs[$k]) ?>" target="_blank" style="width:26px;height:26px;border-radius:5px;background:var(--body-bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:12px;text-decoration:none;">
                    <i class="<?= $ic ?>"></i>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div style="display:flex;gap:6px;">
                <button type="button" class="btn btn-xs btn-primary" style="flex:1;justify-content:center;"
                        onclick="editMember(<?= htmlspecialchars(json_encode($m)) ?>)">
                    <i class="fas fa-pen"></i> Modifier
                </button>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                    <input type="hidden" name="eid" value="<?= $m['id'] ?>">
                    <input type="hidden" name="action" value="toggle">
                    <button type="submit" class="btn btn-xs btn-secondary" title="<?= $m['est_actif']?'Désactiver':'Activer' ?>">
                        <i class="fas fa-<?= $m['est_actif']?'eye-slash':'eye' ?>"></i>
                    </button>
                </form>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce membre ?')">
                    <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                    <input type="hidden" name="eid" value="<?= $m['id'] ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state"><i class="fas fa-users"></i><h3>Aucun membre</h3><p>Ajoutez les membres de l'équipe GSCC.</p></div>
<?php endif; ?>

<!-- Modal ajouter/éditer -->
<div class="modal-overlay" id="memberModal">
    <div class="modal" style="max-width:620px;">
        <div class="modal-header">
            <span class="modal-title" id="modalTitle">Ajouter un membre</span>
            <button class="modal-close" onclick="closeForm()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="eid" id="mEid" value="0">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Prénom <span class="required">*</span></label>
                        <input type="text" name="prenom" id="mPrenom" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nom <span class="required">*</span></label>
                        <input type="text" name="nom" id="mNom" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Fonction / Poste</label>
                    <input type="text" name="fonction" id="mFonc" class="form-control" placeholder="Ex. Présidente Fondatrice…">
                </div>
                <div class="form-group">
                    <label class="form-label">Biographie</label>
                    <textarea name="bio" id="mBio" class="form-control" rows="3" placeholder="Courte biographie…"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email_eq" id="mEmail" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" id="mTel" class="form-control">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Ordre d'affichage</label>
                        <input type="number" name="ordre" id="mOrdre" class="form-control" value="0" min="0">
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:4px;">
                        <div class="switch-wrap">
                            <label class="switch"><input type="checkbox" name="est_actif" id="mActif" checked><span class="switch-slider"></span></label>
                            <span class="switch-label">Membre actif</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Photo</label>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <img id="mPhotoPreview" style="width:50px;height:50px;border-radius:50%;object-fit:cover;border:1px solid var(--border);display:none;">
                        <input type="file" name="photo" class="form-control" accept="image/*" style="font-size:.82rem;"
                               onchange="previewMPhoto(this)">
                    </div>
                </div>
                <details style="margin-top:8px;">
                    <summary style="cursor:pointer;font-size:.84rem;font-weight:600;color:var(--primary);margin-bottom:10px;">🌐 Réseaux sociaux (optionnel)</summary>
                    <div style="margin-top:10px;">
                        <?php foreach(['facebook'=>'fab fa-facebook','twitter'=>'fab fa-twitter','linkedin'=>'fab fa-linkedin','instagram'=>'fab fa-instagram'] as $k=>$ic): ?>
                        <div class="form-group" style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                            <i class="<?= $ic ?>" style="color:var(--primary);width:16px;"></i>
                            <input type="url" name="rs_<?= $k ?>" id="mRs_<?= $k ?>" class="form-control" placeholder="URL <?= ucfirst($k) ?>…">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </details>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeForm()" class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
function openForm(){ document.getElementById('memberModal').classList.add('show'); }
function closeForm(){ document.getElementById('memberModal').classList.remove('show'); }

function editMember(m) {
    document.getElementById('modalTitle').textContent = 'Modifier ' + m.prenom + ' ' + m.nom;
    document.getElementById('mEid').value    = m.id;
    document.getElementById('mPrenom').value = m.prenom;
    document.getElementById('mNom').value    = m.nom;
    document.getElementById('mFonc').value   = m.fonction || '';
    document.getElementById('mBio').value    = m.bio || '';
    document.getElementById('mEmail').value  = m.email || '';
    document.getElementById('mTel').value    = m.telephone || '';
    document.getElementById('mOrdre').value  = m.ordre;
    document.getElementById('mActif').checked = m.est_actif == 1;

    // Photo preview
    const prev = document.getElementById('mPhotoPreview');
    if (m.photo) { prev.src = '<?= SITE_URL ?>/'+m.photo; prev.style.display=''; }
    else { prev.style.display='none'; }

    // Réseaux
    try {
        const rs = m.reseaux_sociaux ? JSON.parse(m.reseaux_sociaux) : {};
        ['facebook','twitter','linkedin','instagram'].forEach(k => {
            const el = document.getElementById('mRs_'+k);
            if(el) el.value = rs[k] || '';
        });
    } catch(e){}

    openForm();
}

function previewMPhoto(input) {
    if (input.files && input.files[0]) {
        const r = new FileReader(); r.onload = e => {
            const p = document.getElementById('mPhotoPreview');
            p.src = e.target.result; p.style.display='';
        }; r.readAsDataURL(input.files[0]);
    }
}
</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
