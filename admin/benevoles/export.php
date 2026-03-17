<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
$rows = $pdo->query("SELECT nom,prenom,email,telephone,date_naissance,profession,disponibilites,competences,motivations,statut,notes_admin,date_candidature,date_traitement FROM candidatures_benevoles ORDER BY date_candidature DESC")->fetchAll();
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="gscc-benevoles-'.date('Y-m-d').'.csv"');
$out = fopen('php://output','w');
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($out,['Nom','Prénom','Email','Téléphone','Naissance','Profession','Disponibilités','Compétences','Motivations','Statut','Notes','Date candidature','Date traitement'],';');
foreach ($rows as $r) {
    $comp = json_decode($r['competences']??'[]',true);
    fputcsv($out,[$r['nom'],$r['prenom'],$r['email'],$r['telephone'],$r['date_naissance'],$r['profession'],$r['disponibilites'],implode(', ',$comp??[]),$r['motivations'],$r['statut'],$r['notes_admin'],$r['date_candidature'],$r['date_traitement']],';');
}
fclose($out); exit;
