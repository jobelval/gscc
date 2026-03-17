<?php
/**
 * GSCC CMS — admin/parametres/index.php
 * Paramètres généraux du site GSCC
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$page_title   = 'Paramètres';
$page_section = 'parametres';
$breadcrumb   = [['label' => 'Paramètres du site']];

/* ── Sauvegarder ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCheckCsrf()) {
    $section = $_POST['section'] ?? '';
    $updated = 0;

    try {
        if ($section === 'general') {
            $fields = ['nom_site','slogan','email_contact','telephone','adresse',
                       'description_meta','mots_cles_meta','google_analytics'];
            foreach ($fields as $f) {
                if (isset($_POST[$f])) {
                    $pdo->prepare(
                        "INSERT INTO parametres (cle,valeur,date_modification) VALUES (?,?,NOW())
                         ON DUPLICATE KEY UPDATE valeur=?,date_modification=NOW()"
                    )->execute([$f, trim($_POST[$f]), trim($_POST[$f])]);
                    $updated++;
                }
            }
        }

        if ($section === 'reseaux') {
            $fields = ['facebook','twitter','instagram','youtube','whatsapp','linkedin'];
            foreach ($fields as $f) {
                $v = trim($_POST[$f] ?? '');
                $pdo->prepare(
                    "INSERT INTO parametres (cle,valeur,date_modification) VALUES (?,?,NOW())
                     ON DUPLICATE KEY UPDATE valeur=?,date_modification=NOW()"
                )->execute([$f, $v, $v]);
                $updated++;
            }
        }

        if ($section === 'dons') {
            $fields = ['paypal_email','stripe_public_key','stripe_secret_key','moncash_client_id','moncash_client_secret'];
            foreach ($fields as $f) {
                if (isset($_POST[$f])) {
                    $v = trim($_POST[$f]);
                    $pdo->prepare(
                        "INSERT INTO parametres (cle,valeur,date_modification) VALUES (?,?,NOW())
                         ON DUPLICATE KEY UPDATE valeur=?,date_modification=NOW()"
                    )->execute([$f, $v, $v]);
                    $updated++;
                }
            }
        }

        if ($section === 'smtp') {
            $fields = ['smtp_host','smtp_port','smtp_user','smtp_from_name'];
            foreach ($fields as $f) {
                if (isset($_POST[$f])) {
                    $v = trim($_POST[$f]);
                    $pdo->prepare(
                        "INSERT INTO parametres (cle,valeur,date_modification) VALUES (?,?,NOW())
                         ON DUPLICATE KEY UPDATE valeur=?,date_modification=NOW()"
                    )->execute([$f, $v, $v]);
                    $updated++;
                }
            }
            // Mot de passe SMTP (ne pas écraser si vide)
            if (!empty(trim($_POST['smtp_password'] ?? ''))) {
                $v = trim($_POST['smtp_password']);
                $pdo->prepare(
                    "INSERT INTO parametres (cle,valeur,date_modification) VALUES ('smtp_password',?,NOW())
                     ON DUPLICATE KEY UPDATE valeur=?,date_modification=NOW()"
                )->execute([$v,$v]);
            }
        }

        if ($section === 'maintenance') {
            $mode = isset($_POST['maintenance_mode']) ? '1' : '0';
            $msg  = trim($_POST['maintenance_message'] ?? '');
            foreach (['maintenance_mode'=>$mode,'maintenance_message'=>$msg] as $k=>$v) {
                $pdo->prepare(
                    "INSERT INTO parametres (cle,valeur,date_modification) VALUES (?,?,NOW())
                     ON DUPLICATE KEY UPDATE valeur=?,date_modification=NOW()"
                )->execute([$k,$v,$v]);
            }
            $updated = 2;
        }

        if ($updated > 0) adminFlash('success', 'Paramètres enregistrés avec succès !');
    } catch (PDOException $e) {
        adminFlash('error', 'Erreur : ' . $e->getMessage());
    }
    header('Location: index.php?tab=' . $_POST['tab_after'] ?? 'general'); exit;
}

/* ── Charger tous les paramètres ── */
try {
    $all = $pdo->query("SELECT cle,valeur FROM parametres")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) { $all = []; }

$p = function(string $k, string $d = '') use ($all): string {
    return htmlspecialchars($all[$k] ?? $d);
};

$tab = $_GET['tab'] ?? 'general';

/* ── Statistiques système ── */
try {
    $sys = [
        'articles'   => $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn(),
        'users'      => $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn(),
        'dons'       => $pdo->query("SELECT COALESCE(SUM(montant),0) FROM dons")->fetchColumn(),
        'galerie'    => $pdo->query("SELECT COUNT(*) FROM galerie")->fetchColumn(),
        'newsletter' => $pdo->query("SELECT COUNT(*) FROM newsletter_abonnes WHERE statut='actif'")->fetchColumn(),
        'db_size'    => $pdo->query("SELECT ROUND(SUM(data_length+index_length)/1024/1024,2) FROM information_schema.tables WHERE table_schema=DATABASE()")->fetchColumn(),
    ];
} catch (PDOException $e) { $sys = []; }

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-header">
    <div><div class="page-title">Paramètres du site</div>
    <div class="page-subtitle">Configuration générale du site GSCC</div></div>
</div>

<!-- Onglets -->
<div style="display:flex;gap:0;margin-bottom:24px;border-bottom:2px solid var(--border);flex-wrap:wrap;">
    <?php
    $tabs = [
        'general'     => ['<i class="fas fa-cog"></i> Général',       ''],
        'reseaux'     => ['<i class="fas fa-share-alt"></i> Réseaux',  ''],
        'dons'        => ['<i class="fas fa-hand-holding-heart"></i> Dons', ''],
        'smtp'        => ['<i class="fas fa-envelope"></i> Email',     ''],
        'maintenance' => ['<i class="fas fa-tools"></i> Maintenance',  ''],
        'systeme'     => ['<i class="fas fa-server"></i> Système',     ''],
    ];
    foreach ($tabs as $k => [$label, $extra]):
        $act = ($tab === $k);
    ?>
    <a href="?tab=<?= $k ?>" style="padding:10px 18px;font-weight:600;font-size:.88rem;text-decoration:none;white-space:nowrap;border-bottom:2px solid <?= $act?'var(--primary)':'transparent' ?>;color:<?= $act?'var(--primary)':'var(--text-muted)' ?>;margin-bottom:-2px;display:flex;align-items:center;gap:7px;">
        <?= $label ?>
    </a>
    <?php endforeach; ?>
</div>

<?php if ($tab === 'general'): ?>
<!-- GÉNÉRAL -->
<form method="POST">
    <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
    <input type="hidden" name="section" value="general">
    <input type="hidden" name="tab_after" value="general">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-globe"></i> Identité du site</div></div>
            <div class="card-body">
                <div class="form-group"><label class="form-label">Nom du site</label>
                    <input type="text" name="nom_site" class="form-control" value="<?= $p('nom_site','GSCC') ?>" placeholder="GSCC…"></div>
                <div class="form-group"><label class="form-label">Slogan</label>
                    <input type="text" name="slogan" class="form-control" value="<?= $p('slogan') ?>" placeholder="Slogan affiché sur le site…"></div>
                <div class="form-group"><label class="form-label">Email de contact</label>
                    <input type="email" name="email_contact" class="form-control" value="<?= $p('email_contact') ?>"></div>
                <div class="form-group"><label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="<?= $p('telephone') ?>"></div>
                <div class="form-group" style="margin-bottom:0;"><label class="form-label">Adresse</label>
                    <input type="text" name="adresse" class="form-control" value="<?= $p('adresse') ?>" placeholder="Port-au-Prince, Haïti…"></div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-search"></i> SEO</div></div>
            <div class="card-body">
                <div class="form-group"><label class="form-label">Description meta</label>
                    <textarea name="description_meta" class="form-control" rows="3" maxlength="160"><?= $p('description_meta') ?></textarea>
                    <div class="form-hint">Max 160 caractères — affichée dans Google</div></div>
                <div class="form-group"><label class="form-label">Mots-clés meta</label>
                    <input type="text" name="mots_cles_meta" class="form-control" value="<?= $p('mots_cles_meta') ?>" placeholder="cancer, Haïti, dépistage…"></div>
                <div class="form-group" style="margin-bottom:0;"><label class="form-label">Google Analytics ID</label>
                    <input type="text" name="google_analytics" class="form-control" value="<?= $p('google_analytics') ?>" placeholder="G-XXXXXXXXXX ou UA-XXXXX"></div>
            </div>
        </div>
    </div>
    <div style="margin-top:16px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer les paramètres généraux</button>
    </div>
</form>

<?php elseif ($tab === 'reseaux'): ?>
<!-- RÉSEAUX SOCIAUX -->
<form method="POST">
    <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
    <input type="hidden" name="section" value="reseaux">
    <input type="hidden" name="tab_after" value="reseaux">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-share-alt"></i> Liens réseaux sociaux</div></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <?php foreach ([
                    'facebook'  => ['fab fa-facebook', 'Facebook', '#3b5998'],
                    'twitter'   => ['fab fa-twitter',  'Twitter / X', '#1da1f2'],
                    'instagram' => ['fab fa-instagram', 'Instagram', '#e1306c'],
                    'youtube'   => ['fab fa-youtube',  'YouTube', '#ff0000'],
                    'whatsapp'  => ['fab fa-whatsapp', 'WhatsApp', '#25D366'],
                    'linkedin'  => ['fab fa-linkedin', 'LinkedIn', '#0077B5'],
                ] as $k => [$ic, $label, $color]): ?>
                <div class="form-group">
                    <label class="form-label">
                        <i class="<?= $ic ?>" style="color:<?= $color ?>;margin-right:6px;"></i><?= $label ?>
                    </label>
                    <input type="url" name="<?= $k ?>" class="form-control"
                           value="<?= $p($k) ?>" placeholder="https://…">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div style="margin-top:16px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
    </div>
</form>

<?php elseif ($tab === 'dons'): ?>
<!-- PAIEMENTS -->
<form method="POST">
    <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
    <input type="hidden" name="section" value="dons">
    <input type="hidden" name="tab_after" value="dons">
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fab fa-paypal" style="color:#003087;"></i> PayPal</div></div>
            <div class="card-body">
                <div class="form-group"><label class="form-label">Email PayPal de réception</label>
                    <input type="email" name="paypal_email" class="form-control" value="<?= $p('paypal_email') ?>" placeholder="paiements@gscchaiti.com"></div>
                <div style="background:#F0F9FF;border:1px solid #7DD3FC;border-radius:8px;padding:12px;font-size:.82rem;color:#075985;">
                    <i class="fas fa-info-circle"></i> Les dons PayPal sont envoyés à cet email. Configurez votre compte PayPal Business pour la réception automatique.
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-credit-card" style="color:#635bff;"></i> Stripe</div></div>
            <div class="card-body">
                <div class="form-group"><label class="form-label">Clé publique (pk_live_ ou pk_test_)</label>
                    <input type="text" name="stripe_public_key" class="form-control" value="<?= $p('stripe_public_key') ?>" placeholder="pk_live_…"></div>
                <div class="form-group" style="margin-bottom:0;"><label class="form-label">Clé secrète (sk_live_ ou sk_test_)</label>
                    <input type="password" name="stripe_secret_key" class="form-control" value="<?= $p('stripe_secret_key') ?>" placeholder="sk_live_…">
                    <div class="form-hint">⚠️ Ne partagez jamais cette clé</div></div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-mobile-alt" style="color:#FF6B35;"></i> MonCash (Digicel)</div></div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Client ID</label>
                        <input type="text" name="moncash_client_id" class="form-control" value="<?= $p('moncash_client_id') ?>" placeholder="Client ID MonCash"></div>
                    <div class="form-group"><label class="form-label">Client Secret</label>
                        <input type="password" name="moncash_client_secret" class="form-control" value="<?= $p('moncash_client_secret') ?>" placeholder="Secret MonCash"></div>
                </div>
            </div>
        </div>
    </div>
    <div style="margin-top:16px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer la configuration des paiements</button>
    </div>
</form>

<?php elseif ($tab === 'smtp'): ?>
<!-- EMAIL / SMTP -->
<form method="POST">
    <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
    <input type="hidden" name="section" value="smtp">
    <input type="hidden" name="tab_after" value="smtp">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-envelope-open-text"></i> Configuration email SMTP</div></div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group"><label class="form-label">Serveur SMTP</label>
                    <input type="text" name="smtp_host" class="form-control" value="<?= $p('smtp_host','smtp.gmail.com') ?>" placeholder="smtp.gmail.com"></div>
                <div class="form-group"><label class="form-label">Port</label>
                    <input type="number" name="smtp_port" class="form-control" value="<?= $p('smtp_port','587') ?>" placeholder="587"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Utilisateur SMTP (email)</label>
                    <input type="email" name="smtp_user" class="form-control" value="<?= $p('smtp_user') ?>" placeholder="gscc@gmail.com"></div>
                <div class="form-group"><label class="form-label">Mot de passe d'application</label>
                    <input type="password" name="smtp_password" class="form-control" placeholder="Laisser vide pour ne pas modifier">
                    <div class="form-hint">Utilisez un mot de passe d'application Gmail (16 caractères)</div></div>
            </div>
            <div class="form-group" style="margin-bottom:0;"><label class="form-label">Nom d'expéditeur</label>
                <input type="text" name="smtp_from_name" class="form-control" value="<?= $p('smtp_from_name','GSCC') ?>" placeholder="GSCC — Groupe de Support Contre le Cancer"></div>
        </div>
    </div>
    <div style="margin-top:16px;display:flex;gap:10px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
        <a href="test-email.php" class="btn btn-secondary"><i class="fas fa-paper-plane"></i> Envoyer un email de test</a>
    </div>
</form>

<?php elseif ($tab === 'maintenance'): ?>
<!-- MAINTENANCE -->
<form method="POST">
    <input type="hidden" name="_csrf" value="<?= adminCsrfToken() ?>">
    <input type="hidden" name="section" value="maintenance">
    <input type="hidden" name="tab_after" value="maintenance">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-tools"></i> Mode maintenance</div></div>
        <div class="card-body">
            <div style="background:<?= ($all['maintenance_mode']??'0')==='1'?'#FFF5F5':'#F0FDF4' ?>;border:1px solid <?= ($all['maintenance_mode']??'0')==='1'?'#FCA5A5':'#BBF7D0' ?>;border-radius:10px;padding:16px 18px;margin-bottom:20px;">
                <div style="font-size:1.1rem;font-weight:700;color:<?= ($all['maintenance_mode']??'0')==='1'?'var(--danger)':'var(--success)' ?>;">
                    <?= ($all['maintenance_mode']??'0')==='1'?'🔴 Site en maintenance':'🟢 Site en ligne' ?>
                </div>
                <p style="font-size:.84rem;color:var(--text-muted);margin-top:4px;">
                    <?= ($all['maintenance_mode']??'0')==='1'?'Le site affiche la page de maintenance aux visiteurs.':'Le site est accessible normalement au public.' ?>
                </p>
            </div>
            <div class="switch-wrap" style="margin-bottom:20px;">
                <label class="switch">
                    <input type="checkbox" name="maintenance_mode" <?= ($all['maintenance_mode']??'0')==='1'?'checked':'' ?>>
                    <span class="switch-slider"></span>
                </label>
                <span class="switch-label">Activer le mode maintenance</span>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Message affiché aux visiteurs</label>
                <textarea name="maintenance_message" class="form-control" rows="3"
                          placeholder="Le site est en cours de maintenance. Nous revenons très bientôt !"><?= $p('maintenance_message','Le site est en cours de maintenance. Merci de votre compréhension.') ?></textarea>
            </div>
        </div>
    </div>
    <div style="margin-top:16px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
    </div>
</form>

<?php elseif ($tab === 'systeme'): ?>
<!-- SYSTÈME -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-database"></i> Base de données</div></div>
        <div class="card-body">
            <?php
            $stat_rows = [
                ['Articles',    $sys['articles']??0,    'newspaper'],
                ['Utilisateurs',$sys['users']??0,       'users'],
                ['Total dons',  '$'.number_format($sys['dons']??0,0,',',' '), 'hand-holding-heart'],
                ['Médias galerie',$sys['galerie']??0,   'images'],
                ['Abonnés NL',  $sys['newsletter']??0,  'paper-plane'],
                ['Taille BDD',  ($sys['db_size']??0).' Mo', 'server'],
            ];
            foreach ($stat_rows as [$l,$v,$ic]):?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--border);font-size:.88rem;">
                <span style="color:var(--text-muted);display:flex;align-items:center;gap:8px;"><i class="fas fa-<?= $ic ?>" style="width:14px;color:var(--primary);"></i><?= $l ?></span>
                <strong><?= $v ?></strong>
            </div>
            <?php endforeach;?>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-server"></i> Environnement PHP</div></div>
        <div class="card-body">
            <?php
            $env_rows = [
                ['Version PHP',    phpversion()],
                ['Serveur',        $_SERVER['SERVER_SOFTWARE']??'—'],
                ['Fuseau horaire', date_default_timezone_get()],
                ['Mémoire max',    ini_get('memory_limit')],
                ['Upload max',     ini_get('upload_max_filesize')],
                ['Post max',       ini_get('post_max_size')],
            ];
            foreach ($env_rows as [$l,$v]):?>
            <div style="display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--border);font-size:.85rem;">
                <span style="color:var(--text-muted);"><?= $l ?></span>
                <code style="font-size:.82rem;background:var(--body-bg);padding:2px 6px;border-radius:4px;"><?= htmlspecialchars($v) ?></code>
            </div>
            <?php endforeach;?>
        </div>
    </div>
</div>

<!-- Actions système -->
<div class="card" style="margin-top:16px;">
    <div class="card-header"><div class="card-title"><i class="fas fa-wrench"></i> Actions</div></div>
    <div class="card-body" style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="clear-cache.php" class="btn btn-secondary" onclick="return confirm('Vider le cache ?')">
            <i class="fas fa-broom"></i> Vider le cache
        </a>
        <a href="backup.php" class="btn btn-secondary">
            <i class="fas fa-database"></i> Exporter la BDD
        </a>
        <a href="<?= SITE_URL ?>" target="_blank" class="btn btn-outline">
            <i class="fas fa-external-link-alt"></i> Voir le site
        </a>
    </div>
</div>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
