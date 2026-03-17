<?php
/**
 * GSCC CMS — admin/utilisateurs/edit.php + create.php
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$id      = (int)($_GET['id'] ?? 0);
$is_edit = $id > 0;
$user    = null;
$errors  = [];

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id=?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) { adminFlash('error','Utilisateur introuvable.'); header('Location:index.php'); exit; }
}

$page_title   = $is_edit ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur';
$page_section = 'utilisateurs';
$breadcrumb   = [['label'=>'Utilisateurs','url'=>'index.php'],['label'=>$is_edit?'Modifier':'Créer']];

if ($_SERVER['REQUEST_METHOD']==='POST' && adminCheckCsrf()) {
    $email      = trim(strtolower($_POST['email']??''));
    $nom        = trim($_POST['nom']??'');
    $prenom     = trim($_POST['prenom']??'');
    $telephone  = trim($_POST['telephone']??'');
    $ville      = trim($_POST['ville']??'');
    $role       = in_array($_POST['role']??'',['admin','moderateur','membre']) ? $_POST['role'] : 'membre';
    $statut     = in_array($_POST['statut']??'',['actif','inactif']) ? $_POST['statut'] : 'actif';
    $password   = $_POST['password']??'';
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
    if (!$nom)    $errors[] = 'Nom obligatoire.';
    if (!$prenom) $errors[] = 'Prénom obligatoire.';
    if (!$is_edit && !$password) $errors[] = 'Mot de passe obligatoire pour un nouvel utilisateur.';
    if ($password && strlen($password) < 8) $errors[] = 'Mot de passe : 8 caractères minimum.';

    // Email unique
    if ($email && !$errors) {
        $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email=? AND id!=?");
        $check->execute([$email, $id]);
        if ($check->fetch()) $errors[] = 'Cet email est déjà utilisé.';
    }

    if (!$errors) {
        try {
            $hash = $password ? password_hash($password, PASSWORD_BCRYPT, ['cost'=>12]) : null;

            if ($is_edit) {
                $sql = "UPDATE utilisateurs SET email=?,nom=?,prenom=?,telephone=?,ville=?,role=?,statut=?,newsletter=?";
                $p   = [$email,$nom,$prenom,$telephone,$ville,$role,$statut,$newsletter];
                if ($hash) { $sql .= ",mot_de_passe=?"; $p[] = $hash; }
                $sql .= " WHERE id=?"; $p[] = $id;
                $pdo->prepare($sql)->execute($p);
                adminFlash('success','Utilisateur mis à jour.');
            } else {
                $pdo->prepare(
                    "INSERT INTO utilisateurs (email,mot_de_passe,nom,prenom,telephone,ville,role,statut,newsletter,date_inscription)
                     VALUES (?,?,?,?,?,?,?,?,?,NOW())"
                )->execute([$email,$hash,$nom,$prenom,$telephone,$ville,$role,$statut,$newsletter]);
                adminFlash('success','Utilisateur créé avec succès.');
            }
            header('Location:index.php'); exit;
        } catch (PDOException $e) {
            $errors[] = $e->getMessage();
        }
    }
}

$v = fn($f,$d='') => (isset($_POST[$f]) ? $_POST[$f] : ($user[$f] ?? $d));

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title"><?= $page_title ?></div>
        <?php if ($is_edit): ?>
        <div class="page-subtitle"><?= htmlspecialchars(($user['prenom']??'').' '.($user['nom']??'')) ?> — <?= htmlspecialchars($user['email']) ?></div>
        <?php endif; ?>
    </div>
    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i>
    <ul style="margin:0;padding-left:16px;"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
    <div style="display:grid;grid-template-columns:1fr 280px;gap:16px;align-items:start;">

        <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-user"></i> Informations personnelles</div></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Prénom <span class="required">*</span></label>
                            <input type="text" name="prenom" class="form-control" required
                                   value="<?= htmlspecialchars($v('prenom')) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nom <span class="required">*</span></label>
                            <input type="text" name="nom" class="form-control" required
                                   value="<?= htmlspecialchars($v('nom')) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" required
                               value="<?= htmlspecialchars($v('email')) ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="telephone" class="form-control"
                                   value="<?= htmlspecialchars($v('telephone')) ?>"
                                   placeholder="+509 XX XX XXXX">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ville</label>
                            <input type="text" name="ville" class="form-control"
                                   value="<?= htmlspecialchars($v('ville')) ?>"
                                   placeholder="Port-au-Prince…">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-lock"></i> Sécurité</div></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">
                            <?= $is_edit ? 'Nouveau mot de passe' : 'Mot de passe <span class="required">*</span>' ?>
                        </label>
                        <div style="position:relative;">
                            <input type="password" name="password" id="pwField" class="form-control"
                                   <?= !$is_edit ? 'required' : '' ?>
                                   placeholder="<?= $is_edit ? 'Laisser vide pour ne pas changer' : 'Min. 8 caractères' ?>">
                            <button type="button" onclick="togglePw()"
                                    style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);">
                                <i class="fas fa-eye" id="pwIcon"></i>
                            </button>
                        </div>
                        <?php if ($is_edit): ?>
                        <div class="form-hint">Laisser vide pour conserver le mot de passe actuel.</div>
                        <?php endif; ?>
                    </div>

                    <?php if ($is_edit): ?>
                    <div style="background:#FFFBEB;border:1px solid #FCD34D;border-radius:8px;padding:12px 14px;font-size:.82rem;color:#92400E;">
                        <i class="fas fa-shield-alt" style="margin-right:6px;"></i>
                        Dernière connexion : <strong><?= $user['derniere_connexion'] ? dateFr($user['derniere_connexion'],'d/m/Y à H:i') : 'Jamais' ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-user-gear"></i> Rôle & Statut</div></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Rôle</label>
                        <select name="role" class="form-control">
                            <option value="membre"     <?= $v('role','membre')==='membre'    ?'selected':'' ?>>👤 Membre</option>
                            <option value="moderateur" <?= $v('role','membre')==='moderateur'?'selected':'' ?>>🛡️ Modérateur</option>
                            <option value="admin"      <?= $v('role','membre')==='admin'     ?'selected':'' ?>>👑 Administrateur</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-control">
                            <option value="actif"   <?= $v('statut','actif')==='actif'  ?'selected':'' ?>>✅ Actif</option>
                            <option value="inactif" <?= $v('statut','actif')==='inactif'?'selected':'' ?>>❌ Inactif</option>
                        </select>
                    </div>
                    <div class="switch-wrap" style="margin-bottom:16px;">
                        <label class="switch">
                            <input type="checkbox" name="newsletter" <?= $v('newsletter',0) ? 'checked' : '' ?>>
                            <span class="switch-slider"></span>
                        </label>
                        <span class="switch-label">Abonné newsletter</span>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> <?= $is_edit ? 'Enregistrer' : 'Créer le compte' ?>
                    </button>
                </div>
            </div>

            <?php if ($is_edit): ?>
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-chart-bar"></i> Activité</div></div>
                <div class="card-body" style="font-size:.82rem;">
                    <?php
                    $nb_dons  = $pdo->prepare("SELECT COUNT(*), COALESCE(SUM(montant),0) FROM dons WHERE utilisateur_id=?")->execute([$id]) ? 0 : 0;
                    $ds = $pdo->prepare("SELECT COUNT(*) n, COALESCE(SUM(montant),0) tot FROM dons WHERE utilisateur_id=?");
                    $ds->execute([$id]); $da = $ds->fetch();
                    $nm = $pdo->prepare("SELECT COUNT(*) FROM demandes_aide WHERE utilisateur_id=?");
                    $nm->execute([$id]); $nb_dem = (int)$nm->fetchColumn();
                    ?>
                    <p style="margin-bottom:8px;"><strong><?= $da['n'] ?></strong> don(s) enregistré(s)</p>
                    <p style="margin-bottom:8px;color:var(--success);font-weight:700;">$<?= number_format($da['tot'],0,',',' ') ?></p>
                    <p style="margin-bottom:8px;"><strong><?= $nb_dem ?></strong> demande(s) d'aide</p>
                    <hr class="divider">
                    <p style="color:var(--text-muted);">Inscrit le : <?= dateFr($user['date_inscription'],'d/m/Y') ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
function togglePw() {
    const f = document.getElementById('pwField');
    const i = document.getElementById('pwIcon');
    f.type = f.type==='password' ? 'text' : 'password';
    i.className = f.type==='password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>