<?php
/**
 * GSCC CMS — admin/combattants/index.php
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
$page_title   = 'Nos Combattants';
$page_section = 'combattants';
$breadcrumb   = [['label' => 'Combattants']];

// ═══════════════════════════════════════════════════════════════
// TRAITEMENTS POST
// ═══════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $action  = $_POST['action']  ?? ($_POST['bulk_action'] ?? '');
    $cid     = (int)($_POST['cid'] ?? 0);
    $ids     = array_map('intval', $_POST['ids'] ?? []);

    // Changer statut
    if ($cid && in_array($action, ['publie', 'brouillon'])) {
        $pdo->prepare("UPDATE combattants SET statut=? WHERE id=?")->execute([$action, $cid]);
        adminFlash('success', 'Statut mis à jour.');
        header('Location: index.php'); exit;
    }

    // Suppression
    if (($cid || $ids) && $action === 'delete') {
        $del_ids = $ids ?: [$cid];
        foreach ($del_ids as $i) {
            $r = $pdo->prepare("SELECT photo FROM combattants WHERE id=?");
            $r->execute([$i]);
            $rw = $r->fetch();
            if ($rw && $rw['photo'] && file_exists(ROOT_PATH . 'uploads/' . $rw['photo'])) {
                @unlink(ROOT_PATH . 'uploads/' . $rw['photo']);
            }
        }
        $pdo->prepare("DELETE FROM combattants WHERE id IN (" . implode(',', $del_ids) . ")")->execute();
        adminFlash('success', count($del_ids) > 1 ? count($del_ids) . ' combattants supprimés.' : 'Combattant supprimé.');
        header('Location: index.php'); exit;
    }

    // Enregistrement (création ou édition)
    if (in_array($action, ['save_new', 'save_edit'])) {
        $prenom  = trim($_POST['prenom']  ?? '');
        $nom     = trim($_POST['nom']     ?? '');
        $cancer  = trim($_POST['cancer_type']  ?? '');
        $annees  = max(1, (int)($_POST['annees_combat']  ?? 1));
        $age     = (int)($_POST['age_diagnostic'] ?? 0);
        $ville   = trim($_POST['ville']   ?? 'Haïti');
        $court   = trim($_POST['histoire_courte'] ?? '');
        $long    = $_POST['histoire_longue'] ?? '';
        $force   = trim($_POST['message_force'] ?? '');
        $statut  = in_array($_POST['statut_c'] ?? '', ['publie', 'brouillon']) ? $_POST['statut_c'] : 'publie';
        $edit_id = (int)($_POST['edit_id'] ?? 0);

        if (!$prenom || !$nom || !$cancer || !$court) {
            adminFlash('error', 'Les champs Prénom, Nom, Type de cancer et Histoire courte sont obligatoires.');
            header('Location: index.php'); exit;
        }

        // Upload photo
        $photo = '';
        if (!empty($_FILES['photo']['name'])) {
            $dir = ROOT_PATH . 'uploads/combattants/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $up = uploadFile($_FILES['photo'], $dir, ['jpg', 'jpeg', 'png', 'webp']);
            if ($up['success']) $photo = 'combattants/' . $up['filename'];
        }

        if ($action === 'save_edit' && $edit_id) {
            $sql = "UPDATE combattants SET prenom=?,nom=?,cancer_type=?,annees_combat=?,age_diagnostic=?,ville=?,histoire_courte=?,histoire_longue=?,message_force=?,statut=?";
            $p   = [$prenom,$nom,$cancer,$annees,$age?:null,$ville,$court,$long?:null,$force?:null,$statut];
            if ($photo) { $sql .= ',photo=?'; $p[] = $photo; }
            $sql .= " WHERE id=?"; $p[] = $edit_id;
            $pdo->prepare($sql)->execute($p);
            adminFlash('success', 'Combattant mis à jour.');
        } else {
            $pdo->prepare("INSERT INTO combattants (prenom,nom,photo,cancer_type,annees_combat,age_diagnostic,ville,histoire_courte,histoire_longue,message_force,statut,date_creation) VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW())")
                ->execute([$prenom,$nom,$photo?:null,$cancer,$annees,$age?:null,$ville,$court,$long?:null,$force?:null,$statut]);
            adminFlash('success', 'Combattant ajouté avec succès !');
        }
        header('Location: index.php'); exit;
    }
}

// ═══════════════════════════════════════════════════════════════
// LECTURE — liste paginée
// ═══════════════════════════════════════════════════════════════
$statut_f = $_GET['statut'] ?? '';
$page     = max(1, (int)($_GET['p'] ?? 1));
$per      = 20;
$where    = ['1=1']; $params = [];
if ($statut_f) { $where[] = "statut=?"; $params[] = $statut_f; }
$sw = implode(' AND ', $where);

try {
    $cnt    = $pdo->prepare("SELECT COUNT(*) FROM combattants WHERE $sw");
    $cnt->execute($params); $total = (int)$cnt->fetchColumn();
    $pages  = (int)ceil($total / $per);
    $offset = ($page - 1) * $per;
    $stmt   = $pdo->prepare("SELECT * FROM combattants WHERE $sw ORDER BY date_creation DESC LIMIT $per OFFSET $offset");
    $stmt->execute($params); $combattants = $stmt->fetchAll();

    $nb_pub     = (int)$pdo->query("SELECT COUNT(*) FROM combattants WHERE statut='publie'")->fetchColumn();
    $nb_bro     = (int)$pdo->query("SELECT COUNT(*) FROM combattants WHERE statut='brouillon'")->fetchColumn();
    $nb_complet = (int)$pdo->query("SELECT COUNT(*) FROM combattants WHERE histoire_longue IS NOT NULL AND histoire_longue <> ''")->fetchColumn();
} catch (PDOException $e) {
    $combattants = []; $total = $pages = $nb_pub = $nb_bro = $nb_complet = 0;
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">
            Nos Combattants
            <span style="font-size:1rem;font-weight:400;color:var(--text-muted);">(<?= $total ?>)</span>
        </div>
        <div class="page-subtitle">Personnes vivant avec le cancer affichées sur le site</div>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('cbModal').classList.add('show')">
        <i class="fas fa-plus"></i> Ajouter un combattant
    </button>
</div>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:18px;">
    <div class="stat-card">
        <div class="stat-icon rose"><i class="fas fa-shield-heart"></i></div>
        <div class="stat-info"><div class="stat-value"><?= $total ?></div><div class="stat-label">Total combattants</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-eye"></i></div>
        <div class="stat-info"><div class="stat-value"><?= $nb_pub ?></div><div class="stat-label">Publiés</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-eye-slash"></i></div>
        <div class="stat-info"><div class="stat-value"><?= $nb_bro ?></div><div class="stat-label">Brouillons</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-book-open"></i></div>
        <div class="stat-info"><div class="stat-value"><?= $nb_complet ?></div><div class="stat-label">Histoire complète</div></div>
    </div>
</div>

<!-- Lien vers la page publique -->
<div style="margin-bottom:16px;">
    <a href="<?= SITE_URL ?>/combattants.php" target="_blank" class="btn btn-secondary btn-sm">
        <i class="fas fa-external-link-alt"></i> Voir la page publique
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="?statut="        class="btn btn-<?= !$statut_f?'primary':'secondary' ?> btn-sm">Tous (<?= $total ?>)</a>
            <a href="?statut=publie"  class="btn btn-<?= $statut_f==='publie'?'primary':'secondary' ?> btn-sm">Publiés (<?= $nb_pub ?>)</a>
            <a href="?statut=brouillon" class="btn btn-<?= $statut_f==='brouillon'?'primary':'secondary' ?> btn-sm">Brouillons (<?= $nb_bro ?>)</a>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
        <div style="display:flex;gap:10px;padding:10px 16px;background:#FAFBFF;border-bottom:1px solid var(--border);">
            <select name="bulk_action" class="form-control" style="width:200px;">
                <option value="">— Action groupée —</option>
                <option value="delete">Supprimer la sélection</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Supprimer les éléments sélectionnés ?')">Appliquer</button>
        </div>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="col-check"><input type="checkbox" id="selectAll"></th>
                        <th>Combattant</th>
                        <th>Type de cancer</th>
                        <th>Années de combat</th>
                        <th>Âge diagnostic</th>
                        <th>Ville</th>
                        <th>Statut</th>
                        <th class="col-actions" style="width:150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($combattants): foreach ($combattants as $c): ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?= $c['id'] ?>" class="row-check"></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <?php if ($c['photo'] && file_exists(ROOT_PATH . 'uploads/' . $c['photo'])): ?>
                                <img src="<?= SITE_URL ?>/uploads/<?= htmlspecialchars($c['photo']) ?>"
                                     class="avatar avatar-sm" style="object-fit:cover;"
                                     onerror="this.style.display='none'">
                            <?php else: ?>
                                <div class="avatar avatar-sm" style="background:#EDE9FE;color:#6D28D9;font-size:.9rem;">🛡️</div>
                            <?php endif; ?>
                            <div>
                                <div class="fw-600"><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></div>
                                <div style="font-size:.76rem;color:var(--text-muted);"><?= htmlspecialchars(truncate($c['histoire_courte'], 45)) ?></div>
                                <?php if (!empty($c['histoire_longue'])): ?>
                                <div style="font-size:.70rem;color:var(--success);margin-top:2px;"><i class="fas fa-book-open"></i> Histoire complète</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge" style="background:#EDE9FE;color:#6D28D9;">
                            <?= htmlspecialchars($c['cancer_type']) ?>
                        </span>
                    </td>
                    <td style="text-align:center;">
                        <strong style="font-size:1rem;color:var(--rose);"><?= $c['annees_combat'] ?></strong>
                        <span style="font-size:.75rem;color:var(--text-muted);"> an(s)</span>
                    </td>
                    <td style="text-align:center;font-size:.84rem;">
                        <?= $c['age_diagnostic'] ? $c['age_diagnostic'] . ' ans' : '—' ?>
                    </td>
                    <td style="font-size:.84rem;"><?= htmlspecialchars($c['ville'] ?: '—') ?></td>
                    <td><?= statusBadge($c['statut']) ?></td>
                    <td class="col-actions">
                        <!-- Éditer -->
                        <button type="button" class="btn btn-xs btn-primary"
                                onclick="cbEdit(<?= htmlspecialchars(json_encode($c)) ?>)"
                                title="Modifier">
                            <i class="fas fa-pen"></i>
                        </button>
                        <!-- Publier / Masquer -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                            <input type="hidden" name="cid"    value="<?= $c['id'] ?>">
                            <input type="hidden" name="action" value="<?= $c['statut']==='publie'?'brouillon':'publie' ?>">
                            <button type="submit" class="btn btn-xs btn-secondary"
                                    title="<?= $c['statut']==='publie'?'Masquer':'Publier' ?>">
                                <i class="fas fa-<?= $c['statut']==='publie'?'eye-slash':'eye' ?>"></i>
                            </button>
                        </form>
                        <!-- Supprimer -->
                        <form method="POST" style="display:inline;"
                              onsubmit="return confirm('Supprimer ce combattant ?')">
                            <input type="hidden" name="_csrf"   value="<?= adminCsrfToken() ?>">
                            <input type="hidden" name="cid"    value="<?= $c['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-xs btn-danger" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="fas fa-shield-heart"></i>
                            <h3>Aucun combattant</h3>
                            <p>Ajoutez des histoires de personnes vivant avec le cancer.</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>

    <?php if ($pages > 1): ?>
    <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between;">
        <span>Page <?= $page ?>/<?= $pages ?></span>
        <div class="pagination">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a href="?statut=<?= $statut_f ?>&p=<?= $i ?>"
                   class="page-link <?= $i==$page?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════
     MODAL AJOUT / ÉDITION
     ═══════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="cbModal">
    <div class="modal" style="max-width:660px;">
        <div class="modal-header">
            <span class="modal-title" id="cbModalTitle">Ajouter un combattant</span>
            <button class="modal-close"
                    onclick="document.getElementById('cbModal').classList.remove('show')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_csrf"    value="<?= adminCsrfToken() ?>">
            <input type="hidden" name="action"   id="cbAction" value="save_new">
            <input type="hidden" name="edit_id"  id="cbEditId" value="0">

            <div class="modal-body" style="max-height:72vh;overflow-y:auto;">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Prénom <span class="required">*</span></label>
                        <input type="text" name="prenom" id="cbPrenom" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nom <span class="required">*</span></label>
                        <input type="text" name="nom" id="cbNom" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Type de cancer <span class="required">*</span></label>
                        <input type="text" name="cancer_type" id="cbCancer" class="form-control"
                               required placeholder="Ex. Cancer du sein, Leucémie…">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Années de combat <span class="required">*</span></label>
                        <input type="number" name="annees_combat" id="cbAnnees" class="form-control"
                               required min="1" value="1">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Âge au diagnostic</label>
                        <input type="number" name="age_diagnostic" id="cbAge" class="form-control"
                               min="0" placeholder="ex. 42">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ville</label>
                        <input type="text" name="ville" id="cbVille" class="form-control"
                               value="Haïti" placeholder="Port-au-Prince…">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Histoire courte <span class="required">*</span>
                        <small style="color:var(--text-muted);font-weight:400;">(résumé affiché sur la carte)</small>
                    </label>
                    <textarea name="histoire_courte" id="cbCourt" class="form-control" rows="3"
                              required placeholder="Quelques lignes résumant le parcours de cette personne…"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Histoire complète
                        <small style="color:var(--text-muted);font-weight:400;">(activera le bouton « Lire la suite »)</small>
                    </label>
                    <textarea name="histoire_longue" id="cbLong" class="form-control" rows="6"
                              placeholder="Témoignage détaillé… Chaque ligne vide crée un nouveau paragraphe."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Message de force</label>
                    <textarea name="message_force" id="cbForce" class="form-control" rows="2"
                              placeholder="Citation inspirante ou message pour les autres combattants…"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Statut</label>
                        <select name="statut_c" id="cbStatut" class="form-control">
                            <option value="publie">✅ Publié</option>
                            <option value="brouillon">📝 Brouillon</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Photo</label>
                        <!-- Prévisualisation photo actuelle (édition) -->
                        <div id="cbCurrentPhoto" style="display:none;margin-bottom:8px;">
                            <img id="cbCurrentPhotoImg" src="" alt="Photo actuelle"
                                 style="width:64px;height:64px;border-radius:50%;object-fit:cover;
                                        border:3px solid var(--border);box-shadow:0 2px 8px rgba(0,0,0,.12);">
                            <div style="font-size:.72rem;color:var(--text-muted);margin-top:4px;">
                                Photo actuelle — laisser vide pour conserver
                            </div>
                        </div>
                        <input type="file" name="photo" id="cbPhotoInput" class="form-control"
                               accept="image/*" style="font-size:.82rem;"
                               onchange="cbPreviewNewPhoto(this)">
                        <!-- Prévisualisation nouvelle photo sélectionnée -->
                        <div id="cbNewPhotoPreview" style="display:none;margin-top:8px;display:flex;align-items:center;gap:8px;">
                            <img id="cbNewPhotoImg" src="" alt=""
                                 style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--success);">
                            <span style="font-size:.74rem;color:var(--success);font-weight:600;">
                                <i class="fas fa-check-circle"></i> Nouvelle photo sélectionnée
                            </span>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button"
                        onclick="document.getElementById('cbModal').classList.remove('show')"
                        class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
});

function cbEdit(c) {
    document.getElementById('cbModalTitle').textContent = 'Modifier : ' + c.prenom + ' ' + c.nom;
    document.getElementById('cbAction').value  = 'save_edit';
    document.getElementById('cbEditId').value  = c.id;
    document.getElementById('cbPrenom').value  = c.prenom;
    document.getElementById('cbNom').value     = c.nom;
    document.getElementById('cbCancer').value  = c.cancer_type;
    document.getElementById('cbAnnees').value  = c.annees_combat;
    document.getElementById('cbAge').value     = c.age_diagnostic || '';
    document.getElementById('cbVille').value   = c.ville || '';
    document.getElementById('cbCourt').value   = c.histoire_courte;
    document.getElementById('cbLong').value    = c.histoire_longue || '';
    document.getElementById('cbForce').value   = c.message_force || '';
    document.getElementById('cbStatut').value  = c.statut;

    // Prévisualisation photo actuelle
    var photoWrap = document.getElementById('cbCurrentPhoto');
    var photoImg  = document.getElementById('cbCurrentPhotoImg');
    if (c.photo) {
        photoImg.src = '<?= SITE_URL ?>/uploads/' + c.photo;
        photoWrap.style.display = 'block';
    } else {
        photoWrap.style.display = 'none';
        photoImg.src = '';
    }
    // Réinitialiser la prévisualisation nouvelle photo
    document.getElementById('cbNewPhotoPreview').style.display = 'none';
    document.getElementById('cbPhotoInput').value = '';

    document.getElementById('cbModal').classList.add('show');
}

function cbPreviewNewPhoto(input) {
    var preview = document.getElementById('cbNewPhotoPreview');
    var img     = document.getElementById('cbNewPhotoImg');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.style.display = 'flex';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}

// Réinitialiser le modal à l'ouverture pour un ajout
document.querySelector('[onclick*="cbModal"]')?.addEventListener('click', function() {
    document.getElementById('cbModalTitle').textContent = 'Ajouter un combattant';
    document.getElementById('cbAction').value  = 'save_new';
    document.getElementById('cbEditId').value  = '0';
    document.getElementById('cbCurrentPhoto').style.display = 'none';
    document.getElementById('cbNewPhotoPreview').style.display = 'none';
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
