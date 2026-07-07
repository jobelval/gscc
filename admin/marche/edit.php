<?php
/**
 * GSCC CMS — admin/marche/edit.php  (create + edit)
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$id      = (int)($_GET['id'] ?? 0);
$is_edit = $id > 0;
$item    = null;
$errors  = [];

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM marche_editions WHERE id=?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) { adminFlash('error', 'Édition introuvable.'); header('Location:index.php'); exit; }
}

$page_title   = $is_edit ? 'Modifier l\'édition ' . ($item['annee'] ?? '') : 'Nouvelle édition';
$page_section = 'marche';
$breadcrumb   = [['label' => 'Marche', 'url' => 'index.php'], ['label' => $is_edit ? 'Modifier' : 'Créer']];

/* ── POST ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $annee         = (int)($_POST['annee'] ?? date('Y'));
    $theme         = trim($_POST['theme'] ?? '');
    $date_ev       = $_POST['date_evenement'] ?? '';
    $statut        = in_array($_POST['statut'] ?? '', ['a_venir','en_cours','termine','annule'])
                     ? $_POST['statut'] : 'a_venir';
    $participants  = trim($_POST['participants'] ?? '');
    $km            = trim($_POST['km'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $ordre         = (int)($_POST['ordre'] ?? 0);

    if (!$theme)       $errors[] = 'Le thème est obligatoire.';
    if (!$annee)       $errors[] = 'L\'année est obligatoire.';
    if (!$description) $errors[] = 'La description est obligatoire.';

    // Upload image
    $image = $item['image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $upDir = ROOT_PATH . 'uploads/marche/';
        if (!is_dir($upDir)) mkdir($upDir, 0755, true);
        $up = uploadFile($_FILES['image'], $upDir, ['jpg','jpeg','png','webp']);
        if ($up['success']) {
            // Supprimer l'ancienne image
            if ($image && file_exists(ROOT_PATH . $image)) unlink(ROOT_PATH . $image);
            $image = 'uploads/marche/' . $up['filename'];
        } else {
            $errors[] = $up['error'] ?? 'Erreur upload image.';
        }
    }

    if (!$errors) {
        try {
            if ($is_edit) {
                $pdo->prepare(
                    "UPDATE marche_editions SET annee=?,theme=?,date_evenement=?,statut=?,
                     participants=?,km=?,description=?,image=?,ordre=? WHERE id=?"
                )->execute([
                    $annee, $theme, $date_ev ?: null, $statut,
                    $participants ?: null, $km ?: null, $description,
                    $image, $ordre, $id
                ]);
            } else {
                $pdo->prepare(
                    "INSERT INTO marche_editions
                     (annee,theme,date_evenement,statut,participants,km,description,image,ordre)
                     VALUES (?,?,?,?,?,?,?,?,?)"
                )->execute([
                    $annee, $theme, $date_ev ?: null, $statut,
                    $participants ?: null, $km ?: null, $description,
                    $image, $ordre
                ]);
            }
            adminFlash('success', $is_edit ? 'Édition mise à jour !' : 'Édition créée !');
            header('Location: index.php'); exit;
        } catch (PDOException $e) {
            $errors[] = $e->getMessage();
        }
    }
}

$v = fn($f, $d = '') => $_POST[$f] ?? ($item[$f] ?? $d);

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title"><?= htmlspecialchars($page_title) ?></div>
        <div class="page-subtitle">Marche Contre le Cancer — édition annuelle</div>
    </div>
    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger" style="margin-bottom:16px;">
    <i class="fas fa-exclamation-circle"></i>
    <ul style="margin:0;padding-left:18px;"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">

        <!-- Colonne principale -->
        <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-person-walking"></i> Informations de l'édition</div></div>
                <div class="card-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group">
                            <label class="form-label">Année <span style="color:var(--danger)">*</span></label>
                            <input type="number" name="annee" class="form-control"
                                   value="<?= htmlspecialchars((string)$v('annee', date('Y'))) ?>"
                                   min="2000" max="2100" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date de l'événement</label>
                            <input type="date" name="date_evenement" class="form-control"
                                   value="<?= htmlspecialchars($v('date_evenement')) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Thème <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="theme" class="form-control"
                               value="<?= htmlspecialchars($v('theme')) ?>"
                               placeholder="Ex: Ensemble, plus forts que le cancer" required>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Description <span style="color:var(--danger)">*</span></label>
                        <textarea name="description" class="form-control" rows="6"
                                  placeholder="Décrivez cette édition de la marche…" required><?= htmlspecialchars($v('description')) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-chart-bar"></i> Statistiques</div></div>
                <div class="card-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group">
                            <label class="form-label">Nombre de participants</label>
                            <input type="text" name="participants" class="form-control"
                                   value="<?= htmlspecialchars($v('participants')) ?>"
                                   placeholder="Ex: 3 200+">
                            <div class="form-hint">Laisser vide si édition à venir</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Distance</label>
                            <input type="text" name="km" class="form-control"
                                   value="<?= htmlspecialchars($v('km', '5 km')) ?>"
                                   placeholder="Ex: 5 km">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne latérale -->
        <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-cog"></i> Paramètres</div></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-control">
                            <?php foreach (['a_venir'=>'À venir','en_cours'=>'En cours','termine'=>'Terminée','annule'=>'Annulée'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= $v('statut','a_venir') === $val ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Ordre d'affichage</label>
                        <input type="number" name="ordre" class="form-control"
                               value="<?= (int)$v('ordre', 0) ?>" min="0">
                        <div class="form-hint">Plus petit = affiché en premier</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-image"></i> Image</div></div>
                <div class="card-body">
                    <?php if ($is_edit && !empty($item['image'])): ?>
                    <div style="margin-bottom:12px;">
                        <img src="<?= SITE_URL ?>/<?= htmlspecialchars($item['image']) ?>"
                             style="width:100%;height:140px;object-fit:cover;border-radius:8px;" alt="">
                        <div style="font-size:.75rem;color:var(--text-muted);margin-top:4px;">Image actuelle</div>
                    </div>
                    <?php endif; ?>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label"><?= ($is_edit && !empty($item['image'])) ? 'Changer l\'image' : 'Image' ?></label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div class="form-hint">JPG, PNG, WEBP — Max 5 Mo</div>
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <i class="fas fa-save"></i> <?= $is_edit ? 'Enregistrer les modifications' : 'Créer l\'édition' ?>
                </button>
            </div>
        </div>

    </div>
</form>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
