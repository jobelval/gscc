<?php
/**
 * GSCC CMS — admin/dons/view.php
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$id  = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location:index.php'); exit; }

try {
    $stmt = $pdo->prepare(
        "SELECT d.*, u.prenom, u.nom as u_nom, u.email as u_email, u.telephone as u_tel
         FROM dons d
         LEFT JOIN utilisateurs u ON d.utilisateur_id = u.id
         WHERE d.id = ?"
    );
    $stmt->execute([$id]);
    $don = $stmt->fetch();
    if (!$don) { adminFlash('error','Don introuvable.'); header('Location:index.php'); exit; }
} catch (PDOException $e) {
    adminFlash('error',$e->getMessage()); header('Location:index.php'); exit;
}

$page_title   = 'Détail du don #'.$id;
$page_section = 'dons';
$breadcrumb   = [['label'=>'Dons','url'=>'index.php'],['label'=>'#'.$id]];

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Don #<?= $id ?></div>
        <div class="page-subtitle">Reçu le <?= dateFr($don['date_don'],'d/m/Y à H:i') ?></div>
    </div>
    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

    <!-- Infos don -->
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-hand-holding-heart"></i> Informations du don</div></div>
        <div class="card-body">
            <table style="width:100%;font-size:.9rem;border-collapse:collapse;">
                <?php
                $rows = [
                    ['Montant',    '$'.number_format($don['montant'],2,'.',' ')],
                    ['Type',       ucfirst($don['type_don'])],
                    ['Mode',       ucfirst($don['mode_paiement'])],
                    ['Statut',     statusBadge($don['statut'])],
                    ['Transaction','#'.($don['transaction_id'] ?: 'N/A')],
                    ['Newsletter', $don['newsletter'] ? '✅ Oui' : '❌ Non'],
                    ['Date',       dateFr($don['date_don'],'d/m/Y H:i')],
                ];
                foreach ($rows as [$label, $val]):
                ?>
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:10px 0;color:var(--text-muted);width:160px;"><?= $label ?></td>
                    <td style="padding:10px 0;font-weight:600;"><?= $val ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php if ($don['commentaire']): ?>
            <div style="margin-top:14px;background:var(--body-bg);border-radius:8px;padding:12px;font-size:.85rem;">
                <strong>Commentaire :</strong><br>
                <?= htmlspecialchars(html_entity_decode($don['commentaire'])) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Infos donateur -->
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-user"></i> Donateur</div></div>
        <div class="card-body">
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
                <div class="avatar avatar-lg">
                    <?= strtoupper(substr($don['nom_donateur']??'D',0,1)) ?>
                </div>
                <div>
                    <div style="font-size:1.1rem;font-weight:700;"><?= htmlspecialchars($don['nom_donateur']??'Anonyme') ?></div>
                    <div style="color:var(--text-muted);"><?= htmlspecialchars($don['email_donateur']??'') ?></div>
                </div>
            </div>
            <table style="width:100%;font-size:.88rem;">
                <tr><td style="color:var(--text-muted);padding:7px 0;width:120px;">Téléphone</td>
                    <td><?= htmlspecialchars($don['telephone']??'—') ?></td></tr>
                <?php if ($don['utilisateur_id']): ?>
                <tr><td style="color:var(--text-muted);padding:7px 0;">Membre GSCC</td>
                    <td><span class="badge badge-success">✅ <?= htmlspecialchars(($don['prenom']??'').' '.($don['u_nom']??'')) ?></span></td></tr>
                <?php else: ?>
                <tr><td style="color:var(--text-muted);padding:7px 0;">Statut</td>
                    <td><span class="badge badge-secondary">Donateur externe</span></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="card" style="margin-top:16px;">
    <div class="card-header"><div class="card-title"><i class="fas fa-cog"></i> Actions</div></div>
    <div class="card-body" style="display:flex;gap:10px;flex-wrap:wrap;">
        <?php if ($don['statut']==='en_attente'): ?>
        <form method="POST" action="index.php">
            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
            <input type="hidden" name="don_id" value="<?= $id ?>">
            <input type="hidden" name="action" value="complete">
            <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Marquer complété</button>
        </form>
        <form method="POST" action="index.php">
            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
            <input type="hidden" name="don_id" value="<?= $id ?>">
            <input type="hidden" name="action" value="echoue">
            <button type="submit" class="btn btn-warning"><i class="fas fa-times"></i> Marquer échoué</button>
        </form>
        <?php endif; ?>
        <?php if ($don['statut']==='complete'): ?>
        <form method="POST" action="index.php">
            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
            <input type="hidden" name="don_id" value="<?= $id ?>">
            <input type="hidden" name="action" value="rembourse">
            <button type="submit" class="btn btn-secondary" onclick="return confirm('Confirmer le remboursement ?')">
                <i class="fas fa-rotate-left"></i> Rembourser
            </button>
        </form>
        <?php endif; ?>
        <form method="POST" action="index.php" onsubmit="return confirm('Supprimer ce don ?')">
            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
            <input type="hidden" name="don_id" value="<?= $id ?>">
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Supprimer</button>
        </form>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>