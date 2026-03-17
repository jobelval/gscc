<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

// Export liste des tables en CSV comme sauvegarde rapide
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$filename = 'gscc-backup-' . date('Y-m-d-His') . '.sql';

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $filename . '"');

echo "-- GSCC CMS Backup\n-- Date: " . date('Y-m-d H:i:s') . "\n-- Database: " . DB_NAME . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";

foreach ($tables as $table) {
    echo "-- Table: $table\n";
    echo "DROP TABLE IF EXISTS `$table`;\n";
    $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
    echo $create[1] . ";\n\n";

    $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_NUM);
    if ($rows) {
        echo "INSERT INTO `$table` VALUES\n";
        $parts = [];
        foreach ($rows as $row) {
            $vals = array_map(function($v) use ($pdo) {
                return $v === null ? 'NULL' : $pdo->quote($v);
            }, $row);
            $parts[] = '(' . implode(',', $vals) . ')';
        }
        echo implode(",\n", $parts) . ";\n\n";
    }
}
echo "SET FOREIGN_KEY_CHECKS=1;\n";
exit;