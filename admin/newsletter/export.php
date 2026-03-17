<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
$rows = $pdo->query("SELECT email,nom,statut,date_inscription,derniere_envoi FROM newsletter_abonnes ORDER BY date_inscription DESC")->fetchAll();
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="gscc-newsletter-'.date('Y-m-d').'.csv"');
$out = fopen('php://output','w');
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($out,['Email','Nom','Statut','Date inscription','Dernier envoi'],';');
foreach ($rows as $r) fputcsv($out,[$r['email'],$r['nom'],$r['statut'],$r['date_inscription'],$r['derniere_envoi']],';');
fclose($out); exit;
