<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: text/plain; charset=UTF-8');

echo "=== SESSION DUMP ===\n";
echo "admin_id    : " . ($_SESSION['admin_id'] ?? 'NOT SET') . "\n";
echo "admin_role  : " . ($_SESSION['admin_role'] ?? 'NOT SET') . "\n";
echo "admin_email : " . ($_SESSION['admin_email'] ?? 'NOT SET') . "\n";
echo "admin_nom   : " . ($_SESSION['admin_nom'] ?? 'NOT SET') . "\n";
echo "admin_ip    : " . ($_SESSION['admin_ip'] ?? 'NOT SET') . "\n";
echo "REMOTE_ADDR : " . ($_SERVER['REMOTE_ADDR'] ?? 'NOT SET') . "\n";
echo "\n";
echo "isAdmin()   : " . (isAdmin() ? 'TRUE' : 'FALSE') . "\n";
echo "\n";
echo "=== DB CHECK ===\n";
try {
    $stmt = $pdo->prepare("SELECT id, email, role, statut FROM utilisateurs WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id'] ?? 0]);
    $u = $stmt->fetch();
    if ($u) {
        echo "DB id     : " . $u['id'] . "\n";
        echo "DB email  : " . $u['email'] . "\n";
        echo "DB role   : " . $u['role'] . "\n";
        echo "DB statut : " . $u['statut'] . "\n";
    } else {
        echo "User not found in DB\n";
    }
} catch (Exception $e) {
    echo "DB error: " . $e->getMessage() . "\n";
}
