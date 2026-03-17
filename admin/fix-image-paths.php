<?php
/**
 * GSCC CMS — admin/fix-image-paths.php
 * Corrige les chemins d'images mal stockés en base de données.
 * 
 * Problème : le CMS stockait "uploads/articles/fichier.jpg"
 * Solution  : doit stocker "assets/uploads/articles/fichier.jpg"
 * 
 * ⚠️ SUPPRIMER ce fichier après utilisation !
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$dry_run = !isset($_GET['fix']); // Aperçu par défaut, fixe avec ?fix=1
$results = [];
$fixed   = 0;
$skipped = 0;

// ── Récupérer tous les articles avec une image ──
$articles = $pdo->query(
    "SELECT id, titre, image_couverture FROM articles WHERE image_couverture IS NOT NULL AND image_couverture != ''"
)->fetchAll();

foreach ($articles as $art) {
    $path = $art['image_couverture'];

    // Cas 1 : déjà correct (commence par assets/ ou http)
    if (str_starts_with($path, 'assets/') || str_starts_with($path, 'http')) {
        // Vérifier quand même que le fichier existe
        $full = ROOT_PATH . ltrim($path, '/');
        $results[] = [
            'id'     => $art['id'],
            'titre'  => $art['titre'],
            'ancien' => $path,
            'nouveau'=> $path,
            'statut' => 'ok',
            'existe' => file_exists($full),
        ];
        $skipped++;
        continue;
    }

    // Cas 2 : chemin sans "assets/" → ajouter le préfixe
    // Exemples : "uploads/articles/..." ou "uploads/galerie/..."
    $nouveau = 'assets/' . ltrim($path, '/');
    $full_nouveau = ROOT_PATH . $nouveau;
    $full_ancien  = ROOT_PATH . $path;

    // Vérifier où le fichier existe réellement
    $existe_nouveau = file_exists($full_nouveau);
    $existe_ancien  = file_exists($full_ancien);

    $statut = 'a_corriger';
    if (!$existe_nouveau && !$existe_ancien) {
        $statut = 'fichier_introuvable';
    }

    if (!$dry_run && $statut === 'a_corriger') {
        $pdo->prepare("UPDATE articles SET image_couverture = ? WHERE id = ?")
            ->execute([$nouveau, $art['id']]);
        $statut = 'corrige';
        $fixed++;
    }

    $results[] = [
        'id'     => $art['id'],
        'titre'  => $art['titre'],
        'ancien' => $path,
        'nouveau'=> $nouveau,
        'statut' => $statut,
        'existe' => $existe_nouveau,
    ];
}

$total_a_corriger = count(array_filter($results, fn($r) => $r['statut'] === 'a_corriger'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fix chemins images — GSCC CMS</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',sans-serif;background:#F0F4FF;padding:32px 16px;color:#1E293B}
        .wrap{max-width:900px;margin:0 auto}
        h1{color:#003399;font-size:1.4rem;margin-bottom:4px}
        .sub{color:#64748B;font-size:.88rem;margin-bottom:24px}
        .card{background:#fff;border-radius:12px;border:1px solid #E2E8F0;padding:20px 24px;margin-bottom:16px}
        .card h2{font-size:1rem;font-weight:700;margin-bottom:14px}
        table{width:100%;border-collapse:collapse;font-size:.82rem}
        th,td{padding:9px 12px;border-bottom:1px solid #F1F5F9;text-align:left;vertical-align:top}
        th{background:#F8FAFC;font-size:.72rem;font-weight:700;color:#64748B;text-transform:uppercase}
        tr:last-child td{border-bottom:none}
        .ok{color:#16A34A;font-weight:600}
        .err{color:#DC2626;font-weight:600}
        .warn{color:#D97706;font-weight:600}
        code{font-family:monospace;font-size:.78rem;background:#F1F5F9;padding:1px 5px;border-radius:3px;word-break:break-all}
        .banner{padding:14px 18px;border-radius:10px;margin-bottom:16px;display:flex;gap:12px;align-items:flex-start;font-size:.9rem}
        .banner.blue{background:#EFF6FF;border:1px solid #BFDBFE;color:#1D4ED8}
        .banner.green{background:#F0FDF4;border:1px solid #86EFAC;color:#166534}
        .banner.red{background:#FFF5F5;border:1px solid #FCA5A5;color:#991B1B}
        .btn{display:inline-flex;align-items:center;gap:7px;padding:10px 22px;border-radius:8px;font-size:.9rem;font-weight:600;text-decoration:none;border:none;cursor:pointer}
        .btn-primary{background:#003399;color:#fff}
        .btn-secondary{background:#F1F5F9;color:#1E293B;border:1px solid #E2E8F0}
        .del{background:#FFF5F5;border:1px solid #FCA5A5;border-radius:8px;padding:12px 16px;font-size:.82rem;color:#991B1B;margin-top:20px}
    </style>
</head>
<body>
<div class="wrap">
    <h1>🔧 Correction des chemins d'images</h1>
    <p class="sub">Ce script détecte et corrige les chemins d'images mal stockés en base de données.</p>

    <?php if ($dry_run): ?>
    <!-- MODE APERÇU -->
    <div class="banner blue">
        <span style="font-size:20px;">👁️</span>
        <div>
            <strong>Mode aperçu</strong> — Aucune modification n'a été effectuée.<br>
            <?php if ($total_a_corriger > 0): ?>
                <strong><?= $total_a_corriger ?> article(s)</strong> ont un chemin à corriger.
                Cliquez sur le bouton ci-dessous pour appliquer la correction.
            <?php else: ?>
                Tous les chemins sont déjà corrects ✅
            <?php endif; ?>
        </div>
    </div>

    <?php if ($total_a_corriger > 0): ?>
    <div style="margin-bottom:16px;display:flex;gap:10px;">
        <a href="?fix=1" class="btn btn-primary">✅ Appliquer la correction (<?= $total_a_corriger ?> article(s))</a>
        <a href="index.php" class="btn btn-secondary">Annuler</a>
    </div>
    <?php else: ?>
    <div style="margin-bottom:16px;">
        <a href="<?= SITE_URL ?>/admin/articles/index.php" class="btn btn-primary">← Retour aux articles</a>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- MODE FIX -->
    <div class="banner green">
        <span style="font-size:20px;">✅</span>
        <div>
            <strong>Correction appliquée !</strong><br>
            <?= $fixed ?> chemin(s) corrigé(s) en base de données.
            Les images devraient maintenant s'afficher correctement sur le site.
        </div>
    </div>
    <div style="margin-bottom:16px;">
        <a href="<?= SITE_URL ?>/admin/articles/index.php" class="btn btn-primary">← Retour aux articles</a>
    </div>
    <?php endif; ?>

    <!-- Tableau résultats -->
    <div class="card">
        <h2>📋 Résultats (<?= count($results) ?> articles avec image)</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Titre</th>
                    <th>Ancien chemin</th>
                    <th>Nouveau chemin</th>
                    <th>Fichier</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($results as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td style="max-width:180px;"><?= htmlspecialchars(mb_substr($r['titre'],0,40)) ?>…</td>
                <td><code><?= htmlspecialchars($r['ancien']) ?></code></td>
                <td><code><?= htmlspecialchars($r['nouveau']) ?></code></td>
                <td class="<?= $r['existe']?'ok':'err' ?>"><?= $r['existe']?'✅ Trouvé':'❌ Absent' ?></td>
                <td>
                    <?php match($r['statut']) {
                        'ok'                => print('<span class="ok">✅ Correct</span>'),
                        'a_corriger'        => print('<span class="warn">⚠️ À corriger</span>'),
                        'corrige'           => print('<span class="ok">✅ Corrigé</span>'),
                        'fichier_introuvable'=> print('<span class="err">❌ Fichier manquant</span>'),
                        default             => print(htmlspecialchars($r['statut'])),
                    }; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    // Articles avec fichier manquant
    $manquants = array_filter($results, fn($r) => !$r['existe'] && $r['statut'] !== 'ok');
    if ($manquants):
    ?>
    <div class="card">
        <h2>⚠️ Fichiers introuvables (<?= count($manquants) ?>)</h2>
        <p style="font-size:.85rem;color:#64748B;margin-bottom:12px;">
            Ces images ne se trouvent ni dans <code>uploads/…</code> ni dans <code>assets/uploads/…</code>.
            Elles ont peut-être été supprimées. Vous devrez les re-uploader depuis le CMS.
        </p>
        <table>
            <thead><tr><th>#</th><th>Titre</th><th>Chemin attendu</th></tr></thead>
            <tbody>
            <?php foreach ($manquants as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars(mb_substr($r['titre'],0,50)) ?></td>
                <td><code><?= htmlspecialchars(ROOT_PATH . $r['nouveau']) ?></code></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="del">
        ⚠️ <strong>IMPORTANT :</strong> Supprimez <code>fix-image-paths.php</code> dès que vous avez terminé.
    </div>
</div>
</body>
</html>
