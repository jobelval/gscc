<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location:index.php'); exit; }
$stmt = $pdo->prepare("SELECT * FROM candidatures_benevoles WHERE id=?");
$stmt->execute([$id]); $c = $stmt->fetch();
if (!$c) { adminFlash('error','Introuvable.'); header('Location:index.php'); exit; }
$page_title='Candidature bénévole'; $page_section='benevoles';
$breadcrumb=[['label'=>'Bénévoles','url'=>'index.php'],['label'=>$c['prenom'].' '.$c['nom']]];
$competences = [];
try { $competences = json_decode($c['competences']??'[]',true)?:[]; } catch(Exception $e){}
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="page-header">
    <div>
        <div class="page-title"><?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?></div>
        <div class="page-subtitle">Candidature du <?= dateFr($c['date_candidature'],'d/m/Y à H:i') ?></div>
    </div>
    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start;">
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-user"></i> Informations personnelles</div></div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;font-size:.9rem;">
                    <?php foreach ([
                        ['Prénom',      $c['prenom']],
                        ['Nom',         $c['nom']],
                        ['Email',       $c['email']],
                        ['Téléphone',   $c['telephone']],
                        ['Date de naissance', $c['date_naissance'] ? dateFr($c['date_naissance']) : '—'],
                        ['Profession',  $c['profession']?:'—'],
                        ['Disponibilités', $c['disponibilites']?:'—'],
                    ] as [$l,$v]): ?>
                    <div>
                        <div style="font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;"><?= $l ?></div>
                        <div class="fw-600"><?= htmlspecialchars($v) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($competences): ?>
                <div style="margin-top:16px;">
                    <div style="font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Compétences</div>
                    <?php foreach ($competences as $comp): ?>
                        <span class="badge badge-info" style="margin:2px;"><?= htmlspecialchars($comp) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-heart"></i> Motivations</div></div>
            <div class="card-body">
                <p style="font-size:.9rem;line-height:1.8;color:var(--text);"><?= nl2br(htmlspecialchars($c['motivations'])) ?></p>
            </div>
        </div>
        <?php if ($c['notes_admin']): ?>
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-lock"></i> Notes internes</div></div>
            <div class="card-body">
                <p style="font-size:.88rem;color:var(--text-muted);"><?= nl2br(htmlspecialchars($c['notes_admin'])) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Actions rapides -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-tasks"></i> Traitement</div></div>
            <div class="card-body">
                <p style="margin-bottom:12px;font-size:.84rem;">Statut actuel : <?= statusBadge($c['statut']) ?></p>
                <?php foreach (['contacte'=>['📞 Marquer contacté','btn-outline'],'accepte'=>['✅ Accepter','btn-success'],'refuse'=>['❌ Refuser','btn-danger']] as $action=>[$label,$cls]): ?>
                <form method="POST" action="index.php" style="margin-bottom:8px;">
                    <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
                    <input type="hidden" name="cid" value="<?= $c['id'] ?>">
                    <input type="hidden" name="action" value="<?= $action ?>">
                    <button type="submit" class="btn <?= $cls ?> w-100" style="justify-content:center;"><?= $label ?></button>
                </form>
                <?php endforeach; ?>
                <hr class="divider">
                <a href="mailto:<?= htmlspecialchars($c['email']) ?>" class="btn btn-secondary w-100" style="justify-content:center;">
                    <i class="fas fa-envelope"></i> Envoyer un email
                </a>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$c['telephone']) ?>" target="_blank"
                   class="btn btn-secondary w-100" style="justify-content:center;margin-top:8px;">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-info-circle"></i> Infos</div></div>
            <div class="card-body" style="font-size:.82rem;color:var(--text-muted);">
                <p style="margin-bottom:6px;">Candidature : <?= dateFr($c['date_candidature'],'d/m/Y H:i') ?></p>
                <?php if ($c['date_traitement']): ?>
                <p>Traitement : <?= dateFr($c['date_traitement'],'d/m/Y H:i') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
