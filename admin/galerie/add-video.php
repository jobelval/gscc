<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
$page_title='Ajouter une vidéo'; $page_section='galerie';
$breadcrumb=[['label'=>'Galerie','url'=>'index.php'],['label'=>'Vidéo']];
$errors=[];

if ($_SERVER['REQUEST_METHOD']==='POST' && adminCheckCsrf()) {
    $url_raw  = trim($_POST['youtube_url']??'');
    $titre    = trim($_POST['titre']??'');
    $desc     = trim($_POST['description']??'');
    $est_public= isset($_POST['est_public'])?1:0;

    // Extraire l'ID YouTube
    $vid_id = '';
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $url_raw, $m)) {
        $vid_id = $m[1];
    } elseif (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url_raw)) {
        $vid_id = $url_raw;
    }

    if (!$vid_id) $errors[]='URL YouTube invalide. Collez l\'URL complète ou juste l\'ID.';
    if (!$titre)  $errors[]='Le titre est obligatoire.';

    if (!$errors) {
        $pdo->prepare(
            "INSERT INTO galerie (titre,description,type,url_fichier,date_upload,uploaded_by,est_public,ordre)
             VALUES (?,?,'video',?,NOW(),?,?,0)"
        )->execute([$titre,$desc?:null,$vid_id,$_SESSION['admin_id'],$est_public]);
        adminFlash('success','Vidéo ajoutée à la galerie !');
        header('Location:index.php'); exit;
    }
}
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="page-header">
    <div><div class="page-title">Ajouter une vidéo YouTube</div></div>
    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
</div>
<?php if ($errors): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i><ul style="margin:0;padding-left:16px;"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div style="display:grid;grid-template-columns:1fr 320px;gap:16px;align-items:start;">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fab fa-youtube" style="color:#FF0000;"></i> Informations vidéo</div></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                <div class="form-group">
                    <label class="form-label">URL YouTube ou ID <span class="required">*</span></label>
                    <input type="text" name="youtube_url" id="ytUrl" class="form-control" required
                           placeholder="https://www.youtube.com/watch?v=XXXX ou juste l'ID"
                           oninput="previewYT(this.value)">
                    <div class="form-hint">Ex: https://youtu.be/zGajkhajg38</div>
                </div>
                <div id="ytPreviewWrap" style="display:none;margin-bottom:16px;border-radius:10px;overflow:hidden;border:1px solid var(--border);">
                    <iframe id="ytPreview" width="100%" height="260" frameborder="0" allowfullscreen></iframe>
                </div>
                <div class="form-group">
                    <label class="form-label">Titre <span class="required">*</span></label>
                    <input type="text" name="titre" class="form-control" required placeholder="Titre de la vidéo…" value="<?= htmlspecialchars($_POST['titre']??'') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Description optionnelle…"><?= htmlspecialchars($_POST['description']??'') ?></textarea>
                </div>
                <div class="switch-wrap" style="margin-bottom:20px;">
                    <label class="switch"><input type="checkbox" name="est_public" checked><span class="switch-slider"></span></label>
                    <span class="switch-label">Visible sur le site</span>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter la vidéo</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-info-circle"></i> Comment ça marche</div></div>
        <div class="card-body" style="font-size:.85rem;color:var(--text-muted);">
            <p style="margin-bottom:10px;">1️⃣ Copiez l'URL de la vidéo YouTube</p>
            <p style="margin-bottom:10px;">2️⃣ Collez-la dans le champ ci-contre</p>
            <p style="margin-bottom:10px;">3️⃣ Une prévisualisation apparaît automatiquement</p>
            <p style="margin-bottom:10px;">4️⃣ Remplissez le titre et sauvegardez</p>
            <hr class="divider">
            <p style="font-size:.78rem;">Formats d'URL acceptés :<br>
            <code style="font-size:.72rem;">youtube.com/watch?v=ID</code><br>
            <code style="font-size:.72rem;">youtu.be/ID</code><br>
            <code style="font-size:.72rem;">ID seul (11 caractères)</code></p>
        </div>
    </div>
</div>
<script>
function previewYT(val) {
    let id='';
    const m1=val.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/);
    if(m1) id=m1[1];
    else if(/^[a-zA-Z0-9_-]{11}$/.test(val)) id=val;
    const wrap=document.getElementById('ytPreviewWrap');
    const frame=document.getElementById('ytPreview');
    if(id){ frame.src='https://www.youtube.com/embed/'+id; wrap.style.display=''; }
    else  { wrap.style.display='none'; }
}
</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
