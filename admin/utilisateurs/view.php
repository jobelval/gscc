<?php
/**
 * GSCC CMS — admin/utilisateurs/view.php
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location:index.php'); exit; }

try {
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id=?");
    $stmt->execute([$id]); $user = $stmt->fetch();
    if (!$user) { adminFlash('error','Introuvable.'); header('Location:index.php'); exit; }

    $dons = $pdo->prepare("SELECT * FROM dons WHERE utilisateur_id=? ORDER BY date_don DESC");
    $dons->execute([$id]); $user_dons = $dons->fetchAll();

    $dem = $pdo->prepare("SELECT * FROM demandes_aide WHERE utilisateur_id=? ORDER BY date_soumission DESC");
    $dem->execute([$id]); $demandes = $dem->fetchAll();

} catch (PDOException $e) {
    adminFlash('error',$e->getMessage()); header('Location:index.php'); exit;
}

$page_title   = 'Profil utilisateur';
$page_section = 'utilisateurs';
$breadcrumb   = [['label'=>'Utilisateurs','url'=>'index.php'],['label'=>'Profil']];

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title"><?= htmlspecialchars(($user['prenom']??'').' '.($user['nom']??'')) ?></div>
        <div class="page-subtitle"><?= htmlspecialchars($user['email']) ?></div>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="edit.php?id=<?= $id ?>" class="btn btn-primary"><i class="fas fa-pen"></i> Modifier</a>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:280px 1fr;gap:16px;align-items:start;">

    <!-- Carte profil -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card" style="text-align:center;">
            <div class="card-body">
                <?php if ($user['photo_url']): ?>
                    <img src="<?= htmlspecialchars($user['photo_url']) ?>"
                         style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin:0 auto 12px;display:block;border:3px solid var(--border);">
                <?php else: ?>
                    <div class="avatar avatar-lg" style="margin:0 auto 12px;width:80px;height:80px;font-size:28px;">
                        <?= strtoupper(substr($user['prenom']??'U',0,1).substr($user['nom']??'',0,1)) ?>
                    </div>
                <?php endif; ?>
                <div style="font-size:1.1rem;font-weight:700;"><?= htmlspecialchars(($user['prenom']??'').' '.($user['nom']??'')) ?></div>
                <div style="color:var(--text-muted);font-size:.84rem;margin:4px 0 10px;"><?= htmlspecialchars($user['email']) ?></div>
                <?= statusBadge($user['role']) ?> <?= statusBadge($user['statut']) ?>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-address-card"></i> Coordonnées</div></div>
            <div class="card-body" style="font-size:.84rem;">
                <?php
                $fields = [
                    ['fas fa-phone',     $user['telephone']   ?? '—'],
                    ['fas fa-city',      $user['ville']       ?? '—'],
                    ['fas fa-map-marker',$user['adresse']     ?? '—'],
                    ['fas fa-briefcase', $user['profession']  ?? '—'],
                ];
                foreach ($fields as [$ic, $val]): ?>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;color:var(--text-muted);">
                    <i class="<?= $ic ?>" style="width:14px;text-align:center;"></i>
                    <?= htmlspecialchars($val) ?>
                </div>
                <?php endforeach; ?>
                <hr class="divider">
                <div style="font-size:.78rem;color:var(--text-muted);">
                    Inscrit le <?= dateFr($user['date_inscription'],'d/m/Y') ?><br>
                    Dernière connexion : <?= $user['derniere_connexion'] ? dateFr($user['derniere_connexion'],'d/m/Y H:i') : 'Jamais' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Onglets contenu -->
    <div style="display:flex;flex-direction:column;gap:16px;">

        <!-- Dons -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-hand-holding-heart"></i> Dons (<?= count($user_dons) ?>)</div>
            </div>
            <?php if ($user_dons): ?>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Montant</th><th>Mode</th><th>Statut</th><th>Date</th><th>Commentaire</th></tr></thead>
                    <tbody>
                    <?php foreach ($user_dons as $d): ?>
                    <tr>
                        <td class="fw-600" style="color:var(--success);"><?= number_format($d['montant'],2,',',' ') ?> HTG</td>
                        <td><?= ucfirst($d['mode_paiement']) ?></td>
                        <td><?= statusBadge($d['statut']) ?></td>
                        <td style="font-size:.78rem;color:var(--text-muted);"><?= dateFr($d['date_don'],'d/m/Y') ?></td>
                        <td style="font-size:.8rem;"><?= htmlspecialchars(truncate(html_entity_decode($d['commentaire']??''),30)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="card-body" style="text-align:center;color:var(--text-muted);font-size:.88rem;">Aucun don enregistré.</div>
            <?php endif; ?>
        </div>

        <!-- Demandes d'aide -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-file-medical"></i> Demandes d'aide (<?= count($demandes) ?>)</div>
            </div>
            <?php if ($demandes): ?>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Type</th><th>Statut</th><th>Date</th><th>Description</th></tr></thead>
                    <tbody>
                    <?php foreach ($demandes as $dem): ?>
                    <tr>
                        <td><span class="badge badge-info"><?= ucfirst(str_replace('_',' ',$dem['type_aide'])) ?></span></td>
                        <td><?= statusBadge($dem['statut']) ?></td>
                        <td style="font-size:.78rem;color:var(--text-muted);"><?= dateFr($dem['date_soumission'],'d/m/Y') ?></td>
                        <td style="font-size:.82rem;"><?= htmlspecialchars(truncate($dem['description_demande'],50)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="card-body" style="text-align:center;color:var(--text-muted);font-size:.88rem;">Aucune demande.</div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
