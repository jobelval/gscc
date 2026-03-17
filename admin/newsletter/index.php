<?php
/**
 * GSCC CMS — admin/newsletter/index.php
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$page_title   = 'Newsletter';
$page_section = 'newsletter';
$breadcrumb   = [['label' => 'Newsletter']];

/* ── Actions ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $action = $_POST['action'] ?? '';
    $ids    = array_map('intval', $_POST['ids'] ?? []);

    if ($action === 'send_newsletter') {
        // Envoi newsletter
        $subject = trim($_POST['nl_subject'] ?? '');
        $body    = $_POST['nl_body'] ?? '';
        $test_email = trim($_POST['test_email'] ?? '');

        if ($subject && $body) {
            try {
                if ($test_email) {
                    // Envoi test
                    nlSendOne($pdo, $test_email, 'Test', $subject, $body);
                    adminFlash('success', "Email de test envoyé à $test_email");
                } else {
                    // Envoi à tous les abonnés actifs
                    $abonnes = $pdo->query("SELECT email,nom FROM newsletter_abonnes WHERE statut='actif'")->fetchAll();
                    $sent = 0;
                    foreach ($abonnes as $ab) {
                        nlSendOne($pdo, $ab['email'], $ab['nom']??'', $subject, $body);
                        $sent++;
                        if ($sent % 10 === 0) usleep(100000); // anti-spam
                    }
                    $pdo->query("UPDATE newsletter_abonnes SET derniere_envoi=NOW() WHERE statut='actif'");
                    adminFlash('success', "Newsletter envoyée à $sent abonné(s) !");
                }
            } catch (Exception $e) { adminFlash('error', $e->getMessage()); }
        } else {
            adminFlash('error', 'Sujet et contenu obligatoires.');
        }
        header('Location: index.php'); exit;
    }

    if ($ids) {
        match ($action) {
            'delete'       => $pdo->prepare("DELETE FROM newsletter_abonnes WHERE id IN (".implode(',',$ids).")")->execute(),
            'desabonner'   => $pdo->prepare("UPDATE newsletter_abonnes SET statut='desabonne' WHERE id IN (".implode(',',$ids).")")->execute(),
            'reabonner'    => $pdo->prepare("UPDATE newsletter_abonnes SET statut='actif' WHERE id IN (".implode(',',$ids).")")->execute(),
            default        => null,
        };
        adminFlash('success', 'Action appliquée sur '.count($ids).' abonné(s).');
        header('Location: index.php'); exit;
    }
}

/* ── Envoi individuel ── */
function nlSendOne(PDO $pdo, string $email, string $nom, string $subject, string $body): void {
    $prenom   = $nom ? htmlspecialchars(ucwords(strtolower($nom))) : 'cher(e) abonné(e)';
    $site     = defined('SITE_NAME') ? SITE_NAME : 'GSCC';
    $url      = defined('SITE_URL')  ? rtrim(SITE_URL, '/') : '';
    $from     = defined('SITE_EMAIL')? SITE_EMAIL : 'gscc@gscchaiti.com';

    $stmt = $pdo->prepare("SELECT token_desabonnement FROM newsletter_abonnes WHERE email=? LIMIT 1");
    $stmt->execute([$email]); $row = $stmt->fetch();
    $token = $row['token_desabonnement'] ?? bin2hex(random_bytes(16));
    $unsub = $url.'/newsletter-unsubscribe.php?token='.urlencode($token);

    $html = "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'></head>
    <body style='margin:0;padding:0;background:#F4F6FB;font-family:Arial,sans-serif;'>
    <table width='100%' cellpadding='0' cellspacing='0' style='background:#F4F6FB;padding:36px 16px;'>
    <tr><td align='center'>
    <table width='600' cellpadding='0' cellspacing='0'
      style='max-width:600px;width:100%;background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 4px 24px rgba(0,51,153,.10);'>
      <tr><td style='background:linear-gradient(135deg,#003399,#1a56cc);padding:30px 36px;text-align:center;'>
        <div style='font-size:30px;'>🎗️</div>
        <div style='font-family:Georgia,serif;font-size:22px;font-weight:700;color:#fff;margin-top:8px;'>$site</div>
      </td></tr>
      <tr><td style='padding:36px 36px 28px;'>
        <p style='color:#374151;font-size:15px;margin:0 0 8px;'>Bonjour, <strong>$prenom</strong> 👋</p>
        <div style='color:#374151;font-size:14.5px;line-height:1.8;margin:16px 0;'>".nl2br(htmlspecialchars($body))."</div>
        <div style='text-align:center;margin-top:28px;'>
          <a href='$url' style='display:inline-block;background:linear-gradient(135deg,#003399,#1a56cc);color:#fff;padding:13px 32px;border-radius:30px;text-decoration:none;font-weight:700;font-size:14px;'>Visiter le site</a>
        </div>
      </td></tr>
      <tr><td style='background:#F4F6FB;border-top:1px solid #E5E9F2;padding:16px 36px;text-align:center;'>
        <p style='color:#9CA3AF;font-size:12px;margin:0;'>
          <a href='$unsub' style='color:#D94F7A;text-decoration:none;'>Se désabonner</a> &nbsp;·&nbsp; © ".date('Y')." $site
        </p>
      </td></tr>
    </table></td></tr></table></body></html>";

    $hdr  = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
    $hdr .= "From: $site <$from>\r\n";
    @mail($email, '=?UTF-8?B?'.base64_encode($subject).'?=', $html, $hdr);
}

/* ── Liste abonnés ── */
$statut_f = $_GET['statut'] ?? '';
$search   = trim($_GET['q'] ?? '');
$page     = max(1,(int)($_GET['p']??1));
$per      = 25;
$where    = ['1=1']; $params=[];
if ($statut_f) { $where[]="statut=?"; $params[]=$statut_f; }
if ($search)   { $where[]="(email LIKE ? OR nom LIKE ?)"; $p="%$search%"; $params=array_merge($params,[$p,$p]); }
$sw = implode(' AND ',$where);

try {
    $cnt=$pdo->prepare("SELECT COUNT(*) FROM newsletter_abonnes WHERE $sw"); $cnt->execute($params); $total=(int)$cnt->fetchColumn();
    $pages=(int)ceil($total/$per); $offset=($page-1)*$per;
    $stmt=$pdo->prepare("SELECT * FROM newsletter_abonnes WHERE $sw ORDER BY date_inscription DESC LIMIT $per OFFSET $offset");
    $stmt->execute($params); $abonnes=$stmt->fetchAll();
    $nb_actif=(int)$pdo->query("SELECT COUNT(*) FROM newsletter_abonnes WHERE statut='actif'")->fetchColumn();
    $nb_desab=(int)$pdo->query("SELECT COUNT(*) FROM newsletter_abonnes WHERE statut='desabonne'")->fetchColumn();
    $nb_total=(int)$pdo->query("SELECT COUNT(*) FROM newsletter_abonnes")->fetchColumn();
} catch (PDOException $e) { $abonnes=[]; $total=$pages=0; $nb_actif=$nb_desab=$nb_total=0; }

$tab = $_GET['tab'] ?? 'abonnes';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Newsletter</div>
        <div class="page-subtitle">Gérer les abonnés et envoyer des campagnes email</div>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
        <div class="stat-info"><div class="stat-value"><?= $nb_actif ?></div><div class="stat-label">Abonnés actifs</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon rose"><i class="fas fa-user-minus"></i></div>
        <div class="stat-info"><div class="stat-value"><?= $nb_desab ?></div><div class="stat-label">Désabonnés</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="stat-info"><div class="stat-value"><?= $nb_total ?></div><div class="stat-label">Total inscrits</div></div>
    </div>
</div>

<!-- Onglets -->
<div style="display:flex;gap:0;margin-bottom:20px;border-bottom:2px solid var(--border);">
    <a href="?tab=abonnes" style="padding:10px 20px;font-weight:600;font-size:.9rem;text-decoration:none;border-bottom:2px solid <?= $tab==='abonnes'?'var(--primary)':'transparent' ?>;color:<?= $tab==='abonnes'?'var(--primary)':'var(--text-muted)' ?>;margin-bottom:-2px;">
        <i class="fas fa-users"></i> Abonnés (<?= $nb_total ?>)
    </a>
    <a href="?tab=composer" style="padding:10px 20px;font-weight:600;font-size:.9rem;text-decoration:none;border-bottom:2px solid <?= $tab==='composer'?'var(--primary)':'transparent' ?>;color:<?= $tab==='composer'?'var(--primary)':'var(--text-muted)' ?>;margin-bottom:-2px;">
        <i class="fas fa-paper-plane"></i> Composer & Envoyer
    </a>
</div>

<?php if ($tab === 'composer'): ?>

<!-- Composer newsletter -->
<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start;">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-pen"></i> Rédiger la newsletter</div></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                <input type="hidden" name="action" value="send_newsletter">
                <div class="form-group">
                    <label class="form-label">Sujet <span class="required">*</span></label>
                    <input type="text" name="nl_subject" class="form-control" required
                           placeholder="Ex. [GSCC] Nos actualités de mars 2026…">
                </div>
                <div class="form-group">
                    <label class="form-label">Contenu <span class="required">*</span></label>
                    <textarea name="nl_body" class="form-control" rows="12" required
                              placeholder="Rédigez votre message ici. Il sera mis en forme automatiquement dans un email responsive.&#10;&#10;Bonjour,&#10;&#10;Nous sommes heureux de vous partager nos dernières nouvelles…"></textarea>
                    <div class="form-hint">Texte brut recommandé. Les sauts de ligne sont conservés.</div>
                </div>
                <div style="background:#F0F9FF;border:1px solid #7DD3FC;border-radius:8px;padding:14px;margin-bottom:16px;">
                    <div style="font-size:.82rem;font-weight:700;color:#075985;margin-bottom:8px;"><i class="fas fa-vial"></i> Test avant envoi</div>
                    <div style="display:flex;gap:8px;">
                        <input type="email" name="test_email" class="form-control" placeholder="votre@email.com" style="flex:1;">
                        <button type="submit" class="btn btn-secondary" style="white-space:nowrap;">
                            <i class="fas fa-paper-plane"></i> Envoyer test
                        </button>
                    </div>
                    <div class="form-hint">Envoi unique à cet email pour vérification avant diffusion.</div>
                </div>
                <button type="submit" class="btn btn-primary"
                        onclick="return confirm('Envoyer à TOUS les <?= $nb_actif ?> abonné(s) actif(s) ? Cette action est irréversible.')">
                    <i class="fas fa-rocket"></i> Envoyer à tous les abonnés (<?= $nb_actif ?>)
                </button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-info-circle"></i> Infos envoi</div></div>
        <div class="card-body" style="font-size:.84rem;">
            <div style="background:#F0FDF4;border-radius:8px;padding:14px;margin-bottom:12px;">
                <div class="fw-600" style="color:var(--success);margin-bottom:4px;">✅ <?= $nb_actif ?> abonnés actifs</div>
                <div style="color:var(--text-muted);">La newsletter sera envoyée à ces abonnés uniquement.</div>
            </div>
            <p style="color:var(--text-muted);margin-bottom:8px;">📧 Expéditeur : <strong><?= SITE_EMAIL ?></strong></p>
            <p style="color:var(--text-muted);margin-bottom:8px;">⏱️ Délai entre envois : 100ms (anti-spam)</p>
            <p style="color:var(--text-muted);">🔗 Lien de désabonnement inclus automatiquement.</p>
        </div>
    </div>
</div>

<?php else: ?>

<!-- Liste abonnés -->
<div class="card">
    <div class="card-header">
        <form method="GET" class="toolbar" style="margin:0;width:100%;">
            <div class="search-box"><i class="fas fa-search"></i>
                <input type="text" name="q" class="form-control" placeholder="Email, nom…" value="<?= htmlspecialchars($search) ?>">
            </div>
            <select name="statut" class="form-control" style="width:160px;">
                <option value="">Tous statuts</option>
                <option value="actif"      <?= $statut_f==='actif'     ?'selected':'' ?>>Actif</option>
                <option value="desabonne"  <?= $statut_f==='desabonne' ?'selected':'' ?>>Désabonné</option>
            </select>
            <input type="hidden" name="tab" value="abonnes">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i></button>
            <a href="export.php" class="btn btn-secondary"><i class="fas fa-download"></i> CSV</a>
            <?php if ($search||$statut_f): ?><a href="?tab=abonnes" class="btn btn-ghost"><i class="fas fa-times"></i></a><?php endif; ?>
        </form>
    </div>

    <form method="POST">
        <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
        <div style="display:flex;gap:10px;padding:10px 16px;background:#FAFBFF;border-bottom:1px solid var(--border);">
            <select name="action" class="form-control" style="width:200px;">
                <option value="">— Action groupée —</option>
                <option value="desabonner">Désabonner</option>
                <option value="reabonner">Réabonner</option>
                <option value="delete">Supprimer</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Confirmer ?')">Appliquer</button>
        </div>
        <div class="table-wrap">
            <table class="admin-table">
                <thead><tr>
                    <th class="col-check"><input type="checkbox" id="selectAll"></th>
                    <th>Email</th><th>Nom</th><th>Statut</th><th>Date inscription</th><th>Dernier envoi</th>
                </tr></thead>
                <tbody>
                <?php if ($abonnes): foreach ($abonnes as $ab): ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?= $ab['id'] ?>" class="row-check"></td>
                    <td class="fw-600"><?= htmlspecialchars($ab['email']) ?></td>
                    <td style="color:var(--text-muted);"><?= htmlspecialchars($ab['nom']?:'—') ?></td>
                    <td><?= statusBadge($ab['statut']) ?></td>
                    <td style="font-size:.78rem;color:var(--text-muted);"><?= dateFr($ab['date_inscription'],'d/m/Y') ?></td>
                    <td style="font-size:.78rem;color:var(--text-muted);"><?= $ab['derniere_envoi']?dateFr($ab['derniere_envoi'],'d/m/Y'):'—' ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="6"><div class="empty-state"><i class="fas fa-paper-plane"></i><h3>Aucun abonné</h3></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
    <?php if ($pages>1): ?>
    <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between;">
        <span>Page <?= $page ?>/<?= $pages ?> — <?= $total ?> abonné(s)</span>
        <div class="pagination"><?php for ($i=1;$i<=$pages;$i++): ?>
            <a href="?tab=abonnes&p=<?= $i ?>&statut=<?= $statut_f ?>&q=<?= urlencode($search) ?>" class="page-link <?= $i==$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?></div>
    </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<script>
const sa = document.getElementById('selectAll');
if(sa) sa.addEventListener('change',function(){ document.querySelectorAll('.row-check').forEach(cb=>cb.checked=this.checked); });
</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
