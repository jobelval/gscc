<?php
/**
 * GSCC CMS — admin/articles/create.php & edit.php (VERSION CORRIGÉE)
 * Fix upload image XAMPP Windows
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$id      = (int)($_GET['id'] ?? 0);
$is_edit = $id > 0;
$article = null;
$errors  = [];

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();
    if (!$article) { adminFlash('error','Article introuvable.'); header('Location:index.php'); exit; }
}

$page_title   = $is_edit ? 'Modifier l\'article' : 'Nouvel article';
$page_section = 'articles';
$breadcrumb   = [['label'=>'Articles','url'=>'index.php'],['label'=>$is_edit?'Modifier':'Créer']];

try {
    $categories = $pdo->query("SELECT id, nom FROM categories WHERE type='blog' ORDER BY nom")->fetchAll();
} catch (PDOException $e) { $categories = []; }

/* ════════════════════════════════════════════════════════
   UPLOAD IMAGE — VERSION CORRIGÉE XAMPP WINDOWS
════════════════════════════════════════════════════════ */
function uploadArticleImage(array $file): array
{
    // Codes d'erreur PHP explicites
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $msg = match($file['error']) {
            UPLOAD_ERR_INI_SIZE   => 'Fichier trop grand (limite : ' . ini_get('upload_max_filesize') . ')',
            UPLOAD_ERR_FORM_SIZE  => 'Fichier trop grand (limite formulaire)',
            UPLOAD_ERR_PARTIAL    => 'Upload incomplet, réessayez',
            UPLOAD_ERR_NO_FILE    => 'Aucun fichier envoyé',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant — vérifiez php.ini',
            UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire sur le disque',
            default               => 'Erreur upload code ' . $file['error'],
        };
        return ['success' => false, 'error' => $msg];
    }

    // Vérification extension
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','gif'];
    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'error' => "Format .$ext non autorisé (JPG/PNG/WEBP uniquement)"];
    }

    // Vérification MIME réelle
    if (function_exists('mime_content_type')) {
        $mime  = mime_content_type($file['tmp_name']);
        $mimes = ['image/jpeg','image/png','image/webp','image/gif','image/jpg'];
        if (!in_array($mime, $mimes)) {
            return ['success' => false, 'error' => "Le fichier n'est pas une image valide ($mime)"];
        }
    }

    // Vérification taille (5 Mo)
    if ($file['size'] > 5 * 1024 * 1024) {
        $mb = round($file['size'] / 1024 / 1024, 1);
        return ['success' => false, 'error' => "Image trop lourde : {$mb} Mo (max 5 Mo)"];
    }

    // Construction chemin — compatible Windows (DIRECTORY_SEPARATOR) et Linux
    $root = defined('ROOT_PATH')
        ? rtrim(ROOT_PATH, '/\\')
        : rtrim(dirname(__DIR__, 2), '/\\');

    $upload_dir = $root . DIRECTORY_SEPARATOR . 'uploads'
                       . DIRECTORY_SEPARATOR . 'articles'
                       . DIRECTORY_SEPARATOR;

    // Création du dossier si absent
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            return ['success' => false, 'error' => "Impossible de créer le dossier d'upload. Lancez diag-upload.php"];
        }
    }

    // Vérification accès en écriture
    if (!is_writable($upload_dir)) {
        return ['success' => false, 'error' => "Dossier non accessible en écriture : $upload_dir — Lancez diag-upload.php"];
    }

    // Nom de fichier unique et sécurisé
    $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $dest     = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['success' => false,
                'error'   => "Échec de la sauvegarde. Vérifiez les permissions du dossier : $upload_dir"];
    }

    // Le front-end construit : SITE_URL . '/' . image_couverture
    // Donc on stocke le chemin DEPUIS la racine du site : uploads/articles/...
    return ['success' => true, 'filename' => $filename, 'path' => 'uploads/articles/' . $filename];
}

/* ── Traitement formulaire ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!adminCheckCsrf()) {
        $errors[] = 'Token CSRF invalide.';
    } else {
        // Suppression d'une image de galerie
        if (($_POST['action'] ?? '') === 'delete_image' && $is_edit) {
            $img_id = (int)($_POST['image_id'] ?? 0);
            if ($img_id > 0) {
                $stmt = $pdo->prepare("SELECT fichier FROM article_images WHERE id=? AND article_id=?");
                $stmt->execute([$img_id, $id]);
                $row = $stmt->fetch();
                if ($row) {
                    $root  = defined('ROOT_PATH') ? rtrim(ROOT_PATH,'/\\') : rtrim(dirname(__DIR__,2),'/\\');
                    $fpath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $row['fichier']);
                    if (file_exists($fpath)) @unlink($fpath);
                    $pdo->prepare("DELETE FROM article_images WHERE id=?")->execute([$img_id]);
                    adminFlash('success', 'Image de galerie supprimée.');
                }
            }
            header('Location: create.php?id='.$id); exit;
        }

        $titre       = trim($_POST['titre'] ?? '');
        $contenu     = $_POST['contenu'] ?? '';
        $resume      = trim($_POST['resume'] ?? '');
        $cat_id      = (int)($_POST['categorie_id'] ?? 0);
        $statut      = in_array($_POST['statut']??'',['publie','brouillon','archive']) ? $_POST['statut'] : 'brouillon';
        $est_vedette = isset($_POST['est_vedette']) ? 1 : 0;
        $tags        = trim($_POST['tags'] ?? '');
        $meta_desc   = trim($_POST['meta_description'] ?? '');
        $temps_lec   = (int)($_POST['temps_lecture'] ?? 0);
        $slug        = slugify($titre);

        if (!$titre)   $errors[] = 'Le titre est obligatoire.';
        if (!$contenu) $errors[] = 'Le contenu est obligatoire.';

        $image = $article['image_couverture'] ?? null;
        if (!empty($_FILES['image_couverture']['name'])) {
            $up = uploadArticleImage($_FILES['image_couverture']);
            if ($up['success']) {
                $image = $up['path'];
            } else {
                $errors[] = '⚠️ Image : ' . $up['error'];
            }
        }

        if (!$errors) {
            try {
                $date_pub = ($statut === 'publie') ? date('Y-m-d H:i:s') : ($article['date_publication'] ?? null);
                if ($is_edit) {
                    $pdo->prepare("UPDATE articles SET titre=?,slug=?,contenu=?,resume=?,image_couverture=?,
                         categorie_id=?,statut=?,est_vedette=?,tags=?,meta_description=?,
                         temps_lecture=?,date_publication=?,auteur_id=? WHERE id=?")
                        ->execute([$titre,$slug,$contenu,$resume,$image,$cat_id?:null,$statut,
                                   $est_vedette,$tags,$meta_desc,$temps_lec?:null,$date_pub,$_SESSION['admin_id'],$id]);
                    $saved_id = $id;
                } else {
                    $base_slug = $slug; $i = 0;
                    while (true) {
                        $c = $pdo->prepare("SELECT id FROM articles WHERE slug=?");
                        $c->execute([$slug]); if (!$c->fetch()) break;
                        $i++; $slug = $base_slug.'-'.$i;
                    }
                    $pdo->prepare("INSERT INTO articles (titre,slug,contenu,resume,image_couverture,
                         categorie_id,auteur_id,statut,est_vedette,tags,meta_description,
                         temps_lecture,date_publication,date_creation)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())")
                        ->execute([$titre,$slug,$contenu,$resume,$image,$cat_id?:null,$_SESSION['admin_id'],
                                   $statut,$est_vedette,$tags,$meta_desc,$temps_lec?:null,$date_pub]);
                    $saved_id = (int)$pdo->lastInsertId();
                }
                // Upload images de galerie (non-bloquant)
                $gallery_added = 0;
                $gallery_err   = [];
                if (!empty($_FILES['galerie']['name'][0])) {
                    try {
                        $gf      = $_FILES['galerie'];
                        $mo_stmt = $pdo->prepare("SELECT COALESCE(MAX(ordre),0) FROM article_images WHERE article_id=?");
                        $mo_stmt->execute([$saved_id]);
                        $mo   = (int)$mo_stmt->fetchColumn();
                        $legs = $_POST['galerie_legende'] ?? [];
                        for ($fi = 0, $gn = count($gf['name']); $fi < $gn; $fi++) {
                            if ($gf['error'][$fi] !== UPLOAD_ERR_OK || empty($gf['name'][$fi])) continue;
                            $up = uploadArticleImage([
                                'name'     => $gf['name'][$fi],
                                'tmp_name' => $gf['tmp_name'][$fi],
                                'error'    => $gf['error'][$fi],
                                'size'     => $gf['size'][$fi],
                                'type'     => $gf['type'][$fi] ?? '',
                            ]);
                            if ($up['success']) {
                                $mo++;
                                $pdo->prepare("INSERT INTO article_images (article_id,fichier,legende,ordre) VALUES (?,?,?,?)")
                                    ->execute([$saved_id, $up['path'], trim($legs[$fi] ?? '') ?: null, $mo]);
                                $gallery_added++;
                            } else {
                                $gallery_err[] = basename($gf['name'][$fi]) . ' : ' . $up['error'];
                            }
                        }
                    } catch (PDOException $ge) {
                        $gallery_err[] = 'Erreur BDD galerie — vérifiez que la table article_images existe.';
                    }
                }
                $msg = $is_edit ? 'Article mis à jour !' : 'Article créé !';
                if ($gallery_added > 0) $msg .= " ($gallery_added image(s) de galerie ajoutée(s).)" ;
                if ($gallery_err)       $msg .= ' ⚠ ' . implode(' | ', $gallery_err);
                adminFlash('success', $msg);
                header('Location: index.php'); exit;
            } catch (PDOException $e) {
                $errors[] = 'Erreur BDD : ' . $e->getMessage();
            }
        }
    }
}

$v = fn($f,$d='') => ($_SERVER['REQUEST_METHOD']==='POST' ? ($_POST[$f]??$d) : ($article[$f]??$d));

// Images de galerie existantes (mode édition)
$gallery_images = [];
if ($is_edit) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM article_images WHERE article_id=? ORDER BY ordre ASC");
        $stmt->execute([$id]);
        $gallery_images = $stmt->fetchAll();
    } catch (PDOException $e) { $gallery_images = []; }
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title"><?= $page_title ?></div>
        <div class="page-subtitle"><?= $is_edit ? 'Modification : '.htmlspecialchars($article['titre']) : 'Créer un nouvel article' ?></div>
    </div>
    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <div>
        <?php foreach ($errors as $err): ?>
            <div><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
        <?php if (count($errors) === 1 && str_contains($errors[0], 'Image')): ?>
        <div style="margin-top:8px;font-size:.82rem;">
            💡 <a href="../diag-upload.php" target="_blank" style="color:inherit;font-weight:700;text-decoration:underline;">
                Cliquez ici pour lancer le diagnostic automatique
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="articleForm">
    <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
    <div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start;">

        <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="card">
                <div class="card-body">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Titre <span class="required">*</span></label>
                        <input type="text" name="titre" class="form-control" required
                               value="<?= htmlspecialchars($v('titre')) ?>"
                               placeholder="Titre de l'article…"
                               style="font-size:1.1rem;font-weight:600;"
                               oninput="updateSlug(this.value)">
                        <div class="form-hint">Slug : <code id="slugPreview"><?= htmlspecialchars($article['slug']??'') ?></code></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-align-left"></i> Résumé</div></div>
                <div class="card-body">
                    <textarea name="resume" class="form-control" rows="3" maxlength="300"
                              placeholder="Résumé court affiché en prévisualisation…"><?= htmlspecialchars($v('resume')) ?></textarea>
                    <div class="form-hint"><span id="resumeCount"><?= mb_strlen($v('resume')) ?></span>/300 caractères</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-pen-to-square"></i> Contenu <span class="required">*</span></div></div>
                <div class="card-body" style="padding:0;">
                    <div style="display:flex;flex-wrap:wrap;gap:4px;padding:10px 12px;border-bottom:1px solid var(--border);background:#F8FAFC;">
                        <?php foreach ([['bold','B','Gras','font-weight:700'],['italic','I','Italique','font-style:italic'],['|'],['h2','H2','Titre 2',''],['h3','H3','Titre 3',''],['|'],['ul','≡','Liste',''],['ol','1.','Liste numérotée',''],['blockquote','"','Citation',''],['|'],['link','🔗','Lien','']] as $t):
                            if($t[0]==='|'): ?><span style="width:1px;background:var(--border);margin:0 4px;"></span><?php continue; endif; ?>
                            <button type="button" class="btn btn-xs btn-secondary" onclick="execCmd('<?= $t[0] ?>')" title="<?= $t[2] ?>" style="<?= $t[3] ?>"><?= $t[1] ?></button>
                        <?php endforeach; ?>
                    </div>
                    <textarea name="contenu" id="contenu" class="form-control"
                              style="border-radius:0;border:none;min-height:380px;font-family:inherit;resize:vertical;"
                              placeholder="Contenu de l'article… HTML supporté."
                              required><?= htmlspecialchars($v('contenu')) ?></textarea>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-search"></i> SEO & Métadonnées</div></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Meta description</label>
                        <textarea name="meta_description" class="form-control" rows="2" maxlength="160"
                                  placeholder="Description pour Google (max 160 car.)…"><?= htmlspecialchars($v('meta_description')) ?></textarea>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Tags</label>
                        <input type="text" name="tags" class="form-control"
                               value="<?= htmlspecialchars($v('tags')) ?>"
                               placeholder="cancer, dépistage, prévention…">
                    </div>
                </div>
            </div>

            <!-- ── Galerie d'images ── -->
            <div class="card" id="galerie">
                <div class="card-header"><div class="card-title"><i class="fas fa-images"></i> Galerie d'images</div></div>
                <div class="card-body">
                    <?php if ($is_edit && !empty($gallery_images)): ?>
                    <p style="font-size:.82rem;font-weight:600;color:var(--text-muted);margin-bottom:10px;">
                        Images existantes — cliquez <i class="fas fa-times" style="color:#DC2626;"></i> pour supprimer
                    </p>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:10px;margin-bottom:18px;">
                        <?php foreach ($gallery_images as $gi): ?>
                        <div style="position:relative;border-radius:8px;overflow:hidden;border:1px solid var(--border);">
                            <img src="<?= htmlspecialchars(rtrim(SITE_URL,'/').'/'.ltrim($gi['fichier'],'/')) ?>"
                                 style="width:100%;height:80px;object-fit:cover;display:block;">
                            <?php if ($gi['legende']): ?>
                            <div style="padding:4px 6px;font-size:10px;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($gi['legende']) ?></div>
                            <?php endif; ?>
                            <button type="button"
                                    onclick="confirmDelGallery(<?= $gi['id'] ?>)"
                                    style="position:absolute;top:4px;right:4px;width:22px;height:22px;border-radius:50%;background:rgba(220,38,38,.9);border:none;color:white;font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0;line-height:1;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <hr style="border:none;border-top:1px solid var(--border);margin-bottom:16px;">
                    <?php endif; ?>

                    <!-- Zone glisser-déposer -->
                    <div id="galleryDropZone"
                         style="border:2px dashed var(--border);border-radius:8px;padding:22px;text-align:center;cursor:pointer;transition:all .2s;background:var(--body-bg);"
                         onclick="document.getElementById('galerieInput').click()"
                         ondragover="this.style.borderColor='var(--primary)';this.style.background='var(--primary-light)';event.preventDefault();"
                         ondragleave="this.style.borderColor='var(--border)';this.style.background='var(--body-bg)';"
                         ondrop="handleGalleryDrop(event)">
                        <i class="fas fa-images" style="font-size:26px;color:#CBD5E1;display:block;margin-bottom:8px;"></i>
                        <div style="font-size:.84rem;font-weight:600;">Cliquez ou glissez plusieurs images</div>
                        <div style="font-size:.76rem;color:var(--text-muted);margin-top:3px;">JPG, PNG, WEBP — max 5 Mo par image</div>
                    </div>

                    <input type="file" name="galerie[]" id="galerieInput"
                           accept="image/jpeg,image/png,image/webp,image/gif"
                           multiple style="display:none;" onchange="previewGallery(this)">

                    <!-- Prévisualisations nouvelles images -->
                    <div id="galleryNewPreview" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:10px;margin-top:12px;"></div>
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-rocket"></i> Publication</div></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-control">
                            <option value="brouillon" <?= $v('statut','brouillon')==='brouillon'?'selected':'' ?>>📝 Brouillon</option>
                            <option value="publie"    <?= $v('statut','brouillon')==='publie'   ?'selected':'' ?>>✅ Publié</option>
                            <option value="archive"   <?= $v('statut','brouillon')==='archive'  ?'selected':'' ?>>📦 Archivé</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Catégorie</label>
                        <select name="categorie_id" class="form-control">
                            <option value="">— Aucune —</option>
                            <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $v('categorie_id')==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Temps de lecture (min)</label>
                        <input type="number" name="temps_lecture" class="form-control" min="1" max="60"
                               value="<?= htmlspecialchars($v('temps_lecture','')) ?>" placeholder="ex. 5">
                    </div>
                    <div class="switch-wrap" style="margin-bottom:16px;">
                        <label class="switch"><input type="checkbox" name="est_vedette" <?= $v('est_vedette')?'checked':'' ?>><span class="switch-slider"></span></label>
                        <span class="switch-label">Article vedette ⭐</span>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> <?= $is_edit?'Mettre à jour':'Publier' ?>
                    </button>
                </div>
            </div>

            <!-- Image de couverture — VERSION CORRIGÉE -->
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-image"></i> Image de couverture</div></div>
                <div class="card-body">
                    <?php $ei = $article['image_couverture']??''; ?>

                    <!-- Preview existante -->
                    <div id="imgPreviewWrap" style="<?= $ei?'':'display:none;' ?>margin-bottom:12px;">
                        <img id="imgPreview"
                             src="<?= $ei ? htmlspecialchars(SITE_URL.'/'.ltrim($ei,'/')) : '' ?>"
                             style="width:100%;border-radius:8px;object-fit:cover;max-height:180px;border:1px solid var(--border);"
                             onerror="this.parentElement.style.display='none'">
                    </div>

                    <!-- Zone drop -->
                    <div id="dropZone"
                         style="border:2px dashed var(--border);border-radius:8px;padding:20px;text-align:center;cursor:pointer;transition:all .2s;background:var(--body-bg);"
                         onclick="document.getElementById('imgInput').click()"
                         ondragover="this.style.borderColor='var(--primary)';this.style.background='var(--primary-light)';event.preventDefault();"
                         ondragleave="this.style.borderColor='var(--border)';this.style.background='var(--body-bg)';"
                         ondrop="handleDrop(event)">
                        <i class="fas fa-cloud-upload-alt" style="font-size:26px;color:#CBD5E1;display:block;margin-bottom:8px;"></i>
                        <div style="font-size:.84rem;font-weight:600;">Cliquez ou glissez une image</div>
                        <div style="font-size:.76rem;color:var(--text-muted);margin-top:3px;">JPG, PNG, WEBP — max 5 Mo</div>
                    </div>

                    <input type="file" name="image_couverture" id="imgInput"
                           accept="image/jpeg,image/png,image/webp,image/gif"
                           style="display:none;" onchange="previewImg(this)">

                    <div id="fileInfo" style="display:none;margin-top:8px;background:var(--primary-light);border-radius:6px;padding:8px 12px;font-size:.78rem;color:var(--primary);">
                        <i class="fas fa-check-circle"></i> <span id="fName"></span> (<span id="fSize"></span>)
                    </div>
                </div>
            </div>

            <?php if ($is_edit): ?>
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-info-circle"></i> Infos</div></div>
                <div class="card-body" style="font-size:.82rem;color:var(--text-muted);">
                    <p style="margin-bottom:6px;"><strong>Créé le :</strong> <?= dateFr($article['date_creation'],'d/m/Y H:i') ?></p>
                    <p style="margin-bottom:6px;"><strong>Vues :</strong> <?= number_format($article['vue_compteur']) ?></p>
                    <a href="<?= SITE_URL ?>/article.php?slug=<?= urlencode($article['slug']) ?>" target="_blank"
                       class="btn btn-secondary btn-sm w-100" style="justify-content:center;margin-top:4px;">
                        <i class="fas fa-external-link-alt"></i> Voir sur le site
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<?php if ($is_edit): ?>
<form id="delGalleryForm" method="POST" action="create.php?id=<?= $id ?>">
    <input type="hidden" name="_csrf"     value="<?= adminCsrfToken() ?>">
    <input type="hidden" name="action"   value="delete_image">
    <input type="hidden" name="image_id" id="delGalleryId">
</form>
<?php endif; ?>

<script>
function updateSlug(t) {
    const s = t.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'')
               .replace(/[^a-z0-9\s-]/g,'').trim().replace(/[\s-]+/g,'-');
    document.getElementById('slugPreview').textContent = s||'—';
}
const rt = document.querySelector('[name="resume"]');
if(rt) rt.addEventListener('input',()=>document.getElementById('resumeCount').textContent=rt.value.length);

function previewImg(input) {
    if (!input.files || !input.files[0]) return;
    const f = input.files[0];
    const fi = document.getElementById('fileInfo');
    document.getElementById('fName').textContent = f.name;
    document.getElementById('fSize').textContent = (f.size/1024/1024).toFixed(2)+' Mo';
    fi.style.display = '';
    fi.style.background = f.size > 5*1024*1024 ? '#FFF5F5' : 'var(--primary-light)';
    fi.style.color      = f.size > 5*1024*1024 ? '#DC2626' : 'var(--primary)';
    const r = new FileReader();
    r.onload = e => {
        document.getElementById('imgPreview').src = e.target.result;
        document.getElementById('imgPreviewWrap').style.display = '';
    };
    r.readAsDataURL(f);
}
function handleDrop(e) {
    e.preventDefault();
    const dz = document.getElementById('dropZone');
    dz.style.borderColor = 'var(--border)'; dz.style.background = 'var(--body-bg)';
    if (e.dataTransfer.files.length) {
        try {
            const dt = new DataTransfer(); dt.items.add(e.dataTransfer.files[0]);
            const inp = document.getElementById('imgInput'); inp.files = dt.files; previewImg(inp);
        } catch(err) { document.getElementById('imgInput').click(); }
    }
}
function confirmDelGallery(id) {
    if (!confirm('Supprimer cette image de la galerie ?')) return;
    document.getElementById('delGalleryId').value = id;
    document.getElementById('delGalleryForm').submit();
}
function previewGallery(input) {
    const container = document.getElementById('galleryNewPreview');
    container.innerHTML = '';
    if (!input.files || !input.files.length) return;
    Array.from(input.files).forEach((file, i) => {
        const r = new FileReader();
        r.onload = e => {
            const div = document.createElement('div');
            div.style.cssText = 'position:relative;border-radius:8px;overflow:hidden;border:1px solid var(--border);';
            const tooBig = file.size > 5*1024*1024;
            div.innerHTML = `
                <img src="${e.target.result}" style="width:100%;height:80px;object-fit:cover;display:block;${tooBig?'filter:grayscale(1) opacity(.5)':''}">
                ${tooBig ? '<div style="position:absolute;inset:0;background:rgba(220,38,38,.15);display:flex;align-items:center;justify-content:center;font-size:10px;color:#DC2626;font-weight:700;">Trop grand</div>' : ''}
                <div style="padding:4px 6px;">
                    <input type="text" name="galerie_legende[]" placeholder="Légende…"
                           style="width:100%;font-size:11px;border:1px solid var(--border);border-radius:4px;padding:3px 5px;box-sizing:border-box;font-family:inherit;">
                </div>`;
            container.appendChild(div);
        };
        r.readAsDataURL(file);
    });
}
function handleGalleryDrop(e) {
    e.preventDefault();
    const dz = document.getElementById('galleryDropZone');
    dz.style.borderColor = 'var(--border)'; dz.style.background = 'var(--body-bg)';
    if (e.dataTransfer.files.length) {
        try {
            const dt = new DataTransfer();
            Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
            const inp = document.getElementById('galerieInput');
            inp.files = dt.files;
            previewGallery(inp);
        } catch(err) { document.getElementById('galerieInput').click(); }
    }
}
function execCmd(cmd) {
    const ta = document.getElementById('contenu');
    const s = ta.selectionStart, e2 = ta.selectionEnd, sel = ta.value.substring(s,e2);
    const w = {bold:['<strong>','</strong>'],italic:['<em>','</em>'],
               h2:['\n<h2>','</h2>\n'],h3:['\n<h3>','</h3>\n'],
               ul:['\n<ul>\n  <li>','</li>\n</ul>\n'],ol:['\n<ol>\n  <li>','</li>\n</ol>\n'],
               blockquote:['\n<blockquote style="border-left:4px solid #D94F7A;padding-left:16px;margin:20px 0;color:#555;font-style:italic;">','</blockquote>\n']};
    if (cmd==='link') { const u=prompt('URL du lien :'); if(!u) return; ta.value=ta.value.substring(0,s)+'<a href="'+u+'">'+( sel||'Lien')+'</a>'+ta.value.substring(e2); return; }
    if (w[cmd]) { const [b,a]=w[cmd]; ta.value=ta.value.substring(0,s)+b+sel+a+ta.value.substring(e2); ta.focus(); ta.selectionStart=s+b.length; ta.selectionEnd=s+b.length+sel.length; }
}
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
