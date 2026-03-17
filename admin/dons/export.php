<?php
/**
 * GSCC CMS — admin/dons/export.php
 * Export CSV de tous les dons
 */
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$statut = $_GET['statut'] ?? '';
$where  = '1=1';
$params = [];
if ($statut) { $where = 'statut=?'; $params[] = $statut; }

try {
    $stmt = $pdo->prepare(
        "SELECT d.id, d.nom_donateur, d.email_donateur, d.telephone,
                d.montant, d.type_don, d.mode_paiement, d.statut,
                d.commentaire, d.date_don,
                CONCAT(u.prenom,' ',u.nom) as membre
         FROM dons d
         LEFT JOIN utilisateurs u ON d.utilisateur_id = u.id
         WHERE $where
         ORDER BY d.date_don DESC"
    );
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Erreur : ' . $e->getMessage());
}

$filename = 'gscc-dons-' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
// BOM UTF-8 pour Excel
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

// En-têtes
fputcsv($out, ['ID','Nom donateur','Email','Téléphone','Montant ($)','Type','Mode paiement','Statut','Commentaire','Date','Membre GSCC'], ';');

foreach ($rows as $row) {
    fputcsv($out, [
        $row['id'],
        $row['nom_donateur'] ?? '',
        $row['email_donateur'] ?? '',
        $row['telephone'] ?? '',
        number_format($row['montant'], 2, ',', ''),
        $row['type_don'],
        $row['mode_paiement'],
        $row['statut'],
        html_entity_decode($row['commentaire'] ?? ''),
        $row['date_don'],
        $row['membre'] ?? 'Non membre',
    ], ';');
}

fclose($out);
exit;
