<?php
/**
 * GSCC CMS — admin/diag-upload.php
 * Script de diagnostic et réparation des dossiers d'upload.
 * ⚠️ SUPPRIMER CE FICHIER après utilisation !
 */
require_once dirname(__DIR__) . '/includes/config.php';

$results = [];

// 1. Vérifier les constantes
$results['constantes'] = [
    'ROOT_PATH'    => defined('ROOT_PATH')    ? ROOT_PATH    : '❌ NON DÉFINI',
    'UPLOADS_PATH' => defined('UPLOADS_PATH') ? UPLOADS_PATH : '❌ NON DÉFINI',
    'UPLOADS_URL'  => defined('UPLOADS_URL')  ? UPLOADS_URL  : '❌ NON DÉFINI',
];

// 2. Dossiers à créer
$dirs = [
    'assets/uploads',
    'assets/uploads/articles',
    'assets/uploads/galerie',
    'assets/uploads/evenements',
    'assets/uploads/campagnes',
    'assets/uploads/temoignages',
    'assets/uploads/survivants',
    'assets/uploads/benevoles',
];

$dirs_status = [];
foreach ($dirs as $dir) {
    $full = ROOT_PATH . $dir;
    $exists  = is_dir($full);
    $writable = $exists ? is_writable($full) : false;
    $created  = false;

    if (!$exists) {
        $created = mkdir($full, 0755, true);
    }

    $dirs_status[$dir] = [
        'path'     => $full,
        'existed'  => $exists,
        'created'  => $created,
        'writable' => is_writable($full),
    ];
}

// 3. PHP upload config
$php_config = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size'       => ini_get('post_max_size'),
    'file_uploads'        => ini_get('file_uploads') ? 'ON ✅' : 'OFF ❌',
    'upload_tmp_dir'      => ini_get('upload_tmp_dir') ?: sys_get_temp_dir(),
    'tmp_dir_writable'    => is_writable(ini_get('upload_tmp_dir') ?: sys_get_temp_dir()) ? '✅ Oui' : '❌ Non',
    'max_execution_time'  => ini_get('max_execution_time') . 's',
    'memory_limit'        => ini_get('memory_limit'),
];

// 4. Test d'écriture réel
$test_file = ROOT_PATH . 'assets/uploads/articles/test_write_' . time() . '.txt';
$write_test = file_put_contents($test_file, 'test');
if ($write_test !== false) {
    @unlink($test_file);
    $write_result = '✅ Écriture OK dans assets/uploads/articles/';
} else {
    $write_result = '❌ IMPOSSIBLE d\'écrire dans assets/uploads/articles/ — c\'est la cause de votre erreur !';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Diagnostic Upload — GSCC CMS</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #F0F4FF; padding: 32px 16px; color: #1E293B; }
        .wrap { max-width: 860px; margin: 0 auto; }
        h1 { font-size: 1.4rem; margin-bottom: 4px; color: #003399; }
        .subtitle { color: #64748B; font-size: .88rem; margin-bottom: 28px; }
        .card { background: #fff; border-radius: 12px; border: 1px solid #E2E8F0; padding: 20px 24px; margin-bottom: 16px; }
        .card h2 { font-size: 1rem; font-weight: 700; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
        table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        td, th { padding: 9px 12px; border-bottom: 1px solid #F1F5F9; text-align: left; vertical-align: top; }
        th { font-size: .75rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: .5px; background: #F8FAFC; }
        tr:last-child td { border-bottom: none; }
        .ok   { color: #16A34A; font-weight: 600; }
        .err  { color: #DC2626; font-weight: 600; }
        .warn { color: #D97706; font-weight: 600; }
        .big  { font-size: 1rem; padding: 16px 20px; border-radius: 10px; margin-bottom: 16px; display: flex; align-items: center; gap: 12px; }
        .big.ok-box  { background: #F0FDF4; border: 1px solid #86EFAC; color: #166534; }
        .big.err-box { background: #FFF5F5; border: 1px solid #FCA5A5; color: #991B1B; }
        code { font-family: 'Courier New', monospace; font-size: .82rem; background: #F1F5F9; padding: 2px 6px; border-radius: 4px; word-break: break-all; }
        .fix-box { background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 10px; padding: 16px 20px; margin-top: 16px; }
        .fix-box h3 { font-size: .95rem; margin-bottom: 10px; color: #92400E; }
        .fix-box ol { padding-left: 20px; font-size: .88rem; line-height: 2; color: #78350F; }
        .del-warn { background: #FFF5F5; border: 1px solid #FCA5A5; border-radius: 8px; padding: 12px 16px; font-size: .84rem; color: #991B1B; margin-top: 20px; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>🔍 Diagnostic Upload — GSCC CMS</h1>
    <p class="subtitle">Ce script vérifie et répare automatiquement vos dossiers d'upload.</p>

    <!-- Résultat test d'écriture -->
    <div class="big <?= str_contains($write_result,'✅')?'ok-box':'err-box' ?>">
        <span style="font-size:24px;"><?= str_contains($write_result,'✅')?'✅':'❌' ?></span>
        <div>
            <strong>Test d'écriture :</strong> <?= $write_result ?>
        </div>
    </div>

    <!-- Constantes PHP -->
    <div class="card">
        <h2>📋 Constantes PHP définies</h2>
        <table>
            <thead><tr><th>Constante</th><th>Valeur</th></tr></thead>
            <tbody>
            <?php foreach ($results['constantes'] as $k => $v): ?>
            <tr>
                <td><code><?= $k ?></code></td>
                <td class="<?= str_contains($v,'NON')?'err':'ok' ?>"><code><?= htmlspecialchars($v) ?></code></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Dossiers -->
    <div class="card">
        <h2>📁 Dossiers d'upload</h2>
        <table>
            <thead><tr><th>Dossier</th><th>Chemin complet</th><th>Existait</th><th>Créé</th><th>Accessible</th></tr></thead>
            <tbody>
            <?php foreach ($dirs_status as $dir => $s): ?>
            <tr>
                <td><code><?= $dir ?></code></td>
                <td><code style="font-size:.75rem;"><?= htmlspecialchars($s['path']) ?></code></td>
                <td class="<?= $s['existed']?'ok':'warn' ?>"><?= $s['existed']?'✅ Oui':'⚠️ Non' ?></td>
                <td class="<?= $s['created']?'ok':($s['existed']?'ok':'err') ?>"><?= $s['existed']?'—':($s['created']?'✅ Créé':'❌ Échec') ?></td>
                <td class="<?= $s['writable']?'ok':'err' ?>"><?= $s['writable']?'✅ Oui':'❌ NON' ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        $any_not_writable = array_filter($dirs_status, fn($s) => !$s['writable']);
        if ($any_not_writable): ?>
        <div class="fix-box">
            <h3>⚠️ Un ou plusieurs dossiers ne sont pas accessibles en écriture</h3>
            <ol>
                <li>Ouvrez <strong>l'Explorateur Windows</strong> et naviguez vers <code><?= htmlspecialchars(ROOT_PATH) ?></code></li>
                <li>Faites un clic droit sur le dossier <strong>assets</strong> → <strong>Propriétés</strong></li>
                <li>Onglet <strong>Sécurité</strong> → cliquez <strong>Modifier</strong></li>
                <li>Sélectionnez <strong>Tout le monde</strong> (ou l'utilisateur Apache) → cochez <strong>Contrôle total</strong></li>
                <li>Validez et réessayez l'upload</li>
            </ol>
        </div>
        <?php endif; ?>
    </div>

    <!-- Config PHP -->
    <div class="card">
        <h2>⚙️ Configuration PHP (upload)</h2>
        <table>
            <thead><tr><th>Paramètre</th><th>Valeur</th></tr></thead>
            <tbody>
            <?php foreach ($php_config as $k => $v): ?>
            <tr>
                <td><code><?= $k ?></code></td>
                <td class="<?= str_contains($v,'❌')?'err':(str_contains($v,'✅')?'ok':'') ?>"><?= htmlspecialchars($v) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        $max = (int)ini_get('upload_max_filesize');
        if ($max < 5): ?>
        <div class="fix-box">
            <h3>⚠️ upload_max_filesize trop petit (<?= $max ?>M)</h3>
            <ol>
                <li>Ouvrez XAMPP Control Panel → cliquez <strong>Config</strong> sur Apache → <strong>PHP (php.ini)</strong></li>
                <li>Cherchez <code>upload_max_filesize</code> et mettez <code>upload_max_filesize = 10M</code></li>
                <li>Cherchez <code>post_max_size</code> et mettez <code>post_max_size = 12M</code></li>
                <li>Sauvegardez et <strong>redémarrez Apache</strong> dans XAMPP</li>
            </ol>
        </div>
        <?php endif; ?>
    </div>

    <!-- Résumé -->
    <div class="card">
        <h2>✅ Résumé des réparations effectuées</h2>
        <p style="font-size:.9rem;color:#374151;">
            <?php
            $created_count = count(array_filter($dirs_status, fn($s) => $s['created']));
            $ok_count      = count(array_filter($dirs_status, fn($s) => $s['writable']));
            echo $created_count > 0
                ? "✅ <strong>$created_count dossier(s) créé(s)</strong> automatiquement.<br>"
                : "ℹ️ Tous les dossiers existaient déjà.<br>";
            echo "<strong>$ok_count/" . count($dirs_status) . " dossiers</strong> sont accessibles en écriture.";
            ?>
        </p>
    </div>

    <div class="del-warn">
        ⚠️ <strong>IMPORTANT :</strong> Supprimez ce fichier <code>diag-upload.php</code> dès que vous avez terminé.
        Il expose des informations sensibles sur votre serveur.
        <br><br>
        <a href="<?= SITE_URL ?>/admin/articles/create.php"
           style="display:inline-block;background:#003399;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:700;margin-top:4px;">
            → Retourner à la création d'articles
        </a>
    </div>
</div>
</body>
</html>
