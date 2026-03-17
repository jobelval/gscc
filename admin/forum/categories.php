<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
if ($_SERVER['REQUEST_METHOD']==='POST' && adminCheckCsrf()) {
    $cat_id = (int)($_POST['cat_id']??0);
    $nom    = trim($_POST['nom']??'');
    $desc   = trim($_POST['description']??'');
    $ordre  = (int)($_POST['ordre']??0);
    $actif  = (int)($_POST['est_actif']??1);
    if ($cat_id && $nom) {
        $pdo->prepare("UPDATE forum_categories SET nom=?,description=?,ordre=?,est_actif=? WHERE id=?")
            ->execute([$nom,$desc,$ordre,$actif,$cat_id]);
        adminFlash('success','Catégorie mise à jour.');
    }
}
header('Location: index.php?tab=categories'); exit;
