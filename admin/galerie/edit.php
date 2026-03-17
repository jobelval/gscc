<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
$id=(int)($_GET['id']??0); if(!$id){header('Location:index.php');exit;}
$stmt=$pdo->prepare("SELECT * FROM galerie WHERE id=?"); $stmt->execute([$id]); $m=$stmt->fetch();
if(!$m){adminFlash('error','Introuvable.');header('Location:index.php');exit;}
$page_title='Modifier média'; $page_section='galerie';
$breadcrumb=[['label'=>'Galerie','url'=>'index.php'],['label'=>'Modifier']];
$errors=[];

if($_SERVER['REQUEST_METHOD']==='POST'&&adminCheckCsrf()){
    $titre = trim($_POST['titre']??'');
    $desc  = trim($_POST['description']??'');
    $ordre = (int)($_POST['ordre']??0);
    $pub   = isset($_POST['est_public'])?1:0;

    $new_file = $m['url_fichier'];
    if($m['type']==='photo'&&!empty($_FILES['photo']['name'])){
        $up=uploadFile($_FILES['photo'],ROOT_PATH.'uploads/galerie/',['jpg','jpeg','png','webp','gif']);
        if($up['success']){ $new_file='uploads/galerie/'.$up['filename']; }
        else $errors[]=$up['error'];
    }
    if(!$errors){
        $pdo->prepare("UPDATE galerie SET titre=?,description=?,url_fichier=?,url_thumbnail=?,ordre=?,est_public=? WHERE id=?")
            ->execute([$titre?:null,$desc?:null,$new_file,$new_file,$ordre,$pub,$id]);
        adminFlash('success','Média mis à jour.');
        header('Location:index.php');exit;
    }
}
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="page-header">
    <div><div class="page-title">Modifier le média #<?= $id ?></div></div>
    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
</div>
<?php if($errors):?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($errors[0]) ?></div><?php endif;?>
<div style="display:grid;grid-template-columns:1fr 280px;gap:16px;align-items:start;">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-pen"></i> Informations</div></div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                <div class="form-group">
                    <label class="form-label">Titre</label>
                    <input type="text" name="titre" class="form-control" value="<?= htmlspecialchars($m['titre']??'') ?>" placeholder="Titre du média…">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($m['description']??'') ?></textarea>
                </div>
                <?php if($m['type']==='photo'):?>
                <div class="form-group">
                    <label class="form-label">Remplacer la photo</label>
                    <input type="file" name="photo" class="form-control" accept="image/*" style="font-size:.82rem;" onchange="previewImg(this)">
                </div>
                <?php endif;?>
                <div class="form-group">
                    <label class="form-label">Ordre d'affichage</label>
                    <input type="number" name="ordre" class="form-control" value="<?= $m['ordre'] ?>" min="0">
                </div>
                <div class="switch-wrap" style="margin-bottom:20px;">
                    <label class="switch"><input type="checkbox" name="est_public" <?= $m['est_public']?'checked':'' ?>><span class="switch-slider"></span></label>
                    <span class="switch-label">Visible sur le site</span>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-eye"></i> Aperçu actuel</div></div>
        <div class="card-body" style="text-align:center;">
            <?php if($m['type']==='photo'):?>
            <img id="imgPreview" src="<?= SITE_URL.'/'.htmlspecialchars($m['url_thumbnail']?:$m['url_fichier']) ?>"
                 style="max-width:100%;border-radius:8px;border:1px solid var(--border);" onerror="this.style.display='none'">
            <?php else:?>
            <div style="background:#000;border-radius:8px;overflow:hidden;">
                <img src="https://img.youtube.com/vi/<?= htmlspecialchars($m['url_fichier']) ?>/mqdefault.jpg" style="width:100%;">
            </div>
            <p style="margin-top:8px;font-size:.82rem;color:var(--text-muted);">ID YouTube : <code><?= htmlspecialchars($m['url_fichier']) ?></code></p>
            <?php endif;?>
            <div style="margin-top:8px;font-size:.76rem;color:var(--text-muted);">
                Type : <?= $m['type'] ?> · Vues : <?= $m['vue_compteur'] ?>
            </div>
        </div>
    </div>
</div>
<script>
function previewImg(input){
    if(input.files&&input.files[0]){
        const r=new FileReader(); r.onload=e=>{document.getElementById('imgPreview').src=e.target.result;};
        r.readAsDataURL(input.files[0]);
    }
}
</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
