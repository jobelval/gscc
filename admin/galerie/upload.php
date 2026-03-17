<?php
/**
 * GSCC CMS — admin/galerie/upload.php
 * Upload multiple photos
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$page_title   = 'Upload photos';
$page_section = 'galerie';
$breadcrumb   = [['label'=>'Galerie','url'=>'index.php'],['label'=>'Upload']];

$uploaded = []; $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $files = $_FILES['photos'] ?? null;

    if ($files && is_array($files['name'])) {
        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

            $single = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];

            $dest = UPLOADS_PATH . 'galerie/';
            if (!is_dir($dest)) mkdir($dest, 0755, true);

            $up = uploadFile($single, $dest, ['jpg','jpeg','png','webp','gif']);
            if ($up['success']) {
                $filename = $up['filename'];
                $filepath = 'uploads/galerie/' . $filename;
                $titre    = pathinfo($files['name'][$i], PATHINFO_FILENAME);

                // Titre depuis $_POST si un seul fichier
                if ($count === 1) {
                    $titre = trim($_POST['titre'] ?? $titre);
                }
                $desc = trim($_POST['description'] ?? '');

                $pdo->prepare(
                    "INSERT INTO galerie (titre,description,type,url_fichier,url_thumbnail,date_upload,uploaded_by,est_public,ordre)
                     VALUES (?,?,'photo',?,?,NOW(),?,1,0)"
                )->execute([$titre ?: $filename, $desc ?: null, $filepath, $filepath, $_SESSION['admin_id']]);

                $uploaded[] = $filepath;
            } else {
                $errors[] = $files['name'][$i] . ' : ' . $up['error'];
            }
        }
        if ($uploaded) adminFlash('success', count($uploaded) . ' photo(s) uploadée(s) avec succès !');
        if ($errors)   adminFlash('error',   implode('<br>', $errors));
        if ($uploaded) { header('Location: index.php'); exit; }
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Upload de photos</div>
        <div class="page-subtitle">Ajouter des photos à la galerie</div>
    </div>
    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start;">

    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-cloud-upload-alt"></i> Sélectionner les fichiers</div></div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">

                <!-- Zone drop -->
                <div id="dropZone" style="border:2.5px dashed var(--border);border-radius:12px;padding:48px 24px;text-align:center;cursor:pointer;transition:all .2s;margin-bottom:16px;background:var(--body-bg);"
                     onclick="document.getElementById('photoInput').click()"
                     ondragover="this.style.borderColor='var(--primary)';this.style.background='var(--primary-light)';event.preventDefault();"
                     ondragleave="this.style.borderColor='var(--border)';this.style.background='var(--body-bg)';"
                     ondrop="handleDrop(event)">
                    <i class="fas fa-cloud-upload-alt" style="font-size:48px;color:#CBD5E1;margin-bottom:12px;display:block;"></i>
                    <div style="font-size:1rem;font-weight:600;color:var(--text);margin-bottom:6px;">Glissez-déposez vos photos ici</div>
                    <div style="font-size:.85rem;color:var(--text-muted);margin-bottom:14px;">ou cliquez pour sélectionner</div>
                    <span style="background:var(--primary);color:#fff;padding:8px 20px;border-radius:8px;font-size:.84rem;font-weight:600;">Parcourir</span>
                </div>

                <input type="file" name="photos[]" id="photoInput" multiple accept="image/*"
                       style="display:none;" onchange="previewFiles(this)">

                <!-- Prévisualisation -->
                <div id="previewGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:8px;margin-bottom:16px;"></div>

                <!-- Métadonnées (affichées seulement pour 1 fichier) -->
                <div id="singleMeta" style="display:none;">
                    <div class="form-group">
                        <label class="form-label">Titre</label>
                        <input type="text" name="titre" class="form-control" placeholder="Titre de la photo…">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Description optionnelle…"></textarea>
                    </div>
                </div>

                <div id="uploadInfo" style="display:none;background:var(--primary-light);border:1px solid #C7D7FF;border-radius:8px;padding:12px 14px;font-size:.84rem;color:var(--primary);margin-bottom:16px;">
                    <i class="fas fa-info-circle"></i> <span id="fileCount"></span> fichier(s) sélectionné(s)
                </div>

                <button type="submit" id="uploadBtn" class="btn btn-primary" style="display:none;">
                    <i class="fas fa-upload"></i> Uploader les photos
                </button>
            </form>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-info-circle"></i> Formats acceptés</div></div>
            <div class="card-body" style="font-size:.84rem;">
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px;">
                    <?php foreach (['JPG','JPEG','PNG','WEBP','GIF'] as $fmt): ?>
                    <span class="badge badge-info"><?= $fmt ?></span>
                    <?php endforeach; ?>
                </div>
                <p style="color:var(--text-muted);margin-bottom:6px;"><i class="fas fa-weight"></i> Taille max : <strong>5 Mo</strong> par fichier</p>
                <p style="color:var(--text-muted);margin-bottom:6px;"><i class="fas fa-layer-group"></i> Upload multiple supporté</p>
                <p style="color:var(--text-muted);"><i class="fas fa-eye"></i> Photos visibles sur le site immédiatement</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fab fa-youtube" style="color:#FF0000;"></i> Ajouter une vidéo</div></div>
            <div class="card-body">
                <p style="font-size:.84rem;color:var(--text-muted);margin-bottom:12px;">Intégrez une vidéo YouTube dans la galerie.</p>
                <a href="add-video.php" class="btn btn-secondary w-100" style="justify-content:center;">
                    <i class="fab fa-youtube"></i> Ajouter une vidéo
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function previewFiles(input) {
    const files = Array.from(input.files);
    const grid  = document.getElementById('previewGrid');
    const info  = document.getElementById('uploadInfo');
    const btn   = document.getElementById('uploadBtn');
    const cnt   = document.getElementById('fileCount');
    const meta  = document.getElementById('singleMeta');

    grid.innerHTML = '';
    if (!files.length) { info.style.display='none'; btn.style.display='none'; return; }

    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.style.cssText = 'border-radius:8px;overflow:hidden;height:90px;background:#E2E8F0;position:relative;';
            div.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">
                <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.5);color:#fff;font-size:.62rem;padding:3px 5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${file.name}</div>`;
            grid.appendChild(div);
        };
        reader.readAsDataURL(file);
    });

    cnt.textContent = files.length;
    info.style.display = '';
    btn.style.display  = '';
    meta.style.display = files.length === 1 ? '' : 'none';
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropZone').style.borderColor = 'var(--border)';
    document.getElementById('dropZone').style.background  = 'var(--body-bg)';
    const dt   = e.dataTransfer;
    const inp  = document.getElementById('photoInput');
    inp.files  = dt.files;
    previewFiles(inp);
}
</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
