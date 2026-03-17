<?php
/**
 * GSCC CMS — admin/campagnes/create.php + edit.php
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$id      = (int)($_GET['id'] ?? 0);
$is_edit = $id > 0;
$item    = null;
$errors  = [];

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM campagnes_projets WHERE id=?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) { adminFlash('error','Introuvable.'); header('Location:index.php'); exit; }
}

$page_title   = $is_edit ? 'Modifier' : 'Nouvelle campagne / projet';
$page_section = 'campagnes';
$breadcrumb   = [['label'=>'Campagnes','url'=>'index.php'],['label'=>$is_edit?'Modifier':'Créer']];

/* ── POST ── */
if ($_SERVER['REQUEST_METHOD']==='POST' && adminCheckCsrf()) {
    $titre        = trim($_POST['titre']??'');
    $type         = in_array($_POST['type']??'',['campagne','projet']) ? $_POST['type'] : 'campagne';
    $statut       = in_array($_POST['statut']??'',['a_venir','en_cours','termine']) ? $_POST['statut'] : 'a_venir';
    $description  = trim($_POST['description']??'');
    $contenu      = $_POST['contenu']??'';
    $date_debut   = $_POST['date_debut']??'';
    $date_fin     = $_POST['date_fin']??'';
    $objectif     = trim($_POST['objectif']??'');
    $objectif_m   = (float)($_POST['objectif_montant']??0);
    $progression  = min(100,max(0,(int)($_POST['progression']??0)));
    $lieu         = trim($_POST['lieu']??'');
    $contact      = trim($_POST['contact']??'');
    $partenaires  = trim($_POST['partenaires']??'');
    $est_actif    = isset($_POST['est_actif']) ? 1 : 0;
    $slug         = slugify($titre);

    if (!$titre)       $errors[] = 'Titre obligatoire.';
    if (!$description) $errors[] = 'Description obligatoire.';

    $image = $item['image_couverture'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $up = uploadFile($_FILES['image'], ROOT_PATH.'uploads/campagnes/', ['jpg','jpeg','png','webp']);
        if ($up['success']) $image = 'uploads/campagnes/'.$up['filename'];
        else $errors[] = $up['error'];
    }

    if (!$errors) {
        try {
            if ($is_edit) {
                $pdo->prepare(
                    "UPDATE campagnes_projets SET titre=?,slug=?,type=?,statut=?,description=?,contenu=?,
                     image_couverture=?,date_debut=?,date_fin=?,objectif=?,objectif_montant=?,
                     progression=?,lieu=?,contact=?,partenaires=?,est_actif=? WHERE id=?"
                )->execute([
                    $titre,$slug,$type,$statut,$description,$contenu,
                    $image,$date_debut?:null,$date_fin?:null,$objectif,$objectif_m?:null,
                    $progression,$lieu,$contact,$partenaires,$est_actif,$id
                ]);
            } else {
                $base=$slug; $i=0;
                while (true) {
                    $c=$pdo->prepare("SELECT id FROM campagnes_projets WHERE slug=?");
                    $c->execute([$slug]); if (!$c->fetch()) break;
                    $i++; $slug=$base.'-'.$i;
                }
                $pdo->prepare(
                    "INSERT INTO campagnes_projets (titre,slug,type,statut,description,contenu,
                     image_couverture,date_debut,date_fin,objectif,objectif_montant,
                     progression,lieu,contact,partenaires,est_actif,created_by,date_creation)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())"
                )->execute([
                    $titre,$slug,$type,$statut,$description,$contenu,
                    $image,$date_debut?:null,$date_fin?:null,$objectif,$objectif_m?:null,
                    $progression,$lieu,$contact,$partenaires,$est_actif,$_SESSION['admin_id']
                ]);
            }
            adminFlash('success', $is_edit ? 'Mis à jour !' : 'Créé avec succès !');
            header('Location:index.php'); exit;
        } catch (PDOException $e) {
            $errors[] = $e->getMessage();
        }
    }
}

$v = fn($f,$d='') => (isset($_POST[$f]) ? $_POST[$f] : ($item[$f] ?? $d));

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title"><?= $page_title ?></div>
        <div class="page-subtitle"><?= $is_edit ? htmlspecialchars($item['titre']) : 'Campagne ou projet GSCC' ?></div>
    </div>
    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i>
    <ul style="margin:0;padding-left:16px;"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
    <div style="display:grid;grid-template-columns:1fr 280px;gap:16px;align-items:start;">

        <div style="display:flex;flex-direction:column;gap:16px;">

            <!-- Infos principales -->
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-info-circle"></i> Informations principales</div></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Titre <span class="required">*</span></label>
                            <input type="text" name="titre" class="form-control" required
                                   value="<?= htmlspecialchars($v('titre')) ?>" placeholder="Ex. Octobre Rose 2026">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-control">
                                <option value="campagne" <?= $v('type','campagne')==='campagne'?'selected':'' ?>>🎯 Campagne</option>
                                <option value="projet"   <?= $v('type','campagne')==='projet'  ?'selected':'' ?>>🏗️ Projet</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description courte <span class="required">*</span></label>
                        <textarea name="description" class="form-control" rows="2" required
                                  placeholder="Résumé en 1-2 phrases…"><?= htmlspecialchars($v('description')) ?></textarea>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Contenu détaillé</label>
                        <textarea name="contenu" class="form-control" rows="8"
                                  placeholder="Contenu HTML complet…"><?= htmlspecialchars($v('contenu')) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Dates & lieu -->
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-calendar"></i> Dates & Localisation</div></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Date de début</label>
                            <input type="date" name="date_debut" class="form-control"
                                   value="<?= htmlspecialchars($v('date_debut')) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date de fin</label>
                            <input type="date" name="date_fin" class="form-control"
                                   value="<?= htmlspecialchars($v('date_fin')) ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Lieu</label>
                            <input type="text" name="lieu" class="form-control"
                                   value="<?= htmlspecialchars($v('lieu')) ?>"
                                   placeholder="Ex. Port-au-Prince, National…">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contact</label>
                            <input type="text" name="contact" class="form-control"
                                   value="<?= htmlspecialchars($v('contact')) ?>"
                                   placeholder="Email ou téléphone">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Partenaires</label>
                        <input type="text" name="partenaires" class="form-control"
                               value="<?= htmlspecialchars($v('partenaires')) ?>"
                               placeholder="Ex. Ministère de la Santé, OMS…">
                    </div>
                </div>
            </div>

            <!-- Objectifs -->
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-bullseye"></i> Objectifs & Progression</div></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Objectif qualitatif</label>
                            <input type="text" name="objectif" class="form-control"
                                   value="<?= htmlspecialchars($v('objectif')) ?>"
                                   placeholder="Ex. Sensibiliser 10 000 femmes">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Objectif financier ($)</label>
                            <input type="number" name="objectif_montant" class="form-control"
                                   value="<?= htmlspecialchars($v('objectif_montant','')) ?>"
                                   min="0" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Progression : <strong id="progVal"><?= $v('progression',0) ?>%</strong></label>
                        <input type="range" name="progression" id="progSlider"
                               min="0" max="100" step="5"
                               value="<?= (int)$v('progression',0) ?>"
                               style="width:100%;accent-color:var(--primary);"
                               oninput="document.getElementById('progVal').textContent=this.value+'%'">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar droite -->
        <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-rocket"></i> Paramètres</div></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-control">
                            <option value="a_venir"  <?= $v('statut','a_venir')==='a_venir' ?'selected':'' ?>>🕐 À venir</option>
                            <option value="en_cours" <?= $v('statut','a_venir')==='en_cours'?'selected':'' ?>>🟢 En cours</option>
                            <option value="termine"  <?= $v('statut','a_venir')==='termine' ?'selected':'' ?>>✅ Terminé</option>
                        </select>
                    </div>
                    <div class="switch-wrap" style="margin-bottom:16px;">
                        <label class="switch">
                            <input type="checkbox" name="est_actif" <?= $v('est_actif',1) ? 'checked' : '' ?>>
                            <span class="switch-slider"></span>
                        </label>
                        <span class="switch-label">Visible sur le site</span>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> <?= $is_edit ? 'Mettre à jour' : 'Créer' ?>
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-image"></i> Image</div></div>
                <div class="card-body">
                    <?php $img = $item['image_couverture'] ?? ''; ?>
                    <?php if ($img): ?>
                    <img src="<?= SITE_URL ?>/<?= htmlspecialchars($img) ?>"
                         id="imgPreview"
                         style="width:100%;border-radius:8px;margin-bottom:10px;max-height:160px;object-fit:cover;border:1px solid var(--border);"
                         onerror="this.style.display='none'">
                    <?php else: ?>
                    <img id="imgPreview" style="display:none;width:100%;border-radius:8px;margin-bottom:10px;max-height:160px;object-fit:cover;">
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control" style="font-size:.82rem;"
                           accept="image/*" onchange="previewImg(this)">
                    <div class="form-hint">JPG, PNG, WEBP — max 5 Mo</div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function previewImg(input) {
    if (input.files && input.files[0]) {
        const r = new FileReader();
        r.onload = e => {
            const p = document.getElementById('imgPreview');
            p.src = e.target.result; p.style.display = '';
        };
        r.readAsDataURL(input.files[0]);
    }
}
</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
