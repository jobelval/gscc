<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
$id=(int)($_GET['id']??0); $is_edit=$id>0; $ev=null; $errors=[];
if($is_edit){$s=$pdo->prepare("SELECT * FROM evenements WHERE id=?");$s->execute([$id]);$ev=$s->fetch();if(!$ev){adminFlash('error','Introuvable.');header('Location:index.php');exit;}}
$page_title=$is_edit?'Modifier l\'événement':'Nouvel événement'; $page_section='evenements';
$breadcrumb=[['label'=>'Événements','url'=>'index.php'],['label'=>$is_edit?'Modifier':'Créer']];

if($_SERVER['REQUEST_METHOD']==='POST'&&adminCheckCsrf()){
    $titre     =trim($_POST['titre']??'');
    $desc      =trim($_POST['description']??'');
    $contenu   =$_POST['contenu']??'';
    $lieu      =trim($_POST['lieu']??'');
    $adresse   =trim($_POST['adresse']??'');
    $date_deb  =$_POST['date_debut']??'';
    $date_fin  =$_POST['date_fin']??'';
    $capacite  =(int)($_POST['capacite_max']??0);
    $prix      =(float)($_POST['prix']??0);
    $statut    =in_array($_POST['statut']??'',['a_venir','en_cours','termine','annule'])?$_POST['statut']:'a_venir';
    $slug      =slugify($titre);

    if(!$titre)    $errors[]='Titre obligatoire.';
    if(!$date_deb) $errors[]='Date de début obligatoire.';

    $image = $ev['image']??null;
    if(!empty($_FILES['image']['name'])){
        $up=uploadFile($_FILES['image'],ROOT_PATH.'uploads/evenements/',['jpg','jpeg','png','webp']);
        if($up['success']) $image='uploads/evenements/'.$up['filename'];
        else $errors[]=$up['error'];
    }

    if(!$errors){
        try{
            if($is_edit){
                $pdo->prepare("UPDATE evenements SET titre=?,slug=?,description=?,contenu=?,image=?,lieu=?,adresse=?,date_debut=?,date_fin=?,capacite_max=?,prix=?,statut=? WHERE id=?")
                    ->execute([$titre,$slug,$desc,$contenu,$image,$lieu,$adresse,$date_deb,$date_fin?:null,$capacite?:null,$prix,$statut,$id]);
            } else {
                $base=$slug;$i=0;
                while(true){$c=$pdo->prepare("SELECT id FROM evenements WHERE slug=?");$c->execute([$slug]);if(!$c->fetch())break;$i++;$slug=$base.'-'.$i;}
                $pdo->prepare("INSERT INTO evenements (titre,slug,description,contenu,image,lieu,adresse,date_debut,date_fin,capacite_max,prix,statut,created_by,date_creation) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())")
                    ->execute([$titre,$slug,$desc,$contenu,$image,$lieu,$adresse,$date_deb,$date_fin?:null,$capacite?:null,$prix,$statut,$_SESSION['admin_id']]);
            }
            adminFlash('success',$is_edit?'Événement mis à jour !':'Événement créé !');
            header('Location:index.php');exit;
        } catch(PDOException $e){$errors[]=$e->getMessage();}
    }
}
$v=fn($f,$d='')=>(isset($_POST[$f])?$_POST[$f]:($ev[$f]??$d));
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="page-header">
    <div><div class="page-title"><?= $page_title ?></div></div>
    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
</div>
<?php if($errors):?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i><ul style="margin:0;padding-left:16px;"><?php foreach($errors as $e):?><li><?= htmlspecialchars($e) ?></li><?php endforeach;?></ul></div><?php endif;?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
    <div style="display:grid;grid-template-columns:1fr 280px;gap:16px;align-items:start;">
        <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-calendar-days"></i> Informations</div></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Titre <span class="required">*</span></label>
                        <input type="text" name="titre" class="form-control" required value="<?= htmlspecialchars($v('titre')) ?>" placeholder="Titre de l'événement…">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description courte</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Résumé court…"><?= htmlspecialchars($v('description')) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contenu complet</label>
                        <textarea name="contenu" class="form-control" rows="7" placeholder="Description détaillée, programme…"><?= htmlspecialchars($v('contenu')) ?></textarea>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-map-marker-alt"></i> Lieu & Dates</div></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Date & heure de début <span class="required">*</span></label>
                            <input type="datetime-local" name="date_debut" class="form-control" required value="<?= htmlspecialchars(str_replace(' ','T',substr($v('date_debut'),0,16))) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date & heure de fin</label>
                            <input type="datetime-local" name="date_fin" class="form-control" value="<?= htmlspecialchars(str_replace(' ','T',substr($v('date_fin',''),0,16))) ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Lieu</label>
                            <input type="text" name="lieu" class="form-control" value="<?= htmlspecialchars($v('lieu')) ?>" placeholder="Ex. Port-au-Prince, Delmas…">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Adresse complète</label>
                            <input type="text" name="adresse" class="form-control" value="<?= htmlspecialchars($v('adresse')) ?>" placeholder="Rue, numéro…">
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                            <option value="annule"   <?= $v('statut','a_venir')==='annule'  ?'selected':'' ?>>❌ Annulé</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Capacité max (0 = illimité)</label>
                        <input type="number" name="capacite_max" class="form-control" min="0" value="<?= htmlspecialchars($v('capacite_max',0)) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Prix ($ — 0 = Gratuit)</label>
                        <input type="number" name="prix" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars($v('prix',0)) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> <?= $is_edit?'Mettre à jour':'Créer' ?></button>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-image"></i> Image</div></div>
                <div class="card-body">
                    <?php $img=$ev['image']??''; if($img):?>
                    <img id="imgPreview" src="<?= SITE_URL.'/'.htmlspecialchars($img) ?>" style="width:100%;border-radius:8px;margin-bottom:10px;max-height:160px;object-fit:cover;border:1px solid var(--border);" onerror="this.style.display='none'">
                    <?php else:?><img id="imgPreview" style="display:none;width:100%;border-radius:8px;margin-bottom:10px;"><?php endif;?>
                    <input type="file" name="image" class="form-control" accept="image/*" style="font-size:.82rem;" onchange="previewImg(this)">
                </div>
            </div>
        </div>
    </div>
</form>
<script>
function previewImg(i){if(i.files&&i.files[0]){const r=new FileReader();r.onload=e=>{const p=document.getElementById('imgPreview');p.src=e.target.result;p.style.display=''};r.readAsDataURL(i.files[0]);}}
</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
