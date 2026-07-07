<?php
/**
 * GSCC CMS — admin/sensibilisation/edit.php  (create + edit)
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$id      = (int)($_GET['id'] ?? 0);
$is_edit = $id > 0;
$item    = null;
$errors  = [];

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM sensibilisation_activites WHERE id=?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) { adminFlash('error', 'Activité introuvable.'); header('Location:index.php'); exit; }
}

$page_title   = $is_edit ? 'Modifier : ' . ($item['titre'] ?? '') : 'Nouvelle activité';
$page_section = 'sensibilisation';
$breadcrumb   = [['label' => 'Sensibilisation', 'url' => 'index.php'], ['label' => $is_edit ? 'Modifier' : 'Créer']];

/* ── POST ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $titre       = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $couleur     = trim($_POST['couleur'] ?? '#E91E8C');
    $ordre       = (int)($_POST['ordre'] ?? 0);
    $est_actif   = isset($_POST['est_actif']) ? 1 : 0;

    if (!$titre)       $errors[] = 'Le titre est obligatoire.';
    if (!$description) $errors[] = 'La description est obligatoire.';
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $couleur)) $couleur = '#E91E8C';

    if (!$errors) {
        try {
            if ($is_edit) {
                $pdo->prepare(
                    "UPDATE sensibilisation_activites
                     SET titre=?, description=?, couleur=?, ordre=?, est_actif=?
                     WHERE id=?"
                )->execute([$titre, $description, $couleur, $ordre, $est_actif, $id]);
            } else {
                $pdo->prepare(
                    "INSERT INTO sensibilisation_activites (titre, description, couleur, ordre, est_actif)
                     VALUES (?, ?, ?, ?, ?)"
                )->execute([$titre, $description, $couleur, $ordre, $est_actif]);
            }
            adminFlash('success', $is_edit ? 'Activité mise à jour !' : 'Activité créée !');
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
        <div class="page-subtitle">Activité affichée sur la page Sensibilisation</div>
    </div>
    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger" style="margin-bottom:16px;">
    <i class="fas fa-exclamation-circle"></i>
    <ul style="margin:0;padding-left:18px;"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">

        <!-- Colonne principale -->
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-bullhorn"></i> Contenu de l'activité</div></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Titre <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="titre" class="form-control"
                           value="<?= htmlspecialchars($v('titre')) ?>"
                           placeholder="Ex: Octobre Rose" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Description <span style="color:var(--danger)">*</span></label>
                    <textarea name="description" class="form-control" rows="7"
                              placeholder="Décrivez cette activité de sensibilisation…" required><?= htmlspecialchars($v('description')) ?></textarea>
                    <div class="form-hint">Ce texte apparaît dans la carte sur la page Sensibilisation.</div>
                </div>
            </div>
        </div>

        <!-- Colonne latérale -->
        <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-palette"></i> Apparence</div></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Couleur de la bande</label>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <input type="color" name="couleur" id="couleur-picker"
                                   value="<?= htmlspecialchars($v('couleur', '#E91E8C')) ?>"
                                   style="width:48px;height:38px;border:1px solid var(--border);border-radius:6px;cursor:pointer;padding:2px;">
                            <input type="text" id="couleur-text"
                                   value="<?= htmlspecialchars($v('couleur', '#E91E8C')) ?>"
                                   style="width:100px;font-family:monospace;" class="form-control"
                                   pattern="^#[0-9A-Fa-f]{6}$" placeholder="#E91E8C">
                        </div>
                        <div class="form-hint">Couleur de la barre gauche de la carte</div>
                    </div>

                    <!-- Prévisualisation -->
                    <div style="margin-top:8px;border-left:4px solid <?= htmlspecialchars($v('couleur','#E91E8C')) ?>;
                                padding:10px 14px;background:var(--body-bg);border-radius:0 8px 8px 0;" id="preview-card">
                        <strong style="font-size:.85rem;" id="preview-title"><?= htmlspecialchars($v('titre','Titre de l\'activité')) ?></strong>
                        <p style="font-size:.78rem;color:var(--text-muted);margin:4px 0 0;" id="preview-desc"><?= htmlspecialchars(mb_substr($v('description','Description...'), 0, 60)) ?>…</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-cog"></i> Paramètres</div></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Ordre d'affichage</label>
                        <input type="number" name="ordre" class="form-control"
                               value="<?= (int)$v('ordre', 0) ?>" min="0">
                        <div class="form-hint">Plus petit = affiché en premier</div>
                    </div>
                    <div class="switch-wrap" style="margin-bottom:0;">
                        <label class="switch">
                            <input type="checkbox" name="est_actif" <?= $v('est_actif', 1) ? 'checked' : '' ?>>
                            <span class="switch-slider"></span>
                        </label>
                        <span class="switch-label">Visible sur le site</span>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">
                <i class="fas fa-save"></i> <?= $is_edit ? 'Enregistrer' : 'Créer l\'activité' ?>
            </button>
        </div>

    </div>
</form>

<script>
// Sync color picker ↔ text input + preview
const picker = document.getElementById('couleur-picker');
const text   = document.getElementById('couleur-text');
const card   = document.getElementById('preview-card');
const ptitle = document.getElementById('preview-title');
const pdesc  = document.getElementById('preview-desc');

function syncColor(val) {
    picker.value = val;
    text.value   = val;
    card.style.borderLeftColor = val;
}
picker.addEventListener('input', () => { text.value = picker.value; card.style.borderLeftColor = picker.value; });
text.addEventListener('input', () => { if (/^#[0-9A-Fa-f]{6}$/.test(text.value)) syncColor(text.value); });

// Sync preview title/desc
document.querySelector('[name="titre"]').addEventListener('input', e => { ptitle.textContent = e.target.value || 'Titre de l\'activité'; });
document.querySelector('[name="description"]').addEventListener('input', e => { pdesc.textContent = (e.target.value || 'Description...').substring(0, 60) + '…'; });
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
