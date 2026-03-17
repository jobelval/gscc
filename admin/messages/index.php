<?php
/**
 * GSCC CMS — admin/messages/index.php
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$page_title   = 'Messages';
$page_section = 'messages';
$breadcrumb   = [['label' => 'Messages de contact']];

/* ── Actions ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $action = $_POST['action'] ?? '';
    $mid    = (int)($_POST['mid'] ?? 0);
    $ids    = array_map('intval', $_POST['ids'] ?? []);

    if ($mid && $action === 'reply') {
        $reply_text = trim($_POST['reply_text'] ?? '');
        if ($reply_text) {
            try {
                $msg = $pdo->prepare("SELECT * FROM messages_contact WHERE id=?");
                $msg->execute([$mid]); $m = $msg->fetch();
                if ($m) {
                    $subject  = 'Re: '.($m['sujet'] ?: 'Message GSCC');
                    $body     = nl2br(htmlspecialchars($reply_text));
                    $html = "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                        <div style='background:#003399;color:#fff;padding:20px;border-radius:10px 10px 0 0;'>
                            <strong>🎗️ GSCC — Groupe de Support Contre le Cancer</strong>
                        </div>
                        <div style='padding:24px;background:#f9f9f9;'>
                            <p>Bonjour ".htmlspecialchars($m['nom']).",</p>
                            <div style='line-height:1.8;'>{$body}</div>
                            <hr style='margin:20px 0;border:none;border-top:1px solid #eee;'>
                            <p style='font-size:12px;color:#888;'>L'équipe GSCC · gscchaiti.com</p>
                        </div>
                    </body></html>";
                    $headers  = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
                    $headers .= "From: GSCC <".SITE_EMAIL.">\r\n";
                    mail($m['email'], '=?UTF-8?B?'.base64_encode($subject).'?=', $html, $headers);
                    $pdo->prepare("UPDATE messages_contact SET lu=1,repondu=1,date_reponse=NOW(),reponse_par=? WHERE id=?")
                        ->execute([$_SESSION['admin_id'], $mid]);
                    adminFlash('success','Réponse envoyée à '.$m['email']);
                }
            } catch (PDOException $e) { adminFlash('error',$e->getMessage()); }
        }
        header('Location: index.php'); exit;
    }

    if ($mid && $action === 'lu') {
        $pdo->prepare("UPDATE messages_contact SET lu=1 WHERE id=?")->execute([$mid]);
        header('Location: index.php'); exit;
    }

    if ($ids) {
        match ($action) {
            'mark_read'   => $pdo->prepare("UPDATE messages_contact SET lu=1 WHERE id IN (".implode(',',$ids).")")->execute(),
            'delete'      => $pdo->prepare("DELETE FROM messages_contact WHERE id IN (".implode(',',$ids).")")->execute(),
            default       => null,
        };
        adminFlash('success','Action appliquée.');
        header('Location: index.php'); exit;
    }
}

/* ── Filtres ── */
$filtre = $_GET['f'] ?? '';
$search = trim($_GET['q'] ?? '');
$page   = max(1,(int)($_GET['p']??1));
$per    = 25;
$where  = ['1=1']; $params = [];
if ($filtre === 'non_lu')   { $where[] = "lu=0"; }
if ($filtre === 'repondu')  { $where[] = "repondu=1"; }
if ($filtre === 'en_attente'){ $where[] = "repondu=0"; }
if ($search) { $where[]="(nom LIKE ? OR email LIKE ? OR sujet LIKE ?)"; $p="%$search%"; $params=array_merge($params,[$p,$p,$p]); }
$sw = implode(' AND ',$where);

try {
    $cnt=$pdo->prepare("SELECT COUNT(*) FROM messages_contact WHERE $sw"); $cnt->execute($params); $total=(int)$cnt->fetchColumn();
    $pages=(int)ceil($total/$per); $offset=($page-1)*$per;
    $stmt=$pdo->prepare("SELECT * FROM messages_contact WHERE $sw ORDER BY date_envoi DESC LIMIT $per OFFSET $offset");
    $stmt->execute($params); $messages=$stmt->fetchAll();
    $nb_non_lu=(int)$pdo->query("SELECT COUNT(*) FROM messages_contact WHERE lu=0")->fetchColumn();
    $nb_total=(int)$pdo->query("SELECT COUNT(*) FROM messages_contact")->fetchColumn();
    $nb_repondu=(int)$pdo->query("SELECT COUNT(*) FROM messages_contact WHERE repondu=1")->fetchColumn();
} catch (PDOException $e) { $messages=[]; $total=$pages=0; $nb_non_lu=$nb_total=$nb_repondu=0; }

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Messages de contact
            <?php if ($nb_non_lu > 0): ?>
                <span class="badge badge-danger" style="font-size:.75rem;vertical-align:middle;"><?= $nb_non_lu ?> non lu<?= $nb_non_lu>1?'s':'' ?></span>
            <?php endif; ?>
        </div>
        <div class="page-subtitle">Messages reçus via le formulaire de contact</div>
    </div>
</div>

<!-- Tabs -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <?php foreach ([
        ''           => ['Tous',        $nb_total,   'secondary'],
        'non_lu'     => ['Non lus',     $nb_non_lu,  'danger'],
        'en_attente' => ['Sans réponse',$nb_total-$nb_repondu,'warning'],
        'repondu'    => ['Répondus',    $nb_repondu, 'success'],
    ] as $val=>[$label,$nb,$type]):
        $active = ($filtre===$val) ? 'border-color:var(--primary);background:var(--primary-light);color:var(--primary);':'' ?>
    <a href="?f=<?= $val ?>&q=<?= urlencode($search) ?>"
       style="display:inline-flex;align-items:center;gap:8px;padding:7px 14px;border-radius:8px;border:1.5px solid var(--border);font-size:.82rem;font-weight:600;text-decoration:none;color:var(--text);<?= $active ?>">
        <?= $label ?> <span class="badge badge-<?= $type ?>"><?= $nb ?></span>
    </a>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="toolbar" style="margin:0;width:100%;">
            <div class="search-box"><i class="fas fa-search"></i>
                <input type="text" name="q" class="form-control" placeholder="Nom, email, sujet…" value="<?= htmlspecialchars($search) ?>">
            </div>
            <input type="hidden" name="f" value="<?= htmlspecialchars($filtre) ?>">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
            <?php if ($search||$filtre): ?><a href="index.php" class="btn btn-ghost"><i class="fas fa-times"></i></a><?php endif; ?>
        </form>
    </div>

    <form method="POST">
        <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
        <div style="display:flex;gap:10px;padding:10px 16px;background:#FAFBFF;border-bottom:1px solid var(--border);">
            <select name="action" class="form-control" style="width:200px;">
                <option value="">— Action groupée —</option>
                <option value="mark_read">Marquer comme lu</option>
                <option value="delete">Supprimer</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Confirmer ?')">Appliquer</button>
        </div>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="col-check"><input type="checkbox" id="selectAll"></th>
                        <th>Expéditeur</th>
                        <th>Sujet</th>
                        <th>Message</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th class="col-actions" style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($messages): foreach ($messages as $m): ?>
                <tr style="<?= !$m['lu'] ? 'background:#FFFBEB;' : '' ?>">
                    <td><input type="checkbox" name="ids[]" value="<?= $m['id'] ?>" class="row-check"></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:9px;">
                            <div class="avatar avatar-sm"><?= strtoupper(substr($m['nom']??'M',0,1)) ?></div>
                            <div>
                                <div class="fw-600" style="<?= !$m['lu']?'color:var(--primary);':'' ?>"><?= htmlspecialchars($m['nom']) ?></div>
                                <div style="font-size:.76rem;color:var(--text-muted);"><?= htmlspecialchars($m['email']) ?></div>
                                <?php if ($m['telephone']): ?>
                                <div style="font-size:.74rem;color:var(--text-muted);"><?= htmlspecialchars($m['telephone']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:.85rem;font-weight:<?= !$m['lu']?600:400 ?>;"><?= htmlspecialchars(truncate($m['sujet']??'Sans sujet',40)) ?></td>
                    <td style="font-size:.82rem;color:var(--text-muted);"><?= htmlspecialchars(truncate($m['message'],60)) ?></td>
                    <td>
                        <?php if (!$m['lu']): ?>
                            <span class="badge badge-warning">Non lu</span>
                        <?php elseif ($m['repondu']): ?>
                            <span class="badge badge-success">Répondu</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Lu</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.78rem;color:var(--text-muted);white-space:nowrap;"><?= dateFr($m['date_envoi'],'d/m/Y H:i') ?></td>
                    <td class="col-actions">
                        <button type="button" class="btn btn-xs btn-primary"
                                onclick="openReply(<?= $m['id'] ?>,'<?= htmlspecialchars(addslashes($m['nom'])) ?>','<?= htmlspecialchars(addslashes($m['email'])) ?>','<?= htmlspecialchars(addslashes($m['sujet']??'')) ?>','<?= htmlspecialchars(addslashes($m['message'])) ?>')">
                            <i class="fas fa-reply"></i>
                        </button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce message ?')">
                            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="ids[]" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="7"><div class="empty-state"><i class="fas fa-envelope-open"></i><h3>Aucun message</h3></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>

    <?php if ($pages>1): ?>
    <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between;">
        <span>Page <?= $page ?>/<?= $pages ?> — <?= $total ?> message(s)</span>
        <div class="pagination"><?php for ($i=1;$i<=$pages;$i++): ?>
            <a href="?p=<?= $i ?>&f=<?= $filtre ?>&q=<?= urlencode($search) ?>" class="page-link <?= $i==$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?></div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal réponse -->
<div class="modal-overlay" id="replyModal">
    <div class="modal" style="max-width:580px;">
        <div class="modal-header">
            <span class="modal-title"><i class="fas fa-reply"></i> Répondre à <span id="replyName"></span></span>
            <button class="modal-close" onclick="document.getElementById('replyModal').classList.remove('show')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
            <input type="hidden" name="action" value="reply">
            <input type="hidden" name="mid" id="replyMid">
            <div class="modal-body">
                <div style="background:var(--body-bg);border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:.84rem;border:1px solid var(--border);">
                    <div style="font-size:.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Message original</div>
                    <div id="replySubject" style="font-weight:600;margin-bottom:4px;"></div>
                    <div id="replyOriginal" style="color:var(--text-muted);"></div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Votre réponse <span class="required">*</span></label>
                    <textarea name="reply_text" class="form-control" rows="6" required
                              placeholder="Tapez votre réponse ici…"></textarea>
                    <div class="form-hint">Un email sera envoyé depuis <?= SITE_EMAIL ?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('replyModal').classList.remove('show')" class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Envoyer</button>
            </div>
        </form>
    </div>
</div>

<script>
function openReply(id,name,email,subject,message){
    document.getElementById('replyMid').value=id;
    document.getElementById('replyName').textContent=name+' <'+email+'>';
    document.getElementById('replySubject').textContent='Sujet : '+(subject||'Sans sujet');
    document.getElementById('replyOriginal').textContent=message.substring(0,200)+(message.length>200?'…':'');
    document.getElementById('replyModal').classList.add('show');
}
document.getElementById('selectAll').addEventListener('change',function(){
    document.querySelectorAll('.row-check').forEach(cb=>cb.checked=this.checked);
});
</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
